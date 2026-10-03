# R0025 synthetic V2 golden fixture — candidate 1

`CANDIDATE_AWAITING_INDEPENDENT_REVIEW`. Synthetic and test-only. This package is not approved, not proof and not a canonical golden.

* `manifest.json` — package manifest: family `independent_golden_synthetic_v2`, provenance, target-bound field declaration, not-asserted bound inputs, sha256 of every other file.
* `inputs/` — frozen inputs of the synthetic world (fixed retained roots, one bar, provider response bytes, registry, frozen configuration).
* `expected/` — literal expected semantics. `@TARGET:<kind>` marks an operational identity that the verifier binds from the named target; it is never a wildcard.
* `derivation/` — the standalone reference oracle, its output (all preimages), the field classification and the derivation record.

The package fingerprint is in `../r0025-synthetic-v2-candidate-v1.fingerprint.txt`. An approval must bind that value and the manifest sha256; the verifier refuses the package (`REPLAY_INDEPENDENT_REVIEW_REQUIRED`) until one is supplied.

Never edit a file of this package in place after review: a correction is a new version with renewed derivation, review and approval.
