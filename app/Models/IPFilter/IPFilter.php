<?php

namespace App\Models\IPFilter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IPFilter extends Model
{
    protected $table = 'ip_filters';

    protected $fillable = [
        'name',
        'list_type',
        'is_active',
        'route_type',
        'code'
    ];

    public function ipList(): HasMany
    {
        return $this->hasMany(IPList::class, 'filter_id');
    }

    public function routeList(): HasMany
    {
        return $this->hasMany(RouteList::class, 'filter_id');
    }
}
