<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import Tabs from '../../components/Tabs.vue';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Modal from '../../components/Modal.vue';
import MultiSelect from '../../components/MultiSelect.vue';

/*
 * panel/settings/index.blade.php karşılığı (1469 satır, 9 sekme).
 *
 * Aktif sekme bir URL SÖZLEŞMESİ: Cloudflare controller'ları geçersiz kimlik
 * bilgisinde `?tab=cloudflare` ile buraya yönlendiriyor — `Tabs` bileşenine
 * `queryKey` verilerek korunuyor.
 *
 * Şifreli alanlar (Cloudflare API anahtarı, OneSignal app_id/auth_key) arayüze
 * HİÇ gönderilmez; yalnızca "tanımlı mı" bilgisi taşınır ve boş bırakılırsa
 * sunucuda değişmez.
 */
const props = defineProps({
  tab: { type: String, default: 'general' },
  seo: { type: Object, default: () => ({}) },
  general: { type: Object, default: () => ({}) },
  logos: { type: Object, default: () => ({}) },
  advertise: { type: Object, default: () => ({}) },
  analytics: { type: Object, default: () => ({}) },
  social: { type: Object, default: () => ({}) },
  socialDisplay: { type: Object, default: () => ({}) },
  socialOptions: { type: Array, default: () => [] },
  /*
   * `languages` DEGIL, `languageRecords` - bkz. Menu/Index.vue `menuRecord`.
   * Bu ekranin dil YONETIM kayitlari (id / is_active / is_default). Ayni adli
   * sayfa prop'u, ust bar dil secicisinin okudugu paylasilan `languages`
   * prop'unu ezerdi.
   */
  languageRecords: { type: Array, default: () => [] },
  themes: { type: Array, default: () => [] },
  notifications: { type: Object, default: () => ({}) },
  cloudflare: { type: Object, default: () => ({}) },
  robots: { type: String, default: null },
});

usePageHeader(__('settings.settings'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('settings.settings') },
]);

const activeTab = ref(props.tab);
const seoLanguage = ref(props.languageRecords[0]?.code || null);
const confirm = ref(null);
const languageModal = ref(false);
const editingLanguage = ref(null);

const tabs = computed(() => [
  { value: 'general', label: __('general.general'), icon: 'fa-solid fa-gear' },
  { value: 'seo', label: 'SEO', icon: 'fa-solid fa-magnifying-glass-chart' },
  { value: 'analytics', label: __('dashboard.analytics'), icon: 'fa-solid fa-chart-line' },
  { value: 'advertisement', label: __('settings.advertise_tab'), icon: 'fa-solid fa-rectangle-ad' },
  { value: 'social-networks', label: __('social.social_networks'), icon: 'fa-solid fa-share-nodes' },
  { value: 'themes', label: __('themes.theme'), icon: 'fa-solid fa-palette' },
  { value: 'languages', label: __('language.language'), icon: 'fa-solid fa-language' },
  { value: 'notifications', label: __('notifications.notifications'), icon: 'fa-solid fa-bell' },
  { value: 'cloudflare', label: 'Cloudflare', icon: 'fa-brands fa-cloudflare' },
]);

const SOCIAL_FIELDS = [
  'website', 'linkedin', 'facebook', 'x', 'bluesky', 'instagram', 'github',
  'devto', 'medium', 'youtube', 'reddit', 'xbox', 'deviantart', 'twitch',
  'telegram', 'discord',
];

/*
 * Analitik / reklam / sosyal sekmeleri DB kolonları üzerinde döner; eski blade
 * her alanı elle yazdığı için kolon adı ile çeviri anahtarı arasında haritaya
 * ihtiyaç var. Haritada olmayan bir kolon eklendiğinde `__()` anahtarın kendisini
 * döndürür — ekran bozulmaz, eksik çeviri anahtar olarak görünür ve ne
 * ekleneceğini kendisi söyler.
 */
const ANALYTICS_LABELS = {
  ga_measurement_id: 'settings.analytics_ga_measurement_id',
  ga_api_secret: 'settings.analytics_ga_api_secret',
  google_analytics: 'settings.analytics_google_analytics',
  yandex_metrica: 'settings.analytics_yandex_metrica',
  fb_pixel: 'settings.analytics_fb_pixel',
  log_rocket: 'settings.analytics_log_rocket',
};

// Yalnızca üç alanın blade karşılığında yardım metni vardı.
const ANALYTICS_HELP = {
  ga_measurement_id: 'settings.analytics_ga_measurement_id_help',
  ga_api_secret: 'settings.analytics_ga_api_secret_help',
  google_analytics: 'settings.analytics_google_analytics_help',
};

const analyticsLabelKey = (key) => ANALYTICS_LABELS[key] || `settings.analytics_${key}`;
const advertiseLabelKey = (key) => `advertise.${key}`;
// `website` tek istisna: diğer ağların tümü kullanıcı adı ile tutuluyor.
const socialLabelKey = (field) => (field === 'website' ? 'social.website' : `social.${field}_username`);

const generalForm = useForm({
  ...props.general,
  site_logo_light: null,
  site_logo_dark: null,
  site_favicon: null,
  app_icon: null,
  // Eski blade ile ayni varsayilan (Google'in gunluk kotasi): satir bossa 200 gosterilir.
  google_indexing_daily_limit: props.general.google_indexing_daily_limit ?? 200,
});
const seoForm = useForm({ ...(props.seo[seoLanguage.value] || {}), language: seoLanguage.value });
const robotsForm = useForm({ robots_txt: props.robots || '' });
const analyticsForm = useForm({ ...props.analytics });
const advertiseForm = useForm({ ...props.advertise });
const socialForm = useForm({ ...props.social });
/*
 * `social_networks_header` / `_footer` ÇOKLU SEÇİM listesidir (json kolon),
 * boolean değil: sunucu düz dizi gönderir, düz dizi geri gider. `forceFormData`
 * KULLANILMAZ — dizi değerler JSON gövdesinde korunur.
 */
const socialDisplayForm = useForm({
  social_networks_header: [...(props.socialDisplay.social_networks_header || [])],
  social_networks_footer: [...(props.socialDisplay.social_networks_footer || [])],
});
const notificationsForm = useForm({
  safari_web_id: props.notifications.safari_web_id || '',
  user_segmentation: !!props.notifications.user_segmentation,
  app_id: '',
  auth_key: '',
});
const cloudflareForm = useForm({
  cf_email: props.cloudflare.cf_email || '',
  cf_domain: props.cloudflare.domain || '',
  cf_key: '',
});
const languageForm = useForm({ name: '', code: '', flag: '', is_active: true, is_default: false });
const themeUploadForm = useForm({ theme: null });
const themeUploadModal = ref(false);

// panel/settings/index.blade.php:1108-1119 karşılığı: alan başına ayrı silme route'u.
const LOGO_DELETE_ROUTES = {
  site_logo_light: () => route('admin.settings.general.logo.delete', { type: 'light' }),
  site_logo_dark: () => route('admin.settings.general.logo.delete', { type: 'dark' }),
  site_favicon: () => route('admin.settings.general.favicon.delete'),
  app_icon: () => route('admin.settings.general.app_icon.delete'),
};

function selectSeoLanguage(code) {
  seoLanguage.value = code;
  const data = props.seo[code] || {};
  seoForm.defaults({ ...data, language: code });
  seoForm.reset();
}

function post(form, routeName, params = {}) {
  form.post(route(routeName, params), { preserveScroll: true, forceFormData: true });
}

function saveSocialDisplay() {
  socialDisplayForm.post(route('admin.settings.social.header.save'), { preserveScroll: true });
}

function pickLogo(field, event) {
  generalForm[field] = event.target.files?.[0] || null;
}

async function deleteLogo(key) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(LOGO_DELETE_ROUTES[key](), {}, { preserveScroll: true });
}

function openLanguage(language = null) {
  editingLanguage.value = language;
  languageForm.defaults({
    name: language?.name || '',
    code: language?.code || '',
    flag: language?.flag || '',
    is_active: language ? language.is_active : true,
    is_default: language ? language.is_default : false,
  });
  languageForm.reset();
  languageForm.clearErrors();
  languageModal.value = true;
}

function saveLanguage() {
  const url = editingLanguage.value
    ? route('admin.settings.languages.save', { language: editingLanguage.value.id })
    : route('admin.settings.languages.save');

  languageForm.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      languageModal.value = false;
    },
  });
}

async function deleteLanguage(language) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(
    route('admin.settings.languages.delete'),
    { id: language.id },
    { preserveScroll: true },
  );
}

function activateTheme(theme) {
  // Durum degistiren GET yerine POST alias'i (v2 prefetch tuzagi).
  router.post(route('admin.settings.themes.activate', { theme: theme.name }), {}, { preserveScroll: true });
}

async function deleteTheme(theme) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('admin.settings.themes.delete'), { id: theme.id }, { preserveScroll: true });
}

function pickThemeFile(event) {
  themeUploadForm.theme = event.target.files?.[0] || null;
}

function uploadTheme() {
  themeUploadForm.post(route('admin.settings.themes.upload'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      themeUploadModal.value = false;
      themeUploadForm.reset();
    },
  });
}
</script>

<template>
  <Head :title="__('settings.settings')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <Tabs v-model="activeTab" :tabs="tabs" query-key="tab" />

    <!-- Genel -->
    <div v-if="activeTab === 'general'" class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField
          v-model="generalForm.contact_email"
          type="email"
          :label="__('settings.contact_email')"
          :error="generalForm.errors.contact_email"
        />
        <FormField v-model="generalForm.sharethis" label="ShareThis" />
        <FormField
          v-model="generalForm.homepage_featured_count"
          type="number"
          :label="__('settings.homepage_featured_count')"
          :help="__('settings.homepage_featured_count_help')"
        />
        <FormField
          v-model="generalForm.homepage_recent_count"
          type="number"
          :label="__('settings.homepage_recent_count')"
          :help="__('settings.homepage_recent_count_help')"
        />
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="item in [
            { key: 'site_logo_light', url: logos.light },
            { key: 'site_logo_dark', url: logos.dark },
            { key: 'site_favicon', url: logos.favicon },
            { key: 'app_icon', url: logos.app_icon },
          ]"
          :key="item.key"
        >
          <label class="p-label">{{ __(`settings.${item.key}`) }}</label>
          <img
            v-if="item.url"
            :src="item.url"
            alt=""
            class="mb-2 h-16 w-full rounded-xl border border-p-line bg-p-panel2 object-contain p-2"
          />
          <input
            type="file"
            accept="image/*"
            class="p-input h-auto py-1.5"
            @change="pickLogo(item.key, $event)"
          />
          <button
            v-if="item.url"
            class="p-btn mt-1.5 !text-p-danger"
            @click="deleteLogo(item.key)"
          >
            <i class="fa-solid fa-trash text-[11px]"></i>
          </button>
        </div>
      </div>

      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="generalForm.processing"
          @click="post(generalForm, 'admin.settings.general.save')"
        >
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- SEO -->
    <div v-else-if="activeTab === 'seo'" class="flex flex-col gap-3.5">
      <div class="flex flex-wrap gap-1.5">
        <button
          v-for="language in languageRecords"
          :key="language.code"
          class="p-tab"
          :class="seoLanguage === language.code && 'p-tab-active'"
          @click="selectSeoLanguage(language.code)"
        >
          {{ language.name }}
        </button>
      </div>

      <div class="p-card p-4">
        <div class="grid gap-3 sm:grid-cols-2">
          <FormField v-model="seoForm.site_name" :label="__('settings.site_name')" />
          <FormField v-model="seoForm.title" :label="__('settings.site_title')" />
          <FormField v-model="seoForm.author" :label="__('settings.site_author')" />
          <FormField v-model="seoForm.robots" :label="__('settings.robots')" />
          <FormField v-model="seoForm.keywords" :label="__('settings.site_keywords')" full />
          <FormField
            v-model="seoForm.description"
            type="textarea"
            :label="__('settings.site_description')"
            full
          />
        </div>

        <div class="mt-4 flex justify-end">
          <button
            class="p-btn-primary"
            :disabled="seoForm.processing"
            @click="post(seoForm, 'admin.settings.seo.save')"
          >
            <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
          </button>
        </div>
      </div>

      <div class="p-card p-4">
        <label class="p-label">{{ __('settings.robots_txt') }}</label>
        <textarea v-model="robotsForm.robots_txt" class="p-textarea h-48 font-mono"></textarea>
        <div class="mt-3 flex justify-end">
          <button
            class="p-btn-primary"
            :disabled="robotsForm.processing"
            @click="post(robotsForm, 'admin.settings.seo.robots.save')"
          >
            {{ __('general.save') }}
          </button>
        </div>
      </div>

      <div class="p-card p-4">
        <label class="p-label">{{ __('settings.llms_txt') }}</label>
        <div class="grid gap-3">
          <FormField
            v-model="generalForm.llms_txt_intro"
            type="textarea"
            :label="__('settings.llms_txt_intro')"
            :placeholder="__('settings.llms_txt_intro_placeholder')"
          />
          <FormField
            v-model="generalForm.llms_txt_instructions"
            type="textarea"
            :label="__('settings.llms_txt_instructions')"
            :placeholder="__('settings.llms_txt_instructions_placeholder')"
          />
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <button class="p-btn" @click="router.post(route('admin.settings.seo.llms.clear-cache'), {}, { preserveScroll: true })">
            <i class="fa-solid fa-broom text-[11px]"></i> {{ __('cache.clear_cache') }}
          </button>
          <button class="p-btn-primary" @click="post(generalForm, 'admin.settings.seo.llms.save')">
            {{ __('general.save') }}
          </button>
        </div>
      </div>

      <div class="p-card p-4">
        <label class="p-label">{{ __('settings.google_indexing_title') }}</label>
        <div class="grid gap-3">
          <label class="flex items-center gap-2.5 text-[12.5px]">
            <input v-model="generalForm.google_indexing_enabled" type="checkbox" />
            {{ __('settings.google_indexing_enabled') }}
          </label>
          <div class="grid gap-3 sm:grid-cols-2">
            <FormField
              v-model="generalForm.google_indexing_daily_limit"
              type="number"
              :label="__('settings.google_indexing_daily_limit')"
              :help="__('settings.google_indexing_daily_limit_help')"
            />
            <FormField
              v-model="generalForm.google_indexing_site_url"
              :label="__('settings.google_indexing_site_url')"
              :help="__('settings.google_indexing_site_url_help')"
            />
          </div>
          <div class="text-[11.5px] text-p-ink3">{{ __('settings.google_indexing_credentials_help') }}</div>
        </div>
        <div class="mt-3 flex justify-end">
          <button
            class="p-btn-primary"
            :disabled="generalForm.processing"
            @click="post(generalForm, 'admin.settings.seo.google-indexing.save')"
          >
            {{ __('general.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Analitik -->
    <div v-else-if="activeTab === 'analytics'" class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField
          v-for="(value, key) in analytics"
          :key="key"
          v-model="analyticsForm[key]"
          :label="__(analyticsLabelKey(key))"
          :help="ANALYTICS_HELP[key] ? __(ANALYTICS_HELP[key]) : undefined"
          :error="analyticsForm.errors[key]"
        />
      </div>
      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="analyticsForm.processing"
          @click="post(analyticsForm, 'admin.settings.analytics.save')"
        >
          {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Reklam -->
    <div v-else-if="activeTab === 'advertisement'" class="p-card p-4">
      <div class="grid gap-3">
        <FormField
          v-for="(value, key) in advertise"
          :key="key"
          v-model="advertiseForm[key]"
          type="textarea"
          :label="__(advertiseLabelKey(key))"
          :error="advertiseForm.errors[key]"
        />
      </div>
      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="advertiseForm.processing"
          @click="post(advertiseForm, 'admin.settings.advertisement.save')"
        >
          {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Sosyal ağlar -->
    <div v-else-if="activeTab === 'social-networks'" class="flex flex-col gap-3.5">
      <div class="p-card p-4">
        <div class="grid gap-3 sm:grid-cols-2">
          <FormField
            v-for="field in SOCIAL_FIELDS"
            :key="field"
            v-model="socialForm[field]"
            :label="__(socialLabelKey(field))"
          />
        </div>
        <div class="mt-4 flex justify-end">
          <button
            class="p-btn-primary"
            :disabled="socialForm.processing"
            @click="post(socialForm, 'admin.settings.social.save')"
          >
            {{ __('general.save') }}
          </button>
        </div>
      </div>

      <!--
        panel/settings/index.blade.php:545-569 karşılığı: iki ÇOKLU SEÇİM listesi.
        Kolonlar hangi ağların üst/alt menüde görüneceğini tutar, boolean değil.
      -->
      <div class="p-card p-4">
        <div class="grid gap-3 sm:grid-cols-2">
          <div>
            <label class="p-label">{{ __('social.show_header') }}</label>
            <MultiSelect
              v-model="socialDisplayForm.social_networks_header"
              :options="socialOptions"
              :placeholder="__('social.social_networks')"
            />
          </div>
          <div>
            <label class="p-label">{{ __('social.show_footer') }}</label>
            <MultiSelect
              v-model="socialDisplayForm.social_networks_footer"
              :options="socialOptions"
              :placeholder="__('social.social_networks')"
            />
          </div>
        </div>
        <div class="mt-3 flex justify-end">
          <button
            class="p-btn-primary"
            :disabled="socialDisplayForm.processing"
            @click="saveSocialDisplay()"
          >
            {{ __('general.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Temalar -->
    <div v-else-if="activeTab === 'themes'" class="flex flex-col gap-3.5">
      <div class="flex justify-end">
        <button class="p-btn-primary" @click="themeUploadModal = true">
          <i class="fa-solid fa-upload text-xs"></i> {{ __('themes.upload_theme') }}
        </button>
      </div>

      <div class="p-card divide-y divide-p-line2">
        <div v-for="theme in themes" :key="theme.id" class="flex items-center gap-3 px-4 py-3">
          <span class="flex-1 text-[12.5px] font-semibold">{{ theme.name }}</span>
          <span v-if="theme.is_default" class="p-chip p-chip-accent">{{ __('language.default') }}</span>
          <button v-else class="p-btn" @click="activateTheme(theme)">
            {{ __('themes.make_default') }}
          </button>
          <button
            v-if="!theme.is_default"
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            @click="deleteTheme(theme)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
        <div v-if="!themes.length" class="px-4 py-10 text-center text-p-ink3">
          {{ __('general.no_records') }}
        </div>
      </div>
    </div>

    <!-- Diller -->
    <div v-else-if="activeTab === 'languages'" class="flex flex-col gap-3.5">
      <div class="flex justify-end">
        <button class="p-btn-primary" @click="openLanguage()">
          <i class="fa-solid fa-plus text-xs"></i> {{ __('general.new') }}
        </button>
      </div>

      <div class="p-card divide-y divide-p-line2">
        <div
          v-for="language in languageRecords"
          :key="language.id"
          class="flex items-center gap-3 px-4 py-3"
        >
          <span class="flex-1 text-[12.5px] font-semibold">{{ language.name }}</span>
          <span class="p-chip">{{ language.code }}</span>
          <span v-if="language.is_default" class="p-chip p-chip-accent">
            {{ __('language.default') }}
          </span>
          <span v-if="!language.is_active" class="p-chip">{{ __('post.draft') }}</span>

          <button class="p-icon-btn" @click="openLanguage(language)">
            <i class="fa-solid fa-pen"></i>
          </button>
          <button
            v-if="!language.is_default"
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            @click="deleteLanguage(language)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Bildirimler -->
    <div v-else-if="activeTab === 'notifications'" class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField
          v-model="notificationsForm.app_id"
          :label="`${__('settings.onesignal_app_id')}${notifications.has_app_id ? ' ✓' : ''}`"
          :help="__('settings.leave_blank_to_keep')"
        />
        <FormField
          v-model="notificationsForm.auth_key"
          type="password"
          :label="`${__('settings.onesignal_auth_key')}${notifications.has_auth_key ? ' ✓' : ''}`"
          :help="__('settings.leave_blank_to_keep')"
        />
        <FormField
          v-model="notificationsForm.safari_web_id"
          :label="__('settings.onesignal_safari_web_id')"
        />
        <label class="flex items-center gap-2.5 text-[12.5px]">
          <input v-model="notificationsForm.user_segmentation" type="checkbox" />
          {{ __('settings.onesignal_user_segmentation') }}
        </label>
      </div>
      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="notificationsForm.processing"
          @click="post(notificationsForm, 'admin.settings.notifications.save')"
        >
          {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Cloudflare -->
    <div v-else class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField
          v-model="cloudflareForm.cf_email"
          type="email"
          :label="__('cloudflare.email')"
          :error="cloudflareForm.errors.cf_email"
        />
        <FormField
          v-model="cloudflareForm.cf_domain"
          :label="__('cloudflare.domain')"
          :error="cloudflareForm.errors.cf_domain"
        />
        <FormField
          v-model="cloudflareForm.cf_key"
          type="password"
          :label="`${__('cloudflare.api_key')}${cloudflare.has_key ? ' ✓' : ''}`"
          :help="__('settings.leave_blank_to_keep')"
          :error="cloudflareForm.errors.cf_key"
        />
      </div>
      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="cloudflareForm.processing"
          @click="post(cloudflareForm, 'cf.update.api.settings')"
        >
          {{ __('general.save') }}
        </button>
      </div>
    </div>

    <Modal
      v-model:open="languageModal"
      :title="editingLanguage ? __('general.edit') : __('general.new')"
      icon="fa-solid fa-language"
      @confirm="saveLanguage"
    >
      <div class="grid gap-3">
        <FormField
          v-model="languageForm.name"
          :label="__('language.language')"
          :error="languageForm.errors.name"
        />
        <FormField
          v-model="languageForm.code"
          :label="__('language.code')"
          :error="languageForm.errors.code"
        />
        <FormField v-model="languageForm.flag" :label="__('language.flag')" />
        <label class="flex items-center gap-2.5 text-[12.5px]">
          <input v-model="languageForm.is_active" type="checkbox" /> {{ __('language.status') }}
        </label>
        <label class="flex items-center gap-2.5 text-[12.5px]">
          <input v-model="languageForm.is_default" type="checkbox" /> {{ __('language.default') }}
        </label>
      </div>
    </Modal>

    <Modal
      v-model:open="themeUploadModal"
      :title="__('themes.upload_theme')"
      icon="fa-solid fa-palette"
      @confirm="uploadTheme"
    >
      <div class="grid gap-3">
        <input
          type="file"
          accept=".zip"
          class="p-input h-auto py-1.5"
          @change="pickThemeFile"
        />
        <div v-if="themeUploadForm.errors.theme" class="text-[11.5px] text-p-danger">
          {{ themeUploadForm.errors.theme }}
        </div>
      </div>
    </Modal>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
