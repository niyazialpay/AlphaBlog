<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { __ } from '../composables/useLang';
import { pushToast } from '../composables/useToast';
import { usePush } from '../composables/usePush';

const page = usePage();

/*
 * Tek tek cikarilir: sablonda otomatik `.value` acilimi yalnizca setup'in UST
 * seviyesindeki ref'ler icin calisir. `push.state` seklinde birakilsaydi
 * sablonda Ref nesnesi basilirdi.
 */
const {
  state: pushState,
  permission: pushPermission,
  busy: pushBusy,
  subscribed: pushSubscribed,
  toggle: togglePushState,
} = usePush();
const open = ref(false);
const root = ref(null);
const loaded = ref(false);

function outside(e) { if (root.value && !root.value.contains(e.target)) open.value = false; }
onMounted(() => document.addEventListener('click', outside));
onUnmounted(() => document.removeEventListener('click', outside));

/*
 * `notifications` paylasilan prop'u Inertia::optional() ile tanimli - ilk
 * yuklemede HIC gonderilmiyor. Dropdown acilana kadar hicbir zaman istenmedigi
 * icin liste daima bos gorunuyordu; burada acilista bir kereligine `only`
 * reload ile cekiliyor (`loaded` bayragi tekrar tekrar istek atmayi engeller).
 */
function toggle() {
  open.value = !open.value;

  if (open.value && !loaded.value) {
    loaded.value = true;
    router.reload({ only: ['notifications'] });
  }
}

function markAll() {
  axios
    .get(route('notifications.markAllAsRead'))
    .then(() => router.reload({ only: ['counts', 'notifications'] }))
    .finally(() => (open.value = false));
}

/*
 * Push bolumu.
 *
 * Yalnizca `push.enabled` iken render edilir: VAPID anahtari tanimli degilse
 * abone olunabilecek bir sey YOKTUR, dolayisiyla kullaniciya calismayan bir
 * dugme gosterilmez.
 *
 * Dort durum (bkz. composables/usePush.js):
 *   unsupported -> tarayici desteklemiyor, aciklama metni
 *   blocked     -> izin reddedilmis; tarayici BIR DAHA SORMAZ, ayar yonergesi
 *   on          -> abone, "kapat" dugmesi
 *   off         -> abone degil, "ac" dugmesi
 */
async function togglePush() {
  const wasOn = pushSubscribed.value;
  const ok = await togglePushState();

  if (ok) {
    pushToast(
      wasOn ? __('notifications.push_unsubscribed') : __('notifications.push_subscribed'),
      'success',
    );

    return;
  }

  // Izin reddedildiyse ayri bir mesaj: "hata" degil, tarayici ayari.
  pushToast(
    pushPermission.value === 'denied'
      ? __('notifications.push_blocked')
      : __('notifications.push_error'),
    'error',
  );
}
</script>

<template>
  <div ref="root" class="relative">
    <button :title="__('notifications.notifications')"
            class="relative grid h-[34px] w-[34px] place-items-center rounded-[9px] border border-p-line bg-p-panel2 text-p-ink2 hover:text-p-ink"
            @click="toggle">
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

      <!--
        Push bolumu. `push.enabled` false ise (VAPID anahtari yok) HIC
        basilmaz: abone olunacak bir sey olmadan dugme gostermek anlamsiz.
      -->
      <div v-if="page.props.push?.enabled" class="border-t border-p-line2 px-3.5 py-3">
        <div class="mb-2 flex items-center gap-2">
          <i class="fa-solid fa-bell text-[11px] text-p-ink3"></i>
          <div class="flex-1 text-[11.5px] font-bold text-p-ink2">
            {{ __('notifications.push_title') }}
          </div>
          <span
            v-if="pushState === 'on'"
            class="rounded-full bg-p-chip px-2 py-0.5 text-[10px] font-bold text-p-ok"
          >
            {{ __('notifications.push_on') }}
          </span>
        </div>

        <!-- 1) Tarayici desteklemiyor -->
        <p v-if="pushState === 'unsupported'" class="text-[11.5px] leading-relaxed text-p-ink3">
          {{ __('notifications.push_unsupported') }}
        </p>

        <!-- 2) Izin reddedilmis: tarayici BIR DAHA SORMAZ, yonerge sart -->
        <p v-else-if="pushState === 'blocked'" class="text-[11.5px] leading-relaxed text-p-warn">
          <i class="fa-solid fa-triangle-exclamation mr-1 text-[10.5px]"></i>
          {{ __('notifications.push_blocked') }}
          <span class="block text-p-ink3">{{ __('notifications.push_blocked_hint') }}</span>
        </p>

        <!-- 3/4) Acik ya da kapali: tek dugme -->
        <button
          v-else
          class="w-full justify-center text-[11.5px]"
          :class="pushState === 'on' ? 'p-btn' : 'p-btn-primary'"
          :disabled="pushBusy"
          @click="togglePush"
        >
          <i
            :class="
              pushBusy
                ? 'fa-solid fa-spinner fa-spin'
                : pushState === 'on'
                  ? 'fa-solid fa-bell-slash'
                  : 'fa-solid fa-bell'
            "
            class="text-[11px]"
          ></i>
          {{ pushState === 'on' ? __('notifications.push_disable') : __('notifications.push_enable') }}
        </button>

        <Link
          :href="route('admin.profile.index', { tab: 'notifications' })"
          class="mt-2 block text-[11px] font-semibold"
          @click="open = false"
        >
          {{ __('notifications.push_settings') }} →
        </Link>
      </div>
    </div>
  </div>
</template>
