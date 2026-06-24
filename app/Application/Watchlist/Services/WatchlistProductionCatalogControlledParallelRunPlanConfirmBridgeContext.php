<?php

namespace App\Application\Watchlist\Services;

/**
 * Explicit non-live C73 context for controlled parallel-run PLAN/CONFIRM bridge validation.
 * This context is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogControlledParallelRunPlanConfirmBridgeContext
{
    public const REQUIRED_OPERATOR_OPTION = '--controlled-parallel-run';
    public const DEFAULT_ENABLED = false;
    public const LIVE_PLAN_CONFIRM_MUTATION_ALLOWED = false;

    private bool $controlledParallelRun;
    private bool $killSwitchEnabled;

    public function __construct(bool $controlledParallelRun = false, bool $killSwitchEnabled = false)
    {
        $this->controlledParallelRun = $controlledParallelRun;
        $this->killSwitchEnabled = $killSwitchEnabled;
    }

    public function canValidateInIsolatedPath(): bool
    {
        return $this->controlledParallelRun && ! $this->killSwitchEnabled;
    }

    public function controlledParallelRunRequested(): bool
    {
        return $this->controlledParallelRun;
    }

    public function killSwitchEnabled(): bool
    {
        return $this->killSwitchEnabled;
    }
}
