<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { __ } from '../../composables/useLang';
import { usePageHeader } from '../../composables/usePageHeader';
import FormField from '../../components/FormField.vue';

/*
 * panel/firewall/index.blade.php karşılığı.
 *
 * `FirewallSettingsRequest` sözleşmesi birebir: bütün denetimler boolean
 * (1/0), blacklist_rule_id zorunlu, whitelist_rule_id nullable + different.
 * Sağlayıcı → model şelalesi blade'deki `renderModelOptions()` ile aynı
 * davranır (sağlayıcı değişince model listesi yenilenir, eşleşmeyen seçim
 * varsayılana düşer).
 */
const props = defineProps({
  firewall: { type: Object, required: true },
  ipFilters: { type: Array, default: () => [] },
  chatProviders: { type: [Array, Object], default: () => ({}) },
});

usePageHeader(__('firewall.firewall'), [
  { label: __('dashboard.dashboard'), route: 'admin.index' },
  { label: __('firewall.firewall') },
]);

/* Blade'de her denetim aktif/pasif <select>'i; sıra korunur. */
const CHECKS = [
  'check_referer',
  'check_bots',
  'check_request_method',
  'check_dos',
  'check_union_sql',
  'check_click_attack',
  'check_xss',
  'check_cookie_injection',
];

const AI_NUMBERS = [
  { key: 'ai_confidence_threshold', min: 1, max: 100 },
  { key: 'ai_sample_rate', min: 0, max: 100 },
  { key: 'ai_timeout_seconds', min: 1, max: 30 },
  { key: 'ai_cache_ttl_seconds', min: 60, max: 86400 },
  { key: 'ai_max_payload_chars', min: 500, max: 12000 },
];

function flag(value) {
  return value ? 1 : 0;
}

const form = useForm({
  is_active: flag(props.firewall.is_active),
  blacklist_rule_id: props.firewall.blacklist_rule_id || '',
  whitelist_rule_id: props.firewall.whitelist_rule_id || '',
  ...Object.fromEntries(CHECKS.map((check) => [check, flag(props.firewall[check])])),
  bad_bots: props.firewall.bad_bots || '',
  ai_review_enabled: flag(props.firewall.ai_review_enabled),
  ai_enforcement_enabled: flag(props.firewall.ai_enforcement_enabled),
  ai_provider: props.firewall.ai_provider || '',
  ai_model: props.firewall.ai_model || '',
  ai_confidence_threshold: props.firewall.ai_confidence_threshold ?? 85,
  ai_sample_rate: props.firewall.ai_sample_rate ?? 0,
  ai_timeout_seconds: props.firewall.ai_timeout_seconds ?? 6,
  ai_cache_ttl_seconds: props.firewall.ai_cache_ttl_seconds ?? 900,
  ai_max_payload_chars: props.firewall.ai_max_payload_chars ?? 3000,
});

const toggleOptions = [
  { value: 1, label: __('ip_filter.status_active') },
  { value: 0, label: __('ip_filter.status_passive') },
];

const providers = computed(() => props.chatProviders || {});
const providersAvailable = computed(() => Object.keys(providers.value).length > 0);

const blacklistOptions = computed(() =>
  props.ipFilters
    .filter((filter) => filter.list_type === 'blacklist')
    .map((filter) => ({ value: filter.id, label: filter.name })),
);

const whitelistOptions = computed(() => [
  { value: '', label: __('firewall.select_rule') },
  ...props.ipFilters
    .filter((filter) => filter.list_type === 'whitelist')
    .map((filter) => ({ value: filter.id, label: filter.name })),
]);

const providerOptions = computed(() => [
  { value: '', label: __('firewall.ai_provider_default') },
  ...Object.entries(providers.value).map(([key, provider]) => ({
    value: key,
    label: provider?.label || key,
  })),
]);

const modelOptions = computed(() => [
  { value: '', label: __('firewall.ai_model_default') },
  ...(providers.value[form.ai_provider]?.models || []).map((model) => ({
    value: model.name,
    label: model.name,
  })),
]);

const aiLocked = computed(() => form.ai_review_enabled !== 1 || !providersAvailable.value);

/* Blade: sağlayıcı değişince eşleşmeyen model varsayılana düşer. */
watch(
  () => form.ai_provider,
  () => {
    if (!modelOptions.value.some((option) => option.value === form.ai_model)) {
      form.ai_model = '';
    }
  },
);

function submit() {
  form.post(route('admin.firewall.save'), { preserveScroll: true });
}
</script>

<template>
  <Head :title="__('firewall.firewall')" />

  <div class="flex flex-col gap-3.5 p-[22px]">
    <div class="p-card p-4">
      <div class="mb-3 font-display text-[13.5px] font-bold">{{ __('firewall.rules') }}</div>

      <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <FormField
          v-model.number="form.is_active"
          type="select"
          :label="__('ip_filter.status')"
          :error="form.errors.is_active"
          :options="toggleOptions"
        />
        <FormField
          v-model.number="form.blacklist_rule_id"
          type="select"
          :label="__('firewall.blacklist_rule_id')"
          :error="form.errors.blacklist_rule_id"
          :options="blacklistOptions"
        />
        <FormField
          v-model="form.whitelist_rule_id"
          type="select"
          :label="__('firewall.whitelist_rule_id')"
          :error="form.errors.whitelist_rule_id"
          :options="whitelistOptions"
        />

        <FormField
          v-for="check in CHECKS"
          :key="check"
          v-model.number="form[check]"
          type="select"
          :label="__(`firewall.${check}`)"
          :error="form.errors[check]"
          :options="toggleOptions"
        />

        <FormField
          v-model="form.bad_bots"
          :label="__('firewall.bad_bots')"
          :error="form.errors.bad_bots"
        />
      </div>
    </div>

    <div class="p-card p-4">
      <div class="font-display text-[13.5px] font-bold">{{ __('firewall.ai_section_title') }}</div>
      <p class="mt-1 text-[11.5px] leading-relaxed text-p-ink3">
        {{ __('firewall.ai_section_description') }}
      </p>

      <div
        v-if="!providersAvailable"
        class="mt-3 rounded-xl border border-p-warn/40 bg-p-warn/10 px-3 py-2 text-[12px] text-p-ink2"
      >
        {{ __('chatbot.no_provider_configured') }}
      </div>

      <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <FormField
          v-model.number="form.ai_review_enabled"
          type="select"
          :label="__('firewall.ai_review_enabled')"
          :error="form.errors.ai_review_enabled"
          :options="toggleOptions"
        />
        <FormField
          v-model.number="form.ai_enforcement_enabled"
          type="select"
          :label="__('firewall.ai_enforcement_enabled')"
          :error="form.errors.ai_enforcement_enabled"
          :options="toggleOptions"
        />

        <FormField
          v-model="form.ai_provider"
          type="select"
          :label="__('firewall.ai_provider')"
          :error="form.errors.ai_provider"
          :disabled="aiLocked"
          :options="providerOptions"
        />
        <FormField
          v-model="form.ai_model"
          type="select"
          :label="__('firewall.ai_model')"
          :error="form.errors.ai_model"
          :disabled="aiLocked"
          :options="modelOptions"
        />

        <FormField
          v-for="field in AI_NUMBERS"
          :key="field.key"
          v-model.number="form[field.key]"
          type="number"
          :label="__(`firewall.${field.key}`)"
          :error="form.errors[field.key]"
          :min="field.min"
          :max="field.max"
        />
      </div>
    </div>

    <div class="flex justify-end">
      <button class="p-btn-primary" :disabled="form.processing" @click="submit">
        <i class="fa-solid fa-floppy-disk text-xs"></i> {{ __('general.save') }}
      </button>
    </div>
  </div>
</template>
