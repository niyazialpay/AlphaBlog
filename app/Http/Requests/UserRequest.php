<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'skills' => 'nullable|string|max:255',
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
            'name.required' => __('profile.name.required'),
            'name.string' => __('profile.name.string'),
            'name.max' => __('profile.name.max'),
            'surname.required' => __('profile.surname.required'),
            'surname.string' => __('profile.surname.string'),
            'surname.max' => __('profile.surname.max'),
            'nickname.required' => __('profile.nickname.required'),
            'nickname.string' => __('profile.nickname.string'),
            'nickname.max' => __('profile.nickname.max'),
            'nickname.unique' => __('profile.nickname.unique'),
            'location.string' => __('profile.location.string'),
            'location.max' => __('profile.location.max'),
            'about.string' => __('profile.about.string'),
            'about.max' => __('profile.about.max'),
            'website.string' => __('profile.website.string'),
            'website.max' => __('profile.website.max'),
            'education.string' => __('profile.education.string'),
            'education.max' => __('profile.education.max'),
            'job_title.string' => __('profile.job_title.string'),
            'job_title.max' => __('profile.job_title.max'),
            'skills.string' => __('profile.skills.string'),
            'skills.max' => __('profile.skills.max'),
        ];
    }
}
