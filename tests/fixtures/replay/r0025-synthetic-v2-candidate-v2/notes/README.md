# R0025 synthetic V2 golden fixture — candidate 2

`CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Synthetic and test-only. This package is not approved, not proof and not a canonical golden. It supersedes candidate 1 (reviewed CHANGES REQUIRED, never approved, retained untouched).

* `manifest.json` — package manifest: family `independent_golden_synthetic_v2`, provenance, target-bound field declaration, the eleven asserted bound inputs, sha256 of every other file.
* `inputs/` — frozen inputs of the synthetic world (fixed retained roots, one bar, provider response bytes, registry, frozen configuration, frozen reason registry, registry literals, frozen build identity and manifest).
* `expected/` — literal expected semantics. `@TARGET:<kind>` marks an operational identity that the verifier binds from the named target; it is never a wildcard and never applies to a bound input.
* `derivation/` — the standalone reference oracle, its output (all preimages), the field classification, the derivation record and the authoring tools.

The package fingerprint is in `../r0025-synthetic-v2-candidate-v2.fingerprint.txt`. An approval must bind that value and the manifest sha256; the verifier refuses the package (`REPLAY_INDEPENDENT_REVIEW_REQUIRED`) until one is supplied. The package is valid only for the frozen build in `inputs/frozen_build_identity.json`.

Never edit a file of this package in place after review: a correction is a new version with renewed derivation, review and approval.
