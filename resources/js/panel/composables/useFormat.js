import { usePage } from '@inertiajs/vue3';

/**
 * Tarih biçimlendirme.
 *
 * Sunucu artık ISO-8601 gönderiyor (önceden ekran ekran farklı biçimlerdi:
 * 'Y-m-d H:i:s', 'd.m.Y H:i:s', 'd M. Y D. H:i:s'). Biçimlendirme istemciye
 * taşındığı için saat dilimi AÇIKÇA verilmeli — aksi halde tarayıcının yerel
 * saatine düşer ve başka saat diliminden bakan kullanıcıda damgalar kayar.
 */
function timezone() {
    return usePage().props.timezone || undefined;
}

function locale() {
    return usePage().props.currentLanguage?.code || undefined;
}

export function formatDateTime(value, options = {}) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(locale(), {
        dateStyle: 'medium',
        timeStyle: 'medium',
        timeZone: timezone(),
        ...options,
    }).format(new Date(value));
}

export function formatDate(value, options = {}) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(locale(), {
        dateStyle: 'medium',
        timeZone: timezone(),
        ...options,
    }).format(new Date(value));
}

export function useFormat() {
    return { formatDate, formatDateTime };
}
