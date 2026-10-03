# Finding — a V2-profile pipeline run could not hash its eligibility artifact

- ID: `F-MD-B18-A002-026`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-02T13:43:54+07:00
- Severity: `P2` — fail-closed at the HASH stage; no wrong value was ever published
- Status: `RESOLVED` — fixed and proven in the same unit (`E-MD-B18-A002-092`)
- Class: `IMPLEMENTATION_DEFECT`
- Related: `E-MD-B18-A002-092`, `F-MD-B10-A002-004`, `F-MD-B18-A002-025`, `D-MD-B18-A002-013`

## Observed

While building the first end-to-end V2 world (provider response, real `runDaily`, retained identity), the HASH stage failed with `ARTIFACT_CONFIG_CONTENT_MISSING: row snapshot binding required`. `ArtifactSemanticHashService::assertRowConfigContent` requires every V2 artifact row to name the immutable configuration snapshot whose content it hashes. Bars and indicator rows carry `config_snapshot_id`; the rows `EodEligibilityBuildService` wrote did not (the column stayed NULL), so a V2 run could never seal. No V2 test had run the pipeline end to end: they seed rows, drive repositories or mock the pipeline, which is why this was not seen.

## Resolution

`EodEligibilityBuildService` writes the run's `config_snapshot_id` on each eligibility row only when the run's artifact hash profile is V2. V1 rows keep their historical NULL, so V1 artifact hashes stay byte-identical. Proof: the V2 world now runs the real pipeline to a sealed publication (`R0025SyntheticV2CandidateFixtureTest::test_the_world_runs_the_real_v2_pipeline_and_seals_a_publication`); `test_a_v1_profile_run_keeps_its_historical_interpretation` shows V1 eligibility rows keep NULL; mutation probes P10 and P11 turn those two controls red; the full `MarketData` suite shows no regression.

## Not in scope here

The `MD-B10` closure (`SC-MD-B10-A002-001`, 1072/1072) is not reopened or changed. The B10 proof covered V2 artifact and nested identity on stored rows; the pipeline integration defect is recorded here because it was found, and fixed, on the B18 path. Whether the B10 owner wants to review its closure scope against it is for them.
