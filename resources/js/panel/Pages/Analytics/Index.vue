<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import ApexChart from '../../components/ApexChart.vue';

/*
 * panel/analytics.blade.php karşılığı.
 *
 * Grafik seçenekleri blade'deki ApexCharts konfigürasyonlarıyla aynı; CDN'den
 * yüklenen apexcharts + moment + daterangepicker düşüyor. Tarih aralığı
 * sözleşmesi KORUNUR: `?date_range=m/d/Y - m/d/Y`, controller onu
 * `explode(' - ')` ile ayırıyor.
 */
const props = defineProps({
  configured: { type: Boolean, default: false },
  /*
   * 'ok' | 'not_configured' | 'error'.
   *
   * `configured` tek başına YETMİYORDU: kimlik dosyası kurulu ama Google
   * isteği patlamışsa da ekran boş kalıyordu ve kullanıcı hangi durumda
   * olduğunu anlayamıyordu.
   */
  status: { type: String, default: 'ok' },
  dateRange: { type: String, default: '' },
  overview: { type: [Object, Array], default: () => ({}) },
  trend: { type: Object, default: () => ({ current: [], previous: [] }) },
  topBrowsers: { type: Array, default: () => [] },
  topCountries: { type: Array, default: () => [] },
  operatingSystem: { type: Array, default: () => [] },
  userTypes: { type: Array, default: () => [] },
  totalVisitorsAndPageViews: { type: Array, default: () => [] },
  viewData: { type: Array, default: () => [] },
});

usePageHeader(__('dashboard.analytics'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('dashboard.analytics') },
]);

const METRICS = [
  { key: 'active_users', label: 'Aktif Kullanıcı' },
  { key: 'new_users', label: 'Yeni Kullanıcı' },
  { key: 'pageviews', label: __('dashboard.page_views') },
  { key: 'events', label: 'Etkinlik Sayısı' },
  { key: 'engagement_time', label: 'Ort. Etkileşim' },
];

/* 'm/d/Y - m/d/Y' ↔ <input type="date"> (yyyy-mm-dd) çevirisi. */
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
    route('admin.analytics'),
    { date_range: `${toRange(start.value)} - ${toRange(end.value)}` },
    { preserveState: true, preserveScroll: true, replace: true },
  );
}

const metrics = computed(() =>
  METRICS.map((metric) => ({ ...metric, data: props.overview?.[metric.key] })).filter(
    (metric) => metric.data,
  ),
);

function number(value) {
  return typeof value === 'number' ? value.toLocaleString() : value;
}

function pie(rows, labelKey, valueKey) {
  return {
    series: rows.map((row) => Number(row[valueKey] ?? 0)),
    options: { labels: rows.map((row) => String(row[labelKey] ?? '')), chart: { type: 'pie' } },
  };
}

const browsers = computed(() => pie(props.topBrowsers, 'browser', 'screenPageViews'));
const countries = computed(() => pie(props.topCountries, 'country', 'screenPageViews'));
const systems = computed(() => pie(props.operatingSystem, 'operatingSystem', 'screenPageViews'));

const userTypeChart = computed(() => {
  const label = (type) =>
    type === 'new'
      ? __('dashboard.user_type.new')
      : type === 'returning'
        ? __('dashboard.user_type.returning')
        : __('dashboard.user_type.others');

  return {
    series: props.userTypes.map((row) => Number(row.activeUsers ?? 0)),
    options: {
      labels: props.userTypes.map((row) => label(row.newVsReturning)),
      chart: { type: 'pie' },
    },
  };
});

const trendChart = computed(() => ({
  series: [
    { name: __('dashboard.total_visitors'), data: props.trend.current.map((row) => row.activeUsers) },
    {
      name: __('general.previous'),
      data: props.trend.previous.map((row) => row.activeUsers),
    },
  ],
  options: {
    chart: { type: 'area' },
    stroke: { curve: 'smooth', width: 2 },
    dataLabels: { enabled: false },
    xaxis: { categories: props.trend.current.map((row) => row.date) },
  },
}));

const visitorsChart = computed(() => ({
  series: [
    {
      name: __('dashboard.total_visitors'),
      data: props.totalVisitorsAndPageViews.map((row) => Number(row.activeUsers ?? 0)),
    },
    {
      name: __('dashboard.total_page_views'),
      data: props.totalVisitorsAndPageViews.map((row) => Number(row.screenPageViews ?? 0)),
    },
  ],
  options: {
    chart: { type: 'bar', stacked: true },
    xaxis: {
      type: 'datetime',
      categories: props.totalVisitorsAndPageViews.map((row) => row.date),
    },
  },
}));

const topViewedChart = computed(() => ({
  series: [
    {
      name: __('dashboard.page_views'),
      data: props.viewData.map((row) => Number(row.screenPageViews ?? 0)),
    },
  ],
  options: {
    chart: { type: 'bar' },
    plotOptions: { bar: { borderRadius: 6, horizontal: true, dataLabels: { position: 'top' } } },
    dataLabels: { enabled: true, offsetX: -20, style: { fontSize: '12px' } },
    xaxis: { categories: props.viewData.map((row) => String(row.pageTitle ?? '')) },
  },
}));
</script>

<template>
  <Head :title="__('dashboard.analytics')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-end gap-2">
      <div>
        <label class="p-label">{{ __('general.date') }}</label>
        <div class="flex items-center gap-2">
          <input v-model="start" type="date" class="p-input w-auto" />
          <span class="text-p-ink3">–</span>
          <input v-model="end" type="date" class="p-input w-auto" />
          <button class="p-btn" @click="apply">
            <i class="fa-solid fa-rotate text-xs"></i> {{ __('general.apply') }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="status !== 'ok'" class="p-card p-4 text-[12.5px] leading-relaxed text-p-ink2">
      <i class="fa-solid fa-circle-info mr-1.5 text-p-accent"></i>
      <template v-if="status === 'not_configured'">
        {{ __('dashboard.ga4_not_configured') }}
        <div class="mt-1 text-p-ink3">{{ __('dashboard.ga4_not_configured_hint') }}</div>
      </template>
      <template v-else>
        {{ __('dashboard.data_fetch_failed') }}
        <div class="mt-1 text-p-ink3">{{ __('dashboard.data_fetch_failed_hint') }}</div>
      </template>
    </div>

    <template v-else>
      <div v-if="metrics.length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div v-for="metric in metrics" :key="metric.key" class="p-card p-3.5">
          <div class="text-[11px] uppercase tracking-[.06em] text-p-ink3">{{ metric.label }}</div>
          <div class="mt-1 font-display text-[20px] font-bold tabular-nums">
            {{ number(metric.data.value) }}
          </div>
          <span
            v-if="metric.data.change !== null && metric.data.change !== undefined"
            class="p-chip mt-1"
            :class="metric.data.change >= 0 ? '!bg-p-ok !text-white' : '!bg-p-danger !text-white'"
          >
            {{ metric.data.change >= 0 ? '▲' : '▼' }} {{ Math.abs(metric.data.change) }}%
          </span>
        </div>
      </div>

      <div v-if="trend.current.length" class="p-card p-4">
        <div class="mb-2 font-display text-[13.5px] font-bold">Aktif Kullanıcı Trendi</div>
        <ApexChart type="area" :series="trendChart.series" :options="trendChart.options" />
      </div>

      <div class="grid gap-3.5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">
            {{ __('dashboard.top_browsers') }}
          </div>
          <ApexChart type="pie" :series="browsers.series" :options="browsers.options" />
        </div>
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">
            {{ __('dashboard.top_countries') }}
          </div>
          <ApexChart type="pie" :series="countries.series" :options="countries.options" />
        </div>
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">
            {{ __('dashboard.top_operating_systems') }}
          </div>
          <ApexChart type="pie" :series="systems.series" :options="systems.options" />
        </div>
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">{{ __('dashboard.user_types') }}</div>
          <ApexChart type="pie" :series="userTypeChart.series" :options="userTypeChart.options" />
        </div>
      </div>

      <div class="grid gap-3.5 lg:grid-cols-2">
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">
            {{ __('dashboard.total_visitors_and_page_views') }}
          </div>
          <ApexChart
            type="bar"
            :height="350"
            :series="visitorsChart.series"
            :options="visitorsChart.options"
          />
        </div>
        <div class="p-card p-4">
          <div class="mb-2 font-display text-[13px] font-bold">{{ __('dashboard.page_views') }}</div>
          <ApexChart
            type="bar"
            :height="350"
            :series="topViewedChart.series"
            :options="topViewedChart.options"
          />
        </div>
      </div>
    </template>
  </div>
</template>
