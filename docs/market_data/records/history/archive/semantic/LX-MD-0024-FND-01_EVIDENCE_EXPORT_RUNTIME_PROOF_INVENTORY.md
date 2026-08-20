# Legacy Semantic Extract — LX-MD-0024-FND-01

- Source ID: `LS-MD-0024`
- Original path: `audit/EVIDENCE_EXPORT_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `CE951167381AE5231B705EE619EA1FECEEC18A9E`
- Extract role: `FINDING`
- Source range: `L155-L165`
- Extract body SHA1: `F5F78F2FB1276B5812D1EF261CDB1997FAB1DF96`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Remaining risk

- Container runtime artifact proof is not produced; this is historical/support context only.
- Evidence export full selector runtime proof is LOCKED_LOCAL_RUNTIME_PROOF_FULL_SELECTOR based on operator-local run, correction, and replay artifacts plus targeted/full PHPUnit validation.
- Run selector evidence export is ADMITTED_COMPLETE / COMPLETE for `run_id=2`.
- Correction selector evidence export is ADMITTED_COMPLETE for `correction_id=1`; post-patch rerun proves candidate proof `NOT_APPLICABLE / UNCHANGED_CORRECTION_CANDIDATE_DISCARDED`.
- Replay selector evidence export is ADMITTED_COMPLETE for `replay_id=1` / `2026-02-18` with `comparison_result=MATCH` and `status=SUCCESS`.
- Replay artifact proof supplied: `storage/app/market-data/evidence/runtime-proof-replay-1-2026-02-18/replay_result.json`, `replay_expected_state.json`, `replay_actual_state.json`, `replay_reason_code_counts.json`, `evidence_admission.json`, and `replay_evidence_pack.json`.
- This session does not close broader replay determinism runtime proof, ops runtime matrix, production proof pack, or final roadmap audit synchronization.



<!-- LEGACY_EXTRACT_BODY_END -->
