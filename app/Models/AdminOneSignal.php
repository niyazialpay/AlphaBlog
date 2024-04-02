<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AdminOneSignal extends Model
{
    public $timestamps = false;

    protected $collection = 'admin_one_signal';

    protected $fillable = [
        'onesignal',
    ];
}
