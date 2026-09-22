<script setup>
import { computed, defineAsyncComponent, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import Modal from '../../components/Modal.vue';
import CoreWidget from '../../Widgets/CoreWidget.vue';
import { CORE_WIDGETS, resolveModuleWidget } from '../../Widgets/registry';
import { COLUMNS, byPosition, moveNode, normalize, resizeNode } from '../../Widgets/gridLayout';

/*
 * panel/dashboard.blade.php karsiligi — gridstack davranisi:
 * cellHeight 80, margin 10, float false, kartin her yerinden surukleme,
 * sag-alt koseden boyutlandirma, degisiklikten 500 ms sonra otomatik kayit,
 * 768 px altinda tek kolon. Kayit yuku `layout[] = {type, x, y, w, h}`.
 */
const props = defineProps({
  widgets: { type: Array, default: () => [] },
  widgetGroups: { type: Object, default: () => ({}) },
  widgetData: { type: Object, default: () => ({}) },
});

usePageHeader(__('dashboard.dashboard'), [{ label: __('dashboard.dashboard') }]);

const CELL_HEIGHT = 80;
const MARGIN = 10;
const ONE_COLUMN_BELOW = 768;
const DRAG_THRESHOLD = 4;

const items = ref(
  normalize(props.widgets.map(({ id, type, x, y, w, h }) => ({ id, type, x, y, w, h }))),
);

const editing = ref(false);
const libraryOpen = ref(false);
let sequence = 0;

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

const gridEl = ref(null);
const width = ref(0);
let observer = null;

watch(gridEl, (element, previous) => {
  if (previous) {
    observer?.unobserve(previous);
  }

  if (element) {
    observer ??= new ResizeObserver(([entry]) => {
      width.value = entry.contentRect.width;
    });
    observer.observe(element);
    width.value = element.clientWidth;
  }
});

const oneColumn = computed(() => width.value > 0 && width.value < ONE_COLUMN_BELOW);
const cellWidth = computed(() => width.value / COLUMNS);

const boxes = computed(() => {
  const result = {};

  if (oneColumn.value) {
    let top = 0;

    for (const item of [...items.value].sort(byPosition)) {
      result[item.id] = { left: 0, top, width: width.value, height: item.h * CELL_HEIGHT };
      top += item.h * CELL_HEIGHT;
    }

    return result;
  }

  for (const item of items.value) {
    result[item.id] = {
      left: item.x * cellWidth.value,
      top: item.y * CELL_HEIGHT,
      width: item.w * cellWidth.value,
      height: item.h * CELL_HEIGHT,
    };
  }

  return result;
});

const interaction = ref(null);

const gridHeight = computed(() => {
  let bottom = 0;

  for (const box of Object.values(boxes.value)) {
    bottom = Math.max(bottom, box.top + box.height);
  }

  const pixel = interaction.value?.active ? interaction.value.pixel : null;

  return Math.max(bottom, pixel ? pixel.top + pixel.height : 0);
});

function boxStyle(box) {
  return {
    transform: `translate(${box.left}px, ${box.top}px)`,
    width: `${box.width}px`,
    height: `${box.height}px`,
  };
}

function styleFor(item) {
  const state = interaction.value;

  return boxStyle(state?.active && state.id === item.id ? state.pixel : boxes.value[item.id]);
}

const canEdit = computed(() => editing.value && !oneColumn.value);

function startInteraction(item, event, mode) {
  if (!canEdit.value || event.button !== 0) {
    return;
  }

  if (mode === 'move' && event.target.closest('button, a, [data-resize-handle]')) {
    return;
  }

  event.preventDefault();

  const origin = { ...boxes.value[item.id] };

  interaction.value = {
    mode,
    id: item.id,
    snapshot: items.value.map((node) => ({ ...node })),
    startX: event.clientX,
    startY: event.clientY,
    origin,
    pixel: origin,
    active: false,
  };

  window.addEventListener('pointermove', onPointerMove);
  window.addEventListener('pointerup', endInteraction);
  window.addEventListener('pointercancel', endInteraction);
}

function onPointerMove(event) {
  const state = interaction.value;

  if (!state) {
    return;
  }

  const dx = event.clientX - state.startX;
  const dy = event.clientY - state.startY;

  if (!state.active && Math.hypot(dx, dy) < DRAG_THRESHOLD) {
    return;
  }

  state.active = true;

  const nodes = state.snapshot.map((node) => ({ ...node }));
  const node = nodes.find((candidate) => candidate.id === state.id);
  const { origin } = state;

  if (state.mode === 'move') {
    const left = Math.min(Math.max(0, origin.left + dx), width.value - origin.width);
    const top = Math.max(0, origin.top + dy);

    state.pixel = { ...origin, left, top };
    moveNode(nodes, node, Math.round(left / cellWidth.value), Math.round(top / CELL_HEIGHT));
  } else {
    const boxWidth = Math.min(Math.max(cellWidth.value, origin.width + dx), width.value - origin.left);
    const boxHeight = Math.max(CELL_HEIGHT, origin.height + dy);

    state.pixel = { ...origin, width: boxWidth, height: boxHeight };
    resizeNode(nodes, node, Math.round(boxWidth / cellWidth.value), Math.round(boxHeight / CELL_HEIGHT));
  }

  items.value = nodes;
}

function positionsOf(nodes) {
  return JSON.stringify(nodes.map(({ id, x, y, w, h }) => [id, x, y, w, h]));
}

function endInteraction() {
  window.removeEventListener('pointermove', onPointerMove);
  window.removeEventListener('pointerup', endInteraction);
  window.removeEventListener('pointercancel', endInteraction);

  const state = interaction.value;

  interaction.value = null;

  if (state?.active && positionsOf(state.snapshot) !== positionsOf(items.value)) {
    scheduleSave();
  }
}

function add(type, config) {
  const bottom = items.value.reduce((max, node) => Math.max(max, node.y + node.h), 0);

  items.value = normalize([
    ...items.value.map((node) => ({ ...node })),
    { id: `new-${++sequence}`, type, x: 0, y: bottom, w: Number(config.w ?? 3), h: Number(config.h ?? 2) },
  ]);

  libraryOpen.value = false;
  save();
}

function remove(item) {
  items.value = normalize(items.value.filter((node) => node.id !== item.id).map((node) => ({ ...node })));
  scheduleSave();
}

let saveTimer = null;

function scheduleSave() {
  clearTimeout(saveTimer);
  saveTimer = setTimeout(save, 500);
}

function toggleEditing() {
  editing.value = !editing.value;

  if (!editing.value && saveTimer) {
    save();
  }
}

function save() {
  clearTimeout(saveTimer);
  saveTimer = null;

  router.post(
    route('admin.dashboard.widgets.save'),
    { layout: items.value.map(({ type, x, y, w, h }) => ({ type, x, y, w, h })) },
    { preserveScroll: true, preserveState: true },
  );
}

onUnmounted(() => {
  clearTimeout(saveTimer);
  observer?.disconnect();
  endInteraction();
});
</script>

<template>
  <Head :title="__('dashboard.dashboard')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex justify-end gap-2">
      <button v-if="items.length && !oneColumn" class="p-btn" @click="toggleEditing">
        <i class="fa-solid text-xs" :class="editing ? 'fa-check' : 'fa-pen'"></i>
        {{ editing ? __('dashboard.done') : __('general.edit') }}
      </button>
      <button class="p-btn-primary" @click="libraryOpen = true">
        <i class="fa-solid fa-plus text-xs"></i> {{ __('dashboard.add_widget') }}
      </button>
    </div>

    <div v-if="!items.length" class="p-card px-4 py-14 text-center">
      <i class="fa-solid fa-table-cells-large mb-3 block text-[32px] text-p-ink3"></i>
      <p class="mb-3 text-[12.5px] text-p-ink3">{{ __('dashboard.empty') }}</p>
      <button class="p-btn-primary" @click="libraryOpen = true">
        <i class="fa-solid fa-plus text-xs"></i> {{ __('dashboard.add_widget') }}
      </button>
    </div>

    <div
      v-else
      ref="gridEl"
      class="relative -mx-[5px]"
      :class="canEdit && 'select-none'"
      :style="{ height: `${gridHeight}px` }"
    >
      <div
        v-if="interaction?.active"
        class="pointer-events-none absolute left-0 top-0 transition-transform duration-150"
        :style="boxStyle(boxes[interaction.id])"
      >
        <div class="absolute rounded-xl border-2 border-dashed border-p-accent bg-p-soft/40" :style="{ inset: `${MARGIN / 2}px` }"></div>
      </div>

      <div
        v-for="item in items"
        :key="item.id"
        class="absolute left-0 top-0"
        :class="[
          interaction?.active && interaction.id === item.id
            ? 'z-20 opacity-90'
            : 'transition-[transform,width,height] duration-200',
          canEdit ? 'cursor-grab touch-none' : '',
          interaction?.active && interaction.id === item.id && interaction.mode === 'move' ? '!cursor-grabbing' : '',
        ]"
        :style="styleFor(item)"
        @pointerdown="startInteraction(item, $event, 'move')"
      >
        <div
          class="p-card absolute overflow-hidden"
          :class="interaction?.active && interaction.id === item.id && 'shadow-pop'"
          :style="{ inset: `${MARGIN / 2}px` }"
        >
          <button
            v-if="canEdit"
            class="p-icon-btn absolute right-1.5 top-1.5 z-10 !h-6 !w-6 !text-[10px] hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="remove(item)"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>

          <div class="h-full" :class="canEdit && 'pointer-events-none'">
            <CoreWidget v-if="configFor(item.type)" :config="configFor(item.type)" :data="widgetData" />

            <component
              :is="componentFor(item.type)"
              v-else-if="componentFor(item.type)"
              :widget="item"
              :widget-data="widgetData"
            />

            <div v-else class="flex h-full flex-col justify-center p-3 text-[12px] text-p-ink3">
              <div class="font-semibold text-p-ink2">{{ labelFor(item.type) }}</div>
              <div>{{ __('dashboard.widget_missing') }}</div>
            </div>
          </div>

          <div
            v-if="canEdit"
            data-resize-handle
            class="absolute bottom-0 right-0 z-10 grid h-5 w-5 cursor-nwse-resize touch-none place-items-center rounded-tl-lg bg-p-panel2/90 text-[9px] text-p-ink3 hover:text-p-accent"
            :title="__('dashboard.resize_hint')"
            @pointerdown.stop="startInteraction(item, $event, 'resize')"
          >
            <i class="fa-solid fa-up-right-and-down-left-from-center rotate-90"></i>
          </div>
        </div>
      </div>
    </div>

    <Modal v-model:open="libraryOpen" :title="__('dashboard.add_widget')" icon="fa-solid fa-table-cells-large" width="720px">
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
