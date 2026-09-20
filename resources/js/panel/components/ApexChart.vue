<script setup>
/**
 * ApexCharts sarmalayıcı.
 *
 * Eski panel `https://cdn.jsdelivr.net/npm/apexcharts` script tag'ini (sürümsüz,
 * bütünlük doğrulaması yok) kimliği doğrulanmış panele yüklüyordu; artık npm
 * bağımlılığı. Kütüphane yalnızca grafik içeren ekranlar açıldığında
 * indirilecek şekilde dinamik import edilir.
 *
 * Tema: blade'de `#dark-mode-switcher-button` tıklamasına bağlı
 * `updateChartThemeMode()` vardı — burada panel teması izlenip `theme.mode`
 * yeniden uygulanır.
 */
import { computed, defineAsyncComponent } from 'vue';
import { theme as panelTheme } from '../composables/useTheme';

const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));

const props = defineProps({
  type: { type: String, required: true },
  series: { type: Array, required: true },
  options: { type: Object, default: () => ({}) },
  height: { type: [Number, String], default: 320 },
});

const merged = computed(() => ({
  ...props.options,
  chart: { ...(props.options.chart || {}), toolbar: { show: false } },
  theme: { ...(props.options.theme || {}), mode: panelTheme.value === 'dark' ? 'dark' : 'light' },
  // Şeffaf arka plan: kart zaten p-card arka planını veriyor.
  ...(props.options.chart?.background ? {} : {}),
}));
</script>

<template>
  <VueApexCharts
    :key="panelTheme"
    :type="type"
    :series="series"
    :options="merged"
    :height="height"
  />
</template>
