import { onUnmounted, ref } from 'vue';

/**
 * Üst bardaki sayfa başlığı ve breadcrumb.
 *
 * Kalıcı layout'ta named slot çalışmaz: Inertia sayfayı layout'un DEFAULT slot'una
 * koyar, sayfadan layout'a `<template #title>` geçmez. Bu yüzden başlık paylaşılan
 * bir ref üzerinden akar.
 *
 * Her sayfa setup içinde çağırır:
 *   usePageHeader(__('general.about'), [{ label: __('dashboard.dashboard'), route: 'admin.index' }])
 */
export const pageHeader = ref({ title: '', crumbs: [] });

export function usePageHeader(title, crumbs = []) {
    pageHeader.value = { title, crumbs };

    // Sayfa degisince bayat baslik kalmasin.
    onUnmounted(() => {
        pageHeader.value = { title: '', crumbs: [] };
    });

    return pageHeader;
}
