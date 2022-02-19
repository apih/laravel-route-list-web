<?php

namespace Apih\RouteListWeb;

use Illuminate\Support\ServiceProvider;

class RouteListWebServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'routelistweb');
    }
}
