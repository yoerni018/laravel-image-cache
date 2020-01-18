<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Response;

class ImgController extends Controller {
    

    public function showImg(Request $request, $size, $image) {


        $path = storage_path('app/public/images/'.$image);
        $handler = new \Symfony\Component\HttpFoundation\File\File($path);

        $lifetime = 31556926;
        $file_time = $handler->getMTime();
        $header_content_type = $handler->getMimeType();
        $header_content_length = $handler->getSize();
        $header_etag = md5($file_time . $path . $size);
        $header_last_modified = gmdate('r', $file_time);
        $header_expires = gmdate('r', $file_time + $lifetime);

        $headers = array(
            'Content-Disposition' => 'inline; filename="img1.jpg"',
            'Last-Modified' => $header_last_modified,
            'Cache-Control' => 'must-revalidate',
            'Expires' => $header_expires,
            'Pragma' => 'public',
            'Etag' => $header_etag
        );

        /**
         * Is the resource cached?
         */
        $h1 = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified;
        $h2 = isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag;

        if ($h1 || $h2) {
            return Response::make('', 304, $headers); // File (image) is cached by the browser, so we don't have to send it again
        }

        
        /*$headers = array_merge($headers, array(
            'Content-Type' => $header_content_type,
            'Content-Length' => $header_content_length
        ));
        return Response::make(file_get_contents($path), 200, $headers);
        https://slick.pl/kb/laravel/load-images-stored-outside-public-directory-use-browser-cache-send-304-laravel-response/
        */

        $img = Image::make($path);
        
        $img->resize($size, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        
        $response = Response::make($img->encode('jpg'));
        $response->header('Content-Type', $header_content_type);
        $response->header('Content-Disposition', 'inline; filename="img1.jpg"');
        $response->header('Last-Modified', $header_last_modified);
        $response->header('Cache-Control', 'must-revalidate');
        $response->header('Expires', $header_expires);
        $response->header('Pragma', 'public');
        $response->header('Etag', $header_etag);
       
        return $response;
    }

}
