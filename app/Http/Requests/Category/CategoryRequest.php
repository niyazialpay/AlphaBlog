<?php

namespace App\Http\Requests\Category;

use App\Models\Post\Categories;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (auth()->user()->role == 'owner' || auth()->user()->role == 'admin' || auth()->user()->role == 'editor');
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Categories ? $category->id : $category;

        return [
            'name' => ['required', 'string'],
            'slug' => [
                'string',
                Rule::unique('categories', 'slug')
                    ->where('language', $this->input('language'))
                    ->ignore($categoryId),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'image' => 'nullable|file|image|max:51200|mimes:jpeg,png,jpg,gif,webp',
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
        ];
    }

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
