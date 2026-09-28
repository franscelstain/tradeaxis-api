<?php

namespace App\Providers;

use App\Infrastructure\Persistence\SecurityIdentity\FoundationRepository;
use Illuminate\Support\ServiceProvider;

final class SecurityIdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Deferred binding: registering the shared module performs no query or import.
        $this->app->bind(FoundationRepository::class, function ($app): FoundationRepository {
            return new FoundationRepository($app['db']->connection());
        });
    }
}
