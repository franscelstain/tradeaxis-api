# Legacy Semantic Extract — LX-MD-0039-EVD-02

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `EVIDENCE`
- Source range: `L63-L239`
- Extract body SHA1: `3BC71BB715D4F538EC0BBE45CF26C4CE87C1729B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
