<script setup>
import { useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';

/*
 * panel/user/create.blade.php karşılığı.
 *
 * Rol listesi SUNUCUDAN gelir: `canAssignRole()` hiyerarşisi atanamayacak
 * rolleri zaten eler ve sunucu tarafı kontrolü yerinde duruyor — arayüz
 * yalnızca aynı kuralı görünür kılar.
 */
defineProps({
  assignableRoles: { type: Array, default: () => [] },
});

usePageHeader(__('user.new_user'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('user.users'), route: 'admin.users' },
  { label: __('user.new_user') },
]);

const form = useForm({
  name: '',
  surname: '',
  nickname: '',
  username: '',
  email: '',
  role: 'user',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post(route('admin.user.create'), { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('user.new_user')" />

  <div class="p-[22px]">
    <div class="p-card mx-auto max-w-2xl p-5">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField v-model="form.name" :label="__('user.name')" :error="form.errors.name" />
        <FormField v-model="form.surname" :label="__('user.surname')" :error="form.errors.surname" />
        <FormField
          v-model="form.nickname"
          :label="__('user.nickname')"
          :error="form.errors.nickname"
        />
        <FormField
          v-model="form.username"
          :label="__('user.username')"
          :error="form.errors.username"
        />
        <FormField
          v-model="form.email"
          type="email"
          :label="__('user.email')"
          :error="form.errors.email"
        />
        <FormField
          v-model="form.role"
          type="select"
          :label="__('user.role')"
          :error="form.errors.role"
          :options="assignableRoles.map((role) => ({ value: role, label: role }))"
        />
        <FormField
          v-model="form.password"
          type="password"
          :label="__('user.password')"
          :error="form.errors.password"
        />
        <FormField
          v-model="form.password_confirmation"
          type="password"
          :label="__('user.password_confirmation')"
          :error="form.errors.password_confirmation"
        />
      </div>

      <div class="mt-4 flex justify-end gap-2">
        <Link :href="route('admin.users')" class="p-btn no-underline">
          {{ __('general.cancel') }}
        </Link>
        <button class="p-btn-primary" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-user-plus text-xs"></i>
          {{ __('general.create') }}
        </button>
      </div>
    </div>
  </div>
</template>
