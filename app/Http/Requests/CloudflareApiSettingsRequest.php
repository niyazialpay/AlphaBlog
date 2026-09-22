<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CloudflareApiSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role == 'owner' && (auth()->user()->otp === 1 || auth()->user()->webauthn === 1);
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'cf_email' => ['required', 'email'],
            'cf_key' => ['nullable', 'string'],
            'cf_domain' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cf_email.required' => 'Email is required',
            'cf_email.email' => 'Email must be a valid email address',
            'cf_key.required' => 'API Key is required',
            'cf_domain.required' => 'Domain is required',
        ];
    }
}
