# Behavioral Test Coverage Inventory

Current admission status: **HISTORICAL PRE-V2 INVENTORY / SUPERSEDED FOR STRATEGY CLOSURE**. Rows marked `LOCKED`, `YES`, or “no gap” below describe the legacy contract/test surface and cannot close the corrected semantic families in `Contract_Test_Matrix_LOCKED.md`. The current documentation strategy is ready; implementation proof remains open until the required V2 oracles execute on production paths.

## Status

[RELATED_CONTRACT] TEST_COVERAGE_BEHAVIORAL_CONTRACT

[LAST_UPDATED] 2026-05-07

[SESSION] Test Coverage Behavioral

[STATUS] HISTORICAL_LOCKED_LOCAL_PHPUNIT_PASS / NOT_CURRENT_V2_PROOF
[HISTORICAL_ENFORCEMENT_MARKER] ENFORCED_PENDING_LOCAL_PHPUNIT

## Rule

Market-data test coverage is only valid when the test proves runtime behavior and durable state, not just assertion volume, method-level implementation details, or mock-shaped return values. DB-backed integration tests are the primary proof for lifecycle-critical behavior. Unit tests and static guards are support proof only. Command-surface tests may use command/service mocks for operator output coverage, but those tests do not count as lifecycle proof unless they assert real DB/evidence/replay state through runtime-like execution.

## Behavioral coverage inventory

| Area | Critical Behavior | Existing Coverage | Mock Level | Runtime Proof | Gap Found | Action |
|---|---|---|---|---|---|---|
| source/manual/API boundary | Manual file remains local, API remains provider-backed, source identity/attempt telemetry is persisted and exported. | `EodBarsIngestServiceTest`, `LocalFileEodBarsAdapterTest`, `PublicApiEodBarsAdapterTest`, `MarketDataPipelineIntegrationTest::test_run_daily_api_source_timeout_degraded_hold_persists_attempt_context_in_run_event`, `MarketDataPipelineIntegrationTest::test_run_daily_manual_file_with_explicit_input_file_exports_source_context_in_run_evidence`, `MarketDataPipelineIntegrationTest::test_run_daily_api_success_after_retry_exports_source_context_in_run_evidence` | BOUNDARY_ONLY | YES | No lifecycle gap for source identity; external API boundary remains mock/fake by design. | Keep boundary mocking only for provider/file input. |
| manual import/promote split | Import-only must not become readable automatically; promote must enforce coverage before publication. | `MarketDataPipelineIntegrationTest::test_manual_file_import_only_writes_candidate_bars_without_finalize_or_pointer_switch`, `MarketDataPipelineIntegrationTest::test_manual_file_promote_from_imported_partial_dataset_enforces_coverage_gate_and_does_not_switch_pointer`, `ManualFilePolicyEnforcementStaticGuardTest` | NONE | YES | Prior coverage had static proof and command support, but lacked explicit DB-backed import-only/promote split proof. | Added integration tests. |
| coverage gate | Expected/available/missing/ratio/threshold must drive publishability/finalize/pointer. | `CoverageGateEvaluatorTest`, `MarketDataPipelineIntegrationTest::test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication`, `MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication`, `MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_without_fallback_finishes_not_readable_and_emits_coverage_reason_code`, `MarketDataPipelineIntegrationTest::test_finalize_blocked_without_universe_stays_not_readable_and_emits_blocked_coverage_reason_code`, `CoverageGateNoBypassStaticGuardTest` | NONE | YES | None for runtime coverage gate behavior. | Guard as primary lifecycle proof in pipeline integration. |
| finalize | Finalize must create only valid readable publication, block invalid candidates, preserve reason code, and stay idempotent. | `MarketDataPipelineIntegrationTest`, `FinalizeDecisionServiceTest`, `PublicationFinalizeOutcomeServiceTest`, `PublicationCurrentPointerReadinessStaticGuardTest` | NONE for integration; LOW for service unit | YES | None for core finalize runtime proof. | Keep service tests as support; pipeline integration is proof source. |
| publishability state | `SUCCESS`, `FAILED`, `HELD`, `READABLE`, `NOT_READABLE` must stay coherent across run/publication/pointer. | `MarketDataPipelineIntegrationTest`, `PublicationFinalizeOutcomeServiceTest`, `MarketDataInvariantGuardTest`, `CoverageGateNoBypassStaticGuardTest` | NONE for integration | YES | None for current scope. | Keep invariant/static guard as regression net. |
| publication repository | Repository must reject invalid current pointer targets and protect sealed publications. | `PublicationRepositoryIntegrationTest` | NONE | YES | None. | Continue DB-backed repository proof. |
| current pointer | Pointer may only target `READABLE + SUCCESS + SEALED + coverage PASS` publication. | `PublicationRepositoryIntegrationTest`, `ReadablePublicationReadContractIntegrationTest`, `MarketDataPipelineIntegrationTest`, `PublicationCurrentPointerReadinessStaticGuardTest` | NONE | YES | None. | Keep pointer resolver as authoritative read gate. |
| fallback | Fallback must use previous readable pointer-resolved publication only and never MAX/latest shortcut. | `MarketDataPipelineIntegrationTest`, `MarketDataInvariantGuardTest`, `ReadSideAntiBypassStaticContractTest`, `CoverageGateNoBypassStaticGuardTest` | NONE for integration | YES | None. | Keep negative malformed fallback tests. |
| correction lifecycle | Correction requires current readable baseline, preserves previous pointer on failure/unchanged, and only publishes valid changed candidate. | `MarketDataPipelineIntegrationTest`, `CorrectionRepositoryIntegrationTest`, `CorrectionEvidenceExportServiceTest`, `CorrectionCommandsTest`, `CorrectionLifecycleSafetyStaticGuardTest` | NONE for lifecycle integration; COMMAND_RUNTIME uses command mocks | YES | Command output proof is support only, not lifecycle source. | Keep pipeline integration as proof; command mocks remain support. |
| evidence export | Evidence must include run/source/coverage/artifact/publication/pointer/fallback/correction/lineage and be readable without DB spelunking. | `MarketDataEvidenceExportServiceTest`, `CorrectionEvidenceExportServiceTest`, `ReplayEvidenceExportServiceTest`, `MarketDataPipelineIntegrationTest::*_exports_source_context_*`, `EvidenceExportCompletenessStaticGuardTest` | UNIT_WITH_MOCK for focused export-shape tests; NONE in pipeline export-readback support | YES | No blocker after operator-local Evidence filter and focused evidence export tests passed. Service-level mocks remain support evidence, not lifecycle proof. | LOCKED with local validation; keep pipeline/export-readback and static guard as proof boundary. |
| replay verification | Replay must compare expected vs actual proof and fail with reason-coded mismatches. | `ReplayVerificationServiceTest`, `ReplayResultRepositoryIntegrationTest`, `ReplayEvidenceExportServiceTest`, `ReplayDeterminismStaticGuardTest` | UNIT_WITH_MOCK for replay evidence export support; NONE for repository persistence | YES | No blocker after operator-local Replay filter, replay verifier, replay static guard, and full suite passed. | LOCKED with replay determinism contract reconciliation. |
| read-side consumer | Consumers must read through pointer-resolved readable publication only and fail safe on missing/invalid pointer. | `ReadablePublicationReadContractIntegrationTest`, `ReadSideAntiBypassStaticContractTest`, `ConsumerSurfaceSweepStaticGuardTest` | NONE for integration | YES | None for repository consumers covered. | Keep static anti-bypass sweep. |
| command surface | Operator output must expose reason-coded summaries and artifact paths. | `OpsCommandSurfaceTest`, `CorrectionCommandsTest` | INTERNAL_MOCK_HEAVY | PARTIAL | Most command tests mock internal services/repositories and cannot prove lifecycle state by themselves. | Do not count command tests as lifecycle proof; add runtime command test later if feasible with local app container. |
| static guard | Static guards must prevent MAX/latest/raw/staging bypass and prevent proof drift. | `ReadSideAntiBypassStaticContractTest`, `CoverageGateNoBypassStaticGuardTest`, `ReplayDeterminismStaticGuardTest`, `EvidenceExportCompletenessStaticGuardTest`, `ManualFilePolicyEnforcementStaticGuardTest`, `TestCoverageBehavioralStaticGuardTest` | NONE | SUPPORT_ONLY | Static guard is not runtime proof. | Keep as regression guard only. |
| migration/schema | Runtime schema, SQLite schema, and SQL docs must remain aligned for lifecycle fields. | `MarketDataSqliteSchemaSyncTest`, migrations, `docs/market_data/db/Database_Schema_MariaDB.sql` | NONE | YES | None found in this static session. | Validate locally with migration and full MarketData suite. |
| reason code propagation | Failure/held/replay/coverage/correction must expose reason code consistently. | `Reason_Codes_Registry.md`, `Reason_Codes_Seed.sql`, `ReplayDeterminismStaticGuardTest`, integration tests checking event reason codes | NONE | YES | None for current static scope. | Keep reason-code registry/seed scan. |
| full pipeline integration | End-to-end ingest/import/promote/finalize/coverage/pointer/correction flows must be DB-backed. | `MarketDataPipelineIntegrationTest` | NONE | YES | Prior gap: explicit import-only/promote split. | Added two integration tests. |

## Existing behavioral proof source

Primary lifecycle proof must come from:

- `MarketDataPipelineIntegrationTest`
- `PublicationRepositoryIntegrationTest`
- `ReadablePublicationReadContractIntegrationTest`
- `CorrectionRepositoryIntegrationTest`
- `ReplayResultRepositoryIntegrationTest`
- `MarketDataSqliteSchemaSyncTest`

## Mock-heavy tests that must not be counted as lifecycle proof

The following tests are still useful, but only as support/surface tests:

- `OpsCommandSurfaceTest` because it mocks `MarketDataPipelineService`, `MarketDataBackfillService`, `SessionSnapshotService`, `ReplaySmokeSuiteService`, `ReplayBackfillService`, and evidence repositories.
- `MarketDataEvidenceExportServiceTest` because it mocks persistence repositories for focused export-shape assertions.
- `ReplayEvidenceExportServiceTest` because it mocks evidence/publication/correction repositories for focused replay export assertions.
- `ReplaySmokeSuiteServiceTest` and `ReplayBackfillServiceTest` because they mock replay/evidence services for orchestration behavior.
- `SessionSnapshotServiceTest` because it mocks publication/run/snapshot repositories and local adapter.
- `MarketDataBackfillServiceTest` because it mocks pipeline service for orchestration failure behavior.

## Final mock policy

Allowed mocks:

- external provider API
- file system fixture adapter where isolation is required
- clock/time provider
- console IO and command surface output
- explicit orchestration shell tests that do not claim lifecycle proof

Not accepted as behavioral proof:

- mocked internal repository lifecycle
- mocked internal service return state for finalize/pointer/correction/replay/evidence proof
- command output test without DB/proof state assertion
- static guard alone

## Final validation status

Operator-local targeted, filtered, focused-file, static guard, integration, command-surface, replay/evidence/read-side, and full MarketData PHPUnit validation passed.

- implementation: `DONE`
- contract: `LOCKED`
- full suite: `vendor/bin/phpunit tests/Unit/MarketData` -> 298 tests / 3327 assertions

Command-surface tests still remain support-only where they use internal mocks; they are not reclassified as primary lifecycle proof.
