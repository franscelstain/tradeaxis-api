# LUMEN_CONTRACT_TRACKER.md

## External Source Operational Resilience

### Backfill Run-Backed Source Proof
- Status: CLOSED
- Verification: PASSED (previous full PHPUnit run recorded in prior session)
- Previous blockers:
  - Missing `market_calendar` table → RESOLVED
  - Incorrect retry assertion → RESOLVED

### Exception-Path Operator Summary Recovery
- Status: IMPLEMENTED / REGRESSION FIX APPLIED / PENDING LOCAL PROOF
- Scope:
  - keep failure-side source notes authoritative in persisted runs
  - recover latest run summary for operator-facing `market-data:daily` / `market-data:backfill` exception paths
  - preserve `final_reason_code` inside recovered operator summaries when present in run notes
- Repo evidence in this ZIP:
  - `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
  - `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  - `app/Console/Commands/MarketData/DailyPipelineCommand.php`
  - `app/Application/MarketData/Services/MarketDataBackfillService.php`
  - `app/Application/MarketData/Services/MarketDataPipelineService.php`
  - `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  - `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  - `tests/Unit/MarketData/OpsCommandSurfaceTest.php`
- Waiting for:
  - local PHPUnit confirmation that command container resolution preserves mocked/scoped run lookup for `market-data:daily` exception recovery
  - local PHPUnit confirmation that `MarketDataBackfillService` remains backward-compatible for existing two-argument construction paths while preserving `ERROR` case recovery when a failed run exists
  - full regression confirmation

### Family status note
- Source-resilience family remains PARTIAL at project readiness level.
- Closure of one batch does not claim full live operational readiness.

- Latest batch fixes the discovered constructor + command binding regression while preserving operator recovery. Validation still depends on local PHPUnit re-run.
