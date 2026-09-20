<script setup>
/**
 * `panel/auth/layouts/app.blade.php` karşılığı.
 *
 * Inertia layout OLARAK atanmaz: auth sayfaları `layout: null` taşır (sidebar'lı
 * PanelLayout'a düşmemeleri için) ve kendi kabuklarını burayla sarar. Böylece
 * AdminLTE + Bootstrap + jQuery + toastr CDN'leri auth ekranlarından da düşer.
 */
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import FlashToast from './FlashToast.vue';
import { useTheme } from '../composables/useTheme';

/*
 * Toast kabı burada: auth sayfaları `layout: null` taşıdığı için PanelLayout —
 * ve onun içindeki FlashToast — mount edilmiyor. Eski kabukta toastr sayfa
 * başına yükleniyordu; giriş/parola geri bildirimi aksi halde sessiz kalır.
 */
defineProps({
  title: { type: String, default: '' },
});

const page = usePage();
const { theme } = useTheme();

/*
 * `siteLogo` bir NESNE: {light, dark}. Auth ekranlari da <html> uzerindeki
 * data-panel-theme'i aliyor (FOUC script'i her sayfada calisiyor), bu yuzden
 * logo burada da tema ile secilir. Tek logo yuklenmisse o kullanilir.
 */
const brandLogo = computed(() => {
  const logo = page.props.siteLogo || {};

  return (theme.value === 'dark' ? logo.dark || logo.light : logo.light || logo.dark) || null;
});
</script>

<template>
  <div class="grid min-h-screen place-items-center bg-p-bg px-4 py-10">
    <div class="w-full max-w-sm">
      <div class="text-center">
        <img
          v-if="brandLogo"
          :src="brandLogo"
          :alt="page.props.siteName"
          height="60"
          class="mx-auto h-[60px] w-auto"
        />
        <div class="mt-3 font-display text-[15px] font-extrabold">{{ page.props.siteName }}</div>
        <div v-if="title" class="mt-1 text-[12.5px] text-p-ink2">{{ title }}</div>
      </div>

      <div class="p-card mt-5 p-4">
        <slot />
      </div>

      <div class="mt-3 text-center">
        <slot name="footer" />
      </div>
    </div>

    <FlashToast />
  </div>
</template>
