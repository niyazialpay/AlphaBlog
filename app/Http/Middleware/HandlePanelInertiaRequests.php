<?php

namespace App\Http\Middleware;

use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Support\Panel\Panel;
use App\Support\Panel\PanelMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Panel yüzeyinin Inertia middleware'i.
 *
 * `bootstrap/app.php` içinde `HandleInertiaRequests`'ten SONRA eklenir; sıra orada
 * deterministik olduğu için `Inertia::setRootView()` yarışı yoktur — sonuncu kazanır.
 * Panel dışı isteklerde hemen çekilir, yani ön yüz teması hiç etkilenmez.
 *
 * Panel tespiti yol tabanlıdır, bu yüzden 7 modülün panel ekranları da kapsanır
 * (hepsi `{admin_panel_path}/<slug>` altında) — modül başına kayıt gerekmez.
 */
final class HandlePanelInertiaRequests extends Middleware
{
    protected $rootView = 'panel.app';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Panel::isPanelRequest($request) || ! Panel::vueEnabled()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * Skalerler dışında her şey Closure'dur: Inertia bunları yalnızca gerçek bir
     * Inertia yanıtı üretirken çözer. Böylece henüz taşınmamış Blade ekranlarında
     * (ve tüm modül ekranlarında) hiçbir sorgu tetiklenmez.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $shared = [
            ...parent::share($request),

            'app' => ['name' => config('app.name')],
            'panelPath' => Panel::path(),
            'siteUrl' => config('app.url'),
            /*
             * Tarih biçimlendirme istemciye taşındı (prop'lar ISO-8601).
             * Saat dilimi AÇIKÇA gönderilmeli: aksi halde Intl tarayıcının yerel
             * saatine düşer ve başka saat diliminden bakan kullanıcıda damgalar kayar.
             */
            'timezone' => config('app.timezone'),
            'fontawesomePro' => (bool) config('settings.fontawesome_pro'),

            'siteName' => fn () => self::siteName(),
            'siteDomain' => parse_url((string) config('app.url'), PHP_URL_HOST),
            'favicon' => fn () => self::favicon(),
            // Auth kabugu (panel/auth/layouts/app.blade.php) acik temali logoyu basiyordu.
            'siteLogo' => fn () => self::siteLogo(),
            // <x-turnstile /> bilesen karsiligi: anahtar prop olarak gecer.
            'turnstileSiteKey' => fn () => config('cloudflare.turnstile_site_key'),
            'aiEnabled' => fn () => PanelMenu::aiEnabled(),

            // Tek migrasyon defteri (config/panel_inertia_routes.php) istemciye de verilir:
            // komut paleti, bildirim zili ve capraz baglantilar tasinmamis ekrana
            // router.visit() yapmamali, tam sayfa yuklemesi kullanmali.
            'inertiaRoutes' => PanelMenu::inertiaRoutePatterns(),

            'languages' => fn () => self::languages(),
            'currentLanguage' => fn () => [
                'code' => session('language'),
                'name' => session('language_name'),
                'flag' => session('language_flag'),
            ],
            'defaultLanguage' => fn () => self::defaultLanguage(),

            /*
             * Panel bir SPA: kok blade yalnizca ILK tam sayfa yuklemesinde
             * render ediliyor, dolayisiyla `<meta name="csrf-token">` degeri
             * (ve bootstrap.js'in ondan kurdugu statik `X-CSRF-TOKEN` basligi)
             * oturum yenilendiginde BAYATLIYOR ve sonraki her POST 419 donuyor.
             * Token her Inertia yanitinda tazelenir; istemci basligi gunceller.
             */
            'csrfToken' => fn () => $request->session()->token(),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'message' => fn () => $request->session()->get('message'),
                // Fortify 2FA akışları 'status' anahtarını kullanıyor; yeniden adlandırılmadı.
                'status' => fn () => $request->session()->get('status'),
            ],
        ];

        if ($user === null) {
            // Auth ekranları (login, otp, parola sıfırlama) aynı kabuğu kullanıyor.
            return [...$shared, 'auth' => ['user' => null], 'can' => [], 'counts' => [], 'menu' => []];
        }

        return [
            ...$shared,

            'auth' => fn () => ['user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'initials' => self::initials($user),
                'profileImage' => replaceCDN($user->profile_image),
                'hasTwoFactor' => (bool) ($user->webauthn || $user->otp),
                'impersonated' => $request->session()->has('impersonated'),
            ]],

            /*
             * partials/menu.blade.php ve header-navbar.blade.php içindeki @can'lerin tamamı.
             * DİKKAT: 'create' yeteneği BİLEREK yok — PostPolicy::create()
             * request()->route()->parameter('type') okuyor ve {type} parametresi
             * olmayan route'larda patlıyor. Yerine 'createPost' kullanılıyor.
             */
            'can' => fn () => [
                'admin' => $user->can('admin', User::class),
                'owner' => $user->can('owner', User::class),
                'moderator' => $user->can('moderator', User::class),
                'cloudflare' => $user->can('cloudflare', User::class),
                'createPost' => $user->can('createPost', Posts::class),
                'viewPages' => $user->can('viewPages', Posts::class),
                'category' => $user->can('category', Categories::class),
                'viewComments' => $user->can('view', Comments::class),
            ],

            // Sayımlar NewCommentsCount / SearchedWords middleware'lerinden okunur,
            // yeniden sorgulanmaz: eski kabukla asla farklı sayı göstermesin.
            'counts' => fn () => [
                'newComments' => (int) View::shared('newCommentsCount', 0),
                'searchedWords' => (int) View::shared('searchedWordsCount', 0),
                'unreadNotifications' => (int) $user->unreadNotifications()->count(),
            ],

            /*
             * Zil dropdown'ı. Alan adları panel/components/notifications.blade.php ile
             * aynı veriyi taşır: data['title'], data['message'], data['url'] ve
             * notifications.readAndRedirect bağlantısı.
             *
             * Inertia::optional → yalnızca router.reload({ only: ['notifications'] })
             * çağrıldığında gönderilir; her ziyarette 5 satırlık sorgu atılmaz.
             */
            'notifications' => Inertia::optional(fn () => $user->unreadNotifications()->take(5)->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'url' => route('notifications.readAndRedirect', $notification->id),
                    'readAt' => $notification->read_at?->toIso8601String(),
                    'createdAt' => $notification->created_at?->toIso8601String(),
                    'ago' => $notification->created_at?->diffForHumans(),
                ])->all()),

            'menu' => fn () => PanelMenu::build($user),
        ];
    }

    private static function initials(User $user): string
    {
        $source = trim((string) $user->name.' '.(string) $user->surname) ?: (string) $user->nickname;

        return Str::of($source)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    private static function siteName(): ?string
    {
        try {
            return app('seo_settings')?->site_name ?? config('app.name');
        } catch (Throwable) {
            return config('app.name');
        }
    }

    private static function siteLogo(): ?string
    {
        try {
            return app('general_settings')?->getFirstMediaUrl('site_logo_light') ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    private static function favicon(): ?string
    {
        try {
            return app('general_settings')?->getFirstMediaUrl('site_favicon') ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function languages(): array
    {
        try {
            return collect(app('languages'))
                ->map(fn ($language) => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'flag' => $language->flag,
                ])
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private static function defaultLanguage(): ?string
    {
        try {
            return app('default_language')?->code;
        } catch (Throwable) {
            return null;
        }
    }
}
