<script setup>
import { ref } from 'vue';
import { __ } from '../composables/useLang';

/**
 * jquery.nestable'ın yerini alan menü ağacı kurucusu.
 *
 * HTML5 drag & drop ile sıralama, butonlarla girinti (alt menü). Ağaç
 * doğrudan mutasyona uğrar; üst bileşen JSON'a çevirip kaydeder.
 *
 * Sunucu sözleşmesi gereği her düğüm `title`, `url`, `language`, `icon`,
 * `nav_target`, `menu_type` ve `children` taşır — bkz. Menu/Items.vue.
 */
const props = defineProps({
  modelValue: { type: Array, required: true },
  language: { type: String, default: null },
  level: { type: Number, default: 0 },
});

const emit = defineEmits(['update:modelValue']);

const dragIndex = ref(null);
const openIndex = ref(null);

const TARGETS = ['_self', '_blank'];

function onDragStart(index) {
  dragIndex.value = index;
}

function onDrop(index) {
  if (dragIndex.value === null || dragIndex.value === index) {
    return;
  }

  const next = [...props.modelValue];
  const [moved] = next.splice(dragIndex.value, 1);
  next.splice(index, 0, moved);
  dragIndex.value = null;
  emit('update:modelValue', next);
}

/** Bir üstteki kardeşin altına taşı (girinti = alt menü). */
function indent(index) {
  if (index === 0) {
    return;
  }

  const next = [...props.modelValue];
  const [moved] = next.splice(index, 1);
  next[index - 1] = { ...next[index - 1], children: [...next[index - 1].children, moved] };
  emit('update:modelValue', next);
}

function remove(index) {
  const next = [...props.modelValue];
  next.splice(index, 1);
  emit('update:modelValue', next);
}

function update(index, patch) {
  const next = [...props.modelValue];
  next[index] = { ...next[index], ...patch };
  emit('update:modelValue', next);
}

function updateChildren(index, children) {
  update(index, { children });
}

/** Alt menüdeki ögeyi bir üst seviyeye çıkar. */
function outdent(index) {
  const next = [...props.modelValue];
  const node = next[index];

  if (!node.children.length) {
    return;
  }

  const [first, ...rest] = node.children;
  next[index] = { ...node, children: rest };
  next.splice(index + 1, 0, first);
  emit('update:modelValue', next);
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <div v-for="(item, index) in modelValue" :key="item.id ?? index">
      <div
        class="rounded-xl border border-p-line bg-p-panel2"
        :style="{ marginLeft: `${level * 20}px` }"
        draggable="true"
        @dragstart="onDragStart(index)"
        @dragover.prevent
        @drop.prevent="onDrop(index)"
      >
        <div class="flex items-center gap-2 px-2.5 py-2">
          <i class="fa-solid fa-grip-vertical cursor-grab text-[11px] text-p-ink3"></i>
          <span class="flex-1 truncate text-[12.5px] font-semibold">{{ item.title }}</span>
          <span class="hidden truncate text-[11px] text-p-ink3 sm:inline">{{ item.url }}</span>

          <button
            class="p-icon-btn"
            :title="__('menu.indent')"
            :disabled="index === 0"
            @click="indent(index)"
          >
            <i class="fa-solid fa-indent"></i>
          </button>
          <button
            class="p-icon-btn"
            :title="__('menu.outdent')"
            :disabled="!item.children.length"
            @click="outdent(index)"
          >
            <i class="fa-solid fa-outdent"></i>
          </button>
          <button
            class="p-icon-btn"
            :title="__('general.edit')"
            @click="openIndex = openIndex === index ? null : index"
          >
            <i class="fa-solid fa-pen"></i>
          </button>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="remove(index)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>

        <div v-if="openIndex === index" class="grid gap-2 border-t border-p-line2 p-2.5 sm:grid-cols-2">
          <div>
            <label class="p-label">{{ __('menu.title') }}</label>
            <input
              class="p-input"
              :value="item.title"
              @input="update(index, { title: $event.target.value })"
            />
          </div>
          <div>
            <label class="p-label">URL</label>
            <input
              class="p-input"
              :value="item.url"
              @input="update(index, { url: $event.target.value })"
            />
          </div>
          <div>
            <label class="p-label">{{ __('menu.icon') }}</label>
            <input
              class="p-input"
              :value="item.icon"
              placeholder="fa-solid fa-link"
              @input="update(index, { icon: $event.target.value })"
            />
          </div>
          <div>
            <label class="p-label">{{ __('menu.target') }}</label>
            <select
              class="p-input"
              :value="item.nav_target"
              @change="update(index, { nav_target: $event.target.value })"
            >
              <option v-for="target in TARGETS" :key="target" :value="target">{{ target }}</option>
            </select>
          </div>
        </div>
      </div>

      <MenuTreeBuilder
        v-if="item.children.length"
        :model-value="item.children"
        :language="language"
        :level="level + 1"
        class="mt-1"
        @update:model-value="updateChildren(index, $event)"
      />
    </div>
  </div>
</template>
