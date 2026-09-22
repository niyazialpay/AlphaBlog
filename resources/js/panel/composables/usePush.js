import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { route } from 'ziggy-js';

/**
 * Tarayici Push API'siyle konusan TEK yer.
 *
 * Durum modul seviyesinde tutulur (tekil): zil dropdown'i, ilk ziyaret karti ve
 * profil sekmesi AYNI aboneligi gosterir; biri acinca digeri aninda degisir.
 *
 * GERCEGIN KAYNAGI TARAYICIDIR. `subscribed` daima canli
 * `pushManager.getSubscription()` sonucundan tureetilir, localStorage'dan DEGIL:
 * kullanici izni tarayici ayarlarindan geri almis ya da site verisini silmis
 * olabilir; o durumda localStorage "abone" der ama gercek abonelik yoktur.
 * localStorage yalnizca ilk ziyaret kartinin "bir daha sorma" bilgisini tutar.
 *
 * Desteklenmeyen tarayicida HICBIR SEY FIRLATMAZ: her sey "unsupported"
 * durumuna duser ve arayuz bunu acikca yazar.
 */

/*
 * Service worker KOKTE (`/panel-push-sw.js`) duruyor ve `scope: '/'` ile
 * kaydediliyor. Bir service worker yalnizca kendi dizinini ve altini kontrol
 * edebilir; panel yolu ADMIN_PANEL_PATH ile degisebildigi icin kapsam kok
 * olmak ZORUNDA.
 */
const SW_URL = '/panel-push-sw.js';
const SW_SCOPE = '/';

/** Ilk ziyaret kartinin "bir daha gosterme" isareti. */
const DISMISS_KEY = 'alphablog-panel-push-prompt-dismissed';

const page = usePage();

const supported = ref(detectSupport());
const permission = ref(readPermission());
const subscribed = ref(false);
/** Tarayicidan ilk okuma tamamlandi mi — kart/dugme yanip sonmesin diye. */
const resolved = ref(false);
const busy = ref(false);
const promptDismissed = ref(readDismissed());

let registrationPromise = null;
let initialised = false;

/* ------------------------------------------------------------------ */
/* Ozellik tespiti                                                     */
/* ------------------------------------------------------------------ */

function detectSupport() {
    return (
        typeof window !== 'undefined' &&
        typeof navigator !== 'undefined' &&
        'serviceWorker' in navigator &&
        'PushManager' in window &&
        'Notification' in window
    );
}

function readPermission() {
    if (typeof window === 'undefined' || !('Notification' in window)) {
        return 'unsupported';
    }

    try {
        return window.Notification.permission;
    } catch (error) {
        return 'unsupported';
    }
}

/* ------------------------------------------------------------------ */
/* localStorage — HER erisim try/catch                                 */
/* ------------------------------------------------------------------ */

/*
 * Gizli sekmede, site verisi engellenmis tarayicida ve bazi kurumsal
 * politikalarda `localStorage`a DOKUNMAK bile firlatir (okumak dahil). Bu
 * yuzden okuma ve yazma ayri ayri sarmalanir; basarisizlik "hatirlanmadi"
 * demektir, cokme degil.
 */
function readDismissed() {
    try {
        return window.localStorage.getItem(DISMISS_KEY) === '1';
    } catch (error) {
        return false;
    }
}

function writeDismissed() {
    try {
        window.localStorage.setItem(DISMISS_KEY, '1');
    } catch (error) {
        // Yoksay: kart bu oturumda gizlenir, sonraki ziyarette yeniden cikar.
    }
}

/* ------------------------------------------------------------------ */
/* VAPID anahtari                                                      */
/* ------------------------------------------------------------------ */

const config = computed(() => page.props?.push || {});
const enabled = computed(() => Boolean(config.value.enabled && config.value.publicKey));

/**
 * base64url -> Uint8Array.
 *
 * `applicationServerKey` HAM BAYT ister; sunucudan gelen anahtar base64url
 * (`-` ve `_`, dolgu yok) oldugu icin once standart base64'e cevrilip dolgusu
 * tamamlanir, sonra `atob` ile bayta acilir. Dolgu eklenmezse `atob` firlatir.
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    const output = new Uint8Array(raw.length);

    for (let i = 0; i < raw.length; i += 1) {
        output[i] = raw.charCodeAt(i);
    }

    return output;
}

/**
 * Sunucuya bildirilecek sifreleme bicimi.
 *
 * Modern tarayicilar `aes128gcm` kullanir; `aesgcm` eski bicim ve sunucudaki
 * varsayilan. Tarayici ne diyorsa o kaydedilir, aksi halde gonderim
 * cozulemeyen bir yukle yapilir.
 */
function contentEncoding() {
    const encodings = window.PushManager?.supportedContentEncodings;

    if (Array.isArray(encodings) && encodings.length > 0) {
        return encodings.includes('aes128gcm') ? 'aes128gcm' : encodings[0];
    }

    return 'aesgcm';
}

/* ------------------------------------------------------------------ */
/* Service worker                                                      */
/* ------------------------------------------------------------------ */

function registration() {
    if (!supported.value) {
        return Promise.resolve(null);
    }

    if (registrationPromise === null) {
        registrationPromise = navigator.serviceWorker
            .register(SW_URL, { scope: SW_SCOPE })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Push service worker kaydedilemedi', error);
                supported.value = false;
                registrationPromise = null;

                return null;
            });
    }

    return registrationPromise;
}

async function currentSubscription() {
    const registered = await registration();

    if (!registered) {
        return null;
    }

    try {
        return await registered.pushManager.getSubscription();
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Mevcut push aboneligi okunamadi', error);

        return null;
    }
}

/* ------------------------------------------------------------------ */
/* Sunucu uclari — VERI ucu, `axios` ile (bkz. R1)                     */
/* ------------------------------------------------------------------ */

async function store(subscription) {
    const payload = subscription.toJSON();

    await axios.post(route('admin.profile.push.subscribe'), {
        endpoint: payload.endpoint,
        keys: payload.keys,
        contentEncoding: contentEncoding(),
    });
}

async function forget(endpoint) {
    await axios.post(route('admin.profile.push.unsubscribe'), { endpoint });
}

/* ------------------------------------------------------------------ */
/* Genel API                                                           */
/* ------------------------------------------------------------------ */

/**
 * Tarayicidaki gercek durumu yeniden okur.
 *
 * @returns {Promise<PushSubscription|null>}
 */
async function refresh() {
    permission.value = readPermission();

    if (!supported.value || !enabled.value) {
        subscribed.value = false;
        resolved.value = true;

        return null;
    }

    const subscription = await currentSubscription();

    subscribed.value = subscription !== null;
    resolved.value = true;

    return subscription;
}

/**
 * `Notification.requestPermission()` iki bicimde var: eski Safari geri cagirma
 * ister, digerleri promise doner. Ikisi de desteklenir — geri cagirma fazladan
 * argumani gormezden gelen tarayicilarda zararsizdir.
 */
function requestPermission() {
    return new Promise((resolve) => {
        try {
            const result = window.Notification.requestPermission((value) => resolve(value));

            if (result && typeof result.then === 'function') {
                result.then(resolve, () => resolve('denied'));
            }
        } catch (error) {
            resolve('denied');
        }
    });
}

/**
 * Izin iste, abone ol, sunucuya kaydet.
 *
 * @returns {Promise<boolean>} abonelik olustuysa true
 */
async function subscribe() {
    if (busy.value || !supported.value || !enabled.value) {
        return false;
    }

    busy.value = true;

    try {
        /*
         * Izin 'denied' ise tarayici BIR DAHA SORMAZ — `requestPermission()`
         * aninda 'denied' dondurur ve kullanici hicbir sey olmadigini sanir.
         * Bu yuzden cagirmadan once ayrilir; arayuz "tarayici ayarlarindan
         * engellenmis" der.
         */
        const granted = await requestPermission();

        permission.value = granted;

        if (granted !== 'granted') {
            subscribed.value = false;

            return false;
        }

        const registered = await registration();

        if (!registered) {
            return false;
        }

        let subscription = await registered.pushManager.getSubscription();

        if (subscription === null) {
            subscription = await registered.pushManager.subscribe({
                // Push API sartI: her push GORUNUR bir bildirim uretmeli.
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(config.value.publicKey),
            });
        }

        await store(subscription);

        subscribed.value = true;
        resolved.value = true;

        return true;
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Push aboneligi olusturulamadi', error);
        await refresh();

        return false;
    } finally {
        busy.value = false;
    }
}

/**
 * Tarayicidaki aboneligi VE sunucudaki satiri birlikte kaldirir.
 *
 * Yerel `unsubscribe()` patlasa bile sunucu cagrisi YAPILIR: aksi halde satir
 * tabloda kalir ve her bildirimde bosa gonderim denenir.
 *
 * @returns {Promise<boolean>}
 */
async function unsubscribe() {
    if (busy.value || !supported.value) {
        return false;
    }

    busy.value = true;

    let ok = true;

    try {
        const subscription = await currentSubscription();
        const endpoint = subscription?.endpoint || null;

        if (subscription !== null) {
            try {
                await subscription.unsubscribe();
            } catch (error) {
                // eslint-disable-next-line no-console
                console.error('Tarayici aboneligi kaldirilamadi', error);
                ok = false;
            }
        }

        if (endpoint !== null) {
            try {
                await forget(endpoint);
            } catch (error) {
                // eslint-disable-next-line no-console
                console.error('Sunucudaki abonelik kaydi silinemedi', error);
                ok = false;
            }
        }

        subscribed.value = false;
        resolved.value = true;

        return ok;
    } finally {
        busy.value = false;
    }
}

function toggle() {
    return subscribed.value ? unsubscribe() : subscribe();
}

/** Ilk ziyaret kartini kalici olarak kapatir. */
function dismissPrompt() {
    promptDismissed.value = true;
    writeDismissed();
}

/**
 * Panel acilisinda BIR KEZ cagrilir (PanelLayout).
 *
 * Zaten abone olan bir tarayicida mevcut abonelik sunucuya yeniden gonderilir.
 * Ucuz bir upsert (`endpoint_hash` uzerinden) ama gerekli: service worker
 * `pushsubscriptionchange` ile aboneligi panel kapaliyken YENILEMIS olabilir
 * ve sunucu hala eski endpoint'i tutuyor olabilir.
 */
async function initPush() {
    if (initialised || !enabled.value || !supported.value) {
        return;
    }

    initialised = true;

    const subscription = await refresh();

    if (subscription === null) {
        return;
    }

    try {
        await store(subscription);
    } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Mevcut push aboneligi sunucuya bildirilemedi', error);
    }
}

/**
 * Arayuzun gordugu TEK durum.
 *
 *  - `unsupported` : tarayici Push API'sini bilmiyor (ya da SW kaydi dustu)
 *  - `blocked`     : izin 'denied' — tarayici BIR DAHA SORMAZ, ayarlardan acilir
 *  - `on`          : gecerli bir abonelik var
 *  - `off`         : izin sorulmamis ya da verilmis ama abonelik yok
 */
const state = computed(() => {
    if (!supported.value) {
        return 'unsupported';
    }

    if (permission.value === 'denied') {
        return 'blocked';
    }

    return subscribed.value ? 'on' : 'off';
});

/**
 * Ilk ziyaret karti yalnizca HIC SORULMAMISSA cikar: izin 'default', abonelik
 * yok ve kullanici karti daha once kapatmamis. Her sayfa yuklemesinde sormaz.
 */
const shouldPrompt = computed(
    () =>
        enabled.value &&
        supported.value &&
        resolved.value &&
        !promptDismissed.value &&
        !subscribed.value &&
        permission.value === 'default',
);

export function usePush() {
    return {
        enabled,
        supported,
        permission,
        subscribed,
        resolved,
        busy,
        state,
        shouldPrompt,
        initPush,
        refresh,
        subscribe,
        unsubscribe,
        toggle,
        dismissPrompt,
    };
}

export {
    enabled,
    supported,
    permission,
    subscribed,
    resolved,
    busy,
    state,
    shouldPrompt,
    initPush,
    refresh,
    subscribe,
    unsubscribe,
    toggle,
    dismissPrompt,
    urlBase64ToUint8Array,
};
