<?php

namespace App\Http\Controllers\Admin\Cloudflare;

use App\Http\Controllers\Controller;
use App\Models\Cloudflare;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\Zones;
use Exception;

class CloudflareController extends Controller
{
    private static string $zoneID;
    private static Zones $zones;
    private static Guzzle $adapter;

    private bool $invalidCredentials = false;

    public function __construct()
    {
        $cf = Cloudflare::first();

        if($cf){
            $key = new APIKey($cf->cf_email, $cf->cf_key);
            self::$adapter = new Guzzle($key);
            $zones = new Zones(self::$adapter);
            self::$zones = $zones;
            try{
                $zones->getZoneID($cf->domain);
                self::$zoneID = $zones->getZoneID($cf->domain);
            }
            catch (Exception $e) {
                $this->invalidCredentials = true;
            }
        }
        else{
            $this->invalidCredentials = true;
        }
    }

    public function index()
    {
        if($this->invalidCredentials){
            return redirect()->route('admin.settings', ['tab' => 'cloudflare']);
        }

        $cloudflare = self::$zones->getBody();

        return view('panel.cloudflare.index', [
            'cloudflare' => $cloudflare
        ]);
    }

    public function CacheClear(){
        self::$zones->cachePurgeEverything(self::$zoneID);
        return response()->json([
            'status' => true,
            'message' => __('cloudflare.cache_cleared')
        ]);
    }

    public function ToggleDevelopment(){
        $develop_ment_mode_status = self::$zones->getBody()->result[0]->development_mode;
        if($develop_ment_mode_status > 0){
            $status = false;
            $message = __('cloudflare.development_mode_deactivated');
        }else{
            $status = true;
            $message = __('cloudflare.development_mode_activated');
        }
        self::$zones->changeDevelopmentMode(self::$zoneID, $status);
        return response()->json([
            'status' => true,
            'message' => $message,
            'mode' => $status
        ]);
    }


}
