<script setup>
import { computed, ref } from 'vue';
import { __ } from '../composables/useLang';

/**
 * jquery.nestable'ın yerini alan menü ağacı kurucusu — SINIRSIZ DERİNLİK.
 *
 * MODEL: ağaç, render ve taşıma işlemleri için DÜZ bir listeye (DFS sırası +
 * `depth`) açılır, işlem sonunda tekrar ağaca sarılır. Özyinelemeli bileşen
 * yerine bunun seçilmesinin nedeni, `outdent`'in ebeveyn bağlamına ihtiyaç
 * duyması ve sürükle-bırakın TEK bir düz liste üzerinde seviyeler arası
 * çalışabilmesidir. Eski sürüm özyinelemeliydi: alt seviyedeki bir öğe kendi
 * ebeveynini göremediği için `outdent` yanlışlıkla ögenin İLK ÇOCUĞUNU dışarı
 * çıkarıyordu ve hiçbir alt menü oluşturulamıyordu.
 *
 * Sunucu sözleşmesi gereği her düğüm `title`, `url`, `language`, `icon`,
 * `nav_target`, `menu_type` ve `children` taşır — düzleştirme yalnızca
 * `children`'ı ayırır, geri kalan TÜM anahtarlar aynen korunur; `nest()` her
 * düğüme `children` dizisini geri koyar. Bkz. Menu/Items.vue.
 */
const props = defineProps({
  modelValue: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue']);

const openId = ref(null);
const dragIndex = ref(null);
const dropIndex = ref(null);

/*
 * YATAY SURUKLEME ile girintileme — jquery.nestable'in asil davranisi.
 *
 * Suruklerken imlecin YATAY yer degistirmesi hedef derinligi belirler: bir
 * `INDENT_STEP` saga = bir seviye alt menu, sola = bir seviye yukari. Dikey
 * konum sirayi, yatay konum derinligi verir; ikisi tek birakmada uygulanir.
 * Girinti/cikinti dugmeleri ayni islemin klavye/dokunmatik karsiligi olarak
 * duruyor.
 */
const INDENT_STEP = 22;
const dragStartX = ref(0);
const dragDepth = ref(0);
const dropDepth = ref(0);

const TARGETS = [
  { value: '_self', label: () => __('menu.same_tab') },
  { value: '_blank', label: () => __('menu.new_tab') },
];

/**
 * Ağaç → düz satır listesi (DFS). Bir düğümün alt ağacı, listede daima o
 * düğümden hemen sonra ve daha büyük `depth` ile BİTİŞİK durur; tüm taşıma
 * işlemleri bu değişmeze dayanır.
 */
function flatten(nodes, depth = 0, acc = []) {
  for (const node of nodes ?? []) {
    const { children, ...rest } = node;

    acc.push({ ...rest, depth });
    flatten(children ?? [], depth + 1, acc);
  }

  return acc;
}

/** Düz satır listesi → ağaç. Derinlik sıçramaları en yakın üst düğüme bağlanır. */
function nest(list) {
  const root = [];
  const stack = [{ depth: -1, children: root }];

  for (const { depth, ...rest } of list) {
    const node = { ...rest, children: [] };

    while (stack.length > 1 && stack[stack.length - 1].depth >= depth) {
      stack.pop();
    }

    stack[stack.length - 1].children.push(node);
    stack.push({ depth, children: node.children });
  }

  return root;
}

const rows = computed(() => flatten(props.modelValue));

function commit(list) {
  emit('update:modelValue', nest(list));
}

/** i. satırın alt ağacındaki satır sayısı (kendisi hariç). */
function subtreeSize(list, i) {
  let size = 0;

  while (i + size + 1 < list.length && list[i + size + 1].depth > list[i].depth) {
    size++;
  }

  return size;
}

function shift(block, delta) {
  return block.map((row) => ({ ...row, depth: row.depth + delta }));
}

/** Bir öğe ancak kendisinden önceki satırın çocuğu olabilecek kadar girintilenebilir. */
function canIndent(index) {
  return index > 0 && rows.value[index].depth <= rows.value[index - 1].depth;
}

function canOutdent(index) {
  return rows.value[index].depth > 0;
}

/** Bir üstteki uygun kardeşin altına taşı (alt menü). Derinlik sınırı yok. */
function indent(index) {
  if (!canIndent(index)) {
    return;
  }

  const list = [...rows.value];
  const size = subtreeSize(list, index) + 1;

  list.splice(index, size, ...shift(list.slice(index, index + size), 1));
  commit(list);
}

/**
 * Bir seviye yukarı çıkar: öğe, EBEVEYNİNİN bir sonraki kardeşi olur.
 *
 * Kendisinden sonra gelen kardeşler ebeveynde KALIR; bu yüzden blok, ebeveynin
 * alt ağacının sonuna taşınır (yalnızca `depth`'i azaltmak, sonraki kardeşleri
 * sessizce ögenin çocuğu yapardı).
 */
function outdent(index) {
  if (!canOutdent(index)) {
    return;
  }

  const list = [...rows.value];
  const size = subtreeSize(list, index) + 1;
  const parentDepth = list[index].depth - 1;

  let end = index + size;

  while (end < list.length && list[end].depth > parentDepth) {
    end++;
  }

  const block = shift(list.splice(index, size), -1);

  list.splice(end - size, 0, ...block);
  commit(list);
}

function onDragStart(index, event) {
  dragIndex.value = index;
  dragStartX.value = event.clientX;
  dragDepth.value = rows.value[index].depth;
  dropIndex.value = index;
  dropDepth.value = rows.value[index].depth;

  if (event.dataTransfer) {
    event.dataTransfer.effectAllowed = 'move';
    // Firefox surukleme baslatmak icin bir veri yuku sart kosuyor.
    event.dataTransfer.setData('text/plain', String(index));
  }
}

function onDragEnd() {
  dragIndex.value = null;
  dropIndex.value = null;
}

function onDragLeave(index) {
  if (dropIndex.value === index) {
    dropIndex.value = null;
  }
}

/**
 * Blogu `from`'dan cikarip `target`'a yerlestirdikten sonraki konum bilgisi.
 *
 * Derinlik sinirini hesaplamak icin blok LISTEDEN CIKARILMIS olmali: aksi halde
 * ogenin kendi eski komsulari "onceki satir" sayilir ve sinir yanlis cikar.
 * Bir oge ancak kendisinden onceki satirin cocugu olabilir.
 */
function placement(target) {
  const from = dragIndex.value;
  const list = [...rows.value];
  const size = subtreeSize(list, from) + 1;

  // Bir öğe kendi alt ağacının içine bırakılamaz.
  if (target > from && target < from + size) {
    return null;
  }

  const block = list.splice(from, size);
  const insertAt = target > from ? target - size + 1 : target;

  return {
    list,
    block,
    insertAt,
    maxDepth: insertAt > 0 ? list[insertAt - 1].depth + 1 : 0,
  };
}

/** Imlecin yatay kaymasindan hedef derinlik. */
function depthFor(target, clientX) {
  const spot = placement(target);

  if (spot === null) {
    return dropDepth.value;
  }

  const steps = Math.round((clientX - dragStartX.value) / INDENT_STEP);

  return Math.min(Math.max(dragDepth.value + steps, 0), spot.maxDepth);
}

function onDragOver(index, event) {
  if (dragIndex.value === null) {
    return;
  }

  dropIndex.value = index;
  dropDepth.value = depthFor(index, event.clientX);
}

/**
 * Sürüklenen öğe alt ağacıyla birlikte taşınır. Yeni derinlik yatay kaymadan
 * gelir; hedef konumda geçersizse (bir üstteki satırın çocuğu olamayacak kadar
 * derinse) kırpılır.
 */
function onDrop(target, event) {
  const from = dragIndex.value;

  if (from === null) {
    onDragEnd();

    return;
  }

  const depth = depthFor(target, event.clientX);
  const spot = placement(target);

  onDragEnd();

  if (spot === null) {
    return;
  }

  // Ayni satira, ayni derinlige birakmak islemsizdir.
  if (from === target && depth === spot.block[0].depth) {
    return;
  }

  const { list, block, insertAt } = spot;

  list.splice(insertAt, 0, ...shift(block, depth - block[0].depth));
  commit(list);
}

function update(index, patch) {
  const list = [...rows.value];

  list[index] = { ...list[index], ...patch };
  commit(list);
}

/** Eski ekranda olduğu gibi alt ağacıyla birlikte silinir. */
function remove(index) {
  const list = [...rows.value];

  list.splice(index, subtreeSize(list, index) + 1);
  commit(list);
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <div v-for="(item, index) in rows" :key="item.id ?? index" class="relative">
      <!--
        Hedef derinlik gostergesi: WordPress'teki gibi, birakilacak seviyeyi
        surukleme sirasinda gosterir. Sol bosluk hedef derinlikle ayni adimi
        (INDENT_STEP) kullanir, yani cizgi tam olarak ogenin inecegi yeri isaret eder.

        MUTLAK konumlu ve `pointer-events-none`: akista yer kaplasaydi belirip
        kaybolurken satirlari 2px oynatir, bu da dragover/dragleave'i tetikleyip
        gostergeyi titretirdi.
      -->
      <div
        v-if="dragIndex !== null && dropIndex === index"
        class="pointer-events-none absolute -top-1 left-0 right-0 h-0.5 rounded-full bg-p-accent"
        :style="{ marginLeft: `${dropDepth * 22}px` }"
      ></div>

      <div
        class="rounded-xl border bg-p-panel2 transition-colors"
        :style="{ marginLeft: `${item.depth * 22}px` }"
        :class="[
          dropIndex === index ? 'border-p-accent' : 'border-p-line',
          dragIndex === index ? 'opacity-50' : '',
        ]"
      >
        <div
          class="flex items-center gap-2 px-2.5 py-2"
          draggable="true"
          @dragstart="onDragStart(index, $event)"
          @dragover.prevent="onDragOver(index, $event)"
          @dragleave="onDragLeave(index)"
          @drop.prevent="onDrop(index, $event)"
          @dragend="onDragEnd"
        >
          <i class="fa-solid fa-grip-vertical cursor-grab text-[11px] text-p-ink3"></i>
          <i v-if="item.icon" :class="item.icon" class="w-4 text-[11px] text-p-ink3"></i>
          <span class="flex-1 truncate text-[12.5px] font-semibold">{{ item.title }}</span>
          <span class="hidden truncate text-[11px] text-p-ink3 sm:inline">{{ item.url }}</span>

          <button
            class="p-icon-btn"
            :title="__('menu.outdent')"
            :disabled="!canOutdent(index)"
            @click="outdent(index)"
          >
            <i class="fa-solid fa-outdent"></i>
          </button>
          <button
            class="p-icon-btn"
            :title="__('menu.indent')"
            :disabled="!canIndent(index)"
            @click="indent(index)"
          >
            <i class="fa-solid fa-indent"></i>
          </button>
          <button
            class="p-icon-btn"
            :title="__('general.edit')"
            @click="openId = openId === item.id ? null : item.id"
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

        <div
          v-if="openId === item.id"
          class="grid gap-2 border-t border-p-line2 p-2.5 sm:grid-cols-2"
        >
          <div>
            <label class="p-label">{{ __('menu.title') }}</label>
            <input
              class="p-input"
              :value="item.title"
              @input="update(index, { title: $event.target.value })"
            />
          </div>
          <div>
            <label class="p-label">{{ __('menu.url') }}</label>
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
              <option v-for="target in TARGETS" :key="target.value" :value="target.value">
                {{ target.label() }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
