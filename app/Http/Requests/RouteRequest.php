<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RouteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'old_url' => 'required|string|unique:routes,old_url,'.$this->route_id.',_id',
            'new_url' => 'required|string',
            'redirect_code' => 'required|integer',
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
            'old_url.required' => __('routes.old_url_required'),
            'old_url.string' => __('routes.old_url_string'),
            'old_url.unique' => __('routes.old_url_unique'),
            'new_url.required' => __('routes.new_url_required'),
            'new_url.string' => __('routes.new_url_string'),
            'redirect_code.required' => __('routes.redirect_code_required'),
            'redirect_code.integer' => __('routes.redirect_code_integer'),
        ];
    }
}
