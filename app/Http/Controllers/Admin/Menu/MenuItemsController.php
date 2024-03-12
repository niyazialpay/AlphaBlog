<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuItemRequest;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItems;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuItemsController extends Controller
{
    public function show(Menu $menu){
        $posts = Posts::class;
        return view('panel.menu.show', [
            'categories'=>Categories::where('language', $menu->language)->get(),

            'pages'=>$posts::where('post_type', 'page')
                ->where('language', $menu->language)->get(),

            'posts' => $posts::where('post_type', 'post')
                ->where('language', $menu->language)->get(),

            'menu'=>$menu,

            'html_menu' => $this->menuTree($menu->id),
        ]);
    }
    public function save(MenuItemRequest $request){

        $menu = $request->post('menu');
        $array_menu = json_decode($menu, true);

        MenuItems::where('menu_id', $request->post('menu_id'))->delete();

        $this->updateMenu($request->post('menu_id'), $array_menu);

        return response()->json([
            'message' => __('menu.menu_saved'),
            'status' => 'success',
        ]);
    }

    private function updateMenu($menu_id, $menu, $parent=null): void
    {
        if (!empty($menu)) {

            foreach ($menu as $value) {
                $menu_item = new MenuItems();
                $menu_item->fill([
                    'title' => $value['title'],
                    'url' => (empty($value['url'])) ? 'javascript:void(0)' : $value['url'],
                    'parent_id' => $parent,
                    'menu_id' => $menu_id,
                    'language' => $value['language'],
                    'icon' => $value['icon'],
                    'target' => $value['nav_target'],
                ]);
                $menu_item->save();
                if (array_key_exists('children', $value)) {
                    $this->updateMenu($menu_id, $value['children'], $menu_item->id);
                }
            }

        }
        foreach (app('languages') as $language) {
            Cache::forget(config('cache.prefix').'header_menu_'.$language->code);
        }
    }

    private function renderMenuItem($id, $label, $url, $language,  $target, $icon, $menu_id): string
    {
        return '<li class="dd-item dd3-item" data-id="' . $id . '" data-title="' . $label . '" data-url="' . $url . '" data-language="'.$language.'" data-nav_target="'.$target.'" data-icon="'.$icon.'" data-menu_id="'.$menu_id.'">' .
            '<div class="dd-handle dd3-handle" > Drag</div>' .
            '<div class="dd3-content"><span>' . $label . '</span>' .
            '<div class="item-edit"><i class="fa-duotone fa-pen-to-square"></i></div>' .
            '</div>' .
            '<div class="item-settings d-none">' .
            '<p><label for="">'.__('menu.title').'<br><input type="text" class="form-control" name="navigation_title" value="' . $label . '"></label></p>' .
            '<p><label for="">'.__('menu.url').'<br><input type="text" class="form-control" name="navigation_url" value="' . $url . '"></label></p>' .
            '<p><label for="">'.__('menu.target').'<br>
            <select name="navigation_target" class="form-control">
                <option value="_self" '.($target == '_self' ? 'selected' : '').'>'.__('menu.same_tab').'</option>
                <option value="_blank" '.($target == '_blank' ? 'selected' : '').'>'.__('menu.new_tab').'</option>
            </select>
            </label></p>'.
            '<p><label for="">'.__('menu.icon').'<br><input type="text" class="form-control" name="navigation_icon" value="' . $icon . '"></label></p>'.
            '<p><a class="item-delete btn btn-sm btn-outline-danger" href="javascript:void(0);"><i class="fa-duotone fa-trash"></i></a>' .
            '<a class="item-close btn btn-sm btn-outline-secondary mx-2" href="javascript:void(0);"><i class="fa-duotone fa-circle-xmark"></i></a></p>' .
            '</div>';
    }

    private function menuTree($menu_id, $parent_id = null): string
    {
        $items = '';
        $query = MenuItems::where('parent_id', $parent_id)->where('menu_id', $menu_id)->orderBy('_id', 'ASC');
        if ($query->count() > 0) {
            $items .= '<ol class="dd-list">';
            foreach ($query->get() as $row) {
                $items .= $this->renderMenuItem($row->id, $row->title, $row->url, $row->language, $row->target, $row->icon, $row->menu_id);
                $items .= $this->menuTree($menu_id, $row->id);
                $items .= '</li>';
            }
            $items .= '</ol>';
        }
        return $items;
    }
}
