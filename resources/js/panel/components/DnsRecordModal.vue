<script setup>
/**
 * Cloudflare DNS kayıt ekle/düzenle modalı.
 *
 * Alanlar ve yük `cloudflare/dns.blade.php`'deki `content_html` JS üreticisi
 * ile birebir aynı: tür değişince içerik alanları değişir, `status` (proxied)
 * yalnızca A/AAAA/CNAME'de görünür.
 *
 * Uç sözleşmesi DEĞİŞMEZ: tek `cf.dns.save` route'u, `type: 'add'|'edit'` ve
 * düzenlemede `dns_id`. Düzenleme alanları blade'in `all_data` okumalarının
 * karşılığı olan `record.raw`'dan doldurulur.
 */
import { computed, reactive, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from './Modal.vue';
import FormField from './FormField.vue';
import { __ } from '../composables/useLang';

const props = defineProps({
  open: Boolean,
  record: { type: Object, default: null }, // null → ekle
});
const emit = defineEmits(['update:open', 'saved']);

const TYPES = ['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'SRV', 'HTTPS', 'CAA'];

const form = useForm({
  type: 'add',
  dns_id: '',
  record_type: 'A',
  name: '',
  content: '',
  ttl: 1,
  status: 1,
  priority: '',
  flags: '',
  tag: '',
  service: '',
  protocol: '_tcp',
  weight: '',
  port: '',
  target: '',
});

const blank = { ...form.data() };

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      return;
    }

    form.clearErrors();
    Object.assign(form, blank);

    const record = props.record;

    if (!record) {
      return;
    }

    const raw = record.raw || {};
    const data = raw.data || {};

    Object.assign(form, {
      type: 'edit',
      dns_id: record.id,
      record_type: record.type,
      name: record.type === 'SRV' ? data.name || record.name : record.name,
      ttl: record.ttl,
      status: record.proxied ? 1 : 0,
    });

    if (record.type === 'MX') {
      form.content = raw.content ?? '';
      form.priority = raw.priority ?? '';
    } else if (record.type === 'CAA') {
      form.content = data.value ?? '';
      form.tag = data.tag ?? '';
      form.flags = data.flags ?? '';
    } else if (record.type === 'SRV') {
      form.target = data.target ?? '';
      form.priority = data.priority ?? '';
      form.weight = data.weight ?? '';
      form.port = data.port ?? '';
      form.service = data.service ?? '';
      form.protocol = data.proto ?? '_tcp';
    } else if (record.type === 'HTTPS') {
      form.priority = data.priority ?? '';
      form.target = data.target ?? '';
      form.content = data.value ?? '';
    } else {
      form.content = raw.content ?? record.content ?? '';
    }
  },
);

const proxiable = computed(() => ['A', 'AAAA', 'CNAME'].includes(form.record_type));

function save() {
  form.post(route('cf.dns.save'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('update:open', false);
      emit('saved');
    },
  });
}
</script>

<template>
  <Modal
    :open="open"
    :title="record ? __('cloudflare.edit_record') : __('cloudflare.add_record')"
    icon="fa-brands fa-cloudflare"
    width="720px"
    @update:open="emit('update:open', $event)"
    @confirm="save"
  >
    <div class="grid grid-cols-2 gap-3">
      <FormField
        v-model="form.record_type"
        :label="__('cloudflare.type')"
        type="select"
        :options="TYPES"
      />
      <FormField
        v-model="form.name"
        :label="__('cloudflare.name')"
        :placeholder="__('cloudflare.name')"
      />

      <template v-if="form.record_type === 'MX'">
        <FormField v-model.number="form.priority" :label="__('cloudflare.priority')" type="number" />
        <FormField v-model="form.content" :label="__('cloudflare.content')" />
      </template>

      <template v-else-if="form.record_type === 'TXT'">
        <FormField v-model="form.content" :label="__('cloudflare.content')" type="textarea" full />
      </template>

      <template v-else-if="form.record_type === 'CAA'">
        <FormField v-model.number="form.flags" :label="__('cloudflare.flags')" type="number" />
        <FormField v-model="form.tag" :label="__('cloudflare.tag')" placeholder="issue" />
        <FormField v-model="form.content" :label="__('cloudflare.content')" full />
      </template>

      <template v-else-if="form.record_type === 'SRV'">
        <FormField v-model.number="form.priority" :label="__('cloudflare.priority')" type="number" />
        <FormField v-model.number="form.weight" :label="__('cloudflare.weight')" type="number" />
        <FormField v-model.number="form.port" :label="__('cloudflare.port')" type="number" />
        <FormField v-model="form.service" :label="__('cloudflare.service')" placeholder="_sip" />
        <FormField
          v-model="form.protocol"
          :label="__('cloudflare.protocol')"
          type="select"
          :options="[
            { value: '_tls', label: 'TLS' },
            { value: '_tcp', label: 'TCP' },
            { value: '_udp', label: 'UDP' },
          ]"
        />
        <FormField v-model="form.target" :label="__('cloudflare.target')" full />
      </template>

      <template v-else-if="form.record_type === 'HTTPS'">
        <FormField v-model.number="form.priority" :label="__('cloudflare.priority')" type="number" />
        <FormField v-model="form.target" :label="__('cloudflare.target')" />
        <FormField v-model="form.content" :label="__('cloudflare.content')" full />
      </template>

      <template v-else>
        <FormField v-model="form.content" :label="__('cloudflare.content')" full />
      </template>

      <FormField v-model.number="form.ttl" :label="__('cloudflare.ttl')" type="number" :min="1" />
      <FormField
        v-if="proxiable"
        v-model.number="form.status"
        :label="__('cloudflare.proxied')"
        type="select"
        :options="[
          { value: 1, label: __('general.active') },
          { value: 0, label: __('general.passive') },
        ]"
      />
    </div>
  </Modal>
</template>
