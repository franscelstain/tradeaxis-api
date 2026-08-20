# Legacy Semantic Extract — LX-MD-0039-CTX-01

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `CONTEXT`
- Source range: `L391-L450`
- Extract body SHA1: `72DB4C8D0B198B618C4BA5B37A7CC35DBEA916CF`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Replay fixture generation fix checklist

This fix is required because committed `valid_case` is static and can become stale against local runtime runs. The generated fixture must be built from the actual run context, not from raw/staging/latest/MAX(date).

Manual validation commands:

- `php artisan market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1`
- `php artisan market-data:replay:verify 1 storage/app/market_data/replay-fixtures/generated-valid-run-1 --output_dir=storage/app/market-data/replay`
- `php artisan market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay`

Expected output:

- `fixture_generated=1`
- generated `manifest.json`
- generated `expected/expected_replay_result.json`
- generated `expected/expected_reason_code_counts.json`
- generated fixture verify returns `comparison_result=MATCH`
- generated fixture verify returns `mismatch_count=0`
- generated fixture verify returns `replay_id=5`
- smoke with `--generate_runtime_valid_case` returns `all_passed=1`
- smoke generated valid case returns `observed=MATCH | passed=1`
- smoke with `--generate_runtime_valid_case` returns `all_passed=1`
- stale committed `valid_case` may remain MISMATCH when used against a different run; that is expected and must not be hidden

Pass/fail criteria:

- PASS only if generated runtime fixture returns MATCH for the same run used to generate it, and mismatch/error cases still remain reason-coded.
- FAIL if generated fixture still returns MISMATCH, if smoke requires stale committed `valid_case` for local runtime MATCH proof, or if fixture generation reads raw/staging/latest/MAX(date) instead of the actual run/publication/pointer evidence context.


## 2026-05-20 Final Audit Docs Synchronization Lock Update

This append-only update closes the governance-only production validation gap left after the Ops Command Surface Runtime Matrix Lock Update.

Current synchronized status:

- Aggregate production proof pack: `MARKET_DATA_PRODUCTION_READY_LOCKED / LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT`: `LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `LUMEN_IMPLEMENTATION_STATUS.md`: current working implementation `Full Market-Data Production Readiness Proof Pack -> DONE` with `[REVIEW_STATUS] MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `LUMEN_CONTRACT_TRACKER.md`: current working contract `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Earlier `PENDING_RUNTIME_EVIDENCE`, `PARTIAL_RUNTIME_PROOF`, and `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE` rows in this inventory are retained as historical transition records only; they are superseded for the current source state by the ops matrix production-ready artifact root and final production proof pack.

Final validation basis consumed:

- `docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`.
- `docs/market_data/audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**`.
- `storage/app/market-data/full-production-ready/runtime/historical-replay/**`.
- `storage/app/market-data/correction-lifecycle-hardening/**`.
- Operator-local `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6942 assertions).
- Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4124 assertions).
- Operator-local `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` -> OK (10 tests, 404 assertions).

Remaining risk classification:

- No P0/P1 source-code production validation blocker remains for the current market-data source state.
- Sandbox runtime remains `BLOCKED_CONTAINER_RUNTIME_ENV` because PHP 8.4.16 is intentionally rejected and required PHPUnit extensions are missing; this is not counted as PASS and is not a source-code blocker.
- External/live provider credentials, production scheduler/SLO, deployment infrastructure, CI/runtime parity, future vendor changes, and future code/config changes still require environment-specific revalidation.



<!-- LEGACY_EXTRACT_BODY_END -->
