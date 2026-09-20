<script setup>
import { computed, ref } from 'vue';
import { __ } from '../composables/useLang';

/**
 * JSON gösterici.
 *
 * Eski log tabloları `'<pre>'.htmlspecialchars(json_encode(...))` ile sunucudan
 * HTML basıyordu. Artık prop gerçek bir nesne; burada metin olarak render edilir
 * (v-html yok) ve uzun kayıtlar katlanır.
 */
const props = defineProps({
  value: { type: [Object, Array, String, Number, Boolean, null], default: null },
  collapsedLines: { type: Number, default: 6 },
});

const open = ref(false);

const text = computed(() => {
  if (props.value === null || props.value === undefined || props.value === '') {
    return '';
  }

  if (typeof props.value === 'string') {
    return props.value;
  }

  return JSON.stringify(props.value, null, 2);
});

const lines = computed(() => text.value.split('\n'));
const truncated = computed(() => lines.value.length > props.collapsedLines);
const shown = computed(() =>
  open.value || !truncated.value ? text.value : lines.value.slice(0, props.collapsedLines).join('\n'),
);
</script>

<template>
  <div v-if="text" class="min-w-[180px]">
    <pre
      class="max-w-[320px] overflow-x-auto whitespace-pre-wrap break-all rounded-lg bg-p-panel2 px-2 py-1.5 font-mono text-[11px] leading-relaxed text-p-ink2"
      >{{ shown }}</pre
    >
    <button
      v-if="truncated"
      class="mt-1 text-[10.5px] font-semibold text-p-accent"
      @click="open = !open"
    >
      {{ open ? __('general.less') : __('general.more') }}
    </button>
  </div>
  <span v-else class="text-p-ink3">—</span>
</template>
