<script setup>
import { useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';

/*
 * panel/personal_notes/encryption-form.blade.php karşılığı.
 *
 * Notlar kullanıcının KENDİ anahtarıyla şifreleniyor; anahtar bir cookie'de
 * tutuluyor ve sunucuda hiç saklanmıyor. Bu ekran, anahtar yokken ya da
 * yanlışken not ekranlarının yerine geçer — paylaşılan prop değil ayrı sayfa,
 * çünkü "notlar açık mı" bilgisi her sayfanın yüküne sızmamalı.
 */
defineProps({
  intended: { type: String, default: null },
  invalid: { type: Boolean, default: false },
});

usePageHeader(__('notes.notes'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notes.notes') },
]);

const REMEMBER = [1, 30, 90, 180, 365];

const form = useForm({
  encryption_key: '',
  remember_time: 30,
});

function submit() {
  form.post(route('admin.notes.encryption'), {
    preserveScroll: true,
    onSuccess: () => form.reset('encryption_key'),
  });
}
</script>

<template>
  <Head :title="__('notes.notes')" />

  <div class="grid flex-1 place-items-center p-[22px]">
    <div class="p-card w-full max-w-md p-6">
      <div class="text-center">
        <i class="fa-solid fa-file-shield text-2xl text-p-accent"></i>
        <div class="mt-3 font-display text-[15px] font-bold">
          {{ __('notes.encryption_key') }}
        </div>
        <p class="mt-1.5 text-[12.5px] leading-relaxed text-p-ink2">
          {{ __('notes.define_encryption_key') }}
        </p>
      </div>

      <div
        v-if="invalid"
        class="mt-4 rounded-xl border border-p-danger/40 bg-p-danger/10 px-3 py-2 text-[12px] text-p-danger"
      >
        {{ __('notes.encryption_key_invalid') }}
      </div>

      <div class="mt-4 flex flex-col gap-3">
        <FormField
          v-model="form.encryption_key"
          type="password"
          :label="__('notes.encryption_key')"
          :error="form.errors.encryption_key"
        />
        <FormField
          v-model.number="form.remember_time"
          type="select"
          :label="__('notes.remember_me')"
          :error="form.errors.remember_time"
          :options="REMEMBER.map((days) => ({ value: days, label: `${days}` }))"
        />

        <button class="p-btn-primary justify-center" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-unlock text-xs"></i>
          {{ __('general.save') }}
        </button>
      </div>
    </div>
  </div>
</template>
