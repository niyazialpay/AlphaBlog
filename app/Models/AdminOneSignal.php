<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOneSignal extends Model
{
    public $timestamps = false;

    protected $table = 'admin_one_signal';

    protected $fillable = [
        'onesignal',
    ];
}
