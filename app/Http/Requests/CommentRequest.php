<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string'],
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => __('comments.request.comment_required'),
            'post_id.required' => __('comments.request.post_id_required'),
            'post_id.exists' => __('comments.request.post_id_exists'),
            'name.required' => __('comments.request.name_required'),
            'email.required' => __('comments.request.email_required'),
            'email.email' => __('comments.request.email_email'),
        ];
    }
}
