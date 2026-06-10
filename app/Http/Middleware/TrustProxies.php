<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * SECURITY: previously '*' (trust ALL proxies), which let any direct-to-origin
     * client forge X-Forwarded-For and defeat every IP-based control (firewall
     * allow/deny, trusted-bot bypass) and X-Forwarded-Host (host poisoning).
     *
     * The site sits behind Cloudflare, so we trust only Cloudflare's published
     * edge ranges. Keep this list current (https://www.cloudflare.com/ips/) and
     * ensure the origin server is firewalled to accept traffic only from these
     * ranges, otherwise an attacker can still hit the origin directly.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = [
        // Cloudflare IPv4
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        // Cloudflare IPv6
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * SECURITY: X-Forwarded-Host is intentionally NOT trusted. Cloudflare does not
     * forward it, and trusting it enables host-header poisoning on direct origin
     * hits (password-reset link poisoning, cache poisoning).
     *
     * @var int
     */
    protected $headers =
        RequestAlias::HEADER_X_FORWARDED_FOR |
        RequestAlias::HEADER_X_FORWARDED_PORT |
        RequestAlias::HEADER_X_FORWARDED_PROTO |
        RequestAlias::HEADER_X_FORWARDED_AWS_ELB;
}
