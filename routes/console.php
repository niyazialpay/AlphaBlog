<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('clear-trash', function(){
    \App\Models\Post\Comments::onlyTrashed()->whereDate('deleted_at', '<=', now()->subDays(30))->forceDelete();
    \App\Models\Post\Posts::onlyTrashed()->whereDate('deleted_at', '<=', now()->subDays(30))->forceDelete();
    \App\Models\Search::where('think', false)->whereDate('created_at', '<=', now()->subDays(30))->forceDelete();
    $this->info('Recycle bin emptied successfully');
})->purpose('Empty recycle bin')->daily();

