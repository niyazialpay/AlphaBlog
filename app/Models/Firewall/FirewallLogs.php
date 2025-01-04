<?php

namespace App\Models\Firewall;

use Illuminate\Database\Eloquent\Model;

class FirewallLogs extends Model
{
    protected $table = 'firewall_logs';

    protected $fillable = [
        'ip',
        'url',
        'user_agent',
        'reason',
        'request_data',
        'ip_filter_id',
        'ip_list_id',
    ];
}
