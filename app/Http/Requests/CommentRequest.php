<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
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
        return [
            'comment' => ['required', 'string'],
            'post_id' => ['required', 'string', 'exists:posts,_id'],
            'user_id' => ['required_if:name,null', 'string', 'exists:users,_id', 'nullable'],
            'is_approved' => ['boolean'],
            'name'  => ['required_if:user_id,null', 'string', 'nullable'],
            'email' => ['required_if:user_id,null', 'string', 'email', 'nullable'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'comment.required' => __('comments.request.comment_required'),
            'post_id.required' => __('comments.request.post_id_required'),
            'post_id.exists' => __('comments.request.post_id_exists'),
            'user_id.required_if' => __('comments.request.user_id_required_if'),
            'user_id.exists' => __('comments.request.user_id_exists'),
            'name.required_if' => __('comments.request.name_required_if'),
            'email.required_if' => __('comments.request.email_required_if'),
            'email.email' => __('comments.request.email_email'),
        ];
    }
}
