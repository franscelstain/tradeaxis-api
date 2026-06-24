<?php

namespace App\Application\Watchlist\Services;

/**
 * Isolated non-live C71 dry-run validation contract.
 * This contract is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogDryRunRuntimeValidationContract
{
    public const FEATURE_FLAG = 'watchlist.production_catalog_dry_run_enabled';
    public const DEFAULT_ENABLED = false;
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
    public const A01_COMPARATOR_ONLY = true;
}
