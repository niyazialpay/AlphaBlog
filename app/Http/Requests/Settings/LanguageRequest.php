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
        if ($this->id) {
            $code_unique = Rule::unique('languages', 'code')
                ->where('code', $this->input('code'))
                ->whereNot('_id', $this->input('id'));

            $name_unique = Rule::unique('languages', 'name')
                ->where('name', $this->input('name'))
                ->whereNot('_id', $this->input('id'));
        } else {
            $code_unique = Rule::unique('languages', 'code')
                ->where('code', $this->input('code'));

            $name_unique = Rule::unique('languages', 'name')
                ->where('name', $this->input('name'));
        }

        return [
            'name' => ['required', 'string', $name_unique],
            'code' => ['required', 'string', $code_unique],
            'flag' => ['required', 'string'],
            'default' => ['integer', 'in:0,1'],
            'status' => ['integer', 'in:0,1'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('language.name_required'),
            'name.string' => __('language.name_string'),
            'name.unique' => __('language.name_unique'),
            'code.required' => __('language.code_required'),
            'code.string' => __('language.code_string'),
            'code.unique' => __('language.code_unique'),
            'flag.required' => __('language.flag_required'),
            'flag.string' => __('language.flag_string'),
            'default.integer' => __('language.default_integer'),
            'default.in' => __('language.default_in'),
            'status.integer' => __('language.status_integer'),
            'status.in' => __('language.status_in'),
        ];
    }
}
