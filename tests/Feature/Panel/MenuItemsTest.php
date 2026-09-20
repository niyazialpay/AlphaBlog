<?php

namespace Tests\Feature\Panel;

use App\Models\Menu\Menu;
use App\Models\Menu\MenuItems;

/**
 * "Menü Öğeleri" ekranının iki regresyonu.
 *
 * B6 — PROP GÖLGELEME: `HandlePanelInertiaRequests` sidebar bölümlerini `menu`
 * adıyla paylaşıyor. Inertia'da sayfa prop'ları aynı adlı paylaşılan prop'u
 * EZDİĞİ için bu ekranın `menu` adlı sayfa prop'u sol menüyü yok ediyordu
 * (PanelLayout'ta `sections` dizi yerine nesne alıyor, `sections.find()`
 * TypeError fırlatıyor ve layout alt ağacı komple düşüyordu).
 *
 * B7 — SINIRSIZ DERİNLİK: Vue kurucusu yalnızca düz sıralama yapabiliyordu;
 * sunucu sözleşmesi ise zaten özyinelemeli. Burada sözleşmenin ucu uca
 * çalıştığı (kaydet → oku) doğrulanır.
 */
class MenuItemsTest extends PanelTestCase
{
    private function menu(): Menu
    {
        return Menu::create([
            'title' => 'Ana Menü',
            'menu_position' => 'header',
            'language' => $this->language->code,
        ]);
    }

    /**
     * Sunucunun `updateMenu()` içinde KORUMASIZ okuduğu altı anahtar.
     *
     * @param  list<array<string, mixed>>  $children
     * @return array<string, mixed>
     */
    private function node(string $title, string $url, array $children = []): array
    {
        return [
            'title' => $title,
            'url' => $url,
            'language' => $this->language->code,
            'icon' => '',
            'nav_target' => '_self',
            'menu_type' => 'standard',
            'children' => $children,
        ];
    }

    public function test_menu_items_screen_keeps_the_shared_sidebar_menu_prop(): void
    {
        $this->migrateScreens(['admin.menu.show']);

        $menu = $this->menu();

        $response = $this->actingAs($this->owner)->get(route('admin.menu.show', ['menu' => $menu->id]));

        $response->assertOk();

        $props = $response->viewData('page')['props'];

        $this->assertSame('Menu/Items', $response->viewData('page')['component']);

        // Paylaşılan sidebar prop'u: bölüm listesi, menü kaydı DEĞİL.
        $this->assertIsArray($props['menu']);
        $this->assertArrayNotHasKey('menu_position', $props['menu']);

        foreach ($props['menu'] as $section) {
            $this->assertArrayHasKey('key', $section);
            $this->assertArrayHasKey('items', $section);
        }

        // Ekranın kendi kaydı ayrı bir ad altında.
        $this->assertSame($menu->id, $props['menuRecord']['id']);
        $this->assertSame('Ana Menü', $props['menuRecord']['title']);
    }

    public function test_menu_index_screen_keeps_the_shared_sidebar_menu_prop(): void
    {
        $this->migrateScreens(['admin.menu.index']);

        $menu = $this->menu();

        $response = $this->actingAs($this->owner)->get(route('admin.menu.index', ['menu' => $menu->id]));

        $response->assertOk();

        $props = $response->viewData('page')['props'];

        $this->assertSame('Menu/Index', $response->viewData('page')['component']);
        $this->assertIsArray($props['menu']);
        $this->assertArrayNotHasKey('menu_position', $props['menu']);
        $this->assertSame($menu->id, $props['menuRecord']['id']);
    }

    public function test_save_persists_a_tree_of_arbitrary_depth(): void
    {
        $menu = $this->menu();

        $tree = [
            $this->node('Kurumsal', '/kurumsal', [
                $this->node('Hakkımızda', '/hakkimizda', [
                    $this->node('Tarihçe', '/tarihce', [
                        $this->node('1990', '/1990'),
                    ]),
                ]),
                $this->node('İletişim', '/iletisim'),
            ]),
            $this->node('Blog', '/blog'),
        ];

        $this->actingAs($this->owner)
            ->post(route('admin.menu-item.save'), [
                'menu_id' => $menu->id,
                'menu' => json_encode($tree),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $root = MenuItems::where('menu_id', $menu->id)->whereNull('parent_id')->orderBy('order')->get();

        $this->assertCount(2, $root);
        $this->assertSame(['Kurumsal', 'Blog'], $root->pluck('title')->all());
        $this->assertSame([0, 1], $root->pluck('order')->all());

        $kurumsal = $root->first();
        $level2 = MenuItems::where('parent_id', $kurumsal->id)->orderBy('order')->get();

        $this->assertSame(['Hakkımızda', 'İletişim'], $level2->pluck('title')->all());

        $level3 = MenuItems::where('parent_id', $level2->first()->id)->get();
        $this->assertSame(['Tarihçe'], $level3->pluck('title')->all());

        $level4 = MenuItems::where('parent_id', $level3->first()->id)->get();
        $this->assertSame(['1990'], $level4->pluck('title')->all());
    }

    public function test_item_tree_prop_mirrors_the_saved_nesting_and_order(): void
    {
        $this->migrateScreens(['admin.menu.show']);

        $menu = $this->menu();

        $tree = [
            $this->node('Kurumsal', '/kurumsal', [
                $this->node('Hakkımızda', '/hakkimizda', [
                    $this->node('Tarihçe', '/tarihce'),
                ]),
            ]),
            $this->node('Blog', '/blog'),
        ];

        $this->actingAs($this->owner)->post(route('admin.menu-item.save'), [
            'menu_id' => $menu->id,
            'menu' => json_encode($tree),
        ])->assertOk();

        $props = $this->actingAs($this->owner)
            ->get(route('admin.menu.show', ['menu' => $menu->id]))
            ->assertOk()
            ->viewData('page')['props'];

        $this->assertSame(['Kurumsal', 'Blog'], array_column($props['tree'], 'title'));

        $kurumsal = $props['tree'][0];
        $this->assertSame('Hakkımızda', $kurumsal['children'][0]['title']);
        $this->assertSame('Tarihçe', $kurumsal['children'][0]['children'][0]['title']);

        // Sunucunun korumasız okuduğu anahtarlar geri de geliyor: istemci aynı
        // düğümü hiçbir alanı kaybetmeden tekrar POST edebilmeli.
        foreach (['title', 'url', 'language', 'icon', 'nav_target', 'menu_type', 'children'] as $key) {
            $this->assertArrayHasKey($key, $kurumsal);
        }
    }

    /**
     * `menuTree()` (Blade kabuğu) `order` yerine `id`'ye göre sıralıyordu.
     * Satırlar her kayıtta silinip yeniden yaratıldığı için kazara çalışıyordu.
     */
    public function test_legacy_blade_markup_is_ordered_by_order_column(): void
    {
        config()->set('settings.panel_ui', 'blade');

        $menu = $this->menu();

        foreach ([['Blog', 1], ['Kurumsal', 0]] as [$title, $order]) {
            MenuItems::create([
                'title' => $title,
                'url' => '/'.$order,
                'target' => '_self',
                'icon' => '',
                'order' => $order,
                'parent_id' => null,
                'language' => $this->language->code,
                'menu_type' => 'standard',
                'menu_id' => $menu->id,
            ]);
        }

        $html = $this->actingAs($this->owner)
            ->get(route('admin.menu.show', ['menu' => $menu->id]))
            ->assertOk()
            ->viewData('html_menu');

        $this->assertLessThan(
            strpos($html, 'data-title="Blog"'),
            strpos($html, 'data-title="Kurumsal"'),
        );
    }
}
