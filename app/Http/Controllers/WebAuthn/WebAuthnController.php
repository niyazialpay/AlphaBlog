<?php

namespace App\Http\Controllers\WebAuthn;

use App\Http\Controllers\Controller;
use App\Models\WebAuthnCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebAuthnController extends Controller
{
    public function WebAuthnList()
    {
        return response()->json(auth()->user()->WebAuthn);
    }

    public function delete(Request $request, WebAuthnCredential $webauthn): JsonResponse
    {
        return (new \App\Action\WebAuthnAction())->delete($request, $webauthn, auth()->user());
    }

    public function rename(Request $request, WebAuthnCredential $webauthn): JsonResponse
    {
        return (new \App\Action\WebAuthnAction())->rename($request, $webauthn, auth()->user());
    }
}
