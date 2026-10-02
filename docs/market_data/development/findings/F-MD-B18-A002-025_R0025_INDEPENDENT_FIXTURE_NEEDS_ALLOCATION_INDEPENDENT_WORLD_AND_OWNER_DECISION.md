# Finding — the first independent R0025 fixture needs an allocation-independent world and an owner decision

- ID: `F-MD-B18-A002-025`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-02T11:48:07+07:00
- Severity: `P1` for `MD-S003-R0025`; no effect on any other predicate
- Status: `OPEN`
- Class: `PROOF_FIXTURE_ADMISSION_GAP`
- Related: `E-MD-B18-A002-091`, `F-MD-B18-A002-017`, `F-MD-B10-A002-004`, `F-MD-B10-A002-003`, `D-MD-B18-A002-011`, `E-MD-B18-A002-085`
- Remediation owner: `MD-B18-A002` after the owner decision named below

## Observed

With the environment dependency resolved, the `MD-S003-R0025` aggregate runs on MariaDB with no skipped member: 10 pass and 1 fails. The failing member is the exact-publication family (`s_f1_frozen_inputs_and_seal`), which calls `ReplayVerificationService::generateFixtureFromRun` on the run it then verifies. That copies the run's own actual state into the expected file, so `replayAdmissibility` correctly rejects it with `REPLAY_FIXTURE_SELF_GENERATED`. The rejection is the required behaviour and is not changed.

`D-MD-B18-A002-011` requires an independently derived, reviewed and owner-approved package instead. Building it exposed three things:

1. **The current world cannot carry literal hashes.** The same frozen inputs run twice through the real pipeline gave different bars, indicators, eligibility, factor-set, observation-manifest and publication-manifest hashes (`E-MD-B18-A002-091`, `diagnostics/allocation_dependence.json`). The V1 profile serializes local ids (`listing_id`, `config_snapshot_id`, status and sector revision ids). Section 6A of the runtime evidence standard forbids operational identities in semantic expected-content hashes. Only the source file hash and the config identity are stable.
2. **A retained-identity world is required and none exists end to end.** V2 hashes drop local ids but need a retained foundation identity per listing; `R25X` has none. The V2 tests seed rows, drive repositories or mock the pipeline; none runs the pipeline over a retained listing.
3. **The F-MD-B10-A002-004 residual applies to this fixture.** An independent expectation of the frozen temporal and event/factor identities cannot be allocation-independent while captured replay content carries V1 entity ids.

Of the 134 leaf fields of the circular expected file, 94 are authorable now from authority and the one-bar frozen input, 3 are derivable from the frozen file, 27 are target operational ids (to be bound by the verification record, not authored), 8 are allocation-bound hashes and 1 is the configuration identity.

## Not caused by this finding

The environment, the five other families and the five authority-binding guards are green. `F-MD-B10-A002-003` is not exercised by any R0025 scenario. Nothing here promotes or demotes a predicate.

## Remediation conditions

1. The owner decides Q1 to Q3 of `E-MD-B18-A002-091` (the world class of the first golden, the independent reviewer, the `F-MD-B10-A002-004` direction).
2. Replay frozen identities for V2-profile publications are built from retained roots and fail closed without one; an allocation-history probe shows equality (this is the `F-MD-B10-A002-004` closure).
3. The chosen allocation-independent world runs the real pipeline, and the verifier accepts target-bound operational identities and refuses independent packages that lack their oracle fields.
4. The package and its separate reference oracle are authored with derivation evidence, independently reviewed and owner-approved by fingerprint before any proof claim.
5. The exact-publication member verifies the executed target against the approved package; positive, independence, sensitivity and anti-circularity probes are red where intended and restore byte-identically; the aggregate runs with zero skips.

## Orchestration

`MD-S003-R0025` stays `INCOMPLETE`. `F-MD-B18-A002-017` stays open on it and on `MD-S050-R0005`. `F-018` is not started; `MD-DEP-0015` and `MD-DEP-0017` are unchanged.
