<?php

namespace App\Application\Watchlist\Services;

/**
 * Non-live C70 contract marker for the future production catalog runtime bridge.
 * This class is intentionally not consumed by PLAN/CONFIRM runtime services.
 */
class WatchlistProductionCatalogRuntimeBridgeContract
{
    public const FEATURE_FLAG = 'watchlist.production_catalog_runtime_bridge_enabled';
    public const KILL_SWITCH = 'watchlist.production_catalog_runtime_bridge_kill_switch';
    public const DEFAULT_ENABLED = false;
    public const DEFAULT_KILL_SWITCH = false;
    public const PRIMARY_CANDIDATE = 'C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE';
    public const BACKUP_CANDIDATE = 'C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION';
    public const COMPARATOR_ONLY_CANDIDATE = 'C61_A01_B01_WEAK_REGIME_QUALITY_FIRST';
}
