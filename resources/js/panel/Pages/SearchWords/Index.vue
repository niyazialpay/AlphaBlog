<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { formatDateTime } from '../../composables/useFormat';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/search.blade.php karşılığı.
 *
 * Eski ekranda 11 adet Swal.fire vardı; hepsi ConfirmDialog'a indi.
 * `platform` / `browser` artık sunucuda çözülüp string olarak geliyor: Blade'e
 * `new Browser` servis nesnesi geçiliyordu ve o JSON'a serileştirilemez.
 */
const props = defineProps({
  words: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  total: { type: Number, default: 0 },
});

usePageHeader(__('search.searched_words'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('search.searched_words') },
]);

const search = ref(props.filters.search || '');
const confirm = ref(null);

let timer = null;
watch(search, (value) => {
  clearTimeout(timer);
  timer = setTimeout(() => {
    router.get(
      route('admin.search.index'),
      { search: value || undefined },
      { only: ['words', 'filters', 'total'], preserveState: true, preserveScroll: true, replace: true },
    );
  }, 250);
});

function toggleThink(word) {
  router.post(route('admin.search.think', { search: word.id }), {}, { preserveScroll: true });
}

async function destroy(word) {
  if (!(await confirm.value.ask({ body: __('search.delete.warning') }))) {
    return;
  }

  router.post(route('admin.search.delete', { search: word.id }), {}, { preserveScroll: true });
}

async function destroyAll() {
  if (!(await confirm.value.ask({ body: __('search.delete.delete_all_warning') }))) {
    return;
  }

  router.post(route('admin.search.delete.all'), {}, { preserveScroll: true });
}

async function destroyNotInterested() {
  if (!(await confirm.value.ask({ body: __('search.delete.delete_all_warning') }))) {
    return;
  }

  router.post(route('admin.search.delete.not-interested'), {}, { preserveScroll: true });
}

function markAllChecked() {
  router.post(
    route('admin.search.check'),
    { search: search.value || '' },
    { preserveScroll: true },
  );
}
</script>

<template>
  <Head :title="__('search.searched_words')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <span class="p-chip">{{ __('search.total') }}: {{ total }}</span>

      <div class="flex h-8 items-center gap-2 rounded-[9px] border border-p-line bg-p-panel px-2.5">
        <i class="fa-solid fa-magnifying-glass text-[11px] text-p-ink3"></i>
        <input
          v-model="search"
          :placeholder="__('general.search')"
          class="w-[180px] border-0 bg-transparent text-[12.5px] text-p-ink outline-none"
        />
      </div>

      <div class="flex-1"></div>

      <button class="p-btn" @click="markAllChecked">
        <i class="fa-solid fa-check-double text-[11px]"></i>
        {{ __('general.mark_all_checked') }}
      </button>
      <button class="p-btn !text-p-danger" @click="destroyNotInterested">
        <i class="fa-solid fa-broom text-[11px]"></i>
        {{ __('search.delete.delete_not_think') }}
      </button>
      <button class="p-btn !text-p-danger" @click="destroyAll">
        <i class="fa-solid fa-trash text-[11px]"></i>
        {{ __('search.delete.delete_all') }}
      </button>
    </div>

    <div class="p-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[12.5px]">
          <thead>
            <tr class="bg-p-panel2 text-left text-[11px] uppercase tracking-[.06em] text-p-ink3">
              <th class="px-3.5 py-2.5 font-semibold">{{ __('search.searched_words') }}</th>
              <th class="whitespace-nowrap px-3.5 py-2.5 font-semibold">
                {{ __('search.search_date') }}
              </th>
              <th class="px-3.5 py-2.5 font-semibold">
                {{ __('search.search_ip_and_browser') }}
              </th>
              <th class="w-[110px] px-3.5 py-2.5 text-right font-semibold">
                {{ __('general.actions') }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="word in words.data"
              :key="word.id"
              class="border-t border-p-line2 align-top hover:bg-p-panel2"
            >
              <!-- Blade'de incelenmemis kelimeler <strong> ile vurgulaniyordu. -->
              <td class="px-3.5 py-2.5" :class="!word.checked && 'font-bold'">
                {{ word.search }}
              </td>
              <td class="whitespace-nowrap px-3.5 py-2.5 tabular-nums text-p-ink3">
                {{ formatDateTime(word.createdAt) }}
              </td>
              <td class="px-3.5 py-2.5 text-[11px] text-p-ink3">
                <div>{{ word.platform }}</div>
                <div>{{ word.browser }}</div>
                <div>{{ word.ip }}</div>
              </td>
              <td class="px-3.5 py-2.5">
                <div class="flex justify-end gap-1">
                  <button
                    class="p-icon-btn"
                    :class="word.think && '!border-p-warn !text-p-warn'"
                    :title="word.think ? __('search.thinking') : __('search.think')"
                    @click="toggleThink(word)"
                  >
                    <i class="fa-solid fa-lightbulb"></i>
                  </button>
                  <button
                    class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
                    :title="__('general.delete')"
                    @click="destroy(word)"
                  >
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!words.data.length">
              <td colspan="4" class="px-3.5 py-10 text-center text-p-ink3">
                {{ __('general.no_records') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Pagination :links="words.links" :meta="words" :only="['words', 'filters', 'total']" />

    <ConfirmDialog ref="confirm" />
  </div>
</template>
