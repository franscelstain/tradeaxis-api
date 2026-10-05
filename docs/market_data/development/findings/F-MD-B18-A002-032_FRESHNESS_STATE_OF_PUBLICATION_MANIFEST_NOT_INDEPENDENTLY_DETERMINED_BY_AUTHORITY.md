# Finding — the freshness state of the V2 publication manifest is not independently determined by authority for the synthetic R0025 world

- ID: `F-MD-B18-A002-032`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-05T12:24:15+07:00
- Severity: `P1` for `MD-S003-R0025`; `freshness_state` is one of the 35 members of the publication manifest preimage
- Status: `OPEN` — raised by the independent review of candidate-v3 (`E-MD-B18-A002-097`); blocked on an owner / authority decision (`Q8`); no candidate-v4 exists
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
