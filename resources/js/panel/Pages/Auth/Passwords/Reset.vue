<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { __ } from '../../../composables/useLang';
import { pushToast } from '../../../composables/useToast';
import AuthShell from '../../../components/AuthShell.vue';
import HoneypotFields from '../../../components/HoneypotFields.vue';
import Turnstile from '../../../components/Turnstile.vue';

/*
 * panel/auth/passwords/reset-form.blade.php karşılığı.
 *
 * `password.update` `{status, message}` JSON döndürüyor (R1 → XHR kalır).
 * `token` route parametresinden, `user` query'den geliyordu; ikisi de artık
 * controller'dan prop olarak gelir.
 */
const props = defineProps({
  token: { type: String, required: true },
  user: { type: String, default: '' },
  honeypot: { type: Object, required: true },
  routes: { type: Object, required: true },
});

defineOptions({ layout: null });

const password = ref('');
const passwordConfirmation = ref('');
const busy = ref(false);
const turnstile = ref(null);

async function submit() {
  if (busy.value) {
    return;
  }

  busy.value = true;

  const body = {
    token: props.token,
    user: props.user,
    password: password.value,
    password_confirmation: passwordConfirmation.value,
    _token: document.head.querySelector('meta[name="csrf-token"]')?.content || '',
  };

  if (props.honeypot.enabled) {
    body[props.honeypot.nameFieldName] = '';
    body[props.honeypot.validFromFieldName] = props.honeypot.encryptedValidFrom;
  }

  try {
    const { data } = await axios.post(props.routes.passwordUpdate, body);

    pushToast(data.message, data.status ? 'success' : 'error');

    if (data.status) {
      setTimeout(() => (window.location.href = props.routes.dashboard), 1500);
    }
  } catch (error) {
    password.value = '';
    passwordConfirmation.value = '';
    pushToast(error.response?.data?.message || __('general.server_error'), 'error');
  } finally {
    turnstile.value?.reset();
    busy.value = false;
  }
}
</script>

<template>
  <Head :title="__('user.change_password')" />

  <AuthShell :title="__('user.change_password')">
    <form @submit.prevent="submit">
      <HoneypotFields :honeypot="honeypot" />

      <label class="p-label" for="password">{{ __('user.password') }}</label>
      <input
        id="password"
        v-model="password"
        type="password"
        autocomplete="new-password"
        required
        class="p-input"
      />

      <label class="p-label mt-3" for="password_confirmation">
        {{ __('user.password_confirmation') }}
      </label>
      <input
        id="password_confirmation"
        v-model="passwordConfirmation"
        type="password"
        autocomplete="new-password"
        required
        class="p-input"
      />

      <div class="mt-3">
        <Turnstile ref="turnstile" />
      </div>

      <button type="submit" class="p-btn-primary mt-3 w-full justify-center" :disabled="busy">
        <i class="fa-solid fa-key text-xs"></i> {{ __('user.change_password') }}
      </button>
    </form>
  </AuthShell>
</template>
