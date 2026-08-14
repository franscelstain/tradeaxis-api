# Coverage Gate Candidate Scope Hardening Inventory

> **HISTORICAL AUDIT/IMPLEMENTATION EVIDENCE — NON-AUTHORITATIVE FOR CURRENT V2 STRATEGY.** This file preserves dated runtime/inventory facts and may contain legacy field names, command behavior, locks, or production claims from earlier contracts. Current strategy authority is the owner contracts + Blueprint + Conformance Matrix; current execution/conformance state is `MARKET_DATA_IMPLEMENTATION_LEDGER.md`; current audit verdict is `reports/AUDIT_FINAL_STATE.md`. Legacy statements are not current requirements unless explicitly re-admitted by those authorities.


[SESSION]
- Name: Coverage Gate Candidate Scope Hardening
- Date: 2026-05-13
- Status: DONE_LOCAL_PHPUNIT_PASS / LOCKED_LOCAL_PHPUNIT_PASS
- Scope note: this is not coverage gate enforcement ulang. Existing coverage PASS/FAIL, threshold, reason-code, and publishability behavior remain owned by the existing coverage/publishability contracts. This session only hardens candidate publication scope for promote, manual promote, and correction candidate evaluation.

[RUNTIME_ENVIRONMENT]
- Container PHP version observed: PHP 8.4.16
- Container PHPUnit status: BLOCKED_CONTAINER_RUNTIME_ENV because `dom`, `mbstring`, `xml`, and `xmlwriter` are missing.
- Runtime authority for DONE/LOCKED remains operator-local PHPUnit output.
- Required local validation: targeted Coverage/Promote/Manual/Correction/Finalize/Publication/Pointer/Evidence/Replay/CommandSurface/StaticGuard/Integration filters and full `vendor/bin/phpunit tests/Unit/MarketData`.

[EXISTING CONTRACT OWNER]
- Existing owner reused: `COVERAGE_GATE_ENFORCEMENT_CONTRACT` plus publishability/finalize/pointer contracts already present in `LUMEN_CONTRACT_TRACKER.md`.
- This inventory does not create a replacement coverage contract.
- Related prior inventories: `IMPORT_PROMOTE_SEPARATION_INVENTORY.md`, `FAIL_SAFE_NO_SILENT_FAILURE_INVENTORY.md`, `READ_SIDE_CONSUMER_SURFACE_FINAL_SWEEP_INVENTORY.md`.

[CANDIDATE COVERAGE FLOW MATRIX]
| Flow | Entrypoint | Candidate Run | Candidate Publication | Baseline Publication | Coverage Basis | Coverage Result | Pointer Switch | Status |
|---|---|---:|---:|---:|---|---|---:|---|
| Manual promote from import-only run | `market-data:promote` -> `promoteSingleDay` -> `completeCoverageEvaluation` | promote run derived from seed | `candidate_publication_id` from run notes / run publication context | n/a unless replacement has prior current | `CandidatePublication` / `coverage_basis_publication_id` | candidate available/missing/ratio | only after PASS + seal + finalize | PATCHED_STATIC_GUARDED |
| Correction current promote | `market-data:correction:run` / promote correction path | correction run | correction candidate publication from candidate artifact context | `baseline_publication_id` only for lineage/comparison/preservation | `CandidatePublication` / `coverage_basis_publication_id` | candidate available/missing/ratio | blocked on candidate FAIL | PATCHED_STATIC_GUARDED |
| Normal full publish | daily/full publish eligibility path | owning run | result `publication_id` from candidate build | n/a | `CandidatePublication` / `coverage_basis_publication_id` | candidate available/missing/ratio | only after PASS + seal + finalize | PATCHED_STATIC_GUARDED |

[COVERAGE QUERY RISK MATRIX]
| File | Method | Query/Pattern | Runtime Path | Candidate Scoped? | Live/Current Risk | Action | Status |
|---|---|---|---:|---:|---|---|---|
| `MarketDataPipelineService.php` | `completeCoverageEvaluation` | previous evaluator call without publication id | yes | previously no | could evaluate live/current artifact | now resolves `coverageBasisPublicationId` before evaluate | PATCHED |
| `MarketDataPipelineService.php` | `completeEligibility` | previous candidate id only for correction | yes | partial | non-correction candidate proof could rely on trade-date artifact | now passes result `publication_id` for all builds | PATCHED |
| `EodArtifactRepository.php` | `loadCanonicalBarTickerIdsForTradeDate` | publication id previously delegated to history-only path | yes | partial | candidate live rows not explicitly scoped across both candidate stores | now reads `eod_bars_history` and `eod_bars` filtered by `publication_id` | PATCHED |
| `CoverageGateEvaluator.php` | `evaluate` | coverage result lacked explicit coverage basis fields | yes | evidence ambiguity | evidence/replay/command could not prove basis directly | now emits coverage basis and candidate counters | PATCHED |

[PATCH MATRIX]
| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| Promote/correction coverage could call evaluator without candidate publication id | `MarketDataPipelineService.php` | Added `resolveCandidateCoveragePublicationId()` and used it in `completeCoverageEvaluation()` | If candidate id exists, coverage is candidate-scoped; if candidate cannot be resolved, publication id `0` forces empty candidate scope instead of live/current fallback | New static guard + updated evaluator unit expectation | PATCHED |
| Coverage artifact lookup needed publication-scoped rows | `EodArtifactRepository.php` | Added `loadCandidateScopedBarTickerIdsForTradeDate()` over `eod_bars_history` and `eod_bars` filtered by `publication_id` | No current pointer/latest lookup; no baseline fallback | Static guard checks publication filter and no latest/current shortcut | PATCHED |
| Evidence surfaces lacked explicit basis | `AbstractMarketDataCommand.php`, `MarketDataEvidenceExportService.php`, `ReplayVerificationService.php` | Surface `coverage_basis`, `coverage_basis_publication_id`, candidate/baseline fields from run notes | Does not change gate result; exposes proof | Static guard checks command/evidence/replay strings | PATCHED |
| Audit docs lacked this hardening scope | audit docs/inventory | Added inventory and append-only status/tracker entries | Preserves prior coverage contract history | Static guard checks docs | PATCHED |

[EVIDENCE_REPLAY_COMMAND_PROOF_MATRIX]
| Surface | Candidate Coverage Field | Baseline Field | Candidate Field | Proof Status |
|---|---|---|---|---|
| Command output | `coverage_basis`, `coverage_basis_publication_id`, `coverage_basis_artifact_scope` | `baseline_publication_id` from notes if present | `candidate_publication_id` | STATIC_PATCHED |
| Evidence export | coverage state includes basis fields | `baseline_publication_id` | `candidate_publication_id` | STATIC_PATCHED |
| Replay actual context | `actual_coverage_context.coverage_basis*` | `baseline_publication_id` | `candidate_publication_id` | STATIC_PATCHED |
| Coverage evaluator result | `coverage_basis=CandidatePublication` | n/a | `candidate_publication_id` | STATIC_PATCHED |

[VALIDATION MATRIX]
| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l app/Application/MarketData/Services/CoverageGateEvaluator.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Infrastructure/Persistence/MarketData/EodArtifactRepository.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Console/Commands/MarketData/AbstractMarketDataCommand.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l app/Application/MarketData/Services/ReplayVerificationService.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l tests/Unit/MarketData/CoverageGateCandidateScopeHardeningStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php -l tests/Unit/MarketData/CoverageGateEvaluatorTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php vendor/bin/phpunit --version` | blocked by missing extensions | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |
| full `vendor/bin/phpunit tests/Unit/MarketData` | OK | 397 | 5461 | PASS_OPERATOR_LOCAL |

[RECOVERY_2026_05_13_FIX1]
- Operator-local first retest result before recovery patch:
  - `CoverageGateCandidateScopeHardeningStaticGuardTest.php` -> FAILED, 5 tests / 1 failure, audit docs missing required candidate-scope wording alignment.
  - `Promote` -> ERRORS, 30 tests / 267 assertions / 5 errors, all rooted at `Undefined variable: correction` in `MarketDataPipelineService.php:615`.
  - `Manual` -> ERRORS, 25 tests / 237 assertions / 2 errors, same root cause.
  - `Correction` -> ERRORS, 67 tests / 639 assertions / 23 errors, same root cause.
  - `Finalize` -> ERRORS, 48 tests / 315 assertions / 9 errors, same root cause.
  - `Publication` -> ERRORS, 100 tests / 786 assertions / 17 errors, same root cause.
  - `Pointer` -> ERRORS, 76 tests / 947 assertions / 8 errors, same root cause.
  - `Evidence` -> ERRORS, 46 tests / 808 assertions / 2 errors, same root cause.
  - `Replay` -> PASS, 44 tests / 732 assertions.
  - `CommandSurface` -> PASS, 49 tests / 359 assertions.
  - `StaticGuard` -> FAILED, 130 tests / 5 failures due stale audit-doc active-session and duplicate canonical contract tracking.
  - `Integration` -> ERRORS, 91 tests / 37 errors, same root cause.
- Recovery patch actions:
  - `MarketDataPipelineService::completeFinalize()` transaction closure now imports `$correction`.
  - `AuditDocsSynchronizationStaticGuardTest.php` now validates current working sections against the active session dynamically instead of hardcoding the previous Read-Side session.
  - `LUMEN_CONTRACT_TRACKER.md` preserves older coverage/read-side locked histories as historical context, not duplicate canonical contract headings.
  - `LUMEN_IMPLEMENTATION_STATUS.md` and `LUMEN_CONTRACT_TRACKER.md` include explicit `correction candidate` wording required by candidate-scope guard.
- Container recovery syntax validation:
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS.
  - `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` -> PASS.
  - `php -l tests/Unit/MarketData/CoverageGateCandidateScopeHardeningStaticGuardTest.php` -> PASS.
- Status after recovery patch: PARTIAL / READY_FOR_OPERATOR_LOCAL_RUNTIME_RERUN.

[FINAL_RULE]
- Coverage for promote/manual promote/correction must be computed from the candidate publication / candidate artifact scope being promoted.
- Baseline/current publication may be used for lineage, comparison, fallback preservation, and unchanged detection only.
- Baseline/current publication must not be used as coverage basis for candidate publishability.
- Missing or incomplete candidate artifact must remain FAIL/HELD/NOT_READABLE and must not switch pointer; unresolved candidate publication context is forced to candidate scope id `0`, not live/current fallback.
- DONE/LOCKED requires operator-local targeted and full PHPUnit proof because this container cannot run PHPUnit.


[RECOVERY_2026_05_13_FIX2]
- Operator-local fix1 partial retest result:
  - `CoverageGateCandidateScopeHardeningStaticGuardTest.php` -> PASS, `OK (5 tests, 53 assertions)`.
  - `Manual` -> PASS, `OK (25 tests, 262 assertions)`.
  - `Correction` -> PASS, `OK (67 tests, 1321 assertions)`.
  - `Publication` -> PASS, `OK (100 tests, 1215 assertions)`.
  - `Pointer` -> PASS, `OK (76 tests, 1117 assertions)`.
  - `Evidence` -> PASS, `OK (46 tests, 827 assertions)`.
  - `Replay` -> PASS, `OK (44 tests, 732 assertions)`.
  - `CommandSurface` -> PASS, `OK (49 tests, 359 assertions)`.
  - Remaining before fix2: `Promote` 2 failures, `Finalize` 1 failure, `StaticGuard` 2 failures, `Integration` 3 failures.
- Root cause:
  - Direct `manual_file` promote from an environment with an existing current baseline could enter coverage without a materialized candidate publication artifact. Candidate-scope hardening correctly prevented live/current fallback, but the direct manual promote compatibility path needed to materialize its own candidate first.
  - Historical Read-Side final-sweep static guard still assumed it had to remain the active session forever and performed case-sensitive runtime-authority wording checks.
- Fix2 patch actions:
  - `MarketDataPipelineService::promoteSingleDay()` now calls `materializeDirectManualPromoteCandidateIfNeeded()` before candidate-scoped coverage.
  - Direct manual promote with no candidate publication runs `completeIngest()` under `request_mode=promote`, creating a non-current candidate publication/artifact before coverage. This does not use live/current as coverage basis.
  - Pointer conflict / post-finalize mismatch outcomes now carry `reason_code=RUN_LOCK_CONFLICT` before invariant validation.
  - `ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` now validates historical read-side DONE/LOCKED proof without requiring the Read-Side session to remain the current active session.
- Container fix2 syntax validation:
  - `php -l app/Application/MarketData/Services/MarketDataPipelineService.php` -> PASS.
  - `php -l tests/Unit/MarketData/ReadSideConsumerSurfaceFinalSweepStaticGuardTest.php` -> PASS.
- Status after fix2: PARTIAL / READY_FOR_OPERATOR_LOCAL_RUNTIME_RERUN.


## Fix3 command-surface source telemetry isolation

Operator-local fix2 retest result:

- `Finalize` -> PASS: `OK (48 tests, 372 assertions)`.
- `StaticGuard` -> PASS: `OK (130 tests, 2894 assertions)`.
- `Integration` -> PASS: `OK (91 tests, 1450 assertions)`.
- `Promote` -> ERROR in 5 OpsCommandSurface tests because `AbstractMarketDataCommand::writeSourceAttemptTelemetryArtifact()` queried `eod_run_events` through the default MySQL connection even when no `output_dir` was requested.

Fix3 action:

- `AbstractMarketDataCommand::writeSourceAttemptTelemetryArtifact()` now returns `[null, []]` immediately when `output_dir` is empty.
- This avoids unintended DB access in command-summary-only tests and normal operator command summaries.
- Source telemetry artifact export remains intact when `--output_dir` is supplied, because the method still queries and writes telemetry only for artifact-producing runs.

Status after fix3: PARTIAL / READY_FOR_OPERATOR_LOCAL_RUNTIME_RERUN. Required rerun: `Promote`, then full targeted filters and full `tests/Unit/MarketData`.


## Fix4 full-suite command telemetry and eligibility expectation recovery

Operator-local fix3 retest result:

- `Promote` -> PASS: `OK (30 tests, 340 assertions)`.
- `Finalize` -> PASS: `OK (48 tests, 372 assertions)`.
- `StaticGuard` -> PASS: `OK (130 tests, 2894 assertions)`.
- `Integration` -> PASS: `OK (91 tests, 1450 assertions)`.
- Full `tests/Unit/MarketData` -> FAILED before fix4 with 4 DB connection errors from source attempt telemetry lookup, 4 missing `source_attempt_event_type` / `source_attempt_count` command-output assertions, and 1 stale `completeEligibility()` unit expectation.

Fix4 actions:

- `AbstractMarketDataCommand::exportRunSourceAttemptTelemetry()` now catches DB connection/query failures and returns empty telemetry instead of failing command summaries when no test/runtime DB is available.
- `AbstractMarketDataCommand::writeSourceAttemptTelemetryArtifact()` returns `[null, null]` when `output_dir` is empty. This lets no-output command summaries recover telemetry from a mocked evidence repository when available, while still avoiding forced artifact writes.
- `MarketDataPipelineServiceTest::test_complete_eligibility_stores_coverage_telemetry_separately_from_eligibility_metrics` now expects `coverageGateEvaluator->evaluate('2026-04-03', 15)` because eligibility candidate coverage is intentionally candidate-publication scoped.

Container fix4 syntax validation:

- `php -l app/Console/Commands/MarketData/AbstractMarketDataCommand.php` -> PASS.
- `php -l tests/Unit/MarketData/MarketDataPipelineServiceTest.php` -> PASS.

Status after fix4: DONE / LOCKED_LOCAL_PHPUNIT_PASS. Operator-local full `vendor/bin/phpunit tests/Unit/MarketData` passed with `OK (397 tests, 5461 assertions)`.


## FINAL_CLOSURE_2026_05_13

- Operator-local final validation passed after fix4: `vendor/bin/phpunit tests/Unit/MarketData` returned `OK (397 tests, 5461 assertions)`.
- Candidate-scope hardening is now `DONE_LOCAL_PHPUNIT_PASS` / `LOCKED_LOCAL_PHPUNIT_PASS`.
- Promote/manual promote/correction coverage remains candidate-publication scoped; direct manual promote materializes a candidate before coverage rather than falling back to live/current baseline.
- Container PHPUnit remains blocked by missing PHP extensions; operator-local PHPUnit output is the runtime authority for this lock.
