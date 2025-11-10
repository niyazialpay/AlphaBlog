<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="{{ rtrim(config('app.cdn_url') ?: config('app.url'), '/') }}/themes/fontawesome/css/all.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
