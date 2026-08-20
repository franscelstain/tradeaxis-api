# Legacy Semantic Extract — LX-MD-0031-CTX-07

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `CONTEXT`
- Source range: `L4411-L4452`
- Extract body SHA1: `5E98781618E25CAE452142BF5B3AFF72D3C7EA85`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-27 - OUT-OF-ORDER IMPORT IMPACT EXECUTION + RECOVERED ROW APPLY

[STATUS]
- `DONE` for recovered checkpoint partial-row apply, actual affected indicator recompute execution, actual affected eligibility rebuild execution, command/evidence execution summaries, and correction-safe handling of already-readable affected publications.
- Superseded by the later correction-current patch: readable affected dates are now correction-current candidates when automated correction-current promotion can complete.

[GAP_CLOSED]
- `market-data:backfill:lifecycle --resume --only-failed` no longer stops at source acquisition when retry succeeds and recovered rows are available.
- Recovered rows are applied through partial ticker/date upsert, not full-date `replaceBars()`, so unrelated ticker rows on the same trade date are preserved.
- Changed recovered/historical bars now flow into affected-date execution: indicators are recomputed and eligibility is rebuilt for affected non-readable dates.
- Execution output now distinguishes detection (`indicator_impact_summary`) from execution (`indicator_reprocess_execution_summary`, `eligibility_reprocess_execution_summary`, `publication_reprocess_summary`).

[IMPLEMENTED_CHANGE]
- Added `EodArtifactRepository::upsertBarsPartial()` for idempotent recovered row apply. It writes only inserted/updated ticker rows and leaves unchanged rows plus unrelated tickers untouched.
- Added `EodBarsIngestService::ingestRecoveredRowsPartial()` and `MarketDataPipelineService::applyRecoveredRowsPartial()`.
- Added `MarketDataImpactReprocessExecutor` to execute full-date indicator recompute and eligibility rebuild for affected non-readable dates.
- Already-readable affected dates are excluded from silent indicator/eligibility/hash/pointer mutation and must be routed to correction-current publication reprocess candidates before any pointer-visible replacement.
- Backfill lifecycle resume-only-failed summary now includes recovered row apply counts, changed bar counts, and reprocess execution states.

[VALIDATION_THIS_SESSION]
- `php artisan migrate --env=testing` -> `Nothing to migrate.`
- `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataImpactReprocessExecutor"` -> OK (3 tests, 11 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "EodArtifactRepositoryPartialUpsert"` -> OK (2 tests, 14 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "OutOfOrderImportImpact"` -> OK (5 tests, 57 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Recovered"` -> OK (7 tests, 56 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Resume"` -> OK (8 tests, 61 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPipelineService"` -> OK (15 tests, 19 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Indicator"` -> OK (26 tests, 229 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Eligibility"` -> OK (12 tests, 61 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Backfill"` -> OK (44 tests, 292 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "ApiBackfill"` -> OK (25 tests, 153 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Daily"` -> OK (62 tests, 1234 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Correction"` -> OK (75 tests, 1416 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "StaticGuard"` -> OK (224 tests, 5467 assertions).
- Historical note: at this checkpoint the full MarketData suite still had to be rerun before changing status to full locked. Later full-suite proof closed that requirement, and the latest docs-review refresh passed `vendor\bin\phpunit` with OK (641 tests, 9547 assertions).

[REMAINING_RISK]
- Automated correction/republication for already-readable affected downstream dates remains correction-guarded: baseline resolution, approval, correction-current promotion, seal/finalize, and pointer validation must pass or the current pointer remains unchanged.
- No DB schema change and no ENV/config key was added.

---


<!-- LEGACY_EXTRACT_BODY_END -->
