<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class IPFilter extends Model
{
    protected $collection = 'ip_filters';

    protected $fillable = [
        'name',
        'ip_range',
        'routes',
        'list_type',
        'is_active',
        'route_type',
    ];
}
