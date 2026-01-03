<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\IpUtils;

class IpRangeMatcher
{
    public static function matches(string $clientIp, array $ranges): bool
    {
        foreach ($ranges as $range) {
            $normalized = self::normalizeRange($range);
            if ($normalized && IpUtils::checkIp($clientIp, $normalized)) {
                return true;
            }
        }

        return false;
    }

    public static function normalizeRange(string $range): ?string
    {
        $range = trim($range);
        if ($range === '') {
            return null;
        }

        if (str_contains($range, '/')) {
            return $range;
        }

        if (filter_var($range, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $range.'/128';
        }

        if (filter_var($range, FILTER_VALIDATE_IP)) {
            return $range.'/32';
        }

        return null;
    }
}
