<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/* panel/personal_notes/categories/index.blade.php karşılığı. */
const props = defineProps({
  categories: { type: Array, default: () => [] },
  category: { type: Object, default: null },
});

usePageHeader(__('categories.categories'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes'), route: 'admin.notes' },
  { label: __('categories.categories') },
]);

const confirm = ref(null);

const form = useForm({ name: props.category?.name || '' });

function submit() {
  const url = props.category
    ? route('admin.notes.categories.update', { category: props.category.id })
    : route('admin.notes.categories.create');

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
}

async function destroy(item) {
  // Sunucu, icinde not olan kategoriyi zaten reddediyor; uyari yalnizca ondan once.
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.notes.categories.delete', { category: item.id }),
    {},
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('categories.categories')" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[minmax(0,1fr)_340px]">
    <div class="p-card divide-y divide-p-line2">
      <div
        v-for="item in categories"
        :key="item.id"
        class="flex items-center gap-3 px-4 py-2.5"
      >
        <span class="flex-1 truncate text-[12.5px]">{{ item.name }}</span>
        <span class="p-chip tabular-nums">{{ item.notes_count }}</span>

        <div class="flex gap-1">
          <Link
            :href="route('admin.notes.category', { category: item.id })"
            class="p-icon-btn"
            :title="__('general.edit')"
          >
            <i class="fa-solid fa-pen"></i>
          </Link>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="destroy(item)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>

      <div v-if="!categories.length" class="px-4 py-10 text-center text-p-ink3">
        {{ __('general.no_records') }}
      </div>
    </div>

    <div class="p-card h-max p-4">
      <div class="mb-3 flex items-center gap-2">
        <div class="flex-1 font-display text-[13.5px] font-bold">
          {{ category ? __('general.edit') : __('general.new') }}
        </div>
        <Link v-if="category" :href="route('admin.notes.categories')" class="p-icon-btn">
          <i class="fa-solid fa-xmark"></i>
        </Link>
      </div>

      <div class="flex flex-col gap-3">
        <FormField v-model="form.name" :label="__('categories.name')" :error="form.errors.name" />
        <button class="p-btn-primary justify-center" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          {{ category ? __('general.update') : __('general.create') }}
        </button>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
