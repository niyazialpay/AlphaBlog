<?php

namespace App\Http\Controllers;

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
        $file_info = pathinfo($image);
        $image_path = storage_path('app/public/'.$path.'/conversions/'.$file_info['filename'].'-'.$type.'.'.$file_info['extension']);
        if (! file_exists($image_path)) {
            abort(404);
        } else {
            $im = new Imagick;
            try {
                $im->readImage($image_path);
            } catch (ImagickException $e) {
                return $e->getMessage();
            }

            $last_modified = filemtime(__FILE__);
            $modified_since = (request()->header('HTTP_IF_MODIFIED_SINCE') !== null ? strtotime(request()->header('HTTP_IF_MODIFIED_SINCE')) : false);
            $etagHeader = (request()->header('HTTP_IF_NONE_MATCH') !== null ? trim(request()->header('HTTP_IF_NONE_MATCH')) : false);
            // generate the etag from your output
            $etag = sprintf('"%s-%s"', $last_modified, md5($im));

            // if last modified date is same as "HTTP_IF_MODIFIED_SINCE", send 304 then exit
            if ((int) $modified_since === (int) $last_modified && $etag === $etagHeader) {
                header('HTTP/1.1 304 Not Modified');
            }

            $original_dimensions = getimagesize($image_path);
            $original_with = $original_dimensions[0];
            $original_height = $original_dimensions[1];

            $height = $height === true ? ($original_with * $width / $original_height) : $height;

            $im->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
            $im->setImageCompression(Imagick::COMPRESSION_JBIG2);
            $im->setImageCompressionQuality(75);

            $im->setImageFormat($im->getImageFormat());

            if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
                $domain = config('app.cdn_url');
            } else {
                $domain = config('app.url');
            }

            return Response::make($im, 200)
                ->header('Content-type', 'image/'.$im->getImageFormat())
                /*->header('Pragma', 'public')
                ->header('Cache-Control', ' max-age='.(86400 * 365))
                ->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 86400 * 365))
                ->header('Etag', $etag)*/
                ->header('access-control-allow-origin', $domain)
                //->setContent('image/'.$im->getImageFormat())
                ->setPublic()
                ->setMaxAge(86400 * 365)
                ->setExpires(now()->addYear())
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $last_modified).' GMT')
                //->setLastModified(now()->addYear())
                ->setEtag($etag)->withoutCookie(config('session.cookie'));
        }
    }
}
