# Decision — project owner selects O1 for F-MD-B19-A001-003: batch the MD-B19 production changes, then re-freeze R0025 once before MD-B19 closure

- ID: `D-MD-B19-A001-001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Decides: `F-MD-B19-A001-003` (Option O1)
- Applies to: `MD-DEP-0025`
- Decisions relied on: `D-MD-B18-A002-018` (item 13), `D-MD-B18-A002-019`, `D-MD-B18-A002-022`
- Evidence relied on: `E-MD-B18-A002-115` (candidate-v5 admission), `SC-MD-B18-A002-001`, `E-MD-B19-A001-002`
- Change impact: `CI-MD-B19-A001-001` (amendment issued with this decision)
- Issued: 2026-10-08T08:04:28+07:00
- Status: `APPROVED` — project owner, in the 2026-10-08 instruction "RECORD B19 O1 OWNER DECISION AND CONTINUE NEXT NON-PRODUCTION FAMILY"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Frozen-build identity this decision is bound to

Candidate-v5 (`tests/fixtures/replay/r0025-synthetic-v2-candidate-v5`) is frozen to build
`sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a` — method `php_source_build_v1`,
5916 files (`app/` 195, `config/` 4, `bootstrap/` 1, `composer.json`, `composer.lock`, `vendor/` 5714), manifest
`inputs/frozen_build_manifest.txt` sha256 `8464514949826b5a2a7cd686cd3d2dc33a57bba58e43824aad3667bc0971146e`. At the
time of this decision every one of those files equals the manifest (`5916` checked, `0` differ).

## Owner decision (as supplied)

Project owner selects F-MD-B19-A001-003 Option O1.

Normative semantics:

1. All B19 production-changing work may be batched before re-freezing R0025.
2. Candidate-v5 and all MD-B18 closure/proof history remain immutable and historically valid for the approved frozen build.
3. Before the first executable-build change in B19: candidate-v5 may remain current.
4. After the first executable-build change: candidate-v5 becomes historical-only for current-build purposes, but MD-B18 is NOT reopened solely because a successor-stage build changed.
5. Until candidate-v6 exists: the current-build R0025 build-identity failure must remain visible as a governed successor-pending failure under MD-DEP-0025.
6. That failure must NOT: be skipped; be called PASS; be hidden; be "fixed" by modifying candidate-v5.
7. All production-changing B19 work should complete first.
8. After the final B19 executable-build change and before B19 closure: author one candidate-v6 against the final build.
9. Candidate-v6 requires: independent review; owner approval; admission/binding; required current-build R0025 revalidation.
10. Any build change after candidate-v6 invalidates its currentness and requires another freeze.
11. Any R0025 failure beyond the expected build-identity successor-pending mismatch is a regression and must be investigated.

## What this record does and does not do

- It selects O1 and nothing else. It does not select a design for any production change, does not approve any
  candidate-v6 content, and does not accept any B19 predicate as proven.
- It does not resolve `MD-DEP-0025`: the dependency tracks candidate-v6, which does not exist yet. Its status
  changes from `BLOCKING` (no production edit allowed) to an open dependency that no longer blocks production-changing
  units but **does** block `MD-B19` closure (item 8).
- It does not edit candidate-v5, `D-MD-B18-A002-018/019/022`, `E-MD-B18-A002-115`, `SC-MD-B18-A002-001` or any
  `MD-B18` record. `MD-B18` stays `DONE` / `PASS` under `SC-MD-B18-A002-001`.
- The interim failure of the build-identity control of `R0025SyntheticV2CandidateFixtureTest` after the first
  executable-build change is the *expected successor-pending mismatch* of item 5. It is recorded as such, never
  skipped, never reported as PASS, and any other R0025 failure is a regression (item 11).
- It does not change the `D-MD-B18-A002-022` acceptance rule, which was written for `MD-B18` closure only, and does
  not make the `ProductionCorpusInvariantOracleTest` failures a `MD-B19` blocker.
