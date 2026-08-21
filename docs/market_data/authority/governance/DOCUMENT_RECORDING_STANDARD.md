# Document Recording Standard

## 1. Mutability classes

- `CONTROLLED_REVISION`: current strategy/governance.
- `MUTABLE_TRACEABLE`: implementation, research draft, open finding lifecycle, navigation/status indexes.
- `IMMUTABLE_AFTER_ISSUE`: evidence, issued decisions, locked research, historical archive, baseline locks, closure manifests.

Evidence correction creates new evidence; issued decisions are superseded by new decisions; historical records are never rewritten to look current.

## 2. Governed evidence is not raw runtime output

An evidence record under `records/evidence/**` is the governed statement of proof. Raw logs, command output, replay directories, generated fixtures, dumps, and evidence-export packs normally remain in configured application `storage/**`.

Do not copy large/generated raw artifacts into `docs/` merely to turn them into evidence.

When an evidence claim depends on an external raw artifact, the evidence record MUST carry the correlation and artifact linkage required by `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.

A raw artifact alone is not a current evidence record and MUST NOT silently acquire current-proof authority.

## 3. Correction of external-artifact proof

If required raw proof is missing, unreadable, incomplete, or hash-mismatched, do not edit an issued immutable evidence record.

Create the required new/corrective evidence after valid re-execution/re-export/reverification, preserve the prior record, and update current traceability/closure only according to the applicable governance standards.
