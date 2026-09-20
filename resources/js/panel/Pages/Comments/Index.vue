<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import Modal from '../../components/Modal.vue';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';
import TinyMceEditor from '../../components/TinyMceEditor.vue';

/*
 * panel/post/comments/index.blade.php karşılığı.
 *
 * Eski ekranda yorum düzenleme TinyMCE'li bir Bootstrap modalıydı ve tüm
 * eylemler Swal + $.ajax ile yürüyordu. Aynı uçlar kullanılıyor; sunucu R2
 * ile şimlendiği için eski çağıranlar bozulmadı.
 */
const props = defineProps({
  comments: { type: Object, required: true },
  users: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

usePageHeader(__('comments.comments'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('comments.comments') },
]);

const search = ref(props.filters.search || '');
const confirm = ref(null);
const open = ref(false);
const editing = ref(null);

const tab = computed(() => props.filters.tab || 'all');

const form = useForm({
  name: '',
  email: '',
  comment: '',
  user_id: '',
  post_id: '',
  created_date: '',
});

function reload(overrides = {}) {
  router.get(
    route('admin.post.comments'),
    {
      search: search.value || undefined,
      tab: tab.value === 'trashed' ? 'trashed' : undefined,
      ...overrides,
    },
    { only: ['comments', 'filters'], preserveState: true, preserveScroll: true, replace: true },
  );
}

let timer = null;
watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => reload({ page: 1 }), 250);
});

function switchTab(value) {
  reload({ tab: value === 'trashed' ? 'trashed' : undefined, page: 1 });
}

function edit(comment) {
  editing.value = comment;
  form.name = comment.name || '';
  form.email = comment.email || '';
  form.comment = comment.comment || '';
  form.user_id = comment.user?.id ? String(comment.user.id) : '';
  form.post_id = comment.post?.id ? String(comment.post.id) : '';
  form.created_date = comment.createdAt ? comment.createdAt.slice(0, 16) : '';
  form.clearErrors();
  open.value = true;
}

function submit() {
  form.post(route('admin.post.comments.update', { comment: editing.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      open.value = false;
    },
  });
}

function approve(comment) {
  router.post(
    route('admin.post.comments.approve', { comment: comment.id }),
    {},
    { preserveScroll: true },
  );
}

function disapprove(comment) {
  router.post(
    route('admin.post.comments.disapprove', { comment: comment.id }),
    {},
    { preserveScroll: true },
  );
}

function restore(comment) {
  router.post(
    route('admin.post.comments.restore', { comment: comment.id }),
    {},
    { preserveScroll: true },
  );
}

async function destroy(comment) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.post.comments.delete', { comment: comment.id }),
    {},
    { preserveScroll: true },
  );
}

async function forceDelete(comment) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.post.comments.force-delete', { comment: comment.id }),
    {},
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('comments.comments')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <button class="p-tab" :class="tab === 'all' && 'p-tab-active'" @click="switchTab('all')">
        {{ __('comments.comments') }}
      </button>
      <button
        class="p-tab"
        :class="tab === 'trashed' && 'p-tab-active'"
        @click="switchTab('trashed')"
      >
        <i class="fa-solid fa-trash mr-1.5 text-[11px]"></i>{{ __('post.trashed') }}
      </button>

      <div class="flex-1"></div>

      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[200px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>
    </div>

    <div class="flex flex-col gap-2.5">
      <div v-for="comment in comments.data" :key="comment.id" class="p-card p-4">
        <div class="flex flex-wrap items-start gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-semibold">{{ comment.user?.nickname || comment.name }}</span>
              <span class="text-[11.5px] text-p-ink3">{{ comment.email }}</span>
              <span v-if="comment.ip" class="text-[11px] text-p-ink3">· {{ comment.ip }}</span>
              <span
                class="p-chip"
                :class="comment.is_approved ? '!bg-p-ok !text-white' : '!bg-p-warn !text-white'"
              >
                {{ comment.is_approved ? __('comments.approved') : __('comments.disapproved') }}
              </span>
            </div>

            <!-- Yorum icerigi TinyMCE ile uretilmis HTML; tek v-html noktasi. -->
            <div
              class="prose prose-sm mt-2 max-w-none text-[12.5px] text-p-ink2"
              v-html="comment.comment"
            ></div>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-p-ink3">
              <span>{{ formatDateTime(comment.createdAt) }}</span>
              <template v-if="comment.post">
                <span>·</span>
                <Link
                  :href="route('admin.post.edit', { type: comment.post.type, post: comment.post.id })"
                  >{{ comment.post.title }}</Link
                >
              </template>
            </div>
          </div>

          <div class="flex shrink-0 flex-wrap gap-1">
            <template v-if="comment.deleted_at">
              <button class="p-icon-btn" :title="__('general.restore')" @click="restore(comment)">
                <i class="fa-solid fa-rotate-left"></i>
              </button>
              <button
                class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                :title="__('general.delete')"
                @click="forceDelete(comment)"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </template>
            <template v-else>
              <button
                v-if="!comment.is_approved"
                class="p-icon-btn hover:!border-p-ok hover:!text-p-ok"
                :title="__('comments.approve')"
                @click="approve(comment)"
              >
                <i class="fa-solid fa-check"></i>
              </button>
              <button
                v-else
                class="p-icon-btn hover:!border-p-warn hover:!text-p-warn"
                :title="__('comments.disapprove')"
                @click="disapprove(comment)"
              >
                <i class="fa-solid fa-ban"></i>
              </button>
              <button class="p-icon-btn" :title="__('general.edit')" @click="edit(comment)">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button
                class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                :title="__('general.delete')"
                @click="destroy(comment)"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </template>
          </div>
        </div>
      </div>

      <div v-if="!comments.data.length" class="p-card px-4 py-10 text-center text-p-ink3">
        {{ __('comments.no_comments_found') }}
      </div>
    </div>

    <Pagination :links="comments.links" :meta="comments" :only="['comments', 'filters']" />

    <Modal
      v-model:open="open"
      :title="__('general.edit')"
      icon="fa-solid fa-comment"
      width="720px"
      @confirm="submit"
    >
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField v-model="form.name" :label="__('contact.name_surname')" :error="form.errors.name" />
        <FormField v-model="form.email" :label="__('contact.email')" :error="form.errors.email" />
        <FormField
          v-model="form.user_id"
          type="select"
          :label="__('user.user')"
          :options="[{ value: '', label: '—' }, ...users.map((u) => ({ value: u.id, label: u.nickname }))]"
        />
        <FormField
          v-model="form.created_date"
          type="datetime-local"
          :label="__('general.created_at')"
        />
        <div class="sm:col-span-2">
          <label class="p-label">{{ __('comments.comment') }}</label>
          <TinyMceEditor
            v-model="form.comment"
            :upload-url="''"
            :language="$page.props.currentLanguage?.code || 'tr'"
            :height="320"
            :ai-enabled="false"
          />
          <div v-if="form.errors.comment" class="mt-1.5 text-[11px] font-semibold text-p-danger">
            {{ form.errors.comment }}
          </div>
        </div>
      </div>
    </Modal>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
