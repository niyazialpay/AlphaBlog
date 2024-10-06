<?php

namespace App\Http\Controllers\Admin\Cloudflare;

use App\Http\Controllers\Controller;
use App\Models\Cloudflare;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\EndpointException;
use Cloudflare\API\Endpoints\FirewallSettings;
use Cloudflare\API\Endpoints\SSL;
use Cloudflare\API\Endpoints\TLS;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Http\Request;

class CloudflareController extends Controller
{
    public static string $zoneID;
    public static Zones $zones;
    public static string $domain;
    public static Guzzle $adapter;

    /**
     * @throws EndpointException
     */
    public function __construct()
    {
        $cf = Cloudflare::first();

        if($cf){
            $key = new APIKey($cf->cf_email, $cf->cf_key);
            self::$adapter = new Guzzle($key);
            $zones = new Zones(self::$adapter);

            self::$domain = $cf->domain;
            self::$zones = $zones;
            self::$zoneID = $zones->getZoneID($cf->domain);
        }
    }

    public function index()
    {
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
