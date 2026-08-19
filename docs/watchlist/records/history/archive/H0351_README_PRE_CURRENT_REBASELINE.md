# Watchlist Governance

`authority/governance/` adalah current process/change authority untuk Watchlist. Ia bukan owner business rule Weekly Swing; business behavior tetap berada di [`../strategy/`](../strategy/README.md).

## Read Order

1. [`DOCUMENTATION_ARCHITECTURE.md`](DOCUMENTATION_ARCHITECTURE.md) — root `authority / development / records` dan ownership.
2. [`DOCUMENT_RECORDING_STANDARD.md`](DOCUMENT_RECORDING_STANDARD.md) — lifecycle/mutability/no-silent-update.
3. [`WORK_BASELINE_LOCK_STANDARD.md`](WORK_BASELINE_LOCK_STANDARD.md) — exact starting authority/source baseline.
4. [`WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md`](WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md) — Work ID + current record relationships.
5. [`CHANGE_IMPACT_DECLARATION_STANDARD.md`](CHANGE_IMPACT_DECLARATION_STANDARD.md) — planned/actual change impact.
6. [`STAGE_EXECUTION_AND_REWORK_STANDARD.md`](STAGE_EXECUTION_AND_REWORK_STANDARD.md) — attempts, convergence, strict closure.
7. [`DEPENDENCY_REGISTRY_STANDARD.md`](DEPENDENCY_REGISTRY_STANDARD.md) — verified dependency + resume trigger.
8. [`IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md) — recurring residue/conformance gate.
9. [`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`](STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md) — rule-level coverage.
10. [`DOCUMENT_INTEGRITY_GATE_STANDARD.md`](DOCUMENT_INTEGRITY_GATE_STANDARD.md) — executable structural + relationship integrity gates.
11. [`STAGE_CLOSURE_MANIFEST_STANDARD.md`](STAGE_CLOSURE_MANIFEST_STANDARD.md) — terminal stage evidence manifest.
12. [`CURRENT_STATE_SUMMARY_STANDARD.md`](CURRENT_STATE_SUMMARY_STANDARD.md) — generated current state.
13. [`STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`](STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv) — current strategy coverage index.
14. [`DOCUMENT_CHANGE_POLICY.md`](DOCUMENT_CHANGE_POLICY.md) — controlled strategy revision.
15. [`WATCHLIST_DOCUMENT_AUTHORITY.md`](WATCHLIST_DOCUMENT_AUTHORITY.md) — conflict resolution.
16. [`WATCHLIST_OWNER_MATRIX.md`](WATCHLIST_OWNER_MATRIX.md) — owner/mutability mapping.
17. [`DOCUMENT_CHANGE_LOG.md`](DOCUMENT_CHANGE_LOG.md) — append-only material documentation changes.

## Current Working / Record Pointers

- attempt baseline standard: [`WORK_BASELINE_LOCK_STANDARD.md`](WORK_BASELINE_LOCK_STANDARD.md)
- executable integrity standard: [`DOCUMENT_INTEGRITY_GATE_STANDARD.md`](DOCUMENT_INTEGRITY_GATE_STANDARD.md)
- attempt record template: [`../../development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md`](../../development/implementation/examples/WS_STAGE_ATTEMPT_RECORD_TEMPLATE.md)
- current work registry: [`../../records/WORK_RECORD_REGISTRY.csv`](../../records/WORK_RECORD_REGISTRY.csv)
- dependency registry: [`../../development/implementation/WS_DEPENDENCY_REGISTRY.csv`](../../development/implementation/WS_DEPENDENCY_REGISTRY.csv)
- generated current state: [`../../development/implementation/CURRENT_STATE.md`](../../development/implementation/CURRENT_STATE.md)
- current stage resume: [`../../development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](../../development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md)
- build sequence: [`../../development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md`](../../development/implementation/WS_IMPLEMENTATION_BUILD_SEQUENCE.md)
- implementation contract/status tracker: [`../../development/implementation/LUMEN_CONTRACT_TRACKER.md`](../../development/implementation/LUMEN_CONTRACT_TRACKER.md)
- current findings: [`../../development/findings/`](../../development/findings/README.md)
- evidence: [`../../records/evidence/`](../../records/evidence/README.md)
- issued decisions: [`../../records/decisions/`](../../records/decisions/README.md)
- immutable history: [`../../records/history/`](../../records/history/README.md)

Tidak ada semantic document class yang bebas diubah tanpa trace.

- explicit cross-attempt relationship registry: [`../../records/WORK_RELATIONSHIP_REGISTRY.csv`](../../records/WORK_RELATIONSHIP_REGISTRY.csv)

## Document role purity

- [`ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md`](ONE_DOCUMENT_ONE_AUTHORITATIVE_ROLE_STANDARD.md) — satu file = satu authoritative role; cross-role information harus direferensikan, bukan digabung sebagai authority kedua.
- [`DOCUMENT_ROLE_REGISTRY.csv`](DOCUMENT_ROLE_REGISTRY.csv) — machine-readable current physical document role registry.
