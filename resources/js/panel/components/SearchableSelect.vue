<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { __ } from '../composables/useLang';

/**
 * Aranabilir TEK secim — eski temadaki select2'nin karsiligi.
 *
 * `MultiSelect.vue` coklu secim icin ayni isi yapiyor; bu onun tekli kardesi.
 * Ayri bir bilesen, cunku tekli secimde davranis farkli: secim yapilinca liste
 * kapanir, secili deger dugmede metin olarak durur ve "temizle" secenegi olur.
 *
 * Neden gerekli: yazar alaninda yuzlerce kullanici olabiliyor ve duz bir
 * `<select>` icinde isim aramak mumkun degil — kullanici listeyi kaydirarak
 * bulmak zorunda kaliyordu.
 *
 * IKI MOD:
 *  - Yerel (varsayilan): `options` tam listedir, filtreleme istemcide.
 *    Turkce kucultme icin `toLocaleLowerCase('tr')`, aksi halde "I"/"İ"
 *    yanlis eslesir.
 *  - Uzak (`remote` verilirse): `remote(query)` bir Promise ile secenek dizisi
 *    doner; filtreleme SUNUCUDA. `options` o zaman yalnizca baslangic tohumudur
 *    (ör. secili kayit), acilista etiketi gosterebilmek icin.
 *
 * Secenek sekli: { value, label, description? } — `description` etiketin
 * altinda soluk satir olarak gosterilir (ör. ad soyad / e-posta).
 */
const props = defineProps({
  modelValue: { type: [String, Number, null], default: null },
  options: { type: Array, default: () => [] }, // [{ value, label }]
  placeholder: { type: String, default: '' },
  /** Bos secim sunulsun mu (ör. "Kategori seciniz"). */
  clearable: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  /** `(query: string) => Promise<Array<{ value, label, description? }>>` */
  remote: { type: Function, default: null },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const query = ref('');
const root = ref(null);
const search = ref(null);

const remoteOptions = ref([]);
const loading = ref(false);
let debounce = null;
let sequence = 0;

/*
 * Uzak modda secili kayit son arama sonucunda olmayabilir (kullanici baska
 * bir sey aradi). Gorulen her secenek hatirlanir ki dugmedeki etiket
 * kaybolmasin.
 */
const known = ref(new Map());

function remember(options) {
  for (const option of options) {
    known.value.set(String(option.value), option);
  }
}

watch(() => props.options, (options) => remember(options), { immediate: true });

const selected = computed(() => {
  const key = String(props.modelValue);

  return props.options.find((option) => String(option.value) === key)
    || known.value.get(key)
    || null;
});

async function runRemote() {
  const current = ++sequence;
  loading.value = true;

  try {
    const options = (await props.remote(query.value.trim())) || [];

    // Yavas donen eski bir istek yeni sonucun ustune yazmasin.
    if (current === sequence) {
      remoteOptions.value = options;
      remember(options);
    }
  } finally {
    if (current === sequence) {
      loading.value = false;
    }
  }
}

watch(query, () => {
  if (! props.remote) {
    return;
  }

  clearTimeout(debounce);
  debounce = setTimeout(runRemote, 250);
});

const filtered = computed(() => {
  if (props.remote) {
    return remoteOptions.value;
  }

  const needle = query.value.trim().toLocaleLowerCase('tr');

  if (! needle) {
    return props.options;
  }

  return props.options.filter((option) =>
    String(option.label).toLocaleLowerCase('tr').includes(needle),
  );
});

function pick(option) {
  emit('update:modelValue', option ? option.value : null);
  open.value = false;
  query.value = '';
}

function toggle() {
  if (props.disabled) {
    return;
  }

  open.value = ! open.value;
}

/* Acilinca arama kutusuna odaklan: select2'de de oyleydi, fare gerekmiyor. */
watch(open, async (value) => {
  if (value) {
    // Uzak modda ilk acilista bos sorguyla ilk sayfa getirilir.
    if (props.remote && ! remoteOptions.value.length) {
      runRemote();
    }

    await nextTick();
    search.value?.focus();
  }
});

function outside(event) {
  if (root.value && ! root.value.contains(event.target)) {
    open.value = false;
  }
}

function onKeydown(event) {
  if (event.key === 'Escape' && open.value) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', outside);
  document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
  clearTimeout(debounce);
  document.removeEventListener('click', outside);
  document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
  <div ref="root" class="relative">
    <button
      type="button"
      class="flex h-9 w-full items-center gap-2 rounded-[9px] border border-p-line bg-p-panel2 px-2.5 text-left text-[12.5px] transition-colors disabled:opacity-50"
      :class="open ? 'border-p-accent' : ''"
      :disabled="disabled"
      @click="toggle"
    >
      <span :class="selected ? 'truncate text-p-ink' : 'truncate text-p-ink3'">
        {{ selected ? selected.label : placeholder }}
      </span>
      <i class="fa-solid fa-chevron-down ml-auto shrink-0 text-[10px] text-p-ink3"></i>
    </button>

    <div
      v-if="open"
      class="absolute z-30 mt-1 max-h-64 w-full animate-popIn overflow-auto rounded-xl border border-p-line bg-p-panel shadow-pop"
    >
      <div class="sticky top-0 border-b border-p-line2 bg-p-panel p-2">
        <input
          ref="search"
          v-model="query"
          :placeholder="__('general.search')"
          class="p-input h-7 text-[12px]"
          @keydown.enter.prevent="filtered.length === 1 && pick(filtered[0])"
        />
      </div>

      <button
        v-if="clearable"
        type="button"
        class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12.5px] text-p-ink3 hover:bg-p-panel2"
        @click="pick(null)"
      >
        <i class="fa-solid fa-xmark w-3 text-[10px]"></i>
        {{ placeholder || __('general.clear') }}
      </button>

      <button
        v-for="option in filtered"
        :key="option.value"
        type="button"
        class="flex w-full items-center gap-2 px-3 py-2 text-left text-[12.5px] hover:bg-p-panel2"
        :class="String(option.value) === String(modelValue) && 'bg-p-soft text-p-accent'"
        @click="pick(option)"
      >
        <i
          class="fa-solid w-3 text-[10px]"
          :class="String(option.value) === String(modelValue) ? 'fa-check' : 'fa-minus opacity-0'"
        ></i>
        <span class="min-w-0">
          <span class="block truncate">{{ option.label }}</span>
          <span v-if="option.description" class="block truncate text-[11px] text-p-ink3">
            {{ option.description }}
          </span>
        </span>
      </button>

      <div v-if="loading" class="px-3 py-4 text-center text-[12px] text-p-ink3">
        <i class="fa-solid fa-spinner fa-spin"></i>
      </div>

      <div v-else-if="! filtered.length" class="px-3 py-4 text-center text-[12px] text-p-ink3">
        {{ __('general.no_records') }}
      </div>
    </div>
  </div>
</template>
