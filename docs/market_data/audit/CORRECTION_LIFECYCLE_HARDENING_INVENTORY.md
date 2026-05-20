# Correction Lifecycle Hardening Inventory

Status: LOCKED_LOCAL_RUNTIME_PROOF for the final-lock patch source state. Operator-local correction/evidence/replay proof has been supplied after the unchanged-correction evidence consistency fix; the remaining StaticGuard/AuditDocs failure was a stale ledger-status mismatch now corrected in `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md`.

## Session

- Active session: Correction Lifecycle Hardening
- Source of truth for final-lock patched session output: `tradeaxis-api-correction-lifecycle-hardening-202605200904.zip`
- Input source ZIP: `tradeaxis-api-correction-lifecycle-hardening-202605200904.zip`
- Runtime: operator-local proof uses the supported PHP/Lumen baseline; this container still has PHP 8.4.16 and missing PHPUnit extensions, so container validation remains static/fail-closed only
- Scope: correction request, approval, execution eligibility, baseline proof, unchanged pointer safety, evidence linkage, replay linkage, repair/force guard, audit docs synchronization

## Decision Matrix

| Correction Area | Contract Required | Code Enforces | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| correction request | request must have valid current sealed readable PASS baseline | `RequestCorrectionCommand` resolves `findCorrectionBaselinePublicationForTradeDate` before create | `CorrectionCommandsTest` baseline success/block | `correction_id=3`, baseline publication `5`, baseline run `6` | none |
| correction approval | only approved correction can execute | `RunCorrectionCommand` status guard + repository status guard | `CorrectionCommandsTest`, `MarketDataPipelineIntegrationTest` | `correction_id=3` approved by `codex` | none |
| execution eligibility | approved, date-match, non-consumed, mode valid | `EodCorrectionRepository::canExecuteCorrection` + pipeline baseline recheck | integration/static guards | run `8` executed after approval | none |
| baseline publication lookup | pointer-resolved current readable baseline | `EodPublicationRepository::findCorrectionBaselinePublicationForTradeDate` | `CorrectionLifecycleSafetyStaticGuardTest` | baseline publication `5`, run `6` | none |
| baseline readable requirement | SUCCESS/READABLE/SEALED/PASS + mirror | baseline resolver predicates | static guard + pipeline tests | request printed baseline ids | none |
| coverage gate requirement | PASS required before readable/publish | finalize/coverage services | Coverage/Correction filters | run `8` coverage PASS | none |
| seal/finalize requirement | candidate cannot become current unless sealed/finalized | publication repository + pipeline finalize | Publication/Finalize/Pointer filters | unchanged path did not promote candidate | none |
| pointer replacement | replacement only after valid changed candidate | `promoteCandidateToCurrent` baseline guard | pipeline pointer failure tests | pointer stayed publication `5`/run `6` | none |
| unchanged correction behavior | preserve current pointer, no reseal, no candidate switch | pipeline discard + command/evidence switch false | command/evidence/pipeline tests | `candidate_publication_id=7` discarded; `candidate_publication_switch=false`; evidence `publication_switch=0` | none |
| failed correction behavior | failed candidate never current | pipeline restore/fail-safe handling + command failure handling | `CorrectionCommandsTest`, `CorrectionRepositoryIntegrationTest`, `MarketDataPipelineIntegrationTest` failure cases | `correction_id=4`, candidate `run_id=11`, status `FAILED`, baseline pointer preserved | none |
| reseal guard | sealed publication immutable | `assertPublicationMutable` | repository/static tests | unchanged proof did not reseal baseline | none |
| force replace guard | force requires reason | promote command guard | command static/ops tests | promote help shows force reason options | none |
| repair candidate guard | repair apply requires reason | repair command guard | `OpsCommandSurfaceTest` + static guard | apply without reason blocked | none |
| run/publication/correction linkage | correction links prior/new run and baseline/replacement publication | correction repository + evidence repository | repository/evidence/pipeline tests | correction `3`: prior run `6`, new run `8`, baseline publication `5`, replacement null | none |
| evidence export linkage | correction evidence records lineage and unchanged result | evidence export service | `CorrectionEvidenceExportServiceTest` | correction `3` export ADMITTED_COMPLETE | none |
| replay verification linkage | replay stores/compares correction context when fixture resolves | replay service/repository preserved-baseline semantics | `ReplayVerificationServiceTest`, `ReplayEvidenceExportServiceTest`, Replay filter/static guard | fixture generation for run `8` succeeded; verify `replay_id=10` PASS/MATCH | none |
| command output | operator sees baseline, candidate, switch, reason | correction/repair commands | command tests | request/run/repair outputs captured | none |
| audit docs entry | active session and canonical entry synchronized | ledger + static guard | audit docs guard after docs update | correction scope locked; aggregate production-ready downgraded to REVIEW_REQUIRED | none |
| contract tracker entry | one canonical correction contract | tracker + static guard | audit docs guard after docs update | `CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED` | none |

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

## Closed Runtime Gaps

- Unchanged correction replay MATCH is now claimed for run `8`: preserved baseline publication `5` / owner run `6`, discarded candidate publication `7`, `replay_id=10`, `PASS`.
- Failed correction artisan runtime proof is now claimed for correction `4`: candidate run `11` failed, no replacement publication was current, baseline pointer publication `5` remained safe.

## Remaining Risk

- No correction lifecycle blocker remains in this scoped session.
- Full market-data production-ready remains `REVIEW_REQUIRED` for this patched source state until the aggregate proof pack and ops runtime matrix are rerun.


## Final Lock Patch Addendum — Unchanged Evidence Candidate Alias Fix

- Status: LOCKED_LOCAL_RUNTIME_PROOF.
- The final audit found `correction-3/correction_evidence.json` still aliasing baseline/current publication `5` as `candidate_publication_id`, `new_publication.publication_id`, and `candidate_historical_publication_proof.publication_id`.
- Patch outcome:
  - `correction_lifecycle.baseline_publication_id=5`.
  - `correction_lifecycle.preserved_publication_id=5`.
  - `correction_lifecycle.candidate_publication_id=7`.
  - `correction_lifecycle.discarded_candidate_publication_id=7`.
  - `correction_lifecycle.replacement_publication_id=null`.
  - `new_publication=null`.
  - `candidate_historical_publication_proof.publication_id=7`.
  - `candidate_historical_publication_proof.proof_status=DISCARDED_CANDIDATE_RECORDED`.
  - `publication_switch=false`.
- Code source for discarded candidate is traceable through `new_run.notes` keys `discarded_candidate_publication_id` and `candidate_publication_id`; baseline fallback is explicitly blocked for unchanged evidence.
- Container validation:
  - Changed PHP files passed `php -l`.
  - Container Artisan/PHPUnit runtime proof remains blocked by PHP baseline mismatch and missing extensions.
  - Operator-local final-lock rerun supplied: `CorrectionEvidenceExportServiceTest.php` OK (2 tests, 51 assertions), `CorrectionLifecycleSafetyStaticGuardTest.php` OK (5 tests, 78 assertions), Correction filter OK (75 tests, 1438 assertions), Evidence filter OK (56 tests, 1071 assertions), and Replay filter OK (58 tests, 894 assertions).
  - StaticGuard/AuditDocs failure was limited to stale audit-ledger status text; this patch updates the canonical correction implementation to DONE and the contract to LOCKED.
- Required follow-up validation after this ledger patch:
  - Rerun `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"`.
  - Rerun `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"`.
  - Rerun full `vendor/bin/phpunit tests/Unit/MarketData` before using this ZIP as an aggregate production-ready proof pack.
