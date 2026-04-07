# LUMEN_CONTRACT_TRACKER.md

## External Source Operational Resilience

### Backfill Source Context Recovery From Attempt Telemetry

* Status: PARTIAL

* Scope:

  * recover missing minimum operator-facing source context during backfill summary generation when `eod_runs.notes` are thinner than persisted attempt telemetry
  * keep recovery bounded to facts already stored in persisted run-event payloads
  * align in-memory backfill cases and `market_data_backfill_summary.json` with the same recovered minimum source context

* Owner-doc anchor:

  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`

* Repo evidence:

  * `app/Application/MarketData/Services/MarketDataBackfillService.php`
  * `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`

* Drift found:

  * run evidence export was already able to recover thin note context from persisted attempt telemetry
  * backfill summary still depended only on `eod_runs.notes`
  * when notes were thin, operator-facing backfill `source_summary` could remain weaker than the persisted attempt telemetry already available for the same run

* Resolution applied in this session:

  * backfill source-context building now optionally reads persisted attempt telemetry per run and merges only missing minimum fields
  * recovered fields stay bounded to persisted telemetry and do not invent new source facts
  * constructor compatibility preserved by keeping the new evidence dependency optional
  * PHPUnit coverage now includes the thin-notes recovery path
  * owner/ops docs now state that backfill summary may recover minimum source context from persisted attempt telemetry when notes are thin

* Available proof:

  * checkpoint-vs-repo parity revalidation completed for this batch
  * changed docs are aligned with the bounded recovery behavior
  * changed PHP files pass `php -l`
  * local PHPUnit proof is still pending because the uploaded ZIP omits `vendor/`

* Pending proof:

  * `vendor\bin\phpunit tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  * `vendor\bin\phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php`
  * `vendor\bin\phpunit`

### Run Evidence Source Context Recovery From Attempt Telemetry

* Status: DONE

* Scope:

  * recover missing minimum operator-facing source context during run evidence export when `eod_runs.notes` are thinner than persisted attempt telemetry
  * keep recovery bounded to facts already stored in persisted run-event payloads
  * align `run_summary.json`, `evidence_pack.json`, and CLI export summary with the same recovered minimum source context

* Owner-doc anchor:

  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/market_data/ops/Run_Artifacts_Format_LOCKED.md`
  * `docs/market_data/ops/Audit_Evidence_Pack_Contract_LOCKED.md`
  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`

* Repo evidence:

  * `app/Application/MarketData/Services/MarketDataEvidenceExportService.php`
  * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php`

* Drift found:

  * the prior batch already exported bounded attempt telemetry as its own artifact
  * operator-facing evidence summary still depended mostly on `eod_runs.notes`
  * when notes were thin, exported `source_summary` could remain weaker than the persisted attempt telemetry already available in the same run
  * first local PHPUnit proof also exposed that fallback-effective-date runs still expected current-publication resolution for `trade_date_effective`, but the implementation only looked up current publication for requested-date readable runs

* Resolution applied in this session:

  * run evidence export now merges missing minimum source-context fields from persisted attempt telemetry into exported run summary payloads
  * recovered fields stay bounded to persisted telemetry and do not invent new source facts
  * PHPUnit coverage now includes the thin-notes recovery path
  * owner/ops docs now state that run evidence export may recover minimum source context from persisted attempt telemetry when notes are thin
  * publication resolution inside run evidence export now falls back to the current publication for `trade_date_effective` whenever that fallback readable date exists, keeping failed/held evidence packs aligned with the locked readability contracts

* Available proof:

  * changed PHP files pass `php -l`
  * checkpoint-vs-repo parity revalidation completed for this batch
  * changed docs are aligned with the recovery behavior

* Regression feedback already received:

  * first local PHPUnit run failed on `MarketDataEvidenceExportServiceTest::test_export_run_evidence_recovers_source_summary_from_attempt_telemetry_when_notes_are_thin` because `findCurrentPublicationForTradeDate('2026-04-21')` was never called
  * root cause was publication resolution being gated too narrowly to requested-date readable runs

* Available proof after fix:

  * changed PHP files pass `php -l`
  * checkpoint-vs-repo parity revalidation completed for this batch
  * changed docs are aligned with the recovery behavior
  * local PHPUnit execution passed after the publication-resolution fix:

    * `tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` → `2 tests, 52 assertions`
    * `tests/Unit/MarketData/OpsCommandSurfaceTest.php` → `25 tests, 140 assertions`
    * `vendor\bin\phpunit` → `164 tests, 1742 assertions`

* Pending proof:

  * none for this batch

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

* Run evidence source context recovery batch is now PARTIAL pending local PHPUnit proof.
* Run evidence source attempt telemetry export batch remains DONE and verified.
* Exception-path operator recovery batch remains CLOSED and verified.
* Coverage final-state parity batch is CLOSED and verified.
* Operator source summary enrichment batch is DONE and verified.
* External source operational resilience still remains PARTIAL at family/owner-doc level because the locked contract still lists broader remaining operational gaps outside the closed batches, including live-source runtime proof and future operator/dashboard hardening.
* System-level daily live runtime validation remains outside this session scope.
