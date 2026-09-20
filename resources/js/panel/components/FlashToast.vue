<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toasts, pushToast, dismissToast } from '../composables/useToast';

/*
 * toastr + SweetAlert2 "toast" kullanımlarının yerini alır.
 * Sunucu tarafı flash anahtarları base.blade.php ile aynı: success / error /
 * warning / message, ayrıca Fortify 2FA akışlarının kullandığı `status`.
 */
const page = usePage();

const ICONS = {
  success: 'fa-solid fa-circle-check',
  error: 'fa-solid fa-circle-exclamation',
  warning: 'fa-solid fa-triangle-exclamation',
  info: 'fa-solid fa-circle-info',
};

const COLORS = {
  success: 'rgb(var(--p-ok))',
  error: 'rgb(var(--p-danger))',
  warning: 'rgb(var(--p-warn))',
  info: 'rgb(var(--p-accent))',
};

watch(
  () => page.props.flash,
  (flash) => {
    if (!flash) {
      return;
    }

    pushToast(flash.success, 'success');
    pushToast(flash.error, 'error');
    pushToast(flash.warning, 'warning');
    pushToast(flash.message, 'info');
    pushToast(flash.status, 'info');
  },
  { immediate: true, deep: true },
);
</script>

<template>
  <div class="pointer-events-none fixed bottom-4 right-4 z-[80] flex w-[min(360px,92vw)] flex-col gap-2">
    <div
      v-for="toast in toasts"
      :key="toast.id"
      class="pointer-events-auto flex animate-slideIn items-start gap-2.5 rounded-2xl border border-p-line bg-p-panel px-3.5 py-3 shadow-pop"
    >
      <i :class="ICONS[toast.type]" class="mt-0.5 text-[13px]" :style="{ color: COLORS[toast.type] }"></i>
      <div class="min-w-0 flex-1 text-[12.5px] leading-relaxed text-p-ink">{{ toast.message }}</div>
      <button
        class="border-0 bg-transparent text-[12px] text-p-ink3 hover:text-p-ink"
        @click="dismissToast(toast.id)"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>
</template>
