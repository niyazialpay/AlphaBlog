import axios from 'axios';

/**
 * Panel bundle'inin KENDI axios kurulumu.
 *
 * NEDEN AYRI: panel girisi eskiden `resources/js/bootstrap.js` dosyasini import
 * ediyordu. O dosya git'te IZLENMIYOR (`.gitignore:37` -> `/resources/js/*`,
 * yalnizca `panel/`, `panel.js`, `app.js`, `app.min.js` geri dahil edilmis) —
 * cunku `resources/js` site basina tema dizini olarak ele aliniyor ve deploy'da
 * paylasilan (shared) kopyadan geliyor.
 *
 * Sonuc: panel build'i, repoda OLMAYAN bir dosyaya bagimliydi. Tema dizininin
 * paylasilan kopyasinda panel yoksa `vite build` giris modulunu cozemiyor
 * (UNRESOLVED_ENTRY). Panelin ihtiyaci zaten yalnizca su birkac satir; ayri
 * bir yuzey oldugu icin kendi kurulumunu tasimasi dogrusu.
 *
 * Echo/Pusher BILEREK yok: panel hicbir yerde `Echo` kullanmiyor (bildirim zili
 * sunucu prop'undan besleniyor), dolayisiyla websocket istemcisini panel
 * bundle'ina sokmanin anlami yok.
 */
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/*
 * CSRF: VerifyCsrfToken sirayla `_token` -> `X-CSRF-TOKEN` -> `X-XSRF-TOKEN`
 * okur. Inertia disi axios cagrilari (TinyMCE yuklemeleri, arama, index durumu,
 * onbellek temizleme) aksi halde 419 alir.
 *
 * Bu baslik ILK sayfa yuklemesindeki <meta>'dan kurulur ve panel bir SPA oldugu
 * icin bayatlayabilir; `app.js` her Inertia yanitindan gelen taze token ile
 * uzerine yazar.
 */
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}
