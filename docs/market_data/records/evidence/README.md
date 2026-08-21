# Market Data Evidence

Current and historical evidence is separated by verification metadata. Pre-rebaseline evidence cannot close a current `MD-Bxx` stage.

This folder stores **governed evidence records**, not an unlimited copy of raw runtime output.

When current evidence depends on external test/runtime/replay/backfill/evidence-export artifacts, the evidence record must bind those artifacts according to `../../authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.

Raw `storage/**` artifacts alone are supporting execution material; they are not current proof without valid current correlation and required integrity/linkage.
