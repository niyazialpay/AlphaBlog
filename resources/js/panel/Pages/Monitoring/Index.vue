<script setup>
import { computed } from 'vue';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { isMigrated } from '../../composables/useNavigate';

/*
 * panel/monitoring.blade.php karşılığı.
 *
 * Dört route (Pulse / Telescope / Horizon / Log Viewer) tek bileşeni kullanır.
 * iframe'ler aynı kaynak; SecurityHeaders `X-Frame-Options: SAMEORIGIN`
 * gönderdiği için çalışır — bu başlık daraltılırsa ekran boş kalır.
 */
const props = defineProps({
  // Pulse/Telescope kapaliysa null gelir.
  iframeUrl: { type: String, default: null },
  title: { type: String, required: true },
});

usePageHeader(props.title, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('settings.monitoring') },
]);

const tabs = computed(() => [
  { label: 'Pulse', icon: 'fa-solid fa-heart-pulse', routeName: 'admin.monitoring.pulse' },
  { label: 'Telescope', icon: 'fa-solid fa-binoculars', routeName: 'admin.monitoring.telescope' },
  { label: 'Horizon', icon: 'fa-brands fa-laravel', routeName: 'admin.monitoring.horizon' },
  { label: __('logs.logs'), icon: 'fa-solid fa-clipboard-list', routeName: 'admin.monitoring.logs' },
]);

function active(routeName) {
  return route().current(routeName);
}
</script>

<template>
  <Head :title="title" />

  <div class="flex min-h-0 flex-1 flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap gap-1.5">
      <!--
        Sekmeler arası geçiş: hedefler de bu bileşeni kullandığı için Inertia
        ziyareti güvenli; yine de defter (config/panel_inertia_routes.php)
        sorgulanır, taşınmamışsa tam sayfa yüklemesine düşer.
      -->
      <component
        :is="isMigrated(tab.routeName) ? 'Link' : 'a'"
        v-for="tab in tabs"
        :key="tab.routeName"
        :href="route(tab.routeName)"
        class="p-tab no-underline"
        :class="active(tab.routeName) && 'p-tab-active'"
      >
        <i :class="tab.icon" class="mr-1.5 text-[11px]"></i>
        {{ tab.label }}
      </component>

      <div class="flex-1"></div>

      <a v-if="iframeUrl" :href="iframeUrl" target="_blank" rel="noopener" class="p-btn no-underline">
        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
        {{ __('general.open_in_new_tab') }}
      </a>
    </div>

    <div class="p-card min-h-0 flex-1 overflow-hidden">
      <iframe
        v-if="iframeUrl"
        :src="iframeUrl"
        :title="title"
        class="h-full min-h-[70vh] w-full border-0 bg-p-panel"
      ></iframe>
      <div v-else class="grid min-h-[40vh] place-items-center px-6 text-center">
        <div>
          <i class="fa-solid fa-plug-circle-xmark text-2xl text-p-ink3"></i>
          <div class="mt-3 text-[12.5px] text-p-ink2">{{ __('general.not_available') }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
