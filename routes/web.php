<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::get('route:list', function (Request $request) {
    $router = app(Router::class);

    $aliases = $router->getMiddleware();
    $groups = $router->getMiddlewareGroups();

    foreach ($groups as $group => $groupMiddlewares) {
        foreach ($groupMiddlewares as $key => $groupMiddleware) {
            if (str_contains($groupMiddleware, '\\')) {
                continue;
            }

            $parameters = null;

            if (str_contains($groupMiddleware, ':')) {
                [$groupMiddleware, $parameters] = explode(':', $groupMiddleware, 2);
            }

            if (isset($aliases[$groupMiddleware])) {
                $groupMiddleware = $aliases[$groupMiddleware];
            }

            $groupMiddlewares[$key] = $groupMiddleware . ($parameters ? ':' . $parameters : '');
        }

        $groups[$group] = $groupMiddlewares;
    }

    $router->flushMiddlewareGroups();

    $routes = collect(Route::getRoutes())->map(function ($route) use ($router) {
        return [
            'domain' => $route->domain(),
            'method' => $route->methods(),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => collect($router->gatherRouteMiddleware($route))->map(function ($middleware) {
                return $middleware instanceof Closure ? 'Closure' : $middleware;
            }),
        ];
    })->sortBy('uri')->values();

    $columns = [
        'method' => 'Method',
        'uri' => 'URI',
        'name' => 'Name',
        'action' => 'Action',
        'middleware' => 'Middleware',
    ];

    return $request->expectsJson()
        ? compact('groups', 'routes')
        : view('routelistweb::index', compact('groups', 'routes', 'columns'));
})->name('route:list');
