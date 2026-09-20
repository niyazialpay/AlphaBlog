<?php

namespace App\Support\Panel;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Honeypot\Honeypot;

/**
 * Panel yüzeyi hakkındaki ortak kararlar.
 *
 * Panel tespiti YOL TABANLIDIR. Çekirdek panel route'ları da, 7 modülün panel
 * route'ları da `config('settings.admin_panel_path')` ile prefixlenmiştir
 * (ör. /admin/valefix, /admin/edergi/magazines), bu yüzden tek bir kontrol
 * hepsini kapsar ve modül başına middleware düzenlemesi gerekmez.
 */
final class Panel
{
    /**
     * Panel Inertia kabuğuyla sunulacak, panel prefix'i DIŞINDAKİ route adları.
     *
     * URI deseni değil route adı kullanılır: `/login` çıplak bir yol ve bir
     * modülün front route'u ile çakışabilir.
     *
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
     * spatie/laravel-honeypot alanları, Blade'deki `@honeypot` direktifinin
     * Vue karşılığı olarak prop biçiminde.
     *
     * Login ve OTP uçları `ProtectAgainstSpam` middleware'i ile korunuyor; bu
     * alanlar gönderilmezse istek sessizce boş sayfaya düşer. Alan adı
     * `randomize_name_field_name` açıkken her render'da değiştiği için değerler
     * sunucudan gelmek zorunda.
     *
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

    /**
     * İstek panel yüzeyine mi ait? (çekirdek + modül panel ekranları + auth ekranları)
     */
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

    /**
     * Vue paneli bu bileşen için etkin mi? (kill switch)
     *
     * PANEL_UI=blade  → her şey eski Blade kabuğuna döner.
     * PANEL_UI_SCREENS=Dashboard,Posts → yalnızca listelenenler Vue.
     */
    public static function vueEnabled(?string $component = null): bool
    {
        if (config('settings.panel_ui') !== 'vue') {
            return false;
        }

        // Bileşen verilmediyse soru "Vue yüzeyi hiç açık mı?" demektir (middleware seviyesi).
        // Ekran bazlı süzme, render anında yapılır.
        if ($component === null) {
            return true;
        }

        $only = array_filter(array_map('trim', explode(',', (string) config('settings.panel_ui_screens'))));

        return $only === []
            || in_array($component, $only, true)
            || in_array(Str::before($component, '/'), $only, true);
    }
}
