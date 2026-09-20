<script setup>
import { __ } from '../composables/useLang';
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
  icon: { type: String, default: '' },
  width: { type: String, default: '560px' },
  danger: { type: Boolean, default: false },
});
const emit = defineEmits(['update:open', 'confirm']);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[60] flex items-start justify-center overflow-auto bg-black/50 px-4 pb-4 pt-[8vh]"
       @click="emit('update:open', false)">
    <div class="animate-popIn overflow-hidden rounded-2xl border border-p-line bg-p-panel shadow-pop"
         :style="{ width: `min(${width}, 96vw)` }" @click.stop>
      <div class="flex items-center gap-2.5 border-b border-p-line2 px-4 py-3.5">
        <i v-if="icon" :class="icon" :style="{ color: danger ? 'rgb(var(--p-danger))' : 'rgb(var(--p-warn))' }"></i>
        <div class="flex-1 font-display text-[13.5px] font-bold">{{ title }}</div>
        <button class="border-0 bg-transparent text-p-ink3 hover:text-p-ink" @click="emit('update:open', false)">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-4"><slot /></div>

      <div class="flex justify-end gap-2 border-t border-p-line2 bg-p-panel2 px-4 py-3">
        <slot name="footer">
          <button class="p-btn" @click="emit('update:open', false)">{{ __('general.cancel') }}</button>
          <button class="p-btn-primary" :class="danger && '!bg-p-danger !text-white'" @click="emit('confirm')">
            {{ danger ? __('general.delete') : __('general.save') }}
          </button>
        </slot>
      </div>
    </div>
  </div>
</template>
