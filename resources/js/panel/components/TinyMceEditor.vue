<script setup>
/**
 * TinyMCE sarmalayıcı — mevcut panel/post/add-edit.blade.php konfigürasyonunu
 * BİREBİR korur. Değiştirilen tek şey: tema (skin/content_css) artık
 * data-panel-theme'e bağlı ve AI Asistan toolbar butonu eklendi.
 *
 * ÖNEMLİ: tinymce.min.js Blade kabuğunda self-hosted olarak yükleniyor
 * (public/themes/panel/js/tinymce). npm paketine geçirmeyin — lisans gpl + yüklü dil
 * paketleri ve mevcut yükleme yolu bozulur.
 */
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { theme as panelTheme } from '../composables/useTheme';
import { __ } from '../composables/useLang';
import { pushToast } from '../composables/useToast';

const props = defineProps({
  modelValue: { type: String, default: '' },
  // Bos birakilirsa gorsel yukleme kapali kalir (iletisim sayfasi editorunde
  // blade'de de images_upload_url tanimli degildi).
  uploadUrl: { type: String, default: '' },      // route('admin.post.editor.image.upload', [...])
  language: { type: String, default: 'tr' },     // session('language')
  /**
   * Sayi => piksel, metin => ham CSS degeri, null => ekranin altina kadar uzayan
   * `dvh` tabanli varsayilan (asagidaki nota bakiniz).
   */
  height: { type: [Number, String], default: null },
  /** Kisa pencerede editor kullanilabilir kalsin diye taban yukseklik. */
  minHeight: { type: Number, default: 320 },
  /**
   * Dar ekranda SABIT yukseklik (px).
   *
   * Mobilde `dvh` hesabi kullanilmaz: sarmalayici sayfada asagida oldugu icin
   * olculen ust konum viewport'a yaklasiyor, `100dvh - top` sifira hatta eksiye
   * dusuyor ve editor tamamen kayboluyordu. Eski Blade ekrani da mobilde sabit
   * 400px veriyordu (add-edit.blade.php:602, `mobile: { height: 400 }`).
   */
  mobileHeight: { type: Number, default: 400 },
  aiEnabled: { type: Boolean, default: true },
  /** Yükleme isteğine eklenecek ek alanlar (title, slug, meta_keywords, language) */
  uploadMeta: { type: Function, default: () => ({}) },
});
const emit = defineEmits(['update:modelValue', 'ai', 'uploaded', 'ready']);

const el = ref(null);
const wrap = ref(null);
let editor = null;

/*
 * YÜKSEKLİK
 *
 * Editör gövdesi sayfanın altına kadar uzar ve ölçü birimi `dvh`'dir: `vh`
 * mobil tarayıcılarda katlanan adres/araç çubuğunu hesaba katmaz, bu yüzden
 * editörün alt kenarı ekranın dışında kalır. `dvh` görünür alanla birlikte
 * daralıp genişler.
 *
 * Yukarıdan düşülecek pay (panel üst çubuğu + sayfanın kendi başlık/araç
 * satırları + kenar boşluğu) ekrandan ekrana değiştiği için SABİT YAZILMAZ:
 * sarmalayıcının viewport'a göre üst konumu ölçülür ve yükseklik
 * `calc(100dvh - <ölçülen>px)` olur. Böylece yazı, sayfa ve kişisel not
 * editörleri kendi bileşenlerine dokunulmadan doğru yüksekliği alır.
 */
const FALLBACK_OFFSET = 200;
const BOTTOM_GAP = 22; // sayfa kabuğundaki p-[22px] alt boşluğu

const offset = ref(FALLBACK_OFFSET);

/* Tailwind `md` esigi; CSS'teki medya sorgusuyla AYNI deger olmali. */
const NARROW_BREAKPOINT = 768;

function isNarrow() {
    return typeof window !== 'undefined' && window.innerWidth < NARROW_BREAKPOINT;
}

function measure() {
  const top = wrap.value?.getBoundingClientRect().top;

  offset.value = Number.isFinite(top) && top > 0
    ? Math.round(top) + BOTTOM_GAP
    : FALLBACK_OFFSET;
}

const resolvedHeight = computed(() => {
  if (typeof props.height === 'number') {
    return `${props.height}px`;
  }

  if (typeof props.height === 'string' && props.height !== '') {
    return props.height;
  }

  return `calc(100dvh - ${offset.value}px)`;
});

/*
 * TinyMCE'nin kendi `height` secenegi yalnizca ILK cizimde bos bir kutu
 * gorunmesin diye piksel olarak verilir; nihai yukseklik her durumda asagidaki
 * `.tox-tinymce { height: 100% !important }` kuralindan gelir.
 */
function initialPixelHeight() {
  if (typeof props.height === 'number') {
    return props.height;
  }

  if (isNarrow()) {
    return props.mobileHeight;
  }

  const available = (window.innerHeight || 0) - offset.value;

  return Math.max(props.minHeight, Math.round(available) || props.minHeight);
}

const AI_ACTIONS = [
  { value: 'titles', text: 'Başlık önerileri' },
  { value: 'meta', text: 'SEO meta açıklaması üret' },
  { value: 'excerpt', text: 'Özet / excerpt çıkar' },
  { value: 'expand', text: 'Metni genişlet' },
  { value: 'shorten', text: 'Metni kısalt' },
  { value: 'rewrite', text: 'Yeniden yaz' },
  { value: 'tone', text: 'Tonu değiştir' },
  { value: 'translate', text: 'İngilizceye çevir' },
  { value: 'tags', text: 'Etiket & kategori öner' },
  { value: 'alt', text: 'Görsel alt metni üret' },
];

function uploadHandler(blobInfo, progress) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.open('POST', props.uploadUrl);
    xhr.upload.onprogress = (e) => progress((e.loaded / e.total) * 100);
    xhr.onload = () => {
      if (xhr.status === 403) return reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
      if (xhr.status < 200 || xhr.status >= 300) return reject('HTTP Error: ' + xhr.status);
      const json = JSON.parse(xhr.responseText);
      if (!json || typeof json.location !== 'string') return reject('Invalid JSON: ' + xhr.responseText);
      resolve(json.location);
      // Yeni yazıda ilk görsel yüklemesi post kaydı oluşturur → id'yi üst bileşene bildir.
      emit('uploaded', json);
    };
    xhr.onerror = () => reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);

    const fd = new FormData();
    fd.append('file', blobInfo.blob(), blobInfo.filename());
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    Object.entries(props.uploadMeta()).forEach(([k, v]) => fd.append(k, v ?? ''));
    xhr.send(fd);
  });
}

function settings() {
  const dark = panelTheme.value === 'dark';
  return {
    target: el.value,
    language: props.language,
    branding: false,
    license_key: 'gpl',
    /*
     * TinyMCE'nin kendi yukledigi eklenti/tema/skin dosyalarina surum eki.
     * Sunucu hepsini `immutable` onbellekle veriyor; ek olmazsa bir sonraki
     * yukseltmede tarayici eski eklentiyi yeni cekirdekle karistirir.
     */
    cache_suffix: `?v=${window.tinymce.majorVersion}.${window.tinymce.minorVersion}`,
    height: initialPixelHeight(),
    plugins: [
      'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
      'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'insertdatetime',
      'media', 'nonbreaking', 'table', 'directionality', 'emoticons', 'codesample', 'help', 'quickbars',
      'accordion',
    ],
    toolbar1: 'undo redo | bold italic | fontsize blocks forecolor backcolor | '
      + 'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | help '
      + (props.aiEnabled ? '| aiassistant' : ''),
    toolbar2: 'print preview media image | charmap emoticons codesample code | visualblocks',
    image_advtab: true,
    fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
    extended_valid_elements: 'a[class|name|href|target|title|onclick|rel],'
      + 'script[type|src]iframe[src|style|'
      + 'width|height|scrolling|marginwidth|marginheight|frameborder]'
      + 'img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]',
    contextmenu: 'undo redo | link image imagetools table spellchecker | '
      + 'inserttable cell row column deletetable | help',
    required: true,
    entity_encoding: 'raw',
    promotion: false,
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    skin: dark ? 'oxide-dark' : 'oxide',
    content_css: dark ? 'dark' : 'default',
    ...(props.uploadUrl ? { images_upload_handler: uploadHandler } : {}),
    mobile: {
      theme: 'silver',
      toolbar: 'undo | bold italic | link | image | font size select forecolor',
      menubar: false,
      height: 400,
      plugins: ['autosave', 'lists', 'autolink', 'code', 'fullscreen', 'wordcount'],
    },
    setup(ed) {
      ed.on('Change KeyUp Undo Redo', () => emit('update:modelValue', ed.getContent()));
      if (!props.aiEnabled) return;
      ed.ui.registry.addMenuButton('aiassistant', {
        icon: 'ai',
        text: 'AI Asistan',
        tooltip: 'AI Asistan',
        fetch: (cb) => cb(AI_ACTIONS.map((a) => ({
          type: 'menuitem',
          text: a.text,
          onAction: () => emit('ai', { action: a.value, selection: ed.selection.getContent({ format: 'text' }), editor: ed }),
        }))),
      });
    },
  };
}

/*
 * TinyMCE yuklenemezse SESSIZ KALMA.
 *
 * `tinymce.min.js` kok blade'den self-hosted geliyor. Dosya 404 verir, CDN/WAF
 * keser ya da `init()` firlatirsa eskiden hicbir sey olmuyordu: editor mount
 * edilmiyor, textarea da TinyMCE tarafindan gizlenecegi varsayilarak ciplak
 * kaliyordu — kullanici BOS bir kutu goruyordu ve konsol disinda iz yoktu.
 * Mobilde bildirilen "editor acilmiyor" sikayetinin bu olma ihtimali var.
 *
 * Artik basarisizlikta duz textarea kullanilabilir halde kaliyor (icerik
 * kaybolmuyor, yaziya devam edilebiliyor) ve durum ekranda soyleniyor.
 */
const failed = ref(false);

async function mount(content = props.modelValue) {
  measure();

  /*
   * Icerik init'ten ONCE textarea'ya yazilir, sonradan `setContent` ile degil.
   * `wordcount` eklentisi dinleyicilerini init'ten sonra `setTimeout(0)` ile
   * bagliyor; init'in hemen ardindan yapilan `setContent` o dinleyicilere
   * ulasmiyor ve sayac ilk tusa kadar "0 kelime" kaliyordu. Yedek textarea da
   * bu sayede bos degil, icerikle acilir.
   */
  el.value.value = content || '';

  if (typeof window.tinymce === 'undefined') {
    failed.value = true;
    pushToast(__('post.editor_unavailable'), 'error', 8000);

    return;
  }

  /*
   * Yuklenemeyen kaynaklar (tema/eklenti/dil script'i, skin CSS) `error`
   * olayini yalnizca CAPTURE fazinda verir; teshis icin toplanir.
   */
  const loadErrors = [];
  const collectError = (event) => {
    const source = event.target?.src || event.target?.href;
    loadErrors.push(source ? `load:${source.split('/tinymce/').pop()}` : String(event.message || event.reason || event));
  };

  window.addEventListener('error', collectError, true);
  window.addEventListener('unhandledrejection', collectError);

  let initTimer;

  try {
    /*
     * Editor HATA VERMEDEN gorunmez kalabiliyor: iPhone onbellekten eski
     * cekirdegi (7.0) calistirdi, yeni eklentiler init icinde patladi ve init
     * promise'i hic sonuclanmadi; textarea gizli, alan dokunusa tepkisiz, mesaj
     * yok. Ayrica skin yuklenmezse init BASARIYLA doner ama kabuk
     * `visibility: hidden` kalir. Bu yuzden `SkinLoaded` + zaman asimi beklenir,
     * olmazsa yedek textarea'ya dusulur ve sebep ekrana yazilir.
     */
    await Promise.race([
      (async () => {
        await window.tinymce.init(settings());

        editor = window.tinymce.get(el.value.id);

        // init hata firlatmadan editor kurmayabiliyor: bos ekran yerine yedege dus.
        if (!editor) {
          throw new Error('no editor');
        }

        await skinReady(editor);
      })(),
      new Promise((_, reject) => {
        initTimer = setTimeout(() => reject(new Error('timeout')), INIT_TIMEOUT_MS);
      }),
    ]);

    failed.value = false;
    emit('ready', editor.getContent());
  } catch (error) {
    failed.value = true;
    // eslint-disable-next-line no-console
    console.error('TinyMCE init hatasi', error, loadErrors);
    fallBackToTextarea();
    pushToast(`${__('post.editor_unavailable')} [${diagnose(error, loadErrors)}]`, 'error', 30000);
  } finally {
    clearTimeout(initTimer);
    window.removeEventListener('error', collectError, true);
    window.removeEventListener('unhandledrejection', collectError);
  }
}

const INIT_TIMEOUT_MS = 15000;

function skinReady(instance) {
  if (instance._skinLoaded) {
    return Promise.resolve();
  }

  return new Promise((resolve, reject) => {
    instance.once('SkinLoaded', resolve);
    instance.once('SkinLoadError', (event) => reject(new Error(event?.message || 'skin')));
  });
}

/** Askida kalan kurulumu soker ve TinyMCE'nin gizledigi textarea'yi geri acar. */
function fallBackToTextarea() {
  try {
    window.tinymce?.get(el.value.id)?.remove();
  } catch {
    // Yarim kurulmus editor remove'da firlatabilir; asagida elle temizlenir.
  }

  wrap.value?.querySelectorAll('.tox-tinymce, .tox-tinymce-aux').forEach((node) => node.remove());
  editor = null;
  el.value.style.display = '';
  el.value.removeAttribute('aria-hidden');
}

/** Gercek cihazda konsol olmadan sebebi gorebilmek icin kisa durum ozeti. */
function diagnose(error, loadErrors) {
  const shell = wrap.value?.querySelector('.tox-tinymce');
  const iframe = wrap.value?.querySelector('iframe');

  const instance = window.tinymce?.get(el.value.id);

  return [
    `v${window.tinymce?.majorVersion}.${window.tinymce?.minorVersion}`,
    error?.message || String(error),
    instance ? `init:${instance.initialized}/skin:${Boolean(instance._skinLoaded)}` : 'editor:none',
    shell ? `tox:${getComputedStyle(shell).visibility}/${Math.round(shell.getBoundingClientRect().height)}` : 'tox:none',
    iframe ? `iframe:${iframe.contentDocument?.readyState ?? 'x'}` : 'iframe:none',
    ...loadErrors.slice(0, 4),
  ].join(' | ');
}
function destroy() {
  editor?.remove();
  editor = null;
}

onMounted(async () => {
  await mount();
  // Editor kurulduktan sonra ust konum kesinlesir (arac satirlari yerine oturur).
  await nextTick();
  measure();
  window.addEventListener('resize', measure, { passive: true });
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', measure);
  destroy();
});

// Tema değişince editörü yeniden kur (AdminLTE'deki dark-mode-switcher davranışının karşılığı).
watch(panelTheme, async () => {
  const content = editor ? editor.getContent() : props.modelValue;
  destroy();
  await mount(content);
});

watch(() => props.modelValue, (v) => {
  if (editor && v !== editor.getContent()) editor.setContent(v || '');
});

/** AI sonucunu editöre yazmak için: editorRef.insert('<p>…</p>') */
defineExpose({
  insert: (html) => editor?.insertContent(html),
  replaceSelection: (html) => editor?.selection.setContent(html),
  getContent: () => editor?.getContent(),
});
</script>

<template>
  <div
    ref="wrap"
    class="tinymce-shell overflow-hidden rounded-2xl border border-p-line bg-p-panel shadow-panel"
    :style="{
      '--tinymce-height': resolvedHeight,
      '--tinymce-min-height': `${minHeight}px`,
      '--tinymce-mobile-height': `${mobileHeight}px`,
    }"
  >
    <textarea
      :id="`tinymce-${$.uid}`"
      ref="el"
      :class="failed ? 'tinymce-fallback' : ''"
      @input="failed && emit('update:modelValue', $event.target.value)"
    ></textarea>
  </div>
</template>

<style scoped>
/*
 * Mobilde SABIT yukseklik. `dvh` hesabi yalnizca genis ekranda devreye girer:
 * dar ekranda sarmalayici sayfada asagida kaldigi icin olculen ust konum
 * viewport'a yaklasiyor, `calc(100dvh - top)` sifira/eksiye dusuyor ve editor
 * gorunmez oluyordu.
 */
.tinymce-shell {
  height: var(--tinymce-mobile-height);
  min-height: var(--tinymce-mobile-height);
}

@media (min-width: 768px) {
  .tinymce-shell {
    /* `max()` guvenlik agi: olcum hatali cikarsa bile kutu cokmez. */
    height: max(var(--tinymce-min-height), var(--tinymce-height));
    min-height: var(--tinymce-min-height);
  }
}

/*
 * TinyMCE dis kabuga SATIR ICI `height` basar; satir ici stili yalnizca
 * `!important` yener. Boylece tek yukseklik kaynagi sarmalayici olur ve
 * pencere/`dvh` degistiginde editor onunla birlikte buyuyup kuculur.
 */
/* TinyMCE yuklenemedigindeki yedek: duz ama kullanilabilir bir metin alani. */
.tinymce-fallback {
  width: 100%;
  height: 100%;
  min-height: inherit;
  padding: 12px;
  border: 0;
  outline: none;
  resize: none;
  background: rgb(var(--p-panel2));
  color: rgb(var(--p-ink));
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 12.5px;
  line-height: 1.6;
}

.tinymce-shell :deep(.tox-tinymce) {
  height: 100% !important;
  max-height: 100% !important;
  min-height: 0 !important;
}
</style>
