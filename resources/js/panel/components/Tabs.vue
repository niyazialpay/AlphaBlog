<script setup>
/**
 * Sekme şeridi. Settings (9 sekme) ve Profile (5 sekme) ekranları buna dayanır.
 *
 * `queryKey` verilirse aktif sekme URL'de taşınır — mevcut `?tab=` sözleşmesi
 * korunmalı: Cloudflare controller'ları geçersiz kimlik bilgisinde
 * `redirect()->route('admin.settings', ['tab' => 'cloudflare'])` yapıyor.
 */
import { router } from '@inertiajs/vue3';

const props = defineProps({
  modelValue: { type: String, required: true },
  tabs: { type: Array, required: true }, // [{ value, label, icon?, badge? }]
  queryKey: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

function select(value) {
  emit('update:modelValue', value);

  if (!props.queryKey) {
    return;
  }

  const url = new URL(window.location.href);
  url.searchParams.set(props.queryKey, value);
  router.visit(url.toString(), { preserveState: true, preserveScroll: true, replace: true });
}
</script>

<template>
  <div class="flex flex-wrap gap-1.5 overflow-x-auto">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      class="p-tab whitespace-nowrap"
      :class="modelValue === tab.value && 'p-tab-active'"
      @click="select(tab.value)"
    >
      <i v-if="tab.icon" :class="tab.icon" class="mr-1.5 text-[11px]"></i>
      {{ tab.label }}
      <span
        v-if="tab.badge"
        class="ml-1.5 rounded-full bg-p-danger px-1.5 text-[10px] font-bold text-white"
        >{{ tab.badge }}</span
      >
    </button>
  </div>
</template>
