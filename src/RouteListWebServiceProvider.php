<?php

namespace Apih\RouteListWeb;

use Illuminate\Support\ServiceProvider;

class RouteListWebServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(realpath(__DIR__ . '/../resources/views'), 'routelistweb');
    }
}
