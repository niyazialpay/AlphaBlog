<?php

namespace Tests\Feature;

use App\Http\Middleware\EarlyHintsMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EarlyHintsMiddlewareTest extends TestCase
{
    private const CDN_LINK = 'https://cdn.test/themes/fontawesome/css/all.css';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.cdn_url' => 'https://cdn.test']);
    }

    private function handle(Request $request, Response $response): Response
    {
        return (new EarlyHintsMiddleware)->handle($request, fn () => $response);
    }

    private function html(): Response
    {
        return new Response('<html></html>', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function test_full_page_html_gets_preload_links(): void
    {
        $response = $this->handle(Request::create('/some-page'), $this->html());

        $this->assertStringContainsString(self::CDN_LINK, (string) $response->headers->get('Link'));
    }

    public function test_redirect_keeps_its_status_and_gets_no_links(): void
    {
        $response = $this->handle(Request::create('/some-page'), new RedirectResponse('/elsewhere'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Link'));
    }

    public function test_xhr_inertia_post_and_panel_requests_get_no_links(): void
    {
        $xhr = Request::create('/some-page');
        $xhr->headers->set('X-Requested-With', 'XMLHttpRequest');

        $inertia = Request::create('/some-page');
        $inertia->headers->set('X-Inertia', 'true');

        $panel = Request::create('/'.trim((string) config('settings.admin_panel_path', 'admin'), '/').'/posts');

        foreach ([$xhr, $inertia, Request::create('/some-page', 'POST'), $panel] as $request) {
            $this->assertFalse($this->handle($request, $this->html())->headers->has('Link'), $request->getRequestUri());
        }
    }

    public function test_middleware_never_sends_an_informational_response(): void
    {
        $code = collect(file(app_path('Http/Middleware/EarlyHintsMiddleware.php')))
            ->reject(fn (string $line) => preg_match('#^\s*(\*|/\*|//)#', $line))
            ->implode('');

        $this->assertStringNotContainsString('headers_send', $code);
    }
}
