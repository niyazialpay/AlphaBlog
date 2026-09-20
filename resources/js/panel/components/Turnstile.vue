<script setup>
/**
 * `<x-turnstile />` Blade bileşeninin Vue karşılığı.
 *
 * Blade yalnızca `<div class="cf-turnstile" data-sitekey=…>` basıyordu ve
 * widget'ı `panel/auth/layouts/app.blade.php`'de yüklenen global
 * `challenges.cloudflare.com/turnstile/v0/api.js` otomatik tarıyordu. Inertia'da
 * ekranlar arası geçişte sayfa yeniden yüklenmediği için script burada bir kez
 * yüklenir ve widget açıkça render edilir.
 *
 * `reset()` dışarı verilir: blade akışı başarısız denemeden sonra
 * `turnstile.reset()` çağırıyordu, jeton tek kullanımlık.
 *
 * `getResponse()` dışarı verilir: çağıran sayfalar jetonu okuyup
 * gövdeye `cf-turnstile-response` olarak eklemek zorunda — aksi halde
 * CloudflareTurnstile middleware'i her isteği 403 ile reddediyor.
 */
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  theme: { type: String, default: 'auto' },
});

const page = usePage();
const el = ref(null);
const widgetId = ref(null);

const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

function loadScript() {
  if (window.turnstile) {
    return Promise.resolve();
  }

  const existing = document.querySelector(`script[src="${SCRIPT_URL}"]`);

  if (existing) {
    return new Promise((resolve) => existing.addEventListener('load', resolve, { once: true }));
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = SCRIPT_URL;
    script.async = true;
    script.defer = true;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
}

onMounted(async () => {
  if (!page.props.turnstileSiteKey) {
    return;
  }

  try {
    await loadScript();

    widgetId.value = window.turnstile.render(el.value, {
      sitekey: page.props.turnstileSiteKey,
      language: page.props.currentLanguage?.code || 'auto',
      theme: props.theme,
    });
  } catch {
    /* Ağ engelli: middleware istemci jetonu olmadan zaten reddeder. */
  }
});

onBeforeUnmount(() => {
  if (widgetId.value !== null) {
    window.turnstile?.remove(widgetId.value);
  }
});

function reset() {
  if (widgetId.value !== null) {
    window.turnstile?.reset(widgetId.value);
  }
}

function getResponse() {
  if (widgetId.value === null) {
    return '';
  }

  return window.turnstile?.getResponse(widgetId.value) || '';
}

defineExpose({ reset, getResponse });
</script>

<template>
  <div v-if="page.props.turnstileSiteKey" ref="el" class="flex justify-center"></div>
</template>
