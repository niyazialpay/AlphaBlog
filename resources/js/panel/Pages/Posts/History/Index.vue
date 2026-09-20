<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../../composables/useLang';
import { usePageHeader } from '../../../composables/usePageHeader';
import { formatDateTime } from '../../../composables/useFormat';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';

/* panel/post/history/index.blade.php karşılığı. */
const props = defineProps({
  type: { type: String, required: true },
  post: { type: Object, required: true },
  history: { type: Array, default: () => [] },
});

usePageHeader(__('post.history'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: props.post.title },
  { label: __('post.history') },
]);

const confirm = ref(null);

async function destroy(item) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.post.history.delete', { type: props.type, posts: props.post.id, history: item.id }),
    {},
    { preserveScroll: true },
  );
}

async function revert(item) {
  if (!(await confirm.value.ask({ body: __('post.revert_sure'), danger: false }))) {
    return;
  }

  router.post(
    route('admin.post.history.revert', { type: props.type, posts: props.post.id, history: item.id }),
    {},
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('post.history')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <Link :href="route('admin.post.edit', { type, post: post.id })" class="p-btn no-underline">
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        {{ post.title }}
      </Link>
    </div>

    <div class="p-card divide-y divide-p-line2">
      <div
        v-for="item in history"
        :key="item.id"
        class="flex flex-wrap items-center gap-3 px-4 py-3"
      >
        <div class="min-w-0 flex-1">
          <div class="truncate text-[12.5px] font-semibold">{{ item.title }}</div>
          <div class="mt-0.5 truncate text-[11.5px] text-p-ink3">{{ item.slug }}</div>
        </div>

        <div class="whitespace-nowrap text-[11px] tabular-nums text-p-ink3">
          {{ formatDateTime(item.createdAt) }}
        </div>

        <div class="flex gap-1">
          <Link
            :href="route('admin.post.history.show', { type, posts: post.id, history: item.id })"
            class="p-icon-btn"
            :title="__('media.show')"
          >
            <i class="fa-solid fa-code-compare"></i>
          </Link>
          <button class="p-icon-btn" :title="__('post.revert')" @click="revert(item)">
            <i class="fa-solid fa-rotate-left"></i>
          </button>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="destroy(item)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>

      <div v-if="!history.length" class="px-4 py-10 text-center text-p-ink3">
        {{ __('general.no_records') }}
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
