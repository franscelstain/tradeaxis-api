# Market Data Governance

Current process authority. Strategy behavior remains in `../strategy/`.

Core controls: architecture/ownership, one-document-one-role, verification rebaseline, traceability, stage/rework, residue, baseline lock, work correlation, dependency registry, integrity gates, change control, change-impact declarations, runtime-artifact/governed-evidence linkage, and closure manifests.

Important mutability rule:

- strategy authority is frozen during normal implementation/revalidation;
- governance standards are changed only according to their registered change class;
- `MUTABLE_TRACEABLE` governance registries/matrices are expected to update during governed execution;
- authority MUST NOT be changed merely to make implementation pass.

See `DOCUMENT_CHANGE_POLICY.md` for the controlling change rules.

Runtime/raw-proof boundary: [`RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`](RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md).
