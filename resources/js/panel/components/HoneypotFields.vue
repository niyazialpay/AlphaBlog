<script setup>
/**
 * Blade'deki `@honeypot` / `<x-honeypot />` direktifinin Vue karşılığı.
 *
 * `ProtectAgainstSpam` middleware'i (login, OTP, parola sıfırlama uçları) bu
 * alanları bekler; gönderilmezse istek sessizce boş sayfaya düşer — hata bile
 * dönmez, bu yüzden unutulması çok maliyetlidir.
 *
 * Alan adı `randomize_name_field_name` açıkken her render'da değiştiği için
 * değerler sunucudan prop olarak gelmek zorunda (App\Support\Panel\Panel::honeypot).
 */
defineProps({
  honeypot: { type: Object, required: true },
});
</script>

<template>
  <div v-if="honeypot.enabled" :name="honeypot.nameFieldName" style="display: none">
    <input :name="honeypot.nameFieldName" type="text" value="" :id="honeypot.nameFieldName" />
    <input :name="honeypot.validFromFieldName" type="text" :value="honeypot.encryptedValidFrom" />
  </div>
</template>
