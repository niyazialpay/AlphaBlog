<script setup>
import { __ } from '../composables/useLang';

/**
 * Özyinelemeli kategori ağacı.
 *
 * Blade tarafındaki `panel.post.category.partials.category_row` partial'inin
 * karşılığı; ağaç sunucuda çözülüp JSON olarak geliyor.
 */
defineProps({
  nodes: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
});

defineEmits(['edit', 'delete']);
</script>

<template>
  <ul class="flex flex-col gap-px">
    <li v-for="node in nodes" :key="node.id">
      <div
        class="group flex items-center gap-2 rounded-[9px] px-2.5 py-2 text-[12.5px] hover:bg-p-panel2"
        :style="{ paddingLeft: `${10 + level * 18}px` }"
      >
        <i
          class="fa-solid text-[10px] text-p-ink3"
          :class="node.children.length ? 'fa-folder-open' : 'fa-hashtag'"
        ></i>
        <span class="flex-1 truncate">{{ node.name }}</span>
        <span class="hidden text-[11px] text-p-ink3 sm:inline">{{ node.slug }}</span>

        <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
          <button class="p-icon-btn" :title="__('general.edit')" @click="$emit('edit', node)">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="$emit('delete', node)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>

      <CategoryTree
        v-if="node.children.length"
        :nodes="node.children"
        :level="level + 1"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </li>
  </ul>
</template>
