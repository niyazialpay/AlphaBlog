<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot() {}

    public function register(): void
    {
        foreach (glob(app_path().'/Helpers/*.php') as $file) {
            require_once $file;
        }
    }
}
