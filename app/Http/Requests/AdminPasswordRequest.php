<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Yoneticinin BASKA bir hesabin parolasini degistirmesi.
 *
 * `PasswordRequest`ten farki: eski parola istenmez (yonetici onu bilmez), ama
 * parola politikasi (zorunlu + onay + min:12) aynen uygulanir. Uc daha once
 * hic dogrulama yapmiyordu: bos gonderim `Hash::make('')` calistirip hesabi
 * sessizce kilitliyor, arayuzun gonderdigi `password_confirmation` ise hic
 * kontrol edilmiyordu.
 */
class AdminPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('admin', auth()->user());
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|confirmed|min:12',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required' => __('profile.password.required'),
            'password.confirmed' => __('profile.password.confirmed'),
            'password.min' => __('profile.password.min'),
        ];
    }
}
