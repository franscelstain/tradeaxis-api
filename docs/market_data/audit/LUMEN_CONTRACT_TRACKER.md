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

### Coverage BLOCKED Final-State Parity

* Status: PARTIAL

* Scope:

  * align runtime-visible coverage final-state output with the active owner contract
  * remove `NOT_EVALUABLE` as a persisted/operator/test-visible final `coverage_gate_state`
  * preserve the locked blocked-path reason code `RUN_COVERAGE_NOT_EVALUABLE`

* Owner-doc anchor:

  * `docs/market_data/book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
  * `docs/market_data/book/Run_Status_and_Quality_Gates_LOCKED.md`
  * `docs/market_data/book/EOD_Cutoff_and_Finalization_Contract_LOCKED.md`

* Repo evidence:

  * `app/Application/MarketData/Services/CoverageGateEvaluator.php`
  * `app/Application/MarketData/Services/FinalizeDecisionService.php`
  * `app/Application/MarketData/Services/MarketDataPipelineService.php`
  * `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  * `tests/Unit/MarketData/CoverageGateEvaluatorTest.php`
  * `tests/Unit/MarketData/FinalizeDecisionServiceTest.php`
  * `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`
  * `tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php`

* Drift found:

  * active owner docs already reject `NOT_EVALUABLE` as a final gate state
  * live code/tests still emitted `NOT_EVALUABLE` on zero-universe / blocked finalize paths

* Resolution applied in this session:

  * zero-universe coverage evaluation now returns `BLOCKED`
  * finalize defaults now fall back to `BLOCKED` instead of `NOT_EVALUABLE`
  * operator/run reason-code resolution now maps blocked coverage state to `RUN_COVERAGE_NOT_EVALUABLE`
  * companion docs/test matrix synced to `BLOCKED` wording

* Available proof:

  * changed PHP files and changed PHPUnit files pass `php -l`
  * checkpoint-vs-repo drift revalidation completed for this batch

* Pending proof:

  * targeted PHPUnit execution in local environment with `vendor/`
  * full PHPUnit regression run in local environment with `vendor/`

### Family status note

* Exception-path operator recovery batch remains CLOSED and verified.
* Coverage final-state parity batch is still PARTIAL pending local PHPUnit proof.
* System-level daily live runtime validation remains outside this session scope.
