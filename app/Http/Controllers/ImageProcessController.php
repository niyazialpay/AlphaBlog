<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Imagick;
use ImagickException;

class ImageProcessController extends Controller
{
    /**
     * @throws ImagickException
     */
    public function index($path, $width, $height, $type, $image)
    {
        $cacheKey = config('cache.prefix')."images.{$path}.{$width}.{$height}.{$type}.{$image}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $file_info = pathinfo($image);
        $image_path = storage_path(
            'app/public/'.$path.'/conversions/'.
            $file_info['filename'].'-'.$type.'.'.$file_info['extension']
        );

        if (! file_exists($image_path)) {
            abort(404);
        }

        $im = new Imagick;

        try {
            $im->readImage($image_path);
        } catch (ImagickException $e) {
            Log::warning('Image processing failed', ['path' => $image_path, 'error' => $e->getMessage()]);
            abort(404);
        }

        $last_modified = filemtime($image_path);
        $modified_since = request()->header('If-Modified-Since') !== null
            ? strtotime(request()->header('If-Modified-Since'))
            : false;
        $etagHeader = request()->header('If-None-Match') !== null
            ? trim(request()->header('If-None-Match'))
            : false;

        $etag = sprintf('"%s-%s"', $last_modified, md5($im));

        if ((int) $modified_since === (int) $last_modified && $etag === $etagHeader) {
            return response('', 304);
        }

        $original_dimensions = getimagesize($image_path);
        $original_width = $original_dimensions[0];
        $original_height = $original_dimensions[1];

        if ($height === true) {
            $height = ($original_width * $width) / $original_height;
        }

        $im->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

        $im->setImageCompression(Imagick::COMPRESSION_JBIG2);
        $im->setImageCompressionQuality(75);

        $format = $im->getImageFormat();
        $im->setImageFormat($format);

        $response = Response::make($im, 200)
            ->header('Content-type', 'image/'.$format)
            ->setPublic()
            ->setMaxAge(86400 * 365)
            ->setExpires(now()->addYear())
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $last_modified).' GMT')
            ->setEtag($etag);

        Cache::forever($cacheKey, $response);

        return $response;
    }
}
