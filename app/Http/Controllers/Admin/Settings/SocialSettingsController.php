<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Action\SocialNetworkSaveAction;
use App\Http\Controllers\Controller;
use App\Models\Settings\SocialSettings;
use Exception;
use Illuminate\Http\Request;

class SocialSettingsController extends Controller
{
    public function save(Request $request){
        if(SocialNetworkSaveAction::execute($request, 'website')){
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success')
            ], 200);
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error')
            ], 422);
        }
    }

    public function saveHeader(Request $request){
        try{
            $socialSettings = SocialSettings::first();
            if($socialSettings){
                $socialSettings->social_networks = $request->social_networks;
                $socialSettings->save();
            }
            else{
                SocialSettings::create([
                    'social_networks' => $request->social_networks
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success')
            ], 200);
        }
        catch(Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
