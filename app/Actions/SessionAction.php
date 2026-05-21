<?php

namespace App\Actions;

use App\Models\UserSessions;
use GeoIp2\Database\Reader;
use Throwable;

class SessionAction
{
    public static function sessionUpdate($request): void
    {
        $ip = $request->ip();

        [$country_code, $country_name, $region_name, $city_name, $zip_code] = self::resolveLocation($ip);

        UserSessions::updateOrInsert([
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
        ], [
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'country_code' => $country_code,
            'country_name' => $country_name,
            'region_name' => $region_name,
            'city_name' => $city_name,
            'zip_code' => $zip_code,
        ]);
    }

    /**
     * Best-effort GeoIP lookup. This must never throw: a missing or unreadable
     * GeoLite2 database is non-critical and may not block authentication.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string, 4: ?string}
     */
    private static function resolveLocation(?string $ip): array
    {
        $databasePath = storage_path('GeoLite2-City.mmdb');

        if (! $ip || ! is_readable($databasePath)) {
            return [null, null, null, null, null];
        }

        try {
            $record = (new Reader($databasePath))->city($ip);

            return [
                $record->country->isoCode,
                $record->country->name,
                $record->mostSpecificSubdivision->name,
                $record->city->name,
                $record->postal->code,
            ];
        } catch (Throwable $e) {
            return [null, null, null, null, null];
        }
    }
}
