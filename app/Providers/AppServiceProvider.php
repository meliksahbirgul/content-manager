<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Infrastructure\Persistence\PageRepository;
use Source\Pages\Infrastructure\Persistence\PageRepositroy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            Repository::class,
            PageRepository::class,
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
