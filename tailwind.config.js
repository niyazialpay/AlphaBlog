import typography from '@tailwindcss/typography';
import tailwindcssAnimate from 'tailwindcss-animate';
import defaultTheme from 'tailwindcss/defaultTheme';
import { existsSync } from 'node:fs';
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

const themeConfig = loadThemeConfig(process.env.THEME_TAILWIND_CONFIG);
const baseClone = cloneConfig(baseConfig);
const finalConfig = themeConfig ? resolveThemeConfig(themeConfig, baseClone) : baseConfig;

export default finalConfig;

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

function isPlainObject(value) {
    return typeof value === 'object' && value !== null && Object.getPrototypeOf(value) === Object.prototype;
}
