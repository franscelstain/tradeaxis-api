# Legacy Semantic Extract — LX-MD-0031-EVD-02

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `EVIDENCE`
- Source range: `L3653-L3750`
- Extract body SHA1: `DA2ADAB37AD7C455FC029A123ADD81C1F62D97E6`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-20 Final Lock Patch — Unchanged Correction Evidence Consistency

- Status: LOCKED_LOCAL_RUNTIME_PROOF.
- Source ZIP/session: `tradeaxis-api-correction-lifecycle-hardening-202605200904.zip`.
- Governance note: prior correction lifecycle DONE/LOCKED wording in this file is retained as historical proof for the pre-final-lock source state. At this 2026-05-20 checkpoint the current final-lock source state still required artisan evidence export and PHPUnit rerun on the supported PHP/Lumen baseline; later proof entries closed that requirement and restored the current production-ready lock.
- Gap fixed in source: unchanged correction evidence no longer aliases preserved baseline publication as candidate/new publication.
- Code patch:
  - `EodEvidenceRepository::findCorrectionById()` and `findCorrectionByRunId()` now expose `new_run.notes as new_run_notes` so evidence export can read discarded candidate lineage from runtime notes.
  - `MarketDataEvidenceExportService` resolves unchanged discarded candidate publication from `discarded_candidate_publication_id` / `candidate_publication_id` notes and rejects fallback to preserved baseline as candidate.
  - Unchanged correction evidence now emits explicit `preserved_publication_id`, `discarded_candidate_publication_id`, `replacement_publication_id=null`, and `publication_switch=false` semantics.
  - `new_publication` and `new_hashes` are null for unchanged correction because no replacement publication exists.
- Artifact patch:
  - `storage/app/market-data/correction-lifecycle-hardening/correction-3/correction_evidence.json` now records baseline/preserved publication `5`, discarded candidate publication `7`, replacement publication `null`, and candidate proof `DISCARDED_CANDIDATE_RECORDED`.
  - `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json` remains `ADMITTED_COMPLETE` with `critical_missing_sections=[]`.
  - `storage/app/market-data/correction-lifecycle-hardening/replay-run-8/replay_result.json` remains the runtime replay proof reference for candidate publication `7` and preserved baseline publication `5`.
- Tests updated:
  - `CorrectionEvidenceExportServiceTest` now prevents unchanged correction evidence from treating baseline/current publication as candidate/new publication.
  - `CorrectionLifecycleSafetyStaticGuardTest` now guards the `new_run.notes` evidence source and discarded/replacement publication fields.
- Validation performed in this container:
  - `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` -> PASS.
  - `php -l app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` -> PASS.
  - `php -l tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php` -> PASS.
  - `php -l tests/Unit/MarketData/CorrectionLifecycleSafetyStaticGuardTest.php` -> PASS.
  - `php artisan list market-data` -> BLOCKED by environment guard: PHP `8.4.16` is outside the documented Lumen clean-output baseline `<8.4`.
  - `php vendor/bin/phpunit tests/Unit/MarketData/CorrectionEvidenceExportServiceTest.php` -> BLOCKED because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are missing.
- Required operator rerun before LOCKED:
  - `php artisan market-data:evidence:export --correction_id=3 --output_dir=storage/app/market-data/correction-lifecycle-hardening/correction-3 --env=testing`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"`.
  - `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"`.
  - `vendor/bin/phpunit tests/Unit/MarketData`.
- Final implementation status for this patched ZIP: `Correction Lifecycle Hardening / Correction Lifecycle Safety -> DONE`; `CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED`.


---


## 2026-05-21 Production Rollout Validation Runtime Parity Proof

- Production Rollout Validation / Ops Runtime Parity Proof -> BLOCKED

  [LAST_UPDATED] 2026-05-21

  [RELATED_CONTRACT] PRODUCTION_ROLLOUT_RUNTIME_PARITY_PROOF_CONTRACT

  [REVIEW_STATUS] [OPS_RUNTIME_PARITY] SUPERSEDED_BY_FINAL_PASSED_RECONCILIATION

  [HISTORY]
  - 2026-05-21 -> Latest source ZIP `tradeaxis-api-market-data.zip` was extracted into a clean validation workspace and treated as the source of truth.
  - 2026-05-21 -> Runtime baseline passed on PHP 7.4.33 with Composer 2.8.4; required PHP extensions were present, including `dom`, `mbstring`, `xml`, `xmlwriter`, `json`, `PDO`, `pdo_mysql`, `pdo_pgsql`, `pdo_sqlite`, `openssl`, `curl`, `fileinfo`, and `tokenizer`.
  - 2026-05-21 -> Artisan boot proof passed cleanly: `php artisan list` and `php artisan --version` returned exit code 0, Lumen 8.3.4, no PHP warning/deprecation/noise, and 20 `market-data:*` commands.
  - 2026-05-21 -> Market-data help surface proof passed for daily, stage, hash/seal/finalize, evidence, replay, backfill, session snapshot, and correction commands; all requested help commands returned exit code 0 with clean output.
  - 2026-05-21 -> Initial AuditDocs guard exposed two audit-doc wording mismatches only; tracker/status wording was aligned append-only without changing runtime application logic.
  - 2026-05-21 -> Targeted guard proof passed after audit-doc wording alignment: AuditDocs OK (10 tests, 419 assertions), ProductionValidation OK (13 tests, 220 assertions), OperationalReadiness OK (10 tests, 204 assertions), OpsEnvironment OK (8 tests, 107 assertions), ConfigEnvGovernance OK (10 tests, 123 assertions).
  - 2026-05-21 -> Filtered proof passed: AuditDocs OK (10 tests, 419 assertions), StaticGuard OK (176 tests, 4139 assertions), Production OK (14 tests, 253 assertions), Operational OK (11 tests, 211 assertions), OpsEnvironment OK (8 tests, 107 assertions).
  - 2026-05-21 -> Full `tests/Unit/MarketData` proof passed: OK (475 tests, 6957 assertions), Time 00:10.716, Memory 38.00 MB.
  - 2026-05-21 -> Safe runtime smoke passed before DB reset: manual-file daily import-only for 2026-05-11 kept `promote_status=NOT_PROMOTED` and `pointer_switched=false`; promote for `run_id=30` returned `SUCCESS`, `READABLE`, `COVERAGE_THRESHOLD_MET`, `SEALED`, and current publication `24`.
  - 2026-05-21 -> Evidence export runtime proof passed for `run_id=30`: `ADMITTED_COMPLETE`, `COMPLETE`, `RESOLVED_READABLE_CURRENT`, and 10 files.
  - 2026-05-21 -> Replay runtime proof passed for current-readable `run_id=33` with `replay_id=19`, `MATCH`, `PASS`, `mismatch_count=0`; historical non-current publication `run_id=2` / `publication_id=2` passed with `replay_id=20`, `HISTORICAL_PUBLICATION_AUDIT`, `HISTORICAL_SEALED_PUBLICATION`, `NOT_CURRENT_POINTER`, `MATCH`, `PASS`, and `mismatch_count=0`.
  - 2026-05-21 -> Correction lifecycle runtime proof passed for `correction_id=5`: request/approve/run/evidence export completed, unchanged correction preserved current publication, evidence was `ADMITTED_COMPLETE`, and rerun was blocked with `COMMAND_CORRECTION_STATUS_NOT_EXECUTABLE`.
  - 2026-05-21 -> Migration chain source proof passed, but default testing command revealed an environment parity blocker: plain `php artisan migrate:fresh --env=testing` operated against `.env` database `tradeaxis`, not `.env.testing` database `tradeaxis_testing`; explicit env override `APP_ENV=testing DB_DATABASE=tradeaxis_testing php artisan migrate:fresh --env=testing` migrated all market-data tables into `tradeaxis_testing`.
  - 2026-05-21 -> Scheduler readiness is not production-complete in this workspace: `schedule:list` is unavailable in this Lumen build; `schedule:run` exits 0 cleanly with no ready commands because `MARKET_DATA_DAILY_ENABLED` is not enabled; code registers only `market-data:daily --latest` at configured cutoff when daily scheduling is enabled.
  - 2026-05-21 -> Storage/log/evidence paths are present and writable for the current operator user.
  - 2026-05-21 -> Live provider smoke was not executed because the public command surface has no dry-run or ticker-limit option; running provider/API mode would attempt a broad universe fetch.
  - 2026-05-21 -> Post-doc validation passed: AuditDocs OK (10 tests, 421 assertions), ProductionValidation OK (13 tests, 220 assertions), OpsEnvironment OK (8 tests, 107 assertions), StaticGuard OK (176 tests, 4141 assertions), and full `tests/Unit/MarketData` OK (475 tests, 6959 assertions).

  [IMPLEMENTATION]
  - No market-data runtime service, repository, migration, provider, replay, correction, finalize, pointer, or command logic was changed in this rollout validation session.
  - Audit-doc wording alignment was limited to existing evidence export proof wording required by `AuditDocsSynchronizationStaticGuardTest.php`.
  - Runtime evidence was written under `storage/app/market-data/production-rollout-validation-runtime-parity/**`.

  [ENFORCEMENT]
  - Runtime PASS is claimed only for commands actually executed with exit code 0 and clean output.
  - Testing DB migration proof is not claimed from plain `--env=testing`; that command is recorded as an environment parity blocker because it did not target `.env.testing`.
  - Provider/live smoke is deferred rather than faked because no safe narrow provider command is available.

  [FINAL_BEHAVIOR]
  - `MARKET_DATA_PRODUCTION_READY_LOCKED` remains valid for the locked market-data source state.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.
  - Historical ops runtime parity blocker is superseded by final provider smoke PASS and scheduler due-run/non-silent-failure proof; current status is `OPS_RUNTIME_PARITY_PASSED`.

  [EVIDENCE]
  - Command output root: `storage/app/market-data/production-rollout-validation-runtime-parity/command-output`.
  - Runtime artifact root: `storage/app/market-data/production-rollout-validation-runtime-parity/runtime`.
  - Baseline, artisan, command registry, PHPUnit, migration, evidence, replay, correction, scheduler, and storage permission outputs are stored in that command-output root.

  [BLOCKERS]
  - `BLOCKED_TESTING_DATABASE_ENV`: plain `php artisan migrate:fresh --env=testing` did not use `.env.testing` database `tradeaxis_testing`.
  - `OPS_DEPLOYMENT_TASK_REQUIRED`: production scheduler/cron requires deployment configuration, `MARKET_DATA_DAILY_ENABLED=true`, external cron entry, and log/output routing review.
  - `PROVIDER_SMOKE_SAFE_MODE_SURFACE_ADDED`: live provider command was not run because no safe dry-run/ticker-limit surface exists.

  [NEXT_ACTION]
  - Fix or document operator invocation so testing/staging migrations cannot hit `.env` database by accident.
  - Configure production scheduler/cron with explicit logging and rerun scheduler proof.
  - Add or use a safe provider smoke mode before live provider rollout.

---


<!-- LEGACY_EXTRACT_BODY_END -->
