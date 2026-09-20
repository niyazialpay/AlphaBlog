<?php

namespace App\Http\Controllers\Admin\Cloudflare;

use App\Http\Controllers\Controller;
use App\Models\Cloudflare;
use App\Support\Panel\PanelResponse;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\Zones;
use Exception;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CloudflareController extends Controller
{
    // Octane: static olurlarsa worker içinde istekler arası sızarlar.
    private string $zoneID = '';

    private ?Zones $zones = null;

    private bool $invalidCredentials = false;

    public function __construct()
    {
        $cf = Cloudflare::first();

        if ($cf) {
            $key = new APIKey($cf->cf_email, $cf->cf_key);
            $zones = new Zones(new Guzzle($key));
            $this->zones = $zones;
            try {
                $this->zoneID = Cache::remember(
                    Cloudflare::zoneCacheKey($cf->domain),
                    now()->addHours(6),
                    fn () => $zones->getZoneID($cf->domain),
                );
            } catch (Exception $e) {
                $this->invalidCredentials = true;
            }
        } else {
            $this->invalidCredentials = true;
        }
    }

    public function index(): SymfonyResponse
    {
        if ($this->invalidCredentials) {
            return redirect()->route('admin.settings', ['tab' => 'cloudflare']);
        }

        $cloudflare = $this->zones->getBody();
        $zone = $cloudflare->result[0] ?? null;

        return PanelResponse::render(
            'Cloudflare/Index',
            'panel.cloudflare.index',
            [
                /*
                 * Blade'e ham API govdesi (stdClass) geciyordu ve icinde
                 * $cloudflare->result[0]->... okunuyordu. Inertia prop'u
                 * JSON-serilestirilebilir duz bir yapiya indirgenir.
                 */
                'zone' => $zone ? [
                    'name' => $zone->name ?? null,
                    'status' => $zone->status ?? null,
                    'paused' => (bool) ($zone->paused ?? false),
                    'development_mode' => (int) ($zone->development_mode ?? 0),
                    'name_servers' => array_values((array) ($zone->name_servers ?? [])),
                ] : null,
            ],
            ['cloudflare' => $cloudflare],
        );
    }

    public function CacheClear()
    {
        $this->zones->cachePurgeEverything($this->zoneID);

        if (request()->inertia()) {
            return back()->with('success', __('cloudflare.cache_cleared'));
        }

        return response()->json([
            'status' => true,
            'message' => __('cloudflare.cache_cleared'),
        ]);
    }

    public function ToggleDevelopment()
    {
        $develop_ment_mode_status = $this->zones->getBody()->result[0]->development_mode;
        if ($develop_ment_mode_status > 0) {
            $status = false;
            $message = __('cloudflare.development_mode_deactivated');
        } else {
            $status = true;
            $message = __('cloudflare.development_mode_activated');
        }
        $this->zones->changeDevelopmentMode($this->zoneID, $status);

        if (request()->inertia()) {
            return back()->with('success', $message);
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'mode' => $status,
        ]);
    }
}
