<?php

namespace App\Http\Middleware;

use App\Models\Firewall\Firewall;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPList;
use App\Jobs\ProcessFirewallBlock;
use App\Support\IPFilterCache;
use App\Support\IpRangeMatcher;
use App\Support\TrustedBots;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FirewallMiddleware
{
    protected array $compiledFilters = [];
    protected array $trustedBotIps = [];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Application|Response|ResponseFactory|JsonResponse|RedirectResponse|StreamedResponse
     * @throws ConnectionException
     */
    public function handle(Request $request, Closure $next): Application|Response|ResponseFactory|JsonResponse|RedirectResponse|StreamedResponse
    {
        $compiled = IPFilterCache::get();
        $this->compiledFilters = $compiled;
        $this->trustedBotIps = TrustedBots::ipRanges();

        $globalFilters = $compiled['global'] ?? [];
        $scopedFilters = $compiled['scoped'] ?? [];
        $allIps = $compiled['all_ips'] ?? [];

        if (empty($globalFilters) && empty($scopedFilters)) {
            return $next($request);
        }

        if ($this->shouldBypassTrustedBot($request)) {
            return $next($request);
        }

        // Fetch main Firewall settings
        $firewall = Firewall::first();

        // If the firewall is active, perform additional checks
        if ($firewall->is_active) {
            // Check Referer
            if ($firewall->check_referer) {
                if (!$this->checkReferer($request)) {
                    $this->blockRequest('Referer Blocked', $request, $firewall, $allIps);
                }
            }

            // Check Bots
            if ($firewall->check_bots) {
                // Convert comma-separated string from DB into array
                $badBots = explode(',', $firewall->bad_bots ?? '');
                if ($this->isBadBot($request->userAgent(), $badBots)) {
                    $this->blockRequest('Bot Blocked', $request, $firewall, $allIps);
                }
            }

            // Check valid request methods (GET, HEAD, POST, PUT)
            if ($firewall->check_request_method) {
                if (!$this->checkRequestMethod($request)) {
                    $this->blockRequest('Invalid Request Method', $request, $firewall, $allIps);
                }
            }

            // Check possible DOS attacks (simple user agent check here)
            if ($firewall->check_dos) {
                if ($this->checkDosAttack($request)) {
                    $this->blockRequest('DOS Attack', $request, $firewall, $allIps);
                }
            }

            // Check UNION SQL patterns in query strings
            if ($firewall->check_union_sql) {
                if ($this->checkUnionSql($request)) {
                    $this->blockRequest('Union SQL Attack', $request, $firewall, $allIps);
                }
            }

            // Check for suspicious strings related to click-jacking or injection
            if ($firewall->check_click_attack) {
                if ($this->checkClickAttack($request)) {
                    $this->blockRequest('Click Attack', $request, $firewall, $allIps);
                }
            }

            // Check XSS patterns
            if ($firewall->check_xss) {
                if ($this->checkXss($request)) {
                    $this->blockRequest('XSS Attack', $request, $firewall, $allIps);
                }
            }

            // Check cookie injection
            if ($firewall->check_cookie_injection) {
                if ($this->checkCookieInjection($request)) {
                    $this->blockRequest('Cookie Injection', $request, $firewall, $allIps);
                }
            }
        }

        // Process filters that apply to all routes ('*')
        foreach ($globalFilters as $filter) {
            $ips = $filter['ips'] ?? [];
            $blockCode = $filter['code'] ?? 403;

            if ($this->checkIpInList($request->getClientIp(), $ips)) {
                if ($filter['list_type'] === 'blacklist') {
                    // Block blacklisted IP
                    abort($blockCode);
                } elseif ($filter['list_type'] === 'whitelist') {
                    // Allow whitelisted IP
                    return $next($request);
                }
            } else {
                // IP is not in the list
                if ($filter['list_type'] === 'whitelist') {
                    // If it's a whitelist and not found, block
                    abort($blockCode);
                }
                // If it's a blacklist and not found, ignore
            }
        }

        // Process filters for specific routes
        foreach ($scopedFilters as $filter) {
            $routes = $filter['routes'] ?? [];
            $ips = $filter['ips'] ?? [];
            $blockCode = $filter['code'] ?? 403;

            // Check if the request path matches any route for this filter
            if (!empty($routes) && $request->is($routes)) {
                // If the IP is in the filter's list
                if ($this->checkIpInList($request->getClientIp(), $ips)) {
                    if ($filter['list_type'] === 'blacklist') {
                        // Block blacklisted IP
                        abort($blockCode);
                    } elseif ($filter['list_type'] === 'whitelist') {
                        // Allow whitelisted IP
                        return $next($request);
                    }
                } else {
                    // IP not found in this filter's list
                    if ($filter['list_type'] === 'whitelist') {
                        // Whitelist filter => blocks if not in the list
                        abort($blockCode);
                    }
                    // Blacklist filter => ignores if not in the list
                }
            }
            // If the route does not match, continue to the next filter
        }

        // If no filters block the request, allow it
        return $next($request);
    }

    /**
     * Checks the Referer header for POST requests (simple host match).
     *
     * @param Request $request
     * @return bool
     */
    protected function checkReferer(Request $request): bool
    {
        if ($request->isMethod('post')) {
            $referer = $request->headers->get('referer');
            if ($referer && !str_contains($referer, $request->getHost())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if the user agent matches any known bad bot from the DB list.
     * If the user agent is empty or no match is found, it will NOT be blocked.
     *
     * @param string|null $userAgent
     * @param array $badBots
     * @return bool
     */
    protected function isBadBot(?string $userAgent, array $badBots): bool
    {
        if (!$userAgent) {
            return false;
        }

        $agent = strtolower($userAgent);

        $badBots = array_map('trim', $badBots);
        $badBots = array_filter($badBots, 'strlen');

        foreach ($badBots as $badBot) {
            $badBot = strtolower($badBot);

            if (str_contains($agent, $badBot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the HTTP method is valid (GET, HEAD, POST, PUT).
     *
     * @param Request $request
     * @return bool
     */
    protected function checkRequestMethod(Request $request): bool
    {
        $validMethods = ['get', 'head', 'post', 'put'];
        return in_array(strtolower($request->method()), $validMethods);
    }

    /**
     * Checks if the request might be a DOS attack (simple user agent check).
     *
     * @param Request $request
     * @return bool
     */
    protected function checkDosAttack(Request $request): bool
    {
        $agent = $request->userAgent();
        // Previously we blocked if $agent was empty.
        // But you can adjust this logic as needed.
        if (!$agent || $agent === '-') {
            return true;
        }
        return false;
    }

    /**
     * Checks if the query string might contain a UNION SQL attack pattern.
     *
     * @param Request $request
     * @return bool
     */
    protected function checkUnionSql(Request $request): bool
    {
        $queryString = strtolower($request->getQueryString() ?? '');
        if (empty($queryString)) {
            return false;
        }

        $patterns = [
            '/\bunion\b.*\bselect\b/',
            '/(\bor\b|\band\b)\s+\d=\d/',
            '#[\d\W](union select|union join|union distinct)[\d\W]#is',
            '#[\d\W](union|union select|insert|from|where|concat|into|cast|truncate|select|delete|having)[\d\W]#is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $queryString)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the query string might contain suspicious strings like 'c2nyaxb0'.
     *
     * @param Request $request
     * @return bool
     */
    protected function checkClickAttack(Request $request): bool
    {
        $queryString = strtolower($request->getQueryString() ?? '');
        if (str_contains($queryString, 'c2nyaxb0')) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the query string contains common XSS patterns.
     *
     * @param Request $request
     * @return bool
     */
    protected function checkXss(Request $request): bool
    {
        $queryString = strtolower($request->getQueryString() ?? '');

        $badStrings = [
            '<script', 'javascript:', 'vbscript:', 'onload=', 'onclick=',
            'alert(', 'document.cookie', 'expression(',
            // Evil starting attributes
            '#(<[^>]+[\x00-\x20\"\'\/])(form|formaction|on\w*|style|xmlns|xlink:href)[^>]*>?#iUu',

            // javascript:, livescript:, vbscript:, mocha: protocols
            '!((java|live|vb)script|mocha|feed|data):(\w)*!iUu',
            '#-moz-binding[\x00-\x20]*:#u',

            // Unneeded tags
            '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|img)[^>]*>?#i'
        ];

        foreach ($badStrings as $bad) {
            if (str_contains($queryString, $bad)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks cookies, POST data, and GET data for dangerous tags (like <script>, <embed>, etc.).
     *
     * @param Request $request
     * @return bool
     */
    protected function checkCookieInjection(Request $request): bool
    {
        $dangerousTags = [
            'applet', 'base', 'bgsound', 'blink', 'embed', 'expression', 'frame',
            'javascript', 'layer', 'link', 'meta', 'object', 'script', 'style',
            'title', 'vbscript', 'xml', 'onabort', 'onerror', 'onload', 'onclick',
        ];

        // Check cookies
        foreach ($request->cookies as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }

        // Check POST data
        foreach ($request->post() as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }

        // Check GET data
        foreach ($request->query() as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method to detect dangerous keywords or tags in a string or array.
     *
     * @param mixed $haystack
     * @param array $badWords
     * @return bool
     */
    protected function containsDangerous(mixed $haystack, array $badWords): bool
    {
        if (is_array($haystack)) {
            foreach ($haystack as $value) {
                if ($this->containsDangerous($value, $badWords)) {
                    return true;
                }
            }
            return false;
        }

        $haystack = strtolower($haystack);
        foreach ($badWords as $word) {
            if (str_contains($haystack, $word)) {
                return true;
            }
        }

        return false;
    }

/**
     * Blocks the request, logs it, and optionally adds the IP to the firewall blacklist.
     *
     * @param string $reason
     * @param Request $request
     * @param Firewall $firewall
     * @param array $ipList
     * @return void
     * @throws ConnectionException
     */
    protected function blockRequest(string $reason, Request $request, Firewall $firewall, array $ipList): void
    {
        $ip = $request->ip();

        // Merge all IPs with trusted bot IPs to bypass if needed
        $trustedBotIps = TrustedBots::ipRanges();
        $ips = array_merge($ipList, $trustedBotIps);

        // If the IP is in the merged list, do not block
        if ($this->checkIpInList($ip, $ips)) {
            return;
        }

        Log::warning(
            "[FIREWALL] Reason: $reason | IP: $ip | Agent: {$request->userAgent()} | URL: {$request->fullUrl()}"
        );

        if ($this->isIpListedInAnyFilter($ip)) {
            return;
        }

        ProcessFirewallBlock::dispatch(
            $ip,
            $reason,
            $request->userAgent(),
            $request->fullUrl(),
            $request->all(),
            $firewall->blacklist_rule_id
        );
    }

    /**
     * Checks if a given IP is already listed in any filter (whitelist or blacklist)
     *
     * @param string $clientIp
     * @return bool
     */
    protected function isIpListedInAnyFilter(string $clientIp): bool
    {
        $allIps = $this->compiledFilters['all_ips'] ?? [];
        return $this->checkIpInList($clientIp, $allIps);
    }

    /**
     * Checks if a given client IP is in a list of IPs or CIDR blocks.
     * If the record in the DB is a plain IP (no slash), we append /32 for IPv4
     * or /128 for IPv6 before checking with the helper.
     *
     * @param string $clientIp
     * @param string[] $ips
     * @return bool
     */
    protected function checkIpInList(string $clientIp, array $ips): bool
    {
        return IpRangeMatcher::matches($clientIp, $ips);
    }

    /**
     * Checks if a specific IP is already included in the given filter_id's IP list
     * (whether it is exactly the same IP or within the same CIDR range).
     *
     * @param string $clientIp
     * @param int $filterId
     * @return bool
     */
    protected function ipAlreadyListedInFilter(string $clientIp, int $filterId): bool
    {
        $filter = $this->findCompiledFilterById($filterId);

        if ($filter) {
            return $this->checkIpInList($clientIp, $filter['ips'] ?? []);
        }

        $allIps = IpList::where('filter_id', $filterId)->pluck('ip')->toArray();
        return $this->checkIpInList($clientIp, $allIps);
    }

    protected function findCompiledFilterById(int $filterId): ?array
    {
        foreach (['global', 'scoped'] as $group) {
            foreach ($this->compiledFilters[$group] ?? [] as $filter) {
                if (($filter['id'] ?? null) === $filterId) {
                    return $filter;
                }
            }
        }

        return null;
    }

    protected function shouldBypassTrustedBot(Request $request): bool
    {
        $clientIp = $request->getClientIp() ?? '';

        if ($clientIp && IpRangeMatcher::matches($clientIp, $this->trustedBotIps)) {
            return true;
        }

        return TrustedBots::isTrustedAgent($request->userAgent());
    }
}
