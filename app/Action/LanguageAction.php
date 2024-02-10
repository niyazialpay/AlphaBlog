<?php

namespace App\Action;

use App\Models\Languages;

class LanguageAction
{
    public static function setLanguage($request): void
    {
        $languages = new Languages();
        if(session()->has('language')) {
            if($request->segment(1)==session()->get('language')){
                $language = $languages->getLanguage(session()->get('language'));
            }
            else{
                $language = $languages->getLanguage($request->segment(1));
                if($language==null){
                    $language = $languages->getLanguage(app('default_language')->code);
                }
            }
        }
        else{
            if($request->segment(1)==null){
                $language = $languages->getLanguage(app('default_language')->code);
            }
            else{
                $language = $languages->getLanguage($request->segment(1));
                if($language==null){
                    $language = $languages->getLanguage(app('default_language')->code);
                }
            }
        }
        session()->put('language', $language->code);
        session()->put('language_flag', $language->flag);
        session()->put('language_name', $language->name);

        app()->setLocale($language->code);
        setlocale(LC_ALL, $language->code);
        setlocale(LC_TIME, $language->code);
    }
}
