# Legacy Role Extract — LEGACY — STRATEGY

> **Document Type:** STRATEGY
> **Authoritative Role:** `STRATEGY`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0064-STR-06`
> **Legacy Source ID:** `LS-WS-0064`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
> **Original SHA1:** `EA74B18E611681C8BFDFEA7F436AE16E2222F596`
> **Source Sections:** L3385-L3432 WL-CONTRACT-005 â€” ELIGIBILITY CONTRACT; L3433-L3489 WL-CONTRACT-006 â€” SCORING DETERMINISM CONTRACT
> **Extract Body SHA1:** `CA95F53C9B043E928C13E548F4D34AAB92AB30A9`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## WL-CONTRACT-005 â€” ELIGIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-005`

Title:
`ELIGIBILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` â€” no watchlist command/API exists yet.

Current gaps:

- Upstream market-data watchlist repository returns `elig.eligible = 1` rows only and publication/run scopes eligibility to the resolved readable publication.
- Watchlist service rechecks `eligibility_state` and excludes any non-eligible row if the upstream payload is malformed.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- Watchlist candidate universe contains only eligible tickers from the resolved market-data publication.
- Non-eligible rows are not silently accepted.
- Eligibility reason state remains traceable for downstream scoring/recommendation work.

Last update:
`2026-05-28 â€” WATCHLIST â€” MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

---

## WL-CONTRACT-006 â€” SCORING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-006`

Title:
`SCORING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` scoring input metric pass-through
- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` ticker id pass-through
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` ticker id read-surface pass-through

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` â€” no watchlist command/API exists yet.

Current gaps:

- Scoring engine foundation is baseline local PASS for Phase 3 unit/static scope.
- PLAN grouping foundation consumes scoring output and preserves deterministic sort keys for Phase 4 unit/static scope.
- `score_total` is deterministic `WEIGHTED_MEAN` over momentum, breakout, volume, and risk components.
- Component scores and total score are clamped to `0..1`.
- Ranking sort keys are deterministic: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- PLAN grouping deduplicates duplicate `ticker_id` by deterministic best item before active group assignment.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Same publication input + same paramset + same universe produces the same score and ranking.
- Tie-breaking is deterministic.
- Tests cover deterministic scoring output and deterministic PLAN grouping output.

Last update:
`2026-06-05 â€” WATCHLIST â€” PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---
