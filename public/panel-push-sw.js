/*
 * Yonetim paneli Web Push service worker'i.
 *
 * KOKTE DURUYOR (public/) cunku bir service worker yalnizca KENDI DIZINI ve
 * altini kontrol edebiliyor. `/panel/...` altina konsaydi panel yolu env ile
 * degistiginde (ADMIN_PANEL_PATH) kapsam disinda kalirdi; kokte olunca panel
 * yolu ne olursa olsun calisir.
 *
 * Vite tarafindan derlenmez: service worker'in adresi SABIT olmali. Hash'li bir
 * dosya adi her derlemede yeni bir worker demek olurdu ve tarayicidaki mevcut
 * abonelikler her deploy'da kopardi.
 */

self.addEventListener('push', (event) => {
    if (! event.data) {
        return;
    }

    let payload;

    try {
        payload = event.data.json();
    } catch (error) {
        // Sunucu duz metin gonderdiyse en azindan onu goster.
        payload = { title: self.registration.scope, body: event.data.text() };
    }

    const title = payload.title || 'Bildirim';

    const options = {
        body: payload.body || '',
        icon: payload.icon || '/favicon.ico',
        badge: payload.badge || payload.icon || '/favicon.ico',
        tag: payload.tag || undefined,
        // Ayni etiketli yeni bildirim eskisinin yerine gecsin, ust uste yigilmasin.
        renotify: Boolean(payload.tag),
        requireInteraction: Boolean(payload.requireInteraction),
        data: {
            url: payload.url || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = (event.notification.data && event.notification.data.url) || '/';

    /*
     * Panel zaten bir sekmede acikse ONU one al, yeni sekme acma. Kullanici
     * her bildirimde yeni bir panel sekmesi biriktirmesin.
     */
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url.includes(target) && 'focus' in client) {
                    return client.focus();
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(target);
            }

            return undefined;
        }),
    );
});

/*
 * Push servisi aboneligi kendiliginden yenileyebiliyor (anahtar rotasyonu).
 *
 * Burada SUNUCUYA HABER VERILMIYOR ve bu bilincli: service worker'in oturumu
 * yok, panel yolunu da bilmiyor. Kimlik dogrulamasiz bir "abonelik kaydet" ucu
 * acmak, herhangi birinin tabloya satir yazabilmesi demek olurdu.
 *
 * Bunun yerine tarayicida GECERLI bir abonelik tutulur; panel bir sonraki
 * acilista mevcut aboneligi zaten sunucuya upsert ediyor. Arada sunucudaki eski
 * endpoint'e gonderim yapilirsa push servisi 410 doner ve satir otomatik
 * temizlenir (bkz. WebPushChannel).
 */
self.addEventListener('pushsubscriptionchange', (event) => {
    const key = event.oldSubscription?.options?.applicationServerKey;

    if (! key) {
        return;
    }

    event.waitUntil(
        self.registration.pushManager
            .subscribe({ userVisibleOnly: true, applicationServerKey: key })
            .catch(() => undefined),
    );
});
