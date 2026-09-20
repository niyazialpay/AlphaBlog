<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import CategoryTree from '../../components/CategoryTree.vue';

/*
 * panel/post/category/index.blade.php karşılığı.
 *
 * Sol tarafta ağaç, sağda form — tasarım paketindeki düzen. Ağaç sunucuda
 * çözülür (blade özyinelemeli bir partial kullanıyordu) ve boş `new Categories`
 * modeli artık prop olarak geçmez.
 */
const props = defineProps({
  language: { type: String, default: null },
  tree: { type: Array, default: () => [] },
  flat: { type: Array, default: () => [] },
  category: { type: Object, default: null },
  /*
   * `languages` DEĞİL, `languageOptions`: paylaşılan `languages` prop'u bayrak
   * taşıyan üst bar dil listesidir ve aynı adlı sayfa prop'u onu EZER
   * (bkz. Menu/Index.vue `menuRecord`). Buradaki liste yalnızca form seçenekleri.
   */
  languageOptions: { type: Array, default: () => [] },
});

usePageHeader(__('categories.categories'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('categories.categories') },
]);

const confirm = ref(null);
const imagePreview = ref(props.category?.image || null);
const hreflangTab = ref(props.languageOptions[0]?.code || null);

const editing = computed(() => !!props.category);

const form = useForm({
  name: props.category?.name || '',
  slug: props.category?.slug || '',
  language: props.category?.language || props.language,
  parent_id: props.category?.parent_id || '',
  meta_description: props.category?.meta_description || '',
  meta_keywords: props.category?.meta_keywords || '',
  hreflang_url: { ...(props.category?.hreflang || {}) },
  image: null,
});

// Ust kategori secenekleri: duzenlenen kategori kendi kendinin ustu olamaz.
const parentOptions = computed(() => [
  { value: '', label: '—' },
  ...props.flat
    .filter((item) => !props.category || item.id !== props.category.id)
    .map((item) => ({ value: item.id, label: item.name })),
]);

function switchLanguage(code) {
  router.get(route('admin.categories'), { tab: code }, { preserveState: false });
}

function pickImage(event) {
  const file = event.target.files?.[0] || null;
  form.image = file;
  imagePreview.value = file ? URL.createObjectURL(file) : props.category?.image || null;
}

function submit() {
  const url = editing.value
    ? route('admin.categories', { category: props.category.id })
    : route('admin.categories');

  form.post(url, { forceFormData: true, preserveScroll: true });
}

function edit(node) {
  router.get(route('admin.categories', { category: node.id }));
}

async function destroy(node) {
  if (!(await confirm.value.ask({ body: __('categories.delete_confirm') }))) {
    return;
  }

  router.post(route('admin.categories.delete'), { id: node.id }, { preserveScroll: true });
}

function removeImage() {
  if (!props.category) {
    form.image = null;
    imagePreview.value = null;

    return;
  }

  router.post(
    route('admin.categories.image.delete'),
    { id: props.category.id },
    { preserveScroll: true, onSuccess: () => (imagePreview.value = null) },
  );
}

watch(
  () => props.category,
  (value) => {
    imagePreview.value = value?.image || null;
  },
);
</script>

<template>
  <Head :title="__('categories.categories')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap gap-1.5">
      <button
        v-for="item in languageOptions"
        :key="item.code"
        class="p-tab"
        :class="language === item.code && 'p-tab-active'"
        @click="switchLanguage(item.code)"
      >
        {{ item.name }}
      </button>
    </div>

    <div class="grid gap-3.5 lg:grid-cols-[minmax(0,1fr)_380px]">
      <!-- Ağaç -->
      <div class="p-card p-4">
        <div class="mb-3 font-display text-[13.5px] font-bold">
          {{ __('categories.categories') }}
        </div>

        <CategoryTree :nodes="tree" @edit="edit" @delete="destroy" />

        <div v-if="!tree.length" class="py-8 text-center text-[12.5px] text-p-ink3">
          {{ __('general.no_records') }}
        </div>
      </div>

      <!-- Form -->
      <div class="p-card h-max p-4">
        <div class="mb-3 flex items-center gap-2">
          <div class="flex-1 font-display text-[13.5px] font-bold">
            {{ editing ? __('general.edit') : __('general.new') }}
          </div>
          <Link v-if="editing" :href="route('admin.categories')" class="p-icon-btn">
            <i class="fa-solid fa-xmark"></i>
          </Link>
        </div>

        <div class="flex flex-col gap-3">
          <FormField
            v-model="form.name"
            :label="__('categories.name')"
            :error="form.errors.name"
          />
          <FormField
            v-model="form.slug"
            :label="__('categories.slug')"
            :error="form.errors.slug"
          />
          <FormField
            v-model="form.language"
            type="select"
            :label="__('language.language')"
            :options="languageOptions.map((l) => ({ value: l.code, label: l.name }))"
          />
          <FormField
            v-model="form.parent_id"
            type="select"
            :label="__('categories.parent_category')"
            :options="parentOptions"
          />

          <div>
            <label class="p-label">{{ __('post.image') }}</label>
            <div v-if="imagePreview" class="relative mb-2">
              <img :src="imagePreview" alt="" class="w-full rounded-xl border border-p-line" />
              <button
                class="p-icon-btn absolute right-2 top-2 !bg-p-panel hover:!border-p-danger hover:!text-p-danger"
                @click="removeImage"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
            <input type="file" accept="image/*" class="p-input h-auto py-1.5" @change="pickImage" />
          </div>

          <FormField
            v-model="form.meta_description"
            type="textarea"
            :label="__('post.meta_description')"
            :error="form.errors.meta_description"
          />
          <FormField
            v-model="form.meta_keywords"
            :label="__('post.meta_keywords')"
            :error="form.errors.meta_keywords"
          />

          <div>
            <label class="p-label">Href Lang</label>
            <div class="mb-2 flex flex-wrap gap-1">
              <button
                v-for="item in languageOptions"
                :key="item.code"
                class="p-tab !h-7 !px-2 !text-[11px]"
                :class="hreflangTab === item.code && 'p-tab-active'"
                @click="hreflangTab = item.code"
              >
                {{ item.code.toUpperCase() }}
              </button>
            </div>
            <input
              v-for="item in languageOptions"
              v-show="hreflangTab === item.code"
              :key="item.code"
              v-model="form.hreflang_url[item.code]"
              class="p-input"
              :placeholder="`Href Lang (${item.code})`"
            />
          </div>

          <button class="p-btn-primary justify-center" :disabled="form.processing" @click="submit">
            <i class="fa-solid fa-floppy-disk text-xs"></i>
            {{ editing ? __('general.update') : __('general.create') }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
