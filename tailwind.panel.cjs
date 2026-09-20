/**
 * Panel Tailwind yapılandırması — BAĞIMSIZ.
 *
 * Kök `tailwind.config.js` aktif temanın `tailwind.theme.cjs`'ini yükler ve dizileri
 * REPLACE eder; bu yüzden tema `content`'i temel glob'ları eziyor ve panel sınıfları
 * purge ediliyordu. Ayrıca tema `darkMode`'u `[data-theme="dark"]`, panelinki
 * `[data-panel-theme="dark"]` — tek config ikisini birden tutamaz.
 *
 * Çözüm: `resources/css/panel.css` ilk satırında `@config "../../tailwind.panel.cjs";`
 * ile panel kendi Tailwind örneğini alır. Tema mekanizmasına hiç dokunulmaz.
 *
 * DİKKAT: `postcss.config.js` içindeki `tailwindcss` eklentisi `config` seçeneği
 * ALMAMALI — aksi halde `@config` sessizce yok sayılır.
 *
 * CommonJS zorunlu (Tailwind config'leri senkron `require` ile yüklenir).
 */
const defaultTheme = require('tailwindcss/defaultTheme');

const withVar = (v) => ({ opacityValue }) =>
  opacityValue === undefined ? `rgb(var(${v}))` : `rgb(var(${v}) / ${opacityValue})`;

module.exports = {
  darkMode: ['selector', '[data-panel-theme="dark"]'],
  content: [
    // Yalnızca Inertia kabuğu. Eski AdminLTE blade'leri BİLİNÇLİ olarak taranmaz:
    // içlerindeki Bootstrap sınıfları (table, border, fixed, col-12…) geçerli Tailwind
    // utility'lerine çarpıp bundle'ı kirletir ve panel CSS'ini silinecek dosyalara bağlar.
    './resources/views/panel/app.blade.php',
    './resources/js/panel/**/*.{vue,js}',
    // Modül panel arayüzleri kendi dizinlerinde yaşar; modül yoksa glob boş geçer.
    './Modules/*/resources/js/panel/**/*.{vue,js}',
  ],
  theme: {
    extend: {
      colors: {
        'p-bg': withVar('--p-bg'),
        'p-panel': withVar('--p-panel'),
        'p-panel2': withVar('--p-panel2'),
        'p-rail': withVar('--p-rail'),
        'p-railink': withVar('--p-railink'),
        'p-ink': withVar('--p-ink'),
        'p-ink2': withVar('--p-ink2'),
        'p-ink3': withVar('--p-ink3'),
        'p-line': withVar('--p-line'),
        'p-line2': withVar('--p-line2'),
        'p-accent': withVar('--p-accent'),
        'p-accent-ink': withVar('--p-accent-ink'),
        'p-soft': withVar('--p-soft'),
        'p-cyan': withVar('--p-cyan'),
        'p-ok': withVar('--p-ok'),
        'p-warn': withVar('--p-warn'),
        'p-danger': withVar('--p-danger'),
        'p-chip': withVar('--p-chip'),
      },
      fontFamily: {
        sans: ['Public Sans', ...defaultTheme.fontFamily.sans],
        display: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
        mono: ['ui-monospace', 'SFMono-Regular', ...defaultTheme.fontFamily.mono],
      },
      boxShadow: {
        panel: '0 1px 2px rgba(16,20,40,.06), 0 8px 24px -16px rgba(16,20,40,.25)',
        pop: '0 30px 80px -20px rgba(0,0,0,.45)',
      },
      keyframes: {
        popIn: { from: { opacity: 0, transform: 'translateY(6px) scale(.98)' }, to: { opacity: 1, transform: 'none' } },
        slideIn: { from: { opacity: 0, transform: 'translateX(24px)' }, to: { opacity: 1, transform: 'none' } },
      },
      animation: {
        popIn: 'popIn .16s ease',
        slideIn: 'slideIn .18s ease',
      },
    },
  },
  // `@config` MERGE etmez, REPLACE eder: kök config'in plugin'leri miras alınmaz.
  // TinyMCE içerik önizlemeleri `prose` kullanacağı için typography burada da gerekli.
  plugins: [require('@tailwindcss/typography')],
};
