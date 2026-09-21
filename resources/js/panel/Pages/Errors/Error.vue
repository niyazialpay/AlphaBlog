<script setup>
import { computed } from 'vue';
import { __ } from '../../composables/useLang';

/*
 * bootstrap/app.php içindeki withExceptions()->respond() yalnızca PANEL Inertia
 * istekleri için bu bileşeni render eder; ön yüz temasının hata görünümleri
 * olduğu gibi kalır.
 */
const props = defineProps({
  /*
   * Zorunlu DEGIL: app.js, bundle'da bulunamayan bir sayfa icin de bu bileseni
   * render ediyor (bkz. resolve()) ve o durumda prop'lar eksik sayfaninkilerdir.
   */
  status: { type: Number, default: 404 },
});

const title = computed(
  () =>
    ({
      403: __('general.forbidden'),
      404: __('general.not_found'),
      500: __('general.server_error'),
      503: __('general.service_unavailable'),
    })[props.status] || __('general.server_error'),
);

const icon = computed(
  () =>
    ({
      403: 'fa-solid fa-lock',
      404: 'fa-solid fa-compass',
      500: 'fa-solid fa-triangle-exclamation',
      503: 'fa-solid fa-plug-circle-xmark',
    })[props.status] || 'fa-solid fa-triangle-exclamation',
);
</script>

<template>
  <Head :title="`${status}`" />

  <div class="grid flex-1 place-items-center p-[22px]">
    <div class="p-card w-full max-w-md px-6 py-10 text-center">
      <i :class="icon" class="text-3xl text-p-ink3"></i>
      <div class="mt-4 font-display text-4xl font-extrabold tabular-nums">{{ status }}</div>
      <div class="mt-2 text-[13px] text-p-ink2">{{ title }}</div>

      <Link :href="route('admin.index')" class="p-btn-primary mt-6 inline-flex">
        <i class="fa-solid fa-gauge-high text-xs"></i>
        {{ __('dashboard.dashboard') }}
      </Link>
    </div>
  </div>
</template>
