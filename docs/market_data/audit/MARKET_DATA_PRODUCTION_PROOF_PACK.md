# Market-Data Production Proof Pack

Last updated: 2026-05-21
Source ZIP: `tradeaxis-api-production-rollout-validation-runtime-parity.zip`
Source ZIP SHA-256: `2685A6553FDBFA3516530BCD329FE306D78A77B356F73F2BE8336C109540FA02`
Decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`
Review status: `FINAL_AUDIT_DOCS_SYNCHRONIZED / OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT`
Final lock status: `LOCKED`

This proof pack is the aggregate validation record for the current uploaded source state. It consumes the already-recorded operator-local runtime proof embedded in this source ZIP and the artifact inspection performed against this ZIP. Final Audit Docs Synchronization has consumed this proof pack and locked the current source state as `MARKET_DATA_PRODUCTION_READY_LOCKED`. The 2026-05-21 rollout overlay keeps full production rollout parity blocked by remaining deployment/provider proof gaps while preserving the source-state lock.

## 1. Environment Baseline

| Item | Value |
|---|---|
| Audit date | 2026-05-20 |
| Sandbox PHP | PHP 8.4.16 |
| Sandbox PHPUnit | BLOCKED: missing `dom`, `mbstring`, `xml`, `xmlwriter` |
| Sandbox artisan | BLOCKED by fail-closed guard: `ENV_UNSUPPORTED_PHP_VERSION` |
| Supported operator proof PHP | PHP 7.4.33 |
| Supported operator proof PHPUnit | PHPUnit 9.6.34 |
| Required supported extensions | `dom`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `xml`, `xmlreader`, `xmlwriter` |
| Lumen context | Lumen 8.3.4 according to runtime command output |
| Source artifact root | `storage/app/market-data/**` |
| Runtime limitation | Current sandbox cannot be used as runtime evidence; use supported operator/CI baseline `>=7.3` and `<8.4` with required extensions. |

## 2. Production Validation Matrix

| Area | Contract | Code | Test | Runtime | Audit Ledger | Status | Blocker? |
|---|---|---|---|---|---|---|---|
| coverage policy | LOCKED | Enforced by coverage gate and candidate scope | Static/unit proof recorded | PASS/FAIL/NOT_EVALUABLE behavior recorded | Synced | PASS | no |
| DB schema | LOCKED | Migration + SQLite/schema mirror present | Schema/static proof recorded | Migration proof recorded operator-local | Synced | PASS | no |
| read-side | LOCKED | Pointer/current resolver contract enforced | Anti-bypass proof recorded | No-readable/fallback behavior recorded | Synced | PASS | no |
| evidence export | LOCKED | Run/correction/replay selectors implemented | Evidence filters recorded | `run_id=33`, `correction_id=3`, replay proof admitted | Synced | PASS | no |
| replay | LOCKED | Current + historical audit resolution implemented | Replay filters recorded | `replay_id=15`, smoke/backfill PASS, historical replay `replay_id=8` | Synced | PASS | no |
| correction | LOCKED | Request/approve/run/failed/unchanged lifecycle guarded | Correction filters recorded | correction `3`, failed correction `4`, correction replay MATCH | Synced | PASS | no |
| ops commands | LOCKED | 20 public commands registered/help-renderable | Command/Ops/StaticGuard proof recorded | Fresh success/held/failed/conflict/repair/snapshot/evidence/replay matrix | Synced | PASS | no |
| hash/seal | LOCKED | SHA-256 hashes + seal state enforced | Hash/seal static proof recorded | Stage chain and promote proof include hashes + SEALED state | Synced | PASS | no |
| audit docs | LOCKED for final sync | Governance guarded | AuditDocs proof recorded and static guard expectations synchronized | Current proof pack consumed by final lock | Synced | PASS | no |

## 3. Test Proof

Current source-state operator-local proof recorded in `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md`:

| Command | Result |
|---|---|
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` | OK (57 tests, 341 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CorrectionCommandsTest.php` | OK (11 tests, 60 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/CommandSurfaceSafetyStaticGuardTest.php` | OK (5 tests, 89 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` | OK (10 tests, 204 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` | OK (8 tests, 107 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` | OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` | OK (6 tests, 114 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Command"` | OK (97 tests, 1009 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Ops"` | OK (74 tests, 616 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Operational"` | OK (11 tests, 211 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "RuntimeProof"` | OK (13 tests, 220 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK (10 tests, 404 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK (176 tests, 4124 assertions) |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK (475 tests, 6942 assertions) |

Sandbox validation result for this audit: `BLOCKED_CONTAINER_RUNTIME_ENV`, not counted as runtime PASS.

## 4. Command Registry Proof

Command registry artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/final-list-market-data.txt`.

Total registered market-data commands proven: 20.

- `market-data:audit:hash`
- `market-data:backfill`
- `market-data:correction:approve`
- `market-data:correction:request`
- `market-data:correction:run`
- `market-data:current-publication:repair`
- `market-data:daily`
- `market-data:dataset:seal`
- `market-data:eod-bars:ingest`
- `market-data:eod-eligibility:build`
- `market-data:eod-indicators:compute`
- `market-data:evidence:export`
- `market-data:promote`
- `market-data:replay:backfill`
- `market-data:replay:fixture:generate`
- `market-data:replay:smoke`
- `market-data:replay:verify`
- `market-data:run:finalize`
- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`

## 5. Command Runtime Matrix Summary

| Scenario | Artifact / Output | Result | Status |
|---|---|---|---|
| daily import-only | `daily-2026-05-11/market_data_daily_summary.json` | `run_id=30`, accepted `913`, pointer switch `false` | PASS |
| promote success | `promote-2026-05-14/market_data_promote_summary.json` | `run_id=33`, `publication_id=27`, `coverage_gate_state=PASS`, `publishability_state=READABLE`, `seal_state=SEALED` | PASS |
| held partial promote | `held-partial-promote-2026-05-16/market_data_promote_summary.json` | `terminal_status=HELD`, `coverage_reason_code=COVERAGE_BELOW_THRESHOLD`, available `5/913` | PASS |
| failed empty source | `failed-empty-daily-2026-05-17/market_data_daily_summary.json` | `terminal_status=FAILED`, `final_reason_code=RUN_SOURCE_MANUAL_FILE_EMPTY` | PASS |
| replay smoke | `replay-smoke-run-33/replay_smoke_suite_summary.json` | `all_passed=true`, generated valid fixture included | PASS |
| replay backfill | `replay-backfill-run-33/market_data_replay_backfill_summary.json` | `all_passed=true`, replay case MATCH | PASS |

## 6. Schema Proof

Schema/migration status is consumed from locked `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`, `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT`, and `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT`. Current aggregate status: PASS for production-ready candidate. Current sandbox did not rerun migrations because PHP/PHPUnit runtime is blocked by environment, not by source code.

## 7. Coverage Proof

Current success promote proof for `run_id=33` records:

- `coverage_gate_state=PASS`
- `coverage_reason_code=COVERAGE_THRESHOLD_MET`
- `coverage_available_count=913`
- `coverage_universe_count=913`
- `coverage_ratio=1`
- `coverage_min_threshold=0.98`
- `coverage_universe_basis=ACTIVE_LISTED_EQUITY_AS_OF_DATE`

Held partial promote proof records `COVERAGE_BELOW_THRESHOLD` and `coverage_summary=available=5/913`, proving fail-closed not-readable behavior.

## 8. Read-Side Proof

Read-side scope remains `INTERNAL_ONLY` unless future HTTP/API consumers are introduced. Canonical reads are through current readable publication pointer resolution. Current proof pack consumes the locked read-side anti-bypass contract plus runtime proof showing successful current pointer publication `publication_id=27` and blocked/held paths with `pointer_switched=false`.

## 9. Evidence Export Proof

Run evidence admission artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/evidence-run-33/evidence_admission.json`.

- `selector_type=run`
- `selector_id=33`
- `evidence_admission_state=ADMITTED_COMPLETE`
- `missing_sections=[]`
- `critical_missing_sections=[]`
- `database_lookup_required_after_export=false`
- `deterministic_export=true`

Correction evidence admission artifact: `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json`, state `ADMITTED_COMPLETE`.

## 10. Replay Proof

Replay verify artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-verify-run-33/replay_result.json`.

- `replay_id=15`
- `replay_suite=runtime_generated_valid_case`
- `replay_case=ops_matrix_production_ready`
- `trade_date=2026-05-14`
- `publication_id=27`
- `publication_run_id=33`
- `publication_seal_state=SEALED`
- `current_pointer_status=RESOLVED_READABLE_CURRENT`
- `comparison_result=MATCH`
- `replay_status=PASS`
- `mismatch_count=0`

Replay smoke artifact proves valid/MISMATCH/BLOCKED cases with `all_passed=true`. Historical non-current replay proof remains present under `storage/app/market-data/full-production-ready/runtime/historical-replay/**` and proves `HISTORICAL_SEALED_PUBLICATION` resolution for `replay_id=8`.

## 11. Correction Proof

Correction lifecycle proof is LOCKED and consumed from `CORRECTION_LIFECYCLE_HARDENING_INVENTORY.md` plus artifacts:

- `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json`
- `storage/app/market-data/correction-lifecycle-hardening/failed-correction-4/evidence_admission.json`
- `storage/app/market-data/correction-lifecycle-hardening/replay-run-8/replay_result.json`

Required correction behaviors proven: valid baseline requirement, unchanged correction preserves pointer, failed correction does not publish a candidate, repair apply requires explicit reason, and correction evidence/replay lineage remains deterministic.

## 12. Hash / Seal Proof

Promote success proof for `run_id=33` records SHA-256 batch hashes and `seal_state=SEALED`:

- `bars_batch_hash=54e375d51bd2801e0d85f4cc0d17f7d795351b672940bcc2cebd533f36d6ca84`
- `indicators_batch_hash=4e504bb7da8644a305bfee444f64bf72aca8d60f6b7fe87ebe4392d858f7dfe9`
- `eligibility_batch_hash=b593ad09bb7a14550ca25c8e24db588de05dd41eb4f80403b807e147b16775a8`
- `sealed_at=2026-05-20 17:00:07`
- `lineage_verification_status=RUN_PUBLICATION_LINK_PRESENT`

## 13. Artifact Presence Proof

| Artifact | Status |
|---|---|
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/final-list-market-data.txt` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/evidence-run-33/evidence_admission.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-verify-run-33/replay_result.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-smoke-run-33/replay_smoke_suite_summary.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-backfill-run-33/market_data_replay_backfill_summary.json` | PRESENT |
| `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json` | PRESENT |
| `storage/app/market-data/correction-lifecycle-hardening/failed-correction-4/evidence_admission.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/fixtures/run-2-publication-2/manifest.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/verify-run-2-publication-2/replay_result.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/evidence_admission.json` | PRESENT |

## 14. Audit Ledger Readiness

| Ledger | Current proof-pack action |
|---|---|
| `LUMEN_IMPLEMENTATION_STATUS.md` | Full production proof pack synchronized to `DONE` with `MARKET_DATA_PRODUCTION_READY_LOCKED` review status. |
| `LUMEN_CONTRACT_TRACKER.md` | `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT` promoted to final `LOCKED`. |
| `FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md` | Updated from candidate state to final current-source `LOCKED` after this final audit sync consumed the ops matrix proof. |
| `MARKET_DATA_PRODUCTION_PROOF_PACK.md` | Promoted from aggregate source-state proof inventory to final source-state production lock basis. |

## 15. Remaining Risk Register

| Risk ID | Area | Severity | Evidence | Is Production Blocker? | Required Action |
|---|---|---:|---|---|---|
| R-001 | Final audit docs synchronization | P2 | CLOSED by this Final Audit Docs Synchronization session; candidate state was consumed and locked. | no | None. |
| R-002 | Live provider / credentials / scheduler / deployment | P2 | Source proof uses deterministic local/manual-file fixtures and recorded runtime artifacts, not live production scheduling. | no | Validate in deployment/CI/live-provider environment before rollout. |
| R-003 | Sandbox runtime | P3 | PHP 8.4.16 and missing extensions block PHPUnit/artisan runtime proof in sandbox. | no | Use supported operator/CI baseline. |

## 16. Final Decision

Decision: `MARKET_DATA_PRODUCTION_READY_LOCKED`.

Reason: all market-data contract areas are locked or consumed as locked for the current source state; the ops command surface blocker was closed by runtime artifact proof; source-state artifacts prove success, held, failed, conflict, correction, evidence, replay, hash/seal, and read-side behavior; no P0/P1 blocker remains; Final Audit Docs Synchronization consumed this proof pack and reconciled the implementation ledger, contract tracker, production validation inventory, full production-ready inventory, and static guard expectations. The final lock is source-state specific and still requires revalidation for new code/config/vendor/provider/deployment changes.


## 17. 2026-05-21 Ops Runtime Parity Revalidation

Scope: `PRODUCTION_ROLLOUT_VALIDATION_RUNTIME_PARITY_PROOF`.

Rollout decision: `OPS_RUNTIME_PARITY_BLOCKED_BY_ENVIRONMENT`.

Source lock impact: `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid. This revalidation found no P0/P1 market-data source-code blocker.

Runtime evidence root: `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

Validated PASS evidence:

- PHP 7.4.33 and required extensions present.
- Composer 2.8.4 and `composer validate` valid.
- Artisan boot clean on Lumen 8.3.4; 20 market-data commands registered.
- Requested market-data help commands exit 0 with clean output.
- Targeted static guards PASS: AuditDocs OK (10 tests, 419 assertions), ProductionValidation OK (13 tests, 220 assertions), OperationalReadiness OK (10 tests, 204 assertions), OpsEnvironment OK (8 tests, 107 assertions), ConfigEnvGovernance OK (10 tests, 123 assertions).
- Filtered suites PASS: AuditDocs OK (10 tests, 419 assertions), StaticGuard OK (176 tests, 4139 assertions), Production OK (14 tests, 253 assertions), Operational OK (11 tests, 211 assertions), OpsEnvironment OK (8 tests, 107 assertions).
- Full `tests/Unit/MarketData` PASS: OK (475 tests, 6957 assertions), Time 00:10.716, Memory 38.00 MB.
- Manual-file runtime smoke PASS: `run_id=30`, `publication_id=24`, import-only no pointer switch, promote `SUCCESS/READABLE/PASS/SEALED`.
- Evidence export PASS for `run_id=30`: `ADMITTED_COMPLETE`, `COMPLETE`, 10 files.
- Replay verify PASS for current-readable fixture: `replay_id=19`, `MATCH`, `PASS`, `mismatch_count=0`.
- Replay verify PASS for historical non-current publication fixture: `replay_id=20`, `publication_id=2`, `publication_is_current=false`, `NOT_CURRENT_POINTER`, `HISTORICAL_PUBLICATION_AUDIT`, `MATCH`, `PASS`, `mismatch_count=0`.
- Correction lifecycle PASS for `correction_id=5`: request/approve/run/evidence export succeeded; unchanged correction preserved current publication; rerun blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`.
- Storage/log/evidence paths writable.

Environment/deployment blockers:

- `BLOCKED_TESTING_DATABASE_ENV`: plain `php artisan migrate:fresh --env=testing` did not select `.env.testing` database `tradeaxis_testing`; it targeted `.env` database `tradeaxis`. Explicit `APP_ENV=testing DB_DATABASE=tradeaxis_testing` override was required to prove migrations and required tables in the intended testing DB.
- `OPS_DEPLOYMENT_TASK_REQUIRED`: `schedule:list` is unavailable in this Lumen build; `schedule:run` exits cleanly but current env has daily scheduling disabled. Production cron, logging/output routing, timezone, and no-silent-failure proof remain deployment tasks.
- `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`: live provider/API smoke was not run because no safe dry-run/ticker-limit command surface is available and a broad provider fetch was intentionally avoided.

Final note: this section is an ops runtime parity overlay. It does not unlock or relock source behavior; it records that source remains production-ready locked while rollout parity is blocked by environment/deployment proof gaps.

Post-doc validation:

- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 421 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (13 tests, 220 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php` -> OK (8 tests, 107 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (176 tests, 4141 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (475 tests, 6959 assertions).


## 18. 2026-05-21 Testing DB Isolation / Safe Migration Guard

Scope: `TESTING_DATABASE_ISOLATION_SAFE_MIGRATION_CONTRACT`.

Decision for this blocker: `TESTING_DB_ISOLATION_GUARD_PASSED`.

Source lock impact: `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid. This patch does not change market-data service/repository/provider/replay/correction/finalize/pointer behavior or migration schema.

Runtime evidence root: `storage/app/market-data/testing-database-isolation-safe-migration/**`.

Validated PASS evidence:

- CLI environment selection proof: `--env=testing` resolves `APP_ENV=testing`, `DB_CONNECTION=mysql`, and `DB_DATABASE=tradeaxis_testing`.
- Negative guard proof: `php artisan migrate:fresh --env=testing --database=nonexistent` exits 3 with reason code `BLOCKED_TESTING_DATABASE_ENV`.
- Migration status proof: `php artisan migrate:status --env=testing` exits 0.
- Migration fresh proof: `php artisan migrate:fresh --env=testing` exits 0 and runs all 29 migrations against `tradeaxis_testing`.
- Required table proof: `tickers`, `market_calendar`, `eod_runs`, `eod_publications`, `eod_current_publication_pointer`, `md_replay_daily_metrics`, `eod_dataset_corrections`, and `md_session_snapshots` are present in `tradeaxis_testing`.
- Static guard scope added: `tests/Unit/MarketData/TestingDatabaseIsolationStaticGuardTest.php` -> OK (4 tests, 41 assertions).
- Audit/governance scope: `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 430 assertions).
- Production/ops targeted scope: ProductionValidation OK (13 tests, 220 assertions), OperationalReadiness OK (10 tests, 204 assertions), OpsEnvironment OK (8 tests, 107 assertions), ConfigEnvGovernance OK (10 tests, 123 assertions).
- StaticGuard filter: `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (180 tests, 4191 assertions).
- Full MarketData suite: `vendor/bin/phpunit tests/Unit/MarketData` -> OK (479 tests, 7009 assertions), Time 00:18.274, Memory 40.00 MB.
- New command output artifacts are UTF-8 plain text without null-byte/UTF-16 evidence noise.

Closed blocker:

- `BLOCKED_TESTING_DATABASE_ENV`: closed for this patched source state when using CLI `--env=testing`.

Remaining rollout blockers:

- `OPS_DEPLOYMENT_TASK_REQUIRED`: scheduler/cron production proof still required.
- `PROVIDER_SMOKE_DEFERRED_NO_SAFE_DRY_RUN_LIMIT`: safe live provider smoke still required.

Final note: this section closes the highest-risk DB isolation gap reported by the ops runtime parity audit. Full production rollout parity remains blocked until scheduler/cron deployment proof and safe provider smoke are completed.
