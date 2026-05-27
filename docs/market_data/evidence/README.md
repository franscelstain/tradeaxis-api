# Evidence Archive

## Purpose
This folder stores archived actual execution evidence only.

It is separate from `../examples/`, which may contain illustrative or representative structures. This archive is a repository of produced evidence, not a normative owner of new domain behavior. Any behavioral rule referenced by archived evidence must trace back to the authoritative contracts in `../book/`, `../ops/`, `../tests/`, and other normative companion folders.

## Evidence classes
- `runs/` for actual executed run evidence
- `replays/` for actual executed replay evidence
- `corrections/` for actual executed correction evidence
- `tests/` for actual executed test evidence

## Admission rule
Artifacts in this folder must satisfy:
- real execution identity
- real produced values
- traceable origin
- no placeholder-only bundles

## Actual archived evidence only
The `evidence/` tree is reserved for archived actual evidence bundles.

The following are invalid under `evidence/`:
- future-dated artifacts
- fabricated or symbolic timestamps
- shape-only examples
- representative demo bundles
- redacted bundles that no longer preserve execution traceability

If a bundle is illustrative, simulated, representative, or otherwise non-admissible as actual archived proof, it must live under `../examples/`, not under `evidence/`.

## Required bundle metadata
Each bundle under `evidence/` must carry `evidence_admission.json` with at least:
- `bundle_type`
- `bundle_id`
- `actuality_status`
- `admission_status`
- execution identity such as `run_id`, `replay_id`, `correction_id`, or `test_id`
- `archived_at` or equivalent executed timestamp
- `traceability_ref`
- `source_contract_refs`
- `redaction_status`

## Evidence placement rule
Use this decision rule:
- actual + traceable + historically valid timestamp -> keep under `evidence/`
- illustrative/simulated/example-only/non-traceable -> move to `../examples/`

## Cross-contract alignment
See:
- `../ops/Archived_Actual_Execution_Evidence_Contract_LOCKED.md`
- `../ops/Executed_Run_Admission_Criteria_LOCKED.md`
- `../ops/Run_Execution_Evidence_Pack_Contract_LOCKED.md`
- `../tests/Executed_Proof_Admission_Criteria_LOCKED.md`
- `../examples/ARCHIVED_EVIDENCE_FOLDER_STRUCTURE_LOCKED.md`

## Amendment 2026-05-26 - Mutation impact evidence
When a run evidence bundle includes EOD bar import output, the run summary may carry:
- `bar_mutation_summary`
- `indicator_impact_summary`
- `publication_impact_summary`

These fields are operational proof fields. They must remain bounded summaries and must not include raw provider payloads, credentials, or large duplicated checkpoint bodies.

## Amendment 2026-05-27 - Reprocess execution evidence
Evidence bundles may include execution summaries proving whether affected derived artifacts were actually recomputed:
- `indicator_reprocess_execution_summary`
- `eligibility_reprocess_execution_summary`
- `publication_reprocess_summary`
- `resume_recovered_apply_summary`

If already-readable affected dates are blocked, evidence should carry `BLOCKED_REQUIRES_CORRECTION` and `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`. This is not replay proof for a replacement publication; replay remains valid only after a sealed readable replacement exists.

## 2026-05-27 - Publication Reprocess Evidence

For affected non-readable downstream dates, lifecycle/full-publish reprocess may call the normal promote flow and then export evidence/replay proof for the republished downstream run. Evidence may report `publication_reprocess_summary.execution_state=REPUBLISHED` plus `republished_trade_dates`, `evidence_exported_count`, `fixtures_generated_count`, and `replay_verified_count`.

For already-readable affected dates, evidence must still report `BLOCKED_REQUIRES_CORRECTION`; it must not imply that a replacement publication was replay-verified unless the correction/republication lifecycle has actually produced a sealed readable replacement.


## Amendment 2026-05-27 - Final readable auto-correction evidence rule

After final validation, already-readable affected dates are no longer limited to safe-block-only behavior when the lifecycle can run automated correction. Evidence may report an automated readable correction only when the system has actually created/approved a correction request and promoted through correction-current mode.

Valid evidence states are:

- `REPUBLISHED` with `republication_mode=AUTOMATED_IMPACT_REPUBLICATION` only after a replacement run is sealed/readable and, when requested, evidence/replay proof is generated.
- `BLOCKED_REQUIRES_CORRECTION` when baseline resolution, correction approval, correction-current promotion, pointer validation, evidence, or replay cannot complete safely.

Final local proof for this evidence contract:

- `BackfillLifecyclePublicationReprocess` -> OK (3 tests, 12 assertions).
- `OutOfOrderImportImpact` -> OK (7 tests, 96 assertions).
- Full MarketData suite -> OK (582 tests, 8678 assertions).
