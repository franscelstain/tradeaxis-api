# LUMEN_CONTRACT_TRACKER

### Yahoo Single-Day Rate-Limit Final Root-Cause Decision on Backfill Path

* Status: DONE

* Scope:

  * finalize the root-cause decision for the accepted Yahoo single-day backfill failure using the already accepted runtime evidence
  * determine whether the repo still contains a bounded code gap that worsens the provider blocker
  * keep any patch minimal and limited to retry-safety / checkpoint truthfulness
  * do not redesign provider strategy, add new env/config keys, or change unrelated market-data paths

* Owner-doc anchor:

  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`
  * `docs/market_data/ops/Performance_SLO_and_Limits_LOCKED.md`

* Accepted runtime evidence:

  * single-day backfill failed on Yahoo
  * `attempt_count = 6`
  * `retry_max = 5`
  * all captured attempts returned HTTP `429`
  * failure occurred on the first proven request URL for `ADMR.JK`

* Repo evidence reviewed:

  * `app/Infrastructure/MarketData/Source/PublicApiEodBarsAdapter.php`
  * `app/Application/MarketData/Services/MarketDataPipelineService.php`
  * `tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php`
  * `tests/Unit/MarketData/MarketDataPipelineServiceTest.php`
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`

* Final root-cause decision:

  * accepted trigger run is sufficiently proven as an **external/provider Yahoo rate-limit blocker**
  * no duplicate-loop or accidental extra-request code defect is proven as the dominant cause for this run
  * the first captured Yahoo symbol request was already blocked and all retries on that same request also returned HTTP `429`

* Drift/gap found in active code:

  * active code let effective API retry budget follow raw runtime config directly
  * accepted runtime evidence proves that environment drift reached `retry_max = 5`
  * this exceeded the locked safer operator maximum of `3` and could worsen a provider-side 429 block even though it did not change the dominant root-cause classification

* Resolution applied in this session:

  * capped effective API retry budget at `3` inside `PublicApiEodBarsAdapter`
  * capped operator/event payload `retry_max` at the same effective value inside `MarketDataPipelineService`
  * added PHPUnit proof that a raw runtime config above `3` is reduced to the locked safe maximum and surfaced consistently
  * updated locked docs so operator diagnosis reflects the capped effective retry value

* Available proof:

  * repo inspection proves Yahoo path is still per-symbol and retry is still per request
  * repo inspection plus accepted runtime evidence proves the accepted run failed on the first captured symbol request, so external/provider blocker is sufficient as the final root-cause decision
  * bounded code patch closes the retry-budget drift that could worsen the blocker

* Pending proof:

  * local/runtime validation still required from the user's environment because no PHPUnit/artisan/API execution was performed in this session without `vendor/`
  * provider strategy readiness is still partial even after the retry cap, because Yahoo can still reject the first symbol request

* Final state:

  * DONE for this bounded root-cause decision batch
  * external/provider blocker classification is now final for the accepted trigger run
  * bounded retry-safety code gap is closed
  * program-level readiness remains PARTIAL


---

# LUMEN_CONTRACT_TRACKER

### Yahoo Single-Day Rate-Limit Root-Cause Audit on Backfill Path

* Status: PARTIAL

* Scope:

  * audit the active backfill path only for the accepted Yahoo single-day rate-limit trigger
  * prove whether the main cause is adapter fan-out, layered retry, duplicate request behavior, or an external provider/runtime blocker
  * avoid unrelated changes to finalize/publication/watchlist/other domains

* Owner-doc anchor:

  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`

* Runtime/manual evidence in scope:

  * accepted trigger: `php artisan market-data:backfill 2026-03-02 2026-03-02 --output_dir=storage/app/market-data-yahoo-rate-limit-audit`
  * `market_data_backfill_summary.json` proves for requested date `2026-03-02`: `run_id=68`, `terminal_status=FAILED`, `publishability_state=NOT_READABLE`, `source_attempt_count=6`, `provider=yahoo_finance`, `retry_max=5`, final reason `RUN_SOURCE_RATE_LIMIT`
  * `source_attempt_telemetry.json` proves six consecutive attempts with HTTP `429`, each tied to the same failed acquisition context
  * `eod_run_events.csv` proves the captured failing Yahoo URL was `https://query1.finance.yahoo.com/v8/finance/chart/ADMR.JK?...`

* What is proven from active repo audit + runtime evidence:

  * `EodBarsIngestService` resolves the full ticker universe for the requested date before API fetch
  * `PublicApiEodBarsAdapter::fetchYahooFinanceBars()` performs one Yahoo chart request per ticker symbol in that universe
  * `PublicApiEodBarsAdapter::requestWithRetry()` applies retry/backoff per symbol request for `RUN_SOURCE_RATE_LIMIT` and `RUN_SOURCE_TIMEOUT`
  * for the accepted failing run `68`, the supplied runtime evidence proves failure on the first captured symbol request (`ADMR.JK`) with six consecutive HTTP `429` responses
  * therefore the accepted trigger run is **not** currently proven to have failed because of multi-symbol fan-out burst across the whole universe; it is proven to have failed because Yahoo rejected the first captured symbol request and all retries on that same request

* What is not yet proven:

  * whether other runs under different provider conditions fail only after several ticker requests
  * whether Yahoo rejected this run because of IP reputation, shared network budget, temporary provider window limits, or another provider-side policy not visible from the repo
  * whether any code change can fully remove the blocker without changing provider/source strategy

* Repo/test/doc updates made in this batch:

  * updated audit checkpoints so the root-cause statement matches the new runtime evidence exactly
  * no production code changes made in this evidence-review turn

* Root-cause hypothesis:

  * hypothesis: for the accepted trigger run, the dominant root cause is an external/provider-side Yahoo runtime rate-limit blocker that rejects the first captured symbol request (`ADMR.JK`) and then defeats all configured retries
  * status: STRONG
  * why strong:
    * runtime evidence now identifies the failing symbol URL and shows six straight HTTP `429` results on that same request
    * there is still no evidence of a duplicate-loop bug in the repo
  * what weakens the earlier fan-out-centric framing:
    * the supplied run evidence does not show a second symbol request before failure
  * why not yet final:
    * additional runs/evidence are still needed to distinguish first-request blocking from broader burst-window throttling patterns across time

* Next evidence required to close or strengthen:

  * rerun single-day backfill later/from a different provider window and compare whether the first symbol still gets immediate `429`
  * if possible, run the same job with a smaller isolated ticker universe or alternative source path for comparison
  * continue exporting:
    * CLI output
    * `market_data_backfill_summary.json`
    * `source_attempt_telemetry.json`
    * DB export of `eod_run_events` filtered to the failing `run_id`

* Final state:

  * PARTIAL
  * accepted trigger run root cause is strongly narrowed to external/provider Yahoo blocking on the first captured symbol request plus exhausted retry
  * external/runtime Yahoo blocker remains open


---

# LUMEN_CONTRACT_TRACKER

### Backfill Failure Source Attempt Telemetry Artifact Export

* Status: DONE

* Scope:

  * close the export/operator-proof gap where persisted source-attempt telemetry already exists in `eod_run_events` for failed backfill runs but no `source_attempt_telemetry.json` artifact is materialized
  * keep the batch limited to backfill artifact export and operator-proof wiring only
  * do not change Yahoo acquisition strategy, retry/backoff semantics, finalize, publication, or source-provider behavior

* Owner-doc anchor:

  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`

* Runtime/manual evidence that triggered the batch:

  * `php artisan market-data:backfill 2026-03-02 2026-03-02` failed with `RUN_SOURCE_RATE_LIMIT` while the backfill summary still exposed only bounded retry summary fields
  * DB exports for `run_id=64` showed `eod_run_events` already persisted detailed failure-side source-attempt telemetry including repeated HTTP 429 responses from Yahoo
  * the backfill output directory did not contain `source_attempt_telemetry.json`, so operator forensics still depended on manual DB inspection even though the telemetry was already persisted

* Repo evidence:

  * `app/Application/MarketData/Services/MarketDataBackfillService.php`
  * `app/Console/Commands/MarketData/BackfillMarketDataCommand.php`
  * `tests/Unit/MarketData/MarketDataBackfillServiceTest.php`
  * `tests/Unit/MarketData/OpsCommandSurfaceTest.php`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`

* Drift found:

  * persisted attempt-level telemetry already existed for failed backfill runs, but the backfill path only surfaced compressed retry-proof fields in the summary artifact and CLI output
  * unlike daily and run-evidence export surfaces, backfill did not materialize a bounded `source_attempt_telemetry.json` companion from that persisted telemetry
  * this left failure-side retry/backoff diagnosis dependent on manual `eod_run_events` inspection even though the data was already available inside the codebase

* Resolution applied in this session:

  * `MarketDataBackfillService` now collects persisted source-attempt telemetry per requested date and writes `source_attempt_telemetry.json` when one or more backfill cases have attempt rows
  * the backfill telemetry artifact is bounded to the existing backfill range/source-mode context plus per-date telemetry cases recovered from persisted `eod_run_events`
  * `BackfillMarketDataCommand` now prints `source_attempt_telemetry_artifact=...` when the bounded telemetry artifact is present
  * PHPUnit coverage now proves the failed backfill path writes the telemetry artifact and the backfill CLI surfaces its normalized artifact path
  * the locked ops runbook now states this artifact requirement explicitly for deterministic backfill output paths

* Available proof:

  * runtime/manual evidence already proves the trigger condition for this batch and distinguishes it from the separate Yahoo source/runtime blocker
  * ZIP-level syntax validation passed:
    * `php -l app/Application/MarketData/Services/MarketDataBackfillService.php` → PASS
    * `php -l app/Console/Commands/MarketData/BackfillMarketDataCommand.php` → PASS
    * `php -l tests/Unit/MarketData/MarketDataBackfillServiceTest.php` → PASS
    * `php -l tests/Unit/MarketData/OpsCommandSurfaceTest.php` → PASS

* Latest local validation feedback:

  * syntax validation passed for all touched files
  * `vendor\bin\phpunit tests/Unit/MarketData/MarketDataBackfillServiceTest.php` passed with `OK (6 tests, 44 assertions)`
  * `vendor\bin\phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` passed with `OK (35 tests, 226 assertions)`
  * `vendor\bin\phpunit` passed with `OK (177 tests, 1858 assertions)`
  * manual backfill validation proved the failed Yahoo rate-limit path now materializes `source_attempt_telemetry.json` and prints `source_attempt_telemetry_artifact=storage/app/market-data-test/source_attempt_telemetry.json`

* Pending proof:

  * none for this bounded backfill telemetry artifact batch

# LUMEN_CONTRACT_TRACKER

### Active Checkpoint Consolidation for Daily Telemetry Runtime Artifact

* Status: DONE

* Scope:

  * close active checkpoint drift after the daily telemetry runtime-artifact follow-up fix was already implemented and locally validated
  * keep the batch limited to audit/checkpoint truthfulness
  * do not change source acquisition, daily pipeline, finalize, publication, correction, backfill, or evidence-export semantics

* Owner-doc anchor:

  * `docs/README.md`
  * `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
  * `docs/market_data/book/EOD_SOURCE_OPERATIONAL_RESILIENCE_CONTRACT_LOCKED.md`
  * `docs/market_data/ops/Commands_and_Runbook_LOCKED.md`

* Repo evidence:

  * `docs/market_data/audit/LUMEN_IMPLEMENTATION_STATUS.md`
  * `docs/market_data/audit/LUMEN_CONTRACT_TRACKER.md`
  * `app/Console/Commands/MarketData/AbstractMarketDataCommand.php`
  * `app/Console/Commands/MarketData/DailyPipelineCommand.php`
  * `tests/Unit/MarketData/OpsCommandSurfaceTest.php`

* Drift found:

  * the active tracker still carried a stale `PARTIAL` entry for `Daily Source Attempt Telemetry Runtime Artifact` even though the next recorded follow-up fix had already closed the concrete implementation gap and local PHPUnit proof had already been accepted as official evidence
  * the active checkpoint files had also become noisy by stacking many older session blocks that duplicated history already preserved under `docs/market_data/audit/histories/**`
  * this created audit ambiguity about what is currently open versus what is only historical context

* Resolution applied in this session:

  * active checkpoint files now state one current authoritative status for the daily telemetry runtime-artifact lane
  * the stale `PARTIAL` state is removed from the active checkpoint because the bounded code/test/doc contract is already closed
  * historical detail remains delegated to `docs/market_data/audit/histories/**`; the active checkpoint is now the current truth surface again

* Available proof:

  * repo revalidation confirms the daily telemetry runtime-artifact implementation is present in code, docs, and ops-surface tests
  * official local validation evidence already recorded for the current source of truth:
    * `php -l app/Console/Commands/MarketData/AbstractMarketDataCommand.php` → PASS
    * `vendor\bin\phpunit tests/Unit/MarketData/OpsCommandSurfaceTest.php` → `OK (35 tests, 225 assertions)`
    * `vendor\bin\phpunit` → `OK (176 tests, 1848 assertions)`
  * no config/env drift was introduced

* Pending proof:

  * none for this bounded batch

### Program-Level Readiness Note

* Status: PARTIAL

* Scope note:

  * this is not a new bounded implementation batch; it is the remaining program-level state from `docs/system_audit/CODEBASE_BUILD_AND_AUDIT_GUIDE.md`
  * tracked implementation batches in the active checkpoint are currently closed, but repo-wide readiness remains partial because live daily operational proof is still not fully established

* Open concern still declared by owner docs:

  * `Operational readiness: PARTIAL`
  * not yet fully production-ready for daily live run
  * next session must select one concrete bounded gap from owner-doc-backed operational-readiness work or fresh runtime/manual evidence, not reopen an already-closed telemetry contract
