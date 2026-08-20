# Legacy Semantic Extract — LX-MD-0053-EVD-01

- Source ID: `LS-MD-0053`
- Original path: `audit/WEEKLY_SWING_PRIORITY1_INDICATOR_EXTENSION_INVENTORY.md`
- Original SHA1: `7DE98BB33121A3E580DB11E5BEE81D00CEC53353`
- Extract role: `EVIDENCE`
- Source range: `L79-L112`
- Extract body SHA1: `C545495A702280CA7F23C7B4E40627069BE9725B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
