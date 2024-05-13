<?php

namespace App\Action;

use App\Models\User;
use App\Models\WebAuthnCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebAuthnAction
{
    public function delete(Request $request, WebAuthnCredential $webauthn, $user): JsonResponse
    {
        $webauthn = $webauthn::where('authenticatable_id', $user->id)->where('id', $request->post('webauthn_id'))->first();
        $webauthn->delete();

        if ($webauthn::where('authenticatable_id', $user->id)->count() == 0) {
            $auth_user = User::where('id', $user->id)->first();
            $auth_user->webauthn = false;
            $auth_user->save();
        }

        return response()->json(['status' => 'success']);
    }

    public function rename(Request $request, WebAuthnCredential $webauthn, $user): JsonResponse
    {
        $webauthn = $webauthn::where('authenticatable_id', $user->id)->where('id', $request->post('webauthn_id'))->first();
        $webauthn->device_name = $request->post('device_name');
        $webauthn->save();

        return response()->json(['status' => true]);
    }
}
