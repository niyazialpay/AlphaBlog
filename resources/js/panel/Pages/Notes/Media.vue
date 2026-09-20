<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { pushToast } from '../../composables/useToast';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Lightbox from '../../components/Lightbox.vue';

/* panel/personal_notes/media.blade.php karşılığı (Fancybox yerine yerel lightbox). */
const props = defineProps({
  note: { type: Object, required: true },
  media: { type: Array, default: () => [] },
});

usePageHeader(__('post.media'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes'), route: 'admin.notes' },
  { label: props.note.title },
]);

const confirm = ref(null);
const preview = ref(null);

async function copy(url) {
  try {
    await navigator.clipboard.writeText(url);
    pushToast(__('media.url_copied'), 'success');
  } catch {
    pushToast(__('general.error'), 'error');
  }
}

async function destroy(item) {
  if (!(await confirm.value.ask({ body: __('post.delete_image_text') }))) {
    return;
  }

  router.post(
    route('admin.notes.media.delete', { note: props.note.id }),
    { media_id: item.id },
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('post.media')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <Link :href="route('admin.notes.edit', { note: note.id })" class="p-btn no-underline">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        {{ note.title }}
      </Link>
      <div class="flex-1"></div>
      <span class="p-chip">{{ media.length }}</span>
    </div>

    <div v-if="media.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="item in media" :key="item.id" class="p-card overflow-hidden">
        <button class="block w-full" @click="preview = item">
          <img :src="item.thumb" :alt="item.name" class="h-40 w-full object-cover" loading="lazy" />
        </button>

        <div class="flex items-center gap-1.5 px-2.5 py-2">
          <div class="min-w-0 flex-1 truncate text-[11.5px]">{{ item.name }}</div>
          <button class="p-icon-btn" :title="__('media.copy_to_clipboard')" @click="copy(item.url)">
            <i class="fa-solid fa-link"></i>
          </button>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('post.delete_image')"
            @click="destroy(item)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    </div>

    <div v-else class="p-card px-4 py-12 text-center text-p-ink3">
      {{ __('general.no_records') }}
    </div>

    <Lightbox :item="preview" @close="preview = null" />
    <ConfirmDialog ref="confirm" />
  </div>
</template>
