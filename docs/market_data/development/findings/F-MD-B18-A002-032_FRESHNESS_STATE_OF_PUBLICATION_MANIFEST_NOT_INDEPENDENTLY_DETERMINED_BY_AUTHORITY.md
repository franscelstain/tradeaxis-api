# Finding — the freshness state of the V2 publication manifest is not independently determined by authority for the synthetic R0025 world

- ID: `F-MD-B18-A002-032`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-05T12:24:15+07:00
- Severity: `P1` for `MD-S003-R0025`; `freshness_state` is one of the 35 members of the publication manifest preimage
- Status: `RESOLVED` — authority corrected (`DOC-CHG-20261005-001`); candidate-v4 derives the state independently, passed independent review (`E-MD-B18-A002-100`) and is admitted (`E-MD-B18-A002-101`)
- Class: `PROOF_BASIS_AUTHORITY_GAP`
- Related: `E-MD-B18-A002-097`, `E-MD-B18-A002-096`, `F-MD-B18-A002-031`, `F-MD-B18-A002-025`, `D-MD-B18-A002-013`, `D-MD-B18-A002-011`
- Remediation owner: `MD-B18-A002` (owner decision required first)

## Observed

Candidate-v3 expects `freshness_state = NOT_AVAILABLE` as a member of the independently derived `publication_manifest_hash`. The independent review of candidate-v3 found the value is not independently justified: it equals what production emits for a development run, and the oracle's stated basis ("the freshness label of an acquisition outside operational activation") is a vocabulary choice, not a derivation from authority and frozen scenario facts. Because the member is in the preimage, the expected hash is not admissible as independent truth while this stands.

## Authority trace (what authority does and does not determine)

| Source | What it fixes |
|---|---|
| `Consumer_Readability_Decision_Table_LOCKED.md` row 1 | A sealed publication passing every minimum-product gate, requested date: `FRESH` "unless an explicit activated degraded condition applies". |
| `Downstream_Data_Readiness_Guarantee_LOCKED.md` "Freshness states" | `FRESH` needs effective date = requested/latest expected AND all *activated* operational freshness gates pass. Before `OPERATIONAL_START_DATE` the response "still may not claim `FRESH`". `STALE` needs a deliberately returned prior publication. `DEGRADED` needs declared non-silent degraded conditions. `NOT_AVAILABLE` means no consumer-safe result. |
| `Terminology_and_Scope.md` "Operational activation"; `EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md` | Activation is an explicit governed marker, never implied by a backfill, proof run or development frontier. Development state permits no unproven fresh/current claim. |
| `Run_Artifacts_Format_LOCKED.md` | `NOT_AVAILABLE` appears only on an unsealed in-progress run summary (null hashes, null publication version); `FRESH` appears on a sealed `READABLE` manifest example. Neither example states an activation context. |
| Frozen synthetic world | `operational_start_date` null, `daily_enabled` false: a development world. No owner decision (`D-MD-B18-A002-011/013/014`) sets an activation marker or a freshness property for it. |
| Production | `eod_runs.freshness_state` is `DEVELOPMENT_NOT_OPERATIONAL` when the marker is unset (`NOT_EVALUATED` when set); the manifest and eligibility normalizers map any value outside `FRESH/STALE/DEGRADED/NOT_AVAILABLE` to `NOT_AVAILABLE`. No code path anywhere in `app/` produces `FRESH`, `STALE` or `DEGRADED`: there is no freshness evaluator. |

Result: for a sealed `READABLE` publication in a development world, authority excludes `FRESH` and `STALE` and does not select between `DEGRADED` and `NOT_AVAILABLE`; the vocabulary has no value for "readable but not activated", and `READABLE` with `NOT_AVAILABLE` contradicts the vocabulary's own definition of `NOT_AVAILABLE`. For an activated world authority gives `FRESH`, but the world is not activated and the activation marker and the activated gate list are owner-level inputs that no record supplies. Authority therefore does **not** uniquely determine the successor semantics (classification B).

## Closure

Resolved when (1) the owner/authority decision `Q8` in `E-MD-B18-A002-097` is answered and recorded as a decision record, (2) a distinct candidate-v4 encodes the decided freshness context in frozen inputs and the oracle derives the member from them, (3) a probe shows the member and the expected hash move with the frozen context and a wrong target freshness gives an exact mismatch, and (4) candidate-v4 has passed independent review.

## 2026-10-05 authority corrected — D-MD-B18-A002-015, DOC-CHG-20261005-001, E-MD-B18-A002-098

The owner selected Direction D. The strategy now defines the freshness state `NOT_APPLICABLE` for a `READABLE` publication whose requested trade date precedes the effective activation marker (or with no marker): operational freshness is not in force, which is neither `FRESH`, `STALE`, `DEGRADED` nor `NOT_AVAILABLE`, and readability is independent of freshness. Closure condition 1 (the owner/authority question) is met. Conditions 2 to 4 remain: the producers conform (`F-MD-B18-A002-033`), a distinct candidate-v4 derives the freshness member from a frozen activation context, and it passes independent review. The finding stays `OPEN`.

## 2026-10-05 candidate-v4 — E-MD-B18-A002-099

Candidate-v4, `tests/fixtures/replay/r0025-synthetic-v2-candidate-v4` (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`, manifest sha256 `6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32`), freezes the pre-activation context (requested date `2026-03-23`, `operational_start_date` null, no marker effective, readable publication, no prior-date fallback, no degraded condition, activated gates not applicable) and derives the freshness state with the oracle's own implementation of the authority's ordered table: `NOT_APPLICABLE` (row 4). The state is carried into the eligibility row, the eligibility batch hash (`a8b3ee0e062c51578287d6d3f23bca65ad51d8dfc8dea3f3fdda6ebd0f545d57`) and the 35-member manifest preimage, and gives the independently derived `publication_manifest_hash` `17f6a5177f18b8e811052aa9eb11abec62c9d0d193e10b7e198b6ed668848e55`. The oracle reads no application code, publication table, run or replay output, and refuses (exit 3) what it cannot decide (an in-force marker without a gate result, a prior-date fallback). Against the real target (production run creator, MD-B10-A003) the comparison has zero mismatches; a target with another freshness label gives exactly the mismatches `eligibility_batch_hash`, `lineage` and `publication_manifest_hash`. Probes F1 to F7 and the candidate-v3 probes (37) are red at their intended controls. No application code changed in this unit. The finding stays `OPEN` until candidate-v4 passes independent review; activated-world freshness is not proved.

## 2026-10-05 candidate-v4 independently reviewed PASS — E-MD-B18-A002-100

An independent-review Agent that is not the authoring Agent (identity as relayed in the project instruction; no name, session or model identifier was supplied and none is invented) returned PASS for candidate-v4 (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`): no blocking finding, no owner or authority decision required, no candidate-v5 required. The candidate is not owner-approved; no admission or promotion follows from the review. The finding stays `OPEN` until admission / reconciliation. Annotation: the authoring session's prose "37 probes, all RED_INTENDED" refers to the 37 successful probes of the main run; the main run also had one `ANCHOR_ERROR` (F5), and F5's re-run with a unique anchor was `RED_INTENDED`, so final unique successful coverage is 38 (the main run itself is not 38/38).

## 2026-10-05 candidate-v4 owner approval — D-MD-B18-A002-016

Candidate-v4 (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`) is owner approved (exact-fingerprint-bound) after the independent review PASS `E-MD-B18-A002-100`. It is not yet admitted or bound as proof; the finding stays `OPEN` until admission / reconciliation.

## 2026-10-05 resolved by the admission of candidate-v4 — E-MD-B18-A002-101

Candidate-v4 (fingerprint `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`, frozen build `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf`) was independently reviewed PASS (`E-MD-B18-A002-100`), owner approved (`D-MD-B18-A002-016`) and admitted: the exact-publication member of the `MD-S003-R0025` aggregate verifies the executed target against the approved package through the verifier's admission mechanism (`PASS` / `MATCH` / `ADMISSIBLE`, zero mismatches), with independence, anti-circularity and sensitivity controls and eight member probes red where intended. This finding's closure conditions are met and it is `RESOLVED`. `MD-S003-R0025` is not promoted by this; that is a separate operation under `F-MD-B18-A002-017`.
