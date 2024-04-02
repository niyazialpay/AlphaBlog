<?php

namespace App\Action;

use App\Models\SocialNetworks;

class SocialNetworkSaveAction
{
    public static function execute($request, $type, $user_id = null)
    {
        if ($type == 'user') {
            $social = SocialNetworks::where('user_id', $user_id)->first();
            if ($social == null) {
                $social = new SocialNetworks();
            }
            $social->user_id = $user_id;
        } else {
            $social = SocialNetworks::where('type', 'website')->first();
            if ($social == null) {
                $social = new SocialNetworks();
            }
        }
        $social->facebook = $request->facebook;
        $social->instagram = $request->instagram;
        $social->linkedin = $request->linkedin;
        $social->github = $request->github;
        $social->devto = $request->devto;
        $social->medium = $request->medium;
        $social->youtube = $request->youtube;
        $social->reddit = $request->reddit;
        $social->xbox = $request->xbox;
        $social->deviantart = $request->deviantart;
        $social->website = $request->website;
        $social->x = $request->x;
        $social->type = $type;

        return $social->save();
    }
}
