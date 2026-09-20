<?php

namespace App\Http\Requests\Category;

use App\Models\Post\Categories;
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
        /*
         * Duzenleme modu govdeden (`id`) DEGIL, route parametresinden okunur —
         * Vue formu `id` alanini hic gondermiyor (route-model binding zaten
         * kategoriyi URL'den cozuyor). Gövdeye bakmak, düzenleme sırasında
         * unique kuralının kendi kaydını asla dışlayamamasına ve doğrulamanın
         * komple reddedilmesine yol açıyordu.
         */
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
