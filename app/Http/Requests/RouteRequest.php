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
            'old_url' => 'required|string|unique:route_redirects,old_url,'.$this->route_id.',id',
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
            'old_url.required' => __('redirects.old_url_required'),
            'old_url.string' => __('redirects.old_url_string'),
            'old_url.unique' => __('redirects.old_url_unique'),
            'new_url.required' => __('redirects.new_url_required'),
            'new_url.string' => __('redirects.new_url_string'),
            'redirect_code.required' => __('redirects.redirect_code_required'),
            'redirect_code.integer' => __('redirects.redirect_code_integer'),
        ];
    }
}
