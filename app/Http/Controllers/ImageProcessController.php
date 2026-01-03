<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use Imagick;
use ImagickException;

class ImageProcessController extends Controller
{
    /**
     * @throws ImagickException
     */
    public function index($path, $width, $height, $type, $image)
    {
        // Create a unique cache key based on the parameters
        // Note: If the underlying image changes with the same parameters,
        // you would need to invalidate or forget the old cache entry manually.
        $cacheKey = config('cache.prefix')."images.{$path}.{$width}.{$height}.{$type}.{$image}";

        // If this key exists in the cache, return it directly
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Otherwise, process the image from disk
        $file_info = pathinfo($image);
        $image_path = storage_path(
            'app/public/'.$path.'/conversions/'.
            $file_info['filename'].'-'.$type.'.'.$file_info['extension']
        );

        // If the file doesn't exist, return 404
        if (! file_exists($image_path)) {
            abort(404);
        }

        $im = new Imagick;

        try {
            $im->readImage($image_path);
        } catch (ImagickException $e) {
            return $e->getMessage();
        }

        // For ETag and Last-Modified, we can use the actual image's file modification time
        $last_modified = filemtime($image_path);
        $modified_since = request()->header('If-Modified-Since') !== null
            ? strtotime(request()->header('If-Modified-Since'))
            : false;
        $etagHeader = request()->header('If-None-Match') !== null
            ? trim(request()->header('If-None-Match'))
            : false;

        // Generate ETag based on modification time and MD5 of the image
        $etag = sprintf('"%s-%s"', $last_modified, md5($im));

        // If the file has not changed (based on time & ETag), return 304
        if ((int) $modified_since === (int) $last_modified && $etag === $etagHeader) {
            return response('', 304);
        }

        // Get the original dimensions
        $original_dimensions = getimagesize($image_path);
        $original_width = $original_dimensions[0];
        $original_height = $original_dimensions[1];

        // If $height === true, keep aspect ratio
        if ($height === true) {
            $height = ($original_width * $width) / $original_height;
        }

        // Resize the image
        $im->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);

        // Set compression (for example, JBIG2 at 75% quality)
        $im->setImageCompression(Imagick::COMPRESSION_JBIG2);
        $im->setImageCompressionQuality(75);

        // Preserve the original format (e.g., png, jpg, etc.)
        $format = $im->getImageFormat();
        $im->setImageFormat($format);

        // Build the HTTP response
        $response = Response::make($im, 200)
            ->header('Content-type', 'image/' . $format)
            ->setPublic()
            ->setMaxAge(86400 * 365) // 1 year
            ->setExpires(now()->addYear())
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $last_modified).' GMT')
            ->setEtag($etag);

        // Store the response in cache FOREVER (until manually removed)
        Cache::forever($cacheKey, $response);

        // Return the response
        return $response;
    }
}
