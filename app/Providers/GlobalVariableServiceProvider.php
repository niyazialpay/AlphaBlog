<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class GlobalVariableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::share('languages', \App\Models\Languages::where('is_active', true)->hint('sort_1')->get());
        View::share('default_language', \App\Models\Languages::where('is_default', true)->first());
    }
}
