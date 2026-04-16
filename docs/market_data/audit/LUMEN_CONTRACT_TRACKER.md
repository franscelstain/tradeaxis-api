- 2026-04-17: Backfill range execution now stops after the first successful `LOCAL_FILE` case when `source_mode=manual_file` and an explicit manual source file is detected, preserving `all_imported=true` while `all_passed=false` for incomplete range coverage.
# LUMEN_CONTRACT_TRACKER

## Traceability / Linkage / Publishability / Correction Guard Session

Status: PARTIAL

### Contract status

1. **source traceability persistence** → PARTIAL
   - code + migration + sqlite schema added
   - runtime evidence still pending

2. **run/publication linkage** → PARTIAL
   - `eod_runs.publication_id` and `eod_runs.correction_id` added
   - finalize/ingest path now persists linkage directly
   - runtime evidence still pending

3. **publishability metadata** → PARTIAL
   - `eod_runs.final_reason_code` added
   - hold/fail/finalize paths now persist final reason
   - command/evidence readers prefer persisted columns
   - runtime evidence still pending

4. **correction/reseal guard minimum** → PARTIAL
   - correction run now keeps direct `correction_id` linkage on owning run
   - correction publish/cancel path remains guarded by existing baseline/pointer checks
   - runtime evidence still pending

### Concrete DB contract additions

Added to `eod_runs`:
- `source_name`
- `source_provider`
- `source_input_file`
- `source_timeout_seconds`
- `source_retry_max`
- `source_attempt_count`
- `source_success_after_retry`
- `source_retry_exhausted`
- `source_final_http_status`
- `source_final_reason_code`
- `publication_id`
- `correction_id`
- `final_reason_code`

### Evidence expected next

- `php artisan migrate`
- targeted PHPUnit:
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
- manual run proof for:
  - normal manual-file publish
  - source failure hold
  - correction publish/cancel path
- DB select proof showing persisted columns populated

### Current blocker

No local dependencies/runtime DB execution available inside this container, so session cannot be marked DONE yet.


## 2026-04-16 test-fix follow-up
- Fixed source_input_file display normalization so relative project paths export as filename-only while absolute operator paths remain intact.
- Fixed numeric source telemetry casting in evidence export (`attempt_count`, `retry_max`, `timeout_seconds`, `final_http_status`).
- Fixed correction post-finalize mismatch path so `RUN_FINALIZED.reason_code` and `eod_runs.final_reason_code` stay aligned on `RUN_LOCK_CONFLICT`.


## 2026-04-16 regression fix follow-up
- Fixed `market-data:daily` command to call `runDaily()` again so ops surface/tests use the full daily pipeline contract.
- Relaxed source-mode immutability guard so legacy/mock runs without persisted `source` do not fail stage startup.
- Fixed backfill summary status semantics: deterministic held/failed runs now remain `FAIL`, while `all_passed` only stays true when every trading date in scope was processed successfully.
- Hardened evidence export against missing optional persisted fields like `final_reason_code` on legacy/stdClass test records.

- 2026-04-17: Fixed final backfill regressions in `MarketDataBackfillService`: deterministic failures now map `source_final_reason_code` -> `final_reason_code`, and summary flags now keep `all_imported=true` when execution stops early after a successfully imported case while `all_passed=false` reflects incomplete coverage.
