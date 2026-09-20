<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import MenuTreeBuilder from '../../components/MenuTreeBuilder.vue';
import FormField from '../../components/FormField.vue';

/*
 * panel/menu/show.blade.php karşılığı — jquery.nestable'ın yerini alır.
 * Sınırsız derinlikte alt menü: sıralama sürükle-bırak, seviye girinti/çıkıntı
 * düğmeleriyle değişir (bkz. components/MenuTreeBuilder.vue).
 *
 * SUNUCU SÖZLEŞMESİ DEĞİŞMEDİ: `admin.menu-item.save` hâlâ `menu_id` + `menu`
 * alanlarını bekler ve `menu` bir JSON STRING'dir. Ağaçtaki her düğüm altı
 * anahtarı da taşımak zorunda (`title`, `url`, `language`, `icon`,
 * `nav_target`, `menu_type`): updateMenu() ilk dördünü KORUMASIZ okuyor ve
 * eksik biri transaction'ı rollback'e düşürüp jenerik hata üretir.
 *
 * Satırlar her kayıtta silinip yeniden yaratıldığı için id'ler değişir;
 * kaydetmeden sonra ağaç sunucudan tazelenir.
 */
/*
 * `menu` DEĞİL, `menuRecord`.
 *
 * HandlePanelInertiaRequests sidebar bölümlerini `menu` adıyla paylaşıyor;
 * Inertia'da sayfa prop'u aynı adlı paylaşılan prop'u EZER. Bu ekran `menu`
 * gönderdiği sürece PanelLayout'un `sections` computed'ı dizi yerine bu nesneyi
 * alıyor, `sections.find(...)` TypeError fırlatıyor ve Vue tüm layout alt
 * ağacını düşürüyordu — sol menü komple kayboluyordu.
 */
const props = defineProps({
  menuRecord: { type: Object, required: true },
  tree: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  pages: { type: Array, default: () => [] },
  posts: { type: Array, default: () => [] },
});

usePageHeader(props.menuRecord.title, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('menu.menu'), route: 'admin.menu.index' },
  { label: props.menuRecord.title },
]);

function clone(value) {
  return JSON.parse(JSON.stringify(value ?? []));
}

const tree = ref(clone(props.tree));

/*
 * Kayıtta satırlar SİLİNİP yeniden yaratılıyor, yani id'ler değişiyor. Sunucu
 * `back()` döndürdüğü için aynı bileşen taze `tree` prop'uyla yeniden gelir;
 * yerel ağaç her zaman sunucudan tazelenir, eski id'lere güvenilmez.
 */
watch(
  () => props.tree,
  (value) => {
    tree.value = clone(value);
  },
);

const showJson = ref(false);

const custom = ref({ title: '', url: '' });

const form = useForm({
  menu_id: props.menuRecord.id,
  menu: '',
});

const json = computed(() => JSON.stringify(tree.value, null, 2));

// Sunucu id'leriyle çakışmasın: yeni öğeler kaydedilene kadar negatif id taşır.
let nextTempId = -1;

/** Sunucunun beklediği ALTI anahtarı da daima doldur. */
function node(title, url) {
  return {
    id: nextTempId--,
    title,
    url,
    language: props.menuRecord.language,
    nav_target: '_self',
    icon: '',
    menu_id: props.menuRecord.id,
    menu_type: 'standard',
    children: [],
  };
}

function addCustom() {
  if (!custom.value.title) {
    return;
  }

  tree.value.push(node(custom.value.title, custom.value.url));
  custom.value = { title: '', url: '' };
}

function addFrom(item, prefix) {
  const slug = item.slug || '';
  tree.value.push(node(item.title || item.name, `/${props.menuRecord.language}/${prefix}${slug}`));
}

function save() {
  form.menu = JSON.stringify(tree.value);
  form.post(route('admin.menu-item.save'), {
    preserveScroll: true,
    // Satirlar yeniden yaratildigi icin id'ler degisti: agaci sunucudan al.
    onSuccess: () => form.reset('menu'),
  });
}
</script>

<template>
  <Head :title="menuRecord.title" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[320px_minmax(0,1fr)]">
    <!-- Kaynaklar -->
    <div class="flex flex-col gap-3.5">
      <div class="p-card p-4">
        <div class="mb-2.5 font-display text-[13.5px] font-bold">{{ __('menu.custom_link') }}</div>
        <div class="flex flex-col gap-2.5">
          <FormField v-model="custom.title" :label="__('menu.title')" />
          <FormField v-model="custom.url" label="URL" placeholder="/ornek-yol" />
          <button class="p-btn justify-center" @click="addCustom">
            <i class="fa-solid fa-plus text-[11px]"></i> {{ __('general.add') }}
          </button>
        </div>
      </div>

      <div v-for="group in [
          { label: __('post.pages'), items: pages, prefix: '' },
          { label: __('post.blogs'), items: posts, prefix: '' },
          { label: __('categories.categories'), items: categories, prefix: '' },
        ]"
        :key="group.label"
        class="p-card p-4"
      >
        <div class="mb-2 font-display text-[13.5px] font-bold">{{ group.label }}</div>
        <div class="max-h-56 overflow-auto">
          <button
            v-for="item in group.items"
            :key="item.id"
            class="flex w-full items-center gap-2 rounded-[9px] px-2 py-1.5 text-left text-[12px] hover:bg-p-panel2"
            @click="addFrom(item, group.prefix)"
          >
            <i class="fa-solid fa-plus text-[9px] text-p-ink3"></i>
            <span class="truncate">{{ item.title || item.name }}</span>
          </button>
          <div v-if="!group.items.length" class="py-3 text-center text-[11.5px] text-p-ink3">
            {{ __('general.no_records') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Ağaç -->
    <div class="flex flex-col gap-3.5">
      <div class="p-card p-4">
        <div class="mb-3 flex items-center gap-2">
          <div class="flex-1 font-display text-[13.5px] font-bold">{{ __('menu.menu_items') }}</div>
          <button class="p-btn" @click="showJson = !showJson">
            <i class="fa-solid fa-code text-[11px]"></i> JSON
          </button>
          <button class="p-btn-primary" :disabled="form.processing" @click="save">
            <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
          </button>
        </div>

        <p class="mb-2.5 text-[11.5px] leading-relaxed text-p-ink3">{{ __('menu.builder_hint') }}</p>

        <MenuTreeBuilder v-model="tree" />

        <div v-if="!tree.length" class="py-10 text-center text-[12.5px] text-p-ink3">
          {{ __('general.no_records') }}
        </div>
      </div>

      <div v-if="showJson" class="p-card p-4">
        <div class="p-label">JSON</div>
        <pre
          class="max-h-72 overflow-auto whitespace-pre-wrap rounded-lg bg-p-panel2 p-3 font-mono text-[11px] text-p-ink2"
          >{{ json }}</pre
        >
      </div>
    </div>
  </div>
</template>
