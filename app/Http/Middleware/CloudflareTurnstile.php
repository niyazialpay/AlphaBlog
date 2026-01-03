<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudflareTurnstile
{
    protected array $except = [];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws GuzzleException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'GET' && ! $request->is($this->except)) {
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://challenges.cloudflare.com/turnstile/v0/',
            ]);
            $response = $client->request('POST', 'siteverify', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'secret' => config('cloudflare.turnstile_secret_key'),
                    'response' => $request->post('cf-turnstile-response'),
                    'remoteip' => $request->ip(),
                ]),

            ]);
            $response = json_decode($response->getBody()->getContents());
            if (! $response->success) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Cloudflare Turnstile doğrulaması başarısız!',
                    ], 403);
                } else {
                    return redirect()->back()->with('error', 'Cloudflare Turnstile doğrulaması başarısız!');
                }
            }
        }

        return $next($request);
    }
}
