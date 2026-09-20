<?php

namespace App\Support\Panel;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Taşınmış panel ekranlarının tek render noktası.
 *
 * Controller'da değişen tek şey `view(...)` satırının bununla değişmesidir;
 * imza, doğrulama, policy ve yönlendirmeler aynı kalır:
 *
 *   - return view('panel.dashboard', $data);
 *   + return PanelResponse::render('Dashboard', 'panel.dashboard', $props, $data);
 *
 * PANEL_UI=blade (ya da PANEL_UI_SCREENS allowlist'i dışında kalmak) durumunda
 * eski Blade ekranı aynen döner — derleme gerektirmeyen geri dönüş yolu.
 */
final class PanelResponse
{
    /**
     * @param  string  $component  Inertia bileşen adı (ör. "Posts/Index", "valefix::Customers/Index")
     * @param  string  $bladeView  Geri dönüş için eski Blade görünümü
     * @param  array<string, mixed>  $props  Vue sayfasının prop'ları
     * @param  array<string, mixed>|null  $viewData  Blade'in beklediği veri; null ise $props kullanılır
     */
    public static function render(string $component, string $bladeView, array $props = [], ?array $viewData = null): Response
    {
        if (! self::vueEnabled($component)) {
            return response()->view($bladeView, $viewData ?? $props);
        }

        return Inertia::render($component, $props)->toResponse(request());
    }

    /**
     * Paginator satırlarını YERİNDE DEĞİŞTİRMEDEN Inertia prop'una indirger.
     *
     * `LengthAwarePaginator::through()` `$this->items->transform(...)` çağırır —
     * yani koleksiyonu yerinde değiştirir ve AYNI nesneyi döndürür. Tipik
     * kullanım
     *
     *     PanelResponse::render($c, $v, ['rows' => $rows->through($fn)], compact('rows'))
     *
     * bu yüzden sinsi bir hata taşır: PHP argümanları çağrıdan ÖNCE
     * değerlendirdiği için `through()` `PANEL_UI=blade` modunda da çalışır ve
     * Blade'e Eloquent modelleri yerine düz diziler ulaşır. Kill switch —
     * derleme gerektirmeyen tek geri dönüş yolumuz — sessizce bozulur.
     *
     * Bu yardımcı klonun koleksiyonunu değiştirir; özgün paginator'a dokunmaz.
     *
     * @template TValue
     *
     * @param  Paginator|CursorPaginator  $paginator
     * @param  callable(mixed, array-key): TValue  $callback
     */
    public static function rows(object $paginator, callable $callback): object
    {
        $projected = clone $paginator;
        $projected->setCollection($paginator->getCollection()->map($callback));

        return $projected;
    }

    /**
     * Ekran Vue olarak mı sunulacak?
     *
     * İki koşul birden aranır ve DEFTER tek otoritedir:
     *   1. Kill switch açık (PANEL_UI ve varsa PANEL_UI_SCREENS allowlist'i),
     *   2. Route adı config/panel_inertia_routes.php içinde.
     *
     * (2) olmadan bir controller'ı dönüştürüp defteri güncellemeyi unutmak,
     * sunucunun Vue render ettiği ama sidebar'ın tam sayfa yüklemesiyle gittiği
     * "yarım taşınmış" bir ekran bırakırdı. Böylece güvenli varsayılan Blade olur.
     */
    private static function vueEnabled(string $component): bool
    {
        if (! Panel::vueEnabled($component)) {
            return false;
        }

        $routeName = request()->route()?->getName();

        // Route adsızsa defterle eşleştirilemez; bu durumda kill switch yeterlidir.
        return $routeName === null || PanelMenu::isInertia($routeName);
    }
}
