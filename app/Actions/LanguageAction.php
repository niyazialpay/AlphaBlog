<?php

namespace App\Actions;

use App\Models\Languages;

class LanguageAction
{
    public static function setLanguage($request): void
    {
        $except = [
            '_debugbar',
            'api',
            'image',
            'rss',
            'sitemap',
            'sitemap.xml',
            'manifest.json',
            config('settings.admin_panel_path'),
            'forgot-password',
            'reset-password',
            'login',
            'cdn-cgi',
            'up',
            'user',
            'webauthn',
            'pulse',
            'livewire',
            'email',
            'email/verify',
        ];
        $languages = new Languages;
        if (! in_array($request->segment(1), $except)) {
            if (session()->has('language')) {
                if ($request->segment(1) == session('language')) {
                    $language = $languages->getLanguage(session('language'));
                } elseif ($request->segment(1) == null) {
                    $language = $languages->getLanguage(app('default_language')->code);
                } else {
                    $language = $languages->getLanguage($request->segment(1));
                    if ($language == null) {
                        abort(404);
                    }
                }
            } else {
                if ($request->segment(1) == null) {
                    $language = $languages->getLanguage(app('default_language')?->code);
                } else {
                    $language = $languages->getLanguage($request->segment(1));
                    if ($language == null) {
                        abort(404);
                    }
                }
            }
        } else {
            if (session()->has('language')) {
                $language = $languages->getLanguage(session('language'));
            } else {
                $language = $languages->getLanguage(
                    explode('-', explode(',', $request->server('HTTP_ACCEPT_LANGUAGE')
                    )[0])[0]
                );
                if (! $language) {
                    $language = $languages->getLanguage(app('default_language')->code);
                }
            }
        }
        session()->put('language', $language?->code);
        session()->put('language_flag', $language?->flag);
        session()->put('language_name', $language?->name);

        app()->setLocale($language?->code);
        setlocale(LC_ALL, $language?->code);
        setlocale(LC_TIME, $language?->code);
    }
}
