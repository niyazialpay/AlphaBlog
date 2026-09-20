<script setup>
import { computed } from 'vue';
import { __ } from '../composables/useLang';

/**
 * Laravel paginator bağlantı şeridi.
 *
 * `paginate()` sonucu Inertia prop'u olarak geçildiğinde `links`, `from`, `to`,
 * `total` alanlarını taşır. Sayfa değişimi kısmi yeniden yükleme ile yapılır:
 * URL paylaşılabilir kalır, geri tuşu doğru çalışır.
 */
const props = defineProps({
  links: { type: Array, default: () => [] },
  meta: { type: Object, default: () => ({}) },
  only: { type: Array, default: () => [] },
});

const visible = computed(() => props.links.filter((link) => link.url || link.active));

function label(link) {
  return link.label
    .replace('&laquo; Previous', '‹')
    .replace('Next &raquo;', '›')
    .replace('pagination.previous', '‹')
    .replace('pagination.next', '›');
}
</script>

<template>
  <div
    v-if="visible.length > 1"
    class="flex flex-wrap items-center gap-2 text-xs text-p-ink3"
  >
    <span v-if="meta.total !== undefined" class="tabular-nums">
      {{ meta.from || 0 }}–{{ meta.to || 0 }} / {{ meta.total }} {{ __('general.records') }}
    </span>

    <div class="flex-1"></div>

    <template v-for="(link, index) in links" :key="index">
      <Link
        v-if="link.url"
        :href="link.url"
        :only="only.length ? only : undefined"
        preserve-scroll
        class="grid h-7 min-w-[28px] place-items-center rounded-lg border border-p-line px-2 text-[11.5px] no-underline"
        :class="link.active ? 'border-transparent bg-p-soft font-bold text-p-accent' : 'bg-p-panel text-p-ink2'"
        v-html="label(link)"
      />
      <span
        v-else
        class="grid h-7 min-w-[28px] place-items-center px-2 text-[11.5px] text-p-ink3 opacity-50"
        v-html="label(link)"
      />
    </template>
  </div>
</template>
