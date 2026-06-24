<?php

namespace App\Application\Watchlist\Services;

/**
 * Explicit non-live C72 context for controlled opt-in production catalog bridge validation.
 * This context is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogControlledOptInRuntimeBridgeContext
{
    public const REQUIRED_OPERATOR_OPTION = '--controlled-opt-in';
    public const DEFAULT_ENABLED = false;
    public const LIVE_PLAN_CONFIRM_MUTATION_ALLOWED = false;

    private bool $controlledOptIn;
    private bool $killSwitchEnabled;

    public function __construct(bool $controlledOptIn = false, bool $killSwitchEnabled = false)
    {
        $this->controlledOptIn = $controlledOptIn;
        $this->killSwitchEnabled = $killSwitchEnabled;
    }

    public function canValidateInIsolatedPath(): bool
    {
        return $this->controlledOptIn && ! $this->killSwitchEnabled;
    }

    public function controlledOptInRequested(): bool
    {
        return $this->controlledOptIn;
    }

    public function killSwitchEnabled(): bool
    {
        return $this->killSwitchEnabled;
    }
}
