<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

app()->make(Router::class)->get('route:list', static function (Router $router, Request $request) {
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

    $routes = collect($router->getRoutes())->map(static fn (Route $route) => [
        'domain' => $route->domain(),
        'method' => $route->methods(),
        'uri' => $route->uri(),
        'name' => $route->getName(),
        'action' => ltrim($route->getActionName(), '\\'),
        'middleware' => (static function (Route $route, Router $router) {
            $closureMiddlewareAsString = static fn ($middleware) => $middleware instanceof Closure ? 'Closure' : $middleware;

            $middleware = collect($router->resolveMiddleware($route->gatherMiddleware()))->map($closureMiddlewareAsString);
            $excludedMiddleware = collect($router->resolveMiddleware($route->excludedMiddleware()))->map($closureMiddlewareAsString)->keyByValue();

            $result = [];

            foreach ($middleware as $item) {
                $result[] = [
                    'name' => $item,
                    'excluded' => isset($excludedMiddleware[$item]),
                ];
            }

            return $result;
        })($route, $router),
    ])->sortBy('uri')->values();

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
