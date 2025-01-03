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
use Illuminate\Support\Facades\Log;

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
     * @return Application|Response|ResponseFactory|JsonResponse
     */
    public function handle(Request $request, Closure $next): Application|Response|ResponseFactory|JsonResponse|RedirectResponse
    {
        $firewall = Firewall::first();

        if ($firewall->is_active) {
            if ($firewall->check_referer) {
                if (! $this->checkReferer($request)) {
                    $this->blockRequest('Referer Blocked', $request, $firewall);
                }
            }

            if ($firewall->check_bots) {
                if ($this->isBadBot($request->userAgent())) {
                    $this->blockRequest('Bot Blocked', $request, $firewall);
                }
            }

            if ($firewall->check_request_method) {
                if (! $this->checkRequestMethod($request)) {
                    $this->blockRequest('Invalid Request Method', $request, $firewall);
                }
            }

            if ($firewall->check_dos) {
                if ($this->checkDosAttack($request)) {
                    $this->blockRequest('DOS Attack', $request, $firewall);
                }
            }

            if ($firewall->check_union_sql) {
                if ($this->checkUnionSql($request)) {
                    $this->blockRequest('Union SQL Attack', $request, $firewall);
                }
            }

            if ($firewall->check_click_attack) {
                if ($this->checkClickAttack($request)) {
                    $this->blockRequest('Click Attack', $request, $firewall);
                }
            }

            if ($firewall->check_xss) {
                if ($this->checkXss($request)) {
                    $this->blockRequest('XSS Attack', $request, $firewall);
                }
            }

            if ($firewall->check_cookie_injection) {
                if ($this->checkCookieInjection($request)) {
                    $this->blockRequest('Cookie Injection', $request, $firewall);
                }
            }
        }



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


    protected function isBadBot($userAgent): bool
    {
        if (! $userAgent) {
            return true;
        }

        $badBots = [
            'apache', 'bandit', 'blackwidow', 'bot', 'crawler', 'disco', 'dragonfly', 'grabber',
            'harvest', 'httpconnect', 'httrack', 'larbin', 'nikto', 'wget', 'libwww', 'offline',
            'searchspider', 'sucker', 'turnitinbot', 'zeus',
        ];

        $agent = strtolower($userAgent);
        foreach ($badBots as $badBot) {
            if (str_contains($agent, $badBot)) {
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

    protected function blockRequest($reason, Request $request, $firewall): void
    {
        Log::warning(
            "[FIREWALL] Reason: {$reason} | IP: {$request->ip()} | Agent: {$request->userAgent()} | URL: {$request->fullUrl()}"
        );

        $ipList = IpList::updateOrCreate(
            [
                'ip'         => $request->ip(),
                'filter_id'  => $firewall->ip_filter_id,
            ]
        );

        if ($ipList->wasRecentlyCreated) {
            FirewallLogs::create([
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'url'         => $request->fullUrl(),
                'reason'      => $reason,
                'request_data'=> json_encode($request->all()),
                'ip_filter_id'=> $firewall->ip_filter_id,
                'ip_list_id'  => $ipList->id,
            ]);
        }
    }
}

