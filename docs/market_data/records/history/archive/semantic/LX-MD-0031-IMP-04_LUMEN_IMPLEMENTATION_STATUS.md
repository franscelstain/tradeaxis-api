# Legacy Semantic Extract — LX-MD-0031-IMP-04

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `IMPLEMENTATION`
- Source range: `L4365-L4410`
- Extract body SHA1: `95DA5C1720977CE35051A96D029960BD6D5ED45E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-26 - OUT-OF-ORDER IMPORT INDICATOR DEPENDENCY IMPACT PATCH

[STATUS]
- `DONE` for mutation detection, affected trading-date resolution, operator/artifact telemetry, and readable-publication impact detection.
- Superseded by the later correction-current patch: already-readable affected downstream dates now flow into automated correction-current republication when the lifecycle can safely create/approve a correction and promote the replacement.

[ROOT_CAUSE_CONFIRMED]
- Normal EOD bar ingest used `EodArtifactRepository::replaceBars()` as a full-date artifact replacement and did not previously return a changed/unchanged bar set to the pipeline.
- Indicator computation is date-scoped, so out-of-order historical imports needed explicit impact telemetry to show when downstream rolling indicators may be affected.
- The existing sealed/current/readable mutation guard already prevents direct silent mutation of readable live artifacts, but the import output did not expose downstream impact in a structured way.

[IMPLEMENTED_CHANGE]
- `EodArtifactRepository::replaceBars()` now compares incoming canonical bars with existing rows before replacement and returns `bar_mutation_summary`.
- Mutation summary distinguishes `inserted_bar_count`, `updated_bar_count`, `unchanged_bar_count`, `removed_bar_count`, `changed_bar_count`, changed ticker ids, and changed trade dates.
- New `EodBarsMutationImpactResolver` resolves affected dates with `market_calendar` trading days and a max dependency horizon derived from active indicator config plus MA50 floor.
- Impact summary reports `affected_ticker_count`, `affected_trade_date_count`, `affected_start_date`, `affected_end_date`, `max_dependency_trading_days`, and `indicator_reprocess_state`.
- If any affected date already has a current readable publication, the publication impact is reported as `REQUIRES_REPUBLICATION` with reason `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`; no silent live update is performed.
- Daily, import-only backfill, lifecycle backfill case output, run summary artifacts, and evidence run summaries now carry mutation/indicator/publication impact fields.

[VALIDATION_ADDED]
- Added `EodBarsMutationImpactResolverTest` covering unchanged NOOP, historical downstream impact over trading days, and readable-publication impact requiring republication/correction.
- Existing `EodBarsIngestService`, `MarketDataPipelineService`, backfill, API backfill, daily, correction, indicator, eligibility, and static guard tests were rerun.

[RUNTIME_PROOF_THIS_SESSION]
- `php artisan migrate --env=testing` -> `Nothing to migrate.`
- `vendor\bin\phpunit tests\Unit\MarketData --filter "EodBarsMutationImpactResolver"` -> OK (3 tests, 13 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "EodBarsIngestService"` -> OK (4 tests, 31 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPipelineServiceTest"` -> OK (14 tests, 17 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Backfill"` -> OK (44 tests, 292 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "ApiBackfill"` -> OK (25 tests, 153 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "OutOfOrderImportImpact"` -> OK (3 tests, 32 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "StaticGuard"` -> OK (222 tests, 5430 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Indicator"` -> OK (23 tests, 215 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Eligibility"` -> OK (9 tests, 47 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Daily"` -> OK (62 tests, 1234 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Correction"` -> OK (75 tests, 1416 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData` -> OK (568 tests, 8560 assertions).
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --plan` -> `PLAN_ONLY`, `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`.

[REMAINING_RISK]
- Resume-only-failed source retry success still needs a dedicated partial-row recovery apply path before it can safely import recovered ticker rows without replacing an entire date artifact. This patch does not fake that behavior.
- Already-readable affected downstream dates are detected and marked as requiring correction/republication; automated correction request creation/execution remains a separate operator-controlled lifecycle.
- No DB schema or ENV/config key was added.

---


<!-- LEGACY_EXTRACT_BODY_END -->
