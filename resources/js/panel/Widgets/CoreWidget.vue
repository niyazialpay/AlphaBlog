<script setup>
/**
 * `resources/views/panel/widgets/*.blade.php` (18 dosya) karşılığı.
 *
 * Blade'de her widget kendi dosyasıydı ama üç şekilden ibaretti: metrik kartı,
 * ApexCharts grafiği, küçük tablo. Burada tek bileşen `registry.js`'teki
 * yapılandırmadan sürülür — 18 neredeyse aynı dosya yerine bir tanesi.
 */
import { computed } from 'vue';
import { __ } from '../composables/useLang';
import { formatDateTime } from '../composables/useFormat';
import ApexChart from '../components/ApexChart.vue';
import { pick } from './registry';

const props = defineProps({
  config: { type: Object, required: true },
  data: { type: Object, required: true },
});

const rows = computed(() => {
  const value = props.config.source ? pick(props.data, props.config.source) : null;

  return Array.isArray(value) ? value : [];
});

const metric = computed(() => (props.config.source ? pick(props.data, props.config.source) : null));

const gscMetric = computed(() => {
  const current = props.data?.gsc?.performance?.current || {};
  const previous = props.data?.gsc?.performance?.previous || {};
  const value = current[props.config.metric];

  if (value === undefined || value === null) {
    return null;
  }

  const before = Number(previous[props.config.metric] ?? 0);
  const change = before > 0 ? Math.round(((value - before) / before) * 1000) / 10 : null;

  return {
    value: props.config.metric === 'position' ? value : Number(value).toLocaleString(),
    change,
    positive:
      change === null
        ? null
        : props.config.lowerIsBetter
          ? change < 0
          : change >= 0,
  };
});

const pieChart = computed(() => {
  const label = (row) => {
    const raw = String(row[props.config.labelKey] ?? '');

    if (props.config.labelKey !== 'newVsReturning') {
      return raw;
    }

    return raw === 'new'
      ? __('dashboard.user_type.new')
      : raw === 'returning'
        ? __('dashboard.user_type.returning')
        : __('dashboard.user_type.others');
  };

  return {
    series: rows.value.map((row) => Number(row[props.config.valueKey] ?? 0)),
    options: {
      labels: rows.value.map(label),
      chart: { type: 'pie' },
      legend: { position: 'bottom' },
    },
  };
});

const trendChart = computed(() => ({
  series: [
    { name: 'Kullanıcı', data: rows.value.map((row) => Number(row.activeUsers ?? 0)) },
    { name: 'Görüntüleme', data: rows.value.map((row) => Number(row.screenPageViews ?? 0)) },
  ],
  options: {
    chart: { type: 'line' },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: rows.value.map((row) => row.date), type: 'datetime' },
    legend: { show: false },
  },
}));

const topPagesChart = computed(() => ({
  series: [{ name: 'Görüntüleme', data: rows.value.map((row) => Number(row.screenPageViews ?? 0)) }],
  options: {
    chart: { type: 'bar' },
    plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
    xaxis: { categories: rows.value.map((row) => String(row.pageTitle ?? '')) },
  },
}));

/*
 * Widget'in hangi veri kaynagina bagli oldugu — bos durum mesaji buna gore
 * secilir. `site_*` widget'lari (yorumlar, firewall) hicbir dis servise
 * bagli degil; onlarda bos = gercekten kayit yok.
 */
const family = computed(() => {
  if (props.config.kind === 'gsc-metric') {
    return 'gsc';
  }

  const source = String(props.config.source || '');

  if (source.startsWith('ga4.')) {
    return 'ga4';
  }

  return source.startsWith('gsc.') ? 'gsc' : null;
});

const hasContent = computed(() => {
  if (props.config.kind === 'metric') {
    return metric.value !== null && metric.value !== undefined;
  }

  if (props.config.kind === 'gsc-metric') {
    return gscMetric.value !== null;
  }

  return rows.value.length > 0;
});

/*
 * Boş durum ARTIK TEK BİR MESAJ DEĞİL.
 *
 * Eskiden beş dalın hepsi `general.not_available` ("Bu servis şu an
 * kullanılamıyor.") gösteriyordu; entegrasyonun hiç kurulmamış olması,
 * Google isteğinin patlaması ve tarih aralığında gerçekten satır olmaması
 * ayırt edilemiyordu. Durum sunucudan `widgetData.status` ile geliyor.
 */
const fallback = computed(() => {
  const status = family.value ? props.data?.status?.[family.value] : null;

  if (status === 'not_configured') {
    return {
      text: __(`dashboard.${family.value}_not_configured`),
      link: family.value === 'gsc' ? props.data?.settingsUrl || null : null,
    };
  }

  if (status === 'error') {
    return { text: __('dashboard.data_fetch_failed'), link: null };
  }

  return { text: __('general.no_records'), link: null };
});

function positionClass(position) {
  if (position <= 3) {
    return '!bg-p-ok !text-white';
  }

  return position <= 10 ? '!bg-p-warn !text-white' : '!bg-p-danger !text-white';
}
</script>

<template>
  <div class="flex h-full flex-col overflow-hidden">
    <div class="flex items-center gap-2 px-3 pt-2.5">
      <span class="text-[11px] font-semibold uppercase tracking-[.06em] text-p-ink3">
        {{ config.label }}
      </span>
      <div class="flex-1"></div>
      <Link
        v-if="config.moreRoute"
        :href="route(config.moreRoute)"
        class="text-[10px] text-p-ink3 no-underline hover:text-p-accent"
      >
        {{ __('general.more') }} →
      </Link>
    </div>

    <!-- Ayırt edilebilir boş durum: yapılandırılmamış / istek patladı / kayıt yok -->
    <div v-if="!hasContent" class="flex min-h-0 flex-1 flex-col justify-center gap-1 px-3 pb-3">
      <div class="text-[12px] leading-snug text-p-ink3">{{ fallback.text }}</div>
      <a
        v-if="fallback.link"
        :href="fallback.link"
        class="w-max text-[11px] text-p-accent underline"
      >
        {{ __('general.settings') }} →
      </a>
    </div>

    <!-- Metrik kartları -->
    <div v-else-if="config.kind === 'metric'" class="flex flex-1 flex-col justify-center px-3 pb-3">
      <div class="font-display text-[24px] font-bold tabular-nums">
        {{ typeof metric.value === 'number' ? metric.value.toLocaleString() : metric.value }}
      </div>
      <span
        v-if="metric.change !== null && metric.change !== undefined"
        class="p-chip mt-1 w-max"
        :class="metric.change >= 0 ? '!bg-p-ok !text-white' : '!bg-p-danger !text-white'"
      >
        {{ metric.change >= 0 ? '▲' : '▼' }} {{ Math.abs(metric.change) }}%
      </span>
    </div>

    <div v-else-if="config.kind === 'gsc-metric'" class="flex flex-1 flex-col justify-center px-3 pb-3">
      <div class="font-display text-[24px] font-bold tabular-nums">
        {{ gscMetric.value }}{{ config.suffix || '' }}
      </div>
      <span
        v-if="gscMetric.change !== null"
        class="p-chip mt-1 w-max"
        :class="gscMetric.positive ? '!bg-p-ok !text-white' : '!bg-p-danger !text-white'"
      >
        {{ gscMetric.positive ? '▲' : '▼' }} {{ Math.abs(gscMetric.change) }}%
      </span>
    </div>

    <!-- Grafikler -->
    <div v-else-if="config.kind === 'pie'" class="min-h-0 flex-1 p-2">
      <ApexChart type="pie" height="100%" :series="pieChart.series" :options="pieChart.options" />
    </div>

    <div v-else-if="config.kind === 'trend'" class="min-h-0 flex-1 p-2">
      <ApexChart type="line" height="100%" :series="trendChart.series" :options="trendChart.options" />
    </div>

    <div v-else-if="config.kind === 'top-pages'" class="min-h-0 flex-1 p-2">
      <ApexChart
        type="bar"
        height="100%"
        :series="topPagesChart.series"
        :options="topPagesChart.options"
      />
    </div>

    <!-- Tablolar -->
    <div v-else class="min-h-0 flex-1 overflow-auto px-1 pb-2">
      <table class="w-full border-collapse text-[11px]">
        <tbody>
          <tr v-for="(row, index) in rows" :key="row.id ?? index" class="border-t border-p-line2">
            <template v-if="config.kind === 'keywords'">
              <td class="max-w-0 truncate px-2 py-1.5">{{ row.query }}</td>
              <td class="px-2 py-1.5 text-right tabular-nums">
                {{ Number(row.clicks).toLocaleString() }}
              </td>
              <td class="px-2 py-1.5 text-right">
                <span class="p-chip !text-[9px]" :class="positionClass(row.position)">
                  {{ row.position }}
                </span>
              </td>
            </template>

            <template v-else-if="config.kind === 'comments'">
              <td class="max-w-0 px-2 py-1.5">
                <div class="font-semibold">{{ row.author }}</div>
                <div class="truncate text-p-ink3">{{ row.comment }}</div>
              </td>
              <td class="whitespace-nowrap px-2 py-1.5 text-right text-[10px] text-p-ink3">
                {{ formatDateTime(row.createdAt, { dateStyle: 'short', timeStyle: 'short' }) }}
              </td>
            </template>

            <template v-else>
              <td class="max-w-0 px-2 py-1.5">
                <div class="font-semibold text-p-danger">{{ row.ip }}</div>
                <div class="truncate text-p-ink3">{{ row.reason }}</div>
              </td>
              <td class="whitespace-nowrap px-2 py-1.5 text-right text-[10px] text-p-ink3">
                {{ formatDateTime(row.createdAt, { dateStyle: 'short', timeStyle: 'short' }) }}
              </td>
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
