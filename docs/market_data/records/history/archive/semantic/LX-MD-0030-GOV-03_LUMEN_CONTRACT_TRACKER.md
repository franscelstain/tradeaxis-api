# Legacy Semantic Extract — LX-MD-0030-GOV-03

- Source ID: `LS-MD-0030`
- Original path: `audit/LUMEN_CONTRACT_TRACKER.md`
- Original SHA1: `88A4CD4C9D12A578A2837DCB275B315C91D1492A`
- Extract role: `GOVERNANCE`
- Source range: `L4081-L4207`
- Extract body SHA1: `621EFA488819D358DAE7060851A9493C202B98E3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL CHECKPOINT + RESUME MINOR NOTES CONTRACT UPDATE

[CONTRACT_STATUS]
- `DONE`.

[DIAGNOSTIC_REASON_CONTRACT]
- `source_acquisition_diagnostics.json.reason_code` must not be `null` when a failed retry/checkpoint has a valid source failure reason.
- Resolution order is deterministic:
  1. explicit summary/source reason,
  2. dominant failed checkpoint reason by count,
  3. tie-break by `window_start ASC`, `window_end ASC`, `ticker_code ASC`, `reason_code ASC`,
  4. `null` only when no failed reason exists.
- No-op resume may use `NO_FAILED_SOURCE_ACQUISITION_CHECKPOINT` according to the existing no-failed-checkpoint contract.

[CACHE_SLIMMING_CONTRACT]
- `source_acquisition_cache.json` is now a slim operational cache, not a full acquisition payload.
- Required cache safety:
  - valid JSON,
  - no raw provider payloads,
  - no full `rows_by_trade_date`,
  - no full `source_acquisition_checkpoints`,
  - no duplicated nested diagnostic/checkpoint context,
  - no token/auth/signature leaks,
  - `error_sample` and `provider_error_sample` capped at 500 chars.
- `source_acquisition_checkpoint.json` remains authoritative for full failed window/ticker retry identity.
- Slim cache is sufficient for `--resume --only-failed` in combination with checkpoint JSON; it intentionally does not claim full row-resume capability.

[VALIDATION_PROOF]
- Targeted filters after cleanup:
  - `ApiBackfill` -> OK (25 tests, 153 assertions)
  - `Backfill` -> OK (44 tests, 292 assertions)
  - `StaticGuard` -> OK (219 tests, 5386 assertions)
- Runtime resume-only-failed proof rewrote diagnostic/cache artifacts with top-level `reason_code=RUN_SOURCE_BAD_REQUEST` and `cache_format=source_acquisition_resume_v2_slim`.

---

## 2026-05-26 - API BACKFILL CHECKPOINT + RESUME FINAL FULL-SUITE CONTRACT LOCK

[CONTRACT_STATUS]
- `FULL_LOCKED`.
- `FULL_PRODUCTION_READY` for the checkpoint/resume diagnostic and slim-cache contract.

[FINAL_VALIDATION_PROOF]
- Command: `vendor\bin\phpunit tests\Unit\MarketData`.
- Result: OK (562 tests, 8503 assertions).
- Runtime: Time 00:20.909, Memory 42.00 MB.

[FINAL_RULE]
- Diagnostic top-level reason-code resolution, slim source acquisition cache, checkpoint telemetry isolation, resume-only-failed accounting, and `FAILED_RETRY_BLOCKED` retry semantics are locked for this scope.
- Future changes touching API backfill checkpoint/resume diagnostics, source acquisition cache, or failed checkpoint retry state must rerun targeted `ApiBackfill`, `Backfill`, `StaticGuard`, and full `tests\Unit\MarketData`.

---

## 2026-05-26 - OUT-OF-ORDER IMPORT MUTATION IMPACT CONTRACT UPDATE

[CONTRACT_STATUS]
- `DONE` for global mutation summary and impact telemetry.
- Superseded by later publication reprocess contracts: automatic downstream non-readable promotion and readable correction-current republication are now part of the locked impact flow when validation proof is current.

[MUTATIO N_CONTRACT]
- Every normal EOD bar replacement through `EodArtifactRepository::replaceBars()` must return `bar_mutation_summary`.
- The summary must distinguish inserted, updated, unchanged, and removed canonical bar rows.
- Idempotent re-imports with identical canonical OHLCV/source values must produce `changed_bar_count=0` and `indicator_reprocess_state=NOOP_UNCHANGED_BARS`.
- Historical changed bars must expose changed ticker ids and changed trade dates for downstream dependency resolution.

[INDICATOR_IMPACT_CONTRACT]
- Affected indicator dates are resolved in market-calendar trading days, not calendar days.
- The max dependency horizon is derived from active indicator config and must include `dv20_idr`, `atr14_pct`, `vol_ratio`, `roc20`, `hh20`, `ma20`, and `ma50`.
- The current implementation uses the configured windows plus an MA50 floor, producing `max_indicator_dependency_trading_days=50` for the baseline registry.
- Command/evidence summaries must report `affected_ticker_count`, `affected_trade_date_count`, `affected_start_date`, `affected_end_date`, `max_indicator_dependency_trading_days`, and `indicator_reprocess_state`.

[PUBLICATION_IMPACT_CONTRACT]
- If affected dates include a current readable publication, the system must report `publication_impact_state=REQUIRES_REPUBLICATION` and reason `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`.
- A readable publication must not be mutated silently. Correction/reseal/republication must remain the safe path.
- A failed or blocked source retry must not create fake readable state or replay verification.

[VALIDATION_PROOF]
- `EodBarsMutationImpactResolver` -> OK (3 tests, 13 assertions).
- `OutOfOrderImportImpact` static guard -> OK (3 tests, 32 assertions).
- `Backfill` -> OK (44 tests, 292 assertions).
- `ApiBackfill` -> OK (25 tests, 153 assertions).
- `StaticGuard` -> OK (222 tests, 5430 assertions).
- Full suite: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (568 tests, 8560 assertions).

---

## 2026-05-27 - OUT-OF-ORDER IMPORT IMPACT EXECUTION CONTRACT UPDATE

[CONTRACT_STATUS]
- `DONE` for recovered row partial apply and non-readable affected-date derived reprocess execution.
- Superseded by later correction-current contract: readable affected dates become publication reprocess candidates and must use correction-current mode, not normal full-publish.

[RECOVERED_ROW_APPLY_CONTRACT]
- Resume-only-failed retry success must not return after source acquisition if recovered rows exist.
- Recovered rows must be applied by partial ticker/date upsert.
- Full-date `replaceBars()` is forbidden for recovered single-ticker/window rows because it can remove unrelated tickers for the same trade date.
- Partial apply must report `recovered_row_apply_state`, `recovered_row_count`, and `bar_mutation_summary`.
- Idempotent recovered rows with identical canonical OHLCV/source values must produce `changed_bar_count=0` and no unnecessary derived reprocess.

[EXECUTION_CONTRACT]
- Changed bars with affected non-readable dates must execute indicator recompute and eligibility rebuild, not merely report that reprocess is required.
- Execution summaries are mandatory:
  - `indicator_reprocess_execution_summary`
  - `eligibility_reprocess_execution_summary`
  - `publication_reprocess_summary`
- If execution is blocked or failed, command/evidence output must show the blocked/failure reason.

[READABLE_PUBLICATION_CONTRACT]
- Already-readable affected dates must not be silently updated.
- Current behavior is correction-current candidate handling:
  - `publication_reprocess_summary.execution_state=PENDING_PROMOTE`
  - `readable_correction_candidate_trade_dates` includes the impacted readable dates
  - correction id lineage is emitted after automated correction-current promotion
  - no pointer switch without correction lineage, seal/finalize, and pointer validation
  - no fake readable or replay verification for an unresealed replacement

[VALIDATION_PROOF]
- `MarketDataImpactReprocessExecutor` -> OK (3 tests, 11 assertions).
- `EodArtifactRepositoryPartialUpsert` -> OK (2 tests, 14 assertions).
- `OutOfOrderImportImpact` -> OK (5 tests, 57 assertions).
- `Recovered` -> OK (7 tests, 56 assertions).
- `Resume` -> OK (8 tests, 61 assertions).
- `StaticGuard` -> OK (224 tests, 5467 assertions).
- Full suite proof is pending rerun after docs update.

---


<!-- LEGACY_EXTRACT_BODY_END -->
