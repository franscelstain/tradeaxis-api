# Decision — owner approval of R0025 candidate-v4 (exact fingerprint)

- ID: `D-MD-B18-A002-016`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001`
- Findings / predicate: `F-MD-B18-A002-025`, `F-MD-B18-A002-032`, `F-MD-B18-A002-030`, `F-MD-B18-A002-031` / `MD-S003-R0025`
- Applies: `E-MD-B18-A002-100` (independent review PASS), `E-MD-B18-A002-099` (candidate authoring record)
- Decisions relied on: `D-MD-B18-A002-011`, `D-MD-B18-A002-013`, `D-MD-B18-A002-014`, `D-MD-B18-A002-015`
- Change impact: `CI-MD-B18-A002-001`
- Issued: 2026-10-05T19:43:16+07:00
- Status: `APPROVED` — project owner, in the 2026-10-05 instruction "RECORD R0025 CANDIDATE-V4 OWNER APPROVAL"
- Mutability: `IMMUTABLE_AFTER_ISSUE`
- Strategy impact: `NONE`; `MD-STRATEGY-FREEZE-20261005-001` unchanged

## Owner approval (verbatim, Indonesian, as supplied by the project owner)

> Saya sebagai project owner menyetujui candidate-v4 R0025 dengan fingerprint:
>
> d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00
>
> Approval ini hanya berlaku untuk exact candidate-v4 tersebut dan tidak otomatis berlaku bila fingerprint, frozen build, package, atau reviewed content berubah.

## What is approved (exact binding)

| Item | Value |
|---|---|
| Candidate path | `tests/fixtures/replay/r0025-synthetic-v2-candidate-v4/` |
| Package fingerprint | `d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00` |
| Package manifest sha256 | `6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32` |
| Expected `publication_manifest_hash` | `17f6a5177f18b8e811052aa9eb11abec62c9d0d193e10b7e198b6ed668848e55` |
| Frozen executable build | `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf` |
| Independent review | `E-MD-B18-A002-100`: `CANDIDATE-V4 INDEPENDENT REVIEW PASS` (a reviewer that is not the authoring Agent), issued before this approval |
| Candidate authoring record | `E-MD-B18-A002-099` |

## Target verified before recording

The candidate fingerprint was recomputed from the package files (`d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00`, equal to the fingerprint file), the package manifest sha256 is `6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32`, the frozen build literal equals `sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf` and equals the build recomputed from the working tree, `E-MD-B18-A002-100` still records the review PASS for this fingerprint, the fingerprints of candidate-v1, candidate-v2 and candidate-v3 are unchanged, and no approval record existed before this one.

## Scope and invalidation

* The approval is bound to the exact fingerprint above. It is effective only for that fingerprint (Runtime Evidence Standard 6A) and only for the frozen build above.
* Any change to the fingerprint, the frozen build, the package files or the reviewed content invalidates this approval for the changed candidate; the changed candidate is a new version that needs renewed derivation, independent review and owner approval. The approval is not re-interpreted as applying to any other fingerprint.
* Candidate-v1, candidate-v2 and candidate-v3 are not approved (reviewed `CHANGES REQUIRED`) and stay retained history.

## What this decision does not do

It does not promote `MD-S003-R0025`: the predicate stays `INCOMPLETE / NOT_ASSESSED`. It does not admit or bind the candidate as proof (admission and binding are a separate governed operation), does not run or claim any runtime proof, and does not resolve or close `F-MD-B18-A002-025`, `F-MD-B18-A002-032`, `F-MD-B18-A002-030`, `F-MD-B18-A002-031`, `F-MD-B18-A002-033`, `F-MD-B10-A003-002` or `F-MD-B18-A002-017`. B10 stays closed `1072/1072`; B17 stays `244/257`; `F-018` stays `NOT STARTED`. No candidate, runtime, application or test file was changed.

## Next

R0025 PROOF ADMISSION / BINDING OF THE APPROVED CANDIDATE-V4: a separate bounded governed operation (D-MD-B18-A002-016 approves the exact fingerprint d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00 only) that supplies the approved fingerprint to the replay verification path, binds the approved candidate to the executed target through the governed verification (Runtime Evidence Standard 6A: approval and package freeze precede the governed verification/proof claim), and then reconciles MD-S003-R0025 and the findings F-MD-B18-A002-025, F-MD-B18-A002-032, F-MD-B18-A002-030, F-MD-B18-A002-031 under their own closure rules. It must not edit candidate-v4 (any change invalidates the approval), must not reopen B10 (1072/1072) or touch B17 (244/257), and F-018 stays NOT STARTED. Candidate-v1, candidate-v2 and candidate-v3 must not be approved.
