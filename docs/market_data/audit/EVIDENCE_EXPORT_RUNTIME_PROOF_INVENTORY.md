# EVIDENCE EXPORT RUNTIME PROOF INVENTORY

[SESSION] Evidence Export Runtime Proof
[STATUS] LOCKED_LOCAL_RUNTIME_PROOF_RUN_SELECTOR
[LAST_UPDATED] 2026-05-19

## Scope

This inventory records the current evidence-export runtime proof hardening pass. The uploaded ZIP is the latest source of truth. This session traces the evidence export command/service/test/audit surface and patches the proof pack so selector-scoped exports have an explicit admission artifact in addition to completeness details.

This session does not claim all market-data production-ready. Evidence export run-selector readable-publication runtime proof is now locked because fresh operator-local runtime artifact proof and targeted/full PHPUnit PASS were supplied.

## Decision

| Decision Area | Final Rule | Status |
|---|---|---|
| selector rule | exactly one of `--run_id`, `--correction_id`, or `--replay_id` is required | ENFORCED |
| replay selector | `--replay_id` requires explicit `--trade_date`; latest-row lookup is forbidden | ENFORCED |
| run evidence admission | write `evidence_admission.json` plus `evidence_completeness.json` | PATCHED |
| correction evidence admission | write `evidence_admission.json` plus `correction_evidence.json` | PATCHED |
| replay evidence admission | write `evidence_admission.json` plus replay expected/actual/result/reason-code artifacts | PATCHED |
| silent missing metadata | forbidden; admission artifact exposes missing/critical sections | ENFORCED |
| current vs historical proof | current consumer result and historical audit evidence remain separated by evidence resolution mode and pointer context | PRESERVED |
| runtime proof status | operator-local run-selector runtime export produced COMPLETE/ADMITTED artifacts; container remains blocked and is historical/support context only | LOCKED_LOCAL_RUNTIME_PROOF |

## Pre-check trace

| File | Finding | Action |
|---|---|---|
| `docs/market_data/audit/AUDIT_UPDATE_GOVERNANCE.md` | DONE/LOCKED requires concrete runtime/operator proof and active-session synchronization | Followed; status kept ENFORCED |
| `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md` | Active session was Read-Side Consumer Surface Completion | Updated to Evidence Export Runtime Proof |
| `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md` | Current working contract was read-side pointer enforcement | Added current evidence-export runtime proof contract as ENFORCED |
| `app/Console/Commands/MarketData/ExportEvidenceCommand.php` | selector validation already enforced; warning only referenced completeness artifact | Warning updated to include admission and completeness artifacts |
| `app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | run evidence exported completeness but not explicit `evidence_admission.json`; correction/replay had no admission artifact | Added explicit selector-scoped admission artifacts |
| `tests/Unit/MarketData/*Evidence*` | tests expected old artifact counts and did not assert admission artifact | Updated expected files/counts and admission assertions |

## Evidence area matrix

| Evidence Area | Contract Required | Code Produces | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| `run_id` | required for run selector | `run_summary.json`, `evidence_pack.json`, `evidence_admission.json` | updated unit/static assertions and operator-local export | PASS_OPERATOR_LOCAL | none |
| `publication_id` | required when readable publication exists | publication context + manifest when available | existing run/historical tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| `correction_id` | required for correction selector | `correction_evidence.json`, `evidence_admission.json` | updated correction assertions | BLOCKED_CONTAINER_RUNTIME_ENV | operator runtime export still required |
| `replay_id` | required for replay selector | replay artifacts + `evidence_admission.json` | updated replay assertions | BLOCKED_CONTAINER_RUNTIME_ENV | operator runtime export still required |
| `trade_date` | required for replay selector | command blocks missing `--trade_date` | existing replay selector test | PASS_TARGETED_PHPUNIT | no replay runtime fixture supplied |
| terminal status | required | run/replay/correction context | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| publishability state | required | run/publication/replay contexts | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| coverage gate state | required | coverage context and replay expected/actual | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| coverage ratio/min/counts | required | coverage aliases include expected/available/missing | existing static/unit assertions and operator-local export | PASS_OPERATOR_LOCAL | none |
| source/provider summary | required | source context + source attempt telemetry | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| reason code/final outcome | required | run/correction/replay contexts | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| hash/seal metadata | required | artifact context, manifest, replay hash/seal contexts | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| current pointer metadata | required | pointer context | existing tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| historical publication metadata | required for audit selector | historical evidence resolver fields | existing historical lineage tests | BLOCKED_CONTAINER_RUNTIME_ENV | none in static trace |
| evidence admission/completeness | required | `evidence_admission.json` and `evidence_completeness.json` | updated tests/static guard and operator-local export | PASS_OPERATOR_LOCAL | none |

## Artifact list after patch

### Run selector

- `run_summary.json`
- `publication_manifest.json` if publication manifest exists
- `run_event_summary.json`
- `source_attempt_telemetry.json` if telemetry exists
- `eligibility_export.csv`
- `invalid_bars_export.csv`
- `anomaly_report.md`
- `lineage.json`
- `evidence_admission.json`
- `evidence_completeness.json`
- `evidence_pack.json`

### Correction selector

- `correction_evidence.json`
- `evidence_admission.json`

### Replay selector

- `replay_result.json`
- `replay_expected_state.json`
- `replay_actual_state.json`
- `replay_reason_code_counts.json`
- `evidence_admission.json`
- `replay_evidence_pack.json`

## Container validation

| Command | Result | Status |
|---|---|---|
| `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | No syntax errors detected | PASS_STATIC |
| `php -l app/Console/Commands/MarketData/ExportEvidenceCommand.php` | No syntax errors detected | PASS_STATIC |
| `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` | No syntax errors detected | PASS_STATIC |
| `php -l tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php` | No syntax errors detected | PASS_STATIC |
| `php -l tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php` | No syntax errors detected | PASS_STATIC |
| `php -l tests/Unit/MarketData/EvidenceExportCompletenessStaticGuardTest.php` | No syntax errors detected | PASS_STATIC |
| `php vendor/bin/phpunit tests/Unit/MarketData/EvidenceExportCompletenessStaticGuardTest.php` | blocked: missing `dom`, `mbstring`, `xml`, `xmlwriter` | BLOCKED_CONTAINER_RUNTIME_ENV |
| `php artisan market-data:evidence:export --help` | blocked/fail-closed: `ENV_UNSUPPORTED_PHP_VERSION` on PHP 8.4.16 | BLOCKED_CONTAINER_RUNTIME_ENV |


## Operator-local validation completed

| Command / proof | Result | Status |
|---|---|---|
| `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` | OK (5 tests, 129 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php` | OK (1 test, 20 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php` | OK (1 test, 47 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/EvidenceExportCompletenessStaticGuardTest.php` | OK (5 tests, 142 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php` | OK (5 tests, 51 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK (54 tests, 1021 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (169 tests, 3885 assertions) | PASS |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (449 tests, 6562 assertions) | PASS |
| `php artisan market-data:daily --requested_date=2026-02-18 --source_mode=manual_file --input_file=storage/app/market-data/operator/manual-full-2026-02-18.csv --output_dir=storage/app/market-data/evidence/runtime-proof-2026-02-18/daily-rerun` | `run_id=2`, `accepted_row_count=901`, `rejected_row_count=0`, `invalid_row_count=0`, `publication_id=2` | PASS |
| `php artisan market-data:promote --requested_date=2026-02-18 --source_mode=manual_file --run_id=2 --mode=full_publish --output_dir=storage/app/market-data/evidence/runtime-proof-2026-02-18/promote-rerun` | `terminal_status=SUCCESS`, `publishability_state=READABLE`, `promote_status=PROMOTED`, `promoted=true`, `pointer_switched=true`, `current_publication_id=2`, `coverage_gate_state=PASS`, `seal_state=SEALED` | PASS |
| `php artisan market-data:evidence:export --run_id=2 --output_dir=storage/app/market-data/evidence/runtime-proof-run-2` | `evidence_admission_state=ADMITTED_COMPLETE`, `evidence_completeness_state=COMPLETE`, `pointer_resolve_status=RESOLVED_READABLE_CURRENT`, `file_count=10` | PASS |

Generated runtime artifacts for `run_id=2`:

- `run_summary.json`
- `publication_manifest.json`
- `run_event_summary.json`
- `eligibility_export.csv`
- `invalid_bars_export.csv`
- `anomaly_report.md`
- `lineage.json`
- `evidence_admission.json`
- `evidence_completeness.json`
- `evidence_pack.json`

`evidence_admission.json` is `ADMITTED_COMPLETE`, has empty `missing_sections` and `critical_missing_sections`, sets `database_lookup_required_after_export=false`, `deterministic_export=true`, and `silent_missing_metadata_allowed=false`.

`publication_manifest.json` proves publication `2` is current, SEALED, tied to `run_id=2`, coverage PASS (`901/913`, ratio `0.986857`, threshold `0.980000`), and includes source/hash/seal metadata.

## Operator-local validation closure

The required run-selector operator-local validation is complete. Correction/replay runtime artifact commands remain optional unless fixtures are supplied; correction/replay selector admission behavior is covered by targeted PHPUnit.

- `vendor/bin/phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/EvidenceExportCompletenessStaticGuardTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"`
- `vendor/bin/phpunit tests/Unit/MarketData`
- `php artisan list market-data`
- `php artisan market-data:evidence:export`
- `php artisan market-data:evidence:export --run_id=<RUN_ID> --output_dir=storage/app/market-data/evidence/runtime-proof-run-<RUN_ID>`
- `php artisan market-data:evidence:export --correction_id=<CORRECTION_ID> --output_dir=storage/app/market-data/evidence/runtime-proof-correction-<CORRECTION_ID>`
- `php artisan market-data:evidence:export --replay_id=<REPLAY_ID> --trade_date=<YYYY-MM-DD> --output_dir=storage/app/market-data/evidence/runtime-proof-replay-<REPLAY_ID>`

## Remaining risk

- Container runtime artifact proof is not produced; this is historical/support context only.
- Evidence export run-selector readable-publication runtime proof is LOCKED_LOCAL_RUNTIME_PROOF_RUN_SELECTOR based on operator-local artifacts and tests.
- Correction/replay runtime artifact exports are not claimed because no correction/replay runtime fixtures were supplied; selector admission behavior is covered by targeted PHPUnit.
- This session does not close broader replay determinism runtime proof, ops runtime matrix, production proof pack, or final roadmap audit synchronization.
