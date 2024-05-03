<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->role == 'owner' || auth()->user()->role == 'admin' || auth()->user()->role == 'editor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        if ($this->input('id')) {
            $slug_unique = Rule::unique('categories', 'slug')
                ->where('language', $this->input('language'))
                ->whereNot('id', $this->input('id'));
        } else {
            $slug_unique = Rule::unique('categories', 'slug')
                ->where('language', $this->input('language'));
        }

        return [
            'name' => ['required', 'string'],
            'slug' => [
                'string',
                $slug_unique,
            ],
            'image' => 'nullable|file|image|max:51200|mimes:jpeg,png,jpg,gif,svg,webp',
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('categories.request.name_required'),
            'slug.required' => __('categories.request.slug_required'),
            'slug.unique' => __('categories.request.slug_unique'),
            'meta_description.string' => __('categories.request.meta_description_string'),
            'meta_keywords.string' => __('categories.request.meta_keywords_string'),
        ];
    }
}
