<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { pushToast } from '../../composables/useToast';
import TinyMceEditor from '../../components/TinyMceEditor.vue';
import FormField from '../../components/FormField.vue';
import MultiSelect from '../../components/MultiSelect.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/*
 * panel/post/add-edit.blade.php karşılığı.
 *
 * KORUNAN SÖZLEŞMELER (bunlara dokunulmadı):
 *  - TinyMCE self-hosted ve konfigürasyonu birebir (bkz. TinyMceEditor.vue).
 *  - Görsel yüklemede TASLAK OLUŞTURMA akışı: yeni yazıda ilk görsel
 *    yüklendiğinde sunucu "<başlık> (draft)" bir post yaratır ve id döner;
 *    o andan itibaren kaydetme ve yükleme URL'leri bu id'ye geçer.
 *  - `language_code` BİLEREK gönderilmiyor; eski form da göndermiyordu
 *    (detay: PostController::editorPost docblock'u).
 */
const props = defineProps({
  type: { type: String, required: true },
  post: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  languages: { type: Array, default: () => [] },
  sessionLanguage: { type: String, default: null },
});

const isPages = computed(() => props.type === 'pages');

const title = computed(() =>
  props.post.id
    ? __('general.edit')
    : __('general.new') + ' · ' + (isPages.value ? __('post.page') : __('post.blog')),
);

usePageHeader(title.value, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  {
    label: isPages.value ? __('post.pages') : __('post.blogs'),
    route: 'admin.posts',
    // `admin.posts` {panel}/{type} prefix'i altinda: `type` ZORUNLU segment.
    // Parametresiz birakilirsa ziggy-js firlatir ve breadcrumb'i render eden
    // PanelLayout alt agaci komple bos ekrana duser (olusturma + duzenleme).
    // Gecerli degerler SADECE 'blogs' | 'pages' — PostController digerlerinde
    // abort(404) yapiyor; props.type zaten dogru segmenti tasiyor.
    params: { type: props.type },
  },
  { label: props.post.title || __('general.new') },
]);

// Taslak id'si: ilk gorsel yuklemesinde sunucudan gelir.
const postId = ref(props.post.id || null);
const confirm = ref(null);
const drawerOpen = ref(false);
const imageFile = ref(null);
const imagePreview = ref(props.post.image);
const hreflangTab = ref(props.languages[0]?.code || null);
const editor = ref(null);

const form = useForm({
  id: props.post.id || '',
  title: props.post.title || '',
  slug: props.post.slug || '',
  content: props.post.content || '',
  language: props.post.language || props.sessionLanguage || props.languages[0]?.code,
  category_id: [...(props.post.category_ids || [])],
  meta_keywords: props.post.meta_keywords || '',
  meta_description: props.post.meta_description || '',
  user_id: props.post.user_id || '',
  is_published: props.post.is_published ? 1 : 0,
  published_at: props.post.published_at,
  hreflang: { ...(props.post.hreflang || {}) },
  hreflang_url: { ...(props.post.hreflang || {}) },
  image: null,
});

/*
 * Kategoriler DİLE BAĞLI. Eski blade dil değişiminde `admin.categories.list`
 * ucuna AJAX atıp seçenekleri yeniliyordu (add-edit.blade.php:475-495); Vue
 * portunda bu kopmuştu ve başka dilin kategorileri yazıya bağlanabiliyordu.
 * Uç veri döndürdüğü için JSON kalır ve axios ile çağrılır (R1).
 */
function categoryLabel(item) {
  return item.language ? `${item.name} (${item.language})` : item.name;
}

const categoryOptions = ref(
  props.categories.map((item) => ({ value: String(item.id), label: categoryLabel(item) })),
);

watch(
  () => form.language,
  async (code, previous) => {
    if (!code || code === previous) {
      return;
    }

    try {
      const { data } = await axios.post(route('admin.categories.list'), { language: code });

      categoryOptions.value = (Array.isArray(data) ? data : []).map((item) => ({
        value: String(item.id),
        label: categoryLabel(item),
      }));
    } catch (error) {
      pushToast(error.response?.data?.message || __('general.error'), 'error');

      return;
    }

    // Hedef dilde bulunmayan seçimler düşer: aksi halde kaydetme anında başka
    // dilin kategorileri post_categories'e yazılıyordu.
    form.category_id = form.category_id.filter((id) =>
      categoryOptions.value.some((option) => option.value === String(id)),
    );
  },
);

/*
 * QR kod paneli — add-edit.blade.php:224-248 + 670-748 karşılığı.
 * Eski ekran QR'ı CDN'den yüklenen qrcodejs ile çiziyordu; yeni bağımlılık
 * eklenmedi, SVG sunucuda üretilip `post.qr_image` data-URI'si olarak geliyor.
 * `admin.post.qr.generate` veri ucu olduğu için JSON kalır (R1).
 */
const qrBusy = ref(false);

async function generateQr() {
  if (qrBusy.value || !postId.value) {
    return;
  }

  if (
    props.post.qr_link &&
    !(await confirm.value.ask({
      title: __('post.qr_regenerate'),
      body: __('post.qr_regenerate_confirm'),
    }))
  ) {
    return;
  }

  qrBusy.value = true;

  try {
    const { data } = await axios.post(
      route('admin.post.qr.generate', { type: props.type, post: postId.value }),
    );

    if (data?.status === 'success') {
      pushToast(__('general.saved'), 'success');
      // Yeni bağlantının SVG'si sunucuda üretiliyor: yalnız `post` prop'u tazelenir.
      router.reload({ only: ['post'], preserveScroll: true, preserveState: true });
    }
  } catch (error) {
    pushToast(error.response?.data?.message || __('general.error'), 'error');
  } finally {
    qrBusy.value = false;
  }
}

/** TinyMCE görsel yükleme isteğine eklenen alanlar — eski form ile birebir. */
function uploadMeta() {
  return {
    language: form.language,
    meta_keywords: form.meta_keywords,
    title: form.title,
    slug: form.slug,
  };
}

const uploadUrl = computed(() =>
  postId.value
    ? route('admin.post.editor.image.upload', { type: props.type, post: postId.value })
    : route('admin.post.editor.image.upload', { type: props.type }),
);

/**
 * Yeni yazıda ilk görsel yüklemesi sunucuda taslak post yaratır ve id döner.
 * Bu andan itibaren kaydetme ve sonraki yüklemeler o id'ye gider.
 */
function onUploaded(payload) {
  if (!postId.value && payload?.blog_id) {
    postId.value = payload.blog_id;
    form.id = payload.blog_id;
    pushToast(__('post.draft_created'), 'info');
  }
}

function pickImage(event) {
  const file = event.target.files?.[0] || null;
  imageFile.value = file;
  form.image = file;
  imagePreview.value = file ? URL.createObjectURL(file) : props.post.image;
}

function submit() {
  const url = postId.value
    ? route('admin.post.update', { type: props.type, post: postId.value })
    : route('admin.post.save', { type: props.type });

  form
    .transform((data) => ({ ...data, id: postId.value || '' }))
    .post(url, { forceFormData: true, preserveScroll: true });
}

function removeImage() {
  if (!postId.value) {
    imageFile.value = null;
    form.image = null;
    imagePreview.value = null;

    return;
  }

  router.post(
    route('admin.post.image.delete', { type: props.type, post: postId.value }),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        imagePreview.value = null;
      },
    },
  );
}
</script>

<template>
  <Head :title="title" />

  <div class="flex min-h-0 flex-1">
    <!-- Editör -->
    <div class="flex min-w-0 flex-1 flex-col gap-3.5 p-[22px]">
      <input
        v-model="form.title"
        :placeholder="__('post.title')"
        class="w-full border-0 bg-transparent font-display text-[30px] font-extrabold text-p-ink outline-none placeholder:text-p-ink3"
      />
      <div v-if="form.errors.title" class="text-[11px] font-semibold text-p-danger">
        {{ form.errors.title }}
      </div>

      <div class="flex flex-wrap items-center gap-2 text-[12px] text-p-ink3">
        <span>{{ $page.props.siteUrl }}/{{ form.language }}/</span>
        <input
          v-model="form.slug"
          :placeholder="__('post.slug')"
          class="min-w-[180px] flex-1 rounded-[9px] border border-p-line bg-p-panel2 px-2 py-1 text-p-ink outline-none focus:border-p-accent"
        />
      </div>
      <div v-if="form.errors.slug" class="text-[11px] font-semibold text-p-danger">
        {{ form.errors.slug }}
      </div>

      <TinyMceEditor
        ref="editor"
        v-model="form.content"
        :upload-url="uploadUrl"
        :language="form.language"
        :ai-enabled="$page.props.aiEnabled"
        :upload-meta="uploadMeta"
        @uploaded="onUploaded"
      />
      <div v-if="form.errors.content" class="text-[11px] font-semibold text-p-danger">
        {{ form.errors.content }}
      </div>
    </div>

    <!-- Ayar çekmecesi: >=1120px sabit sütun, altında overlay -->
    <aside
      class="w-[320px] shrink-0 overflow-y-auto border-l border-p-line bg-p-panel p-[18px]"
      :class="
        drawerOpen
          ? 'fixed right-0 top-0 z-30 h-screen animate-slideIn shadow-pop'
          : 'hidden xl:block'
      "
    >
      <div class="flex items-center gap-2 pb-3">
        <div class="flex-1 font-display text-[13.5px] font-bold">{{ __('general.settings') }}</div>
        <button class="p-icon-btn xl:hidden" @click="drawerOpen = false">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="flex flex-col gap-3">
        <FormField
          v-model="form.is_published"
          type="select"
          :label="__('post.is_published')"
          :error="form.errors.is_published"
          :options="[
            { value: 1, label: __('post.published') },
            { value: 0, label: __('post.draft') },
          ]"
        />

        <FormField
          v-model="form.language"
          type="select"
          :label="__('language.language')"
          :options="languages.map((l) => ({ value: l.code, label: l.name }))"
        />

        <div v-if="!isPages">
          <label class="p-label">{{ __('categories.categories') }}</label>
          <MultiSelect
            v-model="form.category_id"
            :options="categoryOptions"
            :placeholder="__('post.select_category')"
          />
          <div v-if="form.errors.category_id" class="mt-1.5 text-[11px] font-semibold text-p-danger">
            {{ form.errors.category_id }}
          </div>
        </div>

        <FormField
          v-model="form.user_id"
          type="select"
          :label="__('post.author')"
          :error="form.errors.user_id"
          :options="users.map((u) => ({ value: u.id, label: u.nickname }))"
        />

        <FormField
          v-model="form.published_at"
          type="datetime-local"
          :label="__('post.published_at')"
        />

        <!-- Öne çıkan görsel -->
        <div>
          <label class="p-label">{{ __('post.image') }}</label>
          <div v-if="imagePreview" class="relative mb-2">
            <img :src="imagePreview" alt="" class="w-full rounded-xl border border-p-line" />
            <button
              class="p-icon-btn absolute right-2 top-2 !bg-p-panel hover:!border-p-danger hover:!text-p-danger"
              :title="__('post.delete_image')"
              @click="removeImage"
            >
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
          <input type="file" accept="image/*" class="p-input h-auto py-1.5" @change="pickImage" />
          <div v-if="form.errors.image" class="mt-1.5 text-[11px] font-semibold text-p-danger">
            {{ form.errors.image }}
          </div>
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

        <!-- Href Lang -->
        <div>
          <label class="p-label">Href Lang</label>
          <div class="mb-2 flex flex-wrap gap-1">
            <button
              v-for="language in languages"
              :key="language.code"
              class="p-tab !h-7 !px-2 !text-[11px]"
              :class="hreflangTab === language.code && 'p-tab-active'"
              @click="hreflangTab = language.code"
            >
              {{ language.code.toUpperCase() }}
            </button>
          </div>
          <input
            v-for="language in languages"
            v-show="hreflangTab === language.code"
            :key="language.code"
            v-model="form.hreflang_url[language.code]"
            class="p-input"
            :placeholder="`Href Lang (${language.code})`"
          />
        </div>

        <!-- QR Kod -->
        <div v-if="postId && !isPages" class="border-t border-p-line2 pt-3">
          <div class="mb-2 flex items-center gap-2">
            <label class="p-label !mb-0">
              <i class="fa-solid fa-qrcode text-[11px]"></i> {{ __('post.qr_code') }}
            </label>
            <span v-if="post.qr_link" class="p-chip ml-auto">
              {{ __('post.qr_scans', { count: post.qr_scans_count }) }}
            </span>
          </div>

          <template v-if="post.qr_link">
            <img
              v-if="post.qr_image"
              :src="post.qr_image"
              :alt="__('post.qr_code')"
              class="mx-auto w-[180px] rounded-xl border border-p-line bg-white p-2"
            />
            <p class="mt-2 break-all text-[11px] text-p-ink3">{{ post.qr_link }}</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
              <button class="p-btn" :disabled="qrBusy" @click="generateQr">
                <i class="fa-solid fa-rotate text-[11px]"></i> {{ __('post.qr_regenerate') }}
              </button>
              <a
                v-if="post.qr_image"
                class="p-btn no-underline"
                :href="post.qr_image"
                :download="`${form.slug || 'qr'}-qr.svg`"
              >
                <i class="fa-solid fa-download text-[11px]"></i> {{ __('post.qr_download') }}
              </a>
            </div>
          </template>

          <template v-else>
            <p class="text-[12px] text-p-ink3">{{ __('post.qr_not_created') }}</p>
            <button class="p-btn-primary mt-2 w-full justify-center" :disabled="qrBusy" @click="generateQr">
              <i class="fa-solid fa-qrcode text-xs"></i> {{ __('post.qr_create') }}
            </button>
          </template>
        </div>

        <!-- Kayıtlı yazıya özel bağlantılar -->
        <div v-if="postId" class="flex flex-col gap-1.5 border-t border-p-line2 pt-3">
          <Link
            :href="route('admin.post.media', { type, post: postId })"
            class="p-btn justify-start no-underline"
          >
            <i class="fa-solid fa-images text-[11px]"></i> {{ __('post.media') }}
          </Link>
          <Link
            :href="route('admin.post.history', { type, posts: postId })"
            class="p-btn justify-start no-underline"
          >
            <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
            {{ __('post.history') }}
            <span v-if="post.history_count" class="p-chip ml-auto">{{ post.history_count }}</span>
          </Link>
        </div>

        <button class="p-btn-primary mt-2 justify-center" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-floppy-disk text-xs"></i>
          {{ postId ? __('general.update') : __('general.create') }}
        </button>
      </div>
    </aside>

    <!-- Dar ekranda çekmeceyi aç -->
    <button
      class="p-btn-primary fixed bottom-5 right-5 z-20 !h-11 !w-11 !justify-center !rounded-full xl:hidden"
      :title="__('general.settings')"
      @click="drawerOpen = true"
    >
      <i class="fa-solid fa-sliders"></i>
    </button>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
