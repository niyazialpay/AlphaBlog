<?php

namespace App\Http\Requests\Post;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
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
        $predefined_slugs = [];
        foreach(app('languages') as $language){
            foreach (Lang::get('routes', locale: $language->code) as $item) {
                $predefined_slugs[] = $item;
            }
        }
        if($this->id){
            $slug_unique = Rule::unique('posts', 'slug')
                ->where('language_code', $this->input('language_code'))
                ->whereNot('_id', $this->input('id'));
        }
        else{
            $slug_unique = Rule::unique('posts', 'slug')
                ->where('language_code', $this->input('language_code'));
        }
        return [
            'title' => ['required', 'string'],
            'slug' => [
                'nullable',
                'string',
                $slug_unique,
                Rule::notIn($predefined_slugs)
            ],
            'content' => ['string', 'nullable'],
            'image' => 'nullable|file|image|max:51200|mimes:jpeg,png,jpg,gif,svg,webp',
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'category_id' => ['required_if:post_type,post', 'array'],
            'category_id.*' => ['required_if:post_type,post', 'string', 'exists:categories,_id'],
            'is_published' => ['required', 'boolean'],
            'user_id' => ['required', 'string', 'exists:users,_id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => __('post.request.title_required'),
            'slug.unique' => __('post.request.slug_unique'),
            'content.required' => __('post.request.content_required'),
            'meta_description.string' => __('post.request.meta_description_string'),
            'meta_keywords.string' => __('post.request.meta_keywords_string'),
            'category_id.required' => __('post.request.category_id_required'),
            'category_id.array' => __('post.request.category_id_array'),
            'category_id.*.required' => __('post.request.category_id_required'),
            'category_id.*.exists' => __('post.request.category_id_exists'),
            'category_id.required_if' => __('post.request.category_id_required'),
            'category_id.*.required_if' => __('post.request.category_id_required'),
            'user_id.required' => __('post.request.user_id_required'),
            'user_id.exists' => __('post.request.user_id_exists'),
            'is_published.required' => __('post.request.is_published_required'),
            'is_published.boolean' => __('post.request.is_published_boolean'),
            'image.file' => __('post.request.image_file'),
            'image.image' => __('post.request.image_image'),
            'image.max' => __('post.request.image_max'),
            'image.mimes' => __('post.request.image_mimes'),
            'slug.not_in' => __('post.request.slug_unique'),
        ];
    }
}
