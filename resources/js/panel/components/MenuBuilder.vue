<script setup>
/**
 * Menü öğeleri — jquery.nestable yerine bağımlılıksız sürükle-bırak.
 * Çıktı, mevcut MenuItemsController::save() beklentisiyle aynı: JSON string
 * ([{ id, title, url, nav_target, icon, children: [...] }]) `menu` alanında POST edilir.
 */
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import FormField from './FormField.vue';

const props = defineProps({
  menu: { type: Object, required: true },        // { id, title, menu_position, language }
  items: { type: Array, default: () => [] },     // düz liste: { id, title, url, nav_target, icon, depth }
  sources: { type: Object, default: () => ({ pages: [], posts: [], categories: [] }) },
});

const tree = ref(props.items.map((i) => ({ ...i, depth: i.depth ?? 0 })));
const tab = ref('custom');
const openId = ref(null);
const dragId = ref(null);
const draft = ref({ title: '', url: '', nav_target: '_self', icon: '' });

const tabs = [
  { id: 'custom', label: 'Özel Bağlantı' },
  { id: 'pages', label: 'Sayfalar' },
  { id: 'posts', label: 'Bloglar' },
  { id: 'categories', label: 'Kategoriler' },
];

const source = computed(() => props.sources[tab.value] || []);

const nested = computed(() => {
  const out = [];
  tree.value.forEach((m) => {
    const node = { id: m.id, title: m.title, url: m.url, nav_target: m.nav_target, icon: m.icon };
    if (m.depth > 0 && out.length) {
      const parent = out[out.length - 1];
      (parent.children ||= []).push(node);
    } else out.push(node);
  });
  return out;
});

function add(title, url, nav_target = '_self', icon = '') {
  if (!title && !url) return;
  tree.value.push({ id: Date.now(), title, url, nav_target, icon, depth: 0 });
  draft.value = { title: '', url: '', nav_target: '_self', icon: '' };
}
function remove(id) { tree.value = tree.value.filter((m) => m.id !== id); }
function indent(i) { if (i > 0) tree.value[i].depth = 1; }
function outdent(i) { tree.value[i].depth = 0; }

function onDrop(target) {
  const from = tree.value.findIndex((m) => m.id === dragId.value);
  const to = tree.value.findIndex((m) => m.id === target.id);
  if (from < 0 || to < 0 || from === to) return (dragId.value = null);
  const [item] = tree.value.splice(from, 1);
  tree.value.splice(to, 0, item);
  dragId.value = null;
}

function save() {
  router.post(route('admin.menu-item.save'), {
    menu_id: props.menu.id,
    menu: JSON.stringify(nested.value),
  }, { preserveScroll: true });
}
</script>

<template>
  <div class="flex flex-wrap items-start gap-4 p-[22px]">
    <!-- Kaynak paneli -->
    <div class="p-card min-w-[290px] flex-1 basis-[340px] overflow-hidden">
      <div class="flex flex-wrap gap-0.5 border-b border-p-line2 p-2.5">
        <button v-for="t in tabs" :key="t.id" class="p-tab" :class="tab === t.id && 'p-tab-active'" @click="tab = t.id">
          {{ t.label }}
        </button>
      </div>

      <div v-if="tab === 'custom'" class="flex flex-col gap-3 p-4">
        <FormField v-model="draft.title" label="Başlık" placeholder="Başlık" />
        <FormField v-model="draft.url" label="Url" placeholder="Url" />
        <div class="grid grid-cols-2 gap-3">
          <FormField v-model="draft.nav_target" label="Hedef" type="select"
                     :options="[{ value: '_self', label: 'Aynı Sekme' }, { value: '_blank', label: 'Yeni Sekme' }]" />
          <FormField v-model="draft.icon" label="Simge" placeholder="fa-solid fa-house" />
        </div>
        <button class="p-btn-primary h-[34px] self-start px-3.5"
                @click="add(draft.title, draft.url, draft.nav_target, draft.icon)">
          <i class="fa-solid fa-plus text-[11px]"></i> Menü Öğesi Ekle
        </button>
      </div>

      <div v-else class="flex max-h-[420px] flex-col overflow-auto">
        <button v-for="o in source" :key="o.url"
                class="flex cursor-pointer items-center gap-2.5 border-0 border-b border-p-line2 bg-transparent px-4 py-2.5 text-left text-[12.8px] text-p-ink hover:bg-p-panel2"
                @click="add(o.title, o.url)">
          <span class="min-w-0 flex-1">
            {{ o.title }}
            <span class="mt-0.5 block text-[11px] text-p-ink3">{{ o.url }}</span>
          </span>
          <i class="fa-solid fa-plus text-[11px] text-p-accent"></i>
        </button>
      </div>
    </div>

    <!-- Ağaç -->
    <div class="flex min-w-[300px] flex-1 basis-[380px] flex-col gap-3.5">
      <div class="p-card overflow-hidden">
        <div class="flex items-center gap-2 border-b border-p-line2 px-4 py-3.5">
          <div class="flex-1 font-display text-[13.5px] font-bold">
            {{ menu.title }} · {{ menu.menu_position === 'header' ? 'Üst Menü' : 'Alt Menü' }} · {{ menu.language }}
          </div>
          <span class="text-[11px] text-p-ink3">Sürükleyerek sırala</span>
        </div>

        <div class="flex flex-col gap-1.5 p-2.5">
          <div v-for="(m, i) in tree" :key="m.id" :style="{ marginLeft: `${m.depth * 28}px` }">
            <div draggable="true"
                 class="flex items-center gap-2 rounded-xl border bg-p-panel2 px-3 py-2.5"
                 :class="dragId === m.id ? 'border-p-accent' : 'border-p-line'"
                 @dragstart="dragId = m.id" @dragover.prevent @drop.prevent="onDrop(m)" @dragend="dragId = null">
              <span class="cursor-grab px-1 text-xs text-p-ink3"><i class="fa-solid fa-grip-vertical"></i></span>
              <i v-if="m.icon" :class="m.icon" class="w-4 text-xs text-p-ink3"></i>
              <div class="min-w-0 flex-1">
                <div class="text-[12.8px] font-semibold">{{ m.title }}</div>
                <div class="mt-0.5 truncate text-[11px] text-p-ink3">
                  {{ m.url }} · {{ m.nav_target === '_blank' ? 'Yeni Sekme' : 'Aynı Sekme' }}
                </div>
              </div>
              <button class="p-icon-btn h-6.5 w-6.5" title="Girinti azalt" @click="outdent(i)"><i class="fa-solid fa-outdent"></i></button>
              <button class="p-icon-btn h-6.5 w-6.5" title="Girinti artır" @click="indent(i)"><i class="fa-solid fa-indent"></i></button>
              <button class="p-icon-btn h-6.5 w-6.5" :class="openId === m.id && 'border-transparent bg-p-soft text-p-accent'"
                      title="Düzenle" @click="openId = openId === m.id ? null : m.id">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <button class="p-icon-btn h-6.5 w-6.5 !text-p-danger" title="Sil" @click="remove(m.id)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>

            <div v-if="openId === m.id"
                 class="ml-[34px] mt-1.5 grid grid-cols-2 gap-3 rounded-xl border border-p-line bg-p-panel2 p-3.5">
              <FormField v-model="m.title" label="Başlık" full />
              <FormField v-model="m.url" label="Url" full />
              <FormField v-model="m.nav_target" label="Hedef" type="select"
                         :options="[{ value: '_self', label: 'Aynı Sekme' }, { value: '_blank', label: 'Yeni Sekme' }]" />
              <FormField v-model="m.icon" label="Simge" />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 border-t border-p-line2 bg-p-panel2 px-4 py-3">
          <span class="text-[11.5px] text-p-ink3">{{ tree.length }} menü öğesi</span>
          <div class="flex-1"></div>
          <button class="p-btn-primary h-[34px] px-4" @click="save">Kaydet</button>
        </div>
      </div>

      <div class="p-card overflow-hidden">
        <div class="flex items-center gap-2 border-b border-p-line2 px-4 py-3">
          <i class="fa-solid fa-code text-xs text-p-ink3"></i>
          <div class="flex-1 text-[12.5px] font-semibold">Kaydedilecek JSON</div>
          <span class="text-[11px] text-p-ink3">menu alanı</span>
        </div>
        <pre class="max-h-60 overflow-auto whitespace-pre-wrap px-4 py-3.5 font-mono text-[11.5px] leading-relaxed text-p-ink2">{{ JSON.stringify(nested, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>
