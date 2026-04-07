# LUMEN_IMPLEMENTATION_STATUS.md

## SESSION UPDATE

* Batch: Run Evidence Source Context Recovery From Attempt Telemetry
* Status: DONE

### What was implemented

* Re-audited the checkpoint against the uploaded repo and selected one narrow follow-up gap inside the still-partial `External Source Operational Resilience` family: run evidence export still trusted `eod_runs.notes` as the only source for operator-facing `source_summary`, even when richer attempt telemetry was already persisted in `eod_run_events`.
* Extended `MarketDataEvidenceExportService` so run evidence export now merges missing minimum source-context fields from persisted attempt telemetry back into exported `run_summary.json`, `evidence_pack.json`, and CLI summary output.
* Recovery is bounded and non-inventive: it only fills missing minimum fields already present in persisted attempt telemetry (`provider`, `timeout_seconds`, `retry_max`, `attempt_count`, `success_after_retry`, `final_http_status`, `final_reason_code`, plus `source_name` / `source_input_file` when present).
* Added PHPUnit coverage for the thin-notes path so evidence export proves operator-facing source summary still works when `notes` only contain `source_name` but attempt telemetry carries the rest of the minimum resilience context.
* Synced owner/ops docs so this recovery path is explicit companion behavior rather than an implicit implementation detail.
* After local PHPUnit feedback exposed a publication-resolution regression on thin-notes fallback runs, corrected `resolvePublicationForRun()` so run evidence export resolves the current publication for `trade_date_effective` whenever fallback readability is in force, instead of limiting current-publication lookup to requested-date readable runs only.

### Drift / gap that was found

* The previous batch exported `source_attempt_telemetry.json`, but `run_summary.json` and the `market-data:evidence:export` summary could still degrade to a thin or blank `source_summary` when persisted notes were sparse.
* That meant operator-facing evidence export was still weaker than the already-persisted attempt trail, despite both artifacts belonging to the same bounded run-evidence surface.
* Local PHPUnit feedback then exposed a narrower code/test drift: failed or held runs with `trade_date_effective = last_good_trade_date` still needed evidence export to resolve the current publication for that fallback effective date, but the implementation only performed current-publication lookup on requested-date readable runs.

### Evidence available from this session

* Code inspection parity shows run evidence export now recovers missing minimum source context from persisted attempt telemetry before writing operator-facing summaries.
* Local syntax proof in container:
  * `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` → PASS
  * `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → PASS
* Code inspection after the failing local PHPUnit run confirms the publication resolver now follows effective-date fallback semantics for evidence export as required by the readability contracts.
* Local PHPUnit proof received from the user after the fix:
  * `vendor\bin\phpunit tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → PASS (`2 tests, 52 assertions`)
  * `vendor\bin\phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS (`25 tests, 140 assertions`)
  * `vendor\bin\phpunit` → PASS (`164 tests, 1742 assertions`)
* Companion docs synced with the bounded recovery behavior:
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/market_data/ops/Run_Artifacts_Format_LOCKED.md`
  * `docs/market_data/ops/Audit_Evidence_Pack_Contract_LOCKED.md`

### What is still pending

* No additional proof is pending for this batch.
* Family-level `External Source Operational Resilience` remains partial only at the broader owner-doc family level because live-source runtime proof and broader operator/dashboard hardening are still outside this closed batch.

### Final State

* DONE (batch closed with local PHPUnit proof; broader family still PARTIAL outside this batch)
