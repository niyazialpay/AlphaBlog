<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('admin', auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_logo' => 'nullable|file|image|max:4096|mimes:jpeg,png,jpg,gif,svg,webp',
            'site_favicon' => 'nullable|file|image|max:4096|mimes:jpeg,png,jpg,gif,svg,webp,ico',
            'app_icon' => 'nullable|file|image|max:4096|mimes:jpeg,png,jpg,gif,svg,webp,ico',
            'contact_email' => 'nullable|email',
            'sharethis' => 'nullable|string|max:255'
        ];
    }
}
