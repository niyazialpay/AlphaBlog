<?php

namespace App\Models\Firewall;

use Illuminate\Database\Eloquent\Model;

class FirewallLogs extends Model
{
    protected $table = 'firewall_logs';

    protected $fillable = [
        'ip',
        'request_uri',
        'request_method',
        'request_data',
        'response_data',
        'response_code',
        'response_message',
        'created_at',
    ];
}
