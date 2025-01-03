<?php

namespace App\Models\Firewall;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    protected $table = 'firewall';

    protected $fillable = [
        'is_active',
        'ip_filter_id',
        'check_referer',
        'check_bots',
        'check_request_method',
        'check_dos',
        'check_union_sql',
        'check_click_attack',
        'check_xss',
        'check_cookie_injection',
    ];
}
