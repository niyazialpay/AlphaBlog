<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LanguageRequest;
use App\Models\Languages;
use Illuminate\Http\Request;

class LanguagesController extends Controller
{
    public function show(Request $request){
        return response()->json(Languages::where('_id', GetPost($request->post('id')))->first());
    }

    public function save(LanguageRequest $request, Languages $language){
        if($language->id){
            $language = Languages::where('_id', $language->id)->first();
        }
        $language->name = $request->post('name');
        $language->code = $request->post('code');
        $language->flag = $request->post('flag');
        if($request->post('is_default')==1){
            Languages::where('is_default', true)->update(['is_default' => false]);
        }
        $language->is_default = $request->post('default')==1;
        $language->is_active = $request->post('active')==1;
        $language->save();
    }
}
