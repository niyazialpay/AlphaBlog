import { ref } from 'vue';

/**
 * toastr'ın yerini alır. Tek bir paylaşılan yığın; FlashToast bileşeni render eder.
 *
 * Flash mesajları (Inertia `flash` prop'u) FlashToast tarafından otomatik itilir;
 * bu composable yalnızca ekran içi anlık bildirimler için (ör. "URL kopyalandı").
 */
let sequence = 0;

export const toasts = ref([]);

export function pushToast(message, type = 'success', timeout = 4000) {
    if (!message) {
        return;
    }

    const id = ++sequence;

    toasts.value.push({ id, message, type });

    if (timeout > 0) {
        setTimeout(() => dismissToast(id), timeout);
    }

    return id;
}

export function dismissToast(id) {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

export function useToast() {
    return { toasts, pushToast, dismissToast };
}
