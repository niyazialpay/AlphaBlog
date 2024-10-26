<?php

namespace App\Actions;

use App\Models\UserSessions;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class SessionAction
{
    /**
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public static function sessionUpdate($request): void
    {
        $ip = $request->ip();
        $reader = new Reader(storage_path('GeoLite2-City.mmdb'));

        $record = $reader->city($ip);

        UserSessions::updateOrInsert([
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
        ], [
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'country_code' => $record->country->isoCode,
            'country_name' => $record->country->name,
            'region_name' => $record->mostSpecificSubdivision->name,
            'city_name' => $record->city->name,
            'zip_code' => $record->postal->code,
        ]);
    }
}
