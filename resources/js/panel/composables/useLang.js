/**
 * Panel çeviri yardımcısı — Blade'deki @lang / __() ile birebir aynı davranır.
 *
 * Torba, kök blade'de `window.__panelLang` olarak bir kez basılır
 * (App\Support\Panel\PanelLang). Inertia paylaşılan prop'u DEĞİLDİR: prop olsaydı
 * her ziyarette yeniden serileştirilirdi.
 *
 * Anahtar biçimi her zaman ilk noktadan bölünür:
 *   'post.blogs'                  → bag['post'].blogs
 *   'logs.action_list.update'     → bag['logs'].action_list.update
 *   'valefix::panel.customers'    → bag['valefix::panel'].customers
 *
 * Anahtar bulunamazsa anahtarın kendisi döner (Laravel de böyle yapar) — arayüz
 * hiçbir zaman boş kutu göstermez, eksik çeviri hemen görünür olur.
 */

function bag() {
    return (typeof window !== 'undefined' && window.__panelLang) || {};
}

export function __(key, replace = {}) {
    if (typeof key !== 'string' || key === '') {
        return key;
    }

    const separator = key.indexOf('.');

    if (separator === -1) {
        return key;
    }

    const namespace = key.slice(0, separator);
    const path = key.slice(separator + 1);

    const value = path
        .split('.')
        .reduce((carry, segment) => (carry == null ? undefined : carry[segment]), bag()[namespace]);

    if (typeof value !== 'string') {
        return key;
    }

    return Object.entries(replace).reduce(
        (text, [name, replacement]) => text.replaceAll(`:${name}`, replacement),
        value,
    );
}

/** trans_choice karşılığı: "tekil|çoğul" biçimini sayıya göre çözer. */
export function transChoice(key, count, replace = {}) {
    const value = __(key, { count, ...replace });

    if (!value.includes('|')) {
        return value;
    }

    const [singular, plural] = value.split('|');

    return (count === 1 ? singular : plural).trim();
}

export function useLang() {
    return { __, transChoice };
}
