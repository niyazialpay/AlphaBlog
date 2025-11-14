import typography from '@tailwindcss/typography';
import tailwindcssAnimate from 'tailwindcss-animate';
import defaultTheme from 'tailwindcss/defaultTheme.js';
import { existsSync, promises as fsPromises } from 'node:fs';
import path from 'node:path';
import { createHash } from 'node:crypto';
import { pathToFileURL } from 'node:url';

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

const themeConfig = await loadThemeConfig(process.env.THEME_TAILWIND_CONFIG);
const baseClone = cloneConfig(baseConfig);
const finalConfig = themeConfig ? resolveThemeConfig(themeConfig, baseClone) : baseConfig;

export default finalConfig;

async function loadThemeConfig(configPathValue) {
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

    const imported = await importEsmThemeConfig(resolvedPath);
    if (imported !== null) {
        return imported;
    }

    console.error(`[theme] Unable to load Tailwind config "${resolvedPath}". Using default config instead.`);
    return null;
}

async function importEsmThemeConfig(resolvedPath) {
    const previousEnv = process.env.THEME_TAILWIND_CONFIG;
    const normalizedEnvPath = previousEnv
        ? path.resolve(process.cwd(), previousEnv)
        : null;
    const shouldUnsetEnv = normalizedEnvPath === resolvedPath;

    try {
        if (shouldUnsetEnv) {
            delete process.env.THEME_TAILWIND_CONFIG;
        }

        const preparedPath = await ensureEsmSpecifiers(resolvedPath);
        const loadedModule = await import(pathToFileURL(preparedPath).href);
        return unwrapModule(loadedModule);
    } catch (error) {
        console.error(`[theme] Unable to import Tailwind config "${resolvedPath}" as an ES module.`);
        console.error(error);
        return null;
    } finally {
        if (shouldUnsetEnv) {
            process.env.THEME_TAILWIND_CONFIG = previousEnv;
        }
    }
}

async function ensureEsmSpecifiers(originalPath) {
    const content = await fsPromises.readFile(originalPath, 'utf8');
    let mutated = content;
    let changed = false;

    const specifiers = ['defaultTheme', 'defaultConfig', 'colors'];
    for (const segment of specifiers) {
        const pattern = new RegExp(`(['"])tailwindcss\\/${segment}(\\.js)?\\1`, 'g');
        mutated = mutated.replace(pattern, (match, quote, hasExtension) => {
            if (hasExtension) {
                return match;
            }

            changed = true;
            return `${quote}tailwindcss/${segment}.js${quote}`;
        });
    }

    if (!changed) {
        return originalPath;
    }

    const cacheDir = path.join(process.cwd(), 'storage', 'framework', 'cache', 'theme-tailwind');
    await fsPromises.mkdir(cacheDir, { recursive: true });

    const hash = createHash('sha1').update(originalPath).update(content).digest('hex');
    const tempPath = path.join(cacheDir, `theme-${hash}.mjs`);
    await fsPromises.writeFile(tempPath, mutated, 'utf8');

    return tempPath;
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

function unwrapModule(moduleValue) {
    if (moduleValue && typeof moduleValue === 'object' && 'default' in moduleValue && moduleValue.default) {
        return moduleValue.default;
    }

    return moduleValue;
}
