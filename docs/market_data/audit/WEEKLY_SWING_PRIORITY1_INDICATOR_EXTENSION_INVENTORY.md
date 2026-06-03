# WEEKLY SWING PRIORITY 1 INDICATOR EXTENSION INVENTORY

## Status

`DONE_CURRENT_RANGE_PROMOTE_PASS_SAMPLE_EVIDENCE_REPLAY_PASS`

This inventory records the 2026-06-02 market-data extension for the first weekly-swing indicator tranche. Full MarketData PHPUnit passed, and the 2023-01-02 through 2025-10-31 current readable publications were republished from existing current bars. Full-range evidence/replay is optional exhaustive validation, not a blocker for this scoped Priority 1 completion.

## Scope

- Add short-term equity momentum: `roc5`, `roc10`.
- Add 20-day range/support context: `ll20`, `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`.
- Add richer IHSG regime context: `ma20_slope_pct`, `close_to_ma20_pct`, `close_to_ma50_pct`.
- Preserve market-data as an upstream data provider only.

## Explicit Non-Scope

- No watchlist scoring, ranking, recommendation, buy/sell decision, entry/exit rule, risk/reward calculation, or backtest logic.
- No sector rotation placeholder fields without sector master/index source data.
- No event-risk placeholder fields without UMA/suspend/corporate-action source data.

## Schema / Storage

- Migration: `database/migrations/2026_06_02_000001_add_weekly_swing_priority1_indicators.php`.
- Equity tables updated:
  - `eod_indicators`
  - `eod_indicators_history`
- Benchmark table updated:
  - `market_benchmark_indicators`

## Runtime Owners

- Equity formula computation: `app/Application/MarketData/Services/IndicatorVectorService.php`.
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

## Fail-Safe Behavior

- Insufficient required history returns NULL indicator values and reason-coded invalid rows according to the existing indicator validity contract.
- Non-positive denominator returns NULL.
- Flat 20-day range (`hh20 - ll20 <= 0`) returns `range_position_20_pct = NULL`.
- No zero-fill, forward-fill, calendar interpolation, or fake benchmark values are allowed.

## Validation

- `php -l` passed for touched PHP service/repository/migration files.
- `php artisan migrate --env=testing` -> migrated `2026_06_02_000001_add_weekly_swing_priority1_indicators` (174.51ms).
- `vendor\bin\phpunit tests\Unit\MarketData --filter IndicatorVectorServiceTest` -> OK (10 tests, 76 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter BenchmarkIndicatorVectorServiceTest` -> OK (3 tests, 21 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketBenchmarkReadModel` -> OK (3 tests, 23 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketDataWatchlistReadModel` -> OK (3 tests, 28 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter MarketDataSqliteSchemaSync` -> OK (5 tests, 214 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter AuditDocsSynchronizationStaticGuardTest` -> OK (11 tests, 576 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter StaticGuard` -> OK (226 tests, 5527 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData` -> OK (586 tests, 8771 assertions).
- Operator CSV/DB trace: uploaded `eod_runs.csv` and `eod_publications.csv` each contained 1,321 rows; local DB matched 672 current readable final publications and 649 non-current candidate publications before republish.
- Runtime promote proof: 672/672 current readable publications for 2023-01-02 through 2025-10-31 were republished from existing current bars with `force_replace_reason=weekly_swing_priority1_indicator_extension_republish_from_existing_current_bars`.
- Runtime summary artifact: `storage/app/market_data/evidence/weekly_swing_priority1_runtime/promote_force_final_summary.json` records `runtime_status=PASS`, `current_readable_pass_count=672`, `current_new_run_gt_1321_count=672`, `current_old_run_le_1321_count=0`, `current_min_run_id=1323`, and `current_max_run_id=1994`.
- Indicator aggregate after republish: `rows_total=591187`, `valid_rows=573007`, `valid_roc5_null=0`, `valid_roc10_null=0`, `valid_ll20_null=0`, `valid_range20_null=0`; `valid_rangepos_null=62475` is allowed by the flat-range NULL rule.
- Evidence sample proof: `market-data:evidence:export --run_id=1994` produced `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=10`.
- Replay sample proof: `market-data:replay:verify 1994 ...` produced `replay_id=673`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`; replay evidence export for `replay_id=673` with explicit `--trade_date=2025-10-31` produced `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=6`.

## Remaining Gaps

- Full-range evidence/replay proof across all 672 republished dates is optional exhaustive validation, not a blocker for this scoped indicator-extension completion.
- Daily/API import was not repeated because OHLC bars were already operator-confirmed and present; the runtime action was promote/reseal/republication from existing current bars.
- Sector rotation requires source work for sector master/index history; this is non-scope for Priority 1.
- Event-risk flags require source work for suspend/UMA/corporate-action data; this is non-scope for Priority 1.

## Verdict

The first weekly-swing indicator tranche is done, full-suite tested, and runtime-republished across the operator-provided historical current-readable range. It improves market-data readiness for weekly-swing watchlist consumers and has no remaining blocker for the Priority 1 current-range scope.
