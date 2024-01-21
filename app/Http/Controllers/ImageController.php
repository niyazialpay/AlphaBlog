<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Posts;
use Illuminate\Http\Request;
use Imagick;
use ImagickException;
use Illuminate\Support\Facades\Response;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;

class ImageController extends Controller
{
    public function editorImageUpload($type, Posts $post, Request $request)
    {
        if(!$post->_id){
            $post->title="draft";
            $post->save();
        }

        if($request->hasFile('file') && $request->file('file')->isValid()){
            $post->addMediaFromRequest('file')->toMediaCollection('content_images');
        }
        return response()->json([
            'success' => true,
            'blog_id' => $post->id,
            'location' => $post->getMedia('content_images')->last()->getFullUrl('resized'),
        ]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function postImageDelete($type, Posts $post, Request $request)
    {
        $post->deleteMedia($request->post('media_id'));
        return response()->json([
            'success' => true,
        ]);
    }
}
