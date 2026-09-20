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

/**
 * trans_choice karşılığı: Illuminate\Translation\MessageSelector semantiğiyle birebir.
 *
 * Desteklenenler:
 *   - Tam eşleşme:        {0}, {1}, {42}
 *   - Aralık:             [1,19], [20,*], [*,4]
 *   - Koşulsuz "tekil|çoğul" (koşul yoksa count === 1 ? tekil : çoğul)
 *
 * Laravel'in kendi seçicisi de aynı regex sözleşmesini kullanır (bkz.
 * Illuminate\Translation\MessageSelector::extractFromString), böylece lang
 * dosyaları sunucu ve istemci tarafında birebir aynı davranır. Seçilen
 * segmentin koşul öneki (`{1} `, `[2,*] `) burada temizlenir; :count ve diğer
 * replace anahtarları seçimden SONRA uygulanır (Laravel'de de sıra böyledir).
 */
const CONDITION_PATTERN = /^[{[]([^{}[\]]*)[}\]](.*)$/s;

function parseCondition(segment) {
    const match = segment.match(CONDITION_PATTERN);

    return match ? { condition: match[1], text: match[2] } : null;
}

function conditionMatches(condition, count) {
    if (condition.includes(',')) {
        const [from, to] = condition.split(',');

        if (to === '*') {
            return count >= Number(from);
        }

        if (from === '*') {
            return count <= Number(to);
        }

        return count >= Number(from) && count <= Number(to);
    }

    return Number(condition) === count;
}

export function transChoice(key, count, replace = {}) {
    const segments = __(key).split('|');

    let chosen = null;

    for (const segment of segments) {
        const parsed = parseCondition(segment);

        if (parsed && conditionMatches(parsed.condition, count)) {
            chosen = parsed.text.trim();
            break;
        }
    }

    if (chosen === null) {
        const stripped = segments.map((segment) => parseCondition(segment)?.text ?? segment);

        chosen = (stripped.length === 1 ? stripped[0] : stripped[count === 1 ? 0 : 1]).trim();
    }

    return Object.entries({ count, ...replace }).reduce(
        (text, [name, value]) => text.replaceAll(`:${name}`, value),
        chosen,
    );
}

export function useLang() {
    return { __, transChoice };
}
