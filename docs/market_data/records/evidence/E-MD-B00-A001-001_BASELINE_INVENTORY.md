# MD Stage Attempt Record — E-MD-B00-A001-001

- Evidence ID: `E-MD-B00-A001-001`
- Stage / Attempt / Baseline / Epoch: `MD-B00` / `MD-B00-A001` / `MD-B00-A001-BL001` / `MD-REBASELINE-20260820-001`
- Source revision: `dd6ca2a2e56ad4b1bef30467209d6c592eb572f9` (branch `master`, working tree `CLEAN`)
- Role: `EVIDENCE`, scope `ISSUED_EVIDENCE`, mutability `IMMUTABLE_AFTER_ISSUE`
- Companion machine-readable proof: `E-MD-B00-A001-002`

## Objective

Adopt current Market Data strategy and governance as the implementation baseline, keep `authority/strategy/` unchanged, confirm that every pre-epoch verdict stays historical-only, inventory existing code/tests/contracts/artifacts/residue, map that inventory onto `MD-B01..MD-B22`, and leave revalidation legitimately openable.

## Previous gap / do-not-repeat

No prior `MD-Bxx` attempt exists; this is the first record under the active epoch. The historical corpus was read as input only.

Three do-not-repeat lessons were carried in from the legacy corpus and all three proved live at this baseline:

1. A guard that parses a file does not prove the file works. The reason-code seed guard passed for an extended period while the seeder failed on every execution because of a trailing comma before `ON DUPLICATE KEY UPDATE`. It was caught only when `ReasonCodeSeedExecutionTest` executed the statement instead of reading it.
2. Changing one side of a two-sided binding breaks the other silently. This is exactly what `D-MD-20260820-01` and `D-MD-20260820-02` did to every path binding in executable code.
3. `LOCKED` marks authority, not correctness. An artifact that weakens data validity may be corrected, but only with proof.

## Existing work inspected

Read in full or in relevant part, at the locked revision:

- Governance: all 42 documents under `authority/governance`, including the epoch, freeze manifest, role/verification/ID registries, traceability matrix, and every standard.
- Strategy: the 91 frozen documents were treated as read-only authority. No strategy byte was read for modification and none was modified. `STRATEGY_FREEZE` verifies 91/91 unchanged.
- Implementation: `MD_IMPLEMENTATION_STAGE_REGISTER.md`, `MD_IMPLEMENTATION_BUILD_SEQUENCE.md`, `MD_DEPENDENCY_REGISTRY.csv`, `CURRENT_STATE.md`, the seven governance tool scripts, and the `db` and `tests/specs` trees.
- Code: 143 PHP files under `app`, 62 migrations, 1 seeder, `config/market_data.php`, `app/Console/Kernel.php`, and `tools/market_data`.
- Tests: all 169 test files plus the three support traits.
- Records: the current findings/decisions/evidence set, the 255-row legacy source index, the split and reconstruction indexes, and the 201-file historical archive.
- Environment: the reachable MariaDB corpus, read-only.

## Changes

No runtime code, schema, config, test, or strategy document was changed. This attempt produced records only: one baseline lock, two evidence records, four findings, three dependency rows, registry rows, the stage-register row for `MD-B00`, and a regenerated `CURRENT_STATE.md`.

## Tests and negative tests

**Documentation integrity gate — PASS**, exit 0, all twelve checks green: root architecture, one-document-one-role (731/731), strategy freeze (91/91), verification rebaseline, JSON parse, CSV structure, active markdown links, traceability matrix, legacy semantic split integrity (43/43 sources reconstructed), Windows-safe paths, exact duplicate files, no legacy root areas.

**Relationship integrity gate — PASS**, exit 0, zero errors.

**Negative gate proof.** Exit 0 was not accepted as proof. The full `docs/market_data` tree was copied to an isolated scratch location and nine deliberate mutations were applied to the copy, one at a time, with the gate executed after each and the copy restored. The repository was never mutated. Eight of nine fail closed:

| Mutation | Gate | Expected | Observed |
|---|---|---|---|
| control, unmutated | both | PASS | PASS |
| unregistered physical document added | documentation | FAIL | FAIL |
| frozen strategy byte mutated | documentation | FAIL | FAIL |
| legacy evidence flipped to `current_proof_eligible=YES` | documentation | FAIL | FAIL |
| legacy split extract body tampered inside seal | documentation | FAIL | FAIL |
| broken active markdown link introduced | documentation | FAIL | FAIL |
| `work_id` differs from `attempt_id` | relationship | FAIL | FAIL |
| malformed attempt ID shape | relationship | FAIL | FAIL |
| relationship references a non-existent record | relationship | FAIL | FAIL |
| text appended after extract body-end marker | documentation | FAIL | **PASS** |

The single gap is recorded as `F-MD-B00-A001-003` and assessed as low severity.

**PHPUnit suite — RED.** 1488 tests, 8828 assertions, 26 errors, 108 failures, 0 skipped, 1354 passed, exit 2, 4m53s on PHPUnit 9.6.34 / PHP 7.4.33.

All 134 failing methods trace to a single root cause and none indicates a business-logic defect. See `F-MD-B00-A001-001`.

## Strategy coverage

`MD-B00` required coverage is `0/0`. No traceability rule names `MD-B00` as primary stage, so the requirement is satisfied vacuously and no rule moved to `SATISFIED` at this attempt.

Matrix totals at this baseline: 6490 rows, 1407 required mandatory/conditional, 0 satisfied, 1407 not assessed, 54 optional not requested, 5029 reference-only.

Conformance-matrix assignment, which is the second half of the `MD-B00` exit intent, is **SATISFIED**: all 91 frozen strategy documents carry traceability rows, zero have none, and all 731 physical documents are registered in both the role registry and the current verification registry.

## Residue verdict

- `MD-B00` own scope: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`. This attempt added records and changed no reachable behavior.
- Discovered outside `MD-B00` scope: `NON_CONFORMANT_HARMFUL_RESIDUE_OPEN`, attributed to owning stages through the findings and dependency rows below rather than absorbed here.

## Mapping: existing implementation to MD-B01..MD-B22

Classification vocabulary is as requested. `REUSABLE_PENDING_REVALIDATION` means substantial existing work is present and its executable tests currently pass, so it is a credible revalidation candidate — it does **not** mean conformant. Nothing in this table is current PASS.

| Stage | Principal existing implementation | Broken test files | Classification |
|---|---|---|---|
| `MD-B01` | `app/Domain/MarketData/MarketDataScope.php`; `TerminologyOwnerVocabularyTest`, `RequestModeVocabularyTest` | 1 | `PARTIALLY_CONFORMANT` |
| `MD-B02` | `Ports/ApiEodBarsSource`, `Ports/ManualEodBarsSource`, `PublicApiEodBarsAdapter`, `LocalFileEodBarsAdapter`, `EquityProviderSymbolResolver`, `BenchmarkProviderSymbolResolver`, `ProviderSmokeCommand` | 2 | `PARTIALLY_CONFORMANT` |
| `MD-B03` | 62 migrations, `MarketDataReasonCodesSeeder`, 22 repositories under `Infrastructure/Persistence/MarketData`, `tests/Support/UsesMarketDataSqlite.php` | 8 | **`HARMFUL_RESIDUE`** |
| `MD-B04` | `config/market_data.php`, `MarketDataConfigSnapshotRepository`, `ConfigIdentityBindingTest`, `ConfigIsTheOnlyEnvReaderTest` | 1 | `PARTIALLY_CONFORMANT` |
| `MD-B05` | `TemporalIdentityRepository`, `TickerMasterRepository`, `SectorClassificationRepository`, `ImportSectorMembershipCommand`, `BackfillMissingTickersCommand` | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B06` | `MarketCalendarRepository`, `TemporalTradingStatusRepository`, `AuthoritativeTradingStatusSnapshotService`, `AuthoritativeExchangeMarketStructureService`, `ImportTradingStatusEventsCommand` | 2 | `PARTIALLY_CONFORMANT` |
| `MD-B07` | `SourceObservationRecorder`, `SourceObservationRepository`, `InMemorySourceObservationRecorder`, `EodBarsIngestService`, `ApiBackfillRangeAcquisitionService` | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B08` | `SourceAcquisitionException`, `SourceCircuitBreakerTest`, `SourceFailureResilienceTest`, resilience paths in the ingest services | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B09` | `PromoteMarketDataCommand`, `EodArtifactRepository`, `CanonicalRawImportBoundaryTest`, `EodBarsIngestService` | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B10` | `EodPublicationRepository`, `PublicationFinalizeOutcomeService`, `PublicationDiffService`, `PublicationGovernanceBindingService`, `SealDatasetCommand`, `FinalizeRunCommand`, the five correction commands, `EodCorrectionRepository` | 1 | `PARTIALLY_CONFORMANT` |
| `MD-B11` | `CorporateActionDerivationService`, `AuthoritativeCorporateActionTermsService`, `PriceScaleBreakDetectionService`, `PriceScaleStretchRepairService`, `DeriveCorporateActionsCommand` | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B12` | `AdjustmentFactorSetService`, `AnalyticalProductIdentityService`, `CoherentPriceProductBoundaryTest` | 1 | `PARTIALLY_CONFORMANT` |
| `MD-B13` | `ActualVersusProxyMetricBoundaryTest`, daily-metric fields in `EodIndicatorsComputeService` | 0 | `REUSABLE_PENDING_REVALIDATION` |
| `MD-B14` | `EodIndicatorsComputeService`, `IndicatorVectorService`, `BenchmarkIndicatorComputeService`, `BenchmarkIndicatorVectorService`, `RecomputeCurrentIndicatorsCommand`, `MarketDataImpactReprocessExecutor` | 1 | `PARTIALLY_CONFORMANT` |
| `MD-B15` | `CoverageGateEvaluator`, `CoverageGateStateNormalizer`, `EligibilitySnapshotScopeRepository` | 3 | `PARTIALLY_CONFORMANT` |
| `MD-B16` | `EligibilityDecisionService`, `EodEligibilityBuildService`, `BuildEligibilityCommand`, `EventRiskSourceRepository` | 0 | `SUSPECTED_NON_CONFORMANT` |
| `MD-B17` | `MarketDataReadProductService`, `MarketDataReadinessService`, `MarketDataPriceReadService`, `MarketBenchmarkReadService`, `NoReadablePublicationException` | 3 | `PARTIALLY_CONFORMANT` |
| `MD-B18` | `ReplayVerificationService`, `ReplayBackfillService`, `ReplaySmokeSuiteService`, `FullRangeCurrentEvidenceReplayService`, `ReplayResultRepository`, `VerifyReplayCommand` | 4 | `PARTIALLY_CONFORMANT` |
| `MD-B19` | `DailyPipelineCommand`, `MarketDataPipelineService`, `MarketDataEvidenceExportService`, `BackfillLifecycleOrchestrator`, `EodRunRepository`, `EodEvidenceRepository`, `Console/Kernel.php` schedule, 39 commands | 8 | `PARTIALLY_CONFORMANT` |
| `MD-B20` | `SessionSnapshotService`, `SessionSnapshotRepository`, `CaptureSessionSnapshotCommand`, `PurgeSessionSnapshotCommand`, `LocalFileSessionSnapshotAdapter` | 0 | `HISTORICAL_ONLY` pending activation |
| `MD-B21` | `MarketDataSqliteSchemaSyncTest`, `MigrationIntegrityAndDriftTest`, `GlobalConvergenceClosureTest`, `DestructiveMigrationGuardTest` | 2 | **`HARMFUL_RESIDUE`** |
| `MD-B22` | `ProductionCorpusInvariantOracleTest`, `AuditCrossReferenceIntegrityTest`, `ProductionValidationRuntimeProofStaticGuardTest` | 3 | `SUSPECTED_NON_CONFORMANT` |

Broken test files total 40, distributed as above; 31 bind to at least one relocated (Class R) path and 9 bind only to split-and-sealed (Class S) paths.

### Why `MD-B03` and `MD-B21` are `HARMFUL_RESIDUE` and not merely partial

`MD-B03` owns the clean-install path, the reason registry, and the test harness. All three are damaged. `php artisan migrate` cannot pass migration 3 of 62. `MarketDataReasonCodesSeeder` throws. 29 of 436 seed reason codes are absent from the deployed table, and 3431 live `eod_eligibility` rows dated 2023-06-13 to 2026-07-07 already carry `ELIG_TRADING_SUSPENDED`, which has no registry row and no foreign key that would have stopped it.

`MD-B21` owns convergence. The SQLite mirror in `tests/Support/UsesMarketDataSqlite.php` is 1523 hand-written lines rather than a derivation of the canonical schema document, and every test that cross-checks the two is disabled by the same dead path. The 1354 passing tests therefore run against a schema nothing currently proves correct.

### Why `MD-B16` and `MD-B22` are `SUSPECTED_NON_CONFORMANT`

`MD-B16` has zero broken test files, so its suite is green — but its exit intent requires that the compatibility `eligible` field carry no tradability or watchlist policy, and the only artifacts asserting that boundary are among the disabled static guards owned by `MD-B17`. Green here is unearned rather than proven.

`MD-B22` is an audit stage whose own instruments read documents that no longer exist. Its historical verdicts are among the composites that were split and sealed, so its evidence base is `HISTORICAL_ONLY` by construction.

### `HISTORICAL_ONLY`

- The 201-file `records/history` archive, 428 `LX-MD-*` role-pure extracts, 34 legacy decisions, and 127 legacy evidence records. Every one carries `current_proof_eligible=NO`.
- `tools/market_data/session54_local_phpunit_proof.ps1`, a legacy per-session proof harness with no current stage owner.
- `MD-B20` session snapshot: strategy declares it optional and no activation is declared, so it stays `OPTIONAL_NOT_REQUESTED` across all 30 optional rules and blocks nothing.

### `DEAD/UNREACHABLE`

All 45 documentation paths bound from executable code, of which 26 have no repoint target. `MarketDataRelationshipIntegrityGateSelfTest.php` is reachable but inert: it prints nine `PASS` lines and asserts nothing.

## Dependencies

Three dependency rows were opened; none is external to this repository, and none authorizes a local semantic substitution.

- `MD-DEP-0001` — `MD-B03` clean-install and reason-registry rebinding blocks `MD-B21` convergence proof.
- `MD-DEP-0002` — `MD-B03` schema-mirror cross-check must be restored before any stage may cite SQLite-backed tests as schema-conformant evidence.
- `MD-DEP-0003` — the 9 Class S test files need a stage-owned decision to retire or rewrite; they cannot be repointed.

## Integrity and relationship gates

Both PASS at the locked revision, and both are proven to fail closed on eight of nine mutations. Full matrix in `E-MD-B00-A001-002`.

## Convergence

Not applicable at `MD-B00`. Convergence is owned by `MD-B21`, and `MD-DEP-0001` and `MD-DEP-0002` block it today.

## Outcome

`MD-B00` exit intent is met on both clauses. The current code, schema, test, and evidence baseline is recorded, including the parts that are red, and every active document carries a conformance-matrix assignment.

This closure records a baseline; it does not certify the implementation. All 1407 required strategy rules remain `NOT_ASSESSED`, and every existing artifact remains `NOT_ASSESSED_REVALIDATION_REQUIRED`. No historical PASS was inherited.

## Remaining gap / exact resume point

Resume at `MD-B01`, attempt `MD-B01-A001`, scope `W01` — scope, boundary, dataset start, development frontier, activation semantics, and non-goals. Required coverage is 127 mandatory/conditional rules across 500 matrix rows, all currently `NOT_ASSESSED`. Primary authority is `authority/strategy/MARKET_DATA_PLATFORM_EOD_BASELINE.md` and `authority/strategy/book/Terminology_and_Scope.md`.

`MD-B01` may open immediately. Its single broken test file, `TerminologyOwnerVocabularyTest`, is Class R and rebindable within the stage, and `MD-DEP-0001..0003` do not gate it.

`MD-B03` should be scheduled early regardless of stage order. It is the only stage whose residue is actively producing unexplainable rows in the live corpus.
