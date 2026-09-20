<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import ConfirmDialog from '../../components/ConfirmDialog.vue';

/* panel/ip_filter/index.blade.php karşılığı. */
defineProps({
  filters: { type: Array, default: () => [] },
});

usePageHeader(__('ip_filter.ip_filter'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('ip_filter.ip_filter') },
]);

const confirm = ref(null);

function toggle(filter) {
  router.post(route('admin.ip-filter.toggle-status'), { id: filter.id }, { preserveScroll: true });
}

async function destroy(filter) {
  if (
    !(await confirm.value.ask({
      title: __('general.are_you_sure'),
      body: __('general.you_wont_be_able_to_revert_this'),
      confirmLabel: __('general.delete_confirm_yes'),
      cancelLabel: __('general.delete_confirm_no'),
    }))
  ) {
    return;
  }

  router.post(route('admin.ip-filter.delete'), { id: filter.id }, { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('ip_filter.ip_filter')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex justify-end">
      <Link :href="route('admin.ip-filter.create')" class="p-btn-primary">
        <i class="fa-solid fa-plus text-xs"></i> {{ __('general.new') }}
      </Link>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="w-[100px] px-3.5 py-2.5 font-semibold">{{ __('ip_filter.status') }}</th>
              <th class="px-3.5 py-2.5 font-semibold">{{ __('ip_filter.name') }}</th>
              <th class="w-[110px] px-3.5 py-2.5 text-center font-semibold">
                {{ __('ip_filter.ip_range') }}
              </th>
              <th class="w-[110px] px-3.5 py-2.5 text-center font-semibold">
                {{ __('ip_filter.routes') }}
              </th>
              <th class="w-[170px] px-3.5 py-2.5 font-semibold">{{ __('ip_filter.list_type') }}</th>
              <th class="w-[110px] px-3.5 py-2.5 font-semibold">{{ __('ip_filter.code') }}</th>
              <th class="w-[110px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="filter in filters"
              :key="filter.id"
              class="border-t border-p-line2 hover:bg-p-panel2"
            >
              <td class="px-3.5 py-2.5">
                <button
                  class="p-chip"
                  :class="filter.is_active && 'p-chip-accent'"
                  @click="toggle(filter)"
                >
                  <i
                    class="fa-solid"
                    :class="filter.is_active ? 'fa-toggle-on' : 'fa-toggle-off'"
                  ></i>
                  {{ filter.is_active ? __('ip_filter.status_active') : __('ip_filter.status_passive') }}
                </button>
              </td>
              <td class="px-3.5 py-2.5 font-semibold">{{ filter.name }}</td>
              <td class="px-3.5 py-2.5 text-center tabular-nums">{{ filter.ip_count }}</td>
              <td class="px-3.5 py-2.5 text-center tabular-nums">{{ filter.route_count }}</td>
              <td class="px-3.5 py-2.5">
                <span
                  class="p-chip"
                  :class="
                    filter.list_type === 'blacklist'
                      ? '!bg-p-danger !text-white'
                      : '!bg-p-ok !text-white'
                  "
                >
                  {{ __(`ip_filter.${filter.list_type}`) }}
                </span>
              </td>
              <td class="px-3.5 py-2.5 tabular-nums">{{ filter.code }}</td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <Link
                    :href="route('admin.ip-filter.show', { ip_filter: filter.id })"
                    class="p-icon-btn"
                    :title="__('general.edit')"
                  >
                    <i class="fa-solid fa-pen"></i>
                  </Link>
                  <button
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('general.delete')"
                    @click="destroy(filter)"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!filters.length">
              <td colspan="7" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('ip_filter.no_ip_filter') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ConfirmDialog ref="confirm" />
  </div>
</template>
