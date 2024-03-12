<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManifestController extends Controller
{
    public function manifest(){
        $general_settings = app('general_settings');
        $seo_settings = app('seo_settings');

        $site_logo_extension = pathinfo($general_settings->getFirstMediaUrl('app_icon'), PATHINFO_EXTENSION);

        if($site_logo_extension == 'svg'){
            $imageType = 'image/svg+xml';
        }
        elseif($site_logo_extension == 'ico'){
            $imageType = 'image/x-icon';
        }
        elseif($site_logo_extension == 'png'){
            $imageType = 'image/png';
        }
        elseif($site_logo_extension == 'jpg' || $site_logo_extension == 'jpeg'){
            $imageType = 'image/jpeg';
        }
        elseif($site_logo_extension == 'webp'){
            $imageType = 'image/webp';
        }
        else{
            $imageType = 'image/webp';
        }

        $manifest_body = [
            '$schema' => 'https://json.schemastore.org/web-manifest-combined.json',
            'name' => $general_settings->title,
            'description' => $seo_settings->description,
            'short_name' => $general_settings->site_name,
            'display' => 'standalone',
            'scope' => '/',
            'theme_color' => '#182330',
            'background_color' => '#182330',
            'start_url' => '/',
            'manifest_version' => 2,
            'version' => '1.0.2',
            'shortcuts' => [
                [
                    'name' => config('app.name'),
                    'short_name' => config('app.name'),
                    'description' => $seo_settings->description,
                    'url' => config('app.url'),
                    'icons' => [[
                        'src' => $general_settings->getFirstMediaUrl('app_icon', 'r_192x192'),
                        'sizes' => '192x192'
                    ]]
                ]
            ],
            'icons' => [
                [
                    'src' => $general_settings->getFirstMediaUrl('app_icon', 'r_32x32'),
                    'sizes' => '32x32',
                    'type' => $imageType,
                    'density' => '0.75'
                ],
                [
                    'src' => $general_settings->getFirstMediaUrl('app_icon', 'r_72x72'),
                    'sizes' => '72x72',
                    'type' => $imageType,
                    'density' => '1.5'
                ],
                [
                    'src' => $general_settings->getFirstMediaUrl('app_icon', 'r_96x96'),
                    'sizes' => '96x96',
                    'type' => $imageType,
                    'density' => '2.0'
                ],
                [
                    'src' => $general_settings->getFirstMediaUrl('app_icon', 'r_192x192'),
                    'sizes' => '192x192',
                    'type' => $imageType,
                    'density' => '4.0'
                ]
            ]
        ];

        return response(json_encode($manifest_body), 200)
            ->header('Content-Type', 'application/json');
    }
}
