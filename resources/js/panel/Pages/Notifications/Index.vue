<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { __, transChoice } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import { go } from '../../composables/useNavigate';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import Pagination from '../../components/Pagination.vue';

/*
 * panel/notifications/index.blade.php karşılığı.
 *
 * Eski ekran her işlem için Swal.fire + $.ajax kullanıyordu; burada
 * ConfirmDialog + Inertia form post'ları var. Sunucu tarafı R2 ile şimlendi:
 * jQuery çağıranlar hâlâ aynı JSON'u alıyor.
 */
defineProps({
  notifications: { type: Object, required: true },
});

usePageHeader(__('notifications.notifications'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('notifications.notifications') },
]);

const confirm = ref(null);

function markAsRead(id) {
  // Durum değiştiren GET yerine POST alias'ı (v2 prefetch tuzağı).
  router.post(route('notifications.markAsRead.post', { id }), {}, { preserveScroll: true });
}

function markAllAsRead() {
  router.post(route('notifications.markAllAsRead.post'), {}, { preserveScroll: true });
}

async function destroy(id) {
  if (!(await confirm.value.ask({ body: __('notifications.delete_warning') }))) {
    return;
  }

  router.delete(route('notifications.destroy'), {
    data: { id },
    preserveScroll: true,
  });
}

async function destroyAll() {
  if (!(await confirm.value.ask({ body: __('notifications.delete_warning') }))) {
    return;
  }

  router.delete(route('notifications.destroyAll'), { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('notifications.notifications')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="flex flex-wrap items-center gap-2">
      <div class="text-[12.5px] text-p-ink2">
        {{ transChoice('notifications.unread_notifications', $page.props.counts?.unreadNotifications || 0) }}
      </div>
      <div class="flex-1"></div>
      <button class="p-btn" @click="markAllAsRead">
        <i class="fa-solid fa-check text-[11px]"></i>
        {{ __('notifications.mark_all_as_read') }}
      </button>
      <button class="p-btn !text-p-danger" @click="destroyAll">
        <i class="fa-solid fa-trash text-[11px]"></i>
        {{ __('notifications.delete_all') }}
      </button>
    </div>

    <div class="p-card divide-y divide-p-line2">
      <div
        v-for="item in notifications.data"
        :key="item.id"
        class="flex items-start gap-3 px-4 py-3"
        :class="!item.readAt && 'bg-p-soft/40'"
      >
        <i
          class="fa-solid fa-bell mt-0.5 text-[13px]"
          :style="{ color: item.readAt ? 'rgb(var(--p-ink3))' : 'rgb(var(--p-accent))' }"
        ></i>

        <div class="min-w-0 flex-1">
          <div class="text-[12.5px]" :class="!item.readAt && 'font-bold'">{{ item.message }}</div>
          <a
            v-if="item.url"
            :href="item.url"
            class="mt-0.5 block truncate text-[11.5px]"
            @click.prevent="go(item.url)"
            >{{ item.title }}</a
          >
          <div v-else class="mt-0.5 truncate text-[11.5px] text-p-ink3">{{ item.title }}</div>
          <div class="mt-1 text-[10.5px] text-p-ink3">{{ item.ago }}</div>
        </div>

        <div class="flex shrink-0 gap-1">
          <button
            v-if="!item.readAt"
            class="p-icon-btn"
            :title="__('notifications.mark_as_read')"
            @click="markAsRead(item.id)"
          >
            <i class="fa-solid fa-check"></i>
          </button>
          <button
            class="p-icon-btn hover:!border-p-danger hover:!text-p-danger"
            :title="__('general.delete')"
            @click="destroy(item.id)"
          >
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>

      <div
        v-if="!notifications.data.length"
        class="px-4 py-10 text-center text-[12.5px] text-p-ink3"
      >
        {{ __('notifications.no_notifications') }}
      </div>
    </div>

    <Pagination :links="notifications.links" :meta="notifications" />

    <ConfirmDialog ref="confirm" />
  </div>
</template>
