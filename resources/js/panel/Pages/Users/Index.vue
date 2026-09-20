<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/user/index.blade.php karşılığı.
 *
 * Gizli giriş (impersonation) DURUM DEĞİŞTİREN bir GET route'u; Inertia v2'de
 * <Link prefetch> hover'da tetikleyebilirdi. Bu yüzden `<Link>` değil düz bir
 * anchor + tam sayfa yüklemesi kullanılıyor.
 */
const props = defineProps({
  users: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  assignableRoles: { type: Array, default: () => [] },
});

usePageHeader(__('user.users'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('user.users') },
]);

const search = ref(props.filters.search || '');
const confirm = ref(null);

let timer = null;
watch(search, (value) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(
      route('admin.users'),
      { search: value || undefined },
      { only: ['users', 'filters'], preserveState: true, preserveScroll: true, replace: true },
    );
  }, 250);
});

async function destroy(user) {
  if (!(await confirm.value.ask({ body: __('general.you_wont_be_able_to_revert_this') }))) {
    return;
  }

  router.post(route('admin.user.delete'), { user_id: user.id }, { preserveScroll: true });
}

function impersonate(user) {
  /*
   * Oturum kimligi degisiyor: SPA durumu tasinmamali, tam sayfa yuklemesi.
   *
   * POST alias'i kullanilir. Eski uc GET; bir gezinme baglantisi olarak
   * render edilseydi Inertia v2 prefetch'i SADECE fare uzerine gelindiginde
   * kullanici taklidini baslatabilirdi. Gercek form gonderimi yapilir ki
   * yanitin yonlendirmesi tarayicida normal sekilde izlensin.
   */
  const form = document.createElement('form');
  form.method = 'post';
  form.action = route('admin.user.secret-login.post', { user_id: user.id });

  const token = document.createElement('input');
  token.type = 'hidden';
  token.name = '_token';
  token.value = document.head.querySelector('meta[name="csrf-token"]')?.content || '';

  form.appendChild(token);
  document.body.appendChild(form);
  form.submit();
}

const ROLE_STYLE = {
  owner: '!bg-p-accent !text-white',
  admin: '!bg-p-ok !text-white',
  editor: '!bg-p-cyan !text-white',
  author: '!bg-p-warn !text-white',
};
</script>

<template>
  <Head :title="__('user.users')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[200px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>

      <div class="flex-1"></div>

      <Link :href="route('admin.user.create')" class="p-btn-primary">
        <i class="fa-solid fa-user-plus text-xs"></i>
        {{ __('general.new') }}
      </Link>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="px-3.5 py-2.5 font-semibold">{{ __('user.user') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('user.email') }}</th>
              <th class="w-[110px] px-3.5 py-2.5 font-semibold">{{ __('user.role') }}</th>
              <th class="w-[150px] whitespace-nowrap px-3.5 py-2.5 font-semibold">
                {{ __('general.created_at') }}
              </th>
              <th class="w-[130px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="user in users.data"
              :key="user.id"
              class="border-t border-p-line2 hover:bg-p-panel2"
            >
              <td class="px-3.5 py-2.5">
                <div class="flex items-center gap-2.5">
                  <img
                    v-if="user.avatar"
                    :src="user.avatar"
                    :alt="user.nickname"
                    class="h-8 w-8 rounded-full"
                    loading="lazy"
                  />
                  <div class="min-w-0">
                    <div class="truncate font-semibold">{{ user.nickname }}</div>
                    <div class="truncate text-[11px] text-p-ink3">
                      {{ user.name }} {{ user.surname }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-3.5 py-2.5 text-p-ink2">{{ user.email }}</td>
              <td class="px-3.5 py-2.5">
                <span class="p-chip capitalize" :class="ROLE_STYLE[user.role]">{{ user.role }}</span>
              </td>
              <td class="whitespace-nowrap px-3.5 py-2.5 tabular-nums text-p-ink3">
                {{ formatDateTime(user.createdAt) }}
              </td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <Link
                    :href="route('admin.user.edit', { user_id: user.id })"
                    class="p-icon-btn"
                    :title="__('general.edit')"
                  >
                    <i class="fa-solid fa-pen"></i>
                  </Link>
                  <button
                    class="p-icon-btn"
                    :title="__('user.secret_login')"
                    @click="impersonate(user)"
                  >
                    <i class="fa-solid fa-user-secret"></i>
                  </button>
                  <button
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('general.delete')"
                    @click="destroy(user)"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.data.length">
              <td colspan="5" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('general.no_records') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="users.links" :meta="users" :only="['users', 'filters']" />

    <ConfirmDialog ref="confirm" />
  </div>
</template>
