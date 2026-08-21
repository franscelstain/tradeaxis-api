# Market Data Records

- `evidence/` — governed evidence records.
- `decisions/` — issued decisions.
- `history/` — historical/superseded/provenance records.

Records describe what happened; they do not silently redefine current strategy/governance.

Raw test/runtime/replay/backfill/evidence-export output normally remains in configured application `storage/**`. It is not a parallel record layer and does not become current proof merely because it exists.

For current executed proof, follow `../authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`: canonical records determine which storage artifacts are relevant and how they are admitted.
