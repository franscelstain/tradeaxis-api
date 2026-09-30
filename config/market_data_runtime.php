<?php

/*
 * Market Data runtime selections that a run records about itself instead of resolving them into
 * its configuration.
 *
 * config/market_data.php is serialized whole into the resolved configuration snapshot, and every
 * run and V2 artifact binds that snapshot's content hash (MarketDataConfigSnapshotRepository::
 * currentContent). A key there must also be registered in the platform config registry, and
 * changing it changes the configuration identity of every run.
 *
 * The artifact hash profile is not configuration content. It selects the serializer, and each run
 * stores the one it used in eod_runs.artifact_hash_profile. It is read here so that runtime code
 * gets it through config() rather than env().
 */

return [
    // Governed runs default to market-data-semantic-hash/v2. Legacy verification harnesses opt
    // into market-data-row-hash/v1 explicitly (phpunit.xml). Any other value fails run creation.
    'artifact_hash_profile' => env('MARKET_DATA_ARTIFACT_HASH_PROFILE', 'market-data-semantic-hash/v2'),
];
