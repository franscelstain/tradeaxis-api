# Legacy Semantic Extract — LX-MD-0024-EVD-03

- Source ID: `LS-MD-0024`
- Original path: `audit/EVIDENCE_EXPORT_RUNTIME_PROOF_INVENTORY.md`
- Original SHA1: `CE951167381AE5231B705EE619EA1FECEEC18A9E`
- Extract role: `EVIDENCE`
- Source range: `L166-L182`
- Extract body SHA1: `5D464E6F1E328B41727BE46BE6FC3411F445D85F`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-19 correction/replay runtime proof follow-up

- Correction runtime export was supplied for `correction_id=1` and produced `correction_evidence.json` plus `evidence_admission.json` with `ADMITTED_COMPLETE`.
- Review found the unchanged correction path incorrectly rendered `candidate_historical_publication_proof` as `FAILED / EVIDENCE_PUBLICATION_NOT_FOUND` even though the correction outcome was `UNCHANGED`, `NOT_RESEALED_UNCHANGED`, and current publication was preserved.
- Source patch changes unchanged/consumed-current correction candidate proof to `NOT_APPLICABLE / UNCHANGED_CORRECTION_CANDIDATE_DISCARDED` and adds a regression test in `CorrectionEvidenceExportServiceTest`.
- Replay runtime export was supplied for `replay_id=1`, `trade_date=2026-02-18`; admission was `ADMITTED_COMPLETE`, comparison was `MATCH`, status was `SUCCESS`, and all required replay artifacts were present.
- Full evidence export runtime proof is `LOCKED` after the patched correction evidence export was rerun locally and targeted/full MarketData PHPUnit proof was supplied.


## 2026-05-19 final full-selector lock proof

- Post-patch correction export command: `php artisan market-data:evidence:export --correction_id=1 --output_dir=storage/app/market-data/evidence/runtime-proof-correction-1-rerun`.
- Correction export result: `evidence_admission_state=ADMITTED_COMPLETE`, `file_count=2`, files `correction_evidence.json` and `evidence_admission.json`.
- Correction candidate proof result: `proof_status=NOT_APPLICABLE`, `evidence_reason_code=UNCHANGED_CORRECTION_CANDIDATE_DISCARDED`, `lineage_verification_status=NOT_APPLICABLE_UNCHANGED_CORRECTION`, no missing/critical admission sections.
- Replay export result: `replay_id=1`, `trade_date=2026-02-18`, `comparison_result=MATCH`, `status=SUCCESS`, `evidence_admission_state=ADMITTED_COMPLETE`, six required replay artifacts present.
- Final validation: `CorrectionEvidenceExportServiceTest.php` OK (2 tests, 38 assertions); `AuditDocsSynchronizationStaticGuardTest.php` OK (9 tests, 317 assertions); `tests/Unit/MarketData --filter "Evidence"` OK (55 tests, 1039 assertions); `tests/Unit/MarketData --filter "StaticGuard"` OK (169 tests, 3889 assertions); full `tests/Unit/MarketData` OK (451 tests, 6592 assertions).
- Final scope status: `EVIDENCE_EXPORT_RUNTIME_PROOF_CONTRACT -> LOCKED` for run + correction + replay selector evidence export runtime proof. This does not claim broader full MarketData production-ready.

<!-- LEGACY_EXTRACT_BODY_END -->
