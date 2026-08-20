# Legacy Semantic Extract — LX-MD-0031-CTX-08

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `CONTEXT`
- Source range: `L4473-L4534`
- Extract body SHA1: `44BC9952DD13E02D81CF90A86851CA0C35E47727`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-27 - OUT-OF-ORDER IMPORT IMPACT PUBLICATION LIFECYCLE AUDIT CORRECTION

[AGGREGATE_STATUS]
- Superseded by later publication lifecycle and correction-current patches. The full out-of-order import publication lifecycle now has explicit non-readable promotion plus readable correction-current orchestration.
- `DONE` remains valid for:
  - recovered row partial apply,
  - mutation summary,
  - affected trading-date resolution,
  - actual indicator recompute execution for affected non-readable dates,
  - actual eligibility rebuild execution for affected non-readable dates,
  - correction-current candidate handling for already-readable affected dates,
  - command/evidence execution summary.

[NOT_FULLY_IMPLEMENTED]
- The impact executor itself still does not directly hash/seal/finalize; it emits publication reprocess candidates that lifecycle/full-publish paths consume through existing guarded promote flows.
- If correction baseline resolution or correction-current promotion fails, the current readable pointer remains unchanged and the failure is reported.

[CLAIM_BOUNDARY]
- Safe claim after the later patches: `OUT_OF_ORDER_IMPORT_IMPACT_REPROCESS_DONE_WITH_NON_READABLE_PROMOTE_AND_READABLE_CORRECTION_CURRENT`.
- Full-lock claims still require current PHPUnit/runtime proof, artifact evidence, and docs synchronization for the exact source tree under audit.

---

## 2026-05-27 - OUT-OF-ORDER IMPORT HASH SEAL REPUBLICATION EXECUTION

[STATUS]
- `DONE` for automatic hash/seal/finalize execution on affected downstream dates that are not already readable/current.
- `DONE` for `backfill:lifecycle` and `runDaily`/full-publish paths using existing `promoteDaily()` guard flow instead of duplicating hash/seal/finalize logic.
- `DONE` for lifecycle evidence/replay export of automatically republished non-readable affected dates when `--with-evidence` / `--with-replay` is requested.
- Superseded by the correction-current patch: dates that are already readable/current are routed to automated correction-current republication when the correction lifecycle can complete safely.

[IMPLEMENTED_CHANGE]
- `MarketDataImpactReprocessExecutor` now reports `publication_reprocess_summary.execution_state=PENDING_PROMOTE` after indicator/eligibility execution succeeds for affected non-readable dates.
- `BackfillLifecycleOrchestrator` consumes `PENDING_PROMOTE` candidate dates and calls `MarketDataPipelineService::promoteDaily()` for affected non-readable dates, which executes coverage, indicators, eligibility, hash, seal, and finalize through the existing publication guard.
- `MarketDataPipelineService::runDaily()`/full-publish now performs the same downstream non-readable publication reprocess after the primary requested date finalizes.
- The primary requested date is not left in a misleading pending state; if it was already handled by the primary promote flow, publication reprocess is reported as `NOOP` with `REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE`.
- Already-readable affected dates are routed to explicit correction/republication lifecycle; no current pointer is switched without correction lineage and finalize validation.

[VALIDATION_THIS_SESSION]
- `php artisan migrate --env=testing` -> `Nothing to migrate.`
- `vendor\bin\phpunit tests\Unit\MarketData --filter "BackfillLifecyclePublicationReprocess"` -> OK (4 tests, 19 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPipelineService"` -> OK (16 tests, 21 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataImpactReprocessExecutor"` -> OK (3 tests, 12 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Recovered"` -> OK (7 tests, 56 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Resume"` -> OK (8 tests, 61 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Backfill"` -> OK (47 tests, 303 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "ApiBackfill"` -> OK (25 tests, 153 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Indicator"` -> OK (26 tests, 231 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Eligibility"` -> OK (12 tests, 63 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Daily"` -> OK (63 tests, 1236 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Correction"` -> OK (75 tests, 1416 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "StaticGuard"` -> OK (225 tests, 5483 assertions).
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --plan` -> `PLAN_ONLY`, `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --resume --only-failed -vvv` -> expected external provider block for `WBSA`, `source_acquisition_state=FAILED_RETRY_BLOCKED`, `reason_code=RUN_SOURCE_BAD_REQUEST`; no recovered rows were applied because retry did not return rows.

[CLAIM_BOUNDARY]
- Safe claim: affected non-readable downstream dates can now be promoted through hash/seal/finalize automatically in lifecycle/full-publish paths.
- Safe claim: affected readable/current dates use correction-current republication with explicit correction lineage when automated correction workflow completes successfully.
- No DB schema change and no ENV/config key was added.

---


<!-- LEGACY_EXTRACT_BODY_END -->
