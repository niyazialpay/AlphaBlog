<?php

$themeAssetDir = env('THEME_ASSET_DIR');
$cssEntryEnv = env('THEME_CSS_ENTRY');
$jsEntryEnv = env('THEME_JS_ENTRY');
$packageDirEnv = env('THEME_PACKAGE_DIR');
$normalizedThemeAssetDir = null;

if (is_string($themeAssetDir) && $themeAssetDir !== '') {
    $trimmed = rtrim($themeAssetDir, '/\\');
    $normalizedThemeAssetDir = $trimmed === '' ? $themeAssetDir : $trimmed;
}

$themeAssetPath = static function (string $file, string $fallback) use ($themeAssetDir, $normalizedThemeAssetDir): string {
    if (! $themeAssetDir) {
        return $fallback;
    }

    $root = $normalizedThemeAssetDir ?? $themeAssetDir;
    $normalizedFile = ltrim($file, '/\\');

    if ($root === '/' || $root === '\\') {
        return $root.$normalizedFile;
    }

    return $root.'/'.$normalizedFile;
};

return [
    'renderer' => env('THEME_RENDERER', 'blade'),

    'vue' => [
        'theme_namespace' => env('THEME_VUE_NAMESPACE'),

        'page_root' => env('THEME_VUE_PAGE_ROOT', 'Pages'),
    ],

    'paths' => [
        'asset_dir' => $normalizedThemeAssetDir ?? $themeAssetDir,
    ],

    'assets' => [
        'css_entry' => ($cssEntryEnv !== null && $cssEntryEnv !== '') ? $cssEntryEnv : $themeAssetPath('app.css', 'resources/css/app.css'),
        'js_entry' => ($jsEntryEnv !== null && $jsEntryEnv !== '') ? $jsEntryEnv : $themeAssetPath('app.js', 'resources/js/app.js'),
    ],

    'tailwind' => [
        'config_path' => env('THEME_TAILWIND_CONFIG'),
    ],

    'packages' => [
        'directory' => ($packageDirEnv !== null && $packageDirEnv !== '') ? $packageDirEnv : ($normalizedThemeAssetDir ?? $themeAssetDir),
    ],
];
