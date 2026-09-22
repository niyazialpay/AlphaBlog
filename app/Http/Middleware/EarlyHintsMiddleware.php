<?php

namespace App\Http\Middleware;

use App\Support\Panel\Panel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class EarlyHintsMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->wantsHints($request, $response)) {
            return $response;
        }

        $links = array_merge($this->buildStaticEarlyHints(), $this->buildViteEarlyHints());

        if ($links !== []) {
            $response->headers->set('Link', implode(', ', $links), false);
        }

        return $response;
    }

    private function wantsHints(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && ! $request->ajax()
            && ! $request->header('X-Inertia')
            && ! Panel::isPanelRequest($request)
            && $response->isSuccessful()
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }

    private function buildViteEarlyHints(): array
    {
        if (Vite::isRunningHot()) {
            return [];
        }

        $entries = array_values(array_filter([
            config('theme.assets.css_entry'),
            config('theme.assets.js_entry', 'resources/js/app.js'),
        ], static fn ($entry) => is_string($entry) && $entry !== ''));

        if ($entries === []) {
            return [];
        }

        $manifestPath = public_path('build/manifest.json');
        if (! is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (! is_array($manifest)) {
            return [];
        }

        return $this->collectManifestLinks($manifest, $entries, 'build');
    }

    private function buildStaticEarlyHints(): array
    {
        $cdnUrl = config('app.cdn_url');
        if (! is_string($cdnUrl) || $cdnUrl === '') {
            return [];
        }

        $href = rtrim($cdnUrl, '/').'/themes/fontawesome/css/all.css';

        return ['<'.$href.'>; rel=preload; as=style'];
    }

    private function collectManifestLinks(array $manifest, array $entries, string $buildDirectory): array
    {
        $links = [];
        $seenChunks = [];
        $seenLinks = [];

        foreach ($entries as $entry) {
            if (! isset($manifest[$entry])) {
                continue;
            }

            $this->addManifestChunkLinks($entry, $manifest, $buildDirectory, $links, $seenChunks, $seenLinks);
        }

        return $links;
    }

    private function addManifestChunkLinks(
        string $key,
        array $manifest,
        string $buildDirectory,
        array &$links,
        array &$seenChunks,
        array &$seenLinks
    ): void {
        if (isset($seenChunks[$key])) {
            return;
        }

        $seenChunks[$key] = true;

        $chunk = $manifest[$key];
        if (isset($chunk['file']) && is_string($chunk['file'])) {
            $this->addAssetLink($chunk['file'], $buildDirectory, $links, $seenLinks);
        }

        foreach (($chunk['css'] ?? []) as $cssFile) {
            if (is_string($cssFile)) {
                $this->addAssetLink($cssFile, $buildDirectory, $links, $seenLinks);
            }
        }

        foreach (($chunk['imports'] ?? []) as $importKey) {
            if (isset($manifest[$importKey])) {
                $this->addManifestChunkLinks($importKey, $manifest, $buildDirectory, $links, $seenChunks, $seenLinks);
            }
        }
    }

    private function addAssetLink(
        string $file,
        string $buildDirectory,
        array &$links,
        array &$seenLinks
    ): void {
        if (! $this->shouldPreloadFile($file)) {
            return;
        }

        $href = asset($buildDirectory.'/'.$file);
        if (isset($seenLinks[$href])) {
            return;
        }

        $seenLinks[$href] = true;
        $links[] = $this->formatEarlyHintLink($href, $file);
    }

    private function shouldPreloadFile(string $file): bool
    {
        return str_ends_with($file, '.js') || str_ends_with($file, '.css');
    }

    private function formatEarlyHintLink(string $href, string $file): string
    {
        if (str_ends_with($file, '.css')) {
            return '<'.$href.'>; rel=preload; as=style';
        }

        return '<'.$href.'>; rel=modulepreload';
    }
}
