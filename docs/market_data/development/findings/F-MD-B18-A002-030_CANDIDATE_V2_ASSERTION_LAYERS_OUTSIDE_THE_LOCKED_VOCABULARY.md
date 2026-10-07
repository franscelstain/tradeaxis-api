# Finding — candidate-v2 declares assertion layers outside the locked fixture-manifest vocabulary

- ID: `F-MD-B18-A002-030`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-04T23:42:39+07:00
- Severity: `P2` for `MD-S003-R0025`; the package cannot be approved while its manifest breaks its own locked schema
- Status: `RESOLVED` — locked layers preserved in candidate-v4, independently reviewed PASS (`E-MD-B18-A002-100`) and admitted (`E-MD-B18-A002-101`)
- Class: `PROOF_FIXTURE_ADMISSION_GAP`
- Related: `E-MD-B18-A002-095`, `E-MD-B18-A002-094`, `F-MD-B18-A002-025`, `D-MD-B18-A002-011`
- Remediation owner: `MD-B18-A002`

## Observed

`Fixture_Package_Manifest_LOCKED.md` ("Assertion layer values") allows exactly `row`, `run`, `hash`, `publication`, `replay`. The manifest of candidate-v2 (`tests/fixtures/replay/r0025-synthetic-v2-candidate-v2/manifest.json`, fingerprint `bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f`) declares `run`, `source`, `coverage`, `hash`, `seal`, `publication`, `pointer`, `fallback`, `lineage`, `replay`: seven labels the vocabulary does not contain. Candidate-v1 carried the same list and the first independent review did not name it.

The labels were copied from the list the replay verifier's own generator emits for self-generated runtime fixtures (`ReplayVerificationService::generateFixtureFromRun`, `assertion_layers`), which is a different surface. The verifier accepts any manifest that lists `replay` (`loadFixturePackage` checks only that one value), and no control compared an independent package's layers with the locked vocabulary, so nothing could turn red.

## Not in scope here

The generator for self-generated runtime fixtures keeps its list; those fixtures are not independent packages, are refused as proof by `replayAdmissibility`, and are unchanged by this finding.

## Closure

Resolved when an independent package declares only locked values, the verifier refuses an independent package that declares any other value, and a probe that adds a non-locked value turns the control red (`E-MD-B18-A002-096`), and the corrected package has passed independent review.

## 2026-10-05 correction — E-MD-B18-A002-096

Candidate-v3 (fingerprint `8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e`) declares `["run","hash","publication","replay"]`, all locked values. `row` is not declared because the package asserts no row-level value directly; no expected value or semantic was changed to fit a name. `ReplayVerificationService` now refuses an independent package that declares any other value (`LOCKED_ASSERTION_LAYERS`, `REPLAY_INDEPENDENT_ASSERTION_LAYER_INVALID`). Proof: a control reads the allowed values from `Fixture_Package_Manifest_LOCKED.md` itself and compares the package and the verifier constant with them; a second control blocks eleven non-locked values and malformed shapes even with the package's own approval supplied; probes L1 (gate disabled) and L2 (vocabulary widened) are red at those controls. The finding stays `OPEN` until candidate-v3 passes independent review.

## 2026-10-05 resolved by the admission of candidate-v4 — E-MD-B18-A002-101

Candidate-v4 (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`, frozen build `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf`) was independently reviewed PASS (`E-MD-B18-A002-100`), owner approved (`D-MD-B18-A002-016`) and admitted: the exact-publication member of the `MD-S003-R0025` aggregate verifies the executed target against the approved package through the verifier's admission mechanism (`PASS` / `MATCH` / `ADMISSIBLE`, zero mismatches), with independence, anti-circularity and sensitivity controls and eight member probes red where intended. This finding's closure conditions are met and it is `RESOLVED`. `MD-S003-R0025` is not promoted by this; that is a separate operation under `F-MD-B18-A002-017`.
