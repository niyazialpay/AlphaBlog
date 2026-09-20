<script setup>
import { usePage } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import AuthShell from '../../components/AuthShell.vue';

/*
 * panel/auth/verify.blade.php karşılığı.
 *
 * Fortify'ın `verifyEmailView` kancasından render edilir. `verification.send`
 * gerçek bir form post'u: Fortify yönlendirip `status` flash'ı bırakıyor,
 * dolayısıyla Inertia-doğru.
 */
defineOptions({ layout: null });

defineProps({
  routes: { type: Object, required: true },
});

const page = usePage();

function csrf() {
  return document.head.querySelector('meta[name="csrf-token"]')?.content || '';
}
</script>

<template>
  <Head :title="__('auth.verify_email.subject')" />

  <AuthShell :title="__('auth.verify_email.subject')">
    <div
      v-if="page.props.flash?.status"
      class="mb-3 rounded-xl border border-p-ok/40 bg-p-ok/10 px-3 py-2 text-[12px] text-p-ink2"
    >
      {{ __('auth.verify_email.fresh_resend') }}
    </div>

    <p class="text-[12.5px] leading-relaxed text-p-ink2">
      {{ __('auth.verify_email.before_proceeding') }}
      {{ __('auth.verify_email.you_didnt_receive') }},
    </p>

    <form :action="routes.resend" method="post" class="mt-3">
      <input type="hidden" name="_token" :value="csrf()" />
      <button type="submit" class="p-btn-primary w-full justify-center">
        <i class="fa-solid fa-rotate text-xs"></i> {{ __('auth.verify_email.resend') }}
      </button>
    </form>
  </AuthShell>
</template>
