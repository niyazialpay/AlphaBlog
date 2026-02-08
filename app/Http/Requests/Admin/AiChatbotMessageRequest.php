<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AiChatbotMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:10000'],
            'provider' => ['required', 'string', 'max:64'],
            'model' => ['required', 'string', 'max:128'],
            'conversation_id' => ['nullable', 'string', 'size:36'],
        ];
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function messages(): array
    {
        return [
            'message.required' => __('chatbot.validation.message_required'),
            'message.max' => __('chatbot.validation.message_max'),
            'provider.required' => __('chatbot.validation.provider_required'),
            'model.required' => __('chatbot.validation.model_required'),
            'conversation_id.size' => __('chatbot.validation.conversation_id_size'),
        ];
    }
}
