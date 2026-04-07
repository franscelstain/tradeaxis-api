# LUMEN_CONTRACT_TRACKER.md

## External Source Operational Resilience

### Backfill Run-Backed Source Proof

* Status: CLOSED
* Verification: PASSED (previous full PHPUnit run recorded in prior session)
* Previous blockers:

  * Missing `market_calendar` table → RESOLVED
  * Incorrect retry assertion → RESOLVED

### Exception-Path Operator Summary Recovery

* Status: DONE

* Scope:

  * keep failure-side source notes authoritative in persisted runs
  * recover latest run summary for operator-facing `market-data:daily` / `market-data:backfill` exception paths
  * preserve `final_reason_code` inside recovered operator summaries when present in run notes

* Repo evidence:

  * `app/Infrastructure/Persistence/MarketData/EodRunRepository.php`
  * `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  * `app/Console/Commands/MarketData/DailyPipelineCommand.php`
  * `app/Application/MarketData/Services/MarketDataBackfillService.php`
  * `app/Application/MarketData/Services/MarketDataPipelineService.php`
  * `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  * `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  * `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

* Regression encountered during implementation:

  * Constructor breaking change in `MarketDataBackfillService`
  * Non-deterministic run selection (latest global run used instead of scoped run)
  * Ops command test binding mismatch (container resolving different abstract)

* Resolution:

  * Constructor made backward-compatible (optional dependency)
  * Recovery logic aligned to scoped/deterministic run selection
  * Ops command test updated to bind concrete `EodRunRepository` used by command
  * Command container resolution corrected

* Verification:

  * Full PHPUnit run:

    * 163 tests
    * 1714 assertions
    * 0 failures
    * 0 errors

### Family status note

* This batch is CLOSED and verified.
* External source resilience at system level remains PARTIAL until runtime/live validation is proven.
