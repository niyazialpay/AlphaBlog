<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import JsonViewer from '../../components/JsonViewer.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/logs/index.blade.php karşılığı — ilk DataTable dönüşümü.
 *
 * jQuery DataTables + ayrı POST beslemesi yerine Inertia prop paginator'ı ve
 * kısmi yeniden yükleme kullanılır: `only` sayesinde yük eski JSON kadar,
 * ama URL durumu paylaşılabilir ve geri tuşu çalışır.
 *
 * old_data / new_data artık '<pre>' HTML'i değil gerçek nesne; `action` ham
 * anahtar olarak gelir ve burada çevrilir.
 */
const props = defineProps({
  logs: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

usePageHeader(__('logs.logs'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('logs.logs') },
]);

const PER_PAGE = [10, 25, 50, 75, 100];

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

function reload(overrides = {}) {
  router.get(
    route('admin.system-logs'),
    {
      search: search.value || undefined,
      per_page: perPage.value,
      sort: props.filters.sort,
      dir: props.filters.dir,
      ...overrides,
    },
    { only: ['logs', 'filters'], preserveState: true, preserveScroll: true, replace: true },
  );
}

let timer = null;
watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => reload({ page: 1 }), 250);
});

watch(perPage, () => reload({ page: 1 }));

function sortBy(column) {
  const dir = props.filters.sort === column && props.filters.dir === 'desc' ? 'asc' : 'desc';
  reload({ sort: column, dir, page: 1 });
}

function sortIcon(column) {
  if (props.filters.sort !== column) {
    return null;
  }

  return props.filters.dir === 'desc' ? 'fa-solid fa-arrow-down-long' : 'fa-solid fa-arrow-up-long';
}

const COLUMNS = [
  { key: 'created_at', label: () => __('general.created_at'), sortable: true },
  { key: 'ip', label: () => __('sessions.ip_address'), sortable: true },
  { key: 'user_id', label: () => __('user.user'), sortable: true },
  { key: 'model', label: () => 'Model', sortable: true },
  { key: 'action', label: () => __('general.actions'), sortable: true },
];
</script>

<template>
  <Head :title="__('logs.logs')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <select v-model.number="perPage" class="p-input w-auto">
        <option v-for="n in PER_PAGE" :key="n" :value="n">{{ n }}</option>
      </select>

      <div class="flex-1"></div>

      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[180px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th
                v-for="column in COLUMNS"
                :key="column.key"
                class="cursor-pointer select-none whitespace-nowrap px-3.5 py-2.5 font-semibold hover:text-p-ink"
                @click="column.sortable && sortBy(column.key)"
              >
                {{ column.label() }}
                <i v-if="sortIcon(column.key)" :class="sortIcon(column.key)" class="ml-1 text-[9px]"></i>
              </th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('logs.old_data') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('logs.new_data') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">User agent</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in logs.data"
              :key="log.id"
              class="border-t border-p-line2 align-top hover:bg-p-panel2"
            >
              <td class="whitespace-nowrap px-3.5 py-2.5 tabular-nums text-p-ink3">
                {{ formatDateTime(log.createdAt) }}
              </td>
              <td class="whitespace-nowrap px-3.5 py-2.5">{{ log.ip }}</td>
              <td class="whitespace-nowrap px-3.5 py-2.5">{{ log.user?.nickname || '—' }}</td>
              <td class="px-3.5 py-2.5 text-p-ink2">{{ log.model }}</td>
              <td class="whitespace-nowrap px-3.5 py-2.5">
                <span class="p-chip">{{ __(`logs.action_list.${log.action}`) }}</span>
              </td>
              <td class="px-3.5 py-2.5"><JsonViewer :value="log.old_data" /></td>
              <td class="px-3.5 py-2.5"><JsonViewer :value="log.new_data" /></td>
              <td class="max-w-[220px] break-all px-3.5 py-2.5 text-[11px] text-p-ink3">
                {{ log.user_agent }}
              </td>
            </tr>
            <tr v-if="!logs.data.length">
              <td colspan="8" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('general.no_records') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="logs.links" :meta="logs" :only="['logs', 'filters']" />
  </div>
</template>
