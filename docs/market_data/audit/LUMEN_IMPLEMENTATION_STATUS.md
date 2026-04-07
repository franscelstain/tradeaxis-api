# LUMEN_IMPLEMENTATION_STATUS.md

## SESSION UPDATE

* Batch: Run Evidence Source Attempt Telemetry Export
* Status: PARTIAL

### What was implemented

* Re-audited the active checkpoint against the live repo and confirmed the next narrow gap that still fit the owner docs was the missing operator-friendly export of attempt-level source acquisition telemetry from persisted run events.
* Added attempt-level run-evidence extraction in `EodEvidenceRepository` so evidence export can read the latest persisted source-acquisition attempt trail from `eod_run_events.event_payload_json` for either:
  * success-side ingest completion payloads (`source_acquisition`)
  * failure-side ingest exceptions (`exception_context`)
* Extended `MarketDataEvidenceExportService` so requested-date run evidence now also:
  * builds `source_attempt_telemetry` from persisted run events
  * writes `source_attempt_telemetry.json` when attempt telemetry exists
  * embeds the same structure into `evidence_pack.json`
  * exposes minimal operator summary fields (`source_attempt_event_type`, `source_attempt_count`) alongside the existing source summary
* Updated PHPUnit surface for run-evidence export so the new artifact and payload shape are asserted in `MarketDataEvidenceExportServiceTest`.
* Synced owner/ops docs so attempt-level run-evidence export is now documented as a bounded companion artifact instead of an implicit raw-table-only capability.

### Drift / gap that was found

* Owner docs already allowed richer source-resilience telemetry at event level, but run evidence export still stopped at note-derived summary fields only.
* That meant retry/backoff attempt diagnosis still required manual inspection of raw `eod_run_events` rows even though the evidence-export path already existed.
* This was a real auditability gap inside the still-partial external-source operational-resilience family.

### Evidence available from this ZIP

* Code inspection parity shows attempt-level source telemetry is now exported from persisted run events into run evidence artifacts.
* Local syntax proof in container:
  * `php -l app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` → PASS
  * `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` → PASS
  * `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → PASS
* Companion docs synced with the new bounded evidence artifact:
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/market_data/ops/Run_Artifacts_Format_LOCKED.md`
  * `docs/market_data/ops/Audit_Evidence_Pack_Contract_LOCKED.md`

### What is still pending

* PHPUnit proof for the changed evidence-export test has not been executed from this ZIP because `vendor/` is not included here.
* Family-level `External Source Operational Resilience` remains partial because live-source runtime proof and broader operator/dashboard hardening are still outside this batch.

### Final State

* PARTIAL
