# Legacy Semantic Extract — LX-MD-0038-FND-01

- Source ID: `LS-MD-0038`
- Original path: `audit/OPS_ENVIRONMENT_BASELINE_INVENTORY.md`
- Original SHA1: `C4C8EC75AF93028E3F6AEFEF6E52E82B376969D5`
- Extract role: `FINDING`
- Source range: `L523-L711`
- Extract body SHA1: `80553705053618DB5E516D1D50D7444E47B7C8F8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-23 — SOURCE READY → FULL PRODUCTION READY GAP CLOSURE

[SESSION] SOURCE_READY_FULL_PRODUCTION_READY_GAP_CLOSURE

[SESSION_STATUS] MARKET_DATA_PRODUCTION_READY_LOCKED  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

[INPUT_SOURCE_ZIP]
- Source ZIP: `tradeaxis-api.zip`
- Source ZIP SHA-256: `86b29452bf563b1f52d9c423072049b0babb6640be5e2ede0dcb1551fa1be325`

[ZIP_HASH_NOTE]
- Locked source-state ZIP hash refers to the operator-local source ZIP used for validation: `86b29452bf563b1f52d9c423072049b0babb6640be5e2ede0dcb1551fa1be325`.
- Uploaded/distribution ZIP hash for this handoff is `6f87f611937f04dac905bd9ea726df8d6579a165860edfb9d914c70c3b2c770c`; it may differ because of packaging/repackaging during handoff.
- This does not change the validated source-state decision as long as the extracted source tree, audit docs, runtime artifacts, and validation outputs match the locked source state.


[FINAL_DECISION]
- `FULLY_PRODUCTION_READY`  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `MARKET_DATA_PRODUCTION_READY_LOCKED`  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `OPS_RUNTIME_PARITY_PASSED`
- `FINAL_PROVIDER_SMOKE=PASSED`
- `LIVE_PROVIDER_SMOKE_PASSED`
- `FULL_MARKET_DATA_PHPUNIT=PASSED` is backed by the latest operator-local full suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions).

[DOC_RECONCILIATION]
- Previous provider-rate-limit/provider-blocked/provider-smoke-review-required wording is `SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS` for the current source state.
- Previous scheduler missing-artifact wording is `SUPERSEDED_BY_SCHEDULER_DUE_RUN_AND_NON_SILENT_FAILURE_PROOF` for the current source state.
- Scheduler proof is not overclaimed: current artifacts prove due-run execution and non-silent reason-coded failure visibility, not a successful scheduled daily production run.

[SCHEDULER_PROOF]
- `SCHEDULER_DUE_RUN_PROOF_PASSED`
- `SCHEDULER_NON_SILENT_FAILURE_PROOF_PASSED`
- `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_NOT_CLAIMED`
- Scheduler metadata refreshed in `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/artifact-presence-audit.txt` to the uploaded source ZIP identity.

[CODE_PATCHES]
- Provider empty/invalid response now returns `provider_smoke_status=BLOCKED` with `reason_code=PROVIDER_EMPTY_OR_INVALID_RESPONSE`; parse-failed and missing selected trade date outcomes are also BLOCKED.
- Coverage gate flags are runtime-enforced fail-closed: `enabled=false` and `require_canonical_bar_evidence=false` return `NOT_EVALUABLE`; zero-universe behavior records `coverage_zero_universe_blocked`.
- Finalize predecision now uses persisted candidate `seal_state` and run `sealed_at` proof instead of hardcoded `true` / `SEALED`.
- Correction approve transition is strict: only `REQUESTED` can become `APPROVED`; other states are blocked with `COMMAND_CORRECTION_STATUS_NOT_APPROVABLE`.

[VALIDATION]
- Sandbox syntax validation passed for changed PHP source and test files with `php -l`.
- Sandbox PHPUnit could not run because this PHP CLI lacks required PHPUnit extensions: `dom`, `mbstring`, `xml`, and `xmlwriter`.
- Operator-local validation completed after gap-closure patch: ProviderSmokeSafeModeStaticGuardTest OK (6 tests, 169 assertions); Coverage OK (72 tests, 800 assertions); Finalize OK (51 tests, 392 assertions); Correction OK (75 tests, 1416 assertions); StaticGuard OK (194 tests, 4785 assertions); Full MarketData suite: OK (511 tests, 7871 assertions).

[NEXT_ACTION]
- None for Final Provider Smoke Passed / Ops Runtime Parity Lock. Current source state is DONE / LOCKED / PASSED.
- Future changes to provider headers, endpoint template, scheduler proof, audit docs, command surface, or market-data runtime artifacts must rerun targeted guards and full `vendor/bin/phpunit tests/Unit/MarketData`.
- Recommended next independent hardening scope: CI / Regression Guard to enforce this validation automatically.

[SUPERSEDES]
- Previous provider-smoke / provider-rate-limit / ops-parity review-required next actions are superseded by the final provider smoke PASS and full MarketData PHPUnit PASS.
- Previous active-looking scheduler missing-artifact wording is superseded by current due-run/non-silent-failure artifacts; successful scheduled daily production run proof remains not claimed.

---

## 2026-05-24 — API Daily Runtime Proof / Final Post-Gap-Closure Validation

[SESSION] API_DAILY_RUNTIME_PROOF_FINAL_VALIDATION

[SESSION_STATUS] FULLY_PRODUCTION_READY  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

[FINAL_DECISION]
- `FULLY_PRODUCTION_READY` is valid for the current market-data source state after the final API daily runtime proof, evidence export proof, replay verification proof, and full MarketData PHPUnit proof.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `OPS_RUNTIME_PARITY_PASSED` remains valid.
- `FINAL_PROVIDER_SMOKE=PASSED` remains valid.
- `API_DAILY_RUNTIME_PROOF=PASSED`.
- `EVIDENCE_EXPORT=ADMITTED_COMPLETE`.
- `REPLAY_VERIFY=PASS`.
- `FULL_MARKET_DATA_PHPUNIT=PASSED`.

[API_DAILY_RUNTIME_PROOF]
- Command path proven: `market-data:daily --source_mode=api` followed by `market-data:promote --requested_date=2026-05-20 --source_mode=api --run_id=1`.
- `run_id=1`.
- `trade_date_requested=2026-05-20`.
- `trade_date_effective=2026-05-20`.
- `source_mode=api`.
- `source_name=API_FREE`.
- `source_provider=yahoo_finance`.
- `request_mode=promote`.
- `promote_mode=full_publish`.
- `publish_target=current_replace`.
- `terminal_status=SUCCESS`.
- `publishability_state=READABLE`.
- `promote_status=PROMOTED`.
- `promoted=true`.
- `pointer_switched=true`.
- `current_publication_id=1`.
- `publication_id=1`.
- `publication_version=1`.
- `is_current_publication=1`.
- `seal_state=SEALED`.
- `sealed_at=2026-05-24 01:24:51`.
- `lineage_verification_status=RUN_PUBLICATION_LINK_PRESENT`.

[COVERAGE_PROOF]
- `coverage_gate_state=PASS`.
- `coverage_reason_code=COVERAGE_THRESHOLD_MET`.
- `coverage_basis=CandidatePublication`.
- `coverage_basis_publication_id=1`.
- `coverage_summary=available=911/913 | missing=2 | ratio=0.9978 | threshold=0.9800 | threshold_mode=MIN_RATIO | basis=ACTIVE_LISTED_EQUITY_AS_OF_DATE | coverage_basis=CandidatePublication | artifact_scope=candidate_publication_artifact | contract=coverage_gate_v1`.
- `coverage_missing_sample=JSPT,JTPE`.
- The API source returned a partial provider result, but coverage remained above the configured threshold and therefore publication was validly promoted as readable.
- `source_final_status=PARTIAL`.
- `final_reason_code=RUN_SOURCE_PARTIAL_RESPONSE`.
- `source_final_http_status=200`.
- `source_attempt_count=920`.
- `source_success_after_retry=yes`.
- `source_retry_exhausted=yes`.
- `accepted_row_count=911`.
- `rejected_row_count=0`.
- `invalid_row_count=0`.

[HASH_SEAL_PROOF]
- `hash_algorithm=SHA-256`.
- `bars_batch_hash=b9f9737351b6eb95bdce1c275f1a71b626a15ab65655d5a72f7707b0ed65c53d`.
- `indicators_batch_hash=9c80f39855dedaba4418e9d9ef040dfda5051b2e47cccb837f8cfef0083e037c`.
- `eligibility_batch_hash=4e883362a85006428252c625811494168583111a298a8053a9fad653eadd9dd3`.

[EVIDENCE_EXPORT_PROOF]
- Command: `php artisan market-data:evidence:export --run_id=1 --output_dir=storage/app/market-data/manual-validation/evidence-run-1`.
- `selector=run`.
- `selector_id=1`.
- `run_id=1`.
- `terminal_status=SUCCESS`.
- `publishability_state=READABLE`.
- `coverage_gate_state=PASS`.
- `final_reason_code=RUN_SOURCE_PARTIAL_RESPONSE`.
- `evidence_completeness_state=COMPLETE`.
- `evidence_admission_state=ADMITTED_COMPLETE`.
- `publication_id=1`.
- `pointer_resolve_status=RESOLVED_READABLE_CURRENT`.
- `fallback_used=0`.
- `output_dir=storage/app/market-data/manual-validation/evidence-run-1`.
- `file_count=11`.
- Files: `run_summary.json`, `publication_manifest.json`, `run_event_summary.json`, `source_attempt_telemetry.json`, `eligibility_export.csv`, `invalid_bars_export.csv`, `anomaly_report.md`, `lineage.json`, `evidence_admission.json`, `evidence_completeness.json`, `evidence_pack.json`.

[REPLAY_PROOF]
- Fixture command: `php artisan market-data:replay:fixture:generate 1 --case=api_daily_success_run_1 --output_dir=storage/app/market-data/manual-validation/fixtures/run-1`.
- `fixture_generated=1`.
- `fixture_id=api_daily_success_run_1`.
- `fixture_family=runtime_generated_valid_case`.
- `expected_result=MATCH`.
- `fixture_path=storage/app/market-data/manual-validation/fixtures/run-1`.
- `manifest_path=storage/app/market-data/manual-validation/fixtures/run-1/manifest.json`.
- Verify command: `php artisan market-data:replay:verify 1 storage/app/market-data/manual-validation/fixtures/run-1 --output_dir=storage/app/market-data/manual-validation/replay-verify-run-1`.
- `replay_id=1`.
- `replay_suite=runtime_generated_valid_case`.
- `replay_case=api_daily_success_run_1`.
- `expected_final_state=SUCCESS|READABLE|RUN_SOURCE_PARTIAL_RESPONSE`.
- `actual_final_state=SUCCESS|READABLE|RUN_SOURCE_PARTIAL_RESPONSE`.
- `comparison_result=MATCH`.
- `replay_status=PASS`.
- `mismatch_count=0`.
- `source_summary=expected:api/yahoo_finance actual:api/yahoo_finance`.
- `coverage_summary=expected:PASS/0.997809 actual:PASS/0.997809`.
- `publication_summary=expected:1/v1 actual:1/v1`.
- `pointer_summary=expected:1 actual:1`.
- `fallback_summary=expected:not_used actual:not_used`.
- `artifact_changed_scope=none`.
- `replay_artifact_path=storage/app/market-data/manual-validation/replay-verify-run-1/replay_result.json`.

[SESSION_SNAPSHOT_NOTE]
- `market-data:session-snapshot 2026-05-20 OPEN_CHECK` without `--input_file` failed with `Session snapshot input file not found`.
- This is not a failure of the API daily/promote/evidence/replay production proof.
- Session snapshot remains an optional supplemental proof requiring an explicit local input file through `--source_mode=manual_file --input_file=...`.
- `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_NOT_CLAIMED` remains separate from the API daily runtime proof.

[OPERATOR_LOCAL_VALIDATION]
- `vendor\bin\phpunit tests/Unit/MarketData --filter "AuditDocsSynchronizationStaticGuardTest"` -> OK (10 tests, 461 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProductionValidationRuntimeProofStaticGuardTest"` -> OK (15 tests, 482 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProviderSmokeSafeModeStaticGuardTest"` -> OK (6 tests, 169 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (72 tests, 800 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "Finalize"` -> OK (51 tests, 392 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "Correction"` -> OK (75 tests, 1416 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (194 tests, 4788 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions), Time 00:11.456, Memory 40.00 MB.

[FINAL_RULE]
- The current source state can claim `FULLY_PRODUCTION_READY` for the market-data source/runtime proof represented by this audit pack.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- API source partial responses can still be validly promoted only when coverage gate remains PASS and the source attempt telemetry is reason-coded.
- Future provider, scheduler, command-surface, audit-doc, config, coverage, finalize, correction, evidence, or replay changes must rerun the targeted guards and full `vendor/bin/phpunit tests/Unit/MarketData`.

[NEXT_ACTION]
- None for this API daily runtime proof and final validation scope.
- Recommended next independent hardening scope: CI / Regression Guard to enforce the final validation automatically.


<!-- LEGACY_EXTRACT_BODY_END -->
