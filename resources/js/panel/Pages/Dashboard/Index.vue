<script setup>
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import Modal from '../../components/Modal.vue';
import CoreWidget from '../../Widgets/CoreWidget.vue';
import { CORE_WIDGETS, resolveModuleWidget } from '../../Widgets/registry';

/*
 * panel/dashboard.blade.php karşılığı.
 *
 * gridstack yerine 12 kolonlu CSS grid + HTML5 sürükle-bırak. Kayıt yükü
 * DEĞİŞMEZ: `layout[] = {type, x, y, w, h}` — `DashboardWidgetTest` ve
 * `dashboard_widgets` tablosundaki satırlar aynen çalışır. `x`/`y` düzenleme
 * sonrası sıradan yeniden türetilir; genişlik/yükseklik adımlayıcıyla ayarlanır.
 */
const props = defineProps({
  widgets: { type: Array, default: () => [] },
  widgetGroups: { type: Object, default: () => ({}) },
  widgetData: { type: Object, default: () => ({}) },
});

usePageHeader(__('dashboard.dashboard'), [{ label: __('dashboard.dashboard') }]);

const COLUMNS = 12;
const ROW_HEIGHT = 80;

// Blade sıralaması gs_y, gs_x idi; aynı sırayı koru.
const items = ref(
  [...props.widgets].sort((a, b) => a.y - b.y || a.x - b.x).map((widget) => ({ ...widget })),
);

const editing = ref(false);
const libraryOpen = ref(false);
const dragIndex = ref(null);

const moduleComponents = {};

function componentFor(type) {
  if (!type.includes('::')) {
    return null;
  }

  if (!moduleComponents[type]) {
    const importer = resolveModuleWidget(type);

    moduleComponents[type] = importer ? defineAsyncComponent(importer) : false;
  }

  return moduleComponents[type];
}

function configFor(type) {
  return CORE_WIDGETS[type] || null;
}

function labelFor(type) {
  for (const group of Object.values(props.widgetGroups)) {
    if (group[type]) {
      return group[type].label;
    }
  }

  return type;
}

const groups = computed(() => Object.entries(props.widgetGroups));

function add(type, config) {
  items.value.push({
    id: `new-${items.value.length}-${type}`,
    type,
    x: 0,
    y: items.value.length,
    w: Number(config.w ?? 3),
    h: Number(config.h ?? 2),
  });

  libraryOpen.value = false;
  editing.value = true;
  save();
}

function remove(index) {
  items.value.splice(index, 1);
  save();
}

function resize(item, axis, delta) {
  const limits = axis === 'w' ? [1, COLUMNS] : [1, 8];

  item[axis] = Math.min(limits[1], Math.max(limits[0], item[axis] + delta));
  save();
}

const dropIndex = ref(null);
const gridEl = ref(null);

function onDragStart(index, event) {
  dragIndex.value = index;
  dropIndex.value = index;

  if (event?.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    // Firefox surukleme baslatmak icin bir veri yuku sart kosuyor.
    event.dataTransfer.setData('text/plain', String(index));
  }
}

function onDragOver(index) {
  if (dragIndex.value !== null) {
    dropIndex.value = index;
  }
}

function onDragEnd() {
  dragIndex.value = null;
  dropIndex.value = null;
}

function onDrop(index) {
  const from = dragIndex.value;

  onDragEnd();

  if (from === null || from === index) {
    return;
  }

  const [moved] = items.value.splice(from, 1);
  items.value.splice(index, 0, moved);
  save();
}

/*
 * SURUKLEYEREK BOYUTLANDIRMA — gridstack'in kose tutamaginin karsiligi.
 *
 * Ok dugmeleri duruyor (klavye/dokunmatik icin ve tek adimlik ince ayar icin),
 * ama fare ile kartin kosesini tutup cekmek asil beklenen davranis.
 *
 * Kolon genisligi SABIT YAZILMAZ: grid `grid-cols-1 / md:6 / xl:12` ile
 * degisiyor. `getComputedStyle().gridTemplateColumns` cozulmus parca listesini
 * verdigi icin hem kolon SAYISI hem tek kolon GENISLIGI oradan okunur; boylece
 * hesap kirilma noktasindan bagimsiz dogru olur.
 */
const resizing = ref(null);

function gridMetrics() {
  if (! gridEl.value) {
    return null;
  }

  const style = getComputedStyle(gridEl.value);
  const tracks = style.gridTemplateColumns.split(' ').filter(Boolean).map(parseFloat);

  return {
    count: tracks.length,
    column: tracks[0] || 0,
    columnGap: parseFloat(style.columnGap) || 0,
    rowGap: parseFloat(style.rowGap) || 0,
  };
}

/*
 * Tutamak YALNIZCA 12 kolonlu genislikte. Dar ekranda grid 6 ya da 1 kolona
 * dusuyor; orada boyutlandirmak `w` degerini 6'ya kirpar ve MASAUSTU yerlesimi
 * sessizce bozardi. Kayit 12 kolonluk uzayda tutuluyor.
 */
/*
 * `viewportWidth` computed'in BAGIMLILIGI olsun diye var: `getComputedStyle`
 * reaktif degil, dolayisiyla pencere yeniden boyutlandirilip kirilma noktasi
 * degistiginde `canResize` bayat kalirdi (dar ekrana gecince tutamak durmaya
 * devam ederdi).
 */
const viewportWidth = ref(typeof window === 'undefined' ? 0 : window.innerWidth);

function onViewportResize() {
  viewportWidth.value = window.innerWidth;
}

onMounted(() => window.addEventListener('resize', onViewportResize));
onUnmounted(() => window.removeEventListener('resize', onViewportResize));

const canResize = computed(() => {
  // eslint-disable-next-line no-unused-expressions
  viewportWidth.value;

  const metrics = gridMetrics();

  return metrics !== null && metrics.count === COLUMNS;
});

function startResize(item, event) {
  const metrics = gridMetrics();

  if (! metrics || metrics.count !== COLUMNS) {
    return;
  }

  event.preventDefault();
  event.stopPropagation();

  resizing.value = {
    item,
    startX: event.clientX,
    startY: event.clientY,
    startW: item.w,
    startH: item.h,
    step: metrics.column + metrics.columnGap,
    rowStep: ROW_HEIGHT + metrics.rowGap,
  };

  event.currentTarget.setPointerCapture?.(event.pointerId);
}

function moveResize(event) {
  const state = resizing.value;

  if (! state) {
    return;
  }

  const columns = Math.round((event.clientX - state.startX) / state.step);
  const rows = Math.round((event.clientY - state.startY) / state.rowStep);

  state.item.w = Math.min(COLUMNS, Math.max(1, state.startW + columns));
  state.item.h = Math.min(8, Math.max(1, state.startH + rows));
}

/* Kaydetme YALNIZCA birakilinca: her pikselde POST atmak sunucuyu doverdi. */
function endResize() {
  if (! resizing.value) {
    return;
  }

  const changed =
    resizing.value.item.w !== resizing.value.startW || resizing.value.item.h !== resizing.value.startH;

  resizing.value = null;

  if (changed) {
    save();
  }
}

/* Sıradan 12 kolonluk yerleşime geri dönüştürür (gridstack'in yaptığının aynısı). */
function layout() {
  let x = 0;
  let y = 0;

  return items.value.map((item) => {
    if (x + item.w > COLUMNS) {
      x = 0;
      y += 1;
    }

    const placed = { type: item.type, x, y, w: item.w, h: item.h };
    x += item.w;

    return placed;
  });
}

function save() {
  router.post(
    route('admin.dashboard.widgets.save'),
    { layout: layout() },
    { preserveScroll: true, preserveState: true },
  );
}
</script>

<template>
  <Head :title="__('dashboard.dashboard')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex justify-end gap-2">
      <button v-if="items.length" class="p-btn" @click="editing = !editing">
        <i class="fa-solid fa-pen text-xs"></i>
        {{ editing ? __('general.close') : __('general.edit') }}
      </button>
      <button class="p-btn-primary" @click="libraryOpen = true">
        <i class="fa-solid fa-plus text-xs"></i> Widget Ekle
      </button>
    </div>

    <div v-if="!items.length" class="p-card px-4 py-14 text-center">
      <i class="fa-solid fa-table-cells-large mb-3 block text-[32px] text-p-ink3"></i>
      <p class="mb-3 text-[12.5px] text-p-ink3">
        Dashboard henüz boş. Widget ekleyerek özelleştirin.
      </p>
      <button class="p-btn-primary" @click="libraryOpen = true">
        <i class="fa-solid fa-plus text-xs"></i> Widget Ekle
      </button>
    </div>

    <div ref="gridEl" v-else class="grid grid-cols-1 gap-3.5 md:grid-cols-6 xl:grid-cols-12">
      <div
        v-for="(item, index) in items"
        :key="item.id"
        class="p-card relative overflow-hidden transition-shadow"
        :class="[
          dragIndex === index ? 'opacity-50' : '',
          dropIndex === index && dragIndex !== null && dragIndex !== index
            ? 'ring-2 ring-p-accent'
            : '',
          editing ? 'cursor-grab' : '',
        ]"
        :style="{
          gridColumn: `span ${Math.min(item.w, COLUMNS)} / span ${Math.min(item.w, COLUMNS)}`,
          minHeight: `${item.h * ROW_HEIGHT}px`,
        }"
        :draggable="editing && !resizing"
        @dragstart="onDragStart(index, $event)"
        @dragover.prevent="onDragOver(index)"
        @dragend="onDragEnd"
        @drop.prevent="onDrop(index)"
      >
        <div
          v-if="editing"
          class="absolute right-1.5 top-1.5 z-10 flex items-center gap-1 rounded-lg bg-p-panel2/90 p-1"
        >
          <button class="p-icon-btn !h-6 !w-6 !text-[10px]" title="-" @click="resize(item, 'w', -1)">
            <i class="fa-solid fa-left-long"></i>
          </button>
          <button class="p-icon-btn !h-6 !w-6 !text-[10px]" title="+" @click="resize(item, 'w', 1)">
            <i class="fa-solid fa-right-long"></i>
          </button>
          <button class="p-icon-btn !h-6 !w-6 !text-[10px]" title="-" @click="resize(item, 'h', -1)">
            <i class="fa-solid fa-up-long"></i>
          </button>
          <button class="p-icon-btn !h-6 !w-6 !text-[10px]" title="+" @click="resize(item, 'h', 1)">
            <i class="fa-solid fa-down-long"></i>
          </button>
          <button
            class="p-icon-btn !h-6 !w-6 !text-[10px] hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="remove(index)"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!--
          Kose tutamagi: fare ile dogrudan boyutlandirma. Yalnizca 12 kolonlu
          genislikte gorunur — dar ekranda `w` degeri kirpilip masaustu
          yerlesimini bozardi.
        -->
        <div
          v-if="editing && canResize"
          class="absolute bottom-0 right-0 z-10 grid h-5 w-5 cursor-nwse-resize place-items-center rounded-tl-lg bg-p-panel2/90 text-[9px] text-p-ink3 hover:text-p-accent"
          :title="__('dashboard.resize_hint')"
          draggable="false"
          @pointerdown="startResize(item, $event)"
          @pointermove="moveResize"
          @pointerup="endResize"
          @pointercancel="endResize"
        >
          <i class="fa-solid fa-up-right-and-down-left-from-center rotate-90"></i>
        </div>

        <CoreWidget v-if="configFor(item.type)" :config="configFor(item.type)" :data="widgetData" />

        <component
          :is="componentFor(item.type)"
          v-else-if="componentFor(item.type)"
          :widget="item"
          :widget-data="widgetData"
        />

        <!-- Modül kayıtlı ama Vue karşılığı henüz yok (Faz 7'ye kadar). -->
        <div v-else class="flex h-full flex-col justify-center p-3 text-[12px] text-p-ink3">
          <div class="font-semibold text-p-ink2">{{ labelFor(item.type) }}</div>
          <div>{{ __('general.not_available') }}</div>
        </div>
      </div>
    </div>

    <Modal v-model:open="libraryOpen" title="Widget Ekle" icon="fa-solid fa-table-cells-large" width="720px">
      <div class="flex flex-col gap-4">
        <div v-for="[group, entries] in groups" :key="group">
          <div class="mb-2 text-[11px] font-bold uppercase tracking-[.06em] text-p-ink3">
            {{ group }}
          </div>
          <div class="grid gap-2 sm:grid-cols-3">
            <button
              v-for="(config, type) in entries"
              :key="type"
              class="p-btn justify-start !text-left"
              @click="add(type, config)"
            >
              <span class="truncate text-[11.5px]">{{ config.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <template #footer>
        <button class="p-btn" @click="libraryOpen = false">{{ __('general.close') }}</button>
      </template>
    </Modal>
  </div>
</template>
