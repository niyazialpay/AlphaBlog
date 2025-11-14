<?php

$themeAssetDir = env('THEME_ASSET_DIR');
$cssEntryEnv = env('THEME_CSS_ENTRY');
$jsEntryEnv = env('THEME_JS_ENTRY');
$packageDirEnv = env('THEME_PACKAGE_DIR');
$normalizedThemeAssetDir = null;

if (is_string($themeAssetDir) && $themeAssetDir !== '') {
    $trimmed = rtrim($themeAssetDir, "/\\");
    $normalizedThemeAssetDir = $trimmed === '' ? $themeAssetDir : $trimmed;
}

$themeAssetPath = static function (string $file, string $fallback) use ($themeAssetDir, $normalizedThemeAssetDir): string {
    if (! $themeAssetDir) {
        return $fallback;
    }

    $root = $normalizedThemeAssetDir ?? $themeAssetDir;
    $normalizedFile = ltrim($file, "/\\");

    if ($root === '/' || $root === '\\') {
        return $root.$normalizedFile;
    }

    return $root.'/'.$normalizedFile;
};

return [
    /*
    |--------------------------------------------------------------------------
    | Theme Renderer
    |--------------------------------------------------------------------------
    |
    | Determines how frontend themes are rendered. Available options:
    |  - blade: render Blade views under resources/views/themes/{theme}
    |  - vue:   render Vue components via Inertia using resources/js/{theme}
    |
    */
    'renderer' => env('THEME_RENDERER', 'blade'),

    'vue' => [
        /*
        |--------------------------------------------------------------------------
        | Vue Theme Namespace
        |--------------------------------------------------------------------------
        |
        | Allows overriding the namespace used when resolving Vue components.
        | By default the active theme name from the database will be used.
        |
        */
        'theme_namespace' => env('THEME_VUE_NAMESPACE'),

        /*
        |--------------------------------------------------------------------------
        | Vue Page Root
        |--------------------------------------------------------------------------
        |
        | The directory under the theme namespace where page components live.
        |
        */
        'page_root' => env('THEME_VUE_PAGE_ROOT', 'Pages'),
    ],

    'paths' => [
        'asset_dir' => $normalizedThemeAssetDir ?? $themeAssetDir,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Asset Entries
    |--------------------------------------------------------------------------
    |
    | Allow each installation to point to its own Vite entry files. If you keep
    | theme-specific CSS/JS outside the repository, point these variables to
    | the appropriate paths (relative to the project root).
    |
    */
    'assets' => [
        'css_entry' => ($cssEntryEnv !== null && $cssEntryEnv !== '') ? $cssEntryEnv : $themeAssetPath('app.css', 'resources/css/app.css'),
        'js_entry' => ($jsEntryEnv !== null && $jsEntryEnv !== '') ? $jsEntryEnv : $themeAssetPath('app.js', 'resources/js/app.js'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tailwind Configuration Override
    |--------------------------------------------------------------------------
    |
    | Provide an absolute or relative path to a Tailwind config file that should
    | replace the default one. Useful when each deployment ships its own theme
    | assets that live outside of version control.
    |
    */
    'tailwind' => [
        'config_path' => env('THEME_TAILWIND_CONFIG'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Node Package Directory
    |--------------------------------------------------------------------------
    |
    | Directory that contains the theme-specific package.json/node_modules.
    | When provided, tooling scripts can automatically install the theme
    | dependencies without bloating the core package.json.
    |
    */
    'packages' => [
        'directory' => ($packageDirEnv !== null && $packageDirEnv !== '') ? $packageDirEnv : ($normalizedThemeAssetDir ?? $themeAssetDir),
    ],
];
