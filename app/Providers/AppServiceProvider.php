<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Source\Pages\Application\Contracts\ActivityLogger as PageActivityLoggerInterface;
use Source\Pages\Domain\Repository\Repository as PageInterface;
use Source\Pages\Infrastructure\Logging\PageActivityLogger;
use Source\Pages\Infrastructure\Persistence\PageRepository;
use Source\Roles\Domain\Repository\PermissionRepository;
use Source\Roles\Domain\Repository\RoleRepository;
use Source\Roles\Infrastructure\Persistence\EloquentPermissionRepository;
use Source\Roles\Infrastructure\Persistence\EloquentRoleRepository;
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
            PageActivityLoggerInterface::class,
            PageActivityLogger::class,
        );

        $this->app->bind(
            DomainInterface::class,
            UserRepository::class,
        );

        $this->app->bind(
            RoleRepository::class,
            EloquentRoleRepository::class,
        );

        $this->app->bind(
            PermissionRepository::class,
            EloquentPermissionRepository::class,
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
