<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrustedBots
{
    public static function ipRanges(): array
    {
        return Cache::rememberForever(self::cacheKey(), function () {
            return self::fetchRanges();
        });
    }

    public static function refresh(): array
    {
        $ranges = self::fetchRanges();
        Cache::forever(self::cacheKey(), $ranges);

        return $ranges;
    }

    public static function userAgentKeywords(): array
    {
        return [
            'googlebot',
            'bingbot',
            'msnbot',
            'yandexbot',
            'duckduckbot',
            'baiduspider',
            'facebookexternalhit',
            'adsbot-google',
            'applebot',
            'slurp',
            'sogou',
        ];
    }

    public static function isTrustedAgent(?string $agent): bool
    {
        if (! $agent) {
            return false;
        }

        $agent = strtolower($agent);
        foreach (self::userAgentKeywords() as $keyword) {
            if (str_contains($agent, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public static function filterOutTrusted(array $ips): array
    {
        $trusted = self::ipRanges();
        $clean = [];
        $skipped = [];

        foreach ($ips as $ip) {
            $normalized = trim($ip);
            if ($normalized === '') {
                continue;
            }

            if (IpRangeMatcher::matches($normalized, $trusted)) {
                $skipped[] = $normalized;
                continue;
            }

            $clean[] = $normalized;
        }

        return [$clean, $skipped];
    }

    public static function isTrustedIp(string $ip): bool
    {
        return IpRangeMatcher::matches($ip, self::ipRanges());
    }

    protected static function duckDuckGoIps(): array
    {
        return [
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
        ];
    }

    protected static function cacheKey(): string
    {
        return config('cache.prefix').'trusted_bot_ips';
    }

    protected static function fetchRanges(): array
    {
        $urls = [
            'https://developers.google.com/search/apis/ipranges/googlebot.json',
            'https://www.bing.com/toolbox/bingbot.json',
            'https://uptimerobot.com/inc/files/ips/IPv4andIPv6.txt',
            'https://app.360monitoring.com/whitelist?v6',
            'https://app.360monitoring.com/whitelist.php',
        ];

        $ipList = [];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(10)->get($url);
            } catch (\Throwable $e) {
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            if (str_ends_with($url, '.json')) {
                $data = $response->json();
                foreach ($data['prefixes'] ?? [] as $prefix) {
                    if (! empty($prefix['ipv4Prefix'])) {
                        $ipList[] = $prefix['ipv4Prefix'];
                    }
                    if (! empty($prefix['ipv6Prefix'])) {
                        $ipList[] = $prefix['ipv6Prefix'];
                    }
                }

                continue;
            }

            $lines = preg_split('/\r?\n/', $response->body(), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && ! str_starts_with($line, '#')) {
                    $ipList[] = $line;
                }
            }
        }

        $ipList = array_merge($ipList, self::duckDuckGoIps());

        return array_values(array_unique($ipList));
    }
}
