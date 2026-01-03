<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class RouteRedirects extends Model
{
    use ModelLogger;

    protected $table = 'route_redirects';

    protected $fillable = [
        'old_url',
        'new_url',
        'redirect_code',
    ];
}
