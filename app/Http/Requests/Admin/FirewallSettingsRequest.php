<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FirewallSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'blacklist_rule_id' => ['required', 'integer', Rule::exists('ip_filters', 'id')->where('list_type', 'blacklist')],
            'whitelist_rule_id' => ['nullable', 'integer', Rule::exists('ip_filters', 'id')->where('list_type', 'whitelist'), 'different:blacklist_rule_id'],
            'check_referer' => ['required', 'boolean'],
            'check_bots' => ['required', 'boolean'],
            'check_request_method' => ['required', 'boolean'],
            'check_dos' => ['required', 'boolean'],
            'check_union_sql' => ['required', 'boolean'],
            'check_click_attack' => ['required', 'boolean'],
            'check_xss' => ['required', 'boolean'],
            'check_cookie_injection' => ['required', 'boolean'],
            'bad_bots' => ['nullable', 'string', 'max:65535'],
            'ai_review_enabled' => ['required', 'boolean'],
            'ai_enforcement_enabled' => ['required', 'boolean'],
            'ai_provider' => ['nullable', 'string', 'max:64'],
            'ai_model' => ['nullable', 'string', 'max:128'],
            'ai_confidence_threshold' => ['required', 'integer', 'between:1,100'],
            'ai_sample_rate' => ['required', 'integer', 'between:0,100'],
            'ai_cache_ttl_seconds' => ['required', 'integer', 'between:60,86400'],
            'ai_timeout_seconds' => ['required', 'integer', 'between:1,30'],
            'ai_max_payload_chars' => ['required', 'integer', 'between:500,12000'],
        ];
    }
}
