<?php

namespace Tests\Feature\Panel;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;

class SharedLanguagesPropTest extends PanelTestCase
{
    /**
     * @return array<string, array{0: string, 1: array<string, mixed>, 2: string, 3: string}>
     */
    public static function shadowingScreens(): array
    {
        return [
            'categories' => ['admin.categories', [], 'Categories/Index', 'languageOptions'],
            'posts.create' => ['admin.post.create', ['type' => 'blogs'], 'Posts/Edit', 'languageOptions'],
            'settings' => ['admin.settings', [], 'Settings/Index', 'languageRecords'],
            'menu' => ['admin.menu.index', [], 'Menu/Index', 'languageOptions'],
        ];
    }

    /**
     * @param  mixed  $shared
     */
    private function assertSharedLanguageList($shared): void
    {
        $this->assertIsArray($shared);
        $this->assertNotEmpty($shared, 'Paylasilan `languages` prop\'u bos geldi.');
        $this->assertSame(range(0, count($shared) - 1), array_keys($shared));

        foreach ($shared as $language) {
            $this->assertIsArray($language);
            $this->assertArrayHasKey('code', $language);
            $this->assertArrayHasKey('name', $language);
            $this->assertArrayHasKey('flag', $language);
        }

        $this->assertSame($this->language->flag, $shared[0]['flag']);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    #[DataProvider('shadowingScreens')]
    public function test_screen_keeps_the_shared_flag_carrying_languages_prop(
        string $routeName,
        array $parameters,
        string $component,
        string $pageProp,
    ): void {
        $this->migrateScreens([$routeName]);

        $response = $this->actingAs($this->owner)->get(route($routeName, $parameters));

        $response->assertOk();

        $page = $response->viewData('page');

        $this->assertSame($component, $page['component']);

        $this->assertSharedLanguageList($page['props']['languages']);

        $this->assertArrayHasKey($pageProp, $page['props']);
        $this->assertIsArray($page['props'][$pageProp]);
        $this->assertNotEmpty($page['props'][$pageProp]);
        $this->assertSame($this->language->code, $page['props'][$pageProp][0]['code']);
    }

    public function test_shared_current_language_survives_on_a_fixed_screen(): void
    {
        $this->migrateScreens(['admin.categories']);

        $props = $this->actingAs($this->owner)
            ->get(route('admin.categories'))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertArrayHasKey('currentLanguage', $props);
        $this->assertArrayHasKey('flag', $props['currentLanguage']);
    }

    private function healEmptyLanguageRouteConstraints(): void
    {
        foreach (Route::getRoutes() as $route) {
            if (($route->wheres['language'] ?? null) === '') {
                $route->where('language', $this->language->code);
            }
        }
    }

    public function test_valefix_theme_screen_keeps_the_shared_languages_prop(): void
    {
        if (Route::getRoutes()->getByName('panel.valefix.theme.index') === null) {
            $this->markTestSkipped('ValeFix modulu bu kurulumda etkin degil.');
        }

        $this->healEmptyLanguageRouteConstraints();

        $this->migrateScreens(['panel.valefix.theme.index']);

        $response = $this->actingAs($this->owner)->get(route('panel.valefix.theme.index'));

        $response->assertOk();

        $page = $response->viewData('page');

        $this->assertSame('valefix::ThemeContent', $page['component']);

        $this->assertSharedLanguageList($page['props']['languages']);

        $this->assertArrayHasKey('languageOptions', $page['props']);
        $this->assertSame($this->language->code, $page['props']['languageOptions'][0]['code']);
    }
}
