<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/* panel/menu/index.blade.php karşılığı. */
const props = defineProps({
  menus: { type: Array, default: () => [] },
  menu: { type: Object, default: null },
  languages: { type: Array, default: () => [] },
});

usePageHeader(__('menu.menu'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('menu.menu') },
]);

const confirm = ref(null);

const form = useForm({
  title: props.menu?.title || '',
  // MenuRequest: in:header,footer
  menu_position: props.menu?.menu_position || 'header',
  language: props.menu?.language || props.languages[0]?.code,
});

function submit() {
  const url = props.menu
    ? route('admin.menu.save', { menu: props.menu.id })
    : route('admin.menu.save');

  form.post(url, { preserveScroll: true, onSuccess: () => form.reset() });
}

async function destroy(item) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('admin.menu.delete'), { menu_id: item.id }, { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('menu.menu')" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[minmax(0,1fr)_340px]">
    <div class="p-card divide-y divide-p-line2">
      <div v-for="item in menus" :key="item.id" class="flex items-center gap-3 px-4 py-3">
        <div class="min-w-0 flex-1">
          <div class="truncate text-[12.5px] font-semibold">{{ item.title }}</div>
          <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-p-ink3">
            <span class="p-chip">{{ item.menu_position }}</span>
            <span class="p-chip">{{ item.language?.toUpperCase() }}</span>
            <span>{{ item.items_count }}</span>
          </div>
        </div>

        <div class="flex gap-1">
          <Link
            :href="route('admin.menu.show', { menu: item.id })"
            class="p-icon-btn"
            :title="__('menu.menu_items')"
          >
            <i class="fa-solid fa-list-ul"></i>
          </Link>
          <Link
            :href="route('admin.menu.index', { menu: item.id })"
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

      <div v-if="!menus.length" class="px-4 py-10 text-center text-p-ink3">
        {{ __('general.no_records') }}
      </div>
    </div>

    <div class="p-card h-max p-4">
      <div class="mb-3 flex items-center gap-2">
        <div class="flex-1 font-display text-[13.5px] font-bold">
          {{ menu ? __('general.edit') : __('general.new') }}
        </div>
        <Link v-if="menu" :href="route('admin.menu.index')" class="p-icon-btn">
          <i class="fa-solid fa-xmark"></i>
        </Link>
      </div>

      <div class="flex flex-col gap-3">
        <FormField v-model="form.title" :label="__('menu.title')" :error="form.errors.title" />
        <FormField
          v-model="form.menu_position"
          type="select"
          :label="__('menu.menu_position')"
          :error="form.errors.menu_position"
          :options="[
            { value: 'header', label: __('menu.header') },
            { value: 'footer', label: __('menu.footer') },
          ]"
        />
        <FormField
          v-model="form.language"
          type="select"
          :label="__('language.language')"
          :error="form.errors.language"
          :options="languages.map((l) => ({ value: l.code, label: l.name }))"
        />

        <button class="p-btn-primary justify-center" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          {{ menu ? __('general.update') : __('general.create') }}
        </button>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
