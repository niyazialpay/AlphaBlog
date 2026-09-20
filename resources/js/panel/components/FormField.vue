<script setup>
/** Tek tip form alanı: text / number / password / email / select / textarea / datetime-local */
const props = defineProps({
  modelValue: [String, Number, Boolean, null],
  /*
   * Vue 3 bileşen v-model'inde `.number` / `.trim` otomatik uygulanmaz —
   * modifier'lar `modelModifiers` prop'u olarak gelir ve bileşenin kendisi
   * uygulamak zorundadır. Uygulanmazsa `<select>` her zaman string döner ve
   * `form.code === 403` gibi karşılaştırmalar sessizce false olur.
   */
  modelModifiers: { type: Object, default: () => ({}) },
  label: String,
  type: { type: String, default: 'text' },
  options: { type: Array, default: () => [] }, // [{ value, label }] veya [string]
  placeholder: String,
  help: String,
  error: String,
  full: Boolean,
  disabled: Boolean,
  min: [String, Number],
  max: [String, Number],
  step: [String, Number],
});
const emit = defineEmits(['update:modelValue']);
const opt = (o) => (typeof o === 'object' ? o : { value: o, label: o });

function cast(value) {
  if (props.modelModifiers.trim && typeof value === 'string') {
    value = value.trim();
  }

  if (props.modelModifiers.number || props.type === 'number') {
    if (value === '' || value === null) {
      return value;
    }

    const parsed = Number(value);

    return Number.isNaN(parsed) ? value : parsed;
  }

  return value;
}

const update = (event) => emit('update:modelValue', cast(event.target.value));
</script>

<template>
  <div :class="full && 'col-span-full'">
    <label v-if="label" class="p-label">{{ label }}</label>

    <select v-if="type === 'select'" class="p-input" :disabled="disabled"
            :value="modelValue" @change="update">
      <option v-for="o in options.map(opt)" :key="o.value" :value="o.value">{{ o.label }}</option>
    </select>

    <textarea v-else-if="type === 'textarea'" class="p-textarea" :placeholder="placeholder"
              :disabled="disabled"
              :value="modelValue" @input="update"></textarea>

    <input v-else class="p-input" :type="type" :placeholder="placeholder"
           :disabled="disabled" :min="min" :max="max" :step="step"
           :value="modelValue" @input="update">

    <div v-if="help" class="mt-1.5 text-[11px] leading-relaxed text-p-ink3">{{ help }}</div>
    <div v-if="error" class="mt-1.5 text-[11px] font-semibold text-p-danger">{{ error }}</div>
  </div>
</template>
