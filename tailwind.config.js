import typography from '@tailwindcss/typography';
import tailwindcssAnimate from 'tailwindcss-animate';
import defaultTheme from 'tailwindcss/defaultTheme.js';
import { existsSync, readFileSync, statSync } from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';

const baseConfig = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
        './resources/js/**/*.ts',
        './resources/js/**/*.tsx',
        './app/View/Components/**/*.php',
        './Modules/**/resources/views/**/*.blade.php',
        './Modules/**/resources/js/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        container: {
            center: true,
            padding: '1.5rem',
            screens: {
                '2xl': '1280px',
            },
        },
        extend: {
            colors: {
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
            boxShadow: {
                glow: '0 20px 45px -25px rgba(15, 118, 110, 0.35)',
            },
        },
    },
    plugins: [typography, tailwindcssAnimate],
};

hydrateThemeEnvFromDotEnv();

const themeConfig = loadThemeConfig(resolveThemeConfigPath());
const baseClone = cloneConfig(baseConfig);
const finalConfig = themeConfig ? resolveThemeConfig(themeConfig, baseClone) : baseConfig;

export default finalConfig;

/**
 * PostCSS, bu config'i Vite build process'i içinde yükler; ancak Vite `loadEnv`
 * THEME_* değişkenlerini yalnızca yerel bir nesneye alır, process.env'e yazmaz.
 * Bu yüzden .env'deki THEME_* anahtarlarını burada process.env'e taşıyoruz
 * (yalnızca THEME_ prefix'i — bundle'a sır sızmasın diye diğer anahtarlar atlanır).
 */
function hydrateThemeEnvFromDotEnv() {
    const envPath = path.resolve(process.cwd(), '.env');
    if (!existsSync(envPath)) {
        return;
    }

    let raw;

    try {
        raw = readFileSync(envPath, 'utf8');
    } catch {
        return;
    }

    raw.split(/\r?\n/).forEach((line) => {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            return;
        }

        const body = trimmed.startsWith('export ') ? trimmed.slice(7).trim() : trimmed;
        const equalsIndex = body.indexOf('=');
        if (equalsIndex === -1) {
            return;
        }

        const key = body.slice(0, equalsIndex).trim();
        if (!key || !key.startsWith('THEME_') || key in process.env) {
            return;
        }

        let value = body.slice(equalsIndex + 1).trim();
        if (
            (value.startsWith('"') && value.endsWith('"')) ||
            (value.startsWith("'") && value.endsWith("'"))
        ) {
            value = value.slice(1, -1);
        }

        process.env[key] = value;
    });
}

/**
 * Tema Tailwind override yolunu çözer. Önce açık THEME_TAILWIND_CONFIG; yoksa
 * aktif tema giriş yollarından (CSS/JS entry, asset dir) tema kökünü bulup orada
 * `tailwind.theme.cjs` arar. Böylece her tema, ayrı bir env değişkeni gerekmeden
 * kendi Tailwind genişletmelerini otomatik yükler; dosya yoksa baseConfig kullanılır.
 */
function resolveThemeConfigPath() {
    if (process.env.THEME_TAILWIND_CONFIG) {
        return process.env.THEME_TAILWIND_CONFIG;
    }

    const entries = [
        process.env.THEME_CSS_ENTRY,
        process.env.THEME_JS_ENTRY,
        process.env.THEME_ASSET_DIR,
    ].filter(Boolean);

    for (const entry of entries) {
        const base = path.resolve(process.cwd(), entry);

        let dir;
        try {
            dir = existsSync(base) && statSync(base).isDirectory() ? base : path.dirname(base);
        } catch {
            dir = path.dirname(base);
        }

        const candidate = path.join(dir, 'tailwind.theme.cjs');
        if (existsSync(candidate)) {
            return candidate;
        }
    }

    return null;
}

function loadThemeConfig(configPathValue) {
    if (!configPathValue) {
        return null;
    }

    const resolvedPath = path.isAbsolute(configPathValue)
        ? configPathValue
        : path.resolve(process.cwd(), configPathValue);

    if (!existsSync(resolvedPath)) {
        console.warn(`[theme] Tailwind config "${resolvedPath}" not found. Falling back to default config.`);
        return null;
    }

    try {
        const requireModule = createRequire(import.meta.url);
        const loadedModule = requireModule(resolvedPath);

        if (loadedModule && typeof loadedModule === 'object') {
            return 'default' in loadedModule ? loadedModule.default : loadedModule;
        }

        return loadedModule;
    } catch (error) {
        if (error?.code === 'ERR_REQUIRE_ESM') {
            console.error(
                `[theme] "${resolvedPath}" is treated as an ES module. Theme Tailwind overrides must currently be CommonJS (e.g. tailwind.theme.cjs).`,
            );
        } else {
            console.error(`[theme] Unable to load Tailwind config "${resolvedPath}". Using default config instead.`);
            console.error(error);
        }

        return null;
    }
}

function resolveThemeConfig(themeConfigValue, baseConfig) {
    if (typeof themeConfigValue === 'function') {
        return themeConfigValue(baseConfig) ?? baseConfig;
    }

    if (isPlainObject(themeConfigValue)) {
        return mergeConfig(baseConfig, themeConfigValue);
    }

    return baseConfig;
}

function cloneConfig(source) {
    return mergeConfig({}, source);
}

function mergeConfig(target, source) {
    if (!isPlainObject(source)) {
        return target;
    }

    for (const [key, value] of Object.entries(source)) {
        if (isUnsafeKey(key)) {
            continue;
        }

        if (Array.isArray(value)) {
            target[key] = value.slice();
            continue;
        }

        if (isPlainObject(value)) {
            target[key] = mergeConfig(isPlainObject(target[key]) ? target[key] : {}, value);
            continue;
        }

        target[key] = value;
    }

    return target;
}

function isUnsafeKey(key) {
    return key === '__proto__' || key === 'constructor' || key === 'prototype';
}

function isPlainObject(value) {
    return typeof value === 'object' && value !== null && Object.getPrototypeOf(value) === Object.prototype;
}
