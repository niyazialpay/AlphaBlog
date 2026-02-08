<?php

namespace App\Http\Middleware;

use App\Jobs\ProcessFirewallAiReview;
use App\Jobs\ProcessFirewallBlock;
use App\Models\Firewall\Firewall;
use App\Models\IPFilter\IPList;
use App\Support\IPFilterCache;
use App\Support\IpRangeMatcher;
use App\Support\TrustedBots;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FirewallMiddleware
{
    protected array $compiledFilters = [];

    protected array $trustedBotIps = [];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Application|Response|ResponseFactory|JsonResponse|RedirectResponse|StreamedResponse
    {
        $compiled = IPFilterCache::get();
        $this->compiledFilters = $compiled;
        $this->trustedBotIps = TrustedBots::ipRanges();

        $globalFilters = $compiled['global'] ?? [];
        $scopedFilters = $compiled['scoped'] ?? [];
        $allIps = $compiled['all_ips'] ?? [];

        if ($this->shouldBypassTrustedBot($request)) {
            return $next($request);
        }

        $firewall = Firewall::query()->first();
        $wasFlaggedByRuleChecks = false;

        if ($firewall && $firewall->is_active) {
            if ($firewall->check_referer) {
                if (! $this->checkReferer($request)) {
                    $this->blockRequest('Referer Blocked', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_bots) {
                $badBots = explode(',', $firewall->bad_bots ?? '');
                if ($this->isBadBot($request->userAgent(), $badBots)) {
                    $this->blockRequest('Bot Blocked', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_request_method) {
                if (! $this->checkRequestMethod($request)) {
                    $this->blockRequest('Invalid Request Method', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_dos) {
                if ($this->checkDosAttack($request)) {
                    $this->blockRequest('DOS Attack', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_union_sql) {
                if ($this->checkUnionSql($request)) {
                    $this->blockRequest('Union SQL Attack', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_click_attack) {
                if ($this->checkClickAttack($request)) {
                    $this->blockRequest('Click Attack', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_xss) {
                if ($this->checkXss($request)) {
                    $this->blockRequest('XSS Attack', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

            if ($firewall->check_cookie_injection) {
                if ($this->checkCookieInjection($request)) {
                    $this->blockRequest('Cookie Injection', $request, $firewall, $allIps);
                    $wasFlaggedByRuleChecks = true;
                }
            }

        }

        foreach ($globalFilters as $filter) {
            $ips = $filter['ips'] ?? [];
            $blockCode = $filter['code'] ?? 403;

            if ($this->checkIpInList($request->getClientIp() ?? '', $ips)) {
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
            if (! empty($routes) && $request->is($routes)) {
                // If the IP is in the filter's list
                if ($this->checkIpInList($request->getClientIp() ?? '', $ips)) {
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

        if ($firewall && $firewall->is_active) {
            $this->dispatchAiReview($request, $firewall, $wasFlaggedByRuleChecks, $allIps);
        }

        return $next($request);
    }

    /**
     * Checks the Referer header for POST requests (simple host match).
     */
    protected function checkReferer(Request $request): bool
    {
        if ($request->isMethod('post')) {
            $referer = $request->headers->get('referer');
            if ($referer && ! str_contains($referer, $request->getHost())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the user agent matches any known bad bot from the DB list.
     * If the user agent is empty or no match is found, it will NOT be blocked.
     */
    protected function isBadBot(?string $userAgent, array $badBots): bool
    {
        if (! $userAgent) {
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
     */
    protected function checkRequestMethod(Request $request): bool
    {
        $validMethods = ['get', 'head', 'post', 'put'];

        return in_array(strtolower($request->method()), $validMethods);
    }

    /**
     * Checks if the request might be a DOS attack (simple user agent check).
     */
    protected function checkDosAttack(Request $request): bool
    {
        $agent = $request->userAgent();
        // Previously we blocked if $agent was empty.
        // But you can adjust this logic as needed.
        if (! $agent || $agent === '-') {
            return true;
        }

        return false;
    }

    /**
     * Checks if the query string might contain a UNION SQL attack pattern.
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
            '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base|img)[^>]*>?#i',
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

    protected function dispatchAiReview(Request $request, Firewall $firewall, bool $wasFlaggedByRuleChecks, array $allIps): void
    {
        if (! $firewall->ai_review_enabled) {
            return;
        }

        if ($wasFlaggedByRuleChecks) {
            return;
        }

        if ($this->shouldIgnorePathForAi($request)) {
            return;
        }

        $clientIp = $request->getClientIp() ?? '';

        if ($clientIp === '') {
            return;
        }

        $trustedAndListedIps = array_values(array_unique(array_merge($allIps, $this->trustedBotIps)));

        if ($this->checkIpInList($clientIp, $trustedAndListedIps)) {
            return;
        }

        $signals = $this->collectAiSignals($request);

        if (! $this->shouldSampleAiReview($firewall, $signals)) {
            return;
        }

        if (! $this->hasMinimumSuspiciousHits($signals, $clientIp)) {
            return;
        }

        if (! $this->hasAiReviewBudget($clientIp)) {
            return;
        }

        $payload = $this->buildAiPayload($request, $firewall, $signals);
        $dispatchKey = 'firewall:ai:dispatch:'.$payload['fingerprint'];

        if (! Cache::add($dispatchKey, true, now()->addSeconds(30))) {
            return;
        }

        ProcessFirewallAiReview::dispatchAfterResponse($payload, $firewall->id);
    }

    protected function hasMinimumSuspiciousHits(array $signals, string $clientIp): bool
    {
        if ($signals === []) {
            return true;
        }

        $requiredHits = max(1, (int) config('ai.firewall.suspicious_hits_before_review', 2));

        if ($requiredHits <= 1) {
            return true;
        }

        $windowSeconds = max(60, (int) config('ai.firewall.suspicious_hits_window_seconds', 900));
        $key = 'firewall:ai:suspicious:'.sha1($clientIp);
        $hits = Cache::increment($key);

        if ($hits === 1) {
            Cache::put($key, 1, now()->addSeconds($windowSeconds));
        }

        return $hits >= $requiredHits;
    }

    protected function hasAiReviewBudget(string $clientIp): bool
    {
        $maxGlobalPerMinute = max(1, (int) config('ai.firewall.max_reviews_per_minute', 20));
        $maxPerIpPerHour = max(1, (int) config('ai.firewall.max_reviews_per_ip_per_hour', 2));

        $globalKey = 'firewall:ai:budget:global:'.now()->format('YmdHi');
        $globalCount = Cache::increment($globalKey);

        if ($globalCount === 1) {
            Cache::put($globalKey, 1, now()->addMinutes(2));
        }

        if ($globalCount > $maxGlobalPerMinute) {
            return false;
        }

        $ipKey = 'firewall:ai:budget:ip:'.sha1($clientIp).':'.now()->format('YmdH');
        $ipCount = Cache::increment($ipKey);

        if ($ipCount === 1) {
            Cache::put($ipKey, 1, now()->addHours(2));
        }

        return $ipCount <= $maxPerIpPerHour;
    }

    protected function shouldIgnorePathForAi(Request $request): bool
    {
        $path = ltrim(strtolower($request->path()), '/');

        if ($path === '') {
            return false;
        }

        $adminPath = trim((string) config('settings.admin_panel_path'), '/');
        if (
            $adminPath !== ''
            && (
                $path === strtolower($adminPath)
                || Str::startsWith($path, strtolower($adminPath).'/')
            )
        ) {
            return true;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === '') {
            return false;
        }

        return in_array($extension, [
            'css',
            'js',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'svg',
            'ico',
            'webp',
            'avif',
            'woff',
            'woff2',
            'ttf',
            'eot',
            'map',
            'txt',
            'xml',
            'pdf',
            'mp4',
            'webm',
            'mp3',
        ], true);
    }

    protected function collectAiSignals(Request $request): array
    {
        $path = strtolower($request->path());
        $query = strtolower((string) ($request->getQueryString() ?? ''));
        $uri = $path.($query !== '' ? '?'.$query : '');

        $signals = [];
        $patterns = [
            'wp-admin' => 'wordpress_probe',
            'wp-login' => 'wordpress_probe',
            'xmlrpc.php' => 'wordpress_probe',
            'phpmyadmin' => 'phpmyadmin_probe',
            '.env' => 'env_probe',
            '.git' => 'git_probe',
            '../' => 'path_traversal_pattern',
            '%2e%2e%2f' => 'path_traversal_pattern',
            'union%20select' => 'sql_union_pattern',
            'union+select' => 'sql_union_pattern',
            'information_schema' => 'schema_enumeration',
            '<script' => 'xss_pattern',
            '%3cscript' => 'xss_pattern',
            '<?php' => 'php_payload_pattern',
            '%3c%3fphp' => 'php_payload_pattern',
            'cmd=' => 'command_parameter_pattern',
            'shell_exec' => 'command_parameter_pattern',
            'base64_decode' => 'obfuscated_payload',
        ];

        foreach ($patterns as $needle => $label) {
            if (str_contains($uri, $needle)) {
                $signals[] = $label;
            }
        }

        if (strlen($query) >= 600) {
            $signals[] = 'long_query';
        }

        if (strlen((string) ($request->userAgent() ?? '')) <= 2) {
            $signals[] = 'missing_or_short_user_agent';
        }

        if (! in_array(strtolower($request->method()), ['get', 'head', 'post', 'put'], true)) {
            $signals[] = 'uncommon_method';
        }

        return array_values(array_unique($signals));
    }

    protected function shouldSampleAiReview(Firewall $firewall, array $signals): bool
    {
        if ($signals !== []) {
            return true;
        }

        $sampleRate = max(0, min(100, (int) $firewall->ai_sample_rate));

        if ($sampleRate === 0) {
            return false;
        }

        return mt_rand(1, 100) <= $sampleRate;
    }

    protected function buildAiPayload(Request $request, Firewall $firewall, array $signals): array
    {
        $sanitizedData = Arr::except($request->all(), [
            '_token',
            'token',
            'password',
            'password_confirmation',
            'current_password',
            'api_key',
            'secret',
        ]);

        $encodedBody = json_encode($sanitizedData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $maxPayloadChars = max(500, min(12000, (int) $firewall->ai_max_payload_chars));
        $bodyPreview = Str::limit((string) $encodedBody, $maxPayloadChars);
        $query = (string) ($request->getQueryString() ?? '');

        $fingerprint = hash('sha256', implode('|', [
            (string) ($request->getClientIp() ?? ''),
            strtoupper($request->method()),
            strtolower($request->path()),
            strtolower($query),
            strtolower((string) ($request->userAgent() ?? '')),
            implode(',', $signals),
        ]));

        return [
            'fingerprint' => $fingerprint,
            'ip' => (string) ($request->getClientIp() ?? ''),
            'method' => strtoupper($request->method()),
            'url' => $request->fullUrl(),
            'path' => '/'.ltrim($request->path(), '/'),
            'query' => $query,
            'user_agent' => $request->userAgent(),
            'headers' => array_filter([
                'referer' => $request->headers->get('referer'),
                'content_type' => $request->headers->get('content-type'),
                'accept' => $request->headers->get('accept'),
                'accept_language' => $request->headers->get('accept-language'),
                'x_forwarded_for' => $request->headers->get('x-forwarded-for'),
            ], fn ($value) => filled($value)),
            'signals' => $signals,
            'body_preview' => $bodyPreview,
            'is_ajax' => $request->ajax(),
            'is_secure' => $request->isSecure(),
        ];
    }

    /**
     * Blocks the request, logs it, and optionally adds the IP to the firewall blacklist.
     */
    protected function blockRequest(string $reason, Request $request, Firewall $firewall, array $ipList): void
    {
        $ip = $request->ip();

        if (! $ip) {
            return;
        }

        $ips = array_merge($ipList, $this->trustedBotIps);

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
     * @param  string[]  $ips
     */
    protected function checkIpInList(string $clientIp, array $ips): bool
    {
        return IpRangeMatcher::matches($clientIp, $ips);
    }

    /**
     * Checks if a specific IP is already included in the given filter_id's IP list
     * (whether it is exactly the same IP or within the same CIDR range).
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
