# Legacy Semantic Extract — LX-MD-0031-IMP-03

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `IMPLEMENTATION`
- Source range: `L4246-L4341`
- Extract body SHA1: `43F14D827E078B9C896F8C61011BEAC7231E47C7`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL CHECKPOINT + RESUME TELEMETRY CLEANUP

[STATUS]
- `DONE`.
- This cleanup locks checkpoint/diagnostic telemetry isolation for API range-window backfill retry flows.
- Full lifecycle API backfill success path was revalidated after the cleanup: requested dates still promote, export evidence, generate fixtures, replay verify, and become readable when coverage passes.

[ROOT_CAUSE_CONFIRMED]
- Failed checkpoint rows could fall back to aggregate window telemetry when ticker-specific failure context was missing, which risked stale HTTP status or response samples from another ticker.
- Resume-only-failed source retry used the same all-failed aggregate state path as initial acquisition, causing ticker-scoped HTTP 400 retry failure to appear as `SYSTEMIC_FAILED`.
- Resume-only-failed output did not expose enough accounting to prove every eligible failed checkpoint was retried or skipped with a reason.

[IMPLEMENTED_CHANGE]
- `PublicApiEodBarsAdapter` now records timeout/non-HTTP request failures with ticker/window URL context, `http_status=null`, sanitized `error_sample`, and no provider response sample when no HTTP response exists.
- `ApiBackfillRangeAcquisitionService` now keys failure context by `window_start|window_end|ticker_code` before writing checkpoint rows.
- Failed checkpoint rows now use only their own ticker/window context for `reason_code`, `http_status`, `error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `attempt_count`, and `rows_count`.
- Resume-only-failed now reports `failed_checkpoint_total`, `failed_checkpoint_eligible`, `failed_checkpoint_retried`, retry success/failure counts, skipped failed checkpoint count, and skipped reason counts.
- Resume-only-failed state mapping now uses `RETRY_SUCCESS`, `PARTIAL_RETRY_SUCCESS`, `FAILED_RETRY_BLOCKED`, or `NO_FAILED_CHECKPOINT`; ticker-scoped retry failure is no longer reported as `SYSTEMIC_FAILED`.
- `source_acquisition_diagnostics.json` now includes retry accounting and failure samples derived from checkpoint rows so diagnostic JSON and checkpoint JSON stay consistent.

[VALIDATION_ADDED]
- `ApiBackfillRangeAcquisitionServiceTest` covers timeout context isolation, no stale success/error sample leakage, HTTP 400 ticker/window context, retry accounting, skipped failed checkpoint reasons, and retry state mapping.
- `ApiBackfillLifecycleStaticGuardTest` covers retry accounting command output fields and per-window/ticker failure context mapping guards.

[RUNTIME_PROOF_THIS_SESSION]
- `php artisan migrate --env=testing --force` -> `Nothing to migrate.`
- `vendor\bin\phpunit tests\Unit\MarketData --filter PublicApi` -> OK (16 tests, 102 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter ApiBackfill` -> OK (20 tests, 134 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter Backfill` -> OK (39 tests, 273 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter StaticGuard` -> OK (214 tests, 5367 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData` -> OK (557 tests, 8484 assertions).
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --plan` -> `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`, `status=PLAN_ONLY`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --diagnose-source -vvv` -> `source_acquisition_state=PARTIAL_SUCCESS`, `failed_ticker_count=1`, `failed_window_count=1`, `status=SOURCE_DIAGNOSTIC_PARTIAL`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --with-evidence --with-replay -vvv` -> 4/4 requested dates `readable=YES`, evidence `EXPORTED`, fixture `GENERATED`, replay `VERIFIED`.
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --resume --only-failed -vvv` -> `status=BLOCKED`, `source_acquisition_state=FAILED_RETRY_BLOCKED`, `reason_code=RUN_SOURCE_BAD_REQUEST`, `failed_checkpoint_total=1`, `failed_checkpoint_retried=1`, `retry_failed_count=1`.
- Artifact check confirmed diagnostic sample and checkpoint row both point to `2026-01-01|2026-03-31|WBSA`, reason `RUN_SOURCE_BAD_REQUEST`, HTTP 400, failure scope `ticker`, sanitized Yahoo URL.

[REMAINING_RISK]
- WBSA remains blocked by Yahoo HTTP 400 for the warmup window; this is an external provider/data availability condition, not a checkpoint telemetry defect.
- Provider runtime can still vary by network/rate limit, but source retry diagnostics are now reason-coded and checkpoint-consistent.

---

## 2026-05-25 - API BACKFILL CHECKPOINT + RESUME MINOR NOTES CLEANUP

[STATUS]
- `DONE`.
- This final cleanup addresses the remaining minor notes from checkpoint/resume telemetry hardening without changing coverage, publishability, evidence, replay, or date-scoped lifecycle behavior.

[ROOT_CAUSE_CONFIRMED]
- `source_acquisition_diagnostics.json` was written before resume-only-failed summary finalization, so top-level `reason_code` could remain `null` even when failed checkpoint samples already carried a valid source reason.
- `source_acquisition_cache.json` still wrote full acquisition payloads, including canonical rows and large nested telemetry context, which made retry artifacts unnecessarily large.

[IMPLEMENTED_CHANGE]
- Diagnostic writer now resolves top-level `reason_code` deterministically:
  1. explicit summary/source reason wins,
  2. otherwise failed checkpoint reasons are counted,
  3. dominant reason wins,
  4. ties are resolved by `window_start`, `window_end`, `ticker_code`, then `reason_code`.
- Diagnostic/cache sample strings are redacted and capped at 500 characters with `...[truncated]` suffix when truncated.
- Acquisition cache writer now emits `cache_format=source_acquisition_resume_v2_slim`.
- Slim cache omits `rows_by_trade_date`, full `source_acquisition_checkpoints`, full provider payloads, and nested large failure contexts.
- Slim cache keeps operational proof fields: row counts by date, date/window telemetry summaries, failed checkpoint summary, failed checkpoint retry accounting, sanitized URL, reason/http/scope, and bounded samples.
- Existing checkpoint file remains the authoritative source for full per-window/ticker retry identity.

[VALIDATION_ADDED]
- `ApiBackfillLifecycleStaticGuardTest` now covers:
  - diagnostic top-level reason from failed checkpoint,
  - explicit summary reason precedence,
  - dominant reason selection,
  - no-op reason contract,
  - slim cache JSON validity,
  - cache omission of full rows/full checkpoints/nested failure contexts,
  - sample truncation and credential redaction.

[RUNTIME_PROOF_THIS_SESSION]
- `php artisan migrate --env=testing --force` -> `Nothing to migrate.`
- `vendor\bin\phpunit tests\Unit\MarketData --filter ApiBackfill` -> OK (25 tests, 153 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter Backfill` -> OK (44 tests, 292 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter StaticGuard` -> OK (219 tests, 5386 assertions).
- `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --resume --only-failed -vvv` -> expected `status=BLOCKED`, `source_acquisition_state=FAILED_RETRY_BLOCKED`, `reason_code=RUN_SOURCE_BAD_REQUEST`, failed checkpoint/retry counts all consistent for `WBSA`.
- Artifact check:
  - diagnostic top-level `reason_code=RUN_SOURCE_BAD_REQUEST`,
  - summary `reason_code=RUN_SOURCE_BAD_REQUEST`,
  - checkpoint key `2026-01-01|2026-03-31|WBSA` reason `RUN_SOURCE_BAD_REQUEST`,
  - cache format `source_acquisition_resume_v2_slim`,
  - cache size observed `48236` bytes after rewrite,
  - cache has no `rows_by_trade_date` and no full `source_acquisition_checkpoints`,
  - no `SECRET`/token leak found in cache.

[REMAINING_RISK]
- WBSA remains an external Yahoo HTTP 400/data-availability failure for the warmup window.
- Slim cache intentionally does not support row replay by itself; `--resume --only-failed` remains supported through the dedicated source acquisition checkpoint artifact.

---


<!-- LEGACY_EXTRACT_BODY_END -->
