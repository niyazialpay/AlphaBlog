import { cpSync, existsSync, mkdirSync, readdirSync, realpathSync, rmSync, statSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Modul panel kaynaklarini `resources/js/panel/.modules/<modul>/` altina aynalar.
 *
 * NEDEN VAR
 * ---------
 * Modul panel sayfalari `import.meta.glob('../../../Modules/*.../Pages/**.vue')`
 * ile toplaniyordu. Deploy'da `Modules` PAYLASILAN bir dizine SYMLINK olabiliyor
 * (atomic release yapisi) ve glob symlink'i guvenilir gezemiyor: hicbir modul
 * sayfasi bundle'a girmiyor, panelde her modul ekrani
 *
 *   Panel sayfasi bulunamadi: edergi::Magazines/Index
 *
 * ile aciliyor. Ayni symlink node cozumlemesini de bozuyordu: modul dosyasinin
 * GERCEK yolundan yukari cikildigi icin release'deki `node_modules` bulunamiyor
 * ve `@inertiajs/vue3` cozulemiyordu.
 *
 * Node'un `fs` API'si symlink'leri sorunsuz takip ediyor. Bu script derlemeden
 * ONCE kaynaklari proje ici gercek bir dizine kopyalar; Vite yalnizca
 * `resources/js/panel/.modules/**` desenine bakar, yani symlink denklemden
 * tamamen cikar ve davranis her ortamda AYNI olur.
 *
 * Modulun `resources/js/panel` agaci OLDUGU GIBI kopyalanir (yalnizca Pages ve
 * Widgets degil): modul dosyalari birbirine goreli yollarla referans veriyor
 * (`../components/WeekSchedule.vue`, `./RepeaterField.vue`). Cekirdek SDK
 * importlari `~panel` alias'i uzerinden gittigi icin konumdan etkilenmez.
 *
 * Dizin adlari kucuk harfe indirgenir; bileşen adi `<modul>::<Yol>` sozlesmesi
 * zaten kucuk harf namespace kullaniyor (bkz. resources/js/panel/app.js).
 */
const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const modulesDir = join(root, 'Modules');
const target = join(root, 'resources/js/panel/.modules');

function panelSourceDirs() {
    if (! existsSync(modulesDir)) {
        return [];
    }

    // `readdirSync` symlink'i takip eder; glob kutuphanelerinin aksine.
    return readdirSync(realpathSync(modulesDir), { withFileTypes: true })
        .filter((entry) => entry.isDirectory() || entry.isSymbolicLink())
        .map((entry) => ({
            name: entry.name.toLowerCase(),
            path: join(realpathSync(modulesDir), entry.name, 'resources/js/panel'),
        }))
        .filter((module) => {
            try {
                return statSync(module.path).isDirectory();
            } catch {
                return false;
            }
        });
}

/*
 * Ayna her derlemede SIFIRDAN kurulur: silinmis bir modul sayfasinin bundle'da
 * hayalet olarak kalmasi, olmayan bir ekranin menude gorunmesi demek olurdu.
 */
rmSync(target, { recursive: true, force: true });

const modules = panelSourceDirs();

if (modules.length === 0) {
    // Modulsuz kurulum normaldir; glob bos gecer, cekirdek panel etkilenmez.
    mkdirSync(target, { recursive: true });
    console.log('[panel] modul panel kaynagi bulunamadi, ayna bos birakildi');
} else {
    for (const module of modules) {
        cpSync(module.path, join(target, module.name), {
            recursive: true,
            dereference: true,
            filter: (source) => ! source.includes('node_modules'),
        });
    }

    console.log(`[panel] ${modules.length} modul panel kaynagi aynalandi: ${modules.map((m) => m.name).join(', ')}`);
}
