<?php

namespace App\Support;

use App\Models\IPFilter\IPFilter;
use Illuminate\Support\Facades\Cache;

class IPFilterCache
{
    public static function get(): array
    {
        $cached = Cache::get(self::cacheKey());

        if (self::isCompiledStructure($cached)) {
            return $cached;
        }

        $compiled = self::compile();
        Cache::forever(self::cacheKey(), $compiled);

        return $compiled;
    }

    public static function refresh(): array
    {
        $compiled = self::compile();
        Cache::forever(self::cacheKey(), $compiled);

        return $compiled;
    }

    protected static function compile(): array
    {
        $filters = IPFilter::with([
                'ipList' => function ($query) {
                    $query->select('filter_id', 'ip');
                },
                'routeList' => function ($query) {
                    $query->select('filter_id', 'route');
                },
            ])
            ->where('is_active', true)
            ->get();

        $compiled = [
            'global' => [],
            'scoped' => [],
            'all_ips' => [],
        ];

        foreach ($filters as $filter) {
            $item = [
                'id' => $filter->id,
                'list_type' => $filter->list_type,
                'code' => $filter->code ?? 403,
                'ips' => $filter->ipList->pluck('ip')->filter()->unique()->values()->all(),
                'routes' => $filter->routeList->pluck('route')->filter()->unique()->values()->all(),
            ];

            $compiled['all_ips'] = array_merge($compiled['all_ips'], $item['ips']);

            if (in_array('*', $item['routes'], true)) {
                $compiled['global'][] = $item;
            } else {
                $compiled['scoped'][] = $item;
            }
        }

        $compiled['all_ips'] = array_values(array_unique($compiled['all_ips']));

        return $compiled;
    }

    protected static function cacheKey(): string
    {
        return config('cache.prefix').'ip_filter';
    }

    protected static function isCompiledStructure(mixed $value): bool
    {
        return is_array($value) && array_key_exists('global', $value) && array_key_exists('scoped', $value);
    }
}
