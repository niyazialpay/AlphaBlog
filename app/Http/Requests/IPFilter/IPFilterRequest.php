<?php

namespace App\Http\Requests\IPFilter;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IPFilterRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'ip_range' => 'required',
            'routes' => 'required',
            'list_type' => 'required|in:blacklist,whitelist',
            'is_active' => 'required|in:0,1',
            'route_type' => 'required|in:select,manuel',
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
            'name.required' => __('ip_filter.name_required'),
            'ip_range.required' => __('ip_filter.ip_range_required'),
            'routes.required' => __('ip_filter.routes_required'),
            'list_type.required' => __('ip_filter.list_type_required'),
            'list_type.in' => __('ip_filter.list_type_in'),
            'status.required' => __('ip_filter.status_required'),
            'is_active.in' => __('ip_filter.status_in'),
            'route_type.required' => __('ip_filter.route_type_required'),
            'route_type.in' => __('ip_filter.route_type_in'),
        ];
    }
}
