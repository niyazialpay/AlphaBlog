<?php

namespace App\Http\Requests\Menu;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'menu_position' => 'required|string|max:255|in:header,footer',
            'language' => 'required|string|max:2',
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
            'title.required' => __('menu.title_required'),
            'title.string' => __('menu.title_string'),
            'title.max' => __('menu.title_max'),
            'menu_position.required' => __('menu.menu_position_required'),
            'menu_position.string' => __('menu.menu_position_string'),
            'menu_position.max' => __('menu.menu_position_max'),
            'menu_position.in' => __('menu.menu_position_in'),
            'language.required' => __('menu.language_required'),
            'language.string' => __('menu.language_string'),
            'language.max' => __('menu.language_max'),
        ];
    }
}
