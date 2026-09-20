import { router, usePage } from '@inertiajs/vue3';

/**
 * Geçiş dönemi navigasyonu.
 *
 * Panelin bir kısmı Vue, bir kısmı hâlâ eski AdminLTE Blade kabuğunda (ve 7 modülün
 * tamamı, taşınana kadar). Taşınmamış bir ekrana `router.visit()` yapılamaz: yanıtta
 * X-Inertia başlığı yoktur ve Inertia client hata modalı açar.
 *
 * Defter tek yerde: config/panel_inertia_routes.php → `inertiaRoutes` paylaşılan prop'u.
 * Varsayılan HER ZAMAN tam sayfa yüklemesidir; yanlış taraf sessiz bozulma değil,
 * yalnızca bir sayfa yenilenmesi maliyetidir.
 */

/** Str::is benzeri joker eşleşme: 'admin.monitoring.*' */
function matches(pattern, value) {
    if (pattern === value) {
        return true;
    }

    if (!pattern.includes('*')) {
        return false;
    }

    const escaped = pattern.replace(/[.+?^${}()|[\]\\]/g, '\\$&').replace(/\*/g, '.*');

    return new RegExp(`^${escaped}$`).test(value);
}

export function isMigrated(routeName) {
    if (!routeName) {
        return false;
    }

    const patterns = usePage().props.inertiaRoutes || [];

    return patterns.some((pattern) => matches(pattern, routeName));
}

/**
 * @param {string} url        Hedef URL
 * @param {?string} routeName Hedefin route adı (biliniyorsa)
 * @param {object} options    router.visit seçenekleri
 */
export function go(url, routeName = null, options = {}) {
    if (!url) {
        return;
    }

    if (isMigrated(routeName)) {
        router.visit(url, options);

        return;
    }

    window.location.href = url;
}

export function useNavigate() {
    return { go, isMigrated };
}
