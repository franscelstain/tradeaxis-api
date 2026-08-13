# WEEKLY SWING PRIORITY 1 INDICATOR EXTENSION INVENTORY

> **HISTORICAL AUDIT/IMPLEMENTATION EVIDENCE — NON-AUTHORITATIVE FOR CURRENT V2 STRATEGY.** This file preserves dated runtime/inventory facts and may contain legacy field names, command behavior, locks, or production claims from earlier contracts. Current strategy authority is the owner contracts + Blueprint + Conformance Matrix; current execution/conformance state is `MARKET_DATA_IMPLEMENTATION_LEDGER.md`; current audit verdict is `reports/AUDIT_FINAL_STATE.md`. Legacy statements are not current requirements unless explicitly re-admitted by those authorities.


## Status

`DONE_CURRENT_RANGE_PROMOTE_PASS_SECTOR_CODE_CURRENT_PASS_SECTOR_ROTATION_11_SECTORS_PASS_FULL_RANGE_EVIDENCE_REPLAY_PASS`

This inventory records the 2026-06-02 market-data extension for the first weekly-swing indicator tranche. Full MarketData PHPUnit passed, the 2023-01-02 through 2025-10-31 current readable publications were republished from existing current bars, sector membership was stamped into current indicators after operator import, 11 supplied sector index histories including `IDXPROPERT` were imported from `idxic_sector_index_bars.csv`, sector rotation values were recomputed into current publications where source-backed history and lookback exist, and full-range evidence/replay proof passed for all 672 current publications.

The `2023-01-02` through `2025-10-31` dates in this inventory are the indicator-extension proof window, not the end date of global market-data production readiness.

## Scope

- Add short-term equity momentum: `roc5`, `roc10`.
- Add 20-day range/support context: `ll20`, `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`.
- Add richer IHSG regime context: `ma20_slope_pct`, `close_to_ma20_pct`, `close_to_ma50_pct`.
- Add source-backed nullable `sector_code` membership context for future watchlist grouping/filtering.
- Add nullable sector-rotation context: `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg`.
- Preserve market-data as an upstream data provider only.

## Explicit Non-Scope

- No watchlist scoring, ranking, recommendation, buy/sell decision, entry/exit rule, risk/reward calculation, or backtest logic.
- No fake sector rotation strength values without sector index history; sector-rotation fields are source-backed and nullable.
- No event-risk placeholder fields without UMA/suspend/corporate-action source data.

## Schema / Storage

- Migration: `database/migrations/2026_06_02_000001_add_weekly_swing_priority1_indicators.php`.
- Sector migration: `database/migrations/2026_06_03_000001_add_sector_code_to_market_data_indicators.php`.
- Sector rotation migration: `database/migrations/2026_06_03_000002_add_sector_rotation_indicators.php`.
- Sector taxonomy/membership tables:
  - `market_data_sectors`
  - `ticker_sector_memberships`
- Equity tables updated:
  - `eod_indicators`
  - `eod_indicators_history`
- Benchmark table updated:
  - `market_benchmark_indicators`

## Runtime Owners

- Equity formula computation: `app/Application/MarketData/Services/IndicatorVectorService.php`.
- Sector membership resolution/import: `app/Infrastructure/Persistence/MarketData/SectorClassificationRepository.php` and `market-data:sectors:import-memberships`.
- Sector index bar import: `market-data:sector-indexes:import-bars` for CSV/audited input and `market-data:sector-indexes:ingest-api` for provider API input.
- Benchmark/IHSG formula computation: `app/Application/MarketData/Services/BenchmarkIndicatorVectorService.php`.
- Indicator history copy and promote copy: `app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php`.
- Hash/seal indicator column list: `app/Application/MarketData/Services/MarketDataPipelineService.php`.
- Watchlist read output: `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`.
- Benchmark read output: `app/Infrastructure/Persistence/MarketData/MarketBenchmarkReadRepository.php`.

## Formula Definitions

- `roc5 = (P(D) / P(D[-5])) - 1`.
- `roc10 = (P(D) / P(D[-10])) - 1`.
- `ll20 = min(low)` over `D[-19] ... D`.
- `close_to_ll20_pct = ((P(D) - ll20) / ll20) * 100`.
- `range_20_pct = ((hh20 - ll20) / ll20) * 100`.
- `range_position_20_pct = ((P(D) - ll20) / (hh20 - ll20)) * 100`.
- IHSG `ma20_slope_pct = ((ma20_today - ma20_5_trading_days_ago) / ma20_5_trading_days_ago) * 100`.
- IHSG `close_to_ma20_pct = ((close - ma20) / ma20) * 100`.
- IHSG `close_to_ma50_pct = ((close - ma50) / ma50) * 100`.
- `sector_code` is resolved from the effective ticker-sector membership on D and remains NULL when no source-backed membership exists.
- `sector_roc20` is resolved from `market_benchmark_indicators.roc_20` for the active sector index on D.
- `rs_20_vs_sector = (roc20 * 100) - sector_roc20`.
- `sector_rs_20_vs_ihsg = sector_roc20 - IHSG_roc_20`.

## Fail-Safe Behavior

- Insufficient required history returns NULL indicator values and reason-coded invalid rows according to the existing indicator validity contract.
- Non-positive denominator returns NULL.
- Flat 20-day range (`hh20 - ll20 <= 0`) returns `range_position_20_pct = NULL`.
- Missing sector membership returns `sector_code = NULL` and does not invalidate the indicator row.
- Missing sector index history returns `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` as NULL and does not invalidate the indicator row.
- No zero-fill, forward-fill, calendar interpolation, or fake benchmark values are allowed.

## Validation

- `php -l` passed for touched PHP service/repository/migration files.
- `php artisan migrate --env=testing` -> migrated `2026_06_02_000001_add_weekly_swing_priority1_indicators` (174.51ms).
- `vendor\bin\phpunit tests\Unit\MarketData --filter IndicatorVectorServiceTest` -> OK (10 tests, 76 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter BenchmarkIndicatorVectorServiceTest` -> OK (3 tests, 21 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketBenchmarkReadModel` -> OK (3 tests, 23 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketDataWatchlistReadModel` -> OK (3 tests, 28 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketDataSqliteSchemaSync` -> OK (5 tests, 214 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter AuditDocsSynchronizationStaticGuardTest` -> OK (11 tests, 581 assertions).
- `php artisan migrate --env=testing` -> migrated `2026_06_03_000001_add_sector_code_to_market_data_indicators` (308.53ms).
- `php artisan migrate --env=testing` -> migrated `2026_06_03_000002_add_sector_rotation_indicators` (147.85ms); `.env` normal migration passed (77.11ms).
- `vendor\bin\phpunit tests\Unit\MarketData --filter StaticGuard` -> OK (226 tests, 5660 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData` -> OK (600 tests, 9043 assertions).
- `SectorClassificationRepositoryTest` -> OK (2 tests, 6 assertions).
- `ImportSectorMembershipCommandTest` -> OK (3 tests, 18 assertions).
- `ImportSectorIndexBarsCommandTest` -> OK (3 tests, 17 assertions).
- `IngestSectorIndexBarsApiCommandTest` -> OK (3 tests, 22 assertions).
- Operator CSV/DB trace: uploaded `eod_runs.csv` and `eod_publications.csv` each contained 1,321 rows; local DB matched 672 current readable final publications and 649 non-current candidate publications before republish.
- Runtime promote proof: 672/672 current readable publications for 2023-01-02 through 2025-10-31 were republished from existing current bars with `force_replace_reason=weekly_swing_priority1_indicator_extension_republish_from_existing_current_bars`.
- Runtime summary artifact: `storage/app/market_data/evidence/weekly_swing_priority1_runtime/promote_force_final_summary.json` records `runtime_status=PASS`, `current_readable_pass_count=672`, `current_new_run_gt_1321_count=672`, `current_old_run_le_1321_count=0`, `current_min_run_id=1323`, and `current_max_run_id=1994`.
- Indicator aggregate after republish: `rows_total=591187`, `valid_rows=573007`, `valid_roc5_null=0`, `valid_roc10_null=0`, `valid_ll20_null=0`, `valid_range20_null=0`; `valid_rangepos_null=62475` is allowed by the flat-range NULL rule.
- Sector-code membership proof: operator-local `.env` has `sector_memberships=913`; controlled sector-code/rotation republish produced 672/672 current readable dates with current run id range `3339-4010`; `eod_indicators` has `sector_code_not_null=591187`, `sector_code_null=0`.
- Initial 10-sector CSV dry-run proof: `market-data:sector-indexes:import-bars storage/app/market_data/sectors/idxic_sector_index_bars.csv --dry-run -vvv` -> `status=DRY_RUN`, `row_count=6740`, `valid_row_count=6740`, `error_count=0`, benchmark codes `IDXBASIC,IDXCYCLIC,IDXENERGY,IDXFINANCE,IDXHEALTH,IDXINDUST,IDXINFRA,IDXNONCYC,IDXTECHNO,IDXTRANS`.
- Initial 10-sector CSV apply proof: `market-data:sector-indexes:import-bars storage/app/market_data/sectors/idxic_sector_index_bars.csv --apply -vvv` -> `status=APPLIED`, `row_count=6740`, `valid_row_count=6740`, `upserted_count=6740`, `error_count=0`; this proof is superseded by the later DB proof showing 11 sector indexes including `IDXPROPERT`.
- Sector benchmark bars proof after import: `market_benchmark_bars` has `manual_sector_index_csv row_count=8886`, `benchmark_count=11`, range `2023-01-02` to `2026-06-03`; `IDXPROPERT` has `row_count=806`, range `2023-01-02` to `2026-06-03`. Classification `Z` is a listed-investment-product bucket, not one of the 11 equity sector indexes.
- Sector benchmark indicator proof after `IDXPROPERT` republish: 11 imported sector indexes have 7,392 `market_benchmark_indicators` rows over the current publication range `2023-01-02` to `2025-10-31`, with `roc20_not_null=7172` and `roc20_null=220`; the first 20 trading dates per sector are NULL by lookback contract.
- Sector rotation current indicator proof after `IDXPROPERT` republish: current `eod_indicators` has `total=591187`, `sector_code_not_null=591187`, `sector_roc20_not_null=573007`, `rs_20_vs_sector_not_null=573007`, `sector_rs_20_vs_ihsg_not_null=573007`, and `sector_roc20_null=18180`; sector `H` now has `sector_roc20_not_null=58215` and `sector_roc20_null=1840`, with remaining NULLs explained by insufficient-history/lookback behavior.
- Sector index API live dry-run proof: `market-data:sector-indexes:ingest-api 2025-10-31 --dry-run --continue_on_error` returned `status=BLOCKED`, `reason_code=SECTOR_INDEX_API_INGEST_INCOMPLETE`, `fetched_row_count=0`, `upserted_count=0`, and missing default `.JK` sector symbols, so no sector bars were written without a valid provider mapping.
- Evidence sample proof: `market-data:evidence:export --run_id=1994` produced `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=10`.
- Replay sample proof: `market-data:replay:verify 1994 ...` produced `replay_id=673`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`; replay evidence export for `replay_id=673` with explicit `--trade_date=2025-10-31` produced `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=6`.
- Full-range evidence/replay proof after `IDXPROPERT` republish: `market-data:evidence-replay:full-range-current 2023-01-02 2025-10-31 --continue_on_error -vvv` -> exit 0, `trading_date_count=672`, `processed_count=672`, `success_count=672`, `failed_count=0`, `error_count=0`, `all_passed=1`, replay id range `3362-4033`.
- Full-range summary/DB proof after `IDXPROPERT` republish: run/publication id range `3339-4010`, unique run/publication ids `672`, `match_count=672`, `replay_pass_count=672`, `run_admitted_count=672`, `replay_admitted_count=672`, and `zero_mismatch_count=672`; output root `storage/app/market_data/evidence/full_range_current_evidence_replay/full_range_current_2023-01-02_to_2025-10-31_20260604_042854` contains per-date run evidence, generated fixture, replay evidence, and summary artifacts for all 672 current publications.

## Scope Notes (Non-Blocking)

- Daily/API import was not repeated because OHLC bars were already operator-confirmed and present; the runtime action was promote/reseal/republication from existing current bars.
- Classification `Z` is intentionally excluded from sector-rotation benchmark matching because it is a listed-investment-product bucket, not one of the 11 equity sector indexes.
- Sector index API import tooling is available, but provider symbol availability/mapping remains a source-data dependency and empty provider responses are blocked instead of being treated as valid bars.
- Event-risk flags require source work for suspend/UMA/corporate-action data; this is non-scope for Priority 1.

## Verdict

The first weekly-swing indicator tranche is done, full-suite tested, runtime-republished, sector-code populated, sector rotation populated for all 11 supplied equity sector indexes with sufficient lookback, and full-range evidence/replay proven across the operator-provided historical proof window. It improves market-data readiness for weekly-swing watchlist consumers and has no remaining blocker for the Priority 1 proof-window scope.
