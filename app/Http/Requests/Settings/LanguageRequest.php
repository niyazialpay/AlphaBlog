<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('admin', auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        if($this->id){
            $code_unique = Rule::unique('languages', 'code')
                ->where('code', $this->input('code'))
                ->whereNot('_id', $this->input('id'));
        }
        else{
            $code_unique = Rule::unique('languages', 'code')
                ->where('code', $this->input('code'));
        }
        return [
            'name' => ['required', 'string'],
            'code' => ['required', 'string', $code_unique],
            'flag' => ['required', 'string'],
            'default' => ['boolean'],
            'status' => ['required', 'boolean'],
        ];
    }
}
