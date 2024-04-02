<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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
            $username_unique = Rule::unique('users', 'username')
                ->whereNot('_id', $this->input('id'));
            $nickname_unique = Rule::unique('users', 'nickname')
                ->whereNot('_id', $this->input('id'));
            $email_unique = Rule::unique('users', 'email')
                ->whereNot('_id', $this->input('id'));
        } else {
            $username_unique = Rule::unique('users', 'username');
            $nickname_unique = Rule::unique('users', 'nickname');
            $email_unique = Rule::unique('users', 'email');
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'surname' => [
                'required',
                'string',
                'max:255',
            ],
            'nickname' => [
                'required',
                'string',
                'max:255',
                $nickname_unique,
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                $email_unique,
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                $username_unique,
            ],
            'password' => [
                'required',
                'confirmed',
                'min:12',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('profile.name.required'),
            'name.string' => __('profile.name.string'),
            'name.max' => __('profile.name.max'),
            'surname.required' => __('profile.surname.required'),
            'surname.string' => __('profile.surname.string'),
            'surname.max' => __('profile.surname.max'),
            'nickname.required' => __('profile.nickname.required'),
            'nickname.string' => __('profile.nickname.string'),
            'nickname.max' => __('profile.nickname.max'),
            'nickname.unique' => __('profile.nickname.unique'),
            'email.required' => __('profile.email.required'),
            'email.string' => __('profile.email.string'),
            'email.email' => __('profile.email.email'),
            'email.max' => __('profile.email.max'),
            'email.unique' => __('profile.email.unique'),
            'password.required' => __('profile.password.required'),
            'password.confirmed' => __('profile.password.confirmed'),
            'password.min' => __('profile.password.min'),
        ];
    }
}
