<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Source\Pages\Domain\Repository\Repository as PageInterface;
use Source\Pages\Infrastructure\Persistence\PageRepository;
use Source\Users\Domain\Repository\Repository as DomainInterface;
use Source\Users\Infrastructure\Persistence\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PageInterface::class,
            PageRepository::class,
        );

        $this->app->bind(
            DomainInterface::class,
            UserRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
