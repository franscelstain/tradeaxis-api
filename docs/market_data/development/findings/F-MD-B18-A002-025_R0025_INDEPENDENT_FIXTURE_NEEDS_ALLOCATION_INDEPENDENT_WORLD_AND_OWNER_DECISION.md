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

## 2026-10-02 owner decisions and candidate package — D-MD-B18-A002-013, E-MD-B18-A002-092

The owner chose option A for Q1 (a synthetic, labelled V2 world), a separate independent reviewer plus owner approval for Q2, and the V2-only retained-root direction for Q3 (`D-MD-B18-A002-013`). Condition 1 of this finding is met. `E-MD-B18-A002-092` builds the first candidate package `tests/fixtures/replay/r0025-synthetic-v2-candidate-v1` (fingerprint `05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0`): frozen synthetic inputs with fixed retained roots, a standalone reference oracle, literal expectations (99 semantic, 21 derived from frozen input, 20 target-bound operational identities, 4 bound inputs deliberately not asserted), proof that the semantic hashes do not move across allocation layouts, and the V2 verifier support (retained-root replay identities for V2 publications, a closed list of target-bound identities, and a gate that refuses the package until an approval is bound to its fingerprint). Condition 2 is met for four of the five V2 replay identities; the ancillary member of `event_factor_hash` remains. Condition 3 is met. The package matches the actual publication and is not proof. Conditions 4 and 5 are outstanding: independent review, owner approval bound to the fingerprint, then the member probes and the aggregate. The finding stays `OPEN`.

## 2026-10-03 candidate-v1 review response — E-MD-B18-A002-093

The independent review of candidate-v1 (`tests/fixtures/replay/r0025-synthetic-v2-candidate-v1`, fingerprint `05b717c63ef1f5f96a759ed6d2160b46e4eb9d1c9c946e2a03d373229abf26c0`) returned **CHANGES REQUIRED BEFORE APPROVAL**: IR-1 four of eleven bound inputs were not asserted, IR-2 the provider timestamp was not traced, IR-3 the wrong-fingerprint control could catch its own failure. Candidate-v1 stays unchanged, receives no approval and is never reused under its fingerprint. `E-MD-B18-A002-093` records: IR-3 fixed and proven (`F-MD-B18-A002-029`, probe P1); IR-2 traced and classified a production defect with an authority gap (`F-MD-B18-A002-027`); IR-1 traced, with `event_factor_hash` allocation-bound, the formula and reason registry identities one build-bound hash, and no frozen build identity (`F-MD-B18-A002-028`, `F-MD-B10-A002-004`). Candidate-v2 is not produced: it needs owner decisions Q4 (registry identity), Q5 (build identity), Q6 (provider source timestamp) and Q7 (V2 event/factor membership). Condition 4 of this finding is not met; condition 5 is outstanding. The finding stays `OPEN`.

## 2026-10-03 candidate-v2 — D-MD-B18-A002-014, E-MD-B18-A002-094

The owner selected Q4 = A, Q5 = B, Q6 = B, Q7 = A. `E-MD-B18-A002-094` builds candidate-v2, `tests/fixtures/replay/r0025-synthetic-v2-candidate-v2` (fingerprint `bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f`, manifest sha256 `ed1d102ced928d7143f29e717f93dd1a6a8f6872406de7a103741520ca08bb42`), which asserts all eleven bound inputs as literals and is valid only for the frozen build. It matches the actual publication with zero mismatches over all eleven inputs in two allocation layouts and is not proof: no independent review and no owner approval is bound to it, and the verifier answers `REPLAY_INDEPENDENT_REVIEW_REQUIRED`. Candidate-v1 stays rejected and never approved. Conditions 4 and 5 of this finding are outstanding: independent review of candidate-v2, owner approval bound to its fingerprint, then the member probes and the aggregate. The finding stays `OPEN`.

## 2026-10-04 candidate-v2 independent review — E-MD-B18-A002-095

Candidate-v2 (fingerprint `bbd8c73953eb1397a49ba651e8b66b5b6cf914dd0790142d2a706bb8a49edb9f`) was independently reviewed and returned **CHANGES REQUIRED**: its manifest declares assertion layers outside the locked vocabulary (`F-MD-B18-A002-030`) and the exact-publication coverage of `publication_manifest_hash` is not established (`F-MD-B18-A002-031`). Candidate-v2 stays unchanged and is never approved. The owner decisions of `D-MD-B18-A002-014` and the other semantics of candidate-v2 are preserved. A distinct candidate-v3 corrects the two findings. Conditions 4 and 5 of this finding are outstanding. The finding stays `OPEN`.

## 2026-10-05 candidate-v3 — E-MD-B18-A002-096

Candidate-v3, `tests/fixtures/replay/r0025-synthetic-v2-candidate-v3` (fingerprint `8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e`, manifest sha256 `266009f117ebcefdd9a0827b02f037eba415f43e655fd684fed3bcf89a1a5cf5`), corrects the two review findings of candidate-v2 (`F-MD-B18-A002-030`, `F-MD-B18-A002-031`) and preserves every other semantic. It matches the actual publication with zero mismatches and is not proof: no independent review and no owner approval is bound to it, and the verifier answers `REPLAY_INDEPENDENT_REVIEW_REQUIRED`. Candidate-v1 and candidate-v2 stay rejected and never approved. Conditions 4 and 5 of this finding are outstanding. The finding stays `OPEN`.

## 2026-10-05 candidate-v3 independent review — E-MD-B18-A002-097

Candidate-v3 (fingerprint `8a218f5befc0b1c6f278d20ee86a798ca29185ad4522e8b8a00ee069cec9b85e`) was independently reviewed (as relayed; reviewer identity not supplied) and returned **CHANGES REQUIRED**. The blocker is the `freshness_state` member of the publication manifest (`F-MD-B18-A002-032`): it is not derived from authority and frozen scenario semantics. The Agent's authority trace shows that authority does not uniquely determine the successor semantics for the synthetic world, so candidate-v4 is blocked on owner / authority decision Q8 (`E-MD-B18-A002-097`). Candidate-v3 stays unchanged and is never approved. Conditions 4 and 5 of this finding are outstanding. The finding stays `OPEN`.

## 2026-10-05 authority correction — E-MD-B18-A002-098

The owner selected Direction D (`D-MD-B18-A002-015`) and the strategy was corrected by `DOC-CHG-20261005-001` (successor freeze `MD-STRATEGY-FREEZE-20261005-001`). Candidate-v3 stays reviewed `CHANGES REQUIRED`, immutable and never approved. Candidate-v4 is blocked on the bounded `MD-B10` remediation `F-MD-B18-A002-033` because the target must emit the corrected state before an independent expectation of it can be reproduced. Conditions 4 and 5 of this finding are outstanding. The finding stays `OPEN`.

## 2026-10-05 candidate-v4 — E-MD-B18-A002-099

Candidate-v4, `tests/fixtures/replay/r0025-synthetic-v2-candidate-v4` (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`, manifest sha256 `6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32`), answers the review of candidate-v3 (`E-MD-B18-A002-097`) and preserves every reviewed-PASS property. It matches the actual publication with zero mismatches and is not proof: no independent review and no owner approval is bound to it, and the verifier answers `REPLAY_INDEPENDENT_REVIEW_REQUIRED`. Candidate-v1, candidate-v2 and candidate-v3 stay rejected and never approved. Conditions 4 and 5 of this finding are outstanding. The finding stays `OPEN`.
