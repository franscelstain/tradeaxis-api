# DB Fields & Metadata (Coverage Gate)

## Purpose
Define the minimum DB/runtime metadata that must be persisted or emitted so the locked coverage-gate contract is audit-visible and implementable.

This document does not own the coverage formula.
Formula and outcome semantics are owned by `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`.

## Required eod_runs fields (LOCKED minimum)
The `eod_runs` record for a requested trade date must make these values audit-visible:

- `coverage_universe_count` INT NULL  
  Official denominator resolved from the coverage universe as-of `trade_date_requested`.
- `coverage_available_count` INT NULL  
  Canonical valid-bar numerator used for coverage evaluation.
- `coverage_missing_count` INT NULL  
  Derived missing count for the resolved universe. Expected formula: `coverage_universe_count - coverage_available_count`, never below zero.
- `coverage_ratio` DECIMAL(8,6) NULL  
  Evaluated ratio before any UI-only rounding.
- `coverage_min_threshold` DECIMAL(8,6) NULL  
  Threshold actually used by the run.
- `coverage_gate_state` ENUM/VARCHAR NULL  
  Final allowed values: `PASS`, `FAIL`, `NOT_EVALUABLE`, `BLOCKED` (legacy compatibility only).
- `coverage_threshold_mode` VARCHAR(32) NULL  
  Initial locked value: `MIN_RATIO`.
- `coverage_universe_basis` VARCHAR(64) NULL  
  Initial locked value: `ACTIVE_LISTED_EQUITY_AS_OF_DATE`.
- `coverage_contract_version` VARCHAR(64) NULL  
  Contract/config identity for audit and replay clarity.
- `coverage_missing_sample_json` JSON/TEXT NULL  
  Optional but recommended sample of missing ticker codes for operator evidence. Sampling must not replace the official counts.

## Why these fields are required
They close the ambiguity that previously allowed coverage to be discussed without proving:
- what denominator was used
- what numerator was used
- what threshold was used
- why the gate ended in `PASS`, `FAIL`, or `NOT_EVALUABLE`

## Required config metadata linkage
The persisted coverage values must stay explainable from runtime config. At minimum, config must expose:
- `MARKET_DATA_COVERAGE_MIN`
- `MARKET_DATA_COVERAGE_THRESHOLD_MODE`
- `MARKET_DATA_COVERAGE_UNIVERSE_BASIS`
- `MARKET_DATA_COVERAGE_CONTRACT_VERSION`

## Replay / evidence mirror
If replay or evidence tables mirror run metrics, they should also mirror the same coverage metadata fields so proof artifacts can explain the original gate decision without re-deriving it.

## Anti-ambiguity notes
- `coverage_ratio` alone is not sufficient.
- `coverage_gate_state` alone is not sufficient.
- provider row count must never substitute for `coverage_universe_count`.
- eligibility row count must never substitute for `coverage_available_count`.


## Required eod_runs source traceability fields (session hardening minimum)
The `eod_runs` record must also expose first-class traceability fields so source context is queryable without parsing logs only:

- `source` VARCHAR/ENUM NOT NULL  
  Logical source mode used by the run, for example `api` or `manual_file`.
- `source_name` VARCHAR(64) NULL  
  Logical source identity such as `API_FREE` or `LOCAL_FILE`.
- `source_provider` VARCHAR(64) NULL  
  Upstream/provider identity when relevant.
- `source_input_file` VARCHAR(255) NULL  
  Manual input path/reference when manual source mode is used.
- `source_timeout_seconds` INT NULL
- `source_retry_max` INT NULL
- `source_attempt_count` INT NULL
- `source_success_after_retry` BOOLEAN/TINYINT NULL
- `source_retry_exhausted` BOOLEAN/TINYINT NULL
- `source_final_http_status` INT NULL
- `source_final_reason_code` VARCHAR(64) NULL

These fields do not replace run events or evidence artifacts; they make minimum source provenance audit-visible directly from DB state.

## Required eod_runs linkage/publishability fields (session hardening minimum)
To avoid implicit-only linkage, `eod_runs` should also persist:

- `publication_id` BIGINT NULL  
  Publication row produced/resolved by the run.
- `publication_version` INT NULL
- `correction_id` BIGINT NULL  
  Official correction request reference when the run is part of correction flow.
- `final_reason_code` VARCHAR(64) NULL  
  Final consumer/audit-visible reason code aligned with the terminal outcome.

This keeps run/publication linkage and publishability reasoning queryable even when operators are not reading event payload JSON.

---

## 2026-04-26 — DB Schema Sync Metadata Addendum

Status: SYNCED WITH `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`

Newly documented / synchronized table metadata:

### `tickers`

Runtime owner: ticker universe / coverage universe lookup.  
Repository: `TickerMasterRepository`.

Required fields:
- `ticker_id`
- `ticker_code`
- `company_name`
- `company_logo`
- `listed_date`
- `delisted_date`
- `board_code`
- `exchange_code`
- `is_active`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `ticker_id`
- unique key `ticker_code`

### `market_calendar`

Runtime owner: trading date resolution.  
Repository: `MarketCalendarRepository`.

Required fields:
- `cal_date`
- `is_trading_day`
- `holiday_name`
- `session_open_time`
- `session_close_time`
- `breaks_json`
- `source`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `cal_date`
- index `market_calendar_trading_idx (is_trading_day, cal_date)`

### `md_session_snapshots`

Runtime owner: intraday/session snapshot persistence.  
Repository: `SessionSnapshotRepository`.

Required fields:
- `snapshot_id`
- `trade_date`
- `snapshot_slot`
- `ticker_id`
- `captured_at`
- `last_price`
- `prev_close`
- `chg_pct`
- `volume`
- `day_high`
- `day_low`
- `source`
- `run_id`
- `reason_code`
- `error_note`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `snapshot_id`
- unique key `(trade_date, snapshot_slot, ticker_id)`
- index `(trade_date, snapshot_slot)`
- index `(captured_at)`

### `md_replay_daily_metrics`

Replay expected-context fields are part of the DB schema contract and must stay synchronized with `ReplayResultRepository` and `ReplayVerificationService`.
