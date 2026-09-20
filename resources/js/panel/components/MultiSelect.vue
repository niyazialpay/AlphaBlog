<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { __ } from '../composables/useLang';

/**
 * select2'nin yerini alan çoklu seçim.
 *
 * select2 jQuery'ye bağlıydı ve panelde tek bir yerde (yazı editöründeki
 * kategori alanı) kullanılıyordu; yeni bir bağımlılık eklemek yerine yerel
 * bir bileşen yeterli.
 */
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const query = ref('');
const root = ref(null);

const selected = computed(() =>
  props.options.filter((option) => props.modelValue.includes(option.value)),
);

const filtered = computed(() => {
  const needle = query.value.trim().toLocaleLowerCase('tr');

  return props.options.filter(
    (option) => !needle || option.label.toLocaleLowerCase('tr').includes(needle),
  );
});

function toggle(option) {
  const next = props.modelValue.includes(option.value)
    ? props.modelValue.filter((value) => value !== option.value)
    : [...props.modelValue, option.value];

  emit('update:modelValue', next);
}

function outside(event) {
  if (root.value && !root.value.contains(event.target)) {
    open.value = false;
  }
}

onMounted(() => document.addEventListener('click', outside));
onUnmounted(() => document.removeEventListener('click', outside));
</script>

<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="flex min-h-9 w-full flex-wrap items-center gap-1 rounded-[9px] border border-p-line bg-p-panel2 px-2 py-1.5 text-left text-[12.5px]"
      @click="open = !open"
    >
      <span v-if="!selected.length" class="text-p-ink3">{{ placeholder }}</span>
      <span v-for="option in selected" :key="option.value" class="p-chip p-chip-accent">
        {{ option.label }}
        <i class="fa-solid fa-xmark ml-1 text-[9px]" @click.stop="toggle(option)"></i>
      </span>
      <i class="fa-solid fa-chevron-down ml-auto text-[10px] text-p-ink3"></i>
    </button>

    <div
      v-if="open"
      class="absolute z-30 mt-1 max-h-64 w-full animate-popIn overflow-auto rounded-xl border border-p-line bg-p-panel shadow-pop"
    >
      <div class="border-b border-p-line2 p-2">
        <input
          v-model="query"
          :placeholder="__('general.search')"
          class="p-input h-7 text-[12px]"
        />
      </div>
      <button
        v-for="option in filtered"
        :key="option.value"
        type="button"
        class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12.5px] hover:bg-p-panel2"
        :class="modelValue.includes(option.value) && 'bg-p-soft text-p-accent'"
        @click="toggle(option)"
      >
        <i
          class="fa-solid w-3 text-[10px]"
          :class="modelValue.includes(option.value) ? 'fa-check' : 'fa-minus opacity-0'"
        ></i>
        {{ option.label }}
      </button>
      <div v-if="!filtered.length" class="px-3 py-4 text-center text-[12px] text-p-ink3">
        {{ __('general.no_records') }}
      </div>
    </div>
  </div>
</template>
