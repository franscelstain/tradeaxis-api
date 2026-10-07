# MD Stage Closure Manifest — SC-MD-B04-A003-001

- ID: `SC-MD-B04-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B04` / `MD-B04-A003` / `MD-B04-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20261005-001`
- Change Impact Declaration: `CI-MD-B04-A003-001`
- Governed evidence: `E-MD-B04-A003-003`; `E-MD-B04-A003-002`; scope record `E-MD-B04-A003-001`; retained for 112 rows `E-MD-B04-A002-001`
- Reviewed decisions: `D-MD-B18-A002-018` (owner Q9 = A1), `D-MD-B18-A002-014` (Q4 = A), impact classification `E-MD-B18-A002-107`
- Predecessor closure: `SC-MD-B04-A002-001` (immutable; its claim `114/114` under its own strategy freeze is not rewritten)
- Dependencies: `MD-DEP-0023` RESOLVED by this closure; `MD-DEP-0007` RESOLVED earlier; `MD-DEP-0004` B04 entry obligation unchanged
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: `2026-10-07T16:37:11+07:00`

## Historical result versus current obligation

`MD-B04-A002` closed `114/114`. Owner decision `D-MD-B18-A002-018` fixes how the configuration snapshot must realise the registry-version rows of `MD-S082`: the reason-registry semantic identity is a derived member of every new snapshot. That created a current conformance obligation; it did not show that the earlier proof failed on its own terms. Two predicates whose recorded proof was a generic configuration guard pair were demoted at entry and are re-proven here; eight were re-confirmed with the member present.

## Terminal coverage

- Mandatory denominator: **114**; mandatory `SATISFIED`: **114/114**
- Evidence binding: **112** on `E-MD-B04-A002-001` (retained byte for byte), **2** on `E-MD-B04-A003-002` (`MD-S082-R0036`, `MD-S082-R0044`, promoted through the governed successor binder)
- `NOT_ASSESSED`: **0**; moved/supporting: **181**; reference/context: unchanged

## What was built

- `MarketDataConfigSnapshotRepository` carries a derived `reason_registry` member in the canonical snapshot content and hence the content hash; derived by the single projection `ReplayV2IdentityProjection::reasonRegistryIdentity` from semantic content only; an empty, malformed or unreadable registry blocks creation with nothing written; no placeholder; no schema change; no new config key; historical snapshots are not touched and not back-filled; the run-bound refusal `INPUT_CAPTURE_LIVE_CONFIG_DIVERGENCE` is preserved and tested.
- 14 member tests; 9 mutation probes (10 runs) all `RED_INTENDED` with byte-identical restoration: member omitted, registry change not moving the member, hash computed without the member, placeholder, malformed accepted, historical back-fill, order and allocation sensitivity, refusal removed, silent run switch.
- Governed tooling: per-rule expected evidence in the proof gate, successor binder with fail-closed preconditions, gate and binder tests.

## Regression

- Full MarketData suite on the final tree: **2875 tests, 41075 assertions, 26 failures**; failures by class:
  - `B18AsKnownModeIsolationTest`: 1
  - `B18ReleaseCandidateAcceptanceAggregateTest`: 1
  - `B18ScenarioFamiliesOnMariaDbTest`: 1
  - `B18ScenarioFamiliesOnMirrorTest`: 1
  - `GovernanceGateReadOnlyExecutionTest`: 2
  - `ProductionCorpusInvariantOracleTest`: 7
  - `R0025SyntheticV2CandidateFixtureTest`: 13
- Three of them (`B18AsKnownModeIsolationTest` 1, `GovernanceGateReadOnlyExecutionTest` 2) were corrected after the run (a B18 test conformance edit to the stricter refusal; registration of the new tooling file) and re-run green; the remaining are the governed and expected states below. Each is classified in `E-MD-B04-A003-003`; the ProductionCorpusInvariantOracle failures are the governed empty-corpus state (`MD-DEP-0015`/`0016`) and the R0025 candidate-v4 failures are the expected consequence of the current-build change recorded by `E-MD-B18-A002-108`. This is not a full-suite PASS.
- `MD-B10`: `E-MD-B18-A002-107` NO_IMPACT confirmed by re-executing 24 covering files; no B10 predicate reclassified.

## Closure conditions

| Condition | Result |
|---|---|
| Zero transitional required rows | **MET — 0** |
| Complete applicable denominator | **MET — 114/114** |
| No invalidated or foreign proof counted | **MET** — the two demoted rows are bound only to `E-MD-B04-A003-002` |
| Current baseline and change impact | **MET** — `MD-B04-A003-BL001`, `CI-MD-B04-A003-001` |
| Reconfirm rows re-run with the member | **MET** — eight rows, `E-MD-B04-A003-002` |
| B10 impact resolved | **MET** — no semantic impact |
| R0025 transition recorded | **MET** — `E-MD-B18-A002-108` |
| Raw proof linkage | **MET** — manifests `cb57b18574d92057d817a63cbb72f11a2d5ae1c8ce3c16aab10ab9d0a6051d79` and `d685169c21b2228a2a3f4493f93bd1bdc57bd8f3ba624f910da97214782c31a0` |

## Boundaries carried forward, not closed here

- `MD-B18`: `MD-S050-R0005`, `MD-S050-R0014`, `MD-S019-R0071` still need their bindings and proof against the member; `AsKnownReplaySnapshotService` still sets the unavailable marker. Reviewed basis `100/113`; `MD-S003-R0025` `INCOMPLETE` until candidate-v5.
- `MD-B17` (`244/257`) untouched.

## Resume

`MD-B04` is closed under `MD-B04-A003`. Return to `MD-B18-A002` per `MD-DEP-0023`: reason-registry binding and proof.
