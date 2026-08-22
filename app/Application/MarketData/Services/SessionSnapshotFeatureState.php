<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataSemanticBindings;

/** Current governed activation state for the optional MD-B20 capability. */
class SessionSnapshotFeatureState
{
    public function state(): string
    {
        return MarketDataSemanticBindings::SESSION_SNAPSHOT_FEATURE_STATE;
    }

    public function isEnabled(): bool
    {
        return $this->state() === 'ENABLED';
    }
}
