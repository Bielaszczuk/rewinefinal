<?php

namespace App\Providers;

use App\Domain\Wine\Contracts\WineRepositoryInterface;
use App\Domain\Winery\Contracts\WineryRepositoryInterface;
use App\Domain\Event\Contracts\EventRepositoryInterface;
use App\Domain\Review\Contracts\ReviewRepositoryInterface;
use App\Domain\User\Contracts\UserRepositoryInterface;
use App\Domain\WineRoute\Contracts\WineRouteRepositoryInterface;
use App\Domain\Cellar\Contracts\CellarRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\WineRepository;
use App\Infrastructure\Persistence\Repositories\WineryRepository;
use App\Infrastructure\Persistence\Repositories\EventRepository;
use App\Infrastructure\Persistence\Repositories\ReviewRepository;
use App\Infrastructure\Persistence\Repositories\UserRepository;
use App\Infrastructure\Persistence\Repositories\WineRouteRepository;
use App\Infrastructure\Persistence\Repositories\CellarRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WineRepositoryInterface::class, WineRepository::class);
        $this->app->bind(WineryRepositoryInterface::class, WineryRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WineRouteRepositoryInterface::class, WineRouteRepository::class);
        $this->app->bind(CellarRepositoryInterface::class, CellarRepository::class);
    }

    public function boot(): void
    {
    }
}
