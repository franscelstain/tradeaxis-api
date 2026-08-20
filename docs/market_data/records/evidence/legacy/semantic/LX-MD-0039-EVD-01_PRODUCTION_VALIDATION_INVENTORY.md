# Legacy Semantic Extract — LX-MD-0039-EVD-01

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `EVIDENCE`
- Source range: `L1-L41`
- Extract body SHA1: `E5177021D4112BF01F3DA570F848B7A459E20A26`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
# Production Validation Inventory

> **HISTORICAL AUDIT/IMPLEMENTATION EVIDENCE — NON-AUTHORITATIVE FOR CURRENT V2 STRATEGY.** This file preserves dated runtime/inventory facts and may contain legacy field names, command behavior, locks, or production claims from earlier contracts. Current strategy authority is the owner contracts + Blueprint + Conformance Matrix; current execution/conformance state is `MARKET_DATA_IMPLEMENTATION_LEDGER.md`; current audit verdict is `reports/AUDIT_FINAL_STATE.md`. Legacy statements are not current requirements unless explicitly re-admitted by those authorities.


Current admission status (2026-08-02): **HISTORICAL VALIDATION SCOPE / NOT CURRENT RELOCK PROOF**. See `reports/AUDIT_FINAL_STATE.md`. Runtime proof below remains valid for the commands and contracts it executed, but it predates the corrected temporal identity, immutable observation/config, factor/product, indicator, coverage, read-model, and as-known replay requirements.

Status: DONE.
Contract: PRODUCTION_VALIDATION_CONTRACT.
Active implementation: Production Validation / Manual + Runtime Proof.
Runtime policy: static proof is support only. DONE requires runtime evidence. LOCKED requires targeted and full suite PASS plus artisan command, evidence output, and replay verification proof.

Latest final API runtime proof: `market-data:promote --requested_date=2026-05-20 --source_mode=api --run_id=1` produced `SUCCESS / READABLE / PASS / SEALED`, pointer switched to `publication_id=1`; evidence export returned `COMPLETE / ADMITTED_COMPLETE`; replay verify returned `MATCH / PASS / mismatch_count=0`; final full `vendor/bin/phpunit tests/Unit/MarketData` passed with OK (511 tests, 7871 assertions).

This inventory is the production validation control surface for market-data. It separates container/static proof, operator-local runtime proof, and missing runtime proof. The uploaded ZIP has no `vendor/`, so container validation can only prove file presence, docs/test cross-checks, static scans, and `php -l` for changed PHP files. Operator-local PHPUnit and artisan proof can be recorded only when actual output is supplied. Flow execution and evidence export runtime proof have now been supplied and recorded. Replay verification was executed after the persistence and fixture-generation fixes. SQLSTATE[22001] is resolved, mismatch/error cases persist cleanly, generated runtime fixture verification returns MATCH with `mismatch_count=0`, replay smoke with `--generate_runtime_valid_case` returns `all_passed=1`, and replay evidence export for `replay_id=5` returns `status=SUCCESS`, `comparison_result=MATCH`, and 5 replay evidence files. Production Validation is now DONE and `PRODUCTION_VALIDATION_CONTRACT` is LOCKED because targeted PHPUnit, full MarketData PHPUnit, artisan command discovery/help, success flow, held/failure flow, run/replay/correction evidence, replay generated MATCH proof, replay smoke `all_passed=1`, and correction lifecycle are all proven by operator-local runtime output.

Manual validation note: every manual validation runtime output and every fix generated from that output must be recorded in this inventory, `LUMEN_IMPLEMENTATION_STATUS.md`, and `LUMEN_CONTRACT_TRACKER.md`. The replay persistence fix specifically requires `mismatch_summary LONGTEXT NULL` in schema docs/migration coverage before replay proof can be retested.

2026-06-10 benchmark non-blocking backfill proof: initial `2026-06-09` runs were held before equity ingest because benchmark ingest executed first and raised `RUN_SOURCE_NO_VALID_DATA`. The pipeline order was corrected and benchmark-source failure made non-blocking for equity publication. Final rerun produced `run_id=37919`, 948 accepted/written equity bars, zero invalid bars, coverage PASS, promotion SUCCESS, evidence exported, fixture generated, replay verified, readable current publication, and requested/effective date `2026-06-09`. Database proof shows 948 rows each in `eod_bars`, `eod_indicators`, and `eod_eligibility`; the current publication pointer is `publication_id=38186`, `run_id=37919`, version 1, sealed at `2026-06-10 21:07:07`. Full `tests/Unit/MarketData` passed with 641 tests and 9554 assertions. This closes the defect as `RUNTIME_PROOF_PASS`; no additional manual test remains for this scope.

2026-05-19 replay determinism runtime proof update: the uploaded source ZIP for this session contains `vendor/` and `.env.testing`, and local PHP 7.4.33 can execute PHPUnit and artisan. Replay determinism runtime proof was refreshed after adding explicit `replay_status`. `market-data:replay:fixture:generate` generated `storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2`; replay verify PASS produced `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`; a derived reason-code mismatch fixture produced `replay_id=3`, `comparison_result=MISMATCH`, `replay_status=FAIL`, `mismatch_count=1`, and `REPLAY_FINAL_REASON_CODE_MISMATCH`; broken/missing/invalid fixture cases produced `replay_status=BLOCKED`; replay smoke returned `all_passed=1` with PASS/FAIL/BLOCKED cases; replay evidence export for `replay_id=2` produced `file_count=6`, `evidence_admission_state=ADMITTED_COMPLETE`, and files `replay_result.json`, `replay_expected_state.json`, `replay_actual_state.json`, `replay_reason_code_counts.json`, `evidence_admission.json`, and `replay_evidence_pack.json`. Final validation for this scoped update passed `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` with OK (169 tests, 3926 assertions) and full `vendor/bin/phpunit tests/Unit/MarketData` with OK (451 tests, 6642 assertions). This update closes replay determinism as a production-readiness blocker for the replay scope only; it does not close the ops runtime matrix or final production proof pack.

2026-05-10 container runtime proof recovery note: this uploaded ZIP contains `vendor/`, unlike earlier source-of-truth ZIP assumptions, but the current container still cannot execute PHPUnit because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are missing. `.env.testing` is absent, so migration, seed, manual import/promote, evidence export, and replay verification were not run in container. Container evidence for this recheck is limited to command registration (`php artisan list` lists 20 market-data commands, with PHP 8.4 deprecation warnings) and syntax validation (`php -l` passed for 128 market-data PHP files). This note is `BLOCKED_CONTAINER_RUNTIME_ENV` and does not supersede the prior operator-local runtime proof recorded above.

## Runtime proof categories

| Category | Allowed Evidence | Status Ceiling | Rule |
|---|---|---|---|
| Container/static proof | `php -l`, grep/static scan, file existence, docs/test/audit cross-check | STATIC_PROOF_ONLY / READY_FOR_LOCAL_RUNTIME_VALIDATION | Static proof cannot replace runtime proof. |
| Local/runtime proof from operator | Actual PHPUnit output, artisan output, evidence output path, replay result, generated artifact path, command help output | RUNTIME_PROOF_PASS / DONE / LOCKED | Record command, result, test count, assertion count, output summary, and source. |
| Missing runtime proof | No actual command output yet | PENDING_RUNTIME_EVIDENCE | Do not claim PASS, DONE, or LOCKED. |
| Partial runtime proof | Some command/test evidence exists but evidence/replay/flow proof is incomplete | PARTIAL_RUNTIME_PROOF | Keep missing items visible as PENDING_RUNTIME_EVIDENCE. |

## Closed production validation proof statuses

- `PENDING_RUNTIME_EVIDENCE` remains a governance status for future unexecuted runtime scenarios, but no required production-validation blocker remains for this scope.
- `PENDING_EVIDENCE_RUNTIME_PROOF` remains visible as a governance status for future unexecuted evidence variants; run evidence, replay evidence, held-run evidence, and correction evidence are closed by operator-local output.
- `PENDING_REPLAY_RUNTIME_PROOF` is closed for generated MATCH/smoke proof: `market-data:replay:fixture:generate` produced `fixture_generated=1`, verify produced `comparison_result=MATCH` and `mismatch_count=0`, and smoke with `--generate_runtime_valid_case` produced `all_passed=1`.
- `PENDING_FLOW_RUNTIME_PROOF` is closed for both the 2026-02-18 success path and the 2026-03-20 failed/held/not-readable coverage path.
- `READY_FOR_LOCAL_RUNTIME_VALIDATION` remains the maximum state for any future patch area until local PHPUnit/artisan output is supplied.


<!-- LEGACY_EXTRACT_BODY_END -->
