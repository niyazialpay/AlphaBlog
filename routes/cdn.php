<?php

if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
    $domain = config('app.cdn_url');
} else {
    $domain = config('app.url');
}
Route::domain($domain)->group(function () {
    Route::get('/image/{path}/{width}/{height}/{type}/{image}', [\App\Http\Controllers\ImageProcessController::class, 'index'])
        ->name('image')
        ->where([
            'path' => '[a-zA-Z0-9\/]+',
            'image' => '[a-zA-Z0-9\/\.\-_]+',
            'width' => '[0-9]+',
            'height' => '[0-9]+',
            'type' => '[a-zA-Z0-9\/]+',
        ]);

    Route::get('/{any}', [\App\Http\Controllers\CDNController::class, 'index'])
        ->where('any', '.*')->name('cdn');

    if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
        Route::get('/', function () {
            return redirect(config('app.url'));
        });
    }
});
