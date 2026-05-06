<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use App\Models\Post\Posts;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PostQrAdminController extends Controller
{
    public function generate(string $type, Posts $post): JsonResponse
    {
        $token = Str::random(64);
        $post->qr_link = config('app.url').'/'.$post->language.'/'.$post->slug.'/qr/'.$token;
        $post->saveQuietly();

        return response()->json([
            'status' => 'success',
            'qr_link' => $post->qr_link,
            'scan_count' => $post->qrScans()->count(),
        ]);
    }
}
