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
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { theme as panelTheme } from '../composables/useTheme';

const props = defineProps({
  modelValue: { type: String, default: '' },
  // Bos birakilirsa gorsel yukleme kapali kalir (iletisim sayfasi editorunde
  // blade'de de images_upload_url tanimli degildi).
  uploadUrl: { type: String, default: '' },      // route('admin.post.editor.image.upload', [...])
  language: { type: String, default: 'tr' },     // session('language')
  height: { type: Number, default: 750 },
  aiEnabled: { type: Boolean, default: true },
  /** Yükleme isteğine eklenecek ek alanlar (title, slug, meta_keywords, language) */
  uploadMeta: { type: Function, default: () => ({}) },
});
const emit = defineEmits(['update:modelValue', 'ai', 'uploaded']);

const el = ref(null);
let editor = null;

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
    height: props.height,
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
      plugins: ['autosave', 'lists', 'autolink', 'code', 'fullscreen'],
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

async function mount() {
  await window.tinymce.init(settings());
  editor = window.tinymce.get(el.value.id);
  editor?.setContent(props.modelValue || '');
}
function destroy() {
  editor?.remove();
  editor = null;
}

onMounted(mount);
onBeforeUnmount(destroy);

// Tema değişince editörü yeniden kur (AdminLTE'deki dark-mode-switcher davranışının karşılığı).
watch(panelTheme, async () => {
  const content = editor ? editor.getContent() : props.modelValue;
  destroy();
  await mount();
  editor?.setContent(content || '');
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
  <div class="overflow-hidden rounded-2xl border border-p-line bg-p-panel shadow-panel">
    <textarea :id="`tinymce-${$.uid}`" ref="el"></textarea>
  </div>
</template>
