<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Webpass from '@laragear/webpass';
import { __ } from '../../composables/useLang';
import { pushToast } from '../../composables/useToast';
import HoneypotFields from '../../components/HoneypotFields.vue';
import FlashToast from '../../components/FlashToast.vue';

/*
 * OTP / kilit ekranı — panel/auth/otp.blade.php karşılığı.
 *
 * VerifyOTP middleware'i tarafından render edilir (controller değil). Davranış
 * birebir korunur:
 *   - WebAuthn kayıtlıysa passkey doğrulaması öncelikli, değilse TOTP kodu.
 *   - two-factor.verify JSON döndürür ve login durum makinesinin parçası olduğu
 *     için JSON kalır (R1); Inertia form post'una çevrilmez.
 *   - Başarıda tam sayfa yüklemesiyle panele girilir: oturum yenilendiği için
 *     SPA durumu da tazelenmeli.
 */
const props = defineProps({
  webauthn: { type: Boolean, default: false },
  totp: { type: [String, Boolean, null], default: null },
  nickname: { type: String, default: '' },
  username: { type: String, default: '' },
  profileImage: { type: String, default: '' },
  honeypot: { type: Object, required: true },
});

defineOptions({ layout: null });

const csrf = document.head.querySelector('meta[name="csrf-token"]')?.content || '';
const code = ref('');
const busy = ref(false);
const form = ref(null);

async function submitOtp() {
  if (busy.value) {
    return;
  }

  busy.value = true;

  try {
    const { data } = await axios.post(route('two-factor.verify'), new FormData(form.value));

    if (data.status === 'success') {
      pushToast(data.message, 'success');
      window.location.href = route('admin.index');

      return;
    }

    pushToast(data.message, 'error');
  } catch {
    pushToast(__('general.server_error'), 'error');
  } finally {
    busy.value = false;
  }
}

async function loginWithDevice() {
  busy.value = true;

  const response = await Webpass.assert(
    { path: route('webauthn.login.options'), body: { username: props.username } },
    route('webauthn.login'),
  );

  busy.value = false;

  if (response.success) {
    pushToast(__('webauthn.verification_success'), 'success');
    window.location.reload();

    return;
  }

  pushToast(response.error?.message || __('webauthn.verification_failed'), 'error');
}
</script>

<template>
  <Head :title="__('user.lockscreen')" />

  <div class="grid min-h-screen place-items-center bg-p-bg px-4">
    <div class="w-full max-w-sm text-center">
      <img
        v-if="profileImage"
        :src="profileImage"
        :alt="nickname"
        width="96"
        height="96"
        class="mx-auto rounded-full border border-p-line"
      />

      <div class="mt-4 font-display text-lg font-extrabold">{{ nickname }}</div>
      <div class="mt-1 text-[12.5px] text-p-ink2">{{ __('user.lockscreen') }}</div>

      <div class="p-card mt-6 p-4">
        <form v-if="webauthn" @submit.prevent="loginWithDevice">
          <button type="submit" class="p-btn-primary w-full justify-center" :disabled="busy">
            <i class="fa-solid fa-usb-drive text-xs"></i>
            {{ __('webauthn.login_with_device') }}
          </button>
        </form>

        <form v-else-if="totp" ref="form" @submit.prevent="submitOtp">
          <HoneypotFields :honeypot="honeypot" />

          <label class="p-label text-left" for="otp-code">OTP</label>
          <div class="flex gap-2">
            <input
              id="otp-code"
              v-model="code"
              name="code"
              type="password"
              inputmode="numeric"
              autocomplete="one-time-code"
              autofocus
              class="p-input text-center tracking-[.3em]"
            />
            <button type="submit" class="p-btn-primary px-3" :disabled="busy || !code">
              <i class="fa-solid fa-arrow-right text-xs"></i>
            </button>
          </div>
        </form>
      </div>

      <form :action="route('admin.logout')" method="post" class="mt-4">
        <input type="hidden" name="_token" :value="csrf" />
        <button type="submit" class="text-[12px] text-p-ink3 hover:text-p-ink">
          {{ __('user.logout') }}
        </button>
      </form>
    </div>

    <!--
      AuthShell kullanmayan tek auth ekranı bu: toast kabı burada ayrıca
      mount edilmeli, yoksa OTP/WebAuthn hataları sessiz kalır.
    -->
    <FlashToast />
  </div>
</template>
