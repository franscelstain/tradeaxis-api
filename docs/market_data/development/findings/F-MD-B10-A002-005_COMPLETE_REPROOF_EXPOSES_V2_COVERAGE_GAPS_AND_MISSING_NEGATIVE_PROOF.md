# Finding - Complete reproof of the 56 affected predicates exposes V2 coverage gaps and missing negative proof

- ID: F-MD-B10-A002-005
- Stage / Attempt / Baseline / Epoch: MD-B10 / MD-B10-A002 / MD-B10-A002-BL001 / MD-REBASELINE-20260820-001
- Raised: 2026-10-01T23:24:15+07:00
- Severity: P1 - blocks promotion of the 56 affected predicates and B10 successor closure
- Status: OPEN
- Class: PROOF_AND_IMPLEMENTATION_GAP
- Related: E-MD-B10-A002-016, E-MD-B10-A002-015, E-MD-B10-A002-013, E-MD-B10-A002-012, E-MD-B10-A002-011, E-MD-B10-A002-010, E-MD-B10-A002-001, F-MD-B18-A002-023, CI-MD-B10-A002-001, MD-DEP-0020, MD-DEP-0021
- Remediation owner: MD-B10-A002 (B10-owned; same attempt, baseline and impact declaration)

## Observed

The complete governed reproof (`E-MD-B10-A002-016`) re-derived each of the 56 affected predicates against the deployed V2 implementation, without crediting the evidence verdicts. 31 are `PROVEN_CURRENT`. 25 are `INCOMPLETE`: 14 because of five implementation gaps, 11 because the member they require is not negatively proven. Nothing is `BLOCKED` or `AUTHORITY_GAP`. No predicate was promoted.

### Implementation gaps

| Gap | Statement | Predicates |
|---|---|---|
| G1 | The stored bar `source_timestamp` and `acquired_at` are consumer-visible (`Downstream_Consumer_Read_Model_Contract_LOCKED`, canonical market facts) but outside the V2 bars identity. The set-level `observation_manifest_hash` cannot say which bar owns which timestamp: swapping `acquired_at` between two listings leaves the bars hash unchanged. | R0035, R0075, R0015, S018-R0009, and part of R0001, R0087, R0092, R0105 |
| G2 | `atr_state_ref` is hashed only when supplied, and no producer assigns it (no occurrence in `IndicatorVectorService` or `EodIndicatorsComputeService`). The "stable recursive-state reference" the indicator row requires never reaches it, and no test varies ATR lineage. | R0046, and part of R0076, R0078, R0001, R0105 |
| G3 | Sector membership has real revisions (`ticker_sector_memberships`: `recorded_at`, `supersedes_membership_id`), but the V2 indicator row binds only `sector_code` and dependent values, no revision identity. Benchmarks have no revision concept in the schema. | R0047, and part of R0076, R0078, R0087, R0092, R0001, R0105 |
| G4 | The V2 eligibility row has no freshness member. `freshness_state` is bound only in the publication manifest. | R0056, and part of R0001, R0105 |
| G5 | `PublicationDiffService` decides UNCHANGED or CHANGED from the three artifact hashes only. No V2 artifact carries `calendar_revision_set_hash`, so a calendar-revision-only difference is UNCHANGED, while `Historical_Correction_and_Reseal_Contract_LOCKED` requires the comparison to cover every protected field of the seal contract. | S035-R0032, and part of R0087, R0092 |

### Missing negative proof

Each V2 member was removed from production code in turn and the covering tests were run; the file was restored byte-identically each time. A member that no test catches has presence-only proof.

- Artifacts: 102 members, 86 caught, 16 not caught: bars `provider_namespace`, `provider_symbol`, `observation_manifest_hash`, `trade_count`, `quality_reasons_json`, `source_scale_assessment_set_hash`; indicators `observation_manifest_hash` and the identity, status, event and factor-decision set hashes; eligibility identity, status, event and market-structure set hashes and `read_model_version`.
- Manifest: 35 members, 15 caught, 20 not caught: scope and date members, the version members, quality, coverage, readiness and freshness states, row counts, correction lineage and seal constants.
- The duplicate semantic key refusal (row membership) has no test at all.
- Nine leak probes (local ids, run and publication ids, config snapshot id, adj_close, input order, manifest numeric ids) all turned a test red; the predicates that depend only on them are negatively proven.

Predicates affected by missing negative proof: R0024, R0025, R0026, R0031, R0038, R0050, R0059, R0062, R0071, R0077, S045-R0001.

## Not caused by this finding

- Deployment (`E-MD-B10-A002-015`) is valid and unchanged. The successor full MarketData suite ran 2660 tests, 38263 assertions, 8 failures: the seven `ProductionCorpusInvariantOracleTest` population controls (`D-MD-B18-A002-004`) and the R0025 self-generated fixture (`E-MD-B18-A002-082`, `085`). All eight are governed expected states and none touches a B10 predicate.
- `F-MD-B10-A002-003` (platform-created records reused before `recorded_at`) and `F-MD-B10-A002-004` (V1 entity ids in captured replay content) do not invalidate any of the 56: V2 identities exclude `recorded_at` by `D-MD-B10-A002-003` 1A, and the 56 are artifact and manifest identity predicates, not replay-capture identities. Both stay with MD-B18-A002.

## Remediation conditions

Same A002 / BL001 / CI, no new attempt and no new baseline.

1. G1: bind each bar's own source and acquisition timestamps in the V2 bars identity.
2. G2: assign a stable recursive ATR state reference in the production producer and bind it. Authority allows persisted versioned state or recomputation from the stable chain; the reference must identify that state or chain.
3. G3: bind the sector-membership revision identity (content tuple with knowledge time, per `D-MD-B10-A002-003` 1B) in the V2 indicator row.
4. G4: bind the frozen freshness state in the V2 eligibility row.
5. G5: make the correction comparison cover every protected field, including the calendar-revision binding.
6. Add the missing negative proof: a test that varies each uncaught artifact and manifest member, and a test for the duplicate semantic key refusal.
7. Re-run the successor full MarketData suite, repeat the affected reproof rows and, only if all 56 are `PROVEN_CURRENT`, promote through the governed binder.

Changing the V2 payloads invalidates the hash expectations of `E010`-`E013`, so those proofs are re-executed, never edited. No normal-database data exists to rehash: its Market Data tables are empty.

## Orchestration

Opened by the reproof under `MD-DEP-0021` and `MD-DEP-0020`; both stay BLOCKING. It creates the single next executable work for MD-B10-A002 and pauses nothing already paused (R0025 and F-018 stay paused).
