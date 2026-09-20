<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import DnsRecordModal from '../../components/DnsRecordModal.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/*
 * panel/cloudflare/dns.blade.php karşılığı.
 *
 * Eski ekran server-side DataTables kuruyordu ama besleme zaten `perPage: 5000`
 * ile bütün kayıtları çekiyordu — sunucu tarafı sıralama/sayfalama hiçbir şey
 * kazandırmıyordu. Kayıtlar tek prop olarak geliyor, filtre/sıra burada
 * yapılıyor: DataTables + rowReorder + fixedHeader CDN'leri düşüyor ve
 * `dns_json()` sıralama haritası hatası (B3) Vue yolundan tamamen çıkıyor.
 */
const props = defineProps({
  domain: { type: String, default: '' },
  records: { type: Array, default: () => [] },
});

usePageHeader('DNS', [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: 'Cloudflare', route: 'cf.dashboard' },
  { label: 'DNS' },
]);

const COLUMNS = [
  { key: 'proxied', label: __('cloudflare.proxied'), width: 'w-[80px]' },
  { key: 'type', label: __('cloudflare.type'), width: 'w-[90px]' },
  { key: 'name', label: __('cloudflare.name'), width: '' },
  { key: 'content', label: __('cloudflare.content'), width: '' },
  { key: 'ttl', label: __('cloudflare.ttl'), width: 'w-[90px]' },
];

const search = ref('');
const sortKey = ref('name');
const sortAsc = ref(true);

const modalOpen = ref(false);
const editing = ref(null);
const confirm = ref(null);

const visible = computed(() => {
  const term = search.value.trim().toLowerCase();

  const rows = term
    ? props.records.filter((record) =>
        [record.name, record.type, record.content].some((field) =>
          String(field ?? '').toLowerCase().includes(term),
        ),
      )
    : [...props.records];

  const key = sortKey.value;
  const direction = sortAsc.value ? 1 : -1;

  return rows.sort((a, b) => {
    const left = a[key];
    const right = b[key];

    if (typeof left === 'number' && typeof right === 'number') {
      return (left - right) * direction;
    }

    return String(left ?? '').localeCompare(String(right ?? ''), undefined, { numeric: true }) * direction;
  });
});

function sortBy(key) {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value;
    return;
  }

  sortKey.value = key;
  sortAsc.value = true;
}

/* TTL 1 = "Auto" — eski beslemede sunucu tarafında çevriliyordu. */
function ttlLabel(ttl) {
  return ttl === 1 ? 'Auto' : ttl;
}

function create() {
  editing.value = null;
  modalOpen.value = true;
}

function edit(record) {
  editing.value = record;
  modalOpen.value = true;
}

async function destroy(record) {
  if (
    !(await confirm.value.ask({
      title: __('cloudflare.delete_record'),
      body: `${__('cloudflare.delete_warning')}\n${record.name} ${record.type} ${record.content}`,
      confirmLabel: __('general.delete'),
      cancelLabel: __('general.close'),
    }))
  ) {
    return;
  }

  router.post(route('cf.dns.delete'), { dns_id: record.id }, { preserveScroll: true });
}

function refresh() {
  router.reload({ only: ['records'] });
}
</script>

<template>
  <Head title="Cloudflare DNS" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <span v-if="domain" class="p-chip">{{ domain }}</span>
      <span class="p-chip">{{ visible.length }}</span>

      <div class="flex-1"></div>

      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[200px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>

      <button class="p-btn-primary" @click="create">
        <i class="fa-solid fa-plus text-xs"></i> {{ __('cloudflare.add_record') }}
      </button>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th
                v-for="column in COLUMNS"
                :key="column.key"
                class="cursor-pointer select-none px-3.5 py-2.5 font-semibold"
                :class="column.width"
                @click="sortBy(column.key)"
              >
                {{ column.label }}
                <i
                  v-if="sortKey === column.key"
                  class="fa-solid ml-1 text-[9px]"
                  :class="sortAsc ? 'fa-arrow-up' : 'fa-arrow-down'"
                ></i>
              </th>
              <th class="w-[100px] px-3.5 py-2.5 text-right font-semibold">
                <i class="fa-solid fa-gears"></i>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="record in visible"
              :key="record.id"
              class="border-t border-p-line2 hover:bg-p-panel2"
            >
              <td class="px-3.5 py-2.5">
                <i
                  class="fa-solid fa-cloud"
                  :class="record.proxied ? 'text-p-warn' : 'text-p-ink3 opacity-50'"
                  :title="record.proxied ? __('general.active') : __('general.passive')"
                ></i>
              </td>
              <td class="px-3.5 py-2.5"><span class="p-chip">{{ record.type }}</span></td>
              <td class="break-all px-3.5 py-2.5 font-semibold">{{ record.name }}</td>
              <td class="break-all px-3.5 py-2.5 text-p-ink2">{{ record.content }}</td>
              <td class="px-3.5 py-2.5 tabular-nums">{{ ttlLabel(record.ttl) }}</td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <button class="p-icon-btn" :title="__('general.edit')" @click="edit(record)">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <button
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('general.delete')"
                    @click="destroy(record)"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!visible.length">
              <td colspan="6" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('general.no_records') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <DnsRecordModal v-model:open="modalOpen" :record="editing" @saved="refresh" />
    <ConfirmDialog ref="confirm" />
  </div>
</template>
