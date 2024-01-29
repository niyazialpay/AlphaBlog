<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;


class RouteRedirects extends Model
{
    protected $fillable = [
        'old_url',
        'new_url',
        'redirect_code'
    ];

}
