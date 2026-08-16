# C168 Weekly Swing Watchlist Runtime Integration Gap Remediation

## Purpose

C168 replaces declaration-only evidence with one executable, publication-aware path from Market Data to validated stock-ticker Watchlist output. The scope is deliberately limited to proving this integration before any production activation, canonical PLAN/CONFIRM mutation, rollout, or official publication.

## Implemented Runtime Chain

```text
current readable Market Data publication
  -> WatchlistMarketDataConsumerReadService
  -> WatchlistCandidateUniverseService
  -> WatchlistScoringService
  -> WatchlistPlanGroupingService
  -> WatchlistRecommendationService
  -> validated stock-ticker Watchlist JSON
```

The orchestration entry point is `WeeklySwingWatchlistRuntimeService::execute()`, exposed through:

```powershell
php artisan watchlist:weekly-swing-generate --trade-date=YYYY-MM-DD
```

The command requires an explicit trade date, reads only through the producer-facing Market Data contract, and is registered without scheduler activation.

## Runtime Output Contract

Every emitted stock row includes:

- ticker code, id, and name;
- requested and effective trade date;
- publication id/version and run id;
- positive close price and indicator-set version;
- recommendation and PLAN ranks/groups;
- score components, source market metrics, and reason codes.

The runtime rejects strategy candidate codes such as `C61_*_CANDIDATE` as stock tickers. It also rejects missing or mismatched lineage, invalid ticker identity, non-positive close price, and non-numeric recommendation score.

Output identity uses:

- canonical paramset hash;
- Market Data publication/run lineage;
- capital-input hash;
- idempotency key;
- stable output hash.

An existing output with the same identity is returned idempotently. A different identity at the same path fails closed unless the operator supplies explicit `--overwrite`.

## Executed Local Runtime Evidence

The command was executed against the current local readable publication:

```text
TRADE_DATE_REQUESTED=2026-07-23
TRADE_DATE_EFFECTIVE=2026-07-23
PUBLICATION_ID=67009
PUBLICATION_VERSION=5
RUN_ID=66354
POINTER_RESOLVE_STATUS=RESOLVED_READABLE_CURRENT
MARKET_DATA_CANDIDATE_COUNT=826
MARKET_DATA_EXCLUDED_COUNT=33
ELIGIBLE_CANDIDATE_COUNT=373
REJECTED_CANDIDATE_COUNT=453
SCORED_CANDIDATE_COUNT=373
PLAN_TOP_PICKS_COUNT=5
PLAN_SECONDARY_COUNT=10
RECOMMENDATION_EVALUATED_COUNT=15
RECOMMENDED_STOCK_COUNT=3
WATCHLIST_TICKERS=FUTR,SMIL,INPS
```

Persisted proof:

```text
OUTPUT_PATH=storage/app/watchlist/runtime/c168-weekly-swing-watchlist-2026-07-23.json
OUTPUT_HASH=fa89e71a6087bf5bc0716ebd51b0d02b8c295521
OUTPUT_FILE_SHA1=61958F0C67D8719658AECB3A553E158898B36E30
IDEMPOTENCY_KEY=7eff26b11e81a5af687eaf945cad5f1db6154946
STATUS=C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED_REAL_TICKER_WATCHLIST_GENERATED
```

## Validation Log

```text
php -l app/Application/Watchlist/Services/WeeklySwingWatchlistRuntimeService.php
PASS

php -l app/Console/Commands/Watchlist/GenerateWeeklySwingWatchlistCommand.php
PASS

php vendor/bin/phpunit tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeServiceTest.php
OK (5 tests, 76 assertions)

php vendor/bin/phpunit tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeStaticGuardTest.php
OK (5 tests, 35 assertions)

php vendor/bin/phpunit --filter "WeeklySwingWatchlistRuntime|WatchlistMarketDataConsumerReadService|WatchlistCandidateUniverseService|WatchlistScoringService|WatchlistPlanGroupingService|WatchlistRecommendationService|MarketDataWatchlistReadModel|MarketDataPipelineIntegration"
OK (113 tests, 1830 assertions)

php vendor/bin/phpunit tests/Unit/Watchlist
OK (7042 tests, 47588 assertions)
```

The integration fixture was corrected to use the producer's fractional ATR contract (`0.021` for 2.1%), matching the production indicator calculation and Weekly Swing policy. No percent-point conversion was added to the production consumer.

## Scope Boundary

```text
REAL_RUNTIME_INTEGRATION_EXECUTED=1
REAL_MARKET_DATA_CONSUMED=1
REAL_STOCK_OUTPUT_GENERATED=1
CONTROLLED_RUNTIME_OUTPUT_GENERATED=1
PRODUCTION_RUNTIME_ACTIVATED=0
PLAN_CONFIRM_MUTATED=0
CONTROLLED_ROLLOUT_EXECUTED=0
OFFICIAL_OUTPUT_PUBLISHED=0
PRODUCTION_CATALOG_STRATEGY_BINDING_STATE=NOT_CLAIMED_BY_C168_RUNTIME_INTEGRATION_PROOF
```

C168 does not certify the prior C115-C167 activation and rollout declarations. C167 remains incomplete. The C168 runtime artifact is controlled integration evidence, not a canonical persisted PLAN/CONFIRM artifact and not an officially published production watchlist.

## Gap and Next Session

C168 closes the real Market Data-to-ticker integration gap. A post-session document/code/database parity audit corrected the previously broad next-step wording:

- the Watchlist core paramset/PLAN tables were not yet present;
- the official `watchlist_bt_oos_eval_ws` table contained zero rows;
- C64's OOS-looking scorecard was synthesized from IS/default scenario data and could not be used as canonical promotion proof;
- no production-approved ACTIVE paramset could therefore be bound;
- PLAN and RECOMMENDATION persistence could not be designed or executed as one combined step before ACTIVE existed.

The corrected immediate continuation is:

```text
C169_WEEKLY_SWING_CANONICAL_PARAMSET_PERSISTENCE_AND_REAL_OOS_PROMOTION_GATE_REMEDIATION
```

C169 must create the canonical schema, validate and persist an exact DRAFT binding, and prove promotion against the official persisted IS/OOS tables. The remaining order is then:

1. remediate the real canonical IS strategy and produce official persisted OOS proof;
2. promote the unchanged DRAFT only after that exact proof passes;
3. persist canonical PLAN;
4. define and persist RECOMMENDATION from canonical PLAN;
5. proceed separately to CONFIRM, activation, controlled rollout, and observation.
