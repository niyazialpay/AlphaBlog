<?php

namespace App\Http\Controllers\WebAuthn;

use App\Models\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

use function response;

class WebAuthnLoginController
{
    /**
     * Returns the challenge to assertion.
     *
     * @throws BindingResolutionException
     */
    public function options(AssertionRequest $request): Responsable
    {
        return $request->toVerify($request->validate(['username' => 'sometimes|string']));
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request): Response
    {
        $status = $request->login(remember:true) ? 204 : 422;
        if ($status === 204) {
            session()->put('otp', true);
        }

        return response()->noContent($status);
    }
}
