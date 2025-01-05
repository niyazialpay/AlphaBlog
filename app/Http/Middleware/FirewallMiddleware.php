<?php

namespace App\Http\Middleware;

use App\Models\Firewall\Firewall;
use App\Models\Firewall\FirewallLogs;
use App\Models\IPFilter\IPList;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Eski 'PHP Firewall' scriptindeki mantıkları
 * Laravel middleware yapısına uyarlayan örnek sınıf
 */
class FirewallMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Application|Response|ResponseFactory|JsonResponse|RedirectResponse
     */
    public function handle(Request $request, Closure $next): Application|Response|ResponseFactory|JsonResponse|RedirectResponse
    {
        // Retrieve filters from cache or database
        $filters = Cache::rememberForever(config('cache.prefix').'ip_filter', function () {
            return \App\Models\IPFilter\IPFilter::with('ipList', 'routeList')
                ->where('is_active', true)
                ->get();
        });

        if ($filters->isEmpty()) {
            return $next($request);
        }


        // Separate filters into those with route '*' and others
        $starFilters = $filters->filter(function ($filter) {
            return $filter->routeList->pluck('route')->contains('*');
        });

        $allIps = $starFilters
            ->pluck('ipList.*.ip')
            ->flatten()
            ->unique()
            ->toArray();

        $otherFilters = $filters->diff($starFilters);

        $firewall = Firewall::first();

        if ($firewall->is_active) {
            if ($firewall->check_referer) {
                if (! $this->checkReferer($request)) {
                    $this->blockRequest('Referer Blocked', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_bots) {
                if ($this->isBadBot($request->userAgent(), explode(',', $firewall->bad_bots))) {
                    $this->blockRequest('Bot Blocked', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_request_method) {
                if (! $this->checkRequestMethod($request)) {
                    $this->blockRequest('Invalid Request Method', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_dos) {
                if ($this->checkDosAttack($request)) {
                    $this->blockRequest('DOS Attack', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_union_sql) {
                if ($this->checkUnionSql($request)) {
                    $this->blockRequest('Union SQL Attack', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_click_attack) {
                if ($this->checkClickAttack($request)) {
                    $this->blockRequest('Click Attack', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_xss) {
                if ($this->checkXss($request)) {
                    $this->blockRequest('XSS Attack', $request, $firewall, $allIps);
                }
            }

            if ($firewall->check_cookie_injection) {
                if ($this->checkCookieInjection($request)) {
                    $this->blockRequest('Cookie Injection', $request, $firewall, $allIps);
                }
            }
        }


        // Process filters with route '*'
        foreach ($starFilters as $filter) {
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            if (IpUtils::checkIp($request->getClientIp(), $ips)) {
                if ($filter->list_type === 'blacklist') {
                    // Block the request for blacklisted IPs
                    abort($blockCode);
                } elseif ($filter->list_type === 'whitelist') {
                    // Allow the request for whitelisted IPs
                    return $next($request);
                }
            } else {
                if ($filter->list_type === 'whitelist') {
                    // Block the request if the IP is not in the whitelist
                    abort($blockCode);
                }
                // For blacklists, do nothing if IP doesn't match
            }
        }

        // Process other filters
        foreach ($otherFilters as $filter) {
            $routes = $filter->routeList->pluck('route')->toArray();
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            // Check if the request matches any of the routes in the filter
            if ($request->is($routes)) {
                if (IpUtils::checkIp($request->getClientIp(), $ips)) {
                    if ($filter->list_type === 'blacklist') {
                        // Block the request for blacklisted IPs
                        abort($blockCode);
                    } elseif ($filter->list_type === 'whitelist') {
                        // Allow the request for whitelisted IPs
                        return $next($request);
                    }
                } else {
                    if ($filter->list_type === 'whitelist') {
                        // Block the request if the IP is not in the whitelist
                        abort($blockCode);
                    }
                    // For blacklists, do nothing if IP doesn't match
                }
            }
            // Continue to the next filter if the route doesn't match
        }

        // Allow the request if no filters apply
        return $next($request);
    }


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


    protected function isBadBot($userAgent, $badBots): bool
    {
        if (! $userAgent) {
            return true;
        }

        $agent = strtolower($userAgent);
        foreach ($badBots as $badBot) {
            $bot = trim($badBot);
            if (str_contains($agent, $bot)) {
                return true;
            }
        }

        return false;
    }


    protected function checkRequestMethod(Request $request): bool
    {
        $validMethods = ['get','head','post','put'];
        return in_array(strtolower($request->method()), $validMethods);
    }


    protected function checkDosAttack(Request $request): bool
    {
        $agent = $request->userAgent();
        if (! $agent || $agent === '-') {
            return true;
        }
        return false;
    }

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

    protected function checkClickAttack(Request $request): bool
    {
        $queryString = strtolower($request->getQueryString() ?? '');
        if (str_contains($queryString, 'c2nyaxb0')) {
            return true;
        }
        return false;
    }

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

    protected function checkCookieInjection(Request $request): bool
    {
        $dangerousTags = [
            'applet','base','bgsound','blink','embed','expression','frame',
            'javascript','layer','link','meta','object','script','style',
            'title','vbscript','xml','onabort','onerror','onload','onclick',
        ];

        foreach ($request->cookies as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }
        foreach ($request->post() as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }
        foreach ($request->query() as $value) {
            if ($this->containsDangerous($value, $dangerousTags)) {
                return true;
            }
        }

        return false;
    }

    protected function containsDangerous($haystack, array $badWords): bool
    {
        if(is_array($haystack)) {
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

    protected function whitelistedBotIPS(){
        if(Cache::has(config('cache.prefix')."whitelisted_bot_ips")){
            $ipList = Cache::get(config('cache.prefix')."whitelisted_bot_ips");
        }
        else{
            $urls = [
                'googlebot' => 'https://developers.google.com/search/apis/ipranges/googlebot.json',
                'bingbot' => 'https://www.bing.com/toolbox/bingbot.json',

                'uptimerobot' => 'https://uptimerobot.com/inc/files/ips/IPv4andIPv6.txt',
                '360monitoring_v6' => 'https://app.360monitoring.com/whitelist?v6',
                '360monitoring_php' => 'https://app.360monitoring.com/whitelist.php',
            ];

            $ipList = [];

            foreach ($urls as $url) {
                if (str_ends_with($url, '.json')) {
                    $response = Http::get($url);

                    if ($response->successful()) {
                        $data = $response->json();

                        foreach ($data['prefixes'] as $prefix) {
                            if (isset($prefix['ipv4Prefix'])) {
                                $ipList[] = $prefix['ipv4Prefix'];
                            }
                            if (isset($prefix['ipv6Prefix'])) {
                                $ipList[] = $prefix['ipv6Prefix'];
                            }
                        }
                    }
                }

                if (str_ends_with($url, '.txt') || str_contains($url, 'whitelist')) {
                    $response = Http::get($url);

                    if ($response->successful()) {
                        $lines = explode("\n", $response->body());

                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line !== '' && !str_starts_with($line, '#')) {
                                $ipList[] = $line;
                            }
                        }
                    }
                }
            }


            $duckduckgo_ips = [
                '57.152.72.128',
                '51.8.253.152',
                '40.80.242.63',
                '20.12.141.99',
                '20.49.136.28',
                '51.116.131.221',
                '51.107.40.209',
                '20.40.133.240',
                '20.50.168.91',
                '51.120.48.122',
                '20.193.45.113',
                '40.76.173.151',
                '40.76.163.7',
                '20.185.79.47',
                '52.142.26.175',
                '20.185.79.15',
                '52.142.24.149',
                '40.76.162.208',
                '40.76.163.23',
                '40.76.162.191',
                '40.76.162.247',
                '40.88.21.235',
                '20.191.45.212',
                '52.146.59.12',
                '52.146.59.156',
                '52.146.59.154',
                '52.146.58.236',
                '20.62.224.44',
                '51.104.180.53',
                '51.104.180.47',
                '51.104.180.26',
                '51.104.146.225',
                '51.104.146.235',
                '20.73.202.147',
                '20.73.132.240',
                '20.71.12.143',
                '20.56.197.58',
                '20.56.197.63',
                '20.43.150.93',
                '20.43.150.85',
                '20.44.222.1',
                '40.89.243.175',
                '13.89.106.77',
                '52.143.242.6',
                '52.143.241.111',
                '52.154.60.82',
                '20.197.209.11',
                '20.197.209.27',
                '20.226.133.105',
                '191.234.216.4',
                '191.234.216.178',
                '20.53.92.211',
                '20.53.91.2',
                '20.207.99.197',
                '20.207.97.190',
                '40.81.250.205',
                '40.64.106.11',
                '40.64.105.247',
                '20.72.242.93',
                '20.99.255.235',
                '20.113.3.121',
                '52.224.16.221',
                '52.224.21.53',
                '52.224.20.204',
                '52.224.21.19',
                '52.224.20.249',
                '52.224.20.203',
                '52.224.20.190',
                '52.224.16.229',
                '52.224.21.20',
                '52.146.63.80',
                '52.224.20.227',
                '52.224.20.193',
                '52.190.37.160',
                '52.224.21.23',
                '52.224.20.223',
                '52.224.20.181',
                '52.224.21.49',
                '52.224.21.55',
                '52.224.21.61',
                '52.224.19.152',
                '52.224.20.186',
                '52.224.21.27',
                '52.224.21.51',
                '52.224.20.174',
                '52.224.21.4',
                '51.104.164.109',
                '51.104.167.71',
                '51.104.160.177',
                '51.104.162.149',
                '51.104.167.95',
                '51.104.167.54',
                '51.104.166.111',
                '51.104.167.88',
                '51.104.161.32',
                '51.104.163.250',
                '51.104.164.189',
                '51.104.167.19',
                '51.104.160.167',
                '51.104.167.110',
                '20.191.44.119',
                '51.104.167.104',
                '20.191.44.234',
                '51.104.164.215',
                '51.104.167.52',
                '20.191.44.22',
                '51.104.167.87',
                '51.104.167.96',
                '20.191.44.16',
                '51.104.167.61',
                '51.104.164.147',
                '20.50.48.159',
                '40.114.182.172',
                '20.50.50.130',
                '20.50.50.163',
                '20.50.50.46',
                '40.114.182.153',
                '20.50.50.118',
                '20.50.49.55',
                '20.50.49.25',
                '40.114.183.251',
                '20.50.50.123',
                '20.50.49.237',
                '20.50.48.192',
                '20.50.50.134',
                '51.138.90.233',
                '40.114.183.196',
                '20.50.50.146',
                '40.114.183.88',
                '20.50.50.145',
                '20.50.50.121',
                '20.50.49.40',
                '51.138.90.206',
                '40.114.182.45',
                '51.138.90.161',
                '20.50.49.0',
                '40.119.232.215',
                '104.43.55.167',
                '40.119.232.251',
                '40.119.232.50',
                '40.119.232.146',
                '40.119.232.218',
                '104.43.54.127',
                '104.43.55.117',
                '104.43.55.116',
                '104.43.55.166',
                '52.154.169.50',
                '52.154.171.70',
                '52.154.170.229',
                '52.154.170.113',
                '52.154.171.44',
                '52.154.172.2',
                '52.143.244.81',
                '52.154.171.87',
                '52.154.171.250',
                '52.154.170.28',
                '52.154.170.122',
                '52.143.243.117',
                '52.143.247.235',
                '52.154.171.235',
                '52.154.171.196',
                '52.154.171.0',
                '52.154.170.243',
                '52.154.170.26',
                '52.154.169.200',
                '52.154.170.96',
                '52.154.170.88',
                '52.154.171.150',
                '52.154.171.205',
                '52.154.170.117',
                '52.154.170.209',
                '191.235.202.48',
                '191.233.3.202',
                '191.235.201.214',
                '191.233.3.197',
                '191.235.202.38',
                '20.53.78.144',
                '20.193.24.10',
                '20.53.78.236',
                '20.53.78.138',
                '20.53.78.123',
                '20.53.78.106',
                '20.193.27.215',
                '20.193.25.197',
                '20.193.12.126',
                '20.193.24.251',
                '20.204.242.101',
                '20.207.72.113',
                '20.204.242.19',
                '20.219.45.67',
                '20.207.72.11',
                '20.219.45.190',
                '20.204.243.55',
                '20.204.241.148',
                '20.207.72.110',
                '20.204.240.172',
                '20.207.72.21',
                '20.204.246.81',
                '20.207.107.181',
                '20.204.246.254',
                '20.219.43.246',
                '52.149.25.43',
                '52.149.61.51',
                '52.149.58.139',
                '52.149.60.38',
                '52.148.165.38',
                '52.143.95.162',
                '52.149.56.151',
                '52.149.30.45',
                '52.149.58.173',
                '52.143.95.204',
                '52.149.28.83',
                '52.149.58.69',
                '52.148.161.87',
                '52.149.58.27',
                '52.149.28.18',
                '20.79.226.26',
                '20.79.239.66',
                '20.79.238.198',
                '20.113.14.159',
                '20.75.144.152',
                '20.43.172.120',
                '20.53.134.160',
                '20.201.15.208',
                '20.93.28.24',
                '20.61.34.40',
                '52.242.224.168',
                '20.80.129.80',
                '20.195.108.47',
                '4.195.133.120',
                '4.228.76.163',
                '4.182.131.108',
                '4.209.224.56',
                '108.141.83.74',
                '4.213.46.14',
                '172.169.17.165',
                '51.8.71.117',
                '20.3.1.178',
            ];

            $yandex_bot_ips = [
                '5.45.192.0/18',
                '5.255.192.0/18',
                '37.9.64.0/18',
                '37.140.128.0/18',
                '77.88.0.0/18',
                '84.252.160.0/19',
                '87.250.224.0/19',
                '90.156.176.0/22',
                '93.158.128.0/18',
                '95.108.128.0/17',
                '141.8.128.0/18',
                '178.154.128.0/18',
                '213.180.192.0/19',
                '185.32.187.0/24',
                '2a02:6b8::/29'
            ];

            $ipList = array_merge($ipList, $duckduckgo_ips, $yandex_bot_ips);
            Cache::put(config('cache.prefix')."whitelisted_bot_ips", $ipList, now()->addDay());
        }

        return $ipList;

    }

    protected function blockRequest($reason, Request $request, $firewall, $ipList): void
    {
        $ip       = $request->ip();
        $whitelistedBotIPS = $this->whitelistedBotIPS();
        $ips = array_merge($ipList, $whitelistedBotIPS);
        if (IpUtils::checkIp($ip, $ips)) {
            return;
        }

        Log::warning(
            "[FIREWALL] Reason: {$reason} | IP: {$ip} | Agent: {$request->userAgent()} | URL: {$request->fullUrl()}"
        );

        $ipList = IpList::updateOrCreate(
            [
                'ip'         => $ip,
                'filter_id'  => $firewall->blacklist_rule_id,
            ]
        );

        if ($ipList->wasRecentlyCreated) {
            FirewallLogs::create([
                'ip'                => $ip,
                'user_agent'        => $request->userAgent(),
                'url'               => $request->fullUrl(),
                'reason'            => $reason,
                'request_data'      => json_encode($request->all()),
                'ip_filter_id'      => $firewall->blacklist_rule_id,
                'ip_list_id'        => $ipList->id,
            ]);
        }
    }
}

