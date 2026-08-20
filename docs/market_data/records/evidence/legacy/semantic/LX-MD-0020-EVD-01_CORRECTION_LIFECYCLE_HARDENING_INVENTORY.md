# Legacy Semantic Extract — LX-MD-0020-EVD-01

- Source ID: `LS-MD-0020`
- Original path: `audit/CORRECTION_LIFECYCLE_HARDENING_INVENTORY.md`
- Original SHA1: `CF3BD55641F75EDA47DC3EB456D1824632863949`
- Extract role: `EVIDENCE`
- Source range: `L40-L82`
- Extract body SHA1: `214DB600AF59FA6751416A09FBDF33B1CC5ADD6E`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Runtime Proof

- `php artisan market-data:correction:request --trade_date=2026-02-18 --reason_code=READABILITY_FIX --reason_note="runtime unchanged pointer proof after switch-label fix" --requested_by=codex --env=testing` -> PASS; `correction_id=3`, `baseline_publication_id=5`, `baseline_run_id=6`.
- `php artisan market-data:correction:approve 3 --approved_by=codex --env=testing` -> PASS; status `APPROVED`.
- `php artisan market-data:correction:run 3 --requested_date=2026-02-18 --source_mode=manual_file --env=testing` -> PASS; `run_id=8`, `SUCCESS`, `READABLE`, `coverage_gate_state=PASS`, `candidate_publication_id=7`, `correction_outcome=UNCHANGED`, `correction_reseal_status=NOT_RESEALED_UNCHANGED`, `candidate_publication_switch=false`.
- Pointer before/after proof: before request baseline pointer was publication `5` / run `6`; after run `8`, pointer remained publication `5` / run `6` / version `4`.
- Correction linkage proof: correction `3` persisted `prior_run_id=6`, `new_run_id=8`, `baseline_publication_id=5`, `replacement_publication_id=null`, status `CONSUMED_CURRENT`.
- Evidence export: `php artisan market-data:evidence:export --correction_id=3 --output_dir=storage/app/market-data/correction-lifecycle-hardening/correction-3 --env=testing` -> `ADMITTED_COMPLETE`, `UNCHANGED`, `NOT_RESEALED_UNCHANGED`, `publication_switch=0`.
- Replay fixture generation: `php artisan market-data:replay:fixture:generate 8 --case=correction-unchanged-run-8 --output_dir=storage/app/market-data/correction-lifecycle-hardening/fixtures/run-8-correction-unchanged --env=testing` -> generated fixture.
- Replay verification: `php artisan market-data:replay:verify 8 storage/app/market-data/correction-lifecycle-hardening/fixtures/run-8-correction-unchanged --output_dir=storage/app/market-data/correction-lifecycle-hardening/replay-run-8 --env=testing` -> `replay_id=10`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`, `UNCHANGED_CORRECTION_BASELINE_PRESERVED`.
- Failed correction request: `php artisan market-data:correction:request --trade_date=2026-02-18 --reason_code=READABILITY_FIX --reason_note="runtime failed correction pointer proof" --requested_by=codex --env=testing` -> PASS; `correction_id=4`, `baseline_publication_id=5`, `baseline_run_id=6`.
- Failed correction approval: `php artisan market-data:correction:approve 4 --approved_by=codex --env=testing` -> PASS; status `APPROVED`.
- Failed correction run: `MARKET_DATA_SOURCE_LOCAL_DIRECTORY=storage/app/market_data/missing_failed_correction_fixture php artisan market-data:correction:run 4 --requested_date=2026-02-18 --source_mode=manual_file --env=testing` -> expected non-zero, `correction_status=FAILED`, `failure_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, `candidate_publication_id=null`, `candidate_publication_switch=false`.
- Failed correction evidence export: `php artisan market-data:evidence:export --correction_id=4 --trade_date=2026-02-18 --output_dir=storage/app/market-data/correction-lifecycle-hardening/failed-correction-4 --env=testing` -> `ADMITTED_COMPLETE`, `FAILED`, `NOT_RESEALED`, `publication_switch=0`.
- Failed run evidence export: `php artisan market-data:evidence:export --run_id=11 --trade_date=2026-02-18 --output_dir=storage/app/market-data/correction-lifecycle-hardening/failed-correction-4-run-11 --env=testing` -> `ADMITTED_INCOMPLETE`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `final_reason_code=RUN_SOURCE_MANUAL_FILE_NOT_FOUND`.
- Repair guard: `php artisan market-data:current-publication:repair --trade_date=2026-02-18 --apply --env=testing` -> BLOCKED with `COMMAND_DESTRUCTIVE_GUARD_REQUIRED`.
- Repair dry-run: `php artisan market-data:current-publication:repair --trade_date=2026-02-18 --env=testing` -> OK, no invalid current pointer state.

## Validation

- `php -l` passed for changed PHP files.
- `CorrectionRepositoryIntegrationTest.php` -> OK (5 tests, 70 assertions).
- `CorrectionCommandsTest.php` -> OK (10 tests, 56 assertions).
- `CorrectionEvidenceExportServiceTest.php` -> OK (2 tests, 42 assertions).
- `ReplayVerificationServiceTest.php` -> OK (10 tests, 34 assertions).
- `ReplayEvidenceExportServiceTest.php` -> OK (2 tests, 55 assertions).
- `CorrectionLifecycleSafetyStaticGuardTest.php` -> OK (5 tests, 74 assertions).
- `DbIntegrityConstraintEnforcementStaticGuardTest.php` -> OK (6 tests, 452 assertions).
- `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 89 assertions).
- `OpsCommandSurfaceTest.php --filter "current_publication_repair"` -> OK (2 tests, 12 assertions).
- `MarketDataPipelineIntegrationTest.php` -> OK (55 tests, 1227 assertions).
- `tests/Unit/MarketData --filter "Correction"` -> OK (75 tests, 1425 assertions).
- `tests/Unit/MarketData --filter "Publication"` -> OK (114 tests, 1338 assertions).
- `tests/Unit/MarketData --filter "Pointer"` -> OK (85 tests, 1184 assertions).
- `tests/Unit/MarketData --filter "Finalize"` -> OK (51 tests, 392 assertions).
- `tests/Unit/MarketData --filter "Coverage"` -> OK (70 tests, 788 assertions).
- `tests/Unit/MarketData --filter "Evidence"` -> OK (56 tests, 1063 assertions).
- `tests/Unit/MarketData --filter "Replay"` -> OK (58 tests, 894 assertions).
- `tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (10 tests, 382 assertions).
- `tests/Unit/MarketData --filter "AuditDocs"` -> OK (10 tests, 382 assertions).
- `tests/Unit/MarketData --filter "StaticGuard"` -> OK (170 tests, 3982 assertions).
- Full `tests/Unit/MarketData` -> OK (460 tests, 6751 assertions).


<!-- LEGACY_EXTRACT_BODY_END -->
