<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { __ } from '../composables/useLang';
import { go } from '../composables/useNavigate';
import axios from 'axios';

const props = defineProps({
  open: { type: Boolean, default: false },
  sections: { type: Array, default: () => [] },
});
const emit = defineEmits(['update:open', 'navigate']);

const query = ref('');
const input = ref(null);
const results = ref({ posts: [], page: [], categories: [], personal_notes: [], users: [] });

watch(() => props.open, async (v) => {
  if (v) { await nextTick(); input.value?.focus(); }
  else { query.value = ''; }
});

/* base.blade.php içindeki navbar araması ile aynı endpoint: route('general.search') */
let timer = null;
watch(query, (q) => {
  clearTimeout(timer);
  if (!q) { results.value = { posts: [], page: [], categories: [], personal_notes: [], users: [] }; return; }
  timer = setTimeout(async () => {
    const { data } = await axios.post(route('general.search'), { search: q });
    results.value = data;
  }, 180);
});

const navItems = computed(() =>
  props.sections.flatMap((s) => s.items.map((i) => ({ ...i, group: s.label })))
    .filter((i) => !query.value || i.label.toLocaleLowerCase('tr').includes(query.value.toLocaleLowerCase('tr'))));

const groups = computed(() => {
  const g = [{ label: __('general.screens'), items: navItems.value.slice(0, 6).map((i) => ({ label: i.label, icon: i.icon, hint: i.group, go: () => emit('navigate', i) })) }];
  const add = (label, rows) => { if (rows.length) g.push({ label, items: rows }); };
  add(__('post.blogs'), results.value.posts.map((p) => ({
    label: `${p.title} (${p.language})`, icon: 'fa-regular fa-file-lines', hint: __('post.blog'),
    go: () => go(route('admin.post.edit', { type: 'blogs', post: p.id }), 'admin.post.edit'),
  })));
  add(__('post.pages'), results.value.page.map((p) => ({
    label: `${p.title} (${p.language})`, icon: 'fa-regular fa-file', hint: __('post.page'),
    go: () => go(route('admin.post.edit', { type: 'pages', post: p.id }), 'admin.post.edit'),
  })));
  add(__('categories.categories'), results.value.categories.map((c) => ({
    label: `${c.name} (${c.language})`, icon: 'fa-solid fa-list', hint: __('categories.category'),
    go: () => go(route('admin.categories', { tab: c.language }), 'admin.categories'),
  })));
  add(__('notes.notes'), results.value.personal_notes.map((n) => ({
    label: n.title, icon: 'fa-solid fa-file-shield', hint: __('notes.note'),
    go: () => go(route('admin.notes.show', { note: n.id }), 'admin.notes.show'),
  })));
  add(__('user.users'), results.value.users.map((u) => ({
    label: `${u.name} ${u.surname} — ${u.nickname}`, icon: 'fa-solid fa-user', hint: __('user.user'),
    go: () => go(route('admin.user.edit', { user_id: u.id }), 'admin.user.edit'),
  })));
  return g;
});

function close() { emit('update:open', false); }
function run(item) { item.go(); close(); }
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[60] flex items-start justify-center bg-black/50 pt-[12vh]" @click="close">
    <div class="w-[min(620px,92vw)] animate-popIn overflow-hidden rounded-2xl border border-p-line bg-p-panel shadow-pop" @click.stop>
      <div class="flex h-[52px] items-center gap-3 border-b border-p-line2 px-4">
        <i class="fa-solid fa-magnifying-glass text-[13px] text-p-ink3"></i>
        <input ref="input" v-model="query" class="flex-1 border-0 bg-transparent text-sm text-p-ink outline-none"
               :placeholder="__('general.search_placeholder')">
        <kbd class="rounded-[5px] border border-p-line px-1.5 text-[10.5px] text-p-ink3">ESC</kbd>
      </div>
      <div class="max-h-[52vh] overflow-auto p-2">
        <div v-for="g in groups" :key="g.label">
          <div class="px-2.5 pb-1 pt-2 text-[10.5px] font-bold uppercase tracking-[.09em] text-p-ink3">{{ g.label }}</div>
          <button v-for="i in g.items" :key="i.label"
                  class="flex w-full cursor-pointer items-center gap-3 rounded-[9px] border-0 bg-transparent px-2.5 py-2.5 text-left text-[13px] text-p-ink hover:bg-p-soft"
                  @click="run(i)">
            <i :class="i.icon" class="w-4 text-xs text-p-ink3"></i>
            <span class="flex-1 truncate">{{ i.label }}</span>
            <span class="text-[11px] text-p-ink3">{{ i.hint }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
