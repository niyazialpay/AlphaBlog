<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/* panel/personal_notes/index.blade.php karşılığı. */
const props = defineProps({
  notes: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

usePageHeader(__('notes.all_notes'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes') },
]);

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const confirm = ref(null);

function reload(overrides = {}) {
  router.get(
    route('admin.notes'),
    {
      search: search.value || undefined,
      category: category.value || undefined,
      ...overrides,
    },
    { only: ['notes', 'filters'], preserveState: true, preserveScroll: true, replace: true },
  );
}

let timer = null;
watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => reload({ page: 1 }), 250);
});
watch(category, () => reload({ page: 1 }));

async function destroy(note) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('admin.notes.delete', { note: note.id }), {}, { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('notes.all_notes')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <select v-model="category" class="p-input w-auto">
        <option value="">{{ __('notes.select_category') }}</option>
        <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
      </select>

      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[180px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>

      <div class="flex-1"></div>

      <Link :href="route('admin.notes.categories')" class="p-btn no-underline">
        <i class="fa-solid fa-list text-[11px]"></i>
        {{ __('categories.categories') }}
      </Link>
      <Link :href="route('admin.notes.create')" class="p-btn-primary">
        <i class="fa-solid fa-plus text-xs"></i>
        {{ __('general.new') }}
      </Link>
    </div>

    <div class="p-card divide-y divide-p-line2">
      <div
        v-for="note in notes.data"
        :key="note.id"
        class="flex flex-wrap items-center gap-3 px-4 py-3"
      >
        <div class="min-w-0 flex-1">
          <Link
            :href="route('admin.notes.show', { note: note.id })"
            class="truncate text-[12.5px] font-semibold"
            >{{ note.title }}</Link
          >
          <div class="mt-0.5 flex flex-wrap items-center gap-2 text-[11px] text-p-ink3">
            <span v-if="note.category" class="p-chip">{{ note.category.name }}</span>
            <span>{{ formatDateTime(note.createdAt) }}</span>
          </div>
        </div>

        <div class="flex gap-1">
          <Link
            :href="route('admin.notes.edit', { note: note.id })"
            class="p-icon-btn"
            :title="__('general.edit')"
          >
            <i class="fa-solid fa-pen"></i>
          </Link>
          <Link
            :href="route('admin.notes.media', { note: note.id })"
            class="p-icon-btn"
            :title="__('post.media')"
          >
            <i class="fa-solid fa-images"></i>
          </Link>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="destroy(note)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>

      <div v-if="!notes.data.length" class="px-4 py-10 text-center text-p-ink3">
        {{ __('notes.not_found') }}
      </div>
    </div>

    <Pagination :links="notes.links" :meta="notes" :only="['notes', 'filters']" />

    <ConfirmDialog ref="confirm" />
  </div>
</template>
