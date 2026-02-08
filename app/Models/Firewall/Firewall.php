<?php

namespace App\Models\Firewall;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    use ModelLogger;

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
        'ai_review_enabled',
        'ai_enforcement_enabled',
        'ai_provider',
        'ai_model',
        'ai_confidence_threshold',
        'ai_sample_rate',
        'ai_cache_ttl_seconds',
        'ai_timeout_seconds',
        'ai_max_payload_chars',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'check_referer' => 'boolean',
            'check_bots' => 'boolean',
            'check_request_method' => 'boolean',
            'check_dos' => 'boolean',
            'check_union_sql' => 'boolean',
            'check_click_attack' => 'boolean',
            'check_xss' => 'boolean',
            'check_cookie_injection' => 'boolean',
            'ai_review_enabled' => 'boolean',
            'ai_enforcement_enabled' => 'boolean',
            'ai_confidence_threshold' => 'integer',
            'ai_sample_rate' => 'integer',
            'ai_cache_ttl_seconds' => 'integer',
            'ai_timeout_seconds' => 'integer',
            'ai_max_payload_chars' => 'integer',
        ];
    }
}
