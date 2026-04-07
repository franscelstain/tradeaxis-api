# LUMEN_CONTRACT_TRACKER.md

## External Source Operational Resilience

### Backfill Run-Backed Source Proof
- Status: CLOSED
- Verification: PASSED (previous full PHPUnit run recorded in prior session)
- Previous blockers:
  - Missing `market_calendar` table → RESOLVED
  - Incorrect retry assertion → RESOLVED

### Failure-Side Source Context Propagation
- Status: IMPLEMENTED / REGRESSION FIX APPLIED / PENDING LOCAL PROOF
- Scope:
  - persist minimum source-context notes on failed source-acquisition runs
  - expose `final_reason_code` in command/backfill/evidence summaries when present in run notes
- Repo evidence in this ZIP:
  - `app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  - `app/Application/MarketData/Services/MarketDataBackfillService.php`
  - `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
  - `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php`
- Waiting for:
  - local PHPUnit confirmation that API failure notes keep `source_name=API_FREE`
  - local PHPUnit confirmation that command-surface failure rendering no longer leaks `SourceAcquisitionException`
  - full regression confirmation

### Family status note
- Source-resilience family remains PARTIAL at project readiness level.
- Closure of one batch does not claim full live operational readiness.

- Latest regression fix tightens pipeline source telemetry so API failure-side notes/events always emit logical `API_FREE`, not provider default labels. Validation still depends on local PHPUnit.
