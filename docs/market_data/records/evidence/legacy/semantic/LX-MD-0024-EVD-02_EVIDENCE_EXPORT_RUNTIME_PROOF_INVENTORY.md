# Legacy Semantic Extract — LX-MD-0024-EVD-02

- Source ID: `LS-MD-0024`
- Original path: `audit/EVIDENCE_EXPORT_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `CE951167381AE5231B705EE619EA1FECEEC18A9E`
- Extract role: `EVIDENCE`
- Source range: `L40-L154`
- Extract body SHA1: `3EED0BAFB9DB6516F9235A9B75898049207E18F0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Evidence area matrix

| Evidence Area | Contract Required | Code Produces | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| `run_id` | required for run selector | `run_summary.json`, `evidence_pack.json`, `evidence_admission.json` | updated unit/static assertions and operator-local export | PASS_OPERATOR_LOCAL | none |
| `publication_id` | required when readable publication exists | publication context + manifest when available | existing run/historical tests and operator-local export | PASS_OPERATOR_LOCAL | none |
| `correction_id` | required for correction selector | `correction_evidence.json`, `evidence_admission.json` | updated correction assertions plus unchanged-candidate regression | PATCHED_REEXPORT_REQUIRED | operator export exists for correction_id=1 but pre-patch candidate proof was false FAILED; rerun required |
| `replay_id` | required for replay selector | replay artifacts + `evidence_admission.json` | updated replay assertions | PASS_OPERATOR_LOCAL | replay_id=1 exported with ADMITTED_COMPLETE and MATCH |
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

The required run-selector operator-local validation is complete. Correction/replay selector admission behavior is covered by targeted PHPUnit, but correction/replay runtime artifact commands are required before the full evidence export runtime proof can be marked LOCKED.

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


<!-- LEGACY_EXTRACT_BODY_END -->
