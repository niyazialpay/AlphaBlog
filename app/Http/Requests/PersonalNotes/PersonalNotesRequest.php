<?php

namespace App\Http\Requests\PersonalNotes;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PersonalNotesRequest extends FormRequest
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
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'category_id' => 'required|string|exists:personal_note_categories,_id'
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => __('notes.title_required'),
            'title.string' => __('notes.title_string'),
            'title.max' => __('notes.title_max'),
            'content.required' => __('notes.content_required'),
            'content.string' => __('notes.content_string'),
            'category_id.required' => __('notes.category_id_required'),
            'category_id.string' => __('notes.category_id_string'),
            'category_id.exists' => __('notes.category_id_exists'),
        ];
    }
}
