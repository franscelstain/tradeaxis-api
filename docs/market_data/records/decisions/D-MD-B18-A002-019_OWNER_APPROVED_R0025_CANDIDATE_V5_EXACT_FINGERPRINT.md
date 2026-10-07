# Decision — owner approval of R0025 candidate-v5 (exact fingerprint)

- ID: `D-MD-B18-A002-019`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Findings / predicate: `F-MD-B18-A002-018` (context) / `MD-S003-R0025`
- Applies: `E-MD-B18-A002-114` (independent review PASS), `E-MD-B18-A002-113` (candidate authoring record)
- Decisions relied on: `D-MD-B18-A002-018`, `D-MD-B18-A002-016` (approved candidate-v4 only), `D-MD-B18-A002-015`, `D-MD-B18-A002-014`, `D-MD-B18-A002-013`, `D-MD-B18-A002-011`
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-07T23:31:51+07:00
- Status: `APPROVED` — project owner, in the 2026-10-07 instruction "RECORD OWNER APPROVAL AND ADMIT R0025 CANDIDATE-V5"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner approval (as supplied by the project owner)

> The project owner approves R0025 candidate-v5 with exact fingerprint daf96a296875df875539c196e997823e99ee4171d6d9d77af8d213f13a05fbe3
>
> bound to frozen build identity sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a
>
> and independent review E-MD-B18-A002-114.
>
> Approval is valid only for the exact candidate/build/package/reviewed content. Any change invalidates the approval.

## What is approved (exact binding)

| Item | Value |
|---|---|
| Candidate path | `tests/fixtures/replay/r0025-synthetic-v2-candidate-v5/` |
| Package fingerprint | `daf96a296875df875539c196e997823e99ee4171d6d9d77af8d213f13a05fbe3` |
| Package manifest sha256 | `f7cc89399ec1c5e540db28e4ffeccbf2cccdc58dd3496e80b491c9c51a88bf4a` |
| Expected `publication_manifest_hash` | `6162e5fcd1d327fa39d1e43b2a3911ca74807816d36d8ef8ea506a10a977c8bd` |
| Frozen executable build | `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a` |
| Independent review | `E-MD-B18-A002-114`: `R0025 CANDIDATE-V5 INDEPENDENT REVIEW PASS` (a separate Codex independent-review Agent, not the authoring Agent), issued before this approval |
| Candidate authoring record | `E-MD-B18-A002-113` |

## Target verified before recording

The candidate fingerprint was recomputed from the 24 package files (equal to the fingerprint file), the package manifest sha256 is `f7cc89399ec1c5e540db28e4ffeccbf2cccdc58dd3496e80b491c9c51a88bf4a`, the frozen build recomputed read-only from the working tree equals `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a`, `E-MD-B18-A002-114` records the review PASS for this fingerprint and build, `E-MD-B18-A002-113` is unchanged, no earlier approval or admission record binds this fingerprint, `MD-S003-R0025` is `INCOMPLETE`, readiness is 112/113 and formal SATISFIED is 0/113.

## Scope and invalidation

* The approval is bound to the exact fingerprint above, only for the frozen build above, the package and the content reviewed in `E-MD-B18-A002-114`. It is effective only for that fingerprint (Runtime Evidence Standard 6A).
* Any change to the fingerprint, the frozen build, the package files or the reviewed content invalidates this approval; a changed candidate is a new version that needs renewed derivation, independent review and owner approval. The approval is not re-interpreted as applying to any other fingerprint. No broader owner semantics are added.
* The approval of candidate-v4 (`D-MD-B18-A002-016`) is immutable history and does not carry over; this decision is a successor record for candidate-v5, it does not amend, supersede or rewrite `D-MD-B18-A002-016`, `E-MD-B18-A002-100`, `E-MD-B18-A002-101`, `E-MD-B18-A002-104` or `E-MD-B18-A002-105`.

## What this decision does not do

It does not admit or bind the candidate (a separate governed operation), does not promote `MD-S003-R0025` (it stays `INCOMPLETE` until promotion), does not run the atomic binder and does not close B18. No candidate, oracle, build, application or test file was changed by recording it.
