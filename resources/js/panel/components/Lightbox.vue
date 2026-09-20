<script setup>
import { onMounted, onUnmounted } from 'vue';

/**
 * Fancybox 5'in yerini alan küçük lightbox.
 *
 * Medya galerileri (yazı ve not medyası) CDN'den Fancybox çekiyordu; iki ekran
 * için bir galeri kütüphanesi taşımaya değmez.
 */
defineProps({
  item: { type: Object, default: null },
});

const emit = defineEmits(['close']);

function onKey(event) {
  if (event.key === 'Escape') {
    emit('close');
  }
}

onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => window.removeEventListener('keydown', onKey));
</script>

<template>
  <div
    v-if="item"
    class="fixed inset-0 z-[70] grid place-items-center bg-black/80 p-6"
    @click="emit('close')"
  >
    <figure class="max-h-full max-w-full" @click.stop>
      <img :src="item.url" :alt="item.name" class="max-h-[80vh] max-w-full rounded-xl" />
      <figcaption class="mt-2 text-center text-[12px] text-white/70">{{ item.name }}</figcaption>
    </figure>

    <button
      class="absolute right-5 top-5 grid h-9 w-9 place-items-center rounded-full bg-white/10 text-white"
      @click="emit('close')"
    >
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
</template>
