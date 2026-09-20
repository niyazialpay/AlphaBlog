/**
 * Dashboard widget çözümleyici.
 *
 * `config/dashboard_widgets.php` anahtarları DEĞİŞMEZ — `DashboardWidget`
 * tablosundaki `widget_type` değerleri ve `allWidgets()` whitelist'i aynı kalır,
 * yani kullanıcıların kayıtlı düzenleri bozulmaz. Değişen tek şey çözümleme
 * hedefi: `::` içeren anahtarlar artık namespace'li Blade view yerine modülün
 * kendi Vue bileşenini çözer.
 *
 *   'edergi::edergi_reads'
 *     → Modules/EDergi/resources/js/panel/Widgets/edergi_reads.vue
 *
 * Modül yoksa glob boş geçer; çekirdek sorunsuz derlenir ve ekran o widget için
 * "modül güncellenmeli" durumu gösterir.
 */
const moduleWidgets = import.meta.glob('../../../../Modules/*/resources/js/panel/Widgets/*.vue');

const moduleLookup = Object.fromEntries(
    Object.entries(moduleWidgets).map(([path, importer]) => {
        const match = path.match(/Modules\/([^/]+)\/resources\/js\/panel\/Widgets\/(.+)\.vue$/);

        return match ? [`${match[1].toLowerCase()}::${match[2]}`, importer] : [path, importer];
    }),
);

export function resolveModuleWidget(type) {
    if (!type.includes('::')) {
        return null;
    }

    const [namespace, rest] = type.split('::');

    /*
     * Anahtarlar Blade doneminden kalma NAMESPACE'LI GORUNUM YOLLARI:
     * `edergi::widgets.edergi_reads`. Bu anahtarlar DEGISTIRILEMEZ —
     * `dashboard_widgets` tablosundaki `widget_type` degerleri bunlar, yani
     * kullanicilarin kayitli dashboard duzenleri onlara bagli.
     *
     * Vue tarafinda karsiligi `Widgets/edergi_reads.vue`; bu yuzden bastaki
     * `widgets.` segmenti soyulur. Hem soyulmus hem ham biçim denenir ki
     * `widgets.` onekini kullanmayan moduller de calissin.
     */
    const candidates = [rest];

    if (rest.startsWith('widgets.')) {
        candidates.push(rest.slice('widgets.'.length));
    }

    for (const name of candidates) {
        const hit = moduleLookup[`${namespace.toLowerCase()}::${name}`];

        if (hit) {
            return hit;
        }
    }

    return null;
}

/* Çekirdek widget'ları üç jenerik şekle indirger: metric / chart / table. */
export const CORE_WIDGETS = {
    ga4_active_users: { kind: 'metric', source: 'ga4.overview.active_users', label: 'Aktif Kullanıcı' },
    ga4_new_users: { kind: 'metric', source: 'ga4.overview.new_users', label: 'Yeni Kullanıcı' },
    ga4_pageviews: { kind: 'metric', source: 'ga4.overview.pageviews', label: 'Sayfa Görüntüleme' },
    ga4_events: { kind: 'metric', source: 'ga4.overview.events', label: 'Etkinlik Sayısı' },
    ga4_engagement_time: { kind: 'metric', source: 'ga4.overview.engagement_time', label: 'Ort. Etkileşim' },

    // GSC metrikleri ham performance nesnesinden hesaplanır (blade da öyle yapıyordu).
    gsc_clicks: { kind: 'gsc-metric', metric: 'clicks', label: 'GSC Tıklama' },
    gsc_impressions: { kind: 'gsc-metric', metric: 'impressions', label: 'GSC Gösterim' },
    gsc_ctr: { kind: 'gsc-metric', metric: 'ctr', label: 'GSC CTR', suffix: '%' },
    // Konumda DÜŞÜK iyi → değişim yönü ters yorumlanır.
    gsc_position: { kind: 'gsc-metric', metric: 'position', label: 'GSC Ort. Konum', lowerIsBetter: true },

    ga4_browsers: { kind: 'pie', source: 'ga4.browsers', labelKey: 'browser', valueKey: 'screenPageViews', label: 'Tarayıcılar' },
    ga4_countries: { kind: 'pie', source: 'ga4.countries', labelKey: 'country', valueKey: 'screenPageViews', label: 'Ülkeler' },
    ga4_os: { kind: 'pie', source: 'ga4.os', labelKey: 'operatingSystem', valueKey: 'screenPageViews', label: 'İşletim Sistemleri' },
    ga4_user_types: { kind: 'pie', source: 'ga4.user_types', labelKey: 'newVsReturning', valueKey: 'activeUsers', label: 'Kullanıcı Tipi' },

    ga4_visitors_trend: { kind: 'trend', source: 'ga4.trend', label: 'Ziyaretçi Trendi' },
    ga4_top_pages: { kind: 'top-pages', source: 'ga4.top_pages', label: 'En Çok Görüntülenen' },

    gsc_keywords: { kind: 'keywords', source: 'gsc.keywords', label: 'Top Keywords', moreRoute: 'admin.search-console' },
    site_comments: { kind: 'comments', source: 'comments', label: 'Son Yorumlar', moreRoute: 'admin.post.comments' },
    site_firewall: { kind: 'firewall', source: 'firewall', label: 'Firewall Logları', moreRoute: 'admin.firewall.logs' },
};

export function pick(data, path) {
    return String(path)
        .split('.')
        .reduce((carry, segment) => (carry == null ? undefined : carry[segment]), data);
}
