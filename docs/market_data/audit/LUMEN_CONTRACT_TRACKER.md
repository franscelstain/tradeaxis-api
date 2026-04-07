# LUMEN_CONTRACT_TRACKER.md

## External Source Operational Resilience

### Run Evidence Source Attempt Telemetry Export

* Status: DONE

* Scope:

  * export persisted attempt-level source telemetry from `eod_run_events` into run evidence artifacts
  * keep success-side and failure-side source-attempt extraction aligned
  * expose the new evidence artifact without inventing telemetry outside persisted event payloads

* Owner-doc anchor:

  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/market_data/ops/Run_Artifacts_Format_LOCKED.md`
  * `docs/market_data/ops/Audit_Evidence_Pack_Contract_LOCKED.md`
  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`

* Repo evidence:

  * `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php`
  * `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
  * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`

* Drift found:

  * attempt-level retry/backoff telemetry already existed in persisted run-event payloads
  * run evidence export only surfaced note-derived minimum summary fields
  * operator evidence for retry/backoff diagnosis therefore still depended on manual raw event inspection

* Resolution applied in this session:

  * added repository extraction for the latest persisted source-attempt telemetry payload per run
  * run evidence export now writes optional `source_attempt_telemetry.json` when attempt telemetry exists
  * `evidence_pack.json` now embeds the same `source_attempt_telemetry` structure
  * export summary now exposes `source_attempt_event_type` and `source_attempt_count`
  * owner/ops docs updated so the artifact is explicit and bounded by persisted run-event data

* Available proof:

  * changed PHP files pass `php -l`
  * checkpoint-vs-repo parity revalidation completed for this batch
  * changed docs are aligned with the new evidence-export surface
  * targeted local PHPUnit execution passed:

    * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → `1 test, 40 assertions`
    * `tests/Unit/MarketData/OpsCommandSurfaceTest.php` → `25 tests, 140 assertions`
  * full PHPUnit regression passed:

    * `163 tests, 1730 assertions`

* Pending proof:

  * none for this batch

### Operator Source Summary Enrichment

* Status: DONE

* Scope:

  * propagate provider/timeout/retry context from source-acquisition telemetry into persisted `eod_runs.notes`
  * keep success-side and failure-side note persistence aligned
  * surface the enriched source summary consistently in operator command output, backfill summary artifacts, and evidence export summaries

* Owner-doc anchor:

  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`

* Repo evidence:

  * `app/Application/MarketData/Services/MarketDataPipelineService.php`
  * `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  * `app/Application/MarketData/Services/MarketDataBackfillService.php`
  * `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
  * `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  * `tests/Unit/MarketData/OpsCommandSurfaceTest.php`
  * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`
  * `tests/Unit/MarketData/MarketDataPipelineServiceTest.php`
  * `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php`

* Drift found:

  * event payload telemetry already carried provider-level resilience context
  * persisted note recovery paths still exposed only the thinner attempt/final-status subset
  * operator/backfill/evidence note-based recovery therefore lagged behind the telemetry already available in the same run family

* Resolution applied in this session:

  * success-side note persistence now appends `source_provider`, `source_timeout_seconds`, and `source_retry_max`
  * failure-side note persistence now appends the same context alongside existing failure-side source fields
  * operator command/backfill/evidence readers now render summary strings that include provider/timeout/retry metadata when persisted in notes
  * companion docs synced so the richer note/operator-summary context is documented explicitly

* Available proof:

  * changed PHP files and changed PHPUnit files pass `php -l`
  * checkpoint-vs-repo parity revalidation completed for this batch
  * targeted local PHPUnit execution passed:

    * `tests/Unit/MarketData/MarketDataBackfillServiceTest.php` → `3 tests, 20 assertions`
    * `tests/Unit/MarketData/OpsCommandSurfaceTest.php` → `25 tests, 140 assertions`
    * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → `1 test, 30 assertions`
    * `tests/Unit/MarketData/MarketDataPipelineServiceTest.php` → `8 tests, 10 assertions`
    * `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → `45 tests, 1092 assertions`
  * full PHPUnit regression passed:

    * `163 tests, 1720 assertions`

* Pending proof:

  * none for this batch

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

* Status: DONE

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
  * local targeted PHPUnit execution passed:

    * `tests/Unit/MarketData/CoverageGateEvaluatorTest.php` → `4 tests, 38 assertions`
    * `tests/Unit/MarketData/FinalizeDecisionServiceTest.php` → `6 tests, 32 assertions`
    * `tests/Unit/MarketData/PublicationFinalizeOutcomeServiceTest.php` → `8 tests, 39 assertions`
    * `tests/Unit/MarketData/MarketDataPipelineIntegrationTest.php` → `45 tests, 1086 assertions`
  * full PHPUnit regression passed:

    * `163 tests, 1714 assertions`

* Pending proof:

  * none for this batch

### Family status note

* Exception-path operator recovery batch remains CLOSED and verified.
* Coverage final-state parity batch is CLOSED and verified.
* Operator source summary enrichment batch is DONE and verified.
* Run evidence source attempt telemetry export batch is now DONE and verified.
* External source operational resilience still remains PARTIAL at family/owner-doc level because the locked contract still lists broader remaining operational gaps outside this batch, including live-source runtime proof and future operator/dashboard hardening.
* System-level daily live runtime validation remains outside this session scope.
