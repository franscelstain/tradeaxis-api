# Legacy Semantic Extract — LX-MD-0039-IMP-01

- Source ID: `LS-MD-0039`
- Original path: `audit/PRODUCTION_VALIDATION_INVENTORY.md`
- Original SHA1: `A8D8B95268BF27AB2D2EE5D169FEE87AFCAF4EB7`
- Extract role: `IMPLEMENTATION`
- Source range: `L333-L390`
- Extract body SHA1: `C2F8BFF1AB30998BB0C6368DDF58D56D7C701E93`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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



<!-- LEGACY_EXTRACT_BODY_END -->
