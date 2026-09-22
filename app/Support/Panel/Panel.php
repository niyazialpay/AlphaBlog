<?php

namespace App\Support\Panel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Honeypot\Honeypot;

final class Panel
{
    /**
     * @var list<string>
     */
    private const AUTH_ROUTES = [
        'login',
        'login.first_step',
        'two-factor.verify',
        'forgot-password',
        'password.reset',
        'password.update',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'lockscreen',
        'webauthn.login',
        'webauthn.login.options',
    ];

    public static function path(): string
    {
        return (string) config('settings.admin_panel_path', 'admin');
    }

    /**
     * @return array<string, mixed>
     */
    public static function honeypot(): array
    {
        $setup = app(Honeypot::class);

        return [
            'enabled' => $setup->enabled(),
            'nameFieldName' => $setup->nameFieldName(),
            'validFromFieldName' => $setup->validFromFieldName(),
            'encryptedValidFrom' => $setup->encryptedValidFrom(),
        ];
    }

    public static function isPanelRequest(Request $request): bool
    {
        $path = self::path();

        if ($request->is($path, $path.'/*')) {
            return true;
        }

        return self::isAuthRequest($request);
    }

    public static function isAuthRequest(Request $request): bool
    {
        $name = $request->route()?->getName();

        return $name !== null && in_array($name, self::AUTH_ROUTES, true);
    }

    public static function vueEnabled(?string $component = null): bool
    {
        if (config('settings.panel_ui') !== 'vue') {
            return false;
        }

        if ($component === null) {
            return true;
        }

        $only = array_filter(array_map('trim', explode(',', (string) config('settings.panel_ui_screens'))));

        return $only === []
            || in_array($component, $only, true)
            || in_array(Str::before($component, '/'), $only, true);
    }
}
