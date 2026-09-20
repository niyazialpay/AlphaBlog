<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/*
 * panel/ip_filter/show.blade.php karşılığı.
 *
 * `IPFilterRequest` sözleşmesi birebir korunur:
 *   route_type in:select,manuel | code in:403,404 | is_active in:0,1
 *   ip_range ve routes required (satır satır metin ya da dizi).
 *
 * `routeList` Inertia::optional — ~400 kayıt her istekte gönderilmez, yalnız
 * "listeden seç" kipinde kısmi yeniden yükleme ile istenir.
 */
const props = defineProps({
  filter: { type: Object, default: null },
  routeList: { type: Array, default: () => [] },
});

const editing = computed(() => !!props.filter);
const title = computed(() => (editing.value ? props.filter.name : __('general.new')));

usePageHeader(title.value, [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('ip_filter.title'), route: 'admin.ip-filter' },
  { label: title.value },
]);

const confirm = ref(null);
const bulkIps = ref('');
const routeSearch = ref('');

const form = useForm({
  name: props.filter?.name || '',
  // Sunucu satır satır metni parse ediyor (explode PHP_EOL).
  ip_range: (props.filter?.ips || []).map((item) => item.ip).join('\n'),
  route_type: props.filter?.route_type || 'select',
  // 'select' kipinde dizi, 'manuel' kipinde metin — save() ikisini de kabul ediyor.
  routes: props.filter?.routes ? [...props.filter.routes] : [],
  list_type: props.filter?.list_type || 'blacklist',
  code: props.filter?.code || 403,
  is_active: props.filter && !props.filter.is_active ? 0 : 1,
});

const manuelRoutes = ref((props.filter?.routes || []).join('\n'));

/* Kip değişince `routes` tipini de değiştir; sunucu her iki biçimi de okuyor. */
watch(
  () => form.route_type,
  (type) => {
    if (type === 'manuel') {
      manuelRoutes.value = Array.isArray(form.routes) ? form.routes.join('\n') : form.routes;
      form.routes = manuelRoutes.value;
      return;
    }

    form.routes = String(form.routes || '')
      .split('\n')
      .map((line) => line.trim())
      .filter(Boolean);

    loadRoutes();
  },
);

onMounted(() => {
  if (form.route_type === 'select') {
    loadRoutes();
  }
});

function loadRoutes() {
  if (!props.routeList.length) {
    router.reload({ only: ['routeList'] });
  }
}

const visibleRoutes = computed(() => {
  const term = routeSearch.value.trim().toLowerCase();

  if (!term) {
    return props.routeList;
  }

  return props.routeList.filter((item) => item.uri.toLowerCase().includes(term));
});

const selected = computed(() => (Array.isArray(form.routes) ? form.routes : []));

function toggleRoute(uri) {
  const next = [...selected.value];
  const index = next.indexOf(uri);

  if (index === -1) {
    next.push(uri);
  } else {
    next.splice(index, 1);
  }

  form.routes = next;
}

function submit() {
  const url = editing.value
    ? route('admin.ip-filter.save', { ip_filter: props.filter.id })
    : route('admin.ip-filter.save');

  form.post(url, { preserveScroll: true });
}

function addIps() {
  if (!bulkIps.value.trim()) {
    return;
  }

  router.post(
    route('admin.ip-filter.ips.bulk', { ip_filter: props.filter.id }),
    { ips: bulkIps.value },
    { preserveScroll: true, onSuccess: () => (bulkIps.value = '') },
  );
}

async function removeIp(ip) {
  if (
    !(await confirm.value.ask({
      body: __('general.you_wont_be_able_to_revert_this'),
      confirmLabel: __('general.delete_confirm_yes'),
      cancelLabel: __('general.delete_confirm_no'),
    }))
  ) {
    return;
  }

  router.delete(
    route('admin.ip-filter.ips.destroy', { ip_filter: props.filter.id, ip_list: ip.id }),
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="title" />

  <div class="grid gap-3.5 p-[22px] lg:grid-cols-[minmax(0,1fr)_360px]">
    <div class="p-card h-max p-4">
      <div class="grid gap-3 sm:grid-cols-2">
        <FormField v-model="form.name" :label="__('ip_filter.name')" :error="form.errors.name" />
        <FormField
          v-model="form.list_type"
          type="select"
          :label="__('ip_filter.list_type')"
          :error="form.errors.list_type"
          :options="[
            { value: 'blacklist', label: __('ip_filter.blacklist') },
            { value: 'whitelist', label: __('ip_filter.whitelist') },
          ]"
        />
        <FormField
          v-model.number="form.code"
          type="select"
          :label="__('ip_filter.code')"
          :error="form.errors.code"
          :options="[
            { value: 403, label: '403' },
            { value: 404, label: '404' },
          ]"
        />
        <FormField
          v-model.number="form.is_active"
          type="select"
          :label="__('ip_filter.status')"
          :error="form.errors.is_active"
          :options="[
            { value: 1, label: __('ip_filter.status_active') },
            { value: 0, label: __('ip_filter.status_passive') },
          ]"
        />
      </div>

      <div class="mt-3">
        <label class="p-label">{{ __('ip_filter.ip_range') }}</label>
        <textarea
          v-model="form.ip_range"
          class="p-textarea h-28 font-mono"
          :placeholder="__('ip_filter.ip_range_placeholder')"
        ></textarea>
        <p v-if="form.errors.ip_range" class="mt-1 text-[11px] text-p-danger">
          {{ form.errors.ip_range }}
        </p>
      </div>

      <div class="mt-4">
        <div class="mb-2 flex flex-wrap gap-2">
          <label class="flex items-center gap-2 text-[12.5px]">
            <input v-model="form.route_type" type="radio" value="select" />
            {{ __('ip_filter.routes_select_box') }}
          </label>
          <label class="flex items-center gap-2 text-[12.5px]">
            <input v-model="form.route_type" type="radio" value="manuel" />
            {{ __('ip_filter.routes_select_manuel') }}
          </label>
        </div>

        <label class="p-label">{{ __('ip_filter.routes') }}</label>

        <textarea
          v-if="form.route_type === 'manuel'"
          v-model="form.routes"
          class="p-textarea h-32 font-mono"
          :placeholder="__('ip_filter.routes_placeholder')"
        ></textarea>

        <div v-else class="rounded-xl border border-p-line">
          <div class="flex items-center gap-2 border-b border-p-line2 px-2.5 py-1.5">
            <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
            <input
              v-model="routeSearch"
              :placeholder="__('general.search')"
              class="w-full border-0 bg-transparent text-[12px] text-p-ink outline-none"
            />
            <span class="p-chip !text-[9px]">{{ selected.length }}</span>
          </div>

          <div class="max-h-64 overflow-auto">
            <label
              v-for="item in visibleRoutes"
              :key="item.uri"
              class="flex cursor-pointer items-center gap-2 px-2.5 py-1.5 text-[11.5px] hover:bg-p-panel2"
            >
              <input
                type="checkbox"
                :checked="selected.includes(item.uri)"
                @change="toggleRoute(item.uri)"
              />
              <span class="p-chip !text-[9px]">{{ item.method }}</span>
              <span class="truncate">{{ item.uri }}</span>
            </label>

            <div v-if="!routeList.length" class="px-3 py-6 text-center text-[11.5px] text-p-ink3">
              {{ __('general.loading') }}
            </div>
          </div>
        </div>

        <p v-if="form.errors.routes" class="mt-1 text-[11px] text-p-danger">
          {{ form.errors.routes }}
        </p>
      </div>

      <div class="mt-4 flex justify-end">
        <button class="p-btn-primary" :disabled="form.processing" @click="submit">
          <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
        </button>
      </div>
    </div>

    <div v-if="editing" class="p-card h-max p-4">
      <div class="mb-2 flex items-center gap-2">
        <span class="font-display text-[13.5px] font-bold">{{ __('ip_filter.ip_list') }}</span>
        <span class="p-chip">{{ filter.ips.length }}</span>
      </div>

      <div class="mb-1 text-[11px] leading-relaxed text-p-ink3">
        {{ __('ip_filter.ip_bulk_help') }}
      </div>
      <textarea
        v-model="bulkIps"
        class="p-textarea h-24 font-mono"
        placeholder="1.2.3.4&#10;5.6.7.8"
      ></textarea>
      <button class="p-btn mt-2 w-full justify-center" @click="addIps">
        <i class="fa-solid fa-plus text-[11px]"></i> {{ __('ip_filter.ip_bulk_add') }}
      </button>

      <div class="mt-3 max-h-80 divide-y divide-p-line2 overflow-auto">
        <div v-for="ip in filter.ips" :key="ip.id" class="flex items-center gap-2 py-1.5">
          <span class="flex-1 font-mono text-[11.5px]">{{ ip.ip }}</span>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="removeIp(ip)"
          >
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div v-if="!filter.ips.length" class="py-6 text-center text-[11.5px] text-p-ink3">
          {{ __('general.no_records') }}
        </div>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
