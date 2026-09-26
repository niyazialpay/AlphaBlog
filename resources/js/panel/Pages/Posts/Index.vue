<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import { pushToast } from '../../composables/useToast';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Modal from '../../components/Modal.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/post/index.blade.php karşılığı — hem Bloglar hem Sayfalar.
 *
 * jQuery DataTables + ayrı POST beslemesi gitti; satırlar Inertia prop
 * paginator'ı olarak geliyor ve HTML kolonları (checkbox/title/categories/
 * media/action) burada üretiliyor.
 *
 * Toplu Google index gönderimi hâlâ JSON ucu (R1: veri eylemi), axios ile
 * çağrılır ve sonrasında yalnızca satırlar tazelenir.
 */
const props = defineProps({
  type: { type: String, required: true },
  category: { type: [Number, String, null], default: null },
  rows: { type: Object, required: true },
  trashed: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

const isPages = computed(() => props.type === 'pages');
const title = computed(() => (isPages.value ? __('post.pages') : __('post.blogs')));

usePageHeader(title.value, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: title.value },
]);

/*
 * Satir basina Google index durumu — panel/post/index.blade.php:473-565 ve
 * partials/actions.blade.php:28 karsiligi.
 *
 * Uc uc da VERI ucu, dolayisiyla Inertia degil axios ile cagrilir (R1):
 *   GET  admin.post.index.status   -> {indexed, coverage_state, from_cache, ...}
 *   GET  admin.post.index.history  -> gonderim kayitlari
 *   POST admin.post.index.single   -> yeniden gonder
 *
 * Durum ve gecmis TEK SEFERDE istenir; eski ekran da `$.when(...)` ile ikisini
 * paralel cagiriyordu. Biri patlarsa digeri yine gosterilir.
 */
const indexOpen = ref(false);
const indexRow = ref(null);
const indexLoading = ref(false);
const indexStatus = ref(null);
const indexLogs = ref([]);
const indexResending = ref(false);

async function openIndexStatus(row) {
  indexRow.value = row;
  indexStatus.value = null;
  indexLogs.value = [];
  indexLoading.value = true;
  indexOpen.value = true;

  const params = { type: props.type, post: row.id };

  const [status, history] = await Promise.allSettled([
    axios.get(route('admin.post.index.status', params)),
    axios.get(route('admin.post.index.history', params)),
  ]);

  indexStatus.value =
    status.status === 'fulfilled'
      ? status.value.data
      : { error: true, coverage_state: __('general.error') };

  indexLogs.value = history.status === 'fulfilled' ? history.value.data || [] : [];
  indexLoading.value = false;
}

/* Eski ekran "Tekrar Gonder"i yalnizca indexlenmemis ya da hatali durumda acardi. */
const canResendIndex = computed(
  () => indexStatus.value !== null && (indexStatus.value.error || ! indexStatus.value.indexed),
);

async function resendIndex() {
  if (! indexRow.value || indexResending.value) {
    return;
  }

  indexResending.value = true;

  try {
    await axios.post(route('admin.post.index.single', { type: props.type, post: indexRow.value.id }));
    pushToast(__('post.index_queued'), 'success');
    indexOpen.value = false;
  } catch (error) {
    pushToast(error?.response?.data?.message || __('general.error'), 'error');
  } finally {
    indexResending.value = false;
  }
}

const PER_PAGE = [10, 25, 50, 75, 100];

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
const selected = ref(new Set());
const confirm = ref(null);
const indexing = ref(false);

const tab = computed(() => props.filters.tab || 'published');
const searching = computed(() => !!(props.filters.search || '').trim());

function reload(overrides = {}) {
  router.get(
    route('admin.posts', { type: props.type }),
    {
      search: search.value || undefined,
      language: props.filters.language,
      tab: tab.value === 'trashed' ? 'trashed' : undefined,
      per_page: perPage.value,
      sort: props.filters.sort,
      dir: props.filters.dir,
      ...overrides,
    },
    {
      only: ['rows', 'trashed', 'filters'],
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
}

let timer = null;
watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => reload({ page: 1 }), 250);
});
watch(perPage, () => reload({ page: 1 }));

function switchLanguage(code) {
  reload({ language: code, page: 1 });
}

function switchTab(value) {
  reload({ tab: value === 'trashed' ? 'trashed' : undefined, page: 1 });
}

function sortBy(column) {
  // Scout arama sonuçları ilgi sırasında gelir; sıralama anlamsız olur.
  if (searching.value) {
    return;
  }

  const dir = props.filters.sort === column && props.filters.dir === 'desc' ? 'asc' : 'desc';
  reload({ sort: column, dir, page: 1 });
}

function sortIcon(column) {
  if (searching.value || props.filters.sort !== column) {
    return null;
  }

  return props.filters.dir === 'desc' ? 'fa-solid fa-arrow-down-long' : 'fa-solid fa-arrow-up-long';
}

function toggle(id) {
  selected.value.has(id) ? selected.value.delete(id) : selected.value.add(id);
  selected.value = new Set(selected.value);
}

function toggleAll(event) {
  selected.value = event.target.checked ? new Set(props.rows.data.map((row) => row.id)) : new Set();
}

async function bulkIndex() {
  if (!selected.value.size) {
    return;
  }

  indexing.value = true;

  try {
    const { data } = await axios.post(route('admin.post.index.bulk', { type: props.type }), {
      post_ids: [...selected.value],
    });

    pushToast(
      __('post.index_bulk_result', { queued: data.queued ?? 0, skipped: data.skipped ?? 0 }),
      'success',
    );
    selected.value = new Set();
    router.reload({ only: ['rows'] });
  } catch {
    pushToast(__('general.error'), 'error');
  } finally {
    indexing.value = false;
  }
}

/*
 * Form eylemi (R1): sunucu `back()->with(...)` doner, flash toast'u kabuk basar.
 * Yetkisi olmayan satirlari sunucu atlar ve sayisini mesajda bildirir.
 */
async function bulkDestroy() {
  const ids = [...selected.value];

  if (!ids.length || !(await confirm.value.ask({ body: __('post.bulk_delete_confirm', { count: ids.length }) }))) {
    return;
  }

  router.post(
    route('admin.post.delete.bulk', { type: props.type }),
    { post_ids: ids },
    {
      preserveScroll: true,
      onSuccess: () => {
        selected.value = new Set();
      },
    },
  );
}

async function destroy(row) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('admin.post.delete', { type: props.type, post: row.id }), {}, { preserveScroll: true });
}

async function restore(row) {
  router.post(route('admin.post.restore', { type: props.type, post: row.id }), {}, { preserveScroll: true });
}

async function forceDelete(row) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.post.delete.permanent', { type: props.type, post: row.id }),
    {},
    { preserveScroll: true },
  );
}

const list = computed(() => (tab.value === 'trashed' ? props.trashed : props.rows));
</script>

<template>
  <Head :title="title" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <!-- Dil sekmeleri -->
    <div class="flex flex-wrap items-center gap-1.5">
      <button
        v-for="language in $page.props.languages"
        :key="language.code"
        class="p-tab"
        :class="filters.language === language.code && 'p-tab-active'"
        @click="switchLanguage(language.code)"
      >
        {{ language.name }}
      </button>

      <div class="mx-2 h-5 w-px bg-p-line"></div>

      <button class="p-tab" :class="tab === 'published' && 'p-tab-active'" @click="switchTab('published')">
        {{ isPages ? __('post.pages') : __('post.blogs') }}
      </button>
      <button class="p-tab" :class="tab === 'trashed' && 'p-tab-active'" @click="switchTab('trashed')">
        <i class="fa-solid fa-trash mr-1.5 text-[11px]"></i>{{ __('post.trashed') }}
      </button>

      <div class="flex-1"></div>

      <Link
        v-if="$page.props.can?.createPost"
        :href="route('admin.post.create', { type })"
        class="p-btn-primary"
      >
        <i class="fa-solid fa-feather text-xs"></i>
        {{ __('general.new') }}
      </Link>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <select v-model.number="perPage" class="p-input w-auto">
        <option v-for="n in PER_PAGE" :key="n" :value="n">{{ n }}</option>
      </select>

      <button
        v-if="selected.size"
        class="p-btn"
        :disabled="indexing"
        @click="bulkIndex"
      >
        <i class="fa-brands fa-google text-[11px]"></i>
        {{ __('post.send_to_google') }} ({{ selected.size }})
      </button>

      <button
        v-if="selected.size && tab !== 'trashed'"
        class="p-btn hover:!border-p-danger hover:!text-p-danger"
        @click="bulkDestroy"
      >
        <i class="fa-solid fa-trash text-[11px]"></i>
        {{ __('post.bulk_delete') }} ({{ selected.size }})
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

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th v-if="tab !== 'trashed'" class="w-9 px-3.5 py-2.5">
                <input type="checkbox" @change="toggleAll" />
              </th>
              <th
                class="cursor-pointer select-none px-3.5 py-2.5 font-semibold hover:text-p-ink"
                @click="sortBy('title')"
              >
                {{ __('post.title') }}
                <i v-if="sortIcon('title')" :class="sortIcon('title')" class="ml-1 text-[9px]"></i>
              </th>
              <th v-if="!isPages" class="px-3.5 py-2.5 font-semibold">
                {{ __('categories.categories') }}
              </th>
              <th
                v-if="!isPages"
                class="w-[90px] cursor-pointer select-none px-3.5 py-2.5 text-center font-semibold hover:text-p-ink"
                @click="sortBy('views')"
              >
                {{ __('post.views') }}
                <i v-if="sortIcon('views')" :class="sortIcon('views')" class="ml-1 text-[9px]"></i>
              </th>
              <th class="w-[70px] px-3.5 py-2.5 text-center font-semibold">
                {{ __('post.media') }}
              </th>
              <th class="w-[120px] px-3.5 py-2.5 font-semibold">{{ __('user.user') }}</th>
              <th
                class="w-[150px] cursor-pointer select-none whitespace-nowrap px-3.5 py-2.5 font-semibold hover:text-p-ink"
                @click="sortBy('created_at')"
              >
                {{ __('general.created_at') }}
                <i
                  v-if="sortIcon('created_at')"
                  :class="sortIcon('created_at')"
                  class="ml-1 text-[9px]"
                ></i>
              </th>
              <th class="w-[130px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in list.data"
              :key="row.id"
              class="border-t border-p-line2 align-top hover:bg-p-panel2"
            >
              <td v-if="tab !== 'trashed'" class="px-3.5 py-2.5">
                <input type="checkbox" :checked="selected.has(row.id)" @change="toggle(row.id)" />
              </td>

              <td class="px-3.5 py-2.5">
                <Link
                  v-if="row.can.edit"
                  :href="route('admin.post.edit', { type, post: row.id })"
                  class="font-semibold"
                  >{{ row.title }}</Link
                >
                <span v-else class="font-semibold">{{ row.title }}</span>
                <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                  <span class="p-chip" :class="row.is_published ? '!bg-p-ok !text-white' : ''">
                    {{ row.is_published ? __('post.published') : __('post.draft') }}
                  </span>
                  <span v-if="row.comments_count" class="text-[11px] text-p-ink3">
                    <i class="fa-solid fa-comments"></i> {{ row.comments_count }}
                  </span>
                  <!--
                    QR okuma sayısı: row() bu alanı zaten gönderiyordu ama hiçbir
                    yerde kullanılmıyordu. QR'ın kendisi yazı editöründeki kartta.
                  -->
                  <span
                    v-if="row.qr_scans_count"
                    class="text-[11px] text-p-ink3"
                    :title="__('post.qr_code')"
                  >
                    <i class="fa-solid fa-qrcode"></i> {{ row.qr_scans_count }}
                  </span>
                </div>
              </td>

              <td v-if="!isPages" class="px-3.5 py-2.5">
                <span v-for="category in row.categories" :key="category.id" class="p-chip mr-1">
                  {{ category.name }}
                </span>
              </td>

              <td v-if="!isPages" class="px-3.5 py-2.5 text-center tabular-nums text-p-ink2">
                {{ row.views }}
              </td>

              <td class="px-3.5 py-2.5 text-center">
                <img
                  v-if="row.thumbnail"
                  :src="row.thumbnail"
                  :alt="row.title"
                  class="mx-auto h-9 w-9 rounded-lg object-cover"
                  loading="lazy"
                />
                <span v-else class="text-p-ink3">—</span>
              </td>

              <td class="px-3.5 py-2.5 text-p-ink2">{{ row.author?.nickname || '—' }}</td>

              <td class="whitespace-nowrap px-3.5 py-2.5 tabular-nums text-p-ink3">
                {{ formatDateTime(row.created_at) }}
              </td>

              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <template v-if="tab === 'trashed'">
                    <button class="p-icon-btn" :title="__('general.restore')" @click="restore(row)">
                      <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button
                      v-if="$page.props.can?.admin"
                      class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                      :title="__('general.delete')"
                      @click="forceDelete(row)"
                    >
                      <i class="fa-solid fa-xmark"></i>
                    </button>
                  </template>
                  <template v-else>
                    <Link
                      v-if="row.can.edit"
                      :href="route('admin.post.edit', { type, post: row.id })"
                      class="p-icon-btn"
                      :title="__('general.edit')"
                    >
                      <i class="fa-solid fa-pen"></i>
                    </Link>
                    <Link
                      v-if="row.can.edit"
                      :href="route('admin.post.media', { type, post: row.id })"
                      class="p-icon-btn"
                      :title="__('post.media')"
                    >
                      <i class="fa-solid fa-images"></i>
                    </Link>
                    <!-- Google index durumu: eski actions.blade.php:28 karsiligi. -->
                    <button
                      class="p-icon-btn"
                      :title="__('post.index_status')"
                      @click="openIndexStatus(row)"
                    >
                      <i class="fa-brands fa-google"></i>
                    </button>
                    <button
                      v-if="row.can.delete"
                      class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                      :title="__('general.delete')"
                      @click="destroy(row)"
                    >
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </template>
                </div>
              </td>
            </tr>

            <tr v-if="!list.data.length">
              <td colspan="9" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('post.no_posts_found') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="list.links" :meta="list" :only="['rows', 'trashed', 'filters']" />

    <ConfirmDialog ref="confirm" />

    <Modal
      v-model:open="indexOpen"
      :title="__('post.index_status')"
      icon="fa-brands fa-google"
      width="640px"
    >
      <div v-if="indexLoading" class="py-6 text-center text-[12.5px] text-p-ink3">
        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>{{ __('post.index_checking') }}
      </div>

      <template v-else>
        <div class="truncate text-[12px] text-p-ink3">{{ indexRow?.title }}</div>

        <div
          v-if="indexStatus"
          class="mt-2.5 rounded-xl border px-3 py-2.5"
          :class="
            indexStatus.error
              ? 'border-p-danger'
              : indexStatus.indexed
                ? 'border-p-ok'
                : 'border-p-warn'
          "
        >
          <div class="flex flex-wrap items-center gap-2 text-[12.5px] font-semibold">
            <span
              :class="
                indexStatus.error
                  ? 'text-p-danger'
                  : indexStatus.indexed
                    ? 'text-p-ok'
                    : 'text-p-warn'
              "
            >
              <i
                class="mr-1.5"
                :class="
                  indexStatus.error
                    ? 'fa-solid fa-circle-xmark'
                    : indexStatus.indexed
                      ? 'fa-solid fa-circle-check'
                      : 'fa-solid fa-clock'
                "
              ></i>
              {{
                indexStatus.error
                  ? __('post.index_status_error')
                  : indexStatus.indexed
                    ? __('post.indexed')
                    : __('post.not_indexed')
              }}
            </span>

            <span v-if="indexStatus.coverage_state" class="text-p-ink3">
              — {{ indexStatus.coverage_state }}
            </span>

            <span v-if="indexStatus.from_cache" class="p-chip">{{ __('post.index_from_cache') }}</span>
          </div>

          <div v-if="indexStatus.last_crawl_time" class="mt-1 text-[11.5px] text-p-ink3">
            {{ __('post.index_last_crawl') }}: {{ formatDateTime(indexStatus.last_crawl_time) }}
          </div>
          <div v-if="indexStatus.cached_at" class="text-[11.5px] text-p-ink3">
            {{ __('post.index_cached_at') }}: {{ formatDateTime(indexStatus.cached_at) }}
          </div>
        </div>

        <div class="mt-3.5 text-[11.5px] font-bold uppercase tracking-wider text-p-ink3">
          {{ __('post.index_history') }}
        </div>

        <div v-if="!indexLogs.length" class="mt-1.5 text-[12px] text-p-ink3">
          {{ __('post.index_no_history') }}
        </div>

        <div v-else class="mt-1.5 overflow-x-auto">
          <table class="w-full min-w-[520px] border-collapse text-[11.5px]">
            <thead>
              <tr class="text-left text-p-ink3">
                <th class="py-1.5 pr-2 font-semibold">{{ __('general.date') }}</th>
                <th class="py-1.5 pr-2 font-semibold">{{ __('post.index_type') }}</th>
                <th class="py-1.5 pr-2 font-semibold">{{ __('post.index_result') }}</th>
                <th class="py-1.5 pr-2 font-semibold">{{ __('post.index_code') }}</th>
                <th class="py-1.5 font-semibold">{{ __('post.index_message') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="log in indexLogs" :key="log.id" class="border-t border-p-line2 align-top">
                <td class="whitespace-nowrap py-1.5 pr-2 text-p-ink3">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="py-1.5 pr-2"><span class="p-chip">{{ log.type }}</span></td>
                <td class="py-1.5 pr-2">
                  <span
                    class="p-chip"
                    :class="log.status === 'success' ? '!text-p-ok' : '!text-p-danger'"
                  >
                    {{ log.status }}
                  </span>
                </td>
                <td class="py-1.5 pr-2 text-p-ink3">{{ log.response_code }}</td>
                <td class="py-1.5 text-p-ink3">{{ log.message }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <template #footer>
        <button class="p-btn" @click="indexOpen = false">{{ __('general.close') }}</button>
        <button
          v-if="canResendIndex"
          class="p-btn-primary"
          :disabled="indexResending"
          @click="resendIndex"
        >
          <i
            class="text-xs"
            :class="indexResending ? 'fa-solid fa-spinner fa-spin' : 'fa-brands fa-google'"
          ></i>
          {{ __('post.index_resend') }}
        </button>
      </template>
    </Modal>
  </div>
</template>
