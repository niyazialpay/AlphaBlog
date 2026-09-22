<script setup>
import { __ } from '../composables/useLang';
import { pushToast } from '../composables/useToast';
import { usePush } from '../composables/usePush';

/*
 * Ilk ziyaret karti.
 *
 * MODAL DEGIL: panelin akisini kesmez, kapatilabilir ve kendiliginden
 * kaybolmaz. Yalnizca izin HIC SORULMAMISSA cikar (`permission === 'default'`)
 * — reddedilmis bir izinde tarayici bir daha sormaz, o durumda kart yerine
 * zil dropdown'indaki "ayarlardan engellenmis" uyarisi gecerlidir.
 *
 * Kapatma karari `localStorage`da tutulur ve HER erisim try/catch icindedir
 * (bkz. usePush.js): gizli sekmede ve site verisi engellendiginde okuma bile
 * firlatir. Hatirlanamazsa en kotu ihtimalle kart sonraki ziyarette yeniden
 * cikar — veri kaybi yok.
 */
const { shouldPrompt, busy, subscribe, dismissPrompt } = usePush();

async function enable() {
  const ok = await subscribe();

  if (ok) {
    pushToast(__('notifications.push_subscribed'), 'success');

    return;
  }

  /*
   * Kullanici tarayici diyalogunda "engelle" dediyse kart artik anlamsiz
   * (`shouldPrompt` false olur, cunku izin 'denied'). Yine de sessiz kalmamak
   * icin ne oldugu soylenir.
   */
  pushToast(__('notifications.push_error'), 'error');
}
</script>

<template>
  <div v-if="shouldPrompt" class="px-[22px] pt-[22px]">
    <div class="p-card flex flex-wrap items-center gap-3 border-l-2 border-l-p-accent p-3.5">
      <i class="fa-solid fa-bell text-[15px] text-p-accent"></i>

      <div class="min-w-[180px] flex-1">
        <div class="text-[12.8px] font-bold text-p-ink">{{ __('notifications.push_prompt_title') }}</div>
        <div class="mt-0.5 text-[11.5px] leading-relaxed text-p-ink3">
          {{ __('notifications.push_prompt_body') }}
        </div>
      </div>

      <div class="flex gap-2">
        <button class="p-btn-primary text-[11.5px]" :disabled="busy" @click="enable">
          <i :class="busy ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-bell'" class="text-[11px]"></i>
          {{ __('notifications.push_enable') }}
        </button>
        <button class="p-btn text-[11.5px]" @click="dismissPrompt">
          {{ __('notifications.push_prompt_dismiss') }}
        </button>
      </div>
    </div>
  </div>
</template>
