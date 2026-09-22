<?php

namespace App\Http\Requests\IPFilter;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ToogleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->role == 'owner' || auth()->user()->role == 'admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:ip_filters,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => __('ip_filter.id_required'),
            'id.integer' => __('ip_filter.id_integer'),
            'id.exists' => __('ip_filter.id_exists'),
        ];
    }
}
