<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../../composables/useLang';
import { usePageHeader } from '../../../composables/usePageHeader';
import { formatDateTime } from '../../../composables/useFormat';
import DiffHtml from '../../../components/DiffHtml.vue';
import ConfirmDialog from '../../../components/ConfirmDialog.vue';

/*
 * panel/post/history/show.blade.php karşılığı.
 *
 * `diff` prop'u Qazd\TextDiff çıktısıdır ve panelde HTML taşıyan TEK prop'tur;
 * bir diff motorunu Vue'da yeniden yazmak port değil yeniden yazım olurdu.
 * Tek bir <DiffHtml> bileşeninden basılır.
 */
const props = defineProps({
  type: { type: String, required: true },
  post: { type: Object, required: true },
  history: { type: Object, required: true },
  diff: { type: Object, required: true },
});

usePageHeader(__('post.history'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: props.post.title },
  { label: formatDateTime(props.history.createdAt) },
]);

const confirm = ref(null);

async function revert() {
  if (!(await confirm.value.ask({ body: __('post.revert_sure'), danger: false }))) {
    return;
  }

  router.post(
    route('admin.post.history.revert', {
      type: props.type,
      posts: props.post.id,
      history: props.history.id,
    }),
    {},
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('post.history')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <Link
        :href="route('admin.post.history', { type, posts: post.id })"
        class="p-btn no-underline"
      >
        <i class="fa-solid fa-arrow-left text-[11px]"></i>
        {{ __('post.history') }}
      </Link>

      <span class="p-chip tabular-nums">{{ formatDateTime(history.createdAt) }}</span>

      <div class="flex-1"></div>

      <button class="p-btn-primary" @click="revert">
        <i class="fa-solid fa-rotate-left text-xs"></i>
        {{ __('post.revert') }}
      </button>
    </div>

    <div class="p-card p-4">
      <div class="p-label">{{ __('post.title') }}</div>
      <DiffHtml :html="diff.title" />
    </div>

    <div class="p-card p-4">
      <div class="p-label">{{ __('post.slug') }}</div>
      <DiffHtml :html="diff.slug" />
    </div>

    <div class="p-card p-4">
      <div class="p-label">{{ __('post.content') }}</div>
      <DiffHtml :html="diff.content" />
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
