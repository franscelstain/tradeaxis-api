# Legacy Semantic Extract — LX-MD-0179-CTX-04

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `CONTEXT`
- Source range: `L121-L164`
- Extract body SHA1: `8599C0E55494302449C8927DBE507E7BECA1B6B5`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Source retry diagnostics
For `source_mode=api`, the lifecycle command also writes:
- `source_acquisition_checkpoint.json`
- `source_acquisition_diagnostics.json`

`--resume --only-failed` is a source acquisition retry mode for failed window/ticker checkpoints. Its output must include:
- `failed_checkpoint_total`
- `failed_checkpoint_eligible`
- `failed_checkpoint_retried`
- `retry_success_count`
- `retry_failed_count`
- `failed_checkpoint_skipped`
- `skipped_failed_checkpoint_reasons`

Retry state meaning:
- `RETRY_SUCCESS`: all eligible failed checkpoints were retried successfully.
- `PARTIAL_RETRY_SUCCESS`: some eligible failed checkpoints succeeded and some still failed.
- `FAILED_RETRY_BLOCKED`: retry remained blocked at ticker/window scope and is not readable/replayable.
- `NO_FAILED_CHECKPOINT`: no failed source acquisition checkpoint exists for the requested resume scope.
- `SYSTEMIC_FAILED`: reserved for true global/provider/config failures, not a single ticker HTTP 400 retry failure.

Historical runtime proof for `2026-05-01` to `2026-05-07` (retained as execution history; not V2 conformance proof):
- Plan: `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`.
- Diagnose-source: `PARTIAL_SUCCESS`, `failed_ticker_count=1`, `failed_window_count=1`.
- Full lifecycle: 4/4 requested dates readable with evidence exported, fixture generated, and replay verified.
- Resume-only-failed: `FAILED_RETRY_BLOCKED` for `WBSA` HTTP 400 in window `2026-01-01` to `2026-03-31`, with failed checkpoint/retry counts reported explicitly.

## Source cache format
`source_acquisition_cache.json` uses `cache_format=source_acquisition_resume_v2_slim`.

This cache is intentionally slim:
- stores row counts and telemetry summaries
- stores failed checkpoint retry accounting
- stores bounded/sanitized failure samples
- does not store full `rows_by_trade_date`
- does not store full `source_acquisition_checkpoints`
- does not store raw provider payloads

The slim cache is **not** the immutable source-observation store. Provider response/envelope/hash/provenance required by the source-observation contract must be retained through the governed observation/evidence surface even when the resume cache omits the raw payload.

`source_acquisition_checkpoint.json` remains the full retry identity artifact. `source_acquisition_diagnostics.json.reason_code` must match the explicit summary reason or the deterministic failed-checkpoint reason chosen from the retry scope.

---


<!-- LEGACY_EXTRACT_BODY_END -->
