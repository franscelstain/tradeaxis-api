# Decision — Runtime Artifact / Governed Evidence Governance Adoption

- ID: `D-MD-20260821-03`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Finding: `F-MD-20260821-03`
- Evidence: `E-MD-20260821-03`
- Issued: 2026-08-21
- Decision status: `ISSUED`
- Strategy impact: `NONE`

## Decision

Adopt `authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` as current process authority for the boundary between governed evidence records and raw execution artifacts.

The canonical rule is:

`current docs/records select the proof → required raw storage artifact is inspected/produced → integrity is verified → governed evidence admits the result`

not:

`scan storage → infer current state/verdict`.

Raw artifacts remain outside the documentation authority tree and do not independently create current proof.

## Transition

This revision does not edit or automatically invalidate immutable evidence/closure records issued before adoption solely because the docs snapshot does not include application storage.

All new executed proof, all open-attempt closure relying on external raw artifacts, and all future carry-forward of prior execution proof must satisfy the current artifact-linkage/integrity rules when such artifacts are required.

## Non-goals

- no Market Data strategy semantic change;
- no storage-retention implementation change is asserted;
- no PHP/generator/test implementation change is claimed from a docs-only snapshot;
- no historical runtime artifact is promoted to current proof by this decision.
