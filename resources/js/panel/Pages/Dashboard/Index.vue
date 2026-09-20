<script setup>
import { computed, defineAsyncComponent, ref } from 'vue';
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

function onDragStart(index) {
  dragIndex.value = index;
}

function onDrop(index) {
  if (dragIndex.value === null || dragIndex.value === index) {
    return;
  }

  const [moved] = items.value.splice(dragIndex.value, 1);
  items.value.splice(index, 0, moved);
  dragIndex.value = null;
  save();
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

    <div v-else class="grid grid-cols-1 gap-3.5 md:grid-cols-6 xl:grid-cols-12">
      <div
        v-for="(item, index) in items"
        :key="item.id"
        class="p-card relative overflow-hidden"
        :style="{
          gridColumn: `span ${Math.min(item.w, COLUMNS)} / span ${Math.min(item.w, COLUMNS)}`,
          minHeight: `${item.h * ROW_HEIGHT}px`,
        }"
        :draggable="editing"
        @dragstart="onDragStart(index)"
        @dragover.prevent
        @drop="onDrop(index)"
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
