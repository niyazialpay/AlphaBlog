<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { __ } from '../composables/useLang';

const page = usePage();
const open = ref(false);
const root = ref(null);

function outside(e) { if (root.value && !root.value.contains(e.target)) open.value = false; }
onMounted(() => document.addEventListener('click', outside));
onUnmounted(() => document.removeEventListener('click', outside));

/*
 * notifications.markAllAsRead durum degistiren bir GET route'u ve henuz Blade
 * ekranina yonlendiriyor. Inertia ziyareti yerine axios ile cagirilip yalnizca
 * sayaclar tazelenir.
 */
function markAll() {
  axios
    .get(route('notifications.markAllAsRead'))
    .then(() => router.reload({ only: ['counts', 'notifications'] }))
    .finally(() => (open.value = false));
}
</script>

<template>
  <div ref="root" class="relative">
    <button :title="__('notifications.notifications')"
            class="relative grid h-[34px] w-[34px] place-items-center rounded-[9px] border border-p-line bg-p-panel2 text-p-ink2 hover:text-p-ink"
            @click="open = !open">
      <i class="fa-solid fa-bell text-[13px]"></i>
      <span v-if="page.props.counts?.unreadNotifications"
            class="absolute -right-1 -top-1 grid h-4 min-w-[16px] place-items-center rounded-lg bg-p-danger px-1 text-[9.5px] font-bold text-white">
        {{ page.props.counts.unreadNotifications > 99 ? '99+' : page.props.counts.unreadNotifications }}
      </span>
    </button>

    <div v-if="open"
         class="absolute right-0 top-[42px] z-30 w-[min(340px,90vw)] animate-popIn overflow-hidden rounded-2xl border border-p-line bg-p-panel shadow-pop">
      <div class="flex items-center gap-2 border-b border-p-line2 px-3.5 py-3">
        <div class="flex-1 font-display text-[12.8px] font-bold">{{ __('notifications.notifications') }}</div>
        <span class="rounded-full bg-p-chip px-2 py-0.5 text-[10.5px] font-bold text-p-danger">
          {{ page.props.counts?.unreadNotifications || 0 }}
        </span>
      </div>

      <div class="max-h-80 overflow-auto">
        <a v-for="n in page.props.notifications || []" :key="n.id" :href="n.url"
           class="flex gap-2.5 border-b border-p-line2 px-3.5 py-2.5 no-underline hover:bg-p-panel2">
          <i class="fa-solid fa-bell mt-0.5 text-xs"
             :style="{ color: n.readAt ? 'rgb(var(--p-warn))' : 'rgb(var(--p-accent))' }"></i>
          <div class="min-w-0 flex-1">
            <div class="text-[12.5px]" :class="n.readAt ? 'text-p-ink2' : 'font-bold text-p-ink'">{{ n.message }}</div>
            <div class="mt-0.5 truncate text-[11.5px] text-p-ink3">{{ n.title }}</div>
          </div>
          <div class="whitespace-nowrap text-[10.5px] text-p-ink3">{{ n.ago }}</div>
        </a>
        <div v-if="!(page.props.notifications || []).length" class="px-3.5 py-6 text-center text-xs text-p-ink3">
          {{ __('notifications.no_notifications') }}
        </div>
      </div>

      <div class="flex items-center gap-2 bg-p-panel2 px-3.5 py-2.5">
        <button class="p-btn h-7 text-[11.5px]" @click="markAll">{{ __('notifications.mark_all_as_read') }}</button>
        <div class="flex-1"></div>
        <Link :href="route('notifications.index')" class="text-[11.5px] font-semibold" @click="open = false">
          {{ __('notifications.all_notifications') }} →
        </Link>
      </div>
    </div>
  </div>
</template>
