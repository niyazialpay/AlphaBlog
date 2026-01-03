<?php

namespace App\Http\Requests\IPFilter;

use Illuminate\Foundation\Http\FormRequest;

class ToogleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->role == 'owner' || auth()->user()->role == 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:ip_filters,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
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
