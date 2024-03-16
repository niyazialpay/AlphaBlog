<?php

return [
    \Illuminate\Session\SessionServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\GlobalVariableServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\HelperServiceProvider::class,
    MongoDB\Laravel\MongoDBServiceProvider::class,
];
