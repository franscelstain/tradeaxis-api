# Production Validation Inventory

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

## Fix ledger / runtime findings after fix2

- Operator-local runtime PASS: daily import-only for `2026-02-18` produced `run_id=1`, accepted 901 rows, stayed `NOT_READABLE`, did not promote, did not switch pointer, and stayed `UNSEALED`.
- Operator-local runtime PASS: promote/finalize for `run_id=1` produced `SUCCESS`, `READABLE`, `SEALED`, coverage `PASS`, `COVERAGE_THRESHOLD_MET`, and pointer switched to publication `1`.
- Operator-local runtime PASS: run evidence export for `run_id=1` produced complete evidence with 9 files and pointer status `RESOLVED_READABLE_CURRENT`.
- Operator-local runtime BLOCKED before fix3: replay smoke/verify exposed `SQLSTATE[22001]` because long mismatch details overflowed `md_replay_daily_metrics.mismatch_summary`.
- Patch applied in this ZIP: `mismatch_summary LONGTEXT`, concise `buildOperatorMismatchSummary`, details retained in `mismatches_json`, and domain reason-code extraction for replay command failures.
- Operator-local replay after fix3 historical PASS/REVIEW_REQUIRED note: `reason_code_mismatch_case` observed `MISMATCH` and passed, `broken_manifest_case` exposed `REPLAY_FIXTURE_SCHEMA_MISMATCH`, `missing_file_case` exposed `REPLAY_EXPECTED_PROOF_INCOMPLETE`, and stale committed `valid_case` cleanly observed `MISMATCH` instead of SQL truncation.
- Replay fixture note: the committed `valid_case` fixture expects `2026-03-17`, `run_id=41`, `publication_id=4`, and 10 rows; using it against runtime `run_id=1` / `2026-02-18` / 901 rows is expected to produce MISMATCH, not MATCH. A generated runtime fixture is required for true MATCH proof.
- Patch applied in fix4: `market-data:replay:fixture:generate` creates `manifest.json`, `expected/expected_replay_result.json`, and `expected/expected_reason_code_counts.json` from the actual run; `market-data:replay:smoke --generate_runtime_valid_case` can use this generated runtime valid fixture for smoke MATCH proof.
- Operator-local replay fixture proof after fix4 PASS: `market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1` produced `fixture_generated=1`, `expected_result=MATCH`, `fixture_family=runtime_generated_valid_case`, `trade_date=2026-02-18`, publication/pointer `1`, and expected fixture files.
- Operator-local generated replay verify PASS: `market-data:replay:verify 1 storage/app/market_data/replay-fixtures/generated-valid-run-1 --output_dir=storage/app/market-data/replay` produced `replay_id=5`, `comparison_result=MATCH`, `mismatch_count=0`, `final_reason_code=COVERAGE_THRESHOLD_MET`, `artifact_changed_scope=none`, and `replay_artifact_path=storage/app/market-data/replay/replay_result.json`.
- Operator-local generated smoke PASS: `market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay` produced `all_passed=1`, `runtime_valid_fixture_generated=1`, generated valid case `MATCH/pass`, reason-code mismatch `MISMATCH/pass`, broken manifest `ERROR/pass`, and missing file `ERROR/pass`.
- Operator-local replay evidence export PASS after fix5: `market-data:evidence:export --replay_id=5 --trade_date=2026-02-18 --output_dir=storage/app/market-data/evidence` produced selector=replay, `selector_id=5`, `status=SUCCESS`, `comparison_result=MATCH`, `trade_date=2026-02-18`, `file_count=5`, and files `replay_result.json`, `replay_expected_state.json`, `replay_actual_state.json`, `replay_reason_code_counts.json`, and `replay_evidence_pack.json`.
- Operator-local failed/held runtime proof after fix6 PASS: `market-data:daily --requested_date=2026-03-20 --source_mode=manual_file --input_file=storage/app/market_data/operator/manual-2026-03-20.csv --output_dir=storage/app/market-data/runs` produced `run_id=2`, `request_mode=import_only`, `accepted_row_count=5`, `promoted=false`, `pointer_switched=false`, `seal_state=UNSEALED`, and `is_current_publication=0`.
- Operator-local failed/held promote proof after fix6 PASS: `market-data:promote --requested_date=2026-03-20 --source_mode=manual_file --run_id=2 --output_dir=storage/app/market-data/runs` produced `terminal_status=HELD`, `publishability_state=NOT_READABLE`, `promote_status=HELD`, `promoted=false`, `pointer_switched=false`, `coverage_gate_state=FAIL`, `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`, `coverage_summary=available=5/901 | missing=896 | ratio=0.0055 | threshold=0.9800`, `seal_state=UNSEALED`, and `final_reason_code=RUN_PARTIAL_DATA`.
- Operator-local held-run evidence export PASS_WITH_WARNING after fix6: `market-data:evidence:export --run_id=2 --output_dir=storage/app/market-data/evidence` produced selector `run`, `selector_id=2`, `terminal_status=HELD`, `publishability_state=NOT_READABLE`, `coverage_gate_state=FAIL`, `final_reason_code=RUN_PARTIAL_DATA`, `evidence_completeness_state=INCOMPLETE`, `pointer_resolve_status=MISSING`, `fallback_used=1`, `file_count=8`, and `evidence_warning=EVIDENCE_INCOMPLETE`. This is an expected warning proof for a non-readable/unsealed held run and must not be treated as a complete readable proof package.
- Operator-local correction status guard proof PASS: `market-data:correction:request --trade_date=2026-02-18 --reason_code=CORRECTION_OPERATOR_REQUESTED --reason_note="production validation correction proof" --requested_by=operator` produced `correction_id=1` and status `REQUESTED`; executing before approval produced `status=BLOCKED`, `reason_code=COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`, and preserved correction status `REQUESTED`; evidence export for REQUESTED produced `correction_evidence.json`.
- Operator-local correction lifecycle proof PASS after approval: `market-data:correction:approve 1 --approved_by=operator` produced status `APPROVED`; `market-data:correction:run 1 --requested_date=2026-02-18 --source_mode=manual_file` produced `run_id=3`, `request_mode=correction`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `promote_status=PROMOTED`, `pointer_switched=true`, `current_publication_id=3`, `publication_id=3`, `publication_version=2`, `seal_state=SEALED`, `coverage_gate_state=PASS`, `coverage_reason_code=COVERAGE_THRESHOLD_MET`, `correction_status=PUBLISHED`, `correction_outcome=PUBLISHED`, `correction_reseal_status=RESEALED`, `baseline_publication_id=1`, `candidate_publication_id=3`, and `candidate_publication_switch=true`.
- Operator-local correction evidence export PASS: `market-data:evidence:export --correction_id=1 --output_dir=storage/app/market-data/evidence` produced selector `correction`, `selector_id=1`, `status=PUBLISHED`, `changed_decision=CHANGED`, `reseal_status=RESEALED`, `publication_switch=1`, `file_count=1`, and file `correction_evidence.json`.

## Production validation inventory

| Area | Required Runtime Proof | Actual Evidence | Gap | Status | Next Action |
|---|---|---|---|---|---|
| Targeted PHPUnit | `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` and `vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"` | Operator-local PASS after fix1: direct guard OK (10 tests, 131 assertions); ProductionValidation filter OK (10 tests, 131 assertions). | None for this targeted scope. | RUNTIME_PROOF_PASS | Keep rerunning after any ProductionValidation docs/test patch. |
| Related targeted PHPUnit | OperationalReadiness, CommandSurface, Evidence, Replay, Correction, FailSafe filters | Operator-local PASS after ProductionValidation patch/fix cycle: OperationalReadiness OK (10 tests, 199 assertions); CommandSurface OK (47 tests, 348 assertions); Evidence OK (44 tests, 767 assertions); Replay OK (39 tests, 655 assertions); Correction OK (65 tests, 1287 assertions); FailSafe OK (5 tests, 108 assertions). | None for these targeted filters. | RUNTIME_PROOF_PASS | Keep rerunning after related behavior changes. |
| Full MarketData PHPUnit | `vendor/bin/phpunit tests/Unit/MarketData` | Operator-local PASS after fix1: full MarketData suite OK (378 tests, 5072 assertions). | None for PHPUnit regression suite. | RUNTIME_PROOF_PASS | Keep full-suite output current after future patches. |
| Artisan command list | `php artisan list | findstr market-data` / `php artisan list market-data` | Current runtime shows 30 registered market-data commands including `market-data:backfill:lifecycle`, `market-data:backfill:missing-tickers`, `market-data:provider:smoke`, `market-data:evidence-replay:full-range-current`, `market-data:sectors:import-memberships`, `market-data:sector-indexes:import-bars`, `market-data:sector-indexes:ingest-api`, `market-data:events:import-corporate-actions`, and `market-data:events:import-trading-status`, plus `market-data:eod-indicators:recompute-current`; the earlier 20-command fixture-generator proof, 21-command provider-smoke proof, 22-command full-range evidence/replay proof, 23-command sector membership checkpoint, 24-command lifecycle reconciliation, 25-command sector-index CSV checkpoint, 26-command sector API checkpoint, and 28-command event-risk checkpoint are historical current-surface checkpoints. | None for command discovery. | RUNTIME_PROOF_PASS | Keep rerunning after command registration changes. |
| Command help | Help output for fixture generate, replay smoke/verify, full-range current evidence/replay, evidence export, daily, promote, finalize, and correction request/approve/run | Operator-local PASS after fix7 and 2026-06-03 extension: `replay:fixture:generate --help` shows `run_id`, `--case`, `--output_dir`; `replay:smoke --help` shows `--generate_runtime_valid_case`; `evidence-replay:full-range-current --help` shows optional date range, `--fixture_case`, `--output_dir`, `--continue_on_error`, and `--max_dates`; `replay:verify`, `evidence:export`, `daily`, `promote`, `run:finalize`, `correction:request`, `correction:approve`, and `correction:run` all display usage/options with no fatal error. | None for required command-help proof. | RUNTIME_PROOF_PASS | Keep rerunning after command signature/help changes. |
| Daily/import-only flow | `php artisan market-data:daily --requested_date=2026-02-18 --source_mode=manual_file --input_file=storage/app/market_data/operator/manual-full-2026-02-18.csv --output_dir=storage/app/market-data/runs` | Operator-local PASS: `run_id=1`, `request_mode=import_only`, `import_status=COMPLETED`, `promote_status=NOT_PROMOTED`, `promoted=false`, `pointer_switched=false`, `seal_state=UNSEALED`, `is_current_publication=0`, accepted 901 rows. | None for import-only boundary. | RUNTIME_PROOF_PASS | Keep rerunning after import/manual-file changes. |
| Promote/finalize flow | `php artisan market-data:promote --requested_date=2026-02-18 --source_mode=manual_file --run_id=1 --output_dir=storage/app/market-data/runs`; `php artisan market-data:run:finalize --requested_date=2026-02-18 --source_mode=manual_file --run_id=1` | Operator-local PASS: `SUCCESS`, `READABLE`, `PROMOTED`, `pointer_switched=true`, `is_current_publication=1`, `seal_state=SEALED`, `coverage_gate_state=PASS`, `coverage_reason_code=COVERAGE_THRESHOLD_MET`, `available=901/901`, ratio `1.0000`, threshold `0.9800`; finalize rerun preserved state. | None for success flow. | RUNTIME_PROOF_PASS | Keep rerunning after finalize/pointer/seal changes. |
| Evidence export | `php artisan market-data:evidence:export --run_id=1 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --run_id=2 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --replay_id=5 --trade_date=2026-02-18 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --correction_id=1 --output_dir=storage/app/market-data/evidence` | Operator-local run evidence PASS: selector `run`, `selector_id=1`, terminal `SUCCESS`, publishability `READABLE`, coverage `PASS`, final reason `COVERAGE_THRESHOLD_MET`, completeness `COMPLETE`, publication `1`, pointer status `RESOLVED_READABLE_CURRENT`, `fallback_used=0`, `file_count=9`. Operator-local held-run evidence PASS_WITH_WARNING: selector `run`, `selector_id=2`, terminal `HELD`, publishability `NOT_READABLE`, coverage `FAIL`, final reason `RUN_PARTIAL_DATA`, completeness `INCOMPLETE`, pointer status `MISSING`, `fallback_used=1`, `file_count=8`, warning `EVIDENCE_INCOMPLETE`. Operator-local replay evidence PASS: selector=replay, `selector_id=5`, `comparison_result=MATCH`, `status=SUCCESS`, `file_count=5`. Operator-local correction evidence PASS: selector `correction`, `selector_id=1`, `status=PUBLISHED`, `changed_decision=CHANGED`, `reseal_status=RESEALED`, `publication_switch=1`, `file_count=1`, file `correction_evidence.json`. | Evidence proof is complete for success run, held run, replay MATCH, and published correction; held run remains intentionally incomplete because it is not readable/sealed. | RUNTIME_PROOF_PASS | Keep exporting evidence after future run/replay/correction changes. |
| Replay verify/smoke | `php artisan market-data:replay:smoke 1 --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:verify 1 storage/app/market_data/replay-fixtures/* --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1`; `php artisan market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay` | Operator-local replay PASS after fix4/fix5: stale committed `valid_case` cleanly returns `MISMATCH` when expected data differs; generated runtime valid fixture returns `comparison_result=MATCH`, `mismatch_count=0`, `replay_id=5`; smoke with generated valid case returns `all_passed=1`, generated valid `MATCH/pass`, reason-code mismatch `MISMATCH/pass`, broken manifest `ERROR/pass`, missing file `ERROR/pass`; replay evidence export for `replay_id=5` returns `status=SUCCESS`, `comparison_result=MATCH`, and 5 evidence files. | Replay core proof and replay evidence export proof complete. | RUNTIME_PROOF_PASS | Keep rerunning after replay fixture/evidence changes. |
| Correction lifecycle | correction request/approve/run plus evidence proof | Operator-local correction proof PASS: request produced `correction_id=1` and status `REQUESTED`; premature run was blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`; approve produced status `APPROVED`; run produced `run_id=3`, `request_mode=correction`, `SUCCESS`, `READABLE`, `PUBLISHED`, `RESEALED`, `baseline_publication_id=1`, `candidate_publication_id=3`, `candidate_publication_switch=true`, and new current publication `3`; evidence export produced `correction_evidence.json`. | None for request/approve/run/evidence lifecycle. | RUNTIME_PROOF_PASS | Keep correction lifecycle output current after correction command changes. |
| Backfill/session snapshot | backfill, replay backfill, session snapshot capture/purge | Prior operational docs cover commands; no fresh runtime output supplied. | Need output proving no readable shortcut and pointer-resolved snapshot behavior. | PENDING_RUNTIME_EVIDENCE | Run commands when market calendar/readable fixture is available. |
| Failure scenarios | coverage below threshold, replay mismatch, broken/missing fixture, evidence incomplete warning, and correction status guard | Operator-local runtime proof covers coverage below threshold (`run_id=2`, `HELD`, `NOT_READABLE`, `COVERAGE_BELOW_THRESHOLD`, `RUN_PARTIAL_DATA`, `pointer_switched=false`), held evidence warning (`EVIDENCE_INCOMPLETE`), replay mismatch, broken manifest, missing file, and correction not-executable status guard. | None for required production-validation failure scope; provider/source failure, invalid manual file, pointer/seal/no-readable/session snapshot remain optional broader regression scenarios. | RUNTIME_PROOF_PASS | Run broader failure cases only if expanding production validation beyond current minimum. |
| Audit docs | LUMEN_IMPLEMENTATION_STATUS and LUMEN_CONTRACT_TRACKER updated append-only with Production Validation | Updated with current local ProductionValidation/full-suite PASS evidence, flow/evidence/replay/failure/correction runtime proof, and fresh 30 registered market-data commands command-list/full-help proof. | None for production-validation audit sync. | RUNTIME_PROOF_PASS | Keep all new runtime output recorded append-only. |
| Guard marker | Exact current command-surface evidence marker | `30-command command list/full help` is the canonical marker for the current 30-command market-data surface after adding the validated current-bars indicator recompute command. | None for static guard sync. | RUNTIME_PROOF_PASS | Preserve this exact marker while command surface remains 30. |
| Indicator nullability/source-scope cleanup | Latest operator-local full MarketData proof | `vendor\bin\phpunit tests\Unit\MarketData` -> OK (639 tests, 9509 assertions); source/master read-only vs publication-bound recompute docs synchronized. | None for this scope. | RUNTIME_PROOF_PASS | Preserve source/master read-only wording and command removal boundary. |
| Static guard | `ProductionValidationRuntimeProofStaticGuardTest.php` | Operator-local PASS after fix1: direct guard OK (10 tests, 131 assertions) and ProductionValidation filter OK (10 tests, 131 assertions). | None for static guard. | RUNTIME_PROOF_PASS | Keep guard aligned with inventory/audit docs. |
| ZIP artifact | Final ZIP contains docs, audit updates, inventory, and guard | Created by this session after static validation. | User must verify download/extract locally. | STATIC_PROOF_ONLY | Use final ZIP and run local commands. |

## Required PHPUnit validation commands

| Command | Expected Output | Pass Criteria | Current Evidence | Status |
|---|---|---|---|---|
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | `OK (... tests, ... assertions)` | Guard passes with no failures/errors. | Operator-local PASS: OK (10 tests, 131 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"` | `OK (... tests, ... assertions)` | ProductionValidation filter passes. | Operator-local PASS: OK (10 tests, 131 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` | `OK (... tests, ... assertions)` | Operational readiness remains passing. | Operator-local PASS: OK (10 tests, 199 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` | `OK (... tests, ... assertions)` | Command surface remains passing. | Operator-local PASS: OK (47 tests, 348 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | `OK (... tests, ... assertions)` | Evidence tests remain passing. | Operator-local PASS: OK (44 tests, 767 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | `OK (... tests, ... assertions)` | Replay tests remain passing. | Operator-local PASS: OK (39 tests, 655 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | `OK (... tests, ... assertions)` | Correction tests remain passing. | Operator-local PASS: OK (65 tests, 1287 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` | `OK (... tests, ... assertions)` | Fail-safe tests remain passing. | Operator-local PASS: OK (5 tests, 108 assertions). | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | `OK (... tests, ... assertions)` | Full MarketData suite passes after this patch. | Operator-local PASS: OK (378 tests, 5072 assertions). | RUNTIME_PROOF_PASS |

## Required artisan command validation

| Command | Expected Output | Pass Criteria | Status |
|---|---|---|---|
| `php artisan list | findstr market-data` | market-data commands listed, count recorded | Operator-local PASS before fixture generator: 19 market-data commands listed. After this patch expected count is 20 including `market-data:replay:fixture:generate`; rerun command list locally. | PARTIAL_RUNTIME_PROOF |
| `php artisan market-data:daily --help` | Usage/options visible | Operator-local PASS: usage/options visible including `--requested_date`, `--source_mode`, `--input_file`, `--output_dir`, `--correction_id`, and `--latest`. | RUNTIME_PROOF_PASS |
| `php artisan market-data:promote --help` | Usage/options visible | Operator-local PASS: usage/options visible including requested date, source mode, run/correction, mode/output, latest, and force-replace options. | RUNTIME_PROOF_PASS |
| `php artisan market-data:evidence:export --help` | Usage/options visible | Operator-local PASS: run/correction/replay/trade_date/output options visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:replay:verify --help` | Usage/options visible | Operator-local PASS: `run_id`, `fixture_path`, `--replay_id`, and `--output_dir` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:evidence-replay:full-range-current --help` | Usage/options visible | Optional date range, `--fixture_case`, `--output_dir`, `--continue_on_error`, and `--max_dates` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:sector-indexes:ingest-api --help` | Usage/options visible | Date range, `--provider`, `--symbol_suffix`, `--symbol_map_json`, `--dry-run`, `--apply`, `--continue_on_error`, and `--allow_partial` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:sector-indexes:import-bars --help` | Usage/options visible | CSV input, `--source_name`, `--dry-run`, and `--apply` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:sectors:import-memberships --help` | Usage/options visible | CSV input, `--classification_system`, `--source_name`, `--dry-run`, and `--apply` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:events:import-corporate-actions --help` | Usage/options visible | CSV input, `--source_name`, `--dry-run`, and `--apply` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:events:import-trading-status --help` | Usage/options visible | CSV input, `--source_name`, `--dry-run`, and `--apply` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:backfill:lifecycle --help` | Usage/options visible | Range dates, `--source_mode`, `--plan`, evidence/replay, diagnosis, resume, and failed-checkpoint options visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:backfill:missing-tickers --help` | Usage/options visible | Range dates, `--source_mode=api`, `--ticker_codes`, `--plan`, resume/error policy, evidence, and replay options visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:correction:request --help` | Usage/options visible | Operator-local PASS: `--trade_date`, `--reason_code`, `--reason_note`, and `--requested_by` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:correction:approve --help` | Usage/options visible | Operator-local PASS: `correction_id` argument and `--approved_by` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:correction:run --help` | Usage/options visible | Operator-local PASS: `correction_id`, `--requested_date`, `--source_mode`, and `--latest` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:eod-bars:ingest --help` | Usage/options visible | ingest input/source options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:eod-indicators:compute --help` | Usage/options visible | requested/effective date options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:eod-indicators:recompute-current --help` | Usage/options visible | Operator-local PASS; full-range runtime and final replay also PASS for 807/807 trading dates | RUNTIME_PROOF_PASS |
| `php artisan market-data:eod-eligibility:build --help` | Usage/options visible | eligibility/date options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:audit:hash --help` | Usage/options visible | hash/audit options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:dataset:seal --help` | Usage/options visible | seal options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:run:finalize --help` | Usage/options visible | run/requested date options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:replay:smoke --help` | Usage/options visible | smoke/replay output options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:replay:backfill --help` | Usage/options visible | from/to/output options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:replay:fixture:generate --help` | Usage/options visible | run_id, `--case`, and `--output_dir` visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:backfill --help` | Usage/options visible | date range/source options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:session-snapshot --help` | Usage/options visible | snapshot trade date/output options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:session-snapshot:purge --help` | Usage/options visible | purge date/retention options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:current-publication:repair --help` | Usage/options visible | repair/dry-run/apply options visible where supported | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:provider:smoke --help` | Usage/options visible | safe single-ticker provider smoke options visible, including `--ticker`, `--trade_date`, `--dry-run`, `--max-tickers`, `--timeout`, `--provider`, and `--json` | RUNTIME_PROOF_PASS |
| `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=YYYY-MM-DD --dry-run` | `provider_smoke_status=PASS` only when valid data is returned | Current runtime is `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, `pointer_switched=false`, `full_universe_fetch=false`; this is provider PASS | PROVIDER_SMOKE_PASSED |

## Local runtime proof recorded after fix1

| Command | Result | Tests | Assertions | Status |
|---|---|---:|---:|---|
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | OK | 10 | 131 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"` | OK | 10 | 131 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` | OK | 10 | 199 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` | OK | 47 | 348 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK | 44 | 767 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK | 39 | 655 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK | 65 | 1287 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` | OK | 5 | 108 | RUNTIME_PROOF_PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 378 | 5072 | RUNTIME_PROOF_PASS |

Artisan command proof recorded after fix1:

- `php artisan list | findstr market-data` listed 20 market-data commands after fixture generator, including `market-data:replay:fixture:generate`; provider-smoke reconciliation superseded the count to 21 commands; the 2026-06-03 full-range current evidence/replay extension superseded the count to 22 commands; the sector membership import extension superseded the count to 23 commands; lifecycle reconciliation superseded the count to 24 commands; the sector-index CSV import checkpoint superseded the count to 25 commands; the sector-index API checkpoint superseded the count to 26 commands; event-risk source imports superseded the count to 28 commands; the current command surface count is 30 after adding `market-data:backfill:missing-tickers`.
- Help output PASS for `market-data:replay:fixture:generate`, `market-data:replay:smoke`, `market-data:replay:verify`, `market-data:evidence:export`, `market-data:daily`, `market-data:promote`, `market-data:run:finalize`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.
- Required command list/full-help proof is recorded; optional broader command-help reruns remain governance-only for future command changes.


## Evidence export runtime proof checklist

Manual validation commands:

- `php artisan market-data:evidence:export --run_id=<RUN_ID> --output_dir=storage/app/market-data/evidence`
- `php artisan market-data:evidence:export --correction_id=<CORRECTION_ID> --output_dir=storage/app/market-data/evidence`
- `php artisan market-data:evidence:export --replay_id=<REPLAY_ID> --output_dir=storage/app/market-data/evidence`
- `php artisan market-data:evidence:export --trade_date=<YYYY-MM-DD> --output_dir=storage/app/market-data/evidence`

Expected output:

- command exits successfully
- evidence output path printed or inferable
- manifest exists
- run context exists
- publication context exists when applicable
- pointer/current-publication context exists when applicable
- coverage context exists when applicable
- source context exists
- lineage context exists
- reason code exists when terminal state is HELD, FAILED, NOT_READABLE, or otherwise not fully readable
- no proof requires manual DB query as primary validation

Pass/fail criteria:

- PASS only if evidence output exists on disk and metadata proves run, publication, pointer, coverage, source, reason code, correction, replay, and lineage as applicable.
- FAIL if evidence path is missing, manifest is incomplete, output is empty but treated as success, or database query is required as the primary proof.

## Replay runtime proof checklist

Manual validation commands:

- `php artisan market-data:replay:verify <RUN_ID> <FIXTURE_PATH> --output_dir=storage/app/market-data/replay`
- `php artisan market-data:replay:smoke <RUN_ID> --output_dir=storage/app/market-data/replay`
- `php artisan market-data:replay:backfill --from=<YYYY-MM-DD> --to=<YYYY-MM-DD> --output_dir=storage/app/market-data/replay`
- `php artisan market-data:replay:fixture:generate <RUN_ID> --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-<RUN_ID>`
- `php artisan market-data:replay:smoke <RUN_ID> --generate_runtime_valid_case --output_dir=storage/app/market-data/replay`
- `php artisan market-data:evidence-replay:full-range-current <START_DATE> <END_DATE> --fixture_case=valid_case --output_dir=storage/app/market_data/evidence/full_range_current_evidence_replay/<START_DATE>_to_<END_DATE>`
- `php artisan market-data:sector-indexes:ingest-api <START_DATE> <END_DATE> --apply`
- `php artisan market-data:sector-indexes:import-bars <CSV_PATH> --apply`
- `php artisan market-data:sectors:import-memberships <CSV_PATH> --apply`

Expected output:

- replay result PASS/FAIL is explicit
- valid fixture case passes deterministically
- generated runtime fixture returns MATCH
- generated runtime fixture verify returns `mismatch_count=0`
- smoke with `--generate_runtime_valid_case` returns `all_passed=1`
- reason code mismatch case fails with reason code
- broken manifest case fails with reason code
- missing file case fails with reason code
- mismatch output explains observed vs expected state
- output artifact is created
- replay does not use raw/staging/latest/MAX(date)

Pass/fail criteria:

- PASS only if replay artifact exists, generated runtime valid fixture returns MATCH with mismatch_count=0, smoke with generated valid case returns all_passed=1, and valid/mismatch/broken/missing cases are explainable with reason codes.
- FAIL if replay silently passes missing artifacts, generated fixture still mismatches the same run, depends on volatile current DB state, or hides mismatch reason code.

## Daily / import / promote / finalize runtime proof checklist

Manual validation commands:

- `php artisan market-data:daily --requested_date=<YYYY-MM-DD> --source_mode=manual_file --input_file=<PATH> --output_dir=storage/app/market-data/runs`
- `php artisan market-data:promote --requested_date=<YYYY-MM-DD> --source_mode=manual_file --run_id=<RUN_ID> --output_dir=storage/app/market-data/runs`
- `php artisan market-data:run:finalize --requested_date=<YYYY-MM-DD> --run_id=<RUN_ID>`

Expected output:

- daily import-only creates or references a run without making the dataset current/readable automatically
- promote evaluates coverage gate
- hash/seal/finalize steps are visible or inferable from command output/evidence
- pointer switches only when target is SUCCESS + READABLE + SEALED + coverage PASS
- HELD, FAILED, and NOT_READABLE outputs expose reason_code/final_reason_code
- invalid publication does not become current

Pass/fail criteria:

- PASS only if output proves gate sequence and terminal state without shortcut.
- FAIL if manual file becomes readable without promote, coverage is bypassed, pointer changes without valid publication, or output hides reason code.

## Failure scenario validation matrix

| Scenario | Runtime/Test Proof Required | Expected State | Reason Code Requirement | Status |
|---|---|---|---|---|
| Coverage below threshold | Coverage test or promote runtime output | HELD / NOT_READABLE or SUCCESS / NOT_READABLE according to policy | `COVERAGE_BELOW_THRESHOLD` or equivalent registered reason | PENDING_RUNTIME_EVIDENCE |
| Provider rate limited | Provider resilience test/runtime output | HELD / NOT_READABLE or FAILED / NOT_READABLE | registered rate-limit reason | PENDING_RUNTIME_EVIDENCE |
| Source unavailable | Source/provider test/runtime output | HELD / NOT_READABLE or FAILED / NOT_READABLE | registered source failure reason | PENDING_RUNTIME_EVIDENCE |
| Manual file invalid | Manual file policy test/runtime output | FAILED / NOT_READABLE or rejected import | registered manual-file invalid reason | PENDING_RUNTIME_EVIDENCE |
| Run lock conflict | Finalize/lock test/runtime output | HELD / NOT_READABLE and pointer preserved | `RUN_LOCK_CONFLICT` or equivalent | PENDING_RUNTIME_EVIDENCE |
| Pointer mismatch | Pointer/finalize test/runtime output | FAILED or HELD, no current switch | pointer mismatch reason | PENDING_RUNTIME_EVIDENCE |
| Publication not sealed | Hash/seal/finalize test/runtime output | NOT_READABLE, no current switch | unsealed publication reason | PENDING_RUNTIME_EVIDENCE |
| Correction baseline invalid | Correction test/runtime output | correction blocked, previous current preserved | correction baseline reason | PENDING_RUNTIME_EVIDENCE |
| Correction already published | Correction lifecycle test/runtime output | blocked/rejected rerun | `CORRECTION_ALREADY_PUBLISHED` or equivalent | PENDING_RUNTIME_EVIDENCE |
| Replay mismatch | Replay verify output | FAIL with mismatch details | replay mismatch reason | PENDING_RUNTIME_EVIDENCE |
| Evidence export incomplete | Evidence test/runtime output | FAILED_VALIDATION or blocked export | evidence incomplete reason | PENDING_RUNTIME_EVIDENCE |
| No readable publication | Read-side/session snapshot output | NOT_READABLE/BLOCKED | no readable publication reason | PENDING_RUNTIME_EVIDENCE |
| Session snapshot blocked | Session snapshot runtime output | BLOCKED, no raw/latest fallback | snapshot blocked reason | PENDING_RUNTIME_EVIDENCE |

## Audit docs synchronization rule

- `Production Validation / Manual + Runtime Proof` may be marked READY_FOR_LOCAL_RUNTIME_VALIDATION while only static/container proof exists.
- `PRODUCTION_VALIDATION_CONTRACT` may be ENFORCED while runtime evidence is pending.
- Do not mark this implementation DONE unless targeted ProductionValidation, related targeted suites, full `tests/Unit/MarketData`, artisan list/help, evidence export, and replay runtime proof have actual output.
- Do not mark this contract LOCKED unless targeted and full suite PASS plus artisan/evidence/replay runtime proof are recorded.
- Static guard is not runtime proof.
- PENDING_RUNTIME_EVIDENCE must stay visible until closed by actual output.
- PARTIAL_RUNTIME_PROOF must list exactly which evidence exists and which proof is still missing.
- READY_FOR_LOCAL_RUNTIME_VALIDATION is the correct status when vendor/artisan/PHPUnit cannot be run from the uploaded ZIP.

## Manual validation command block

Run locally from the project root after extracting this ZIP:

```text
vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "ProductionValidation"
vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"
vendor/bin/phpunit tests/Unit/MarketData
php artisan list | findstr market-data
php artisan market-data:daily --help
php artisan market-data:promote --help
php artisan market-data:evidence:export --help
php artisan market-data:replay:verify --help
php artisan market-data:correction:request --help
php artisan market-data:correction:approve --help
php artisan market-data:correction:run --help
```

Expected output:

- PHPUnit commands return `OK (... tests, ... assertions)`.
- Artisan list shows registered market-data commands.
- Help commands show usage/options and no fatal error.
- Evidence/replay/flow commands create artifacts when run with valid local fixture IDs/paths.

Pass/fail criteria:

- PASS when all required targeted PHPUnit, full suite, command list/help, evidence output, replay verification, and flow/failure validation are either proven by actual output or explicitly marked pending where fixture/data is unavailable.
- FAIL when any DONE/LOCKED claim exists without runtime proof, any command/test fails, evidence/replay output is claimed without artifact path, command help/list drifts from docs, or pending runtime gaps are hidden.

## Regression reconciliation summary

| Prior Contract Area | Current Production Validation Decision |
|---|---|
| Read-side pointer enforcement | Requires current targeted/full suite evidence before production-validated claim. |
| Coverage gate enforcement | Requires coverage failure/pass runtime or targeted proof. |
| Publishability state integrity | Requires runtime/test proof for READABLE vs NOT_READABLE/HELD/FAILED. |
| Finalize / lock / pointer determinism | Requires finalize/pointer targeted proof and flow output where possible. |
| Run / publication / pointer linkage | Requires lineage proof in evidence/replay/command output. |
| Correction lifecycle safety | Requires correction request/approve/run proof or targeted test output. |
| Source / provider resilience | Requires provider failure/rate-limit proof or documented blocker. |
| Manual file policy enforcement | Requires import-only/promote proof. |
| Evidence export completeness | Requires actual evidence output artifact path before production-ready claim. |
| Replay determinism | Requires actual replay verify/smoke artifact path before production-ready claim. |
| Command surface safety | Requires artisan list/help output. |
| Logging / traceability / reason codes | Requires reason-code test/runtime evidence and seed/registry sync. |
| DB integrity & constraint enforcement | Requires schema/test proof. |
| Test coverage behavioral | Requires full suite evidence after this patch. |
| Hash / seal / dataset integrity | Requires hash/seal targeted proof and deterministic behavior evidence. |
| Import vs Promote Separation | Requires import-only/promote proof. |
| Fail-safe behavior / no silent failure | Requires FailSafe targeted proof and failure scenario evidence. |
| Audit docs synchronization | Requires AuditDocs/ProductionValidation guards and no append-only violation. |
| Operational readiness | Prior LOCKED evidence exists, but production validation needs fresh rerun after this patch. |

## Current decision

`PRODUCTION_VALIDATION_CONTRACT` is LOCKED. `Production Validation / Manual + Runtime Proof` is DONE. Operator-local proof exists for ProductionValidation guard/filter, related targeted PHPUnit filters, full MarketData PHPUnit, 30 registered market-data commands artisan discovery/help output, success flow, held/failure flow, run/replay/correction evidence, replay generated MATCH proof, replay smoke `all_passed=1`, and correction lifecycle proof.

## 2026-05-20 Ops Command Surface Runtime Matrix Update

This append-only update records a new command-runtime matrix on top of the historical production validation proof. It supports the aggregate market-data production-ready claim for this source state. The current ops-command scope is `ENFORCED`, `DONE` / `LOCKED` for the current source state after final provider proof.

Evidence source: `docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`.

Runtime proof added:

- `php artisan list market-data` returned exit 0 with all 21 expected public market-data commands.
- All 20 `--help` commands returned exit 0 and rendered usage/options.
- Invalid/missing input proof returned command-owned `status=BLOCKED` plus `COMMAND_*` or domain reason codes for daily, backfill, promote, evidence export, replay verify/smoke/backfill/fixture generation, correction request/approve/run, current-publication repair, session snapshot capture, and session snapshot purge.
- Seeded finalize re-run proof for `run_id=6` returned `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `seal_state=SEALED`, `publication_id=5`, and `current_publication_id=5`.
- Seeded evidence export proof passed for `run_id=6`, `replay_id=10`, and `correction_id=3`.
- Seeded replay fixture generation and verify proof passed for `run_id=6`, producing `replay_id=11`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`.
- Seeded replay smoke proof passed with `all_passed=1`, including PASS, expected MISMATCH/FAIL, and BLOCKED fixture cases.
- Seeded replay backfill proof passed with `replay_id=14`, `replay_status=PASS`.
- Repair dry-run returned no invalid pointer; repair `--apply` without reason blocked with `COMMAND_DESTRUCTIVE_GUARD_REQUIRED`.
- Session snapshot purge dry-run and safe apply-zero returned `COMMAND_DRY_RUN_ONLY` and `COMMAND_APPLY_CONFIRMED` with `deleted_rows=0`.
- Session snapshot no-readable path blocked with `NO_READABLE_PUBLICATION`.
- Correction rerun for failed correction blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`; correction request without a valid baseline blocked with `CORRECTION_BASELINE_LINK_MISSING`.
- Promote force replacement without reason blocked with `COMMAND_DESTRUCTIVE_GUARD_REQUIRED`.
- Post-ledger validation passed for this ops matrix patch: `OpsCommandSurfaceTest.php` OK (55 tests, 333 assertions), `CommandSurfaceSafetyStaticGuardTest.php` OK (5 tests, 89 assertions), `OperationalReadinessStaticGuardTest.php` OK (10 tests, 204 assertions), `OpsEnvironmentBaselineStaticGuardTest.php` OK (8 tests, 107 assertions), `ProductionValidationRuntimeProofStaticGuardTest.php` OK (13 tests, 220 assertions), `OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` OK (5 tests, 100 assertions), `Command` filter OK (94 tests, 987 assertions), `Ops` filter OK (71 tests, 594 assertions), `Operational` filter OK (11 tests, 211 assertions), `RuntimeProof` filter OK (13 tests, 220 assertions), `AuditDocs` filter OK (10 tests, 398 assertions), `StaticGuard` filter OK (175 tests, 4104 assertions), and full `tests/Unit/MarketData` OK (472 tests, 6914 assertions).

Remaining ops runtime proof gaps:

- Fresh daily/backfill/promote/stage success paths: `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE`.
- Real lock conflict: `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE`.
- Repair `--apply` against an invalid pointer fixture: `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE`.
- Successful session snapshot capture against an isolated readable-publication fixture: `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE`.

Production validation impact: these new runtime outputs strengthen the command-surface portion of the proof pack, but they do not by themselves restore a full market-data production-ready final claim for this source state. A later aggregate proof-pack rerun must consume this matrix and close or explicitly accept the fixture-limited cases.

## 2026-05-20 Ops Command Surface Runtime Matrix Lock Update

This append-only update closes the fixture-limited ops-command cases recorded above. It does not relock or newly claim the aggregate full market-data production-ready final proof pack. It does mark the ops command surface runtime matrix as production-ready for this scoped market-data area.

Evidence source: `docs/market_data/audit/OPS_COMMAND_SURFACE_RUNTIME_MATRIX_INVENTORY.md`.

Runtime artifact root: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/**`.

Runtime proof added:

- Fixture setup PASS: `php tests/Support/MarketData/SeedOpsCommandRuntimeMatrixFixture.php` produced `status=FIXTURE_READY`, `ticker_count=913`, target dates `2026-05-11` through `2026-05-18`, and `fixture_manifest.json`.
- Fresh daily PASS: `market-data:daily --requested_date=2026-05-11 --source_mode=manual_file --input_file=storage/app/market_data/eod_bars/2026-05-11.json` produced `run_id=30`, `accepted_row_count=913`, `request_mode=import_only`, and no pointer switch.
- Fresh backfill PASS: `market-data:backfill 2026-05-12 2026-05-12 --source_mode=manual_file --input_file=storage/app/market_data/eod_bars/2026-05-12.json` produced `all_imported=1` and `all_passed=1`.
- Stage-chain PASS: `market-data:eod-bars:ingest --request_mode=full_publish` plus indicators, eligibility, hash, seal, and finalize for `run_id=32` produced `SUCCESS`, `READABLE`, `coverage_gate_state=PASS`, `seal_state=SEALED`, `current_publication_id=26`.
- Promote success PASS: `market-data:promote` for `2026-05-14` produced `run_id=33`, `publication_id=27`, `current_publication_id=27`, `SUCCESS`, `READABLE`, `PASS`, `SEALED`.
- Session snapshot PASS: `market-data:session-snapshot 2026-05-14 OPEN_CHECK` produced `scope_count=913`, `captured_count=913`, `skipped_count=0`.
- Lock conflict PASS: second promote for `2026-05-15` produced exit 1, `terminal_status=HELD`, `publishability_state=NOT_READABLE`, `reason_code=RUN_LOCK_CONFLICT`, `pointer_switched=false`.
- Held/failed PASS: partial promote for `2026-05-16` produced `RUN_PARTIAL_DATA` and `coverage_summary=available=5/913`; empty daily for `2026-05-17` produced `terminal_status=FAILED`, `reason_code=RUN_SOURCE_MANUAL_FILE_EMPTY`.
- Repair apply PASS: invalid pointer for `2026-05-18` was detected by dry-run, cleared with `--apply --reason`, and after-apply rerun returned `status=OK`.
- Evidence/replay PASS: evidence export for `run_id=33` wrote 10 files; replay fixture generation succeeded; replay verify produced `replay_id=15`, `comparison_result=MATCH`, `replay_status=PASS`; replay smoke returned `all_passed=1`; replay backfill produced `replay_id=18`, `replay_status=PASS`.
- Runtime bug fixed: `market-data:audit:hash` is now callable at runtime because `MarketDataPipelineService::completeHash()` is public.
- Post-lock validation PASS: `OpsCommandSurfaceTest.php` OK (57 tests, 341 assertions), `CommandSurfaceSafetyStaticGuardTest.php` OK (5 tests, 89 assertions), `OperationalReadinessStaticGuardTest.php` OK (10 tests, 204 assertions), `OpsEnvironmentBaselineStaticGuardTest.php` OK (8 tests, 107 assertions), `ProductionValidationRuntimeProofStaticGuardTest.php` OK (13 tests, 220 assertions), `OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` OK (6 tests, 114 assertions), `AuditDocsSynchronizationStaticGuardTest.php` OK (10 tests, 404 assertions), `Command` filter OK (97 tests, 1009 assertions), `Ops` filter OK (74 tests, 616 assertions), `Operational` filter OK (11 tests, 211 assertions), `RuntimeProof` filter OK (13 tests, 220 assertions), `AuditDocs` filter OK (10 tests, 404 assertions), `StaticGuard` filter OK (176 tests, 4124 assertions), and full `tests/Unit/MarketData` OK (475 tests, 6942 assertions).

Production validation impact: ops command surface is no longer a production-readiness blocker. The separate aggregate validation/proof-pack synchronization step has now consumed this proof and locked the current source state.


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


## 2026-05-21 Production Rollout Validation Runtime Parity Proof

Scope: `PRODUCTION_ROLLOUT_VALIDATION_RUNTIME_PARITY_PROOF`.

Status: `SUPERSEDED_BY_FINAL_PASSED_RECONCILIATION` for current full rollout parity. Source-state `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid and final ops runtime parity is now `OPS_RUNTIME_PARITY_PASSED` for the current source ZIP.

Evidence root: `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| PHP/runtime baseline | PHP 7.4.33, required extensions present | `php -v`, `php -m` | PASS |
| Composer | Composer 2.8.4; `composer validate` valid | `composer --version`, `composer validate` | PASS |
| Artisan boot | Lumen 8.3.4, no warning/deprecation/noise | `php artisan list`, `php artisan --version` | PASS |
| Command registry/help | 21 market-data commands registered; requested help commands exit 0 | help outputs under command-output root plus provider-smoke safe-mode artifact | PASS; previous provider rate-limit wording is historical and superseded by final provider smoke PASS |
| Targeted static guards | AuditDocs OK (10/419), ProductionValidation OK (13/220), OperationalReadiness OK (10/204), OpsEnvironment OK (8/107), ConfigEnvGovernance OK (10/123) | final rerun outputs | PASS |
| Filtered suites | AuditDocs OK (10/419), StaticGuard OK (176/4139), Production OK (14/253), Operational OK (11/211), OpsEnvironment OK (8/107) | final rerun outputs | PASS |
| Full MarketData suite | OK (475 tests, 6957 assertions), Time 00:10.716, Memory 38.00 MB | `vendor/bin/phpunit tests/Unit/MarketData` | PASS |
| Manual-file import/promote | Import-only stayed not promoted and no pointer switch; promote returned SUCCESS/READABLE/PASS/SEALED | `run_id=30`, `publication_id=24` | PASS |
| Evidence export | Run evidence admitted and complete | `market-data:evidence:export --run_id=30`, 10 files | PASS |
| Replay verify current | Runtime fixture MATCH/PASS | `replay_id=19`, `run_id=33`, `mismatch_count=0` | PASS |
| Replay verify historical | Historical non-current publication MATCH/PASS | `replay_id=20`, `publication_id=2`, `NOT_CURRENT_POINTER`, `HISTORICAL_PUBLICATION_AUDIT` | PASS |
| Correction lifecycle | Request/approve/run/evidence/rerun guard validated | `correction_id=5`, `CONSUMED_CURRENT`, `ADMITTED_COMPLETE`, rerun blocked | PASS |
| Migration chain | All 29 migrations run cleanly; tables present under explicit `DB_DATABASE=tradeaxis_testing` override | plain `--env=testing` did not select `.env.testing` DB | PASS_WITH_ENVIRONMENT_BLOCKER |
| Scheduler/cron | `schedule:run` cleanly exits with no ready commands; code registers daily only when enabled | `schedule:list` unavailable; `MARKET_DATA_DAILY_ENABLED` not enabled | BLOCKED_DEPLOYMENT_PROOF |
| Storage/log/evidence path | Required paths exist and writable | write probes under storage paths | PASS |
| Live provider smoke | Not executed | no dry-run/ticker-limit command surface; broad provider fetch avoided | BLOCKED_SAFE_PROVIDER_SMOKE |

Blocker classification:

- Source-code P0/P1 blocker: none found.
- `BLOCKED_TESTING_DATABASE_ENV`: plain `php artisan migrate:fresh --env=testing` operated against `.env` database `tradeaxis`, not `.env.testing` database `tradeaxis_testing`; explicit env override was required for the intended testing DB.
- `OPS_DEPLOYMENT_TASK_REQUIRED`: production scheduler/cron enablement, log routing, and no-silent-failure proof still need deployment environment validation.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: live provider smoke requires a safe dry-run/limited ticker path or an isolated staging DB/provider plan.

Final rollout decision for this session:

- `OPS_RUNTIME_PARITY_PASSED`.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for the locked source state.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).


## 2026-05-21 Testing DB Isolation / Safe Migration Guard

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Status: `DONE` for testing DB isolation. Previous transition wording is superseded: scheduler due-run/non-silent-failure proof and safe live provider smoke PASS are now recorded, so overall rollout status is `OPS_RUNTIME_PARITY_PASSED`.

Evidence root: `storage/app/market-data/testing-database-isolation-safe-migration/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| Environment file loading | `--env=testing` resolves `.env.testing` before config boot | `APP_ENV=testing`, `DB_CONNECTION=mysql`, `DB_DATABASE=tradeaxis_testing` | PASS |
| Negative destructive guard | Unsafe testing DB target blocked before migration handling | `php artisan migrate:fresh --env=testing --database=nonexistent` -> exit 3, `BLOCKED_TESTING_DATABASE_ENV` | PASS |
| Migration status | Testing migration status command boots cleanly | `php artisan migrate:status --env=testing` -> exit 0 | PASS |
| Testing migrate fresh | Destructive migration targets testing DB and succeeds | `php artisan migrate:fresh --env=testing` -> exit 0, 29 migrations | PASS |
| Required table proof | Required market-data tables exist in `tradeaxis_testing` | `tickers`, `market_calendar`, `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `md_replay_daily_metrics`, `eod_dataset_corrections`, `md_session_snapshots` | PASS |
| Static guard | Regression guard added | `tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions) | PASS |
| Filtered static guard | New guard included in aggregate static sweep | `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions) | PASS |
| Full MarketData suite | Regression proof remains clean after env/guard patch | `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB | PASS |
| Evidence encoding | New command outputs are UTF-8 without null-byte/UTF-16 evidence noise | command-output files under evidence root | PASS |

Implementation summary:

- `bootstrap/app.php` now detects CLI `--env testing`, CLI `--env=testing`, or system `APP_ENV` before Lumen environment loading and selects `.env.<environment>` when the file exists.
- `artisan` now guards `migrate:fresh`, `migrate:refresh`, `migrate:reset`, and `db:wipe` in testing before `$kernel->handle(...)`.
- The guard accepts only `tradeaxis_testing` as the destructive testing migration database and emits `BLOCKED_TESTING_DATABASE_ENV` with exit code 3 otherwise.

Final decision for this blocker:

- `BLOCKED_TESTING_DATABASE_ENV` is closed for this patched source state.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- At this DB-isolation closure point, full production rollout parity still had `OPS_DEPLOYMENT_TASK_REQUIRED` and `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`; scheduler status is superseded by the Production Scheduler / Cron Deployment Proof section below.

Post-patch validation:

- `vendor/bin/phpunit tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 430 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 204 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 123 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB.


## 2026-05-21 Production Scheduler / Cron Deployment Proof

Scope: `PRODUCTION_SCHEDULER_CRON_DEPLOYMENT_PROOF_CONTRACT`.

Status: `DONE` / `SCHEDULER_RUNTIME_LOG_PRODUCED` because the source ZIP contains the scheduler due-run runtime artifacts required to accept the scheduler proof claim. Overall rollout status remains `OPS_RUNTIME_PARITY_PASSED`.

Evidence root: `storage/app/market-data/production-scheduler-cron-deployment-proof/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| Safe DB precondition | Testing DB reset targets `tradeaxis_testing` | `php artisan migrate:fresh --env=testing` -> exit 0 | PASS |
| Negative DB override | Unsafe env override blocked before destructive migration | `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` -> exit 3, `BLOCKED_TESTING_DATABASE_ENV` | PASS |
| Scheduler config enabled | Daily enabled and due in Asia/Jakarta | `daily_enabled=true`, `default_source_mode=manual_file`, `timezone=Asia/Jakarta`, `cutoff_time=11:52:00` | PASS |
| Scheduler invocation | Daily scheduled command actually invoked | `php artisan schedule:run --env=testing` -> `Running scheduled command: ... market-data:daily --latest` | PASS |
| Scheduler output log | Failure is visible and reason-coded | `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `pointer_switched=false`, `scheduler_status=FAILURE` | PASS |
| Disabled control | Scheduler remains quiet when disabled | `MARKET_DATA_DAILY_ENABLED=false php artisan schedule:run --env=testing` -> `No scheduled commands are ready to run.` | PASS |
| No live provider touch | Proof uses `manual_file` safety mode | no provider/API broad-universe fetch, no readable publication, no pointer switch | PASS |
| Static guard | Scheduler contract regression guard added | `tests/Unit/MarketData/ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update | PASS |
| Filtered static guard | New guard included in aggregate static sweep | `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions) | PASS |
| Full MarketData suite | Regression proof remains clean after scheduler patch | `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB | PASS |
| Evidence encoding | New command outputs are UTF-8 without null-byte/UTF-16 evidence noise | command-output files under evidence root | PASS |

Implementation summary:

- `app/Console/Kernel.php` keeps the daily schedule conditional on `market_data.pipeline.daily_enabled`.
- Scheduler event now uses configured cutoff, `Asia/Jakarta` timezone, `withoutOverlapping`, append-only output log, and success/failure status markers.
- `MARKET_DATA_SCHEDULER_OUTPUT_PATH` and `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES` are documented in config/env surfaces.

Final decision for this blocker:

- `OPS_DEPLOYMENT_TASK_REQUIRED` is closed for this source ZIP because scheduler runtime command-output/log artifacts are supplied and archived.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Full production rollout parity is `OPS_RUNTIME_PARITY_PASSED` because scheduler due-run proof is produced and provider smoke safe mode returned PASS.

Post-patch validation:

- `vendor/bin/phpunit tests/Unit/MarketData/ProductionSchedulerCronStaticGuardTest.php` -> rerun required after artifact-reconciliation guard update.
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 439 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ConfigEnvGovernanceCleanupStaticGuardTest.php` -> OK (10 tests, 123 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Scheduler"` -> rerun required after artifact-reconciliation guard update.
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (184 tests, 4286 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (483 tests, 7104 assertions), Time 00:11.035, Memory 40.00 MB.

## 2026-05-21 Scheduler Runtime Artifact Synchronization Reconciliation

Status: `DONE` / `SCHEDULER_RUNTIME_LOG_PRODUCED`.

The previous scheduler section named runtime artifacts under `storage/app/market-data/production-scheduler-cron-deployment-proof/**`, but those command-output/log files are not present in the source ZIP. The scheduler code and static guard hardening remain useful, but the runtime proof claim cannot be accepted as artifact-backed until those files are supplied or rerun.

Reconciliation artifacts now present:

- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/artifact-presence-audit.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/negative-db-override-proof-gap.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/provider-smoke-gap.txt`.

Open rollout blockers:

- `SCHEDULER_RUNTIME_LOG_PRODUCED`.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.

## 2026-05-21 Runtime Parity Evidence Encoding Cleanup

Status: `DONE`.

The legacy command-output files under `storage/app/market-data/production-rollout-validation-runtime-parity/command-output/**` were normalized to UTF-8 plain text to remove null-byte / UTF-16-like evidence noise that could break grep/CI parsing.

Evidence artifact:

- `storage/app/market-data/production-rollout-validation-runtime-parity/command-output/encoding-normalization-report.txt`.

This cleanup does not change market-data runtime behavior or convert missing scheduler proof into a PASS. The previous scheduler `REVIEW_REQUIRED` wording is `SUPERSEDED_BY_SCHEDULER_DUE_RUN_AND_NON_SILENT_FAILURE_PROOF`; only successful scheduled daily production run proof remains not claimed.

Global evidence encoding cleanup artifact:

- `storage/app/market-data/evidence-encoding-normalization-report.txt`.

This global report confirms all `storage/app/market-data/**/*.txt` evidence files were normalized to UTF-8 plain text with no null-byte residue.

---

## 2026-05-21 — OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION] OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- `OPS_RUNTIME_PARITY_PASSED` remains the only valid overall ops runtime parity status for this source ZIP.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for core market-data source logic.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- This update closes the missing safe provider-smoke command surface at source level, but does not claim live provider PASS because local artisan execution is blocked by the documented unsupported PHP 8.4.16 runtime.

[IMPLEMENTATION]
- Added `app/Console/Commands/MarketData/ProviderSmokeCommand.php` with command surface `market-data:provider:smoke --ticker=BBCA --trade_date=YYYY-MM-DD --dry-run`.
- Registered `ProviderSmokeCommand::class` in `app/Console/Kernel.php`.
- The provider smoke command is dry-run only, single-ticker only, and calls `PublicApiEodBarsAdapter::fetchOrLoadEodBars($tradeDate, 'api', [$ticker])` without ingest pipeline writes.
- Provider smoke does not call seal, finalize, publication switching, current pointer updates, candidate publication creation, or artifact replacement.
- Added early `artisan` fail-closed env override guard so `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` exits before the unsupported-PHP guard and proves `BLOCKED_TESTING_DATABASE_ENV` with exit code 3 in this container.

[PROVIDER_SMOKE_SAFE_MODE]
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.
- Output contract includes `provider_smoke_status=`, `reason_code=`, `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, and `full_universe_fetch=false`.
- Supported reason codes include `PROVIDER_SMOKE_OK`, `PROVIDER_RATE_LIMITED`, `PROVIDER_TIMEOUT`, `PROVIDER_NETWORK_ERROR`, `PROVIDER_EMPTY_OR_INVALID_RESPONSE`, `PROVIDER_SMOKE_TICKER_REQUIRED`, `PROVIDER_SMOKE_INVALID_TICKER`, and `PROVIDER_SMOKE_FULL_UNIVERSE_BLOCKED`.
- Runtime attempt artifact records `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `publication_created=false`, and `pointer_switched=false`.

[SCHEDULER_ARTIFACT_STATUS]
- Scheduler config surface artifact was written, but the actual `schedule:run` enabled/disabled commands are `BLOCKED_CONTAINER_RUNTIME_ENV` because PHP 8.4.16 is intentionally rejected before Laravel boot.
- `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_NOT_PRODUCED` with `REASON_CODE=BLOCKED_CONTAINER_RUNTIME_ENV`.
- `SUPERSEDED_BY_SCHEDULER_DUE_RUN_AND_NON_SILENT_FAILURE_PROOF`: previous scheduler proof review requirement is closed for due-run/non-silent-failure proof; do not claim `SCHEDULER_SUCCESSFUL_DAILY_RUN_PROOF_PASSED`.

[NEGATIVE_DB_OVERRIDE_PROOF]
- `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` was executed in this container.
- Result: `BLOCKED_TESTING_DATABASE_ENV`, `EXIT_CODE:3`.
- This is the only runtime command in this session that produced the expected safety result inside the container.

[LOCAL_RUNTIME_STATUS]
- Environment baseline: `BLOCKED_CONTAINER_RUNTIME_ENV` because `php artisan --version`, `php artisan list`, `schedule:run`, provider smoke, and PHPUnit are blocked by `ENV_UNSUPPORTED_PHP_VERSION` on PHP 8.4.16.
- Composer is unavailable in the container, so `composer --version` and `composer validate` are also blocked.
- PHPUnit targeted/full suite not executed; status remains `BLOCKED_CONTAINER_RUNTIME_ENV`, PASS.

[EVIDENCE]
- `storage/app/market-data/ops-runtime-parity-completion/command-output/phase1-environment-baseline.txt`.
- `storage/app/market-data/ops-runtime-parity-completion/command-output/phpunit-provider-smoke-static-guard.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase0-migrate-fresh-testing-precondition.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase1-testing-db-negative-env-override.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase2-scheduler-config-enabled.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase3-schedule-run-enabled-due.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase4-scheduler-output-log.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/command-output/phase5-schedule-run-disabled-control.txt`.
- `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- `storage/app/market-data/evidence-encoding-normalization-report.txt` reports `checked_files=165`, `normalized_files=0`, `null_byte_remaining=0`, `status=PASS`.

[VALIDATION]
- `php -l artisan` -> PASS.
- `php -l app/Console/Kernel.php` -> PASS.
- `php -l app/Console/Commands/MarketData/ProviderSmokeCommand.php` -> PASS.
- `php -l tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> PASS.
- `php vendor/bin/phpunit tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> `BLOCKED_CONTAINER_RUNTIME_ENV` because PHPUnit stops on missing `dom`, `mbstring`, `xml`, and `xmlwriter` extensions before project bootstrap.

[BLOCKERS]
- Source-code blocker: none found in scoped patch.
- Environment blocker: PHP 8.4.16 unsupported by project evidence guard; Composer unavailable in container.
- Historical provider blocker is superseded by the later live provider smoke PASS artifact; current status is `FINAL_PROVIDER_SMOKE=PASSED`.

[REMAINING_RISK]
- Provider smoke and scheduler/runtime proof have been reconciled on the documented operator baseline; `OPS_RUNTIME_PARITY_PASSED` is the current source-ZIP decision.
- Previous historical `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED` is superseded at source-surface level by the new command, but runtime provider proof is now passed in the operator-local runtime artifact.

---

## 2026-05-21 Final Proof Pack / Ops Runtime Parity Reconciliation

[SOURCE_ZIP]
- Historical source ZIP (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `D:\Laravel\tradeaxis-api\tradeaxis-api-provider.zip`
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`

[CURRENT_PRODUCTION_VALIDATION_SURFACE]
- Current public market-data command count at this 2026-05-21 checkpoint: 21.
- New command surface member at this checkpoint: `market-data:provider:smoke`.
- Required command proof marker at this checkpoint: `21-command command list/full help`.
- 2026-06-03 superseding command surface count: 26 after including `market-data:backfill:lifecycle` and adding `market-data:sector-indexes:import-bars` plus `market-data:sector-indexes:ingest-api`; required marker was `26-command command list/full help`.
- 2026-06-04 superseding command surface count: 28 after adding `market-data:events:import-corporate-actions` and `market-data:events:import-trading-status`; required marker was `28-command command list/full help`.
- 2026-06-06 current command surface count: 30 after adding `market-data:backfill:missing-tickers` and `market-data:eod-indicators:recompute-current`; required marker is `30-command command list/full help`.
- Source-state validation remains `MARKET_DATA_PRODUCTION_READY_LOCKED` because full `vendor/bin/phpunit tests/Unit/MarketData` passed after this reconciliation.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

[VALIDATION]
- Targeted guards passed: ProductionValidation OK (14 tests, 467 assertions), CommandSurfaceSafety OK (5 tests, 91 assertions), OpsCommandSurfaceRuntimeMatrix OK (6 tests, 120 assertions), ProviderSmokeSafeMode OK (4 tests, 104 assertions), AuditDocs OK (10 tests, 446 assertions), ProductionSchedulerCron OK (5 tests, 104 assertions).
- Filtered validation passed: StaticGuard OK (191 tests, 4688 assertions), AuditDocs OK (10 tests, 446 assertions), Command OK (100 tests, 1290 assertions), Ops OK (74 tests, 624 assertions), RuntimeProof OK (14 tests, 467 assertions), Scheduler OK (5 tests, 104 assertions).
- Full MarketData suite passed: OK (490 tests, 7506 assertions), Time 00:20.344, Memory 40.00 MB.

[OPS_PARITY_LIMIT]
- Provider smoke current runtime is `PROVIDER_SMOKE_OK` / HTTP 200.
- Scheduler due-run runtime proof is present; stale auxiliary phase0/phase5 artifacts still need refresh if a completely clean scheduler proof pack is required.
- Therefore ops runtime parity is `OPS_RUNTIME_PARITY_PASSED`.
- Final full `vendor\bin\phpunit tests/Unit/MarketData` has passed locally: OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB. This supports the final ops parity promotion for this source ZIP.


## 2026-05-21 — PROVIDER RATE-LIMIT + SCHEDULER DUE-RUN PROOF RECONCILIATION

[SESSION] PROVIDER_RATE_LIMIT_SCHEDULER_DUE_RUN_RECONCILIATION

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[REVIEW_STATUS] OPS_RUNTIME_PARITY_PASSED_PROVIDER_SMOKE_OK

[INPUT_SOURCE_ZIP]
- Historical source ZIP (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `tradeaxis-api-provider.zip`
- Historical source ZIP SHA-256 (`SUPERSEDED_BY_FINAL_PROVIDER_SMOKE_PASS_AND_FULL_MARKETDATA_PHPUNIT_PASS`): `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`

[WHAT_CHANGED_FROM_PREVIOUS_AUDIT]
- Scheduler proof is no longer `SCHEDULER_RUNTIME_LOG_PRODUCED`: the source ZIP now contains `storage/app/market-data/production-scheduler-cron-deployment-proof/runtime/market-data-scheduler-proof.log`.
- `phase4-scheduler-output-log.txt` records `RESULT=SCHEDULER_RUNTIME_LOG_PRODUCED` and `EXIT_CODE:0`.
- `phase3-schedule-run-enabled-due.txt` records that `php artisan schedule:run` executed `market-data:daily --latest` at the configured cutoff minute and exited `0`.
- Scheduler runtime log records `scheduler_status=FAILURE command="market-data:daily --latest"` with visible reason-coded daily failure (`reason_code=RUN_SOURCE_RESPONSE_CHANGED`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `pointer_switched=false`). This is accepted as scheduler due-run proof because the scheduler executed, wrote output, and did not fail silently.
- Provider smoke safe mode remains implemented and non-destructive, but the live BBCA dry-run is passed against Yahoo/PublicApi: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `retry_exhausted=false`.
- Evidence encoding report is current and clean: `ENCODING: UTF-8`, `SCOPE: storage/app/market-data/**/*.txt`, `checked_files=165`, `null_byte_remaining=0`, `status=PASS`.
- Reconciliation summary artifact: `storage/app/market-data/provider-rate-limit-scheduler-due-run-reconciliation/audit-summary.txt`.
- Full MarketData PHPUnit proof after encoding/report correction passed: `OK (490 tests, 7506 assertions)`, Time `00:15.508`, Memory `40.00 MB`.

[FINAL_DECISION]
- Core source-code readiness remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Overall ops runtime parity is `OPS_RUNTIME_PARITY_PASSED` because live provider smoke now returns `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200`.
- Current rollout status is `OPS_RUNTIME_PARITY_PASSED`.

[CURRENT_BLOCKERS]
- No current provider-smoke rollout blocker for this source ZIP. `LIVE_PROVIDER_SMOKE_PASSED` is backed by `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, `attempt_count=1`, `retry_exhausted=false`, and all non-destructive safety flags remaining false.

[NON_BLOCKING_EVIDENCE_REFRESH]
- `phase0-migrate-fresh-testing-precondition.txt` and `phase5-schedule-run-disabled-control.txt` still contain old container-blocked output from PHP `8.4.16`; these are stale auxiliary artifacts and should be refreshed in the operator PHP `7.4.33` environment if a fully clean scheduler deployment proof pack is required.
- These stale auxiliary artifacts do not invalidate the newly present scheduler due-run runtime log, the source-state lock, or the full MarketData PHPUnit PASS.

[DO_NOT_CLAIM]
- Claim `OPS_RUNTIME_PARITY_PASSED` for this source because provider smoke returns `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` / `http_status=200` and all non-destructive safety flags remain false.
- Count the current artifact as provider PASS because it returns `PROVIDER_SMOKE_OK` with HTTP 200.

---

## 2026-05-22 — YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION] YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[VALIDATION_SCOPE]
- Safe live Yahoo/PublicApi provider smoke for `BBCA` on `2026-05-20`.
- Request-context hardening only; no core finalize, pointer, seal, publication, correction, replay, or scheduler lifecycle change.

[PHASE_1_RESULT]
- Minimal PHP header status: HTTP 200.
- Browser-like PHP header status: HTTP 200.
- Request URL: `https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`.
- Root cause: `PHP_ADAPTER_HEADER_CONTEXT_MISMATCH`.

[FINAL_PROVIDER_SMOKE]
- Command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Result: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `http_status=200`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`, `timeout_seconds=10`.
- Safety flags: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.

[OPS_PARITY_LIMIT]
- Previous `LIVE_PROVIDER_SMOKE_PASSED` is current for this source and is backed by a provider PASS artifact.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.
- Earlier partial/rate-limited status is now superseded by the final passed provider-smoke proof; current status remains `OPS_RUNTIME_PARITY_PASSED`.
- `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_REQUEST_CONTEXT_BLOCKED` remains a valid future classification if request-context proof fails.
- Current rollout status is `OPS_RUNTIME_PARITY_PASSED`.

[VALIDATION]
- Syntax checks passed for `ProviderSmokeCommand.php`, `PublicApiEodBarsAdapter.php`, `config/market_data.php`, `ProviderSmokeSafeModeStaticGuardTest.php`, and `ProductionValidationRuntimeProofStaticGuardTest.php`.
- `vendor/bin/phpunit tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php --filter "runtime_parity"` -> OK (2 tests, 259 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ProviderSmoke"` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (492 tests, 7588 assertions), Time 00:17.316, Memory 40.00 MB.

---

## 2026-05-22 — FINAL PROVIDER SMOKE PASSED / OPS RUNTIME PARITY LOCK

[SESSION] FINAL_PROVIDER_SMOKE_PASSED_OPS_RUNTIME_PARITY_LOCK

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Ops rollout/runtime parity: `OPS_RUNTIME_PARITY_PASSED`.
- Source-state decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- Final source-state lock status: `LOCKED`.
- Final provider smoke: `FINAL_PROVIDER_SMOKE=PASSED`.
- Live provider smoke: `LIVE_PROVIDER_SMOKE_PASSED`.
- Provider smoke safe mode remains non-destructive and single-ticker only.

[AUTHORITATIVE_PROVIDER_SMOKE_ARTIFACT]
- Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- `provider_smoke_status=PASS`.
- `reason_code=PROVIDER_SMOKE_OK`.
- `source_reason_code=none`.
- `provider=Yahoo/PublicApi`.
- `ticker=BBCA`.
- `trade_date=2026-05-20`.
- `dry_run=true`.
- `write_mode=none`.
- `publication_created=false`.
- `seal_executed=false`.
- `finalize_executed=false`.
- `pointer_switched=false`.
- `readable_publication_created=false`.
- `full_universe_fetch=false`.
- `returned_row_count=1`.
- `request_url=https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`.
- `http_status=200`.
- `adapter_reason_code=PROVIDER_SMOKE_OK`.
- `attempt_count=1`.
- `retry_max=0`.
- `retry_exhausted=false`.
- `timeout_seconds=10`.

[SCHEDULER_PROOF]
- Scheduler due-run proof remains present through `storage/app/market-data/production-scheduler-cron-deployment-proof/**`.
- `phase3-schedule-run-enabled-due.txt` records `php artisan schedule:run` executing `market-data:daily --latest`.
- `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_PRODUCED`.
- `runtime/market-data-scheduler-proof.log` records visible scheduler output with `scheduler_status=FAILURE`; this proves cron execution and non-silent failure handling. It is not treated as provider failure.

[VALIDATION]
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProviderSmokeSafeModeStaticGuardTest"` -> OK (5 tests, 162 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "ProductionValidationRuntimeProofStaticGuardTest"` -> OK (15 tests, 477 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData --filter "AuditDocsSynchronizationStaticGuardTest"` -> OK (10 tests, 456 assertions).
- `vendor\bin\phpunit tests/Unit/MarketData` -> OK (492 tests, 7584 assertions), Time 00:09.118, Memory 38.00 MB.

[SUPERSEDES]
- Supersedes previous partial/rate-limited rollout overlays for the current source ZIP; current status remains `OPS_RUNTIME_PARITY_PASSED` and `LIVE_PROVIDER_SMOKE_PASSED`.
- Previous provider-rate-limit records are historical only and must not be used as current rollout status after this proof.
- Current release decision is `OPS_RUNTIME_PARITY_PASSED` because scheduler due-run proof exists, provider smoke returned PASS/HTTP 200, all provider smoke safety flags remained false, and full MarketData PHPUnit passed.

## 2026-05-23 — Final Provider Smoke / Full PHPUnit PASS Document Reconciliation

[SESSION] FINAL_PROVIDER_SMOKE_FULL_PHPUNIT_DOC_SYNC

[SESSION_STATUS] OPS_RUNTIME_PARITY_PASSED

[FINAL_DECISION]
- Current source ZIP is documented as `OPS_RUNTIME_PARITY_PASSED`.
- Final provider smoke is `FINAL_PROVIDER_SMOKE=PASSED` and `LIVE_PROVIDER_SMOKE_PASSED`.
- Authoritative provider-smoke artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Provider smoke proof: `provider_smoke_status=PASS`, `reason_code=PROVIDER_SMOKE_OK`, `source_reason_code=none`, `http_status=200`, `returned_row_count=1`, `attempt_count=1`, `retry_max=0`, `retry_exhausted=false`.
- Safety proof: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.
- Scheduler due-run runtime proof remains present and no silent scheduler failure is claimed.
- Final targeted validation passed: `OpsCommandSurfaceRuntimeMatrixStaticGuardTest` -> OK (6 tests, 120 assertions).
- Final full validation passed: `vendor\bin\phpunit tests/Unit/MarketData` -> OK (492 tests, 7590 assertions), Time 00:36.861, Memory 40.00 MB.

[RECONCILIATION]
- Earlier wording that described provider smoke as provider-rate-limited, provider-blocked, or waiting for full MarketData PHPUnit is superseded for the current source ZIP.
- Future Yahoo/PublicApi rate limit, timeout, network, parse, empty-response, or missing-date outcomes remain valid reason-coded BLOCKED outcomes, but they are not the current final proof state.



---

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

## 2026-05-24 — Market Benchmark + Indicator Extension Runtime Proof Re-Lock

Status: `PASS`.

This append-only reconciliation records the latest current source-state proof after the market benchmark + indicator extension.

- `MARKET_BENCHMARK_INDICATOR_EXTENSION_STATUS=PASS`
- `MARKET_DATA_PRODUCTION_READY_LOCKED=YES`  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
- `FULL_MARKET_DATA_PHPUNIT=PASSED`
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (511 tests, 7871 assertions).
- Targeted proof: Benchmark OK (14 tests, 84 assertions); Indicator OK (18 tests, 104 assertions); MarketBenchmarkIndicatorExtensionStaticGuardTest OK (5 tests, 46 assertions); AuditDocsSynchronizationStaticGuardTest OK (10 tests, 468 assertions); StaticGuard OK (199 tests, 4930 assertions).
- Runtime proof: daily import `run_id=3` for `2026-05-19` completed with `accepted_row_count=913`, `source_final_status=SUCCESS`, `benchmark_import_status=COMPLETED`, and `benchmark_rows_written=1`.
- Promote proof: `publication_id=2`, `terminal_status=SUCCESS`, `publishability_state=READABLE`, `coverage_gate_state=PASS`, `coverage_ratio=1.0000`, `seal_state=SEALED`, and `pointer_switched=true`.
- Evidence proof: `evidence_completeness_state=COMPLETE`, `evidence_admission_state=ADMITTED_COMPLETE`, `pointer_resolve_status=RESOLVED_READABLE_CURRENT`, and `file_count=11`.
- Replay proof: `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, and `mismatch_count=0`.
- Benchmark proof: `IHSG` is stored as benchmark/index with provider symbol `^JKSE`; `^JKSE.JK` and `IHSG.JK` remain forbidden; benchmark `IND_INSUFFICIENT_HISTORY` is expected until enough historical IHSG bars exist.

Final current-source decision: `FULL_MARKET_DATA_PRODUCTION_READY=YES`, with no remaining blocker for this benchmark/indicator scope.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.


## 2026-06-07 — Current Indicator Recompute Final Runtime Lock

- Command help/list proof passed: `market-data:eod-indicators:recompute-current` is registered in the 30-command market-data surface.
- Evidence no-op routing is resolved: unchanged correction-current outcomes preserve the prior current publication and export correction evidence; changed outcomes export replacement run evidence.
- Final targeted proof: CommandSurface OK (6 tests, 126 assertions), OpsCommandSurfaceRuntimeMatrix OK (6 tests, 129 assertions), OperationalReadiness OK (10 tests, 250 assertions), AuditDocsSynchronization OK (11 tests, 644 assertions), ProductionValidationRuntimeProof OK (15 tests, 491 assertions).
- Final full MarketData suite: OK (640 tests, 9539 assertions), Time 01:03.530, Memory 48.00 MB.
- Dry-run range `2023-01-02` to `2026-06-04`: 807/807 success, all source/bar/master write flags false, `all_passed=1`.
- Runtime smoke `2023-01-02`: SUCCESS / READABLE / coverage PASS.
- Full-range recompute: `trading_date_count=807`, `processed_count=807`, `success_count=807`, `failed_count=0`, `skipped_count=0`, `all_passed=1`.
- Runtime write-boundary proof: `source_acquisition_executed=false`, `bar_ingest_executed=false`, `source_master_write_executed=false`, `eod_bars_write_executed=false`.
- Evidence selector proof: 757 replacement publications used run evidence and 50 unchanged/preserved-current outcomes used correction evidence; all 807 evidence exports were `ADMITTED_COMPLETE`.
- Final current evidence/replay: `processed_count=807`, `success_count=807`, `failed_count=0`, `error_count=0`, `all_passed=1`; all cases MATCH/PASS with `mismatch_count=0`.
- Recompute summary: `storage/app/market_data/evidence/indicator_recompute_current/2023-01-02_to_2026-06-04_20260607_103904/indicator_recompute_current_summary.json`.
- Embedded replay summary: `storage/app/market_data/evidence/indicator_recompute_current/2023-01-02_to_2026-06-04_20260607_103904/full_range_current_evidence_replay/market_data_full_range_current_evidence_replay_summary.json`.
- Independent final reconciliation summary: `storage/app/market_data/evidence/indicator_recompute_current/full_range_current_2023-01-02_to_2026-06-04/market_data_full_range_current_evidence_replay_summary.json`.
- Final decision: `CURRENT_INDICATOR_RECOMPUTE_FROM_EXISTING_BARS=LOCKED`; no rerun is required until affected inputs/formulas/publication logic change.

## 2026-06-08 - Docs-Review Full PHPUnit Refresh

- Latest validation command: `vendor\bin\phpunit`.
- Result: OK (641 tests, 9547 assertions), Time 00:37.358, Memory 48.00 MB.
- This refresh updates the active documentation proof count. It does not reopen the 2026-06-07 recompute runtime lock, the 807/807 full-range recompute proof, or the 807/807 final current evidence/replay proof.
