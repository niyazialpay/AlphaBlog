<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuRequest;
use App\Models\Menu\Menu;
use App\Support\Panel\PanelResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends Controller
{
    public function index(Menu $menu): Response
    {
        // Blade'e BOS bir `new Menu` ornegi gecirilip sorgu icerideydi.
        $all = Menu::orderBy('menu_position')->orderBy('title')->get();

        return PanelResponse::render(
            'Menu/Index',
            'panel.menu.index',
            [
                'menus' => $all->map(fn (Menu $item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'menu_position' => $item->menu_position,
                    'language' => $item->language,
                    'items_count' => $item->menuItems()->count(),
                ])->values(),
                /*
                 * `menu` DEGIL, `menuRecord` - bkz. MenuItemsController::show().
                 * Paylasilan `menu` prop'u sidebar bolumleridir; ayni adli sayfa
                 * prop'u onu ezip sol menuyu yok ediyordu (burada null olunca
                 * sessizce bos, duzenleme modunda ise TypeError ile komple).
                 */
                'menuRecord' => $menu->id ? [
                    'id' => $menu->id,
                    'title' => $menu->title,
                    'menu_position' => $menu->menu_position,
                    'language' => $menu->language,
                ] : null,
                'languages' => collect(app('languages'))
                    ->map(fn ($language) => ['code' => $language->code, 'name' => $language->name])
                    ->values(),
            ],
            ['all_menus' => new Menu, 'menu' => $menu],
        );
    }

    public function save(Menu $menu, MenuRequest $request)
    {
        try {
            DB::beginTransaction();
            $menu->fill($request->except('_token'));
            $menu->save();
            Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'header_menu_tree_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_tree_'.$menu->language);
            DB::commit();

            return request()->inertia()
                ? back()->with('success', __('menu.menu_saved'))
                : response()->json([
                    'message' => __('menu.menu_saved'),
                    'status' => 'success',
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return request()->inertia()
                ? back()->with('error', __('menu.menu_save_error'))
                : response()->json([
                    'message' => __('menu.menu_save_error'),
                    'status' => 'error',
                ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $menu = Menu::find($request->post('menu_id'));
            Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'header_menu_tree_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_tree_'.$menu->language);
            $menu->menuItems()->delete();
            $menu->delete();
            DB::commit();

            return request()->inertia()
                ? back()->with('success', __('menu.menu_deleted'))
                : response()->json([
                    'message' => __('menu.menu_deleted'),
                    'status' => 'success',
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return request()->inertia()
                ? back()->with('error', __('menu.menu_delete_error'))
                : response()->json([
                    'message' => __('menu.menu_delete_error'),
                    'status' => 'error',
                ]);
        }
    }
}
