<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Action\SocialNetworkSaveAction;
use App\Http\Controllers\Controller;
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
}
