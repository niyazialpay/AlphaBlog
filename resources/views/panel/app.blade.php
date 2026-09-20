{{--
    Panel Inertia kabuğu.

    Eski AdminLTE kabuğu (panel/base.blade.php) geçiş boyunca yaşamaya devam eder:
    henüz taşınmamış çekirdek ekranlar ve 7 modülün panel blade'leri onu @extends
    ediyor. Son modül ekranı taşındığında silinecek.
--}}
@php
    $panelUser = auth()->user();
    $generalSettings = app()->bound('general_settings') ? app('general_settings') : null;
    $panelFavicon = $generalSettings?->getFirstMediaUrl('site_favicon') ?: null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('language', app()->getLocale())) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title inertia>{{ config('app.name') }}</title>

    {{-- FOUC engeli: tema Vue mount olmadan, @vite'tan ÖNCE uygulanmalı. --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('alphablog-panel-theme');
                var system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-panel-theme', stored || system);
            } catch (e) {
                document.documentElement.setAttribute('data-panel-theme', 'light');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if(config('settings.fontawesome_pro'))
        <link rel="stylesheet" href="{{ config('app.url') }}/themes/fontawesome/css/all.css">
    @else
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @endif

    @if($panelFavicon)
        <link rel="shortcut icon" href="{{ $panelFavicon }}" type="image/x-icon">
    @endif
    <link rel="manifest" href="{{ route('manifest.panel') }}">

    {{--
        TinyMCE self-hosted kalmalı: public/themes/panel/js/tinymce.
        npm paketine taşınırsa GPL lisans anahtarı, yüklü plugin seti ve mevcut
        görsel yükleme yolu bozulur.
    --}}
    <script src="{{ config('app.url') }}/themes/panel/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>

    {{--
        Çeviri torbası: Inertia paylaşılan prop'u DEĞİL. Prop olsaydı her ziyarette
        yeniden serileştirilirdi; burada tam sayfa başına bir kez ödenir.
    --}}
    <script>window.__panelLang = @json(\App\Support\Panel\PanelLang::bag());</script>

    {{-- Yalnızca panel route'ları; çıplak @routes tüm route tablosunu gömer. --}}
    @routes('panel')
    @vite(['resources/js/panel/app.js'])
    @inertiaHead

    @if($panelUser?->role === 'owner' || $panelUser?->role === 'admin')
        {!! ($admin_notification ?? null)?->onesignal !!}
    @endif
</head>
<body class="min-h-screen bg-p-bg font-sans text-p-ink antialiased">
    @inertia
</body>
</html>
