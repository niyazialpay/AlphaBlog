<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Corbado\Classes\Exceptions\Assert;
use Corbado\Classes\Exceptions\Configuration;
use Corbado\Classes\WebhookModels\AuthMethodsDataResponse;
use Corbado\Classes\Webhook;
use Corbado\Generated\ApiException;
use Corbado\Generated\Model\AuthTokenValidateRsp;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Throwable;

class WebAuthnController extends Controller
{
    public function index()
    {
        return view('webauthn.index');
    }

    public function list(WebAuthnCredentials $webauthn): JsonResponse
    {
        return response()->json($webauthn::where('authenticatable_id', auth()->user()->id)->get());
    }

    public function delete(Request $request, WebAuthnCredentials $webauthn): JsonResponse
    {
        $webauthn = $webauthn::where('authenticatable_id', auth()->user()->id)->where('id', $request->post('webauthn_id'))->first();
        $webauthn->delete();

        if (WebAuthnCredentials::where('authenticatable_id', auth()->user()->id)->count() == 0) {
            $auth_user = User::where('id', auth()->user()->id)->first();
            if (!$auth_user?->two_factor_confirmed && $auth_user?->two_factor_secret == null) {
                $auth_user->otp = false;
                $auth_user->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function rename(Request $request, WebAuthnCredentials $webauthn): JsonResponse
    {
        $webauthn = $webauthn::where('authenticatable_id', auth()->user()->id)->where('id', $request->post('webauthn_id'))->first();
        $webauthn->name = $request->post('device_name');
        $webauthn->save();

        return response()->json(['status' => true]);
    }


    public function webhook()
    {
        try {
            // Create new webhook instance with "webhookUsername" and "webhookPassword". Both must be
            // set in the developer panel (https://app.corbado.com) and are used to secure your
            // webhook (this one here) with basic authentication.
            $webhook = new Webhook(config('corbado.username'), config('corbado.password'));

            // Handle authentication so your webhook is secured (basic authentication). If username
            // and/or password are invalid handleAuthentication() will send HTTP status code
            // 401 (Unauthorized) and terminate/exit execution here.
            $webhook->handleAuthentication();

            // Check if request has been made with POST. For Corbado webhooks
            // only POST is allowed/used.
            if (!$webhook->isPost()) {
                throw new Exception('Only POST is allowed');
            }

            // Get the webhook action and act accordingly. Every Corbado
            // webhook has an action.
            switch ($webhook->getAction()) {
                // Handle the "authMethods" action which basically checks
                // if a user exists on your side/in your database.
                case $webhook::ACTION_AUTH_METHODS:
                    $request = $webhook->getAuthMethodsRequest();

                    // Now check if the given user/username exists in your
                    // database and send status. Implement getUserStatus()
                    // function below.
                    $status = $this->getUserStatus($request->data->username);
                    $webhook->sendAuthMethodsResponse($status);

                    break;

                // Handle the "passwordVerify" action which basically checks
                // if the given username and password are valid.
                case $webhook::ACTION_PASSWORD_VERIFY:
                    $request = $webhook->getPasswordVerifyRequest();

                    // Now check if the given username and password is
                    // valid. Implement verifyPassword() function below.
                    if ($this->verifyPassword($request->data->username, $request->data->password) === true) {
                        $webhook->sendPasswordVerifyResponse(true);
                    } else {
                        $webhook->sendPasswordVerifyResponse(false);
                    }

                    break;

                default:
                    throw new Exception('Invalid action "' . $webhook->getAction() . '"');
            }
        } catch (Throwable $e) {
            // If something went wrong just return HTTP status
            // code 500. For successful requests Corbado always
            // expects HTTP status code 200. Everything else
            // will be treated as error.
            http_response_code(500);

            // We expose the full error message here. Usually you would
            // not do this (security!) but in this case Corbado is the
            // only consumer of your webhook. The error message gets
            // logged at Corbado and helps you and us debugging your
            // webhook.
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }
    }

    private function verifyPassword(string $username, string $password): bool
    {
        if (Auth::validate(['email' => $username, 'password' => $password])) {
            return true;
        }
        return false;
    }

    private function getUserStatus(string $username): string
    {
        /////////////////////////////////////
        // Implement your logic here!
        ////////////////////////////////////

        // Example
        if (User::where('email', $username)->exists()) {
            return AuthMethodsDataResponse::USER_EXISTS;
        }

        return AuthMethodsDataResponse::USER_NOT_EXISTS;
    }

    /**
     * @throws Configuration
     * @throws Assert
     * @throws Exception
     */
    public function redirect(Request $request){
        $corbadoAuthToken = $request->get('corbadoAuthToken');
        $remoteAddress = $request->getClientIp();
        $userAgent = $request->header('User-Agent');

        $config = new \Corbado\Configuration(config('corbado.project_id'), config('corbado.project_secret'));
        $corbado = new \Corbado\SDK($config);

        try {
            $corbado_request = new \Corbado\Generated\Model\AuthTokenValidateReq();
            $corbado_request->setToken($corbadoAuthToken);
            $corbado_request->setClientInfo(\Corbado\SDK::createClientInfo($remoteAddress, $userAgent));

            /** @var AuthTokenValidateRsp $response */
            $response = $corbado->authTokens()->authTokenValidate($corbado_request);


            if($response->getHttpStatusCode()==200){
                $user = User::where('email', json_decode($response->getData()->getUserData())->username)->first();
                if($user){
                    Auth::login($user, true);
                    return redirect()->route('admin.index');
                }
                else{
                    return redirect()->route('admin.login');
                }
            }
            else{
                return redirect()->route('login');
            }

        } catch (ApiException $e) {
            // Handle exception (access $e->getResponseBody() for more details)
            throw new Exception($e);
        } catch (Throwable $e) {
            // Handle exception
            throw new Exception($e);
        }
    }

}
