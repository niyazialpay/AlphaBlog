<script setup>
import { nextTick, ref } from 'vue';
import axios from 'axios';
import Webpass from '@laragear/webpass';
import { __ } from '../../composables/useLang';
import { pushToast } from '../../composables/useToast';
import AuthShell from '../../components/AuthShell.vue';
import HoneypotFields from '../../components/HoneypotFields.vue';
import Turnstile from '../../components/Turnstile.vue';

/*
 * panel/auth/login.blade.php karşılığı.
 *
 * İki aşamalı giriş XHR KALIR (R1). Inertia form post'una çevrilemez:
 *   - `login.first_step` yanıtı WebAuthn dalına karar veriyor, yönlendirmiyor;
 *   - `ProtectAgainstSpam` honeypot alanlarını gövdede bekliyor;
 *   - hata sonrası `turnstile.reset()` için sayfada kalmak gerekiyor
 *     (jeton tek kullanımlık).
 *
 * Webpass artık npm bağımlılığından (zaten kurulu) geliyor; CDN script tag'i
 * düşüyor. Başarıda tam sayfa yüklemesi: oturum yenilendiği için paylaşılan
 * prop'lar da tazelenmeli.
 */
const props = defineProps({
  honeypot: { type: Object, required: true },
  routes: { type: Object, required: true },
});

defineOptions({ layout: null });

const step = ref('first');
const username = ref('');
const password = ref('');
const remember = ref(false);
const busy = ref(false);
const turnstile = ref(null);
const passwordInput = ref(null);

function csrf() {
  return document.head.querySelector('meta[name="csrf-token"]')?.content || '';
}

function payload(extra = {}) {
  const body = { username: username.value, _token: csrf(), ...extra };

  if (props.honeypot.enabled) {
    body[props.honeypot.nameFieldName] = '';
    body[props.honeypot.validFromFieldName] = props.honeypot.encryptedValidFrom;
  }

  return body;
}

function enter() {
  window.location.href = props.routes.dashboard;
}

async function loginWithDevice() {
  const response = await Webpass.assert(
    { path: props.routes.webauthnOptions, body: { username: username.value } },
    props.routes.webauthnLogin,
  );

  if (response.success) {
    pushToast(__('webauthn.verification_success'), 'success');
    enter();

    return;
  }

  pushToast(response.error?.message || __('webauthn.verification_failed'), 'error');
}

async function firstStep() {
  if (busy.value || !username.value) {
    return;
  }

  busy.value = true;

  try {
    const { data } = await axios.post(props.routes.firstStep, payload());

    if (data.status && data.webauthn) {
      await loginWithDevice();
    } else {
      step.value = 'password';
      await nextTick();
      passwordInput.value?.focus();
    }
  } catch (error) {
    username.value = '';
    pushToast(
      error.response?.data?.message || __('user.login_request.error'),
      'error',
    );
  } finally {
    turnstile.value?.reset();
    busy.value = false;
  }
}

async function submit() {
  if (busy.value) {
    return;
  }

  busy.value = true;

  try {
    const { data } = await axios.post(
      props.routes.login,
      payload({
        password: password.value,
        remember: remember.value ? 1 : 0,
        'cf-turnstile-response': turnstile.value?.getResponse(),
      }),
    );

    if (!data.status) {
      pushToast(__('user.login_request.error'), 'error');

      return;
    }

    if (data.webauthn) {
      await loginWithDevice();

      return;
    }

    pushToast(__('user.login_request.success'), 'success');
    enter();
  } catch (error) {
    password.value = '';
    pushToast(error.response?.data?.message || __('user.login_request.error'), 'error');
  } finally {
    turnstile.value?.reset();
    busy.value = false;
  }
}
</script>

<template>
  <Head :title="__('user.login')" />

  <AuthShell>
    <form v-if="step === 'first'" @submit.prevent="firstStep">
      <HoneypotFields :honeypot="honeypot" />

      <label class="p-label" for="login-username">{{ __('user.username') }}</label>
      <input
        id="login-username"
        v-model="username"
        name="username"
        autocomplete="username"
        autofocus
        required
        class="p-input"
      />

      <button type="submit" class="p-btn-primary mt-3 w-full justify-center" :disabled="busy">
        <i class="fa-solid fa-right-to-bracket text-xs"></i> {{ __('user.login') }}
      </button>
    </form>

    <form v-else @submit.prevent="submit">
      <HoneypotFields :honeypot="honeypot" />

      <label class="p-label" for="login-username-2">{{ __('user.username') }}</label>
      <input
        id="login-username-2"
        v-model="username"
        name="username"
        autocomplete="username"
        class="p-input"
      />

      <label class="p-label mt-3" for="login-password">{{ __('user.password') }}</label>
      <input
        id="login-password"
        ref="passwordInput"
        v-model="password"
        name="password"
        type="password"
        autocomplete="current-password"
        class="p-input"
      />

      <div class="mt-3">
        <Turnstile ref="turnstile" />
      </div>

      <label class="mt-3 flex items-center gap-2 text-[12.5px]">
        <input v-model="remember" type="checkbox" name="remember" />
        {{ __('user.remember_me') }}
      </label>

      <button type="submit" class="p-btn-primary mt-3 w-full justify-center" :disabled="busy">
        <i class="fa-solid fa-right-to-bracket text-xs"></i> {{ __('user.login') }}
      </button>
    </form>

    <template #footer>
      <a
        v-if="step !== 'first'"
        :href="routes.forgotPassword"
        class="text-[12px] text-p-ink3 hover:text-p-ink"
      >
        {{ __('user.forgot_password') }}
      </a>
    </template>
  </AuthShell>
</template>
