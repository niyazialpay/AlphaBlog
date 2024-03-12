<?php

namespace App\Exceptions;

use App\Action\RouteRedirectAction;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response|JsonResponse|RedirectResponse
    {
        $route = RouteRedirectAction::RouteRedirect($request);
        if ($route) {
            if($route->redirect_code == 404) {
                abort(404);
            }
            else{
                return redirect($route->new_url, (int)$route->redirect_code);
            }
        }
        if ($this->isHttpException($e) && $e->getStatusCode() == 404) {
            return response()->view(app('theme')->name.'.404', [], 404);
        }
        return parent::render($request, $e);
    }
}
