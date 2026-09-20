<script setup>
/**
 * Sunucuda üretilmiş diff HTML'ini basan TEK nokta.
 *
 * Panelde başka hiçbir yerde `v-html` yok: prop'lar veri taşır, markup değil.
 * Buradaki istisna bilinçli — `Qazd\TextDiff::render()` <ins>/<del> işaretlemesi
 * üretir ve bir diff motorunu Vue'da yeniden yazmak port değil yeniden yazım
 * olurdu. İçerik eski Blade ekranında da `{!! !!}` ile basılıyordu, yani yeni
 * bir açık değil; yine de tek bir yere hapsedildi ki denetlenebilir kalsın.
 */
defineProps({
  html: { type: String, default: '' },
});
</script>

<template>
  <div class="diff-html mt-1.5 overflow-x-auto text-[12.5px] leading-relaxed" v-html="html"></div>
</template>

<style>
.diff-html ins {
  background: rgb(var(--p-ok) / 0.15);
  color: rgb(var(--p-ok));
  text-decoration: none;
  padding: 0 2px;
  border-radius: 3px;
}

.diff-html del {
  background: rgb(var(--p-danger) / 0.15);
  color: rgb(var(--p-danger));
  padding: 0 2px;
  border-radius: 3px;
}
</style>
