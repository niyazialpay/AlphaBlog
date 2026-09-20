<script setup>
/**
 * Sunucu taraflı DataTable (yajra/laravel-datatables) için ince sarmalayıcı.
 * AdminLTE + jQuery DataTables yerine geçer: aynı POST endpoint'ini kullanır.
 *
 * columns: [{ key, label, align, width, cell }]  — `cell` opsiyonel slot adı
 * url:     datatable JSON endpoint'i (POST)
 */
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { __ } from '../composables/useLang';

const props = defineProps({
  url: { type: String, required: true },
  columns: { type: Array, required: true },
  pageLength: { type: Number, default: 10 },
  searchable: { type: Boolean, default: true },
  selectable: { type: Boolean, default: false },
  emptyText: { type: String, default: null },
});
const emit = defineEmits(['selection']);

const rows = ref([]);
const total = ref(0);
const start = ref(0);
const search = ref('');
const order = ref({ column: null, dir: 'desc' });
const loading = ref(false);
const selected = ref(new Set());

async function load() {
  loading.value = true;
  try {
    const { data } = await axios.post(props.url, {
      start: start.value,
      length: props.pageLength,
      search: { value: search.value },
      order: order.value.column ? [{ column: order.value.column, dir: order.value.dir }] : [],
      columns: props.columns.map((c) => ({ data: c.key, name: c.key })),
    });
    rows.value = data.data || [];
    total.value = data.recordsFiltered ?? data.recordsTotal ?? rows.value.length;
  } finally {
    loading.value = false;
  }
}

let t = null;
watch(search, () => { clearTimeout(t); t = setTimeout(() => { start.value = 0; load(); }, 250); });
watch(start, load);
onMounted(load);

function sort(col) {
  if (!col.sortable) return;
  const i = props.columns.indexOf(col);
  order.value = { column: i, dir: order.value.column === i && order.value.dir === 'desc' ? 'asc' : 'desc' };
  load();
}
function toggle(id) {
  selected.value.has(id) ? selected.value.delete(id) : selected.value.add(id);
  selected.value = new Set(selected.value);
  emit('selection', [...selected.value]);
}
function toggleAll(e) {
  selected.value = e.target.checked ? new Set(rows.value.map((r) => r.id)) : new Set();
  emit('selection', [...selected.value]);
}
defineExpose({ reload: load, clearSelection: () => { selected.value = new Set(); emit('selection', []); } });
</script>

<template>
  <div class="flex flex-col gap-3.5">
    <div v-if="searchable || $slots.toolbar" class="flex flex-wrap items-center gap-2">
      <slot name="filters" />
      <div class="flex-1"></div>
      <div v-if="searchable" class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input v-model="search" :placeholder="__('general.search')" class="w-[150px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none">
      </div>
      <slot name="toolbar" />
    </div>

    <div class="p-card overflow-hidden">
      <slot name="bulkbar" :selected="[...selected]" />
      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th v-if="selectable" class="w-9 px-3.5 py-2.5 font-semibold">
                <input type="checkbox" @change="toggleAll">
              </th>
              <th v-for="c in columns" :key="c.key"
                  class="whitespace-nowrap px-3.5 py-2.5 font-semibold"
                  :class="[c.align === 'right' && 'text-right', c.sortable && 'cursor-pointer select-none hover:text-p-ink']"
                  :style="c.width ? { width: c.width } : null" @click="sort(c)">
                {{ c.label }}
                <i v-if="c.sortable && order.column === columns.indexOf(c)"
                   :class="order.dir === 'desc' ? 'fa-solid fa-arrow-down-long' : 'fa-solid fa-arrow-up-long'" class="ml-1 text-[9px]"></i>
              </th>
              <th v-if="$slots.actions" class="px-3.5 py-2.5 text-right font-semibold">{{ __('general.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && !rows.length"><td :colspan="columns.length + 2" class="px-3.5 py-8 text-center text-p-ink3">{{ __('general.loading') }}</td></tr>
            <tr v-else-if="!rows.length"><td :colspan="columns.length + 2" class="px-3.5 py-8 text-center text-p-ink3">{{ emptyText || __('general.no_records') }}</td></tr>
            <tr v-for="row in rows" :key="row.id" class="border-t border-p-line2 hover:bg-p-panel2">
              <td v-if="selectable" class="px-3.5 py-2.5">
                <input type="checkbox" :checked="selected.has(row.id)" @change="toggle(row.id)">
              </td>
              <td v-for="c in columns" :key="c.key" class="px-3.5 py-2.5 align-top"
                  :class="c.align === 'right' ? 'text-right tabular-nums' : ''">
                <slot :name="`cell-${c.key}`" :row="row" :value="row[c.key]">
                  <span class="text-p-ink2" v-html="row[c.key]"></span>
                </slot>
              </td>
              <td v-if="$slots.actions" class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1"><slot name="actions" :row="row" /></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-wrap items-center gap-2.5 border-t border-p-line2 bg-p-panel2 px-3.5 py-2.5 text-xs text-p-ink3">
        <span>{{ total }} {{ __('general.records') }}</span>
        <div class="flex-1"></div>
        <button class="p-icon-btn" :disabled="start === 0" @click="start = Math.max(0, start - pageLength)">
          <i class="fa-solid fa-chevron-left"></i>
        </button>
        <span class="tabular-nums">{{ Math.floor(start / pageLength) + 1 }} / {{ Math.max(1, Math.ceil(total / pageLength)) }}</span>
        <button class="p-icon-btn" :disabled="start + pageLength >= total" @click="start = start + pageLength">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>
