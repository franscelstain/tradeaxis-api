# Replay Determinism Runtime Proof Inventory

Last updated: 2026-05-20

## Decision

Replay determinism runtime proof is complete for the current-readable fixture scope, command/evidence surfaces, and historical non-current publication scope. The runtime proof produced explicit `PASS`, `FAIL`, and `BLOCKED` outcomes. Historical replay is locked as an explicit-context audit path because the current source ZIP includes the required historical non-current runtime artifact pack with `historical_publication_allowed=true`, `replay_actual_resolution_mode=HISTORICAL_PUBLICATION_AUDIT`, and `replay_publication_scope=HISTORICAL_SEALED_PUBLICATION`.

This inventory now supports full market-data production readiness for this source ZIP because the historical non-current replay runtime artifacts are supplied and the `FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md` lock conditions are satisfied.  **[SUPERSEDED 2026-08-06 — W22]** Klaim ini tidak berlaku untuk baseline yang dikoreksi; lihat `reports/AUDIT_FINAL_STATE.md`.

## Runtime Proof

| Proof | Command | Result |
|---|---|---|
| Command surface | `php artisan list market-data`; replay/evidence help commands | PASS; 20 market-data commands listed and replay/evidence command options exposed |
| Migration | `php artisan migrate --env=testing --force` | PASS; `2026_05_19_000002_add_replay_status_to_replay_daily_metrics` migrated |
| Fixture generation | `php artisan market-data:replay:fixture:generate 2 --case=valid_case --output_dir=storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2` | PASS; fixture generated from `run_id=2`, `publication_id=2`, `trade_date=2026-02-18` |
| PASS verification | `php artisan market-data:replay:verify 2 storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2 --output_dir=storage/app/market-data/replay-determinism-runtime-proof/verify-pass` | PASS; `replay_id=2`, `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0` |
| FAIL verification | `php artisan market-data:replay:verify 2 storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-run-2-reason-code-mismatch --output_dir=storage/app/market-data/replay-determinism-runtime-proof/verify-fail` | FAIL as expected; `replay_id=3`, `comparison_result=MISMATCH`, `replay_status=FAIL`, `mismatch_count=1`, `REPLAY_FINAL_REASON_CODE_MISMATCH` |
| BLOCKED verification | invalid-BOM derived fixture and smoke broken/missing fixtures | BLOCKED as expected; `REPLAY_EXPECTED_PROOF_INCOMPLETE`, `REPLAY_FIXTURE_SCHEMA_MISMATCH`, and `replay_status=BLOCKED` were surfaced |
| Smoke suite | `php artisan market-data:replay:smoke 2 --fixture_root=storage/app/market-data/replay-fixtures --output_dir=storage/app/market-data/replay-determinism-runtime-proof/smoke --generate_runtime_valid_case` | PASS; `all_passed=1`, generated valid case `PASS`, reason-code mismatch `FAIL`, broken/missing fixture cases `BLOCKED` |
| Evidence export linkage | `php artisan market-data:evidence:export --replay_id=2 --trade_date=2026-02-18 --output_dir=storage/app/market-data/replay-determinism-runtime-proof/evidence-export-replay-2` | PASS; `replay_status=PASS`, `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=6` |
| Historical non-current runtime proof | explicit fixture with `--publication_id=<historical_publication_id>` after pointer has moved to a newer readable publication | LOCKED in this source ZIP; artifacts are present under `storage/app/market-data/full-production-ready/runtime/historical-replay/` and prove `replay_status=PASS`, `comparison_result=MATCH`, and `evidence_admission_state=ADMITTED_COMPLETE` |

## Fixture Identity

- Fixture path: `storage/app/market-data/replay-determinism-runtime-proof/fixtures/generated-valid-run-2`
- Fixture id: `valid_case`
- Fixture family: `runtime_generated_valid_case`
- Fixture schema: `replay_fixture_v2`
- Fixture source: `generated_from_run_2`
- Trade date: `2026-02-18`
- Run id: `2`
- Publication id/version: `2` / `2`
- Source mode: `manual_file`
- Coverage: `PASS`, ratio `0.986857`, threshold `0.980000`, expected `913`, available `901`, missing `12`
- Hashes: bars `f1d70d360d14cc9c63bf56bb35c0c08e78706084311d86dea8d3ed0a2ba9d06d`, indicators `ce9e7b7ef48981c8ff24fb4ff6102f51b307ff845ff10232f546e3bb8837d7ff`, eligibility `1e9ba399d5e7428d106c656664028e9ad35aae83405601154b02d980a8ecad6a`

## Replay Area Mapping

| Replay Area | Contract Required | Code Produces/Checks | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| replay command surface | verify/smoke/backfill/fixture generation/evidence export exposed | `VerifyReplayCommand`, smoke/backfill/generate commands | `OpsCommandSurfaceTest`, command static guards | `php artisan list market-data` and help commands PASS | none |
| replay fixture generation | deterministic manifest and expected files | `ReplayVerificationService::generateFixtureFromRun` | replay fixture generation tests/static guard | generated fixture path above | none |
| replay manifest | identity, source, version, file list, assertion layers | `loadFixturePackage` validates manifest and files | `ReplayDeterminismStaticGuardTest` | `manifest.json` generated with required fields | none |
| current publication lookup | pointer -> publication -> readable/sealed/success/coverage-pass | `findReadableCurrentPublicationForRun` path | historical/current static guards | PASS proof resolved publication `2` through current pointer | none |
| historical publication lookup | explicit run/publication/trade-date context only | `resolvePublicationForEvidenceAudit` under historical context | `ReplayHistoricalDeterminismHardeningStaticGuardTest`; service historical unit tests | historical fixture `run-2-publication-2` verifies non-current `publication_id=2` after pointer moved to newer `publication_id=5` | none |
| expected context | fixture must declare complete expected context | `buildExpectedContext`, `validateExpectedProofCompleteness` | replay service tests | PASS fixture expected context exported | none |
| actual context | runtime must build actual run/publication/pointer/source/coverage/hash context | `buildActualReplayState` | replay service tests | PASS evidence contains actual context | none |
| coverage comparison | state, ratio, threshold, expected/available/missing counts | `compareExpectedAndActual` | replay/coverage tests | PASS and FAIL outputs include coverage summary | none |
| publishability comparison | terminal and publishability state | `compareExpectedAndActual` | replay tests | PASS output `SUCCESS|READABLE` expected/actual | none |
| reason code comparison | reason-code count distribution | `compareReasonCodeCounts` | mismatch tests | FAIL proof returns `REPLAY_FINAL_REASON_CODE_MISMATCH` | none |
| source/provider comparison | source mode/name/provider/file identity if present | source context compare and manual-file policy mismatch | replay tests/static guard | PASS proof compares manual file source context | none |
| hash comparison | bars/indicators/eligibility hashes | artifact context compare | replay tests/static guard | PASS proof includes all three hashes | none |
| seal comparison | seal state and sealed metadata | seal context compare | replay tests/static guard | PASS proof includes `SEALED` state | none |
| correction lineage comparison | correction fields if present | correction context and lineage compare | replay correction/historical tests | not present in current runtime fixture | conditional runtime only |
| manual file policy comparison | manual file cannot bypass coverage/readability | `appendManualFilePolicyMismatches` | replay tests/static guard | PASS proof preserves manual source context | none |
| PASS result | deterministic match maps to `PASS` | `replayStatusForComparison` | replay service/repository/evidence tests | `replay_id=2` and smoke `replay_id=4` PASS | none |
| FAIL result | mismatch maps to `FAIL` | `replayStatusForComparison` | replay service/command tests | `replay_id=3` and smoke `replay_id=5` FAIL | none |
| BLOCKED result | missing fixture/context/runtime prerequisite maps to blocked command output | command catch path and smoke blocked cases | command/static guards | invalid/missing/broken fixture cases BLOCKED | none |
| result persistence | replay metric stores result fields | `ReplayResultRepository` writes `replay_status` | repository integration test | replay rows persisted for ids `2`, `3`, `4`, `5` | none |
| evidence export linkage | replay evidence references result/context/summary/status | `MarketDataEvidenceExportService::exportReplayEvidence` | `ReplayEvidenceExportServiceTest` | evidence export for `replay_id=2`, file_count `6` | none |
| command output | operator-readable result and mismatch reasons | verify/smoke/backfill commands | `OpsCommandSurfaceTest` | output includes `replay_status`, summaries, reason codes | none |
| audit docs entry | implementation ledger records runtime proof | `LUMEN_IMPLEMENTATION_STATUS.md` | audit docs guard | current session entry added | none |
| contract tracker entry | contract tracker records final rule and lock condition | `LUMEN_CONTRACT_TRACKER.md` | audit docs guard | current contract entry added | none |

## Validation

- `vendor/bin/phpunit tests/Unit/MarketData/ReplayVerificationServiceTest.php` -> OK (9 tests, 30 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayResultRepositoryIntegrationTest.php` -> OK (1 test, 15 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayBackfillServiceTest.php` -> OK (2 tests, 11 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplaySmokeSuiteServiceTest.php` -> OK (1 test, 10 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayEvidenceExportServiceTest.php` -> OK (1 test, 51 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayDeterminismStaticGuardTest.php` -> OK (5 tests, 163 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/ReplayHistoricalDeterminismHardeningStaticGuardTest.php` -> OK (6 tests, 70 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` -> OK (46 tests, 288 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (55 tests, 877 assertions)
- Sequential reruns after a parallel fixture-dir collision: `--filter "Evidence"` OK (55 tests, 1050 assertions), `--filter "Publication"` OK (109 tests, 1297 assertions), `--filter "Pointer"` OK (82 tests, 1164 assertions), `--filter "Coverage"` OK (70 tests, 788 assertions), `--filter "Correction"` OK (69 tests, 1358 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> OK (9 tests, 343 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` -> OK (9 tests, 343 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (169 tests, 3926 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (451 tests, 6642 assertions)

## Remaining Risk

- None for replay determinism runtime proof over current-readable generated fixtures and replay command/evidence linkage.
- Historical publication runtime verification is LOCKED for this source ZIP because the seeded readable historical publication fixture is included and verified under `storage/app/market-data/full-production-ready/runtime/historical-replay/`.
- This inventory closes replay determinism runtime proof for current-readable, mismatch, blocked-prerequisite, evidence-linkage, and historical non-current publication scopes. External/live provider operations and deployment-specific runtime matrix remain governed by their own contracts.


## Final Historical Non-Current Replay Runtime Proof Closure

Historical non-current replay proof is now LOCKED for this source ZIP.

Artifact paths:

- `storage/app/market-data/full-production-ready/runtime/historical-replay/fixtures/run-2-publication-2/manifest.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/verify-run-2-publication-2/replay_result.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/evidence_admission.json`
- `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/replay_result.json`

Required semantic proof:

- `publication_id=2`
- `publication_run_id=2`
- `publication_is_current=false`
- `historical_publication_allowed=true`
- `current_pointer_required=false`
- `current_pointer_status=NOT_CURRENT_POINTER`
- `replay_actual_resolution_mode=HISTORICAL_PUBLICATION_AUDIT`
- `replay_publication_scope=HISTORICAL_SEALED_PUBLICATION`
- `comparison_result=MATCH`
- `replay_status=PASS`
- `mismatch_count=0`
- `evidence_admission_state=ADMITTED_COMPLETE`

Final validation supplied:

- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (57 tests, 904 assertions).
- `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` -> OK (170 tests, 3950 assertions).
- Full `vendor/bin/phpunit tests/Unit/MarketData` -> OK (453 tests, 6671 assertions).
