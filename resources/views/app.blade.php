<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if(str_contains(config('theme.assets.css_entry', ''), 'BirdergiV2'))
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700;12..96,800&family=Newsreader:ital,opsz,wght@0,6..72,300;0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400;1,6..72,500&family=JetBrains+Mono:wght@400;500&display=optional" as="style" crossorigin>
        @endif
        <link rel="stylesheet" href="{{ rtrim(config('app.cdn_url') ?: config('app.url'), '/') }}/themes/fontawesome/css/all.css" media="print" onload="this.media='all'">
        <noscript><link rel="stylesheet" href="{{ rtrim(config('app.cdn_url') ?: config('app.url'), '/') }}/themes/fontawesome/css/all.css"></noscript>
        @php
            $viteEntries = array_values(array_filter([
                config('theme.assets.css_entry'),
                config('theme.assets.js_entry', 'resources/js/app.js'),
            ]));
        @endphp
        @if(!empty($viteEntries))
            @vite($viteEntries)
        @endif
        @php
            $meta = $meta ?? [];
            $renderAttributes = static function (array $attributes): string {
                return collect($attributes)
                    ->map(function ($value, $key) {
                        if ($value === null || $value === false || $value === '') {
                            return null;
                        }

                        return $key.'="'.e($value).'"';
                    })
                    ->filter()
                    ->implode(' ');
            };
        @endphp
        <title inertia>{{ $meta['title'] ?? config('app.name') }}</title>
        @foreach (($meta['meta'] ?? []) as $tag)
            <meta {!! $renderAttributes($tag) !!}>
        @endforeach
        @foreach (($meta['links'] ?? []) as $link)
            <link {!! $renderAttributes($link) !!}>
        @endforeach
        @php
            $structuredData = $structuredData ?? [];
            $jsonLd = static fn ($value) => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        @endphp
        @if (!empty($structuredData['organization']))
            <script type="application/ld+json">{!! $jsonLd($structuredData['organization']) !!}</script>
        @endif
        @if (!empty($structuredData['website']))
            <script type="application/ld+json">{!! $jsonLd($structuredData['website']) !!}</script>
        @endif
        @if (!empty($structuredData['article']))
            <script type="application/ld+json">{!! $jsonLd($structuredData['article']) !!}</script>
        @endif
        @if (!empty($structuredData['breadcrumb']))
            <script type="application/ld+json">{!! $jsonLd($structuredData['breadcrumb']) !!}</script>
        @endif
        @if (!empty($structuredData['comments']))
            <script type="application/ld+json">{!! $jsonLd(['@context' => 'https://schema.org/', '@graph' => $structuredData['comments']]) !!}</script>
        @endif
        @php
            $gaId = (optional($analytic_settings ?? null)->ga_measurement_id) ?: config('services.google_analytics.measurement_id');
            $gaClientId = null;
            if ($gaId) {
                $gaClientId = request()->cookie('_ga_cid') ?: (string) \Illuminate\Support\Str::uuid();
                if (! request()->cookie('_ga_cid')) {
                    \Illuminate\Support\Facades\Cookie::queue('_ga_cid', $gaClientId, 730 * 24 * 60);
                }
                app()->instance('ga_client_id', $gaClientId);
            }
        @endphp
        @if ($gaId)
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                // Consent Mode v2 — KVKK/GDPR onay banner'ı eklenince ad_storage/analytics_storage
                // 'denied' başlatılıp onay alınınca gtag('consent','update',...) ile açılabilir.
                gtag('consent', 'default', {
                    'ad_storage': 'denied',
                    'ad_user_data': 'denied',
                    'ad_personalization': 'denied',
                    'analytics_storage': 'granted',
                    'wait_for_update': 500
                });
                // send_page_view:false → page_view server-side Measurement Protocol'den gelir (hybrid).
                // client_id server tarafından dayatılır → MP ile aynı kullanıcı/oturumda eşleşir.
                gtag('config', '{{ $gaId }}', {
                    send_page_view: false,
                    client_id: '{{ $gaClientId }}'
                });
            </script>
        @endif
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
