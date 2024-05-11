<?php

namespace App\Http\Controllers\WebAuthn;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

use Laragear\WebAuthn\Models\WebAuthnCredential;
use function response;

class WebAuthnRegisterController
{
    /**
     * Returns a challenge to be verified by the user device.
     *
     * @param AttestationRequest $request
     * @return Responsable
     */
    public function options(AttestationRequest $request): Responsable
    {
        return $request
            ->fastRegistration()
            ->toCreate();
    }

    /**
     * Registers a device for further WebAuthn authentication.
     *
     * @param AttestedRequest $request
     * @return Response
     */
    public function register(AttestedRequest $request): Response
    {
        $request->save();
        WebAuthnCredential::latest()->first()->update(['device_name' => auth()->user()->nickname.'-'.rand().time()]);
        User::where('id', auth()->user()->id)->update(['webauthn' => true]);
        return response()->noContent();
    }
}
