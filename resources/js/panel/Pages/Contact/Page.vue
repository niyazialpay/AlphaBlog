<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import Tabs from '../../components/Tabs.vue';
import TinyMceEditor from '../../components/TinyMceEditor.vue';

/*
 * panel/contact.blade.php karşılığı.
 *
 * `save()` zaten `redirect()->back()->with(...)` döndürüyor — Inertia-native,
 * sunucuda değişiklik yok. Alan adları birebir korunur:
 * `description_<code>`, `meta_description_<code>`, `meta_keywords_<code>`, `maps`.
 */
const props = defineProps({
  pages: { type: Object, default: () => ({}) },
  maps: { type: String, default: null },
});

usePageHeader(__('contact.contact_page'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('contact.contact_page') },
]);

const languages = usePage().props.languages || [];
const active = ref(languages[0]?.code || 'tr');

const fields = {};
languages.forEach((language) => {
  const page = props.pages[language.code] || {};
  fields[`description_${language.code}`] = page.description || '';
  fields[`meta_description_${language.code}`] = page.meta_description || '';
  fields[`meta_keywords_${language.code}`] = page.meta_keywords || '';
});

const form = useForm({ ...fields, maps: props.maps || '' });

function submit() {
  form.post(route('admin.contact_page'), { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('contact.contact_page')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <Tabs
      v-model="active"
      :tabs="languages.map((language) => ({ value: language.code, label: language.name }))"
    />

    <div v-for="language in languages" v-show="active === language.code" :key="language.code" class="p-card p-4">
      <div class="grid gap-3">
        <div>
          <label class="p-label">
            {{ __('post.meta_description') }} ({{ language.code }})
          </label>
          <textarea
            v-model="form[`meta_description_${language.code}`]"
            class="p-textarea h-20"
          ></textarea>
        </div>

        <div>
          <label class="p-label">{{ __('post.meta_keywords') }} ({{ language.code }})</label>
          <input v-model="form[`meta_keywords_${language.code}`]" class="p-input" />
        </div>

        <div>
          <label class="p-label">{{ __('contact.description') }} ({{ language.code }})</label>
          <TinyMceEditor
            v-model="form[`description_${language.code}`]"
            :language="language.code"
            :height="520"
            :ai-enabled="false"
          />
        </div>
      </div>
    </div>

    <div class="p-card p-4">
      <label class="p-label">{{ __('contact.maps') }}</label>
      <textarea v-model="form.maps" class="p-textarea h-24 font-mono"></textarea>
    </div>

    <div class="flex justify-end">
      <button class="p-btn-primary" :disabled="form.processing" @click="submit">
        <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
      </button>
    </div>
  </div>
</template>
