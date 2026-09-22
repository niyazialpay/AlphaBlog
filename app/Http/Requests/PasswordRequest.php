<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->can('own', auth()->user()) || auth()->user()->can('admin', auth()->user()));
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|confirmed|min:12',
            'old_password' => 'required',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => __('profile.password.required'),
            'password.confirmed' => __('profile.password.confirmed'),
            'password.min' => __('profile.password.min'),
            'old_password.required' => __('profile.old_password.required'),
        ];
    }
}
