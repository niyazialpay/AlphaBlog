#!/usr/bin/env node
import { execSync } from 'node:child_process';
import { existsSync, readFileSync, statSync } from 'node:fs';
import path from 'node:path';

const FORCE_INSTALL = process.argv.includes('--force');
const projectRoot = process.cwd();

hydrateEnvFromDotEnv();

const themeRoot =
    resolveThemePackageRoot(process.env.THEME_PACKAGE_DIR) ??
    resolveThemePackageRoot(process.env.THEME_ASSET_DIR) ??
    resolvePackageFromEntry(process.env.THEME_JS_ENTRY) ??
    resolvePackageFromEntry(process.env.THEME_CSS_ENTRY);

if (!themeRoot) {
    process.exit(0);
}

const packageJsonPath = path.join(themeRoot, 'package.json');

if (!existsSync(packageJsonPath)) {
    console.warn(`[theme] Skipping dependency install. package.json not found at ${packageJsonPath}`);
    process.exit(0);
}

if (!FORCE_INSTALL && !shouldInstall(themeRoot)) {
    process.exit(0);
}

console.log(`[theme] Installing npm dependencies for theme at ${themeRoot}`);

try {
    execSync('npm install', {
        cwd: themeRoot,
        stdio: 'inherit',
        env: process.env,
    });
} catch (error) {
    console.error('[theme] Failed to install theme dependencies.');
    process.exit(error.status ?? 1);
}

function resolveThemePackageRoot(rawPath) {
    if (!rawPath) {
        return null;
    }

    const resolved = path.resolve(projectRoot, rawPath);
    return resolved;
}

function resolvePackageFromEntry(entryPath) {
    if (!entryPath) {
        return null;
    }

    let dir = path.dirname(path.resolve(projectRoot, entryPath));

    while (dir && dir !== path.dirname(dir)) {
        if (dir === projectRoot) {
            return null;
        }

        const candidate = path.join(dir, 'package.json');
        if (existsSync(candidate)) {
            return dir;
        }

        dir = path.dirname(dir);
    }

    return null;
}

function shouldInstall(themeDir) {
    const nodeModulesPath = path.join(themeDir, 'node_modules');
    if (!existsSync(nodeModulesPath)) {
        return true;
    }

    const lockPath = path.join(themeDir, 'package-lock.json');
    if (!existsSync(lockPath)) {
        return false;
    }

    try {
        const lockTime = statSync(lockPath).mtimeMs;
        const modulesTime = statSync(nodeModulesPath).mtimeMs;
        return lockTime > modulesTime;
    } catch (error) {
        return true;
    }
}

function hydrateEnvFromDotEnv() {
    const envPath = path.join(projectRoot, '.env');
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

        const exportMatch = trimmed.startsWith('export ') ? trimmed.slice(7).trim() : trimmed;
        const equalsIndex = exportMatch.indexOf('=');
        if (equalsIndex === -1) {
            return;
        }

        const key = exportMatch.slice(0, equalsIndex).trim();
        if (!key || key.startsWith('#') || key in process.env) {
            return;
        }

        let value = exportMatch.slice(equalsIndex + 1).trim();
        const hasQuotes =
            (value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"));

        if (hasQuotes) {
            value = value.slice(1, -1);
        } else {
            const hashIndex = value.indexOf('#');
            if (hashIndex !== -1) {
                const before = value.slice(0, hashIndex);
                if (!before || /\s$/.test(before)) {
                    value = before.trim();
                }
            }
        }

        process.env[key] = value;
    });
}
