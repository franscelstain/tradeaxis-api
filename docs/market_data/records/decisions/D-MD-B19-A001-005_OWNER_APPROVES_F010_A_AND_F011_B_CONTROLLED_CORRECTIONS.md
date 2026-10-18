# Decision — owner approves F010 Option A and F011 Option B

- ID: `D-MD-B19-A001-005`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Decides: `F-MD-B19-A001-010`, `F-MD-B19-A001-011`
- Evidence relied on: `E-MD-B19-A001-015`, `E-MD-B19-A001-017`, `E-MD-B19-A001-018`
- Change impact: `CI-MD-B19-A001-001`
- Issued: 2026-10-18
- Status: `APPROVED` — Project Owner instruction “APPROVED AUTHORITY CORRECTIONS + HIGH-THROUGHPUT REPLAY PROOF”
- Mutability: `IMMUTABLE_AFTER_ISSUE`

## Binding choices

The Project Owner selected **F010 Option A**: register `BAR_CONFLICTING_SOURCE_ROWS` as a distinct canonical BAR / HARD reason. Its meaning is conflicting, non-equivalent source observations for the same ticker/date where no canonical winner can safely be determined; rejected audit evidence is preserved. It is not `BAR_DUPLICATE_SOURCE_ROW`, which concerns deterministic duplicate resolution.

The Project Owner selected **F011 Option B**, approving the exact eleven-predicate mapping in the current finding for `MD-S075-R0240`–`R0249` and `R0259`. Preserve persisted scalar names and current JSON paths, independent actual and expected identities, and capture the two absent publication-manifest values in one explicitly versioned, persisted, publication-bound envelope. Align Run Artifacts sections 8–10 and affected audit-pack contracts. No semantic hashes may be equated or values invented.

## Conditions retained by the owner

These choices do not waive independent strategy review, freeze revision or verification-impact requirements. The exact coordinated replacement wording and affected-verification map are prepared separately for independent review. This record approves the choices; it does not certify an independent review of wording authored after this instruction, apply a freeze, admit a predicate or declare a finding resolved.

The twelve authority-dependent predicates remain HELD until controlled correction and executed proof. Independent state-pair proof may proceed only where its exact obligation, persisted path and source meaning remain unchanged by the proposed revision. Candidate-v5 remains immutable; candidate-v6 is not created. `MD-DEP-0025` stays ACTIVE. No new attempt or Baseline Lock is created.
