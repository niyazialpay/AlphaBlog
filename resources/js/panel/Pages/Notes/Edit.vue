<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { pushToast } from '../../composables/useToast';
import TinyMceEditor from '../../components/TinyMceEditor.vue';
import FormField from '../../components/FormField.vue';

/*
 * panel/personal_notes/add-edit.blade.php karşılığı.
 *
 * Görsel yükleme uçları POST tarafının AYNADAKİ HALİ: burada PARAMETRESİZ route
 * adlandırılmış (`admin.notes.editor.image.upload`), `{note}`'lu olan isimsiz.
 * Bu yüzden not id'si varken URL string ile kurulur — route adı değiştirmek
 * sözleşmeyi bozar.
 */
const props = defineProps({
  note: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
});

const noteId = ref(props.note.id || null);

const title = computed(() => (noteId.value ? __('general.edit') : __('general.new')));

usePageHeader(title.value, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes'), route: 'admin.notes' },
  { label: props.note.title || __('general.new') },
]);

const form = useForm({
  title: props.note.title || '',
  content: props.note.content || '',
  category_id: props.note.category_id || '',
});

const uploadUrl = computed(() =>
  noteId.value
    ? `${route('admin.notes.editor.image.upload')}/${noteId.value}`
    : route('admin.notes.editor.image.upload'),
);

function uploadMeta() {
  // Sunucu yalnizca `file` ve `title` okuyor (post tarafindan farkli).
  return { title: form.title };
}

function onUploaded(payload) {
  if (!noteId.value && payload?.note_id) {
    noteId.value = payload.note_id;
    pushToast(__('post.draft_created'), 'info');
  }
}

function submit() {
  const url = noteId.value
    ? route('admin.notes.edit.save', { note: noteId.value })
    : route('admin.notes.save');

  form.post(url, { preserveScroll: true });
}
</script>

<template>
  <Head :title="title" />

  <div class="flex min-h-0 flex-1 flex-col gap-3.5 p-[22px]">
    <input
      v-model="form.title"
      :placeholder="__('post.title')"
      class="w-full border-0 bg-transparent font-display text-[26px] font-extrabold text-p-ink outline-none placeholder:text-p-ink3"
    />
    <div v-if="form.errors.title" class="text-[11px] font-semibold text-p-danger">
      {{ form.errors.title }}
    </div>

    <div class="flex flex-wrap items-end gap-3">
      <div class="w-[240px]">
        <FormField
          v-model="form.category_id"
          type="select"
          :label="__('notes.category')"
          :error="form.errors.category_id"
          :options="[
            { value: '', label: __('notes.select_category') },
            ...categories.map((c) => ({ value: c.id, label: c.name })),
          ]"
        />
      </div>

      <div class="flex-1"></div>

      <Link
        v-if="noteId"
        :href="route('admin.notes.media', { note: noteId })"
        class="p-btn no-underline"
      >
        <i class="fa-solid fa-images text-[11px]"></i> {{ __('post.media') }}
      </Link>

      <button class="p-btn-primary" :disabled="form.processing" @click="submit">
        <i class="fa-solid fa-floppy-disk text-xs"></i>
        {{ noteId ? __('general.update') : __('general.create') }}
      </button>
    </div>

    <TinyMceEditor
      v-model="form.content"
      :upload-url="uploadUrl"
      :language="$page.props.currentLanguage?.code || 'tr'"
      :height="600"
      :ai-enabled="false"
      :upload-meta="uploadMeta"
      @uploaded="onUploaded"
    />
    <div v-if="form.errors.content" class="text-[11px] font-semibold text-p-danger">
      {{ form.errors.content }}
    </div>
  </div>
</template>
