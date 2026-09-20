<script setup>
/**
 * Sunucuda üretilmiş diff HTML'ini basan TEK nokta.
 *
 * Panelde başka hiçbir yerde `v-html` yok: prop'lar veri taşır, markup değil.
 * Buradaki istisna bilinçli — `Qazd\TextDiff::render()` <ins>/<del> işaretlemesi
 * üretir ve bir diff motorunu Vue'da yeniden yazmak port değil yeniden yazım
 * olurdu. İçerik eski Blade ekranında da `{!! !!}` ile basılıyordu, yani yeni
 * bir açık değil; yine de tek bir yere hapsedildi ki denetlenebilir kalsın.
 *
 * `TextDiff::render()` varsayılan olarak `WP_Text_Diff_Renderer_Table`'ı
 * split-view (yanyana) modda kullanır: `<table class="diff"><tbody>` içinde
 * her satır [eski hücre][boş ayraç][yeni hücre] üçlüsü — `.diff-deletedline`
 * / `.diff-addedline` / `.diff-context` td sınıflarıyla. Aşağıdaki stil bu
 * tabloyu panelin `p-*` token'larıyla görünür kılar; eski Blade ekranındaki
 * `table.diff` CSS'inin (bkz. git geçmişi: panel/post/history/show.blade.php)
 * panele taşınmış hali. İçerik satır içi HTML her zaman `htmlspecialchars`
 * ile kaçırılmış olarak gelir (render() içinde), yani burada asla markup
 * çalıştırılmaz — sadece kaynak metin olarak görüntülenir.
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

/*
 * `table.diff` düzeni: [eski][ayraç][yeni] üç sütun, TextDiff'in ürettiği
 * ham HTML. Genişlik dar ekranda `.diff-html`'in `overflow-x-auto`'suyla
 * yatay kaydırılır (min-width bunu garanti eder), sığdığında iki sütun
 * yanyana eşit genişlikte durur.
 */
.diff-html table.diff {
  width: 100%;
  min-width: 600px;
  table-layout: fixed;
  border-collapse: collapse;
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 12px;
}

.diff-html table.diff td {
  padding: 4px 8px;
  vertical-align: top;
  white-space: pre-wrap;
  word-break: break-word;
  border-top: 1px solid rgb(var(--p-line2));
}

.diff-html table.diff tr:first-child td {
  border-top: none;
}

.diff-html table.diff td:nth-child(1),
.diff-html table.diff td:nth-child(3) {
  width: calc((100% - 26px) / 2);
}

.diff-html table.diff td:nth-child(2) {
  width: 26px;
  padding: 4px 0;
}

.diff-html table.diff .diff-context {
  color: rgb(var(--p-ink2));
}

.diff-html table.diff .diff-deletedline {
  background: rgb(var(--p-danger) / 0.08);
}

.diff-html table.diff .diff-addedline {
  background: rgb(var(--p-ok) / 0.08);
}
</style>
