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

class FirewallMiddleware
{
    public function handle(Request $request, Closure $next): Application|Response|ResponseFactory|JsonResponse|RedirectResponse
    {
        $filters = Cache::rememberForever(config('cache.prefix').'ip_filter', function () {
            return \App\Models\IPFilter\IPFilter::with('ipList', 'routeList')
                ->where('is_active', true)
                ->get();
        });

        if ($filters->isEmpty()) {
            return $next($request);
        }

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
            if ($firewall->check_referer && !$this->checkReferer($request)) {
                $this->blockRequest('Referer Blocked', $request, $firewall, $allIps);
            }

            if ($firewall->check_bots && $this->isBadBot($request->userAgent(), explode(',', $firewall->bad_bots))) {
                $this->blockRequest('Bot Blocked', $request, $firewall, $allIps);
            }

            if ($firewall->check_request_method && !$this->checkRequestMethod($request)) {
                $this->blockRequest('Invalid Request Method', $request, $firewall, $allIps);
            }
        }

        foreach ($starFilters as $filter) {
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            if ($this->isIpAllowed($request->getClientIp(), $ips)) {
                if ($filter->list_type === 'blacklist') {
                    abort($blockCode);
                } elseif ($filter->list_type === 'whitelist') {
                    return $next($request);
                }
            } elseif ($filter->list_type === 'whitelist') {
                abort($blockCode);
            }
        }

        foreach ($otherFilters as $filter) {
            $routes = $filter->routeList->pluck('route')->toArray();
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            if ($request->is($routes)) {
                if ($this->isIpAllowed($request->getClientIp(), $ips)) {
                    if ($filter->list_type === 'blacklist') {
                        abort($blockCode);
                    } elseif ($filter->list_type === 'whitelist') {
                        return $next($request);
                    }
                } elseif ($filter->list_type === 'whitelist') {
                    abort($blockCode);
                }
            }
        }

        return $next($request);
    }

    protected function isIpAllowed($ip, array $ipList): bool
    {
        foreach ($ipList as $allowedIp) {
            if (filter_var($allowedIp, FILTER_VALIDATE_IP)) {
                if ($ip === $allowedIp) {
                    return true;
                }
            } elseif ($this->isIpInRange($ip, $allowedIp)) {
                return true;
            }
        }
        return false;
    }

    protected function isIpInRange($ip, $range): bool
    {
        [$subnet, $mask] = explode('/', $range);
        $mask = 0xffffffff << (32 - $mask);
        return (ip2long($ip) & $mask) === (ip2long($subnet) & $mask);
    }

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

    protected function isBadBot($userAgent, $badBots): bool
    {
        if (!$userAgent) {
            return true;
        }

        $agent = strtolower($userAgent);
        foreach ($badBots as $badBot) {
            if (str_contains($agent, trim($badBot))) {
                return true;
            }
        }

        return false;
    }

    protected function checkRequestMethod(Request $request): bool
    {
        $validMethods = ['get', 'head', 'post', 'put'];
        return in_array(strtolower($request->method()), $validMethods);
    }

    protected function blockRequest($reason, Request $request, $firewall, $ipList): void
    {
        $ip = $request->ip();
        $whitelistedBotIPS = $this->whitelistedBotIPS();
        $ips = array_merge($ipList, $whitelistedBotIPS);

        if ($this->isIpAllowed($ip, $ips)) {
            return;
        }

        Log::warning(
            "[FIREWALL] Reason: {$reason} | IP: {$ip} | Agent: {$request->userAgent()} | URL: {$request->fullUrl()}"
        );

        $ipList = IpList::updateOrCreate(
            ['ip' => $ip, 'filter_id' => $firewall->blacklist_rule_id]
        );

        if ($ipList->wasRecentlyCreated) {
            FirewallLogs::create([
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'reason' => $reason,
                'request_data' => json_encode($request->all()),
                'ip_filter_id' => $firewall->blacklist_rule_id,
                'ip_list_id' => $ipList->id,
            ]);
        }
    }

    protected function whitelistedBotIPS()
    {
        if (Cache::has(config('cache.prefix') . "whitelisted_bot_ips")) {
            return Cache::get(config('cache.prefix') . "whitelisted_bot_ips");
        }

        $urls = [
            'googlebot' => 'https://developers.google.com/search/apis/ipranges/googlebot.json',
            'bingbot' => 'https://www.bing.com/toolbox/bingbot.json',
            'uptimerobot' => 'https://uptimerobot.com/inc/files/ips/IPv4andIPv6.txt',
            '360monitoring_v6' => 'https://app.360monitoring.com/whitelist?v6',
            '360monitoring_php' => 'https://app.360monitoring.com/whitelist.php',
        ];

        $ipList = [];

        foreach ($urls as $url) {
            $response = Http::get($url);
            if ($response->successful()) {
                if (str_ends_with($url, '.json')) {
                    $data = $response->json();
                    foreach ($data['prefixes'] as $prefix) {
                        if (isset($prefix['ipv4Prefix'])) {
                            $ipList[] = $prefix['ipv4Prefix'];
                        }
                        if (isset($prefix['ipv6Prefix'])) {
                            $ipList[] = $prefix['ipv6Prefix'];
                        }
                    }
                } elseif (str_ends_with($url, '.txt')) {
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

        Cache::put(config('cache.prefix') . "whitelisted_bot_ips", $ipList, now()->addDay());

        return $ipList;
    }
}
