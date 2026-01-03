<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class AdminOneSignal extends Model
{
    use ModelLogger;

    public $timestamps = false;

    protected $table = 'admin_one_signal';

    protected $fillable = [
        'onesignal',
    ];
}
