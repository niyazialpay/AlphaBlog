<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { __ } from '../../../composables/useLang';
import { pushToast } from '../../../composables/useToast';
import AuthShell from '../../../components/AuthShell.vue';
import HoneypotFields from '../../../components/HoneypotFields.vue';
import Turnstile from '../../../components/Turnstile.vue';

/*
 * panel/auth/passwords/reset.blade.php ("şifremi unuttum") karşılığı.
 *
 * `forgot-password` ucu `{status, message}` JSON döndürüyor ve Turnstile
 * jetonu her denemede sıfırlanmalı; bu yüzden XHR kalır (R1).
 */
const props = defineProps({
  honeypot: { type: Object, required: true },
  routes: { type: Object, required: true },
});

defineOptions({ layout: null });

const login = ref('');
const busy = ref(false);
const turnstile = ref(null);

async function submit() {
  if (busy.value || !login.value) {
    return;
  }

  busy.value = true;

  const body = {
    login: login.value,
    _token: document.head.querySelector('meta[name="csrf-token"]')?.content || '',
  };

  if (props.honeypot.enabled) {
    body[props.honeypot.nameFieldName] = '';
    body[props.honeypot.validFromFieldName] = props.honeypot.encryptedValidFrom;
  }

  try {
    const { data } = await axios.post(props.routes.forgotPassword, body);

    pushToast(data.message, data.status ? 'success' : 'error');
  } catch (error) {
    pushToast(error.response?.data?.message || __('general.server_error'), 'error');
  } finally {
    turnstile.value?.reset();
    busy.value = false;
  }
}
</script>

<template>
  <Head :title="__('user.forgot_password')" />

  <AuthShell :title="__('user.forgot_password')">
    <form @submit.prevent="submit">
      <HoneypotFields :honeypot="honeypot" />

      <label class="p-label" for="login">{{ __('user.username_or_email') }}</label>
      <input id="login" v-model="login" name="login" autofocus required class="p-input" />

      <div class="mt-3">
        <Turnstile ref="turnstile" />
      </div>

      <button type="submit" class="p-btn-primary mt-3 w-full justify-center" :disabled="busy">
        <i class="fa-solid fa-paper-plane text-xs"></i> {{ __('general.check') }}
      </button>
    </form>

    <template #footer>
      <a :href="routes.login" class="text-[12px] text-p-ink3 hover:text-p-ink">
        {{ __('user.login') }}
      </a>
    </template>
  </AuthShell>
</template>
