<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/*
 * panel/cloudflare/index.blade.php karşılığı.
 *
 * Blade'de iki jQuery `$.ajax` + `Swal.fire` vardı; uçlar artık Inertia
 * isteğinde `back()->with('success', …)` döndürüyor (R2), yani durum
 * sunucudan gelen prop'lardan tazeleniyor — elle DOM yaması yok.
 */
defineProps({
  zone: { type: Object, default: null },
});

usePageHeader('Cloudflare', [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: 'Cloudflare' },
]);

const confirm = ref(null);
const busy = ref(false);

function toggleDevelopment() {
  router.post(
    route('admin.cloudflare.toggle.development'),
    {},
    {
      preserveScroll: true,
      onStart: () => (busy.value = true),
      onFinish: () => (busy.value = false),
    },
  );
}

async function clearCache() {
  if (
    !(await confirm.value.ask({
      title: __('cache.clear_cache'),
      body: __('cache.are_you_sure'),
      confirmLabel: __('general.yes'),
      cancelLabel: __('general.no'),
      danger: false,
    }))
  ) {
    return;
  }

  router.post(
    route('admin.cloudflare.cache.clear'),
    {},
    {
      preserveScroll: true,
      onStart: () => (busy.value = true),
      onFinish: () => (busy.value = false),
    },
  );
}
</script>

<template>
  <Head title="Cloudflare" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[320px_minmax(0,1fr)]">
    <div class="p-card h-max p-4">
      <div class="mb-2 font-display text-[13.5px] font-bold">Nameservers</div>
      <ul class="flex flex-col gap-1 text-[12.5px] text-p-ink2">
        <li v-for="server in zone?.name_servers || []" :key="server" class="font-mono">
          {{ server }}
        </li>
        <li v-if="!zone?.name_servers?.length" class="text-p-ink3">{{ __('general.no_records') }}</li>
      </ul>
    </div>

    <div class="p-card h-max p-4">
      <dl class="grid grid-cols-[minmax(0,180px)_1fr] gap-y-2 text-[12.5px]">
        <dt class="font-semibold text-p-ink2">{{ __('language.status') }}</dt>
        <dd>
          <span class="p-chip" :class="zone?.status === 'active' && 'p-chip-accent'">
            {{ zone?.status === 'active' ? __('general.active') : __('general.passive') }}
          </span>
        </dd>

        <dt class="font-semibold text-p-ink2">{{ __('cloudflare.paused') }}</dt>
        <dd>{{ zone?.paused ? __('general.yes') : __('general.no') }}</dd>

        <dt class="font-semibold text-p-ink2">{{ __('cloudflare.development_mode') }}</dt>
        <dd>
          <span class="p-chip" :class="zone?.development_mode > 0 && 'p-chip-accent'">
            {{ zone?.development_mode > 0 ? __('cloudflare.active') : __('cloudflare.passive') }}
          </span>
        </dd>
      </dl>

      <div class="mt-4 flex flex-wrap gap-2">
        <button class="p-btn-primary" :disabled="busy" @click="toggleDevelopment">
          <i class="fa-solid fa-screwdriver-wrench text-xs"></i>
          {{
            zone?.development_mode > 0
              ? __('cloudflare.development_mode_passive')
              : __('cloudflare.development_mode_active')
          }}
        </button>
        <button class="p-btn" :disabled="busy" @click="clearCache">
          <i class="fa-solid fa-broom text-xs"></i> {{ __('cache.clear_cache') }}
        </button>
        <Link :href="route('cf.dns')" class="p-btn">
          <i class="fa-solid fa-diagram-project text-xs"></i> DNS
        </Link>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
