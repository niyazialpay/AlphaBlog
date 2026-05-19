<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'post_ids' => ['required', 'array'],
            'post_ids.*' => ['integer', 'exists:posts,id'],
        ];
    }
}
