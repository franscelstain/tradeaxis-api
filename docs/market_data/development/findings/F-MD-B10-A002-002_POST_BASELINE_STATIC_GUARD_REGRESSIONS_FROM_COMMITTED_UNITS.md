# Finding - Static-guard regressions from committed units after the last governed full-suite baseline

- ID: F-MD-B10-A002-002
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-09-30T23:20:20+07:00
- Severity: P2 - full-suite regression control / successor closure precondition
- Status: `RESOLVED — MEMBERS_REMEDIATED_BY_RECORDED_OWNERS; FULL-SUITE_CLOSURE_CONFIRMATION_CARRIED_BY_THE_B10_SUCCESSOR_CLOSURE_CONTROL`
- Class: UNRECORDED_REGRESSION
- Related: E-MD-B10-A002-012, E-MD-B10-A002-011, E-MD-B10-A002-001, CI-MD-B10-A002-001, F-MD-B18-A002-011, D-MD-B18-A002-004, D-MD-B10-A002-004, E-MD-B10-A002-014, E-MD-B18-A002-087

## Observed

The last governed full MarketData suite, recorded by E-MD-B18-A002-074 (issued 2026-09-25T00:06, committed in 3ba4884), failed only the seven ProductionCorpusInvariantOracleTest population controls that D-MD-B18-A002-004 expects on an empty deployed corpus. The recovered run behind E-MD-B10-A002-011 (2600 tests / 37425 assertions / 16 failures) adds four static-guard failures that are not caused by the publication/correction/seal patch. Each flagged file is byte-identical to HEAD `0bebd14`, so each failure is present at HEAD. They arrived in commits made after the baseline, each verified with focused controls only, and no governed record named them before E-MD-B10-A002-012. E011 classified them only in its git-ignored raw package.

| Guard (tests/Unit/MarketData) | What it flags | Introduced by |
|---|---|---|
| `AliasNamingAndMeaningBoundaryTest::test_the_alias_meaning_repetition_gap_is_measured_on_the_identifier_not_the_word` | `records/evidence/E-MD-B10-A002-001_FINAL_SEMANTIC_HASH_AFFECTED_VERIFICATION_SCOPE_CORRECTION.json` uses the alias identifier without repeating that it means `data_usable`; the guard admits only five grandfathered documents | E-MD-B10-A002-001 (issued 2026-09-28), committed in `5a7670a` |
| `DateDrivenCapabilityAndProviderAbstractionTest::test_the_provider_query_shape_lives_in_the_source_adapter` | `app/Infrastructure/Persistence/SecurityIdentity/FrozenSourcePackageReader.php` matches the provider transport-shape pattern that only `PublicApiEodBarsAdapter.php` may carry | `5a7670a` (shared foundation core) |
| `DomainOwnershipSurfaceTest::test_no_surface_outside_the_market_data_tree_touches_a_market_data_table` | `app/Infrastructure/Persistence/SecurityIdentity/FoundationRepository.php` references a Market Data table from outside the domain tree | `5a7670a` (shared foundation core) |
| `LifecycleProofIsNotMockedTest::test_db_backed_tests_only_mock_the_external_source_boundary` | `tests/Unit/MarketData/B18ReplayPersistedEvidenceBindingTest.php` mocks `EodPublicationRepository` inside a DB-backed test | `b9e5091` (MD-B18-A002 F-017, 2026-09-25 08:50, after the baseline commit) |

Each was re-executed after the E012 fix and still fails with the same actual/expected sets (E012 raw package `runs/rerun16-*`).

## What is and is not established

- Established: the four failures are independent of the V2 publication/correction/seal implementation and of the configuration fix recorded in E012; none is caused by the uncommitted MD-B10-A002 diff.
- Not established: whether each is a code defect or a guard that needs a governed refinement. The foundation rows may be deliberate under D-MD-B10-A002-001; that is a decision for the owner of that surface, not an inference from this finding.
- Not a new failure class: the drift-test and production-path migration failures in the same run are the undeployed-schema state MD-DEP-0021 already governs, and the corpus-oracle and R0025 failures are governed by D-MD-B18-A002-004/MD-DEP-0015 and MD-DEP-0020. None of those are part of this finding.

## Remediation and closure

- Resolve each row in the unit that owns its surface. Prove the guard green on the repaired surface and still red on the violation it exists to catch.
- Do not edit immutable E-MD-B10-A002-001. Do not add a document, file or mock to a guard's allow-list without a governed decision and a probe showing the guard still fails on an unlisted violation.
- Close when a full MarketData suite fails only on governed expected states: the seven corpus-oracle controls, R0025 while MD-DEP-0020 blocks, and migration drift until MD-DEP-0021 controlled deployment.

## Orchestration

Recording only; no source, test, guard or evidence change was made under this finding. It is not an MD-DEP-0021 blocker and creates no parallel executable resume point. It is a precondition for the full-suite regression control that MD-B10 successor closure requires.

## 2026-10-01 resolved by the recorded owners — D-MD-B10-A002-004, E-MD-B10-A002-014, E-MD-B18-A002-087

Each member was resolved in the unit that owns its surface, in the same MD-B10-A002 / BL001 / CI. No attempt or baseline was created and no predicate changed status.

| Member | Owner | Resolution |
|---|---|---|
| Alias repetition guard | governed decision `D-MD-B10-A002-004` | `E-MD-B10-A002-001` stays byte-identical. It is admitted to the pinned measured set as issued evidence, keyed by its issued SHA-256; a new test fails if those bytes change. Its one alias line quotes an existing test assertion against the preserved column. |
| Provider query shape guard | shared security-identity foundation (`D-MD-B10-A002-001`) | Code repaired: `FrozenSourcePackageReader` takes each source locator from its fingerprinted extract and requires the acquisition manifest to agree, instead of carrying the provider request URL as a literal. Reader output is byte-identical for all three governed packages. The guard is unchanged. |
| Domain ownership guard | shared security-identity foundation, refinement governed by `D-MD-B10-A002-004` | The guard's population counted the foundation's own `si_*` tables as Market Data tables. They are excluded by name only while the foundation migration creates exactly them, no other migration touches them and the base schema defines none; a new test requires the foundation repository to be their only toucher. |
| Lifecycle mocking guard | MD-B18-A002 | `B18ReplayPersistedEvidenceBindingTest` now persists its world (run, captures through the real capture writer, sealed publication, lineage binding and bound context, pointer, eligibility rows) and uses the real repositories. Four perturbation sets now state the production coupling the mocks had hidden. The guard is unchanged. |

Proof: all four guards pass on the repaired surfaces. Ten probes each turned the intended assertion red and were restored byte-identically: an unadmitted alias document, a changed admission hash, the provider URL written back, both locators taken from one extract, a Market Data table read by the foundation repository, a foundation table read by a Market Data service, a widened exclusion set, a re-added repository mock, the writer dropping `event_factor_hash`, and the manifest builder dropping the factor-set hash. The last stayed green against the pre-unit mocked test. Focused controls pass: 18 Market Data files and the three SecurityIdentity MariaDB files. Raw package: `storage/app/market_data/evidence/MD-B10-A002/f002-resolution-20261001-v1`.

Closure condition unchanged: the finding closes when a full MarketData suite fails only on governed expected states. That run is the B10 successor closure control after `MD-DEP-0021` controlled deployment; it was not run in this unit.

## 2026-10-01 owner review — D-MD-B10-A002-005, E-MD-B18-A002-088

The owner approved `D-MD-B10-A002-004` with its bounded interpretation: the alias exception covers only the byte-pinned `E-MD-B10-A002-001` content, and the `si_*` exclusion holds only while the migration-ownership and reverse-access guards prove foundation ownership. The owner also reviewed the four perturbation sets `E-MD-B18-A002-087` corrected, under one rule: a dependent hash may move only because its governed semantic input changes. The three registry sets are approved because they bind the same `registry_versions` capture. The configuration-snapshot set is not approved: it moved only because of a local id. The implementation was fixed and the expectation restored to `[config_snapshot_id]`. The residual V1 entity ids in captured content are `F-MD-B10-A002-004`, owned by MD-B18-A002. This finding's status and closure condition are unchanged.
