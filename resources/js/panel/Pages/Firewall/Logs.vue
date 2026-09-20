<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import JsonViewer from '../../components/JsonViewer.vue';
import Pagination from '../../components/Pagination.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/*
 * panel/firewall/logs.blade.php (+ logs/actions.blade.php) karşılığı.
 *
 * `request_data` artık '<pre>' HTML'i değil gerçek nesne; satır aksiyonları
 * `is_blacklisted` bayrağından sürülüyor — blade partial'ı aynı koşulu
 * sunucuda işliyordu.
 */
const props = defineProps({
  logs: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

usePageHeader(__('firewall.logs'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('firewall.firewall'), route: 'admin.firewall' },
  { label: __('firewall.logs') },
]);

const PER_PAGE = [10, 25, 50, 75, 100];

const confirm = ref(null);
const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

function reload(overrides = {}) {
  router.get(
    route('admin.firewall.logs'),
    { search: search.value || undefined, per_page: perPage.value, ...overrides },
    { only: ['logs', 'filters'], preserveState: true, preserveScroll: true, replace: true },
  );
}

let timer = null;
watch(search, () => {
  clearTimeout(timer);
  timer = setTimeout(() => reload({ page: 1 }), 250);
});
watch(perPage, () => reload({ page: 1 }));

async function whitelist(log) {
  if (
    !(await confirm.value.ask({
      title: __('firewall.add_to_whitelist'),
      body: __('firewall.add_to_whitelist_text'),
      confirmLabel: __('general.yes'),
      cancelLabel: __('general.no'),
      danger: false,
    }))
  ) {
    return;
  }

  router.post(route('admin.firewall.whitelist'), { ip: log.ip }, { preserveScroll: true });
}

async function removeFromBlocklist(log) {
  if (
    !(await confirm.value.ask({
      title: __('firewall.remove_from_blocklist'),
      body: __('firewall.remove_from_blocklist_text'),
      confirmLabel: __('general.yes'),
      cancelLabel: __('general.no'),
    }))
  ) {
    return;
  }

  router.post(route('admin.firewall.delete'), { ip: log.ip }, { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('firewall.logs')" />

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
          class="w-[200px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="w-[150px] px-3.5 py-2.5 font-semibold">{{ __('general.created_at') }}</th>
              <th class="w-[130px] px-3.5 py-2.5 font-semibold">{{ __('ip_filter.ip_range') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('menu.url') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('firewall.reason') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('firewall.request_data') }}</th>
              <th class="w-[90px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
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
              <td class="whitespace-nowrap px-3.5 py-2.5 font-mono">{{ log.ip }}</td>
              <td class="break-all px-3.5 py-2.5 text-p-ink2">
                {{ log.url }}
                <div class="mt-0.5 break-all text-[10.5px] text-p-ink3" :title="__('sessions.user_agent')">
                  {{ log.user_agent }}
                </div>
              </td>
              <td class="px-3.5 py-2.5">
                <span class="p-chip">{{ log.reason }}</span>
                <div v-if="log.ip_filter" class="mt-1 text-[10.5px] text-p-ink3">
                  {{ log.ip_filter.name }}
                </div>
              </td>
              <td class="px-3.5 py-2.5"><JsonViewer :value="log.request_data" /></td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <button
                    class="p-icon-btn hover:!border-p-ok hover:!text-p-ok"
                    :title="__('firewall.add_to_whitelist')"
                    @click="whitelist(log)"
                  >
                    <i class="fa-solid fa-shield-halved"></i>
                  </button>
                  <button
                    v-if="log.is_blacklisted"
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('firewall.remove_from_blocklist')"
                    @click="removeFromBlocklist(log)"
                  >
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!logs.data.length">
              <td colspan="6" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('general.no_records') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="logs.links" :meta="logs" :only="['logs', 'filters']" />

    <ConfirmDialog ref="confirm" />
  </div>
</template>
