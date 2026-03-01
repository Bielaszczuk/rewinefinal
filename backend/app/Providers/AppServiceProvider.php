<?php

namespace App\Providers;

use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\JwtOptionalMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth',      JwtAuthMiddleware::class);
        $router->aliasMiddleware('jwt.optional',  JwtOptionalMiddleware::class);
        $router->aliasMiddleware('role',          RoleMiddleware::class);
    }
}
