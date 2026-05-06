<?php

namespace App\Http\Controllers;

use App\Models\Post\PostQrScan;
use App\Models\Post\Posts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostQrController extends Controller
{
    public function redirect(Request $request, string $language, Posts $showPost, string $qr_key): RedirectResponse
    {
        if (! $showPost->qr_link || ! str_ends_with($showPost->qr_link, '/qr/'.$qr_key)) {
            abort(404);
        }

        $sessionKey = 'qr_scanned_post_'.$showPost->id;
        if (! session()->has($sessionKey)) {
            PostQrScan::create([
                'post_id' => $showPost->id,
                'session_id' => session()->getId(),
                'ip_address' => $request->ip(),
            ]);
            session()->put($sessionKey, true);
        }

        return redirect()->route('page', [$language, $showPost->slug]);
    }
}
