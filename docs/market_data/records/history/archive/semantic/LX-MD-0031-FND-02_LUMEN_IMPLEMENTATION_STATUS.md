# Legacy Semantic Extract — LX-MD-0031-FND-02

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `FINDING`
- Source range: `L4182-L4245`
- Extract body SHA1: `692B312D8856B874D49EE5A7C5F522C28A8680B6`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL SOURCE ACQUISITION FAILURE CLASSIFICATION + DIAGNOSTICS

[STATUS]
- `PATCHED_PENDING_OPERATOR_RUNTIME_VALIDATION`.
- This session fixes the runtime gap where API backfill lifecycle source acquisition errors could surface as generic command failure and where resume did not have explicit window/ticker acquisition checkpoints.
- Full production-ready claim still requires local operator validation because this audit sandbox cannot run PHPUnit: PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are unavailable.

[ROOT_CAUSE_CONFIRMED]
- `BackfillLifecycleCommand` previously mapped thrown acquisition failures through command-level fallback when the orchestrator did not return a source-stage summary.
- API HTTP 400 was classified through generic unexpected HTTP status handling instead of a domain source acquisition reason code.
- Resume cache was date/run oriented and did not expose retryable window/ticker acquisition state.
- No dedicated `source_acquisition_diagnostics.json` artifact existed for blocked source acquisition before date lifecycle processing.

[IMPLEMENTED_CHANGE]
- `PublicApiEodBarsAdapter` now classifies HTTP failures into source-domain codes including `RUN_SOURCE_BAD_REQUEST`, `RUN_SOURCE_INVALID_SYMBOL`, and `RUN_SOURCE_PROVIDER_REJECTED_RANGE`.
- Adapter telemetry now carries `http_status`, `provider_error_sample`, `failure_scope`, and `sanitized_url` without leaking token-like query parameters.
- Range acquisition now treats ticker-scoped bad request/invalid symbol/no-data failures as partial-tolerant while still escalating all-failed/systemic acquisition to blocked source failure.
- `ApiBackfillRangeAcquisitionService` now emits `source_acquisition_checkpoints` keyed by `window_start|window_end|ticker_code` with SUCCESS/FAILED state, reason code, HTTP status, attempts, and row count.
- `BackfillLifecycleOrchestrator` now writes `source_acquisition_checkpoint.json` and `source_acquisition_diagnostics.json`, returns stage-aware `status=BLOCKED` at `stage=SOURCE_ACQUISITION`, and preserves domain reason code instead of collapsing to `COMMAND_EXECUTION_FAILED`.
- `--resume --only-failed` now uses window/ticker checkpoint state and returns `NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT` instead of silently processing all when no failed acquisition checkpoint exists.
- `--diagnose-source` was added to lifecycle command for safe source acquisition diagnosis without promote/evidence/replay mutation.
- Reason-code registry/seed and logging/taxonomy docs were synchronized for the new source acquisition reason codes.

[VALIDATION_ADDED]
- `PublicApiEodBarsAdapterTest` now covers HTTP 400 domain classification, diagnostic context, URL sanitization, and partial range continuation when one ticker fails but another succeeds.
- `ApiBackfillRangeAcquisitionServiceTest` now covers window/ticker checkpoint output and resume-only-failed requesting only failed tickers.
- `ApiBackfillLifecycleStaticGuardTest` now covers source acquisition diagnostic artifact, checkpoint artifact, domain reason preservation, new command option, and checkpoint-aware resume guard.

[STATIC_VALIDATION_THIS_SESSION]
- `php -l app/Console/Commands/MarketData/BackfillLifecycleCommand.php` -> PASS.
- `php -l app/Application/MarketData/Services/BackfillLifecycleOrchestrator.php` -> PASS.
- `php -l app/Application/MarketData/Services/ApiBackfillRangeAcquisitionService.php` -> PASS.
- `php -l app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php` -> PASS.
- `php -l tests/Unit/MarketData/ApiBackfillLifecycleStaticGuardTest.php` -> PASS.
- `php -l tests/Unit/MarketData/ApiBackfillRangeAcquisitionServiceTest.php` -> PASS.
- `php -l tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php` -> PASS.

[OPERATOR_RUNTIME_VALIDATION_REQUIRED]
- `php artisan migrate --env=testing`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Backfill"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ApiBackfill"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "PublicApi"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Lifecycle"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"`.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"`.
- `vendor/bin/phpunit tests/Unit/MarketData`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --plan`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --with-evidence --with-replay -vvv`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --resume --only-failed -vvv`.
- Optional safe diagnostic: `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --diagnose-source -vvv`.

[EXPECTED_RUNTIME_BEHAVIOR]
- HTTP 400 source acquisition should output `stage=SOURCE_ACQUISITION`, `reason_code=RUN_SOURCE_BAD_REQUEST`, `source_acquisition_state=SYSTEMIC_FAILED` or `PARTIAL_SUCCESS`, `http_status=400`, failure scope, window/ticker context, and `diagnostic_path`.
- Partial ticker/window failure should proceed to date lifecycle only when acquisition is not systemic; coverage gate remains the owner of READABLE / NOT_READABLE decision.
- Replay fixture/verify remains skipped for failed/held/not-readable dates.
- No fake READABLE, no raw/latest/MAX(date) fallback, and no direct mutation of sealed/readable run/publication was added.

[REMAINING_RISK]
- External Yahoo/API provider can still block runtime with real HTTP 400/429/5xx; this is now expected to be diagnosable and reason-coded, not hidden as command infrastructure failure.
- Operator runtime proof is still required before changing this status to DONE / production-ready.

---


<!-- LEGACY_EXTRACT_BODY_END -->
