# Legacy Semantic Extract — LX-MD-0035-EVD-03

- Source ID: `LS-MD-0035`
- Original path: `audit/MARKET_DATA_PRODUCTION_PROOF_PACK.md`
- Original SHA1: `9D1E95DE0523A6EFF6B7E31DF54056B51CB33F26`
- Extract role: `EVIDENCE`
- Source range: `L148-L245`
- Extract body SHA1: `AA016DED43CE5EEDA27634ADE30E6E6F88384897`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 6. Schema Proof

Schema/migration status is consumed from locked `DB_SCHEMA_AND_MIGRATION_SYNC_CONTRACT`, `DB_INTEGRITY_CONSTRAINT_ENFORCEMENT_CONTRACT`, and `DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_CONTRACT`. Current aggregate status: PASS for production-ready candidate. Operator-local proof records full MarketData PHPUnit PASS for this source state.

## 7. Coverage Proof

Current success promote proof for `run_id=33` records:

- `coverage_gate_state=PASS`
- `coverage_reason_code=COVERAGE_THRESHOLD_MET`
- `coverage_available_count=913`
- `coverage_universe_count=913`
- `coverage_ratio=1`
- `coverage_min_threshold=0.98`
- `coverage_universe_basis=ACTIVE_LISTED_EQUITY_AS_OF_DATE`

Held partial promote proof records `COVERAGE_BELOW_THRESHOLD` and `coverage_summary=available=5/913`, proving fail-closed not-readable behavior.

## 8. Read-Side Proof

Read-side scope remains `INTERNAL_ONLY` unless future HTTP/API consumers are introduced. Canonical reads are through current readable publication pointer resolution. Current proof pack consumes the locked read-side anti-bypass contract plus runtime proof showing successful current pointer publication `publication_id=27` and blocked/held paths with `pointer_switched=false`.

## 9. Evidence Export Proof

Run evidence admission artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/evidence-run-33/evidence_admission.json`.

- `selector_type=run`
- `selector_id=33`
- `evidence_admission_state=ADMITTED_COMPLETE`
- `missing_sections=[]`
- `critical_missing_sections=[]`
- `database_lookup_required_after_export=false`
- `deterministic_export=true`

Correction evidence admission artifact: `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json`, state `ADMITTED_COMPLETE`.

## 10. Replay Proof

Replay verify artifact: `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-verify-run-33/replay_result.json`.

- `replay_id=15`
- `replay_suite=runtime_generated_valid_case`
- `replay_case=ops_matrix_production_ready`
- `trade_date=2026-05-14`
- `publication_id=27`
- `publication_run_id=33`
- `publication_seal_state=SEALED`
- `current_pointer_status=RESOLVED_READABLE_CURRENT`
- `comparison_result=MATCH`
- `replay_status=PASS`
- `mismatch_count=0`

Replay smoke artifact proves valid/MISMATCH/BLOCKED cases with `all_passed=true`. Historical non-current replay proof remains present under `storage/app/market-data/full-production-ready/runtime/historical-replay/**` and proves `HISTORICAL_SEALED_PUBLICATION` resolution for `replay_id=8`.

## 11. Correction Proof

Correction lifecycle proof is LOCKED and consumed from `CORRECTION_LIFECYCLE_HARDENING_INVENTORY.md` plus artifacts:

- `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json`
- `storage/app/market-data/correction-lifecycle-hardening/failed-correction-4/evidence_admission.json`
- `storage/app/market-data/correction-lifecycle-hardening/replay-run-8/replay_result.json`

Required correction behaviors proven: valid baseline requirement, unchanged correction preserves pointer, failed correction does not publish a candidate, repair apply requires explicit reason, and correction evidence/replay lineage remains deterministic.

## 12. Hash / Seal Proof

Promote success proof for `run_id=33` records SHA-256 batch hashes and `seal_state=SEALED`:

- `bars_batch_hash=54e375d51bd2801e0d85f4cc0d17f7d795351b672940bcc2cebd533f36d6ca84`
- `indicators_batch_hash=4e504bb7da8644a305bfee444f64bf72aca8d60f6b7fe87ebe4392d858f7dfe9`
- `eligibility_batch_hash=b593ad09bb7a14550ca25c8e24db588de05dd41eb4f80403b807e147b16775a8`
- `sealed_at=2026-05-20 17:00:07`
- `lineage_verification_status=RUN_PUBLICATION_LINK_PRESENT`

## 13. Artifact Presence Proof

| Artifact | Status |
|---|---|
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/command-output/final-list-market-data.txt` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/evidence-run-33/evidence_admission.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-verify-run-33/replay_result.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-smoke-run-33/replay_smoke_suite_summary.json` | PRESENT |
| `storage/app/market-data/ops-command-surface-runtime-matrix-production-ready/replay-backfill-run-33/market_data_replay_backfill_summary.json` | PRESENT |
| `storage/app/market-data/correction-lifecycle-hardening/correction-3/evidence_admission.json` | PRESENT |
| `storage/app/market-data/correction-lifecycle-hardening/failed-correction-4/evidence_admission.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/fixtures/run-2-publication-2/manifest.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/verify-run-2-publication-2/replay_result.json` | PRESENT |
| `storage/app/market-data/full-production-ready/runtime/historical-replay/evidence-export-replay-8/evidence_admission.json` | PRESENT |

## 14. Audit Ledger Readiness

| Ledger | Current proof-pack action |
|---|---|
| `LUMEN_IMPLEMENTATION_STATUS.md` | Full production proof pack synchronized to `DONE` with `MARKET_DATA_PRODUCTION_READY_LOCKED` review status. **[SUPERSEDED 2026-08-06 — W22]** |
| `LUMEN_CONTRACT_TRACKER.md` | `FULL_MARKET_DATA_PRODUCTION_READY_CONTRACT` promoted to final `LOCKED`. **[SUPERSEDED 2026-08-06 — W22]** |
| `FULL_MARKET_DATA_PRODUCTION_READY_INVENTORY.md` | Updated from candidate state to final current-source `LOCKED` after this final audit sync consumed the ops matrix proof. **[SUPERSEDED 2026-08-06 — W22]** |
| `MARKET_DATA_PRODUCTION_PROOF_PACK.md` | Promoted from aggregate source-state proof inventory to final source-state production lock basis. |


<!-- LEGACY_EXTRACT_BODY_END -->
