<script setup>
import { ref } from 'vue';
import Modal from './Modal.vue';
import { __ } from '../composables/useLang';

/*
 * 186 `Swal.fire({ showCancelButton: true })` çağrısının yerini alır.
 *
 * Kullanım:
 *   const confirm = ref(null)
 *   if (await confirm.value.ask({ title: __('general.are_you_sure'), danger: true })) { ... }
 */
const open = ref(false);
const options = ref({});
let resolver = null;

function ask(userOptions = {}) {
  options.value = {
    title: __('general.are_you_sure'),
    body: '',
    confirmLabel: __('general.yes'),
    cancelLabel: __('general.no'),
    danger: true,
    icon: 'fa-solid fa-triangle-exclamation',
    ...userOptions,
  };

  open.value = true;

  return new Promise((resolve) => {
    resolver = resolve;
  });
}

function settle(result) {
  open.value = false;
  resolver?.(result);
  resolver = null;
}

defineExpose({ ask });
</script>

<template>
  <Modal
    :open="open"
    :title="options.title"
    :icon="options.icon"
    :danger="options.danger"
    width="440px"
    @update:open="settle(false)"
  >
    <p v-if="options.body" class="text-[12.5px] leading-relaxed text-p-ink2">{{ options.body }}</p>

    <template #footer>
      <button class="p-btn" @click="settle(false)">{{ options.cancelLabel }}</button>
      <button
        class="p-btn-primary"
        :class="options.danger && '!bg-p-danger !text-white'"
        @click="settle(true)"
      >
        {{ options.confirmLabel }}
      </button>
    </template>
  </Modal>
</template>
