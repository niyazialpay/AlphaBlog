import { ref } from 'vue';

const KEY = 'alphablog-panel-theme';
export const theme = ref('light');

function apply(value) {
  theme.value = value;
  document.documentElement.setAttribute('data-panel-theme', value);
  localStorage.setItem(KEY, value);
}

/** Sistem tercihi varsayılan; kullanıcı seçimi localStorage'da tutulur. */
export function initTheme() {
  const saved = localStorage.getItem(KEY);
  const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  apply(saved || system);

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem(KEY)) apply(e.matches ? 'dark' : 'light');
  });
}

export function toggleTheme() {
  apply(theme.value === 'dark' ? 'light' : 'dark');
}

export function useTheme() {
  return { theme, toggleTheme };
}
