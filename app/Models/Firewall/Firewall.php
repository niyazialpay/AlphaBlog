<?php

namespace App\Models\Firewall;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    protected $table = 'firewall';

    protected $fillable = [
        'is_active',
        'whitelist_rule_id',
        'blacklist_rule_id',
        'check_referer',
        'check_bots',
        'check_request_method',
        'check_dos',
        'check_union_sql',
        'check_click_attack',
        'check_xss',
        'check_cookie_injection',
        'bad_bots',
    ];
}
