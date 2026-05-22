# Production Validation Inventory

Status: DONE.
Contract: PRODUCTION_VALIDATION_CONTRACT.
Active implementation: Production Validation / Manual + Runtime Proof.
Latest local runtime update: flow/run-evidence runtime PASS, replay persistence fix PASS, stale committed valid_case MISMATCH proof, generated runtime fixture MATCH proof, smoke `--generate_runtime_valid_case` all_passed=1, replay evidence export for `replay_id=5` PASS, failed/held coverage proof for `run_id=2` PASS_WITH_WARNING, correction request/approve/run proof PASS, correction evidence export for `correction_id=1` PASS, fresh historical 20-command command list/full help proof after adding `market-data:replay:fixture:generate`, and current 21-command command list/full help proof after adding `market-data:provider:smoke` were supplied and recorded through final reconciliation.
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
| Artisan command list | `php artisan list | findstr market-data` / `php artisan list market-data` | Current runtime shows 21 registered market-data commands including `market-data:provider:smoke`; the earlier 20-command fixture-generator proof remains historical. | None for command discovery. | RUNTIME_PROOF_PASS | Keep rerunning after command registration changes. |
| Command help | Help output for fixture generate, replay smoke/verify, evidence export, daily, promote, finalize, and correction request/approve/run | Operator-local PASS after fix7: `replay:fixture:generate --help` shows `run_id`, `--case`, `--output_dir`; `replay:smoke --help` shows `--generate_runtime_valid_case`; `replay:verify`, `evidence:export`, `daily`, `promote`, `run:finalize`, `correction:request`, `correction:approve`, and `correction:run` all display usage/options with no fatal error. | None for required command-help proof. | RUNTIME_PROOF_PASS | Keep rerunning after command signature/help changes. |
| Daily/import-only flow | `php artisan market-data:daily --requested_date=2026-02-18 --source_mode=manual_file --input_file=storage/app/market_data/operator/manual-full-2026-02-18.csv --output_dir=storage/app/market-data/runs` | Operator-local PASS: `run_id=1`, `request_mode=import_only`, `import_status=COMPLETED`, `promote_status=NOT_PROMOTED`, `promoted=false`, `pointer_switched=false`, `seal_state=UNSEALED`, `is_current_publication=0`, accepted 901 rows. | None for import-only boundary. | RUNTIME_PROOF_PASS | Keep rerunning after import/manual-file changes. |
| Promote/finalize flow | `php artisan market-data:promote --requested_date=2026-02-18 --source_mode=manual_file --run_id=1 --output_dir=storage/app/market-data/runs`; `php artisan market-data:run:finalize --requested_date=2026-02-18 --source_mode=manual_file --run_id=1` | Operator-local PASS: `SUCCESS`, `READABLE`, `PROMOTED`, `pointer_switched=true`, `is_current_publication=1`, `seal_state=SEALED`, `coverage_gate_state=PASS`, `coverage_reason_code=COVERAGE_THRESHOLD_MET`, `available=901/901`, ratio `1.0000`, threshold `0.9800`; finalize rerun preserved state. | None for success flow. | RUNTIME_PROOF_PASS | Keep rerunning after finalize/pointer/seal changes. |
| Evidence export | `php artisan market-data:evidence:export --run_id=1 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --run_id=2 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --replay_id=5 --trade_date=2026-02-18 --output_dir=storage/app/market-data/evidence`; `php artisan market-data:evidence:export --correction_id=1 --output_dir=storage/app/market-data/evidence` | Operator-local run evidence PASS: selector `run`, `selector_id=1`, terminal `SUCCESS`, publishability `READABLE`, coverage `PASS`, final reason `COVERAGE_THRESHOLD_MET`, completeness `COMPLETE`, publication `1`, pointer status `RESOLVED_READABLE_CURRENT`, `fallback_used=0`, `file_count=9`. Operator-local held-run evidence PASS_WITH_WARNING: selector `run`, `selector_id=2`, terminal `HELD`, publishability `NOT_READABLE`, coverage `FAIL`, final reason `RUN_PARTIAL_DATA`, completeness `INCOMPLETE`, pointer status `MISSING`, `fallback_used=1`, `file_count=8`, warning `EVIDENCE_INCOMPLETE`. Operator-local replay evidence PASS: selector=replay, `selector_id=5`, `comparison_result=MATCH`, `status=SUCCESS`, `file_count=5`. Operator-local correction evidence PASS: selector `correction`, `selector_id=1`, `status=PUBLISHED`, `changed_decision=CHANGED`, `reseal_status=RESEALED`, `publication_switch=1`, `file_count=1`, file `correction_evidence.json`. | Evidence proof is complete for success run, held run, replay MATCH, and published correction; held run remains intentionally incomplete because it is not readable/sealed. | RUNTIME_PROOF_PASS | Keep exporting evidence after future run/replay/correction changes. |
| Replay verify/smoke | `php artisan market-data:replay:smoke 1 --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:verify 1 storage/app/market_data/replay-fixtures/* --output_dir=storage/app/market-data/replay`; `php artisan market-data:replay:fixture:generate 1 --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-1`; `php artisan market-data:replay:smoke 1 --generate_runtime_valid_case --output_dir=storage/app/market-data/replay` | Operator-local replay PASS after fix4/fix5: stale committed `valid_case` cleanly returns `MISMATCH` when expected data differs; generated runtime valid fixture returns `comparison_result=MATCH`, `mismatch_count=0`, `replay_id=5`; smoke with generated valid case returns `all_passed=1`, generated valid `MATCH/pass`, reason-code mismatch `MISMATCH/pass`, broken manifest `ERROR/pass`, missing file `ERROR/pass`; replay evidence export for `replay_id=5` returns `status=SUCCESS`, `comparison_result=MATCH`, and 5 evidence files. | Replay core proof and replay evidence export proof complete. | RUNTIME_PROOF_PASS | Keep rerunning after replay fixture/evidence changes. |
| Correction lifecycle | correction request/approve/run plus evidence proof | Operator-local correction proof PASS: request produced `correction_id=1` and status `REQUESTED`; premature run was blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`; approve produced status `APPROVED`; run produced `run_id=3`, `request_mode=correction`, `SUCCESS`, `READABLE`, `PUBLISHED`, `RESEALED`, `baseline_publication_id=1`, `candidate_publication_id=3`, `candidate_publication_switch=true`, and new current publication `3`; evidence export produced `correction_evidence.json`. | None for request/approve/run/evidence lifecycle. | RUNTIME_PROOF_PASS | Keep correction lifecycle output current after correction command changes. |
| Backfill/session snapshot | backfill, replay backfill, session snapshot capture/purge | Prior operational docs cover commands; no fresh runtime output supplied. | Need output proving no readable shortcut and pointer-resolved snapshot behavior. | PENDING_RUNTIME_EVIDENCE | Run commands when market calendar/readable fixture is available. |
| Failure scenarios | coverage below threshold, replay mismatch, broken/missing fixture, evidence incomplete warning, and correction status guard | Operator-local runtime proof covers coverage below threshold (`run_id=2`, `HELD`, `NOT_READABLE`, `COVERAGE_BELOW_THRESHOLD`, `RUN_PARTIAL_DATA`, `pointer_switched=false`), held evidence warning (`EVIDENCE_INCOMPLETE`), replay mismatch, broken manifest, missing file, and correction not-executable status guard. | None for required production-validation failure scope; provider/source failure, invalid manual file, pointer/seal/no-readable/session snapshot remain optional broader regression scenarios. | RUNTIME_PROOF_PASS | Run broader failure cases only if expanding production validation beyond current minimum. |
| Audit docs | LUMEN_IMPLEMENTATION_STATUS and LUMEN_CONTRACT_TRACKER updated append-only with Production Validation | Updated with current local ProductionValidation/full-suite PASS evidence, flow/evidence/replay/failure/correction runtime proof, and fresh 21 registered market-data commands command-list/full-help proof. | None for production-validation audit sync. | RUNTIME_PROOF_PASS | Keep all new runtime output recorded append-only. |
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
| `php artisan market-data:provider:smoke --help` | Usage/options visible | safe single-ticker provider smoke options visible, including `--ticker`, `--trade_date`, `--dry-run`, `--max-tickers`, `--timeout`, `--provider`, and `--json` | RUNTIME_PROOF_PASS |
| `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=YYYY-MM-DD --dry-run` | `provider_smoke_status=PASS` only when valid data is returned | Current runtime is `provider_smoke_status=BLOCKED`, `reason_code=PROVIDER_RATE_LIMITED`, `publication_created=false`, `pointer_switched=false`, `full_universe_fetch=false`; this is not provider PASS | BLOCKED_PROVIDER_RATE_LIMITED |

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

- `php artisan list | findstr market-data` listed 20 market-data commands after fixture generator, including `market-data:replay:fixture:generate`; final reconciliation supersedes the current count to 21 commands including `market-data:provider:smoke`.
- Help output PASS for `market-data:replay:fixture:generate`, `market-data:replay:smoke`, `market-data:replay:verify`, `market-data:evidence:export`, `market-data:daily`, `market-data:promote`, `market-data:run:finalize`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`.
- Required command list/full-help proof is recorded; optional broader command-help reruns remain governance-only for future command changes.

Production Validation is DONE because fresh command-list/full-help output after adding `market-data:replay:fixture:generate` is recorded and current final reconciliation updates the public command surface to 21 after `market-data:provider:smoke`. Success flow, run evidence export, replay persistence, generated MATCH replay verify, generated smoke `all_passed=1`, replay evidence export for `replay_id=5`, failed/held coverage proof, held-run evidence warning proof, correction lifecycle, correction guard, correction evidence export, and 21 registered market-data commands command/help proof and exact 21-command command list/full help proof are now proven by runtime output. Provider smoke runtime remains `PROVIDER_RATE_LIMITED`, which is BLOCKED and not PASS.

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

`PRODUCTION_VALIDATION_CONTRACT` is LOCKED. `Production Validation / Manual + Runtime Proof` is DONE. Operator-local proof exists for ProductionValidation guard/filter, related targeted PHPUnit filters, full MarketData PHPUnit, 21 registered market-data commands artisan discovery/help output, success flow, held/failure flow, run/replay/correction evidence, replay generated MATCH proof, replay smoke `all_passed=1`, and correction lifecycle proof.

## 2026-05-20 Ops Command Surface Runtime Matrix Update

This append-only update records a new command-runtime matrix on top of the historical production validation proof. It does not relock the aggregate market-data production-ready claim. The current ops-command scope is `ENFORCED`, not `DONE` or `LOCKED`, because fixture-limited state-changing cases remain blocked.

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

- Aggregate production proof pack: `MARKET_DATA_PRODUCTION_READY_LOCKED / LOCKED`.
- `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT`: `LOCKED`.
- `LUMEN_IMPLEMENTATION_STATUS.md`: current working implementation `Full Market-Data Production Readiness Proof Pack -> DONE` with `[REVIEW_STATUS] MARKET_DATA_PRODUCTION_READY_LOCKED`.
- `LUMEN_CONTRACT_TRACKER.md`: current working contract `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT -> LOCKED`.
- Earlier `PENDING_RUNTIME_EVIDENCE`, `PARTIAL_RUNTIME_PROOF`, and `BLOCKED_RUNTIME_FIXTURE_UNAVAILABLE` rows in this inventory are retained as historical transition records only; they are superseded for the current source state by the ops matrix production-ready artifact root and final production proof pack.

Final validation basis consumed:

- `docs/market_data/audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`.
- `docs/market_data/audit/FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md`.
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

Status: `BLOCKED_BY_ENVIRONMENT` for full rollout parity. Source-state `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.

Evidence root: `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

| Validation area | Result | Evidence summary | Status |
|---|---|---|---|
| PHP/runtime baseline | PHP 7.4.33, required extensions present | `php -v`, `php -m` | PASS |
| Composer | Composer 2.8.4; `composer validate` valid | `composer --version`, `composer validate` | PASS |
| Artisan boot | Lumen 8.3.4, no warning/deprecation/noise | `php artisan list`, `php artisan --version` | PASS |
| Command registry/help | 21 market-data commands registered; requested help commands exit 0 | help outputs under command-output root plus provider-smoke safe-mode artifact | PASS for command surface; provider runtime BLOCKED by rate limit |
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
- `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`: live provider smoke requires a safe dry-run/limited ticker path or an isolated staging DB/provider plan.

Final rollout decision for this session:

- `OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT`.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for the locked source state.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).


## 2026-05-21 Testing DB Isolation / Safe Migration Guard

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Status: `DONE` for testing DB isolation. Overall rollout status remains `OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT` until scheduler/cron deployment proof and safe live provider smoke are completed.

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
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.
- At this DB-isolation closure point, full production rollout parity still had `OPS_DEPLOYMENT_TASK_REQUIRED` and `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`; scheduler status is superseded by the Production Scheduler / Cron Deployment Proof section below.

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

Status: `REVIEW_REQUIRED` / `SCHEDULER_RUNTIME_ARTIFACTS_MISSING_FROM_SOURCE_ZIP` because the source ZIP does not contain the runtime artifacts required to accept the scheduler proof claim. Overall rollout status remains `OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT`.

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

- `OPS_DEPLOYMENT_TASK_REQUIRED` remains open until scheduler runtime command-output/log artifacts are supplied in the source ZIP or the proof is rerun and archived.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid.
- Full production rollout parity remains blocked by `SCHEDULER_RUNTIME_ARTIFACTS_MISSING_FROM_SOURCE_ZIP` and `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`.

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

Status: `REVIEW_REQUIRED` / `SCHEDULER_RUNTIME_ARTIFACTS_MISSING_FROM_SOURCE_ZIP`.

The previous scheduler section named runtime artifacts under `storage/app/market-data/production-scheduler-cron-deployment-proof/**`, but those command-output/log files are not present in the source ZIP. The scheduler code and static guard hardening remain useful, but the runtime proof claim cannot be accepted as artifact-backed until those files are supplied or rerun.

Reconciliation artifacts now present:

- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/artifact-presence-audit.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/negative-db-override-proof-gap.txt`.
- `storage/app/market-data/production-scheduler-cron-deployment-proof/reconciliation/provider-smoke-gap.txt`.

Open rollout blockers:

- `SCHEDULER_RUNTIME_ARTIFACTS_MISSING_FROM_SOURCE_ZIP`.
- `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`.

## 2026-05-21 Runtime Parity Evidence Encoding Cleanup

Status: `DONE`.

The legacy command-output files under `storage/app/market-data/production-rollout-validation-runtime-parity/command-output/**` were normalized to UTF-8 plain text to remove null-byte / UTF-16-like evidence noise that could break grep/CI parsing.

Evidence artifact:

- `storage/app/market-data/production-rollout-validation-runtime-parity/command-output/encoding-normalization-report.txt`.

This cleanup does not change market-data runtime behavior or convert missing scheduler proof into a PASS. Scheduler proof remains `REVIEW_REQUIRED` until the missing scheduler artifacts are supplied or regenerated.

Global evidence encoding cleanup artifact:

- `storage/app/market-data/evidence-encoding-normalization-report.txt`.

This global report confirms all `storage/app/market-data/**/*.txt` evidence files were normalized to UTF-8 plain text with no null-byte residue.

---

## 2026-05-21 — OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION] OPS_RUNTIME_PARITY_COMPLETION_SCHEDULER_PROVIDER_SMOKE

[SESSION_STATUS] OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT

[FINAL_DECISION]
- `OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT` remains the only valid overall ops runtime parity status for this source ZIP.
- `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for core market-data source logic.
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
- Runtime attempt artifact records `provider_smoke_status=BLOCKED`, `reason_code=PROVIDER_SMOKE_BLOCKED_BY_LOCAL_RUNTIME_ENVIRONMENT`, `publication_created=false`, and `pointer_switched=false`.

[SCHEDULER_ARTIFACT_STATUS]
- Scheduler config surface artifact was written, but the actual `schedule:run` enabled/disabled commands are `BLOCKED_CONTAINER_RUNTIME_ENV` because PHP 8.4.16 is intentionally rejected before Laravel boot.
- `phase4-scheduler-output-log.txt` records `SCHEDULER_RUNTIME_LOG_NOT_PRODUCED` with `REASON_CODE=BLOCKED_CONTAINER_RUNTIME_ENV`.
- Scheduler proof remains `REVIEW_REQUIRED`; do not mark `SCHEDULER_CRON_DEPLOYMENT_PROOF_PASSED`.

[NEGATIVE_DB_OVERRIDE_PROOF]
- `APP_ENV=testing DB_DATABASE=tradeaxis php artisan migrate:fresh --env=testing` was executed in this container.
- Result: `BLOCKED_TESTING_DATABASE_ENV`, `EXIT_CODE:3`.
- This is the only runtime command in this session that produced the expected safety result inside the container.

[LOCAL_RUNTIME_STATUS]
- Environment baseline: `BLOCKED_CONTAINER_RUNTIME_ENV` because `php artisan --version`, `php artisan list`, `schedule:run`, provider smoke, and PHPUnit are blocked by `ENV_UNSUPPORTED_PHP_VERSION` on PHP 8.4.16.
- Composer is unavailable in the container, so `composer --version` and `composer validate` are also blocked.
- PHPUnit targeted/full suite not executed; status remains `BLOCKED_CONTAINER_RUNTIME_ENV`, not PASS.

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
- Provider blocker: live provider smoke could not execute because artisan is blocked before command boot; this is not a provider network PASS or provider network BLOCKED proof.

[REMAINING_RISK]
- Rerun provider smoke and scheduler runtime proof on the documented operator baseline PHP `>=7.3` and `<8.4` before any `OPS_RUNTIME_PARITY_PASSED` claim.
- Previous historical `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT` is superseded at source-surface level by the new command, but runtime provider proof remains blocked by local runtime environment.

---

## 2026-05-21 Final Proof Pack / Ops Runtime Parity Reconciliation

[SOURCE_ZIP]
- `D:\Laravel\tradeaxis-api\tradeaxis-api-provider.zip`
- SHA-256: `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`

[CURRENT_PRODUCTION_VALIDATION_SURFACE]
- Current public market-data command count: 21.
- New command surface member: `market-data:provider:smoke`.
- Required current command proof marker: `21-command command list/full help`.
- Source-state validation remains `MARKET_DATA_PRODUCTION_READY_LOCKED` because full `vendor/bin/phpunit tests/Unit/MarketData` passed after this reconciliation.

[VALIDATION]
- Targeted guards passed: ProductionValidation OK (14 tests, 467 assertions), CommandSurfaceSafety OK (5 tests, 91 assertions), OpsCommandSurfaceRuntimeMatrix OK (6 tests, 120 assertions), ProviderSmokeSafeMode OK (4 tests, 104 assertions), AuditDocs OK (10 tests, 446 assertions), ProductionSchedulerCron OK (5 tests, 104 assertions).
- Filtered validation passed: StaticGuard OK (191 tests, 4688 assertions), AuditDocs OK (10 tests, 446 assertions), Command OK (100 tests, 1290 assertions), Ops OK (74 tests, 624 assertions), RuntimeProof OK (14 tests, 467 assertions), Scheduler OK (5 tests, 104 assertions).
- Full MarketData suite passed: OK (490 tests, 7506 assertions), Time 00:20.344, Memory 40.00 MB.

[OPS_PARITY_LIMIT]
- Provider smoke current runtime is `BLOCKED_PROVIDER_RATE_LIMITED`.
- Scheduler due-run runtime proof is present; stale auxiliary phase0/phase5 artifacts still need refresh if a completely clean scheduler proof pack is required.
- Therefore ops runtime parity is `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED`, not PASS.
- Full market-data PHPUnit suite must still pass locally after this patch before final ops parity can be promoted.


## 2026-05-21 — PROVIDER RATE-LIMIT + SCHEDULER DUE-RUN PROOF RECONCILIATION

[SESSION] PROVIDER_RATE_LIMIT_SCHEDULER_DUE_RUN_RECONCILIATION

[SESSION_STATUS] OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED

[REVIEW_STATUS] MARKET_DATA_SOURCE_READY_BUT_PROVIDER_RATE_LIMITED

[INPUT_SOURCE_ZIP]
- Source ZIP: `tradeaxis-api-provider.zip`
- Source ZIP SHA-256: `aea589eabb634eca4da6051c3e62dfb732e4b9fa563744d7c54246e215f6c333`

[WHAT_CHANGED_FROM_PREVIOUS_AUDIT]
- Scheduler proof is no longer `SCHEDULER_RUNTIME_ARTIFACTS_MISSING_FROM_SOURCE_ZIP`: the source ZIP now contains `storage/app/market-data/production-scheduler-cron-deployment-proof/runtime/market-data-scheduler-proof.log`.
- `phase4-scheduler-output-log.txt` records `RESULT=SCHEDULER_RUNTIME_LOG_PRODUCED` and `EXIT_CODE:0`.
- `phase3-schedule-run-enabled-due.txt` records that `php artisan schedule:run` executed `market-data:daily --latest` at the configured cutoff minute and exited `0`.
- Scheduler runtime log records `scheduler_status=FAILURE command="market-data:daily --latest"` with visible reason-coded daily failure (`reason_code=RUN_SOURCE_RESPONSE_CHANGED`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `pointer_switched=false`). This is accepted as scheduler due-run proof because the scheduler executed, wrote output, and did not fail silently.
- Provider smoke safe mode remains implemented and non-destructive, but the live BBCA dry-run is blocked by Yahoo/PublicApi rate limiting: `provider_smoke_status=BLOCKED`, `reason_code=PROVIDER_RATE_LIMITED`, `source_reason_code=RUN_SOURCE_RATE_LIMIT`, `retry_exhausted=true`.
- Evidence encoding report is current and clean: `ENCODING: UTF-8`, `SCOPE: storage/app/market-data/**/*.txt`, `checked_files=165`, `null_byte_remaining=0`, `status=PASS`.
- Reconciliation summary artifact: `storage/app/market-data/provider-rate-limit-scheduler-due-run-reconciliation/audit-summary.txt`.
- Full MarketData PHPUnit proof after encoding/report correction passed: `OK (490 tests, 7506 assertions)`, Time `00:15.508`, Memory `40.00 MB`.

[FINAL_DECISION]
- Core source-code readiness remains `MARKET_DATA_PRODUCTION_READY_LOCKED`.
- Overall ops runtime parity is **not** `OPS_RUNTIME_PARITY_PASSED` because live provider smoke is still provider-blocked.
- Current rollout status is `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED`.

[CURRENT_BLOCKERS]
- `LIVE_PROVIDER_SMOKE_BLOCKED_PROVIDER_RATE_LIMITED`: external Yahoo/PublicApi rate limiting prevents provider smoke PASS.

[NON_BLOCKING_EVIDENCE_REFRESH]
- `phase0-migrate-fresh-testing-precondition.txt` and `phase5-schedule-run-disabled-control.txt` still contain old container-blocked output from PHP `8.4.16`; these are stale auxiliary artifacts and should be refreshed in the operator PHP `7.4.33` environment if a fully clean scheduler deployment proof pack is required.
- These stale auxiliary artifacts do not invalidate the newly present scheduler due-run runtime log, the source-state lock, or the full MarketData PHPUnit PASS.

[DO_NOT_CLAIM]
- Do not claim `OPS_RUNTIME_PARITY_PASSED` until provider smoke returns `provider_smoke_status=PASS` / `reason_code=PROVIDER_SMOKE_OK` or the release decision explicitly accepts provider rate-limit as an external deployment blocker.
- Do not count `PROVIDER_RATE_LIMITED` as provider PASS.

---

## 2026-05-22 — YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION] YAHOO_PROVIDER_SMOKE_REQUEST_CONTEXT_HARDENING

[SESSION_STATUS] OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED

[VALIDATION_SCOPE]
- Safe live Yahoo/PublicApi provider smoke for `BBCA` on `2026-05-20`.
- Request-context hardening only; no core finalize, pointer, seal, publication, correction, replay, or scheduler lifecycle change.

[PHASE_1_RESULT]
- Minimal PHP header status: HTTP 429.
- Browser-like PHP header status: HTTP 200.
- Request URL: `https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK?interval=1d&range=10d&includePrePost=false&events=div%2Csplits&corsDomain=finance.yahoo.com`.
- Root cause: `PHP_ADAPTER_HEADER_CONTEXT_MISMATCH`.

[FINAL_PROVIDER_SMOKE]
- Command: `php artisan market-data:provider:smoke --ticker=BBCA --trade_date=2026-05-20 --dry-run --retry-max=0`.
- Artifact: `storage/app/market-data/provider-smoke-safe-mode/command-output/provider-smoke-bbca.txt`.
- Result: `provider_smoke_status=BLOCKED`, `reason_code=PROVIDER_RATE_LIMITED`, `http_status=429`, `attempt_count=4`, `retry_max=3`, `retry_exhausted=true`, `timeout_seconds=10`.
- Safety flags: `publication_created=false`, `seal_executed=false`, `finalize_executed=false`, `pointer_switched=false`, `readable_publication_created=false`, `full_universe_fetch=false`.

[OPS_PARITY_LIMIT]
- Previous `LIVE_PROVIDER_SMOKE_BLOCKED_PROVIDER_RATE_LIMITED` remains current for this source; it is not superseded without a provider PASS artifact.
- `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`.
- `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED` remains historical.
- `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_REQUEST_CONTEXT_BLOCKED` remains a valid future classification if request-context proof fails.
- Current rollout status is `OPS_RUNTIME_PARITY_PARTIAL_PROVIDER_RATE_LIMITED`.

[VALIDATION]
- Syntax checks passed for `ProviderSmokeCommand.php`, `PublicApiEodBarsAdapter.php`, `config/market_data.php`, `ProviderSmokeSafeModeStaticGuardTest.php`, and `ProductionValidationRuntimeProofStaticGuardTest.php`.
- `vendor/bin/phpunit tests/Unit/MarketData/ProviderSmokeSafeModeStaticGuardTest.php` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php --filter "runtime_parity"` -> OK (2 tests, 259 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "ProviderSmoke"` -> OK (5 tests, 163 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (492 tests, 7588 assertions), Time 00:17.316, Memory 40.00 MB.
