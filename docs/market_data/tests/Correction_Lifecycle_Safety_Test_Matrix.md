# Correction Lifecycle Safety Test Matrix

Status: LOCKED for code/test/runtime correction lifecycle scope after 2026-05-20 correction lifecycle hardening. Runtime unchanged replay MATCH and failed-correction pointer preservation proof are now recorded.

Required validation coverage:

- request command resolves current sealed readable coverage-PASS baseline before creating a correction
- request command blocks without valid baseline using `CORRECTION_BASELINE_LINK_MISSING`
- approval is required before pipeline execution
- baseline resolver uses current readable pointer only and rejects latest/MAX-date shortcut
- invalid/incomplete correction hash comparison blocks pointer switch
- unchanged correction discards candidate publication, preserves pointer, does not reseal, and renders `candidate_publication_switch=false`
- changed correction requires deterministic artifact changed scope before reseal/promotion
- correction failure restores or preserves previous current readable publication
- repair apply command requires `--reason` or `--force_reason`
- correction-run-publication-artifact linkage is exposed in evidence
- evidence export records unchanged candidate proof as `UNCHANGED_CORRECTION_CANDIDATE_DISCARDED`
- replay stores and compares expected/actual correction lifecycle context when fixture prerequisites resolve
- correction command output displays unchanged/reseal/baseline/candidate/final outcome context
- static guard prevents baseline shortcut, invalid diff bypass, missing evidence/replay fields, and hidden command lifecycle state

2026-05-20 local validation highlights:

- `CorrectionCommandsTest.php` -> OK (10 tests, 56 assertions)
- `CorrectionEvidenceExportServiceTest.php` -> OK (2 tests, 42 assertions)
- `CorrectionRepositoryIntegrationTest.php` -> OK (5 tests, 70 assertions)
- `ReplayVerificationServiceTest.php` -> OK (10 tests, 34 assertions)
- `ReplayEvidenceExportServiceTest.php` -> OK (2 tests, 55 assertions)
- `CorrectionLifecycleSafetyStaticGuardTest.php` -> OK (5 tests, 74 assertions)
- `DbIntegrityConstraintEnforcementStaticGuardTest.php` -> OK (6 tests, 452 assertions)
- `CommandSurfaceSafetyStaticGuardTest.php` -> OK (5 tests, 89 assertions)
- `MarketDataPipelineIntegrationTest.php` -> OK (55 tests, 1227 assertions)
- runtime correction request/approve/run proof for `correction_id=3`, `run_id=8`, baseline publication `5`, candidate publication `7` discarded, pointer before/after publication `5`
- correction evidence export for `correction_id=3` -> `ADMITTED_COMPLETE`, `UNCHANGED`, `NOT_RESEALED_UNCHANGED`, `publication_switch=0`
- replay fixture generation for `run_id=8` succeeded; replay verify now records `replay_id=10`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`, and `UNCHANGED_CORRECTION_BASELINE_PRESERVED`
- failed correction runtime proof for `correction_id=4`, candidate `run_id=11`, status `FAILED`, reason `RUN_SOURCE_MANUAL_FILE_NOT_FOUND`, no replacement publication, baseline pointer publication `5` preserved
- final audit/static/full rerun after ledger sync -> AuditDocs OK (10 tests, 382 assertions), StaticGuard OK (170 tests, 3982 assertions), full `tests/Unit/MarketData` OK (460 tests, 6751 assertions)
