# Legacy Semantic Extract — LX-MD-0020-EVD-02

- Source ID: `LS-MD-0020`
- Original path: `audit/CORRECTION_LIFECYCLE_HARDENING_INVENTORY.md`
- Original SHA1: `CF3BD55641F75EDA47DC3EB456D1824632863949`
- Extract role: `EVIDENCE`
- Source range: `L94-L121`
- Extract body SHA1: `EEA69B0DEA70AE8C55014641DDABE71E4CA55418`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Final Lock Patch Addendum - Unchanged Evidence Candidate Alias Fix

- Status: LOCKED_LOCAL_RUNTIME_PROOF.
- The final audit found `correction-3/correction_evidence.json` still aliasing baseline/current publication `5` as `candidate_publication_id`, `new_publication.publication_id`, and `candidate_historical_publication_proof.publication_id`.
- Patch outcome:
  - `correction_lifecycle.baseline_publication_id=5`.
  - `correction_lifecycle.preserved_publication_id=5`.
  - `correction_lifecycle.candidate_publication_id=7`.
  - `correction_lifecycle.discarded_candidate_publication_id=7`.
  - `correction_lifecycle.replacement_publication_id=null`.
  - `new_publication=null`.
  - `candidate_historical_publication_proof.publication_id=7`.
  - `candidate_historical_publication_proof.proof_status=DISCARDED_CANDIDATE_RECORDED`.
  - `publication_switch=false`.
- Code source for discarded candidate is traceable through `new_run.notes` keys `discarded_candidate_publication_id` and `candidate_publication_id`; baseline fallback is explicitly blocked for unchanged evidence.
- Container validation:
  - Changed PHP files passed `php -l`.
  - Container Artisan/PHPUnit runtime proof remains blocked by PHP baseline mismatch and missing extensions.
  - Operator-local final-lock rerun supplied: `CorrectionEvidenceExportServiceTest.php` OK (2 tests, 51 assertions), `CorrectionLifecycleSafetyStaticGuardTest.php` OK (5 tests, 78 assertions), Correction filter OK (75 tests, 1438 assertions), Evidence filter OK (56 tests, 1071 assertions), and Replay filter OK (58 tests, 894 assertions).
  - StaticGuard/AuditDocs failure was limited to stale audit-ledger status text; this patch updates the canonical correction implementation to DONE and the contract to LOCKED.
- Required follow-up validation after this ledger patch:
  - Rerun `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"`.
  - Rerun `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"`.
  - Rerun full `vendor/bin/phpunit tests/Unit/MarketData` before using this ZIP as an aggregate production-ready proof pack.


---


<!-- LEGACY_EXTRACT_BODY_END -->
