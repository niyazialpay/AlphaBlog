<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Webpass from '@laragear/webpass';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import { pushToast } from '../../composables/useToast';
import { usePush } from '../../composables/usePush';
import Tabs from '../../components/Tabs.vue';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/profile/index.blade.php karşılığı (1220 satır, 5 sekme).
 *
 * `admin.profile.index` ve `admin.user.edit` AYNI bileşeni render eder — eski
 * Blade de öyleydi. `isSelf` hangi eylemlerin görüneceğini sürer.
 *
 * WebAuthn kayıt akışı (`webauthn.register.options` → `webauthn.register`)
 * CSRF'siz ve Inertia dışı kalmalı: @laragear/webpass protokolü.
 *
 * DAVRANIŞ NOTU (2FA sekmesi): panel/profile/partials/two-factor-authentication-tab.blade.php
 * karşılığı burada `security` sekmesine 4. alt sekme olarak taşındı. Eski
 * partial route parametresindeki hedef kullanıcıyı değil DAİMA auth()->user()'ı
 * gösteriyordu; bu Vue portu bu kafa karıştırıcı davranışı taşımıyor ve sekmeyi
 * yalnızca `isSelf` iken gösteriyor. `two-factor.enable`/`.disable` Fortify'ın
 * kendi yanıt sözleşmesini (Inertia isteğinde back()) döndürür; `two-factor.confirm`
 * ise daima çıplak JSON döner (bkz. TwoFactorAuthController::confirm) — bu yüzden
 * axios ile çağrılır, router/useForm ile DEĞİL.
 */
const props = defineProps({
  isSelf: { type: Boolean, default: true },
  profile: { type: Object, required: true },
  twoFactor: { type: Object, default: null },
  social: { type: Object, default: () => ({}) },
  privacy: { type: Object, default: () => ({}) },
  sessions: { type: Object, required: true },
  assignableRoles: { type: Array, default: () => [] },
  /*
   * Bildirim olaylari: `[{ key, label, database, push }]`.
   *
   * Liste SUNUCUDAN gelir (App\Support\Notifications\NotificationEvents) ve
   * kullanicinin yetkisine gore suzulmustur — gormedigi bir olayi ekranda
   * bulamaz. Yalnizca KENDI profilinde doludur; `admin.profile.notifications.preferences`
   * daima `$request->user()` uzerine yazar, baska birinin profilinde gosterilse
   * yanlis kullaniciyi degistirirdi.
   */
  notificationEvents: { type: Array, default: () => [] },
});

usePageHeader(props.isSelf ? __('user.profile') : props.profile.nickname, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  ...(props.isSelf ? [] : [{ label: __('user.users'), route: 'admin.users' }]),
  { label: props.profile.nickname },
]);

const TAB_VALUES = ['about', 'social', 'security', 'privacy', 'notifications', 'sessions'];

/*
 * `?tab=` ile derin baglanti. Bildirim zilindeki "bildirim ayarlari" baglantisi
 * dogrudan bu sekmeyi aciyor.
 *
 * Tabs bilesenine `queryKey` VERILMEDI bilerek: queryKey her sekme
 * degisiminde `router.visit()` yapar, yani her tiklamada sunucuya gidilir.
 * Burada URL yalnizca ACILISTA okunuyor; sekme gecisleri eskisi gibi tamamen
 * istemcide.
 */
function initialTab() {
  try {
    const requested = new URLSearchParams(window.location.search).get('tab');

    // Bildirim sekmesi yalnizca kendi profilinde ve gorulebilir olay varsa var.
    if (requested === 'notifications' && !(props.isSelf && props.notificationEvents.length)) {
      return 'about';
    }

    return TAB_VALUES.includes(requested) ? requested : 'about';
  } catch (error) {
    return 'about';
  }
}

const tab = ref(initialTab());
const securityTab = ref('password');
const confirm = ref(null);
const credentials = ref([]);

const tabs = computed(() =>
  [
    { value: 'about', label: __('profile.about_me_tab'), icon: 'fa-solid fa-id-card' },
    { value: 'social', label: __('social.social_networks'), icon: 'fa-solid fa-share-nodes' },
    { value: 'security', label: __('profile.security'), icon: 'fa-solid fa-shield-halved' },
    { value: 'privacy', label: __('privacy.privacy_tab'), icon: 'fa-solid fa-user-lock' },
    props.isSelf && props.notificationEvents.length
      ? {
          value: 'notifications',
          label: __('notifications.preferences_tab'),
          icon: 'fa-solid fa-bell',
        }
      : null,
    { value: 'sessions', label: __('sessions.sessions_tab'), icon: 'fa-solid fa-desktop' },
  ].filter(Boolean),
);

const securityTabs = computed(() =>
  [
    { value: 'password', label: __('user.password') },
    { value: 'email', label: __('user.email') },
    // twoFactor yalnizca isSelf'te doldurulur (bkz. profileResponse).
    props.isSelf && props.twoFactor ? { value: 'two-factor', label: __('profile.two_fa_tab') } : null,
    { value: 'passkey', label: __('webauthn.register_device') },
  ].filter(Boolean),
);

const SOCIAL_FIELDS = [
  'website', 'linkedin', 'facebook', 'x', 'bluesky', 'instagram', 'github',
  'devto', 'medium', 'youtube', 'reddit', 'xbox', 'deviantart', 'twitch',
  'telegram', 'discord',
];

const PRIVACY_FIELDS = [
  'show_name', 'show_surname', 'show_location', 'show_education',
  'show_job_title', 'show_skills', 'show_about', 'show_social_links',
];

const aboutForm = useForm({
  name: props.profile.name || '',
  surname: props.profile.surname || '',
  nickname: props.profile.nickname || '',
  location: props.profile.location || '',
  job_title: props.profile.job_title || '',
  education: props.profile.education || '',
  skills: props.profile.skills || '',
  about: props.profile.about || '',
  role: props.profile.role || '',
});

const socialForm = useForm({ ...props.social });
const privacyForm = useForm({ ...props.privacy });
const passwordForm = useForm({ old_password: '', password: '', password_confirmation: '' });
const emailForm = useForm({ email: props.profile.email || '' });

/*
 * Bildirim tercihleri.
 *
 * FORM eylemi (R1): `useForm().post()` ile gonderilir, uc `back()->with(...)`
 * doner. Veri ucu DEGIL — `axios` kullanilmaz.
 *
 * Anahtarlar nokta iceriyor (`comment.created`). Inertia govdeyi JSON olarak
 * gonderdigi icin anahtar OLDUGU GIBI korunur; sunucu `$request->input('preferences')`
 * ile diziyi alip kendi izin verdigi olaylar uzerinden doner.
 */
const notificationForm = useForm({
  preferences: Object.fromEntries(
    props.notificationEvents.map((event) => [
      event.key,
      { database: Boolean(event.database), push: Boolean(event.push) },
    ]),
  ),
});

function saveNotifications() {
  notificationForm.post(route('admin.profile.notifications.preferences'), { preserveScroll: true });
}

/*
 * Bu CIHAZIN push aboneligi. Durum zil dropdown'i ile ortak (usePush tekil bir
 * modul durumu tutar), yani buradan acildiginda zil de aninda guncellenir.
 */
const {
  state: pushState,
  permission: pushPermission,
  busy: pushBusy,
  subscribed: pushSubscribed,
  toggle: togglePushState,
} = usePush();

async function togglePush() {
  const wasOn = pushSubscribed.value;
  const ok = await togglePushState();

  if (ok) {
    pushToast(
      wasOn ? __('notifications.push_unsubscribed') : __('notifications.push_subscribed'),
      'success',
    );

    return;
  }

  pushToast(
    pushPermission.value === 'denied'
      ? __('notifications.push_blocked')
      : __('notifications.push_error'),
    'error',
  );
}

function saveAbout() {
  const url = props.isSelf
    ? route('admin.profile.save')
    : route('admin.user.edit', { user_id: props.profile.id });

  aboutForm.post(url, { preserveScroll: true });
}

function saveSocial() {
  const url = props.isSelf
    ? route('admin.profile.social.save')
    : route('admin.user.social.save', { user_id: props.profile.id });

  socialForm.post(url, { preserveScroll: true });
}

function savePrivacy() {
  privacyForm
    .transform((data) => {
      // Sunucu $request->has(...) ile bakiyor: kapali kutular HIC gonderilmemeli.
      const payload = props.isSelf ? {} : { user_id: props.profile.id };

      for (const field of PRIVACY_FIELDS) {
        if (data[field]) {
          payload[field] = 1;
        }
      }

      return payload;
    })
    .post(route('admin.profile.privacy'), { preserveScroll: true });
}

function savePassword() {
  const url = props.isSelf
    ? route('admin.profile.password')
    : route('admin.user.password', { user_id: props.profile.id });

  passwordForm.post(url, {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  });
}

function saveEmail() {
  const url = props.isSelf
    ? route('admin.profile.email')
    : route('admin.user.email', { user_id: props.profile.id });

  emailForm.post(url, { preserveScroll: true });
}

/* ---- İki adımlı doğrulama (2FA) ---- */
const enable2faForm = useForm({});
const disable2faForm = useForm({});
const otpCode = ref('');
const otpBusy = ref(false);
const otpError = ref('');

function enableTwoFactor() {
  // Fortify'ın TwoFactorEnabledResponse'u: Inertia isteğinde back() döner,
  // bu yüzden useForm().post() ile normal bir form eylemi gibi çağrılır.
  enable2faForm.post(route('two-factor.enable'), { preserveScroll: true });
}

async function confirmTwoFactor() {
  if (otpBusy.value || !otpCode.value) {
    return;
  }

  otpBusy.value = true;
  otpError.value = '';

  try {
    // two-factor.confirm HER ZAMAN çıplak JSON döner (bkz.
    // TwoFactorAuthController::confirm) — router/useForm ile çağrılırsa
    // Inertia gecersiz-yanit modali acar, bu yuzden axios kalir.
    const { data } = await axios.post(route('two-factor.confirm'), { code: otpCode.value });

    if (data.status !== 'success') {
      otpError.value = data.message || __('user.two_fa.invalid_code');

      return;
    }

    otpCode.value = '';
    pushToast(data.message, 'success');
    router.reload({ only: ['twoFactor'] });
  } catch (error) {
    otpError.value = error.response?.data?.message || __('general.server_error');
  } finally {
    otpBusy.value = false;
  }
}

function disableTwoFactor() {
  disable2faForm.delete(route('two-factor.disable'), { preserveScroll: true });
}

/* ---- WebAuthn (passkey) ---- */

/**
 * Sunucudan gelen hatayi OKUNABILIR bir metne cevirir.
 *
 * Posts/Edit.vue'deki ayni yardimciyi yansitir: tek bir `general.error`
 * metnine dusuldugunde kullanici 403 (yetki), 419 (oturum) ve 500 (sunucu)
 * arasindaki farki goremiyordu. HTML hata sayfalari toast'a basilmaz, yerine
 * durum kodu gosterilir.
 */
function serverMessage(error) {
  const response = error?.response;
  const data = response?.data;

  if (data?.message) {
    return data.message;
  }

  const errors = data?.errors ? Object.values(data.errors).flat() : [];

  if (errors.length) {
    return String(errors[0]);
  }

  if (typeof data === 'string' && data.trim() !== '' && !data.trim().startsWith('<')) {
    return data.trim().slice(0, 300);
  }

  if (response?.status) {
    return `${__('general.error')} (HTTP ${response.status})`;
  }

  return error?.message || __('general.error');
}

/**
 * Passkey listesi.
 *
 * ESKI DAVRANIS (hata): liste YALNIZCA yenile dugmesine basildiginda, kayittan
 * sonra ve silmeden sonra cekiliyordu. Panelde hicbir `onMounted` ya da izleyici
 * yoktu, dolayisiyla passkey ile giris yapmis bir kullanici sekmeyi actiginda
 * `credentials` hala bos dizi oluyor ve ekranda "Kayit bulunamadi" yaziyordu.
 *
 * Artik sekme GORUNUR OLDUGUNDA cekiliyor. Sayfa acilisinda DEGIL: passkey,
 * guvenlik sekmesinin bir alt sekmesi; her profil ziyaretinde istek atmak bosa
 * is olurdu.
 */
const credentialsLoading = ref(false);
const credentialsLoaded = ref(false);

async function loadCredentials(force = false) {
  if (credentialsLoading.value || (credentialsLoaded.value && !force)) {
    return;
  }

  const url = props.isSelf
    ? route('user.security.webauthn')
    : route('admin.user.webauthn', { user_id: props.profile.id });

  credentialsLoading.value = true;

  try {
    const { data } = await axios.post(url);

    credentials.value = Array.isArray(data) ? data : data?.credentials || [];
    credentialsLoaded.value = true;
  } catch (error) {
    /*
     * Eskiden burada hic yakalama yoktu: 403/500 firlatiyor, panel sessizce
     * bos kaliyor ve "kayit yok" ile ayirt edilemiyordu. `credentialsLoaded`
     * BILEREK false birakilir ki sekmeye tekrar girildiginde yeniden denensin.
     */
    pushToast(serverMessage(error), 'error');
  } finally {
    credentialsLoading.value = false;
  }
}

/*
 * Alt sekme passkey'e gecince bir kez cek. `immediate` yok: `securityTab`
 * daima 'password' ile basliyor, ilk tetikleme bosa istek olurdu.
 */
watch(securityTab, (value) => {
  if (value === 'passkey') {
    loadCredentials();
  }
});

async function registerPasskey() {
  const response = await Webpass.attest(
    { path: route('webauthn.register.options') },
    route('webauthn.register'),
  );

  if (response.success) {
    pushToast(__('webauthn.verification_success'), 'success');
    await loadCredentials(true);

    return;
  }

  pushToast(response.error?.message || __('webauthn.verification_failed'), 'error');
}

async function deleteCredential(credential) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  const url = props.isSelf
    ? route('user.security.webauthn.delete')
    : route('admin.user.webauthn.delete', { user_id: props.profile.id });

  try {
    await axios.post(url, { id: credential.id });
  } catch (error) {
    pushToast(serverMessage(error), 'error');
  }

  // Silme basarisiz olsa da yeniden cekilir: liste her durumda GERCEGI gostersin.
  credentialsLoaded.value = false;
  await loadCredentials(true);
}

/* ---- Oturumlar ---- */
async function killSession(session) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('user.session.logout'), { session_id: session.id }, { preserveScroll: true });
}

function killAllSessions() {
  router.post(route('user.session.logout-all'), { user_id: props.profile.id }, { preserveScroll: true });
}

/* ---- Profil görseli ---- */
function uploadAvatar(event) {
  const file = event.target.files?.[0];

  if (!file) {
    return;
  }

  router.post(
    route('admin.user.profile-image'),
    { profile_image: file, user_id: props.profile.id },
    { forceFormData: true, preserveScroll: true },
  );
}

function deleteAvatar() {
  router.post(
    route('admin.user.profile-image-delete'),
    { user_id: props.profile.id },
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="isSelf ? __('user.profile') : profile.nickname" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="p-card flex flex-wrap items-center gap-4 p-4">
      <img
        v-if="profile.avatar"
        :src="profile.avatar"
        :alt="profile.nickname"
        class="h-16 w-16 rounded-full border border-p-line"
      />
      <div class="min-w-0 flex-1">
        <div class="font-display text-[16px] font-extrabold">{{ profile.nickname }}</div>
        <div class="text-[12px] text-p-ink3">{{ profile.email }}</div>
        <span class="p-chip mt-1.5 inline-block capitalize">{{ profile.role }}</span>
      </div>
      <div class="flex gap-2">
        <label class="p-btn cursor-pointer">
          <i class="fa-solid fa-image text-[11px]"></i>
          {{ __('profile.change_image_label') }}
          <input type="file" accept="image/*" class="hidden" @change="uploadAvatar" />
        </label>
        <button class="p-btn !text-p-danger" @click="deleteAvatar">
          <i class="fa-solid fa-trash text-[11px]"></i>
        </button>
      </div>
    </div>

    <Tabs v-model="tab" :tabs="tabs" />

    <!-- Hakkımda -->
    <div v-if="tab === 'about'" class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField v-model="aboutForm.name" :label="__('user.name')" :error="aboutForm.errors.name" />
        <FormField
          v-model="aboutForm.surname"
          :label="__('user.surname')"
          :error="aboutForm.errors.surname"
        />
        <FormField
          v-model="aboutForm.nickname"
          :label="__('user.nickname')"
          :error="aboutForm.errors.nickname"
        />
        <FormField v-model="aboutForm.location" :label="__('profile.location')" />
        <FormField v-model="aboutForm.job_title" :label="__('profile.job_title')" />
        <FormField v-model="aboutForm.education" :label="__('profile.education')" />
        <FormField v-model="aboutForm.skills" :label="__('profile.skills')" />
        <FormField
          v-if="!isSelf && assignableRoles.length"
          v-model="aboutForm.role"
          type="select"
          :label="__('user.role')"
          :error="aboutForm.errors.role"
          :options="assignableRoles.map((role) => ({ value: role, label: role }))"
        />
        <FormField v-model="aboutForm.about" type="textarea" :label="__('profile.about_text')" full />
      </div>

      <div class="mt-4 flex justify-end">
        <button class="p-btn-primary" :disabled="aboutForm.processing" @click="saveAbout">
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Sosyal ağlar -->
    <div v-else-if="tab === 'social'" class="p-card p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField
          v-for="field in SOCIAL_FIELDS"
          :key="field"
          v-model="socialForm[field]"
          :label="__(field === 'website' ? 'social.website' : `social.${field}_username`)"
          :error="socialForm.errors[field]"
        />
      </div>

      <div class="mt-4 flex justify-end">
        <button class="p-btn-primary" :disabled="socialForm.processing" @click="saveSocial">
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Güvenlik -->
    <div v-else-if="tab === 'security'" class="flex flex-col gap-3.5">
      <Tabs v-model="securityTab" :tabs="securityTabs" />

      <div v-if="securityTab === 'password'" class="p-card p-4">
        <div class="grid max-w-md gap-3">
          <FormField
            v-if="isSelf"
            v-model="passwordForm.old_password"
            type="password"
            :label="__('profile.old_password.text')"
            :placeholder="__('profile.old_password.placeholder')"
            :error="passwordForm.errors.old_password"
          />
          <FormField
            v-model="passwordForm.password"
            type="password"
            :label="__('user.password')"
            :error="passwordForm.errors.password"
          />
          <FormField
            v-model="passwordForm.password_confirmation"
            type="password"
            :label="__('user.password_confirmation')"
            :error="passwordForm.errors.password_confirmation"
          />
          <button class="p-btn-primary" :disabled="passwordForm.processing" @click="savePassword">
            {{ __('general.save') }}
          </button>
        </div>
      </div>

      <div v-else-if="securityTab === 'email'" class="p-card p-4">
        <div class="grid max-w-md gap-3">
          <FormField
            v-model="emailForm.email"
            type="email"
            :label="__('user.email')"
            :error="emailForm.errors.email"
          />
          <button class="p-btn-primary" :disabled="emailForm.processing" @click="saveEmail">
            {{ __('general.save') }}
          </button>
        </div>
      </div>

      <div v-else-if="securityTab === 'two-factor' && twoFactor" class="p-card p-4">
        <!-- Etkin ve onaylanmis -->
        <div v-if="twoFactor.confirmed" class="flex flex-col gap-3">
          <div class="text-[12.5px] font-semibold text-p-ok">
            <i class="fa-solid fa-circle-check"></i> {{ __('profile.two_fa_enabled_message') }}
          </div>

          <div>
            <div class="mb-1.5 text-[12.5px] font-semibold">{{ __('profile.recovery_codes') }}</div>
            <div class="mb-2 text-[11.5px] text-p-ink3">{{ __('profile.recovery_codes_hint') }}</div>
            <div class="grid gap-1.5 sm:grid-cols-2">
              <div
                v-for="code in twoFactor.recoveryCodes"
                :key="code"
                class="rounded-[9px] bg-p-panel2 px-2.5 py-1.5 text-center font-mono text-[12px]"
              >
                {{ code }}
              </div>
            </div>
          </div>

          <div>
            <button
              class="p-btn !text-p-danger"
              :disabled="disable2faForm.processing"
              @click="disableTwoFactor"
            >
              <i class="fa-solid fa-shield-halved text-[11px]"></i> {{ __('profile.deactivate_2fa') }}
            </button>
          </div>
        </div>

        <!-- Etkin ama onay bekliyor: QR + kod dogrulama -->
        <div v-else-if="twoFactor.pending" class="grid gap-4 sm:grid-cols-2">
          <div class="flex flex-col items-center gap-2">
            <img
              v-if="twoFactor.qrCodeSvg"
              :src="twoFactor.qrCodeSvg"
              :alt="__('profile.two_fa_tab')"
              class="h-40 w-40 rounded-lg border border-p-line bg-white p-2"
            />
            <div class="text-center text-[11px] text-p-ink3">{{ __('profile.two_fa_scan_qr') }}</div>
            <div v-if="twoFactor.secretKey" class="text-center text-[11px] text-p-ink3">
              {{ __('profile.two_fa_secret_key') }}:
              <span class="break-all font-mono text-p-ink2">{{ twoFactor.secretKey }}</span>
            </div>
          </div>

          <div class="grid max-w-xs gap-3">
            <FormField v-model="otpCode" :label="__('profile.two_fa_otp_code')" :error="otpError" />
            <button class="p-btn-primary" :disabled="otpBusy" @click="confirmTwoFactor">
              {{ __('profile.validate_2fa') }}
            </button>
          </div>
        </div>

        <!-- Hic etkin degil -->
        <div v-else>
          <button
            class="p-btn-primary"
            :disabled="enable2faForm.processing"
            @click="enableTwoFactor"
          >
            <i class="fa-solid fa-shield-halved text-[11px]"></i> {{ __('profile.active_2fa') }}
          </button>
        </div>
      </div>

      <div v-else-if="securityTab === 'passkey'" class="p-card p-4">
        <div class="mb-3 flex items-center gap-2">
          <div class="flex-1 text-[12.5px] text-p-ink2">{{ __('webauthn.register_device') }}</div>
          <button class="p-btn" :disabled="credentialsLoading" @click="loadCredentials(true)">
            <i
              :class="credentialsLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'"
              class="text-[11px]"
            ></i>
          </button>
          <button v-if="isSelf" class="p-btn-primary" @click="registerPasskey">
            <i class="fa-solid fa-key text-xs"></i> {{ __('webauthn.register_device') }}
          </button>
        </div>

        <div class="divide-y divide-p-line2">
          <div
            v-for="credential in credentials"
            :key="credential.id"
            class="flex items-center gap-3 py-2.5"
          >
            <i class="fa-solid fa-key text-[12px] text-p-ink3"></i>
            <span class="flex-1 truncate text-[12.5px]">{{ credential.device_name }}</span>
            <button
              class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
              @click="deleteCredential(credential)"
            >
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
          <!--
            Yukleniyor ile "kayit yok" AYRI: eskiden ikisi de bos panel olarak
            goruluyordu ve kullanici passkey'i varken "Kayit bulunamadi" okuyordu.
          -->
          <div
            v-if="credentialsLoading"
            class="flex items-center justify-center gap-2 py-6 text-[12px] text-p-ink3"
          >
            <i class="fa-solid fa-spinner fa-spin text-[11px]"></i>
            {{ __('general.loading') }}
          </div>
          <div
            v-else-if="!credentials.length"
            class="py-6 text-center text-[12px] text-p-ink3"
          >
            {{ __('general.no_records') }}
          </div>
        </div>
      </div>
    </div>

    <!-- Gizlilik -->
    <div v-else-if="tab === 'privacy'" class="p-card p-4">
      <div class="grid gap-2 sm:grid-cols-2">
        <label
          v-for="field in PRIVACY_FIELDS"
          :key="field"
          class="flex items-center gap-2.5 rounded-[9px] px-2 py-1.5 text-[12.5px] hover:bg-p-panel2"
        >
          <input v-model="privacyForm[field]" type="checkbox" />
          {{ __(`privacy.${field}`) }}
        </label>
      </div>

      <div class="mt-4 flex justify-end">
        <button class="p-btn-primary" :disabled="privacyForm.processing" @click="savePrivacy">
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Bildirimler -->
    <div v-else-if="tab === 'notifications'" class="p-card p-4">
      <div class="mb-3 text-[12px] leading-relaxed text-p-ink3">
        {{ __('notifications.preferences_intro') }}
      </div>

      <!--
        Bu tarayicidaki push durumu. Olay tercihleri SUNUCUDA saklanir ve tum
        cihazlar icin gecerlidir; abonelik ise CIHAZ BASINA. Ikisi ayri satirda
        duruyor ki "acik isaretledim ama bildirim gelmiyor" karisikligi olmasin.
      -->
      <div
        v-if="$page.props.push?.enabled"
        class="mb-4 flex flex-wrap items-center gap-3 rounded-[11px] border border-p-line bg-p-panel2 px-3.5 py-3"
      >
        <i class="fa-solid fa-bell text-[13px] text-p-ink3"></i>
        <div class="min-w-[180px] flex-1">
          <div class="text-[12.5px] font-semibold text-p-ink">
            {{ __('notifications.push_device_title') }}
          </div>
          <div
            class="mt-0.5 text-[11.5px] leading-relaxed"
            :class="pushState === 'blocked' ? 'text-p-warn' : 'text-p-ink3'"
          >
            <template v-if="pushState === 'unsupported'">
              {{ __('notifications.push_unsupported') }}
            </template>
            <template v-else-if="pushState === 'blocked'">
              {{ __('notifications.push_blocked') }} {{ __('notifications.push_blocked_hint') }}
            </template>
            <template v-else-if="pushState === 'on'">{{ __('notifications.push_device_on') }}</template>
            <template v-else>{{ __('notifications.push_device_off') }}</template>
          </div>
        </div>

        <button
          v-if="pushState === 'on' || pushState === 'off'"
          :class="pushState === 'on' ? 'p-btn' : 'p-btn-primary'"
          :disabled="pushBusy"
          @click="togglePush"
        >
          <i
            :class="pushBusy ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-bell'"
            class="text-[11px]"
          ></i>
          {{ pushState === 'on' ? __('notifications.push_disable') : __('notifications.push_enable') }}
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-[12.5px]">
          <thead>
            <tr class="text-left text-[11.5px] uppercase tracking-wide text-p-ink3">
              <th class="border-b border-p-line2 px-2 py-2 font-semibold">
                {{ __('notifications.event') }}
              </th>
              <th class="w-28 border-b border-p-line2 px-2 py-2 text-center font-semibold">
                {{ __('notifications.channel_database') }}
              </th>
              <th class="w-28 border-b border-p-line2 px-2 py-2 text-center font-semibold">
                {{ __('notifications.channel_push') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="event in notificationEvents" :key="event.key" class="hover:bg-p-panel2">
              <td class="border-b border-p-line2 px-2 py-2.5">{{ event.label }}</td>
              <td class="border-b border-p-line2 px-2 py-2.5 text-center">
                <input
                  v-model="notificationForm.preferences[event.key].database"
                  type="checkbox"
                  :aria-label="`${event.label} — ${__('notifications.channel_database')}`"
                />
              </td>
              <td class="border-b border-p-line2 px-2 py-2.5 text-center">
                <input
                  v-model="notificationForm.preferences[event.key].push"
                  type="checkbox"
                  :aria-label="`${event.label} — ${__('notifications.channel_push')}`"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-end">
        <button
          class="p-btn-primary"
          :disabled="notificationForm.processing"
          @click="saveNotifications"
        >
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <!-- Oturumlar -->
    <div v-else class="flex flex-col gap-3.5">
      <div class="flex justify-end">
        <button class="p-btn !text-p-danger" @click="killAllSessions">
          <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
          {{ __('user.logout_all_devices') }}
        </button>
      </div>

      <div class="p-card divide-y divide-p-line2">
        <div
          v-for="session in sessions.data"
          :key="session.id"
          class="flex flex-wrap items-center gap-3 px-4 py-3"
        >
          <div class="min-w-0 flex-1">
            <div class="text-[12.5px] font-semibold">{{ session.ip }}</div>
            <div class="mt-0.5 break-all text-[11px] text-p-ink3">{{ session.user_agent }}</div>
          </div>
          <div class="whitespace-nowrap text-[11px] tabular-nums text-p-ink3">
            {{ formatDateTime(session.lastActivity || session.createdAt) }}
          </div>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            @click="killSession(session)"
          >
            <i class="fa-solid fa-right-from-bracket"></i>
          </button>
        </div>

        <div v-if="!sessions.data.length" class="px-4 py-10 text-center text-p-ink3">
          {{ __('general.no_records') }}
        </div>
      </div>

      <Pagination :links="sessions.links" :meta="sessions" />
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
