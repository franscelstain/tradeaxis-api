# Legacy Semantic Extract — LX-MD-0030-IMP-03

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `IMPLEMENTATION`
- Source range: `L4043-L4080`
- Extract body SHA1: `C99C14EEAE55C99C2AC2D9CD636B1BDC14502B7D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL CHECKPOINT + RESUME TELEMETRY CLEANUP CONTRACT UPDATE

[CONTRACT_STATUS]
- `DONE`.

[CHECKPOINT_TELEMETRY_CONTRACT]
- Failed API source acquisition checkpoint context is keyed by `window_start|window_end|ticker_code`.
- Checkpoint fields `reason_code`, `http_status`, `error_sample`, `provider_error_sample`, `sanitized_url`, `failure_scope`, `attempt_count`, and `rows_count` must come from the same ticker/window key.
- Timeout/non-HTTP failures must use `http_status=null`, sanitized timeout/error message in `error_sample`, `provider_error_sample=null`, and ticker-specific sanitized URL when available.
- Successful checkpoint rows must not inherit stale error samples from prior failed ticker requests.

[RESUME_ONLY_FAILED_CONTRACT]
- `--resume --only-failed` must expose `failed_checkpoint_total`, `failed_checkpoint_eligible`, `failed_checkpoint_retried`, `retry_success_count`, `retry_failed_count`, `failed_checkpoint_skipped`, and `skipped_failed_checkpoint_reasons`.
- Eligible failed checkpoints must all be retried; any skipped failed checkpoint must have an explicit reason such as `WINDOW_OUT_OF_SCOPE`, `TICKER_NOT_IN_CURRENT_UNIVERSE`, or `CHECKPOINT_CORRUPTED`.
- Source retry state mapping:
  - all retry success -> `RETRY_SUCCESS`
  - mixed retry success/failure -> `PARTIAL_RETRY_SUCCESS`
  - ticker-scoped retry failure -> `FAILED_RETRY_BLOCKED`
  - no failed checkpoint -> `NO_FAILED_CHECKPOINT`
  - true systemic/provider/config failure may still use `SYSTEMIC_FAILED`
- `FAILED_RETRY_BLOCKED` is not publishable/readable and must not generate replay fixtures.

[DIAGNOSTIC_CONSISTENCY_CONTRACT]
- `source_acquisition_diagnostics.json` must include retry accounting when resume-only-failed is used.
- Diagnostic failure samples must match failed checkpoint ticker/window context.
- Diagnostic and checkpoint URLs must be sanitized and must not leak token-like query parameters.

[VALIDATION_PROOF]
- Targeted PHPUnit filters after patch:
  - `PublicApi` -> OK (16 tests, 102 assertions)
  - `ApiBackfill` -> OK (20 tests, 134 assertions)
  - `Backfill` -> OK (39 tests, 273 assertions)
  - `StaticGuard` -> OK (214 tests, 5367 assertions)
- Full MarketData unit proof: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (557 tests, 8484 assertions).
- Runtime resume-only-failed proof: `source_acquisition_state=FAILED_RETRY_BLOCKED`, `reason_code=RUN_SOURCE_BAD_REQUEST`, failed checkpoint/retry counts all 1, failed ticker/window `WBSA` / `2026-01-01` to `2026-03-31`.

---


<!-- LEGACY_EXTRACT_BODY_END -->
