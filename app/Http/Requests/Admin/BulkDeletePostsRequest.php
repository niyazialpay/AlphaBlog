<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeletePostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'post_ids' => ['required', 'array', 'max:500'],
            'post_ids.*' => ['integer', 'exists:posts,id'],
        ];
    }
}
