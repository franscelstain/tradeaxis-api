# Legacy Semantic Extract — LX-MD-0030-IMP-04

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `IMPLEMENTATION`
- Source range: `L4250-L4328`
- Extract body SHA1: `6E5C7E961416FE12BFE116FB73C2CE73E90FF38D`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-27 - OUT-OF-ORDER IMPORT HASH/SEAL/PUBLICATION REPROCESS CONTRACT

[CONTRACT_STATUS]
- `DONE` for affected non-readable downstream date publication reprocess in lifecycle/full-publish paths.
- `DONE` for automated correction/republication of already-readable affected dates through correction-current mode.

[PUBLICATION_REPROCESS_CONTRACT]
- After changed EOD bars execute indicator and eligibility reprocess for affected non-readable dates, the impact state must become `PENDING_PROMOTE` until publication stages run.
- A lifecycle/full-publish path may consume `PENDING_PROMOTE` by calling the existing `promoteDaily()` flow for each affected non-readable date.
- `promoteDaily()` remains the only automatic path used here because it already enforces coverage, recomputes indicators/eligibility, computes hashes, seals, finalizes, and validates publication readability.
- If the affected date is already current/readable, automatic reprocess must not call normal full-publish. It must emit a correction-current candidate and promote only through the correction lifecycle.
- The primary requested date must not remain `PENDING_PROMOTE` after its normal primary promote already handled hash/seal/finalize; report `NOOP` with `REQUESTED_DATE_PROMOTED_BY_PRIMARY_PIPELINE`.

[ARTIFACT_CONTRACT]
- Publication reprocess summaries may include:
  - `execution_state=PENDING_PROMOTE|REPUBLISHED|BLOCKED_REQUIRES_CORRECTION|FAILED|NOOP`
  - `candidate_trade_dates`
  - `republished_trade_dates`
  - `blocked_trade_dates`
  - `failed_trade_dates`
  - `evidence_exported_count`
  - `fixtures_generated_count`
  - `replay_verified_count`
  - `republication_mode`
  - `correction_ids`
  - `correction_id`
- A `REPUBLISHED` state means affected dates were promoted through existing hash/seal/finalize gates; already-readable dates must additionally carry correction-current mode and correction id lineage.

[VALIDATION_PROOF]
- `BackfillLifecyclePublicationReprocess` -> OK (4 tests, 19 assertions).
- `MarketDataPipelineService` -> OK (16 tests, 21 assertions).
- `OutOfOrderImportImpactStaticGuard` -> OK (6 tests, 73 assertions).
- Full suite proof is refreshed in the final readable auto-correction validation entry.

---

## 2026-05-27 - IMPORT-ONLY BACKFILL REPROCESS OUTPUT + READABLE AUTO-CORRECTION CONTRACT

[CONTRACT_STATUS]
- `DONE` for import-only backfill command/summary output surfacing execution-layer impact fields.
- `DONE` for already-readable affected-date automated correction orchestration in lifecycle/full-publish publication reprocess.

[READABLE_AUTO_CORRECTION_CONTRACT]
- If an affected downstream date is already current/readable, publication reprocess must not run normal full-publish over the current pointer.
- It must create an explicit correction request with reason `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` using the current sealed readable coverage-PASS baseline publication.
- It must approve that correction and call the existing correction-current promote path.
- Replacement publication is valid only if existing correction guards pass coverage, hash, seal, finalize, and pointer validation.
- If baseline/correction execution fails, the system must report the failure reason and must not fake readable/current state.

[IMPORT_ONLY_OUTPUT_CONTRACT]
- Plain `market-data:backfill` import-only output and `market_data_backfill_summary.json` must include execution-layer fields when present in run notes:
  - `indicator_reprocess_execution_state`
  - `indicator_reprocessed_trade_date_count`
  - `indicator_reprocessed_trade_dates`
  - `eligibility_reprocess_execution_state`
  - `eligibility_reprocessed_trade_date_count`
  - `eligibility_reprocessed_trade_dates`
  - `publication_reprocess_state`
  - `publication_reprocess_republished_trade_date_count`
  - `publication_reprocess_republished_trade_dates`
  - `publication_reprocess_candidate_trade_dates`
  - `publication_reprocess_readable_correction_candidate_trade_dates`
  - `publication_reprocess_blocked_trade_dates`
  - `publication_reprocess_failed_trade_dates`
  - `publication_reprocess_blocked_reason_code`
  - `publication_reprocess_failure_reason_code`
  - `publication_reprocess_republication_mode`
  - `publication_reprocess_correction_ids`
  - `publication_reprocess_correction_id`
  - `recovered_row_apply_state`
  - `recovered_row_count`

[VALIDATION_STATUS]
- Syntax validation passed for all touched PHP files in the sandbox.
- PHPUnit execution in the sandbox is blocked by missing PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter`; local project environment must rerun targeted and full MarketData suites before changing status to final `LOCKED`.


---


<!-- LEGACY_EXTRACT_BODY_END -->
