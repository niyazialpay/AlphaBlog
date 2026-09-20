<script setup>
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';

/*
 * panel/about.blade.php karşılığı.
 *
 * 'Debug Mode' artık HTML rozet stringi değil bool prop — prop'lar veri taşır,
 * markup değil (tek istisna: post geçmişindeki TextDiff çıktısı).
 */
defineProps({
  debug: { type: Boolean, default: false },
  systemInfo: { type: Object, required: true },
});

usePageHeader(__('general.about'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('general.about') },
]);
</script>

<template>
  <Head :title="__('general.about')" />

  <div class="p-[22px]">
    <div class="p-card mx-auto max-w-3xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[560px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="w-[30%] px-3.5 py-2.5 font-semibold">{{ __('general.feature') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('general.version_info') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-t border-p-line2">
              <td class="px-3.5 py-2.5 font-semibold">Debug Mode</td>
              <td class="px-3.5 py-2.5">
                <span
                  class="p-chip"
                  :class="debug ? '!bg-p-danger !text-white' : '!bg-p-ok !text-white'"
                >
                  {{ debug ? 'Enabled' : 'Disabled' }}
                </span>
              </td>
            </tr>
            <tr
              v-for="(value, key) in systemInfo"
              :key="key"
              class="border-t border-p-line2 hover:bg-p-panel2"
            >
              <td class="whitespace-nowrap px-3.5 py-2.5 font-semibold align-top">{{ key }}</td>
              <td class="break-all px-3.5 py-2.5 text-p-ink2">{{ value }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
