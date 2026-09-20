<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import ApexChart from '../../components/ApexChart.vue';

/*
 * panel/search-console.blade.php karşılığı.
 *
 * Tarih aralığı sözleşmesi korunur (`?date_range=m/d/Y - m/d/Y`).
 * Anahtar kelime tablosunun filtre + sıralaması blade'de elle yazılmış JS'ti;
 * burada hesaplanan değerler. Konum rozetinde eşikler aynı: ≤3 yeşil,
 * ≤10 sarı, üstü kırmızı. Konumda DÜŞÜK iyi olduğu için değişim yönü ters
 * yorumlanır — blade'deki `$isPositive` mantığının aynısı.
 */
const props = defineProps({
  configured: { type: Boolean, default: false },
  // 'ok' | 'not_configured' | 'error' — yapılandırılmamış olmakla Google
  // isteğinin patlaması ayrı şeyler; ekran ikisini ayrı anlatır.
  status: { type: String, default: 'ok' },
  dateRange: { type: String, default: '' },
  performance: { type: [Object, Array], default: () => ({}) },
  keywords: { type: Array, default: () => [] },
  trend: { type: Array, default: () => [] },
  settingsUrl: { type: String, default: '' },
});

usePageHeader('Search Console', [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: 'Search Console' },
]);

function toInput(value) {
  const [month, day, year] = String(value || '').split('/');

  return year ? `${year}-${month}-${day}` : '';
}

function toRange(value) {
  const [year, month, day] = String(value || '').split('-');

  return year ? `${month}/${day}/${year}` : '';
}

const parts = String(props.dateRange).split(' - ');
const start = ref(toInput(parts[0]));
const end = ref(toInput(parts[1]));

function apply() {
  if (!start.value || !end.value) {
    return;
  }

  router.get(
    route('admin.search-console'),
    { date_range: `${toRange(start.value)} - ${toRange(end.value)}` },
    { preserveState: true, preserveScroll: true, replace: true },
  );
}

const CARDS = [
  { key: 'clicks', label: 'Toplam Tıklama', suffix: '' },
  { key: 'impressions', label: 'Toplam Gösterim', suffix: '' },
  { key: 'ctr', label: 'Ort. TO (CTR)', suffix: '%' },
  { key: 'position', label: 'Ort. Konum', suffix: '' },
];

const cards = computed(() => {
  const current = props.performance?.current || {};
  const previous = props.performance?.previous || {};

  return CARDS.map((card) => {
    const now = Number(current[card.key] ?? 0);
    const before = Number(previous[card.key] ?? 0);
    const change = before > 0 ? Math.round(((now - before) / before) * 1000) / 10 : null;
    // Konumda düşük iyi → yön ters.
    const positive =
      card.key === 'position' ? change !== null && change < 0 : change !== null && change >= 0;

    return {
      ...card,
      value: card.key === 'position' ? now : now.toLocaleString(),
      change,
      positive,
    };
  });
});

const trendChart = computed(() => ({
  series: [
    { name: 'Tıklamalar', data: props.trend.map((row) => Number(row.clicks ?? 0)) },
    { name: 'Gösterimler', data: props.trend.map((row) => Number(row.impressions ?? 0)) },
  ],
  options: {
    chart: { type: 'line' },
    stroke: { curve: 'smooth', width: 2 },
    colors: ['#198754', '#0d6efd'],
    xaxis: { categories: props.trend.map((row) => row.date) },
    yaxis: [
      { title: { text: 'Tıklamalar' }, labels: { style: { colors: '#198754' } } },
      { opposite: true, title: { text: 'Gösterimler' }, labels: { style: { colors: '#0d6efd' } } },
    ],
  },
}));

const COLUMNS = [
  { key: 'query', label: 'Anahtar Kelime', align: 'text-left' },
  { key: 'clicks', label: 'Tıklama', align: 'text-right' },
  { key: 'impressions', label: 'Gösterim', align: 'text-right' },
  { key: 'ctr', label: 'TO', align: 'text-right' },
  { key: 'position', label: 'Konum', align: 'text-right' },
];

const filter = ref('');
const sortKey = ref('clicks');
const sortAsc = ref(false);

const rows = computed(() => {
  const term = filter.value.trim().toLowerCase();
  const list = term
    ? props.keywords.filter((row) => String(row.query ?? '').toLowerCase().includes(term))
    : [...props.keywords];

  const key = sortKey.value;
  const direction = sortAsc.value ? 1 : -1;

  return list.sort((a, b) => {
    if (key === 'query') {
      return String(a.query ?? '').localeCompare(String(b.query ?? '')) * direction;
    }

    return (Number(a[key] ?? 0) - Number(b[key] ?? 0)) * direction;
  });
});

function sortBy(key) {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value;
    return;
  }

  sortKey.value = key;
  sortAsc.value = key === 'query' || key === 'position';
}

function positionClass(position) {
  if (position <= 3) {
    return '!bg-p-ok !text-white';
  }

  return position <= 10 ? '!bg-p-warn !text-white' : '!bg-p-danger !text-white';
}
</script>

<template>
  <Head title="Search Console" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-end justify-end gap-2">
      <input v-model="start" type="date" class="p-input w-auto" />
      <span class="pb-2 text-p-ink3">–</span>
      <input v-model="end" type="date" class="p-input w-auto" />
      <button class="p-btn" @click="apply">
        <i class="fa-solid fa-rotate text-xs"></i> {{ __('general.apply') }}
      </button>
    </div>

    <div v-if="status === 'error'" class="p-card p-4 text-[12.5px] leading-relaxed text-p-ink2">
      <i class="fa-solid fa-triangle-exclamation mr-1.5 text-p-danger"></i>
      {{ __('dashboard.data_fetch_failed') }}
      <div class="mt-1 text-p-ink3">{{ __('dashboard.data_fetch_failed_hint') }}</div>
    </div>

    <div v-else-if="!configured" class="p-card p-4 text-[12.5px] leading-relaxed text-p-ink2">
      <i class="fa-solid fa-circle-info mr-1.5 text-p-accent"></i>
      Search Console entegrasyonu yapılandırılmamış.
      <code class="rounded bg-p-panel2 px-1">storage/app/analytics/service-account-credentials.json</code>
      dosyasının mevcut olduğundan ve
      <a :href="settingsUrl" class="text-p-accent underline">Google Indexing ayarlarından</a>
      site URL'sinin girildiğinden emin olun.
    </div>

    <template v-else>
      <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="card in cards" :key="card.key" class="p-card p-3.5">
          <div class="text-[11px] uppercase tracking-[.06em] text-p-ink3">{{ card.label }}</div>
          <div class="mt-1 font-display text-[20px] font-bold tabular-nums">
            {{ card.value }}{{ card.suffix }}
          </div>
          <span
            v-if="card.change !== null"
            class="p-chip mt-1"
            :class="card.positive ? '!bg-p-ok !text-white' : '!bg-p-danger !text-white'"
          >
            {{ card.positive ? '▲' : '▼' }} {{ Math.abs(card.change) }}%
          </span>
        </div>
      </div>

      <div v-if="trend.length" class="p-card p-4">
        <div class="mb-2 font-display text-[13.5px] font-bold">Tıklama &amp; Gösterim Trendi</div>
        <ApexChart type="line" :height="350" :series="trendChart.series" :options="trendChart.options" />
      </div>

      <div class="p-card overflow-hidden">
        <div class="flex items-center gap-2 border-b border-p-line2 px-4 py-3">
          <span class="font-display text-[13px] font-bold">
            Anahtar Kelime Performansı (Top 50)
          </span>
          <div class="flex-1"></div>
          <input
            v-model="filter"
            placeholder="Filtrele…"
            class="h-8 w-[200px] rounded-[9px] border border-p-line bg-p-panel px-2.5 text-[12.5px] text-p-ink outline-none"
          />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[720px] border-collapse text-[12.5px]">
            <thead>
              <tr class="bg-p-panel2 text-[11px] uppercase tracking-[.06em] text-p-ink3">
                <th
                  v-for="column in COLUMNS"
                  :key="column.key"
                  class="cursor-pointer select-none px-3.5 py-2.5 font-semibold"
                  :class="column.align"
                  @click="sortBy(column.key)"
                >
                  {{ column.label }}
                  <i
                    v-if="sortKey === column.key"
                    class="fa-solid ml-1 text-[9px]"
                    :class="sortAsc ? 'fa-arrow-up' : 'fa-arrow-down'"
                  ></i>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.query" class="border-t border-p-line2 hover:bg-p-panel2">
                <td class="break-all px-3.5 py-2.5">{{ row.query }}</td>
                <td class="px-3.5 py-2.5 text-right font-bold tabular-nums">
                  {{ Number(row.clicks).toLocaleString() }}
                </td>
                <td class="px-3.5 py-2.5 text-right tabular-nums">
                  {{ Number(row.impressions).toLocaleString() }}
                </td>
                <td class="px-3.5 py-2.5 text-right tabular-nums">{{ row.ctr }}%</td>
                <td class="px-3.5 py-2.5 text-right">
                  <span class="p-chip" :class="positionClass(row.position)">{{ row.position }}</span>
                </td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="5" class="px-3.5 py-10 text-center text-p-ink3">
                  {{ __('general.no_records') }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
