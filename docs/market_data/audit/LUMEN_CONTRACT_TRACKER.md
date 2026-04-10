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

  * accepted trigger: `php artisan market-data:backfill 2026-03-02 2026-03-02` failed with `RUN_SOURCE_RATE_LIMIT`
  * accepted DB evidence from prior session: failed Yahoo run already persisted repeated HTTP 429 attempt telemetry in `eod_run_events`

* What is proven from active repo audit:

  * `EodBarsIngestService` resolves the full ticker universe for the requested date before API fetch
  * `PublicApiEodBarsAdapter::fetchYahooFinanceBars()` performs one Yahoo chart request per ticker symbol in that universe
  * `PublicApiEodBarsAdapter::requestWithRetry()` applies retry/backoff per symbol request for `RUN_SOURCE_RATE_LIMIT` and `RUN_SOURCE_TIMEOUT`
  * therefore one requested date on Yahoo currently scales as:
    * first-attempt path: `resolved_ticker_count`
    * retry-exhausted path upper bound: `resolved_ticker_count * (1 + retry_max)`
  * no same-symbol duplicate first-attempt loop is proven in the active adapter; the observed amplification comes from intentional per-symbol fan-out plus retry layering

* What is not yet proven:

  * exact symbol-by-symbol request counts for the accepted failing runtime run inside this session
  * whether Yahoo rate-limited because of pure request volume, provider-side burst heuristics, IP reputation, or time-window behavior beyond what the persisted 429 trail already shows

* Repo/test/doc updates made in this batch:

  * added unit proof that Yahoo path fans out one request per ticker without same-symbol duplicate first-attempt requests
  * added unit proof that retry count scales per ticker for Yahoo path
  * updated locked contracts/runbook to state Yahoo request cardinality explicitly so future audits do not treat single-day backfill as one outbound request

* Root-cause hypothesis:

  * hypothesis: the dominant root cause is Yahoo provider/runtime rate-limit pressure caused by per-symbol request fan-out combined with per-symbol retry, not a hidden duplicate loop in backfill orchestration
  * status: STRONG
  * why strong:
    * code path directly proves the request cardinality shape
    * accepted runtime evidence already proves repeated Yahoo 429 responses on the failing run
  * why not yet final:
    * this session still lacks fresh raw runtime export that totals attempt rows by symbol/date for the exact trigger run

* Next evidence required to close or strengthen:

  * rerun single-day backfill locally and export/run-save:
    * CLI output
    * `market_data_backfill_summary.json`
    * `source_attempt_telemetry.json`
    * DB export of `eod_run_events` filtered to the failing `run_id`
  * compute:
    * distinct symbol count attempted
    * total attempt rows
    * per-symbol attempt count
    * final 429 exhaustion count

* Final state:

  * PARTIAL
  * root cause is strongly narrowed to provider-facing request cardinality + retry scaling
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
