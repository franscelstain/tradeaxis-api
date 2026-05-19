# Production Validation Inventory

Status: DONE.
Contract: PRODUCTION_VALIDATION_CONTRACT.
Active implementation: Production Validation / Manual + Runtime Proof.
Latest local runtime update: flow/run-evidence runtime PASS, replay persistence fix PASS, stale committed valid_case MISMATCH proof, generated runtime fixture MATCH proof, smoke `--generate_runtime_valid_case` all_passed=1, replay evidence export for `replay_id=5` PASS, failed/held coverage proof for `run_id=2` PASS_WITH_WARNING, correction request/approve/run proof PASS, correction evidence export for `correction_id=1` PASS, and fresh 20-command command list/full help proof after adding `market-data:replay:fixture:generate` PASS were supplied and recorded through fix8.
Runtime policy: static proof is support only. DONE requires runtime evidence. LOCKED requires targeted and full suite PASS plus artisan command, evidence output, and replay verification proof.

This inventory is the production validation control surface for market-data. It separates container/static proof, operator-local runtime proof, and missing runtime proof. The uploaded ZIP has no `vendor/`, so container validation can only prove file presence, docs/test cross-checks, static scans, and `php -l` for changed PHP files. Operator-local PHPUnit and artisan proof can be recorded only when actual output is supplied. Flow execution and evidence export runtime proof have now been supplied and recorded. Replay verification was executed after the persistence and fixture-generation fixes. SQLSTATE[22001] is resolved, mismatch/error cases persist cleanly, generated runtime fixture verification returns MATCH with `mismatch_count=0`, replay smoke with `--generate_runtime_valid_case` returns `all_passed=1`, and replay evidence export for `replay_id=5` returns `status=SUCCESS`, `comparison_result=MATCH`, and 5 replay evidence files. Production Validation is now DONE and `PRODUCTION_VALIDATION_CONTRACT` is LOCKED because targeted PHPUnit, full MarketData PHPUnit, artisan command discovery/help, success flow, held/failure flow, run/replay/correction evidence, replay generated MATCH proof, replay smoke `all_passed=1`, and correction lifecycle are all proven by operator-local runtime output.

Manual validation note: every manual validation runtime output and every fix generated from that output must be recorded in this inventory, `LUMEN_IMPLEMENTATION_STATUS.md`, and `LUMEN_CONTRACT_TRACKER.md`. The replay persistence fix specifically requires `mismatch_summary LONGTEXT NULL` in schema docs/migration coverage before replay proof can be retested.

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
- Operator-local replay after fix3 PASS/REVIEW_REQUIRED: `reason_code_mismatch_case` observed `MISMATCH` and passed, `broken_manifest_case` exposed `REPLAY_FIXTURE_SCHEMA_MISMATCH`, `missing_file_case` exposed `REPLAY_EXPECTED_PROOF_INCOMPLETE`, and stale committed `valid_case` cleanly observed `MISMATCH` instead of SQL truncation.
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
| Artisan command list | `php artisan list | findstr market-data` | Operator-local PASS after fix7: command list shows 20 registered market-data commands including `market-data:replay:fixture:generate`. | None for command discovery. | RUNTIME_PROOF_PASS | Keep rerunning after command registration changes. |
| Command help | Help output for fixture generate, replay smoke/verify, evidence export, daily, promote, finalize, and correction request/approve/run | Operator-local PASS after fix7: `replay:fixture:generate --help` shows `run_id`, `--case`, `--output_dir`; `replay:smoke --help` shows `--generate_runtime_valid_case`; `replay:verify`, `evidence:export`, `daily`, `promote`, `run:finalize`, `correction:request`, `correction:approve`, and `correction:run` all display usage/options with no fatal error. | None for required command-help proof. | RUNTIME_PROOF_PASS | Keep rerunning after command signature/help changes. |
| Daily/import-only flow | `php artisan market-data:daily --requested_date=2026-02-18 --source_mode=manual_file --input_file=storage/app/market_data/operator/manual-full-2026-02-18.csv --output_dir=storage/app/market-data/runs` | Operator-local PASS: `run_id=1`, `request_mode=import_only`, `import_status=COMPLETED`, `promote_status=NOT_PROMOTED`, `promoted=false`, `pointer_switched=false`, `seal_state=UNSEALED`, `is_current_publication=0`, accepted 901 rows. | None for import-only boundary. | RUNTIME_PROOF_PASS | Keep rerunning after import/manual-file changes. |
| Promote/finalize flow | `php artisan market-data:promote --requested_date=2026-02-18 --source_mode=manual_file --run_id=1 --output_dir=storage/app/market-data/runs`; `php artisan market-data:run:finalize --requested_date=2026-02-18 --source_mode=manual_file --run_id=1` | Operator-local PASS: `SUCCESS`, `READABLE`, `PROMOTED`, `pointer_switched=true`, `is_current_publication=1`, `seal_state=SEALED`, `coverage_gate_state=PASS`, `coverage_reason_code=COVERAGE_THRESHOLD_MET`, `available=901/901`, ratio `1.0000`, threshold `0.9800`; finalize rerun preserved state. | None for success flow. | RUNTIME_PROOF_PASS | Keep rerunning after finalize/pointer/seal changes. |
| Evidence export | `php artisan market-data:evidence:export --run_id=1 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --run_id=2 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --replay_id=5 --trade_date=2026-02-18 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --correction_id=1 --output_dir=storage/app/market-data/evidence` | Operator-local run evidence PASS: selector `run`, `selector_id=1`, terminal `SUCCESS`, publishability `READABLE`, coverage `PASS`, final reason `COVERAGE_THRESHOLD_MET`, completeness `COMPLETE`, publication `1`, pointer status `RESOLVED_READABLE_CURRENT`, `fallback_used=0`, `file_count=9`. Operator-local held-run evidence PASS_WITH_WARNING: selector `run`, `selector_id=2`, terminal `HELD`, publishability `NOT_READABLE`, coverage `FAIL`, final reason `RUN_PARTIAL_DATA`, completeness `INCOMPLETE`, pointer status `MISSING`, `fallback_used=1`, `file_count=8`, warning `EVIDENCE_INCOMPLETE`. Operator-local replay evidence PASS: selector=replay, `selector_id=5`, `comparison_result=MATCH`, `status=SUCCESS`, `file_count=5`. Operator-local correction evidence PASS: selector `correction`, `selector_id=1`, `status=PUBLISHED`, `changed_decision=CHANGED`, `reseal_status=RESEALED`, `publication_switch=1`, `file_count=1`, file `correction_evidence.json`. | Evidence proof is complete for success run, held run, replay MATCH, and published correction; held run remains intentionally incomplete because it is not readable/sealed. | RUNTIME_PROOF_PASS | Keep exporting evidence after future run/replay/correction changes. |
| Replay verify/smoke | `php artisan market-data:replay:smoke 1 --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:verify 1 storage/app/market_data/replay-fixtures/* --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1`; `php artisan market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay` | Operator-local replay PASS after fix4/fix5: stale committed `valid_case` cleanly returns `MISMATCH` when expected data differs; generated runtime valid fixture returns `comparison_result=MATCH`, `mismatch_count=0`, `replay_id=5`; smoke with generated valid case returns `all_passed=1`, generated valid `MATCH/pass`, reason-code mismatch `MISMATCH/pass`, broken manifest `ERROR/pass`, missing file `ERROR/pass`; replay evidence export for `replay_id=5` returns `status=SUCCESS`, `comparison_result=MATCH`, and 5 evidence files. | Replay core proof and replay evidence export proof complete. | RUNTIME_PROOF_PASS | Keep rerunning after replay fixture/evidence changes. |
| Correction lifecycle | correction request/approve/run plus evidence proof | Operator-local correction proof PASS: request produced `correction_id=1` and status `REQUESTED`; premature run was blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`; approve produced status `APPROVED`; run produced `run_id=3`, `request_mode=correction`, `SUCCESS`, `READABLE`, `PUBLISHED`, `RESEALED`, `baseline_publication_id=1`, `candidate_publication_id=3`, `candidate_publication_switch=true`, and new current publication `3`; evidence export produced `correction_evidence.json`. | None for request/approve/run/evidence lifecycle. | RUNTIME_PROOF_PASS | Keep correction lifecycle output current after correction command changes. |
| Backfill/session snapshot | backfill, replay backfill, session snapshot capture/purge | Prior operational docs cover commands; no fresh runtime output supplied. | Need output proving no readable shortcut and pointer-resolved snapshot behavior. | PENDING_RUNTIME_EVIDENCE | Run commands when market calendar/readable fixture is available. |
| Failure scenarios | coverage below threshold, replay mismatch, broken/missing fixture, evidence incomplete warning, and correction status guard | Operator-local runtime proof covers coverage below threshold (`run_id=2`, `HELD`, `NOT_READABLE`, `COVERAGE_BELOW_THRESHOLD`, `RUN_PARTIAL_DATA`, `pointer_switched=false`), held evidence warning (`EVIDENCE_INCOMPLETE`), replay mismatch, broken manifest, missing file, and correction not-executable status guard. | None for required production-validation failure scope; provider/source failure, invalid manual file, pointer/seal/no-readable/session snapshot remain optional broader regression scenarios. | RUNTIME_PROOF_PASS | Run broader failure cases only if expanding production validation beyond current minimum. |
| Audit docs | LUMEN_IMPLEMENTATION_STATUS and LUMEN_CONTRACT_TRACKER updated append-only with Production Validation | Updated with current local ProductionValidation/full-suite PASS evidence, flow/evidence/replay/failure/correction runtime proof, and fresh 20 registered market-data commands command-list/full-help proof. | None for production-validation audit sync. | RUNTIME_PROOF_PASS | Keep all new runtime output recorded append-only. |
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
| `php artisan market-data:correction:request --help` | Usage/options visible | Operator-local PASS: `--trade_date`, `--reason_code`, `--reason_note`, and `--requested_by` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:correction:approve --help` | Usage/options visible | Operator-local PASS: `correction_id` argument and `--approved_by` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:correction:run --help` | Usage/options visible | Operator-local PASS: `correction_id`, `--requested_date`, `--source_mode`, and `--latest` visible. | RUNTIME_PROOF_PASS |
| `php artisan market-data:eod-bars:ingest --help` | Usage/options visible | ingest input/source options visible | PENDING_RUNTIME_EVIDENCE |
| `php artisan market-data:eod-indicators:compute --help` | Usage/options visible | requested/effective date options visible | PENDING_RUNTIME_EVIDENCE |
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

- `php artisan list | findstr market-data` listed 20 market-data commands after fixture generator, including `market-data:replay:fixture:generate`.
- Help output PASS for `market-data:replay:fixture:generate`, `market-data:replay:smoke`, `market-data:replay:verify`, `market-data:evidence:export`, `market-data:daily`, `market-data:promote`, `market-data:run:finalize`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.
- Required command list/full-help proof is recorded; optional broader command-help reruns remain governance-only for future command changes.

Production Validation is DONE because fresh command-list/full-help output after adding `market-data:replay:fixture:generate` is now recorded. Success flow, run evidence export, replay persistence, generated MATCH replay verify, generated smoke `all_passed=1`, replay evidence export for `replay_id=5`, failed/held coverage proof, held-run evidence warning proof, correction lifecycle, correction guard, correction evidence export, and 20 registered market-data commands command/help proof and exact 20-command command list/full help proof are now proven by operator-local output.

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

`PRODUCTION_VALIDATION_CONTRACT` is LOCKED. `Production Validation / Manual + Runtime Proof` is DONE. Operator-local proof exists for ProductionValidation guard/filter, related targeted PHPUnit filters, full MarketData PHPUnit, 20 registered market-data commands artisan discovery/help output, success flow, held/failure flow, run/replay/correction evidence, replay generated MATCH proof, replay smoke `all_passed=1`, and correction lifecycle proof.


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
