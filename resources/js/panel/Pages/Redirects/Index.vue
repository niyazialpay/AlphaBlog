<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import Modal from '../../components/Modal.vue';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/redirects.blade.php karşılığı.
 *
 * Eski ekran SweetAlert + $.ajax kullanıyordu. Kaydetme artık Inertia form
 * post'u; sunucu R2 ile şimlendiği için jQuery çağıranlar bozulmadı.
 */
const props = defineProps({
  routes: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
});

usePageHeader(__('redirects.redirects'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('redirects.redirects') },
]);

const CODES = [301, 302, 303, 307, 308, 404, 410];

const search = ref(props.filters.search || '');
const open = ref(false);
const editing = ref(null);
const confirm = ref(null);

const form = useForm({ route_id: '', old_url: '', new_url: '', redirect_code: 301 });

let timer = null;
watch(search, (value) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(
      route('adminRoutes'),
      { search: value || undefined },
      { only: ['routes', 'filters'], preserveState: true, preserveScroll: true, replace: true },
    );
  }, 250);
});

function create() {
  editing.value = null;
  form.defaults({ route_id: '', old_url: '', new_url: '', redirect_code: 301 });
  form.reset();
  form.clearErrors();
  open.value = true;
}

function edit(row) {
  editing.value = row;
  // RouteRequest'in unique kuralı `route_id` gövde alanını okuyor (eski
  // redirects.blade.php'deki gizli input'un karşılığı) — route parametresi
  // yeterli değil, aksi halde kayıt kendi old_url'ini unique ihlali sayar.
  form.route_id = row.id;
  form.old_url = row.old_url;
  form.new_url = row.new_url;
  form.redirect_code = row.redirect_code;
  form.clearErrors();
  open.value = true;
}

function submit() {
  // route parametresi opsiyonel: yoksa yeni kayit, varsa guncelleme.
  const url = editing.value
    ? route('adminRouteSave', { route: editing.value.id })
    : route('adminRouteSave');

  form.post(url, {
    preserveScroll: true,
    onSuccess: () => {
      open.value = false;
    },
  });
}

async function destroy(row) {
  const ok = await confirm.value.ask({
    title: __('redirects.delete_confirmation_title'),
    body: __('redirects.delete_confirmation_message'),
  });

  if (!ok) {
    return;
  }

  router.post(
    route('adminRoutesDelete'),
    { route_id: row.id },
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('redirects.redirects')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[180px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>
      <div class="flex-1"></div>
      <button class="p-btn-primary" @click="create">
        <i class="fa-solid fa-plus text-[11px]"></i>
        {{ __('redirects.add_route') }}
      </button>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="px-3.5 py-2.5 font-semibold">{{ __('redirects.old_url') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('redirects.new_url') }}</th>
              <th class="w-[140px] px-3.5 py-2.5 font-semibold">
                {{ __('redirects.redirect_code') }}
              </th>
              <th class="w-[110px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in routes.data"
              :key="row.id"
              class="border-t border-p-line2 hover:bg-p-panel2"
            >
              <td class="break-all px-3.5 py-2.5">{{ row.old_url }}</td>
              <td class="break-all px-3.5 py-2.5 text-p-ink2">{{ row.new_url }}</td>
              <td class="px-3.5 py-2.5">
                <span class="p-chip tabular-nums">{{ row.redirect_code }}</span>
              </td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <button class="p-icon-btn" :title="__('general.edit')" @click="edit(row)">
                    <i class="fa-solid fa-pen"></i>
                  </button>
                  <button
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('general.delete')"
                    @click="destroy(row)"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!routes.data.length">
              <td colspan="4" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('redirects.no_data') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="routes.links" :meta="routes" :only="['routes', 'filters']" />

    <Modal
      v-model:open="open"
      :title="editing ? __('redirects.edit_route') : __('redirects.add_route')"
      icon="fa-solid fa-route"
      @confirm="submit"
    >
      <div class="grid gap-3">
        <FormField
          v-model="form.old_url"
          :label="__('redirects.old_url')"
          :error="form.errors.old_url"
          placeholder="eski/yol"
        />
        <FormField
          v-model="form.new_url"
          :label="__('redirects.new_url')"
          :error="form.errors.new_url"
          placeholder="/yeni/yol"
        />
        <FormField
          v-model="form.redirect_code"
          type="select"
          :label="__('redirects.redirect_code')"
          :error="form.errors.redirect_code"
          :options="CODES"
        />
      </div>
    </Modal>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
