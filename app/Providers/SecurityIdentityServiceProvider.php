<?php

namespace App\Providers;

use App\Application\SecurityIdentity\Contracts\IdentityResolver;
use App\Application\SecurityIdentity\FoundationService;
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
        $this->app->bind(IdentityResolver::class, function ($app): IdentityResolver {
            return $app->make(FoundationService::class);
        });
    }
}
