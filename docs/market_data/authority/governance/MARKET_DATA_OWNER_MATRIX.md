# Market Data Owner Matrix

| Concern | Owner layer | May define current Market Data behavior? |
|---|---|---|
| Market-data product/domain behavior | `authority/strategy` | YES |
| Documentation/verification lifecycle | `authority/governance` | YES, process only |
| Technical implementation | `development/implementation` | NO; must conform |
| Research | `development/research` | NO |
| Findings | `development/findings` | NO |
| Governed evidence records | `records/evidence` | NO |
| Raw runtime/test/replay/backfill artifacts | configured application `storage/**` | NO; supporting execution material only |
| Issued decisions | `records/decisions` | NO implicit rule change |
| History | `records/history` | NO |

Raw `storage/**` artifacts do not become a parallel authority/record owner. Admission into current proof is controlled by `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.
