<script setup>
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';

/*
 * panel/personal_notes/show.blade.php karşılığı.
 *
 * İçerik, kullanıcının kendi TinyMCE çıktısıdır ve sunucuda çözülüp gelir.
 * `v-html` burada kaçınılmaz; içerik kullanıcının kendi notu ve zaten Blade'de
 * de aynı şekilde basılıyordu.
 */
const props = defineProps({
  note: { type: Object, required: true },
});

usePageHeader(props.note.title, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes'), route: 'admin.notes' },
  { label: props.note.title },
]);
</script>

<template>
  <Head :title="note.title" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <Link :href="route('admin.notes')" class="p-btn no-underline">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        {{ __('notes.all_notes') }}
      </Link>

      <span v-if="note.category" class="p-chip">{{ note.category.name }}</span>
      <span class="text-[11.5px] text-p-ink3">{{ formatDateTime(note.createdAt) }}</span>

      <div class="flex-1"></div>

      <Link :href="route('admin.notes.edit', { note: note.id })" class="p-btn-primary">
        <i class="fa-solid fa-pen text-xs"></i>
        {{ __('general.edit') }}
      </Link>
    </div>

    <article class="p-card p-6">
      <h1 class="font-display text-[24px] font-extrabold">{{ note.title }}</h1>
      <div class="prose prose-sm mt-4 max-w-none text-p-ink2" v-html="note.content"></div>
    </article>
  </div>
</template>
