# Finding — the formula and reason registry identities are one build-bound hash, and an independent golden has no frozen build identity to assert

- ID: `F-MD-B18-A002-028`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-03T08:45:44+07:00
- Severity: `P1` for `MD-S003-R0025`; no effect on any other predicate
- Status: `OPEN` — IMPLEMENTED under the owner decisions (`D-MD-B18-A002-014`, Q4 = A and Q5 = B); closes when candidate-v2, which asserts all eleven bound inputs, passes independent review (`E-MD-B18-A002-094`)
- Class: `PROOF_FIXTURE_ADMISSION_GAP`
- Related: `E-MD-B18-A002-093`, `E-MD-B18-A002-092`, `F-MD-B18-A002-025`, `F-MD-B18-A002-017`, `D-MD-B10-A002-005`, `D-MD-B18-A002-006`, `D-MD-B18-A002-013`
- Remediation owner: `MD-B18-A002` after the owner decision

## Observed

The independent review of candidate-v1 found that four of the eleven required bound inputs were not asserted: `event_factor_hash`, `formula_registry_hash`, `reason_registry_hash` and `executable_build_identity` (`compareField` skips a NULL expectation). Candidate-v2 must assert all eleven independently. Tracing the real production path (`diagnostics/eleven_bound_inputs_two_layouts.json`, two allocation layouts) shows that three of the four cannot be derived from frozen registry content without a decision:

1. **`formula_registry_hash` and `reason_registry_hash` are the same value.** `ReplayVerificationService::actualBoundInputContext` sets both to the `payload_hash` of the whole `registry_versions` capture. Both read `23689bb20070081073ecfe36f374893a10df7feeafaf481b47afa7425ce9d3ec` in both layouts. A change to the reason entries moves both; a change to a formula version moves both; neither can be asserted separately.
2. **That payload is build-bound.** `ProducerRegistrySnapshot::capture` puts into the payload the whole `executable_build` — the SHA-256 of every `app`, `config`, `bootstrap` and `vendor` file plus `composer.json` and `composer.lock`, the SHA-256 of the gzip archive of that content, the PHP version and the PHP SAPI — and six implementation file hashes. The hash therefore moves with any edit to any application or vendor file, with the PHP version and with the compressor, none of which is registry content. An independent oracle that derived it would have to re-implement the build scan and read the very tree it is meant to check; the literal would go stale at the next edit.
3. **`executable_build_identity` is `sha256:` plus the same whole-tree hash.** On this tree it is `sha256:608008e96f0adf4022fbed84d4c325afed61499be79b2f12963e327c6d345320`. There is no mechanism that freezes a governed build identity apart from "the build that executes"; a package fingerprint approval would be bound to a tree that changes with the next commit.
4. **This is approved semantics, not an accident.** `D-MD-B10-A002-005` records that the read-model version, serialization version and executable build all move "the content of the `registry_versions` capture, whose payload hash is the formula and reason registry identity", and approves that. `D-MD-B18-A002-006` states that `formula_registry_hash` and `reason_registry_hash` read the whole `registry_versions` component's combined payload hash and authorises no new field. Changing it is not an implementation choice; it contradicts decisions the owner already took.

The payload does hold a separate, semantic `reason_registry_hash` (SHA-256 of the canonical reason entries, independent of the build), but no consumer reads it.

## What is and is not derivable today

Independently derivable from frozen input, and already asserted in candidate-v1: the observation manifest, the canonical raw input hash, the temporal identity, the calendar and status identity, the configuration snapshot hash, the read-model version and the serialization version. Not derivable without a decision: the formula identity, the reason identity and the executable build identity (this finding), and the event/factor identity (`F-MD-B10-A002-004`, residual in `E-MD-B18-A002-093` Q7).

## Resolution

None here. `E-MD-B18-A002-093` Q4 and Q5 state the options with a recommendation. No production code is changed. The finding closes when the owner decides, the V2 identities are defined and implemented under the B10 impact review that decision requires, and a candidate that asserts all eleven passes independent review.

## 2026-10-03 implementation — D-MD-B18-A002-014, E-MD-B18-A002-094

Q4 = A: a V2 publication binds `formula_registry_hash` (the formula, indicator and related registry versions) and `reason_registry_hash` (the reason entries ordered by code) as separate semantic identities without the executable build, derived from the frozen registry capture; V1 keeps the whole-payload identity. Q5 = B: `executable_build_identity` is a frozen literal, `sha256:d7e008b973f400189259fbe27ddbc273c769a0328827bec877fb3899cbb16c16`, with its manifest (5915 files) and retained archive in candidate-v2; the oracle consumes it and never inspects the tree; a mismatch fails, and the approval of the package is valid only for that build. Candidate-v2 asserts all eleven bound inputs as literals (none NULL, none target-bound); eleven of eleven are identical across two allocation layouts. The finding closes when candidate-v2 passes independent review.
