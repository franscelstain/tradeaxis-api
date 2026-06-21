# Market Data Database Dictionary

Status: `CANONICAL_REFERENCE_FOR_DATABASE_CONNECTED_WORK`

Last updated: 2026-06-22

This document is the operational data dictionary for database-connected Market Data, Watchlist, backtest, audit, and future feature work. It complements the physical DDL in `Database_Schema_MariaDB.sql`; it does not replace migrations or locked schema contracts.

## Database-Connected Work Rule

Any prompt, implementation, audit, bug fix, backtest, diagnostic, or production feature that reads/writes database-backed data must read this dictionary first, plus the module-specific owner docs. This rule applies even when the work seems small.

Minimum pre-work checklist:

1. Identify all tables touched.
2. Confirm date key and identifier key from this dictionary.
3. Confirm whether each field is pre-trade safe, evaluation-only, or forbidden for selection.
4. Confirm as-of lookup rule; never use unbounded `MAX(trade_date)` or future lookup.
5. Confirm whether source belongs to equity current tables, benchmark tables, history tables, audit/replay tables, or Watchlist-only tables.
6. If a needed field/table is not in the dictionary, stop and update dictionary/governance before coding or claiming evidence.

No agent should assume column names from memory. The C57 repair proved why: benchmark ROC20 is `market_benchmark_indicators.roc_20`, while equity ROC20 is `eod_indicators.roc20`.

## Canonical Field Mapping and Alias Rules

These mappings are mandatory for database-connected work. Do not infer alternate names without checking the dictionary and schema.

| Domain field needed by consumer/backtest | Canonical DB source | Canonical DB column | Notes |
|---|---|---|---|
| `market_index_roc20` | `market_benchmark_indicators` | `roc_20` where `benchmark_code='IHSG'` | This is not `roc20`; C57 fixed this mapping. |
| `market_index_ma20_slope_pct` | `market_benchmark_indicators` | `ma20_slope_pct` where `benchmark_code='IHSG'` | Use `market_benchmark_bars` fallback only when indicator is null and sufficient bounded history exists. |
| IHSG market-index identity | `market_benchmarks` | `benchmark_code='IHSG'`, provider symbol `^JKSE` | IHSG is outside the equity ticker universe. Do not search it as normal `ticker_id` unless a future contract says so. |
| Sector index ROC20 | `market_benchmark_indicators` | `roc_20` for sector benchmark code | Join through `market_data_sectors.sector_index_code` and ticker sector membership as-of date. |
| Equity ROC20 | `eod_indicators` | `roc20` | Equity indicator spelling differs from benchmark `roc_20`. |
| Equity MA20 slope | `eod_indicators` | `ma20_slope_pct` | Use only as-of signal/trade date. |
| Market calendar date | `market_calendar` | `cal_date` | Not `trade_date`. Use bounded previous trading day lookup, never unbounded `MAX(trade_date)`. |
| Equity bar close | `eod_bars` | `adj_close` preferred where adjusted logic applies; otherwise `close` | Future path use is evaluation-only. |
| Benchmark bar close | `market_benchmark_bars` | `adjusted_close` preferred; `close_price` fallback | Used to compute benchmark indicators when source indicator is null. |

## Table Inventory Summary

| Table | Purpose | Primary/identity key | Identifier key | Date/as-of key | Selection safety |
|---|---|---|---|---|---|
| `tickers` | Master saham/listing; maps ticker_code to ticker_id and active/listed/delisted state. | `ticker_id` | `ticker_code` | `listed_date/delisted_date` | Pre-trade safe as master data when resolved as-of date. |
| `market_calendar` | Canonical exchange calendar; determines trading days, sessions, holidays, and previous-trading-day lookup. Date key is cal_date, not trade_date. | `cal_date` | `cal_date` | `cal_date` | Pre-trade safe; required for previous trading day lookup. Do not use MAX(trade_date). |
| `market_data_sectors` | IDX-IC sector taxonomy and sector-index mapping such as IDXTECHNO/IDXFINANCE. | `sector_code` | `sector_code` | `effective_from/effective_to` | Pre-trade safe when membership/sector state is resolved as-of date. |
| `ticker_sector_memberships` | Historical ticker-to-sector membership as of trade date. Required for sector reconstruction. | `membership_id` | `ticker_id + sector_code` | `effective_from/effective_to` | Pre-trade safe if date-bounded; no fabricated sector fallback. |
| `market_data_corporate_actions` | Source-backed corporate action events by ticker/date/type. | `corporate_action_id` | `ticker_id/ticker_code` | `action_date` | Pre-trade safe only if source row is known as-of date; not future action leakage. |
| `market_data_trading_status_events` | Source-backed trading status events such as suspension, UMA, special monitoring, active/exit states. | `trading_status_id` | `ticker_id/ticker_code` | `trade_date` | Pre-trade safe when status is known/carry-forward as-of date. |
| `market_benchmarks` | Benchmark/index master. IHSG maps to Yahoo ^JKSE; sector benchmarks map to IDX sector provider symbols. | `benchmark_id` | `benchmark_code` | `n/a` | Pre-trade safe master mapping; IHSG is market index identifier. |
| `market_benchmark_bars` | Benchmark/index OHLCV rows outside equity ticker universe. Correct source for IHSG/sector index bars. | `benchmark_bar_id` | `benchmark_code` | `trade_date` | Pre-trade safe for benchmark OHLC if trade_date <= signal/asof date. |
| `market_benchmark_indicators` | Derived benchmark/index indicators. Correct source for IHSG roc_20 and ma20_slope_pct used by Watchlist market-index regime fields. | `benchmark_indicator_id` | `benchmark_code` | `trade_date` | Pre-trade safe for benchmark indicators if trade_date <= signal/asof date. |
| `eod_reason_codes` | Reason-code registry for EOD lifecycle, bars, indicators, eligibility and audit semantics. | `code` | `code` | `n/a` | Metadata only. |
| `eod_bars` | Current readable canonical equity EOD OHLCV rows, one row per trade_date/ticker_id. | `(trade_date,ticker_id)` | `ticker_id` | `trade_date` | Pre-trade safe only for asof EOD/date <= signal date. Future path bars are evaluation only. |
| `eod_invalid_bars` | Rejected/invalid source-row evidence and reason-code payload. | `invalid_bar_id` | `ticker_id` | `trade_date` | Audit/evidence only; not a selection source. |
| `eod_indicators` | Current readable derived equity indicators and event-risk/sector-rotation fields, one row per trade_date/ticker_id. | `(trade_date,ticker_id)` | `ticker_id` | `trade_date` | Primary pre-trade indicator source if trade_date <= signal/asof date. |
| `eod_eligibility` | Current readable eligibility state and block reason per trade_date/ticker_id. | `(trade_date,ticker_id)` | `ticker_id` | `trade_date` | Primary pre-trade eligibility source if trade_date <= signal/asof date. |
| `eod_runs` | Lifecycle/run audit header for source acquisition, backfill, recompute, promote and coverage-gate operations. | `run_id` | `run_id` | `trade_date_requested/effective` | Audit/run metadata; not trading selection feature unless explicitly whitelisted. |
| `eod_run_events` | Append-only run event log with stage/severity/reason payloads. | `event_id` | `run_id` | `event_time/trade_date_requested` | Audit/log only. |
| `eod_publications` | Publication/seal records for current and superseded readable datasets. | `publication_id` | `run_id` | `trade_date` | Publication state; used to ensure readable state, not selection alpha. |
| `eod_current_publication_pointer` | Canonical pointer from trade_date to the current readable publication. | `trade_date` | `publication_id` | `trade_date` | Publication pointer; use to resolve current readable state. |
| `eod_dataset_corrections` | Correction workflow state and publication lineage for reprocessing/corrections. | `correction_id` | `prior_run/new_run` | `trade_date` | Correction audit only. |
| `eod_bars_history` | Publication-bound immutable history for equity EOD bars. | `(publication_id,trade_date,ticker_id)` | `ticker_id` | `trade_date` | Audit/history; current consumer read should prefer current published rows/pointer unless replaying a locked publication. |
| `eod_indicators_history` | Publication-bound immutable history for equity indicators. | `(publication_id,trade_date,ticker_id)` | `ticker_id` | `trade_date` | Audit/history; safe only when locked publication is explicit. |
| `eod_eligibility_history` | Publication-bound immutable history for eligibility rows. | `(publication_id,trade_date,ticker_id)` | `ticker_id` | `trade_date` | Audit/history; safe only when locked publication is explicit. |
| `md_session_snapshots` | Intraday/session snapshot storage for manual/live snapshot evidence. | `snapshot_id` | `ticker_id` | `trade_date/captured_at` | Confirm/intraday snapshot evidence; not EOD backtest selection unless explicitly modeled. |
| `md_replay_daily_metrics` | Daily replay/evidence metrics for determinism, coverage and publishability verification. | `replay_id + trade_date` | `replay suite/case` | `trade_date` | Evaluation/audit only; not pre-trade selection input. |
| `md_replay_reason_code_counts` | Aggregated reason-code counts by replay/date. | `(replay_id,trade_date,reason_code)` | `reason_code` | `trade_date` | Evaluation/audit only. |

## Detailed Table Dictionary

### `tickers`

- Purpose: Master saham/listing; maps ticker_code to ticker_id and active/listed/delisted state.
- Primary/identity key: `ticker_id`
- Identifier key: `ticker_code`
- Date/as-of key: `listed_date/delisted_date`
- Selection safety: Pre-trade safe as master data when resolved as-of date.

| Column | Type / contract | Field role |
|---|---|---|
| `ticker_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `ticker_code` | `VARCHAR(10) NOT NULL` | identifier / relationship key |
| `company_name` | `VARCHAR(255) NOT NULL` | data field |
| `company_logo` | `VARCHAR(255) NULL` | data field |
| `listed_date` | `DATE NULL` | date/time / as-of or audit metadata |
| `delisted_date` | `DATE NULL` | date/time / as-of or audit metadata |
| `board_code` | `VARCHAR(10) NULL` | data field |
| `exchange_code` | `VARCHAR(10) NULL` | data field |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `market_calendar`

- Purpose: Canonical exchange calendar; determines trading days, sessions, holidays, and previous-trading-day lookup. Date key is cal_date, not trade_date.
- Primary/identity key: `cal_date`
- Identifier key: `cal_date`
- Date/as-of key: `cal_date`
- Selection safety: Pre-trade safe; required for previous trading day lookup. Do not use MAX(trade_date).

| Column | Type / contract | Field role |
|---|---|---|
| `cal_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `is_trading_day` | `TINYINT(1) NOT NULL DEFAULT 1` | data field |
| `holiday_name` | `VARCHAR(120) NULL` | data field |
| `session_open_time` | `VARCHAR(5) NULL` | data field |
| `session_close_time` | `VARCHAR(5) NULL` | data field |
| `breaks_json` | `TEXT NULL` | data field |
| `source` | `VARCHAR(120) NULL` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `market_data_sectors`

- Purpose: IDX-IC sector taxonomy and sector-index mapping such as IDXTECHNO/IDXFINANCE.
- Primary/identity key: `sector_code`
- Identifier key: `sector_code`
- Date/as-of key: `effective_from/effective_to`
- Selection safety: Pre-trade safe when membership/sector state is resolved as-of date.

| Column | Type / contract | Field role |
|---|---|---|
| `sector_code` | `VARCHAR(8) NOT NULL` | identifier / relationship key |
| `sector_name` | `VARCHAR(120) NOT NULL` | data field |
| `sector_index_code` | `VARCHAR(32) NULL` | data field |
| `classification_system` | `VARCHAR(32) NOT NULL DEFAULT 'IDX-IC'` | data field |
| `effective_from` | `DATE NOT NULL DEFAULT '2021-01-25'` | date/time / as-of or audit metadata |
| `effective_to` | `DATE NULL` | date/time / as-of or audit metadata |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | data field |
| `source_name` | `VARCHAR(64) NOT NULL DEFAULT 'idx'` | data field |
| `source_ref` | `VARCHAR(255) NULL` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `ticker_sector_memberships`

- Purpose: Historical ticker-to-sector membership as of trade date. Required for sector reconstruction.
- Primary/identity key: `membership_id`
- Identifier key: `ticker_id + sector_code`
- Date/as-of key: `effective_from/effective_to`
- Selection safety: Pre-trade safe if date-bounded; no fabricated sector fallback.

| Column | Type / contract | Field role |
|---|---|---|
| `membership_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `sector_code` | `VARCHAR(8) NOT NULL` | identifier / relationship key |
| `classification_system` | `VARCHAR(32) NOT NULL DEFAULT 'IDX-IC'` | data field |
| `effective_from` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `effective_to` | `DATE NULL` | date/time / as-of or audit metadata |
| `source_name` | `VARCHAR(64) NULL` | data field |
| `source_ref` | `VARCHAR(255) NULL` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `market_data_corporate_actions`

- Purpose: Source-backed corporate action events by ticker/date/type.
- Primary/identity key: `corporate_action_id`
- Identifier key: `ticker_id/ticker_code`
- Date/as-of key: `action_date`
- Selection safety: Pre-trade safe only if source row is known as-of date; not future action leakage.

| Column | Type / contract | Field role |
|---|---|---|
| `corporate_action_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `ticker_code` | `VARCHAR(16) NOT NULL` | identifier / relationship key |
| `action_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `action_type` | `VARCHAR(64) NOT NULL` | data field |
| `source_name` | `VARCHAR(64) NOT NULL DEFAULT 'manual_corporate_action_csv'` | data field |
| `source_ref` | `VARCHAR(255) NULL` | data field |
| `notes` | `VARCHAR(255) NULL` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `market_data_trading_status_events`

- Purpose: Source-backed trading status events such as suspension, UMA, special monitoring, active/exit states.
- Primary/identity key: `trading_status_id`
- Identifier key: `ticker_id/ticker_code`
- Date/as-of key: `trade_date`
- Selection safety: Pre-trade safe when status is known/carry-forward as-of date.

| Column | Type / contract | Field role |
|---|---|---|
| `trading_status_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `ticker_code` | `VARCHAR(16) NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `status_code` | `VARCHAR(64) NOT NULL` | data field |
| `is_suspended` | `TINYINT(1) NULL` | data field |
| `is_uma` | `TINYINT(1) NULL` | data field |
| `source_name` | `VARCHAR(64) NOT NULL DEFAULT 'manual_trading_status_csv'` | data field |
| `source_ref` | `VARCHAR(255) NULL` | data field |
| `notes` | `VARCHAR(255) NULL` | data field |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | date/time / as-of or audit metadata |

### `market_benchmarks`

- Purpose: Benchmark/index master. IHSG maps to Yahoo ^JKSE; sector benchmarks map to IDX sector provider symbols.
- Primary/identity key: `benchmark_id`
- Identifier key: `benchmark_code`
- Date/as-of key: `n/a`
- Selection safety: Pre-trade safe master mapping; IHSG is market index identifier.

| Column | Type / contract | Field role |
|---|---|---|
| `benchmark_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `benchmark_code` | `VARCHAR(32) NOT NULL` | identifier / relationship key |
| `benchmark_name` | `VARCHAR(120) NOT NULL` | data field |
| `provider` | `VARCHAR(64) NOT NULL` | data field |
| `provider_symbol` | `VARCHAR(64) NOT NULL` | data field |
| `instrument_type` | `VARCHAR(32) NOT NULL` | data field |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NULL` | date/time / as-of or audit metadata |

### `market_benchmark_bars`

- Purpose: Benchmark/index OHLCV rows outside equity ticker universe. Correct source for IHSG/sector index bars.
- Primary/identity key: `benchmark_bar_id`
- Identifier key: `benchmark_code`
- Date/as-of key: `trade_date`
- Selection safety: Pre-trade safe for benchmark OHLC if trade_date <= signal/asof date.

| Column | Type / contract | Field role |
|---|---|---|
| `benchmark_bar_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `benchmark_code` | `VARCHAR(32) NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `open_price` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `high_price` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `low_price` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `close_price` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `adjusted_close` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `volume` | `BIGINT NULL` | raw price/volume market data |
| `provider` | `VARCHAR(64) NOT NULL` | data field |
| `provider_symbol` | `VARCHAR(64) NOT NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NULL` | date/time / as-of or audit metadata |

### `market_benchmark_indicators`

- Purpose: Derived benchmark/index indicators. Correct source for IHSG roc_20 and ma20_slope_pct used by Watchlist market-index regime fields.
- Primary/identity key: `benchmark_indicator_id`
- Identifier key: `benchmark_code`
- Date/as-of key: `trade_date`
- Selection safety: Pre-trade safe for benchmark indicators if trade_date <= signal/asof date.

| Column | Type / contract | Field role |
|---|---|---|
| `benchmark_indicator_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `benchmark_code` | `VARCHAR(32) NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `roc_20` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma20` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma50` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma20_slope_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_to_ma20_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_to_ma50_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `is_valid` | `TINYINT(1) NOT NULL DEFAULT 0` | data field |
| `invalid_reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `indicator_set_version` | `VARCHAR(64) NOT NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NULL` | date/time / as-of or audit metadata |

### `eod_reason_codes`

- Purpose: Reason-code registry for EOD lifecycle, bars, indicators, eligibility and audit semantics.
- Primary/identity key: `code`
- Identifier key: `code`
- Date/as-of key: `n/a`
- Selection safety: Metadata only.

| Column | Type / contract | Field role |
|---|---|---|
| `code` | `VARCHAR(64) NOT NULL` | identifier / relationship key |
| `category` | `VARCHAR(32) NOT NULL` | data field |
| `description` | `VARCHAR(255) NOT NULL` | data field |
| `severity` | `ENUM('INFO','WARN','HARD') NOT NULL` | data field |
| `is_active` | `TINYINT(1) NOT NULL DEFAULT 1` | data field |
| `created_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NULL` | date/time / as-of or audit metadata |

### `eod_bars`

- Purpose: Current readable canonical equity EOD OHLCV rows, one row per trade_date/ticker_id.
- Primary/identity key: `(trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Pre-trade safe only for asof EOD/date <= signal date. Future path bars are evaluation only.

| Column | Type / contract | Field role |
|---|---|---|
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `open` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `high` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `low` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `close` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `volume` | `BIGINT NOT NULL` | raw price/volume market data |
| `adj_close` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_invalid_bars`

- Purpose: Rejected/invalid source-row evidence and reason-code payload.
- Primary/identity key: `invalid_bar_id`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Audit/evidence only; not a selection source.

| Column | Type / contract | Field role |
|---|---|---|
| `invalid_bar_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `source_row_ref` | `VARCHAR(255) NULL` | data field |
| `open` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `high` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `low` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `close` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `volume` | `BIGINT NULL` | raw price/volume market data |
| `adj_close` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `invalid_reason_code` | `VARCHAR(64) NOT NULL` | state/reason/audit field |
| `invalid_note` | `VARCHAR(255) NULL` | data field |
| `loser_of_trade_date` | `DATE NULL` | date/time / as-of or audit metadata |
| `loser_of_ticker_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_indicators`

- Purpose: Current readable derived equity indicators and event-risk/sector-rotation fields, one row per trade_date/ticker_id.
- Primary/identity key: `(trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Primary pre-trade indicator source if trade_date <= signal/asof date.

| Column | Type / contract | Field role |
|---|---|---|
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `is_valid` | `TINYINT(1) NOT NULL` | data field |
| `invalid_reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `indicator_set_version` | `VARCHAR(64) NOT NULL` | data field |
| `sector_code` | `VARCHAR(8) NULL` | identifier / relationship key |
| `dv20_idr` | `DECIMAL(24,2) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `atr14_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `vol_ratio` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc5` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc10` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc20` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `hh20` | `DECIMAL(20,4) NULL` | data field |
| `ll20` | `DECIMAL(20,4) NULL` | data field |
| `ma20` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma50` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_to_hh20_pct` | `DECIMAL(20,10) NULL` | data field |
| `close_to_ll20_pct` | `DECIMAL(20,10) NULL` | data field |
| `range_20_pct` | `DECIMAL(20,10) NULL` | data field |
| `range_position_20_pct` | `DECIMAL(20,10) NULL` | data field |
| `close_vs_ma20_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_vs_ma50_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma20_slope_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `rs_20_vs_ihsg` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `sector_roc20` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `rs_20_vs_sector` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `sector_rs_20_vs_ihsg` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `corporate_action_flag` | `TINYINT(1) NULL` | data field |
| `corporate_action_types` | `VARCHAR(255) NULL` | data field |
| `trading_status_code` | `VARCHAR(64) NULL` | data field |
| `is_suspended` | `TINYINT(1) NULL` | data field |
| `is_uma` | `TINYINT(1) NULL` | data field |
| `event_risk_flag` | `TINYINT(1) NULL` | data field |
| `event_risk_reasons` | `VARCHAR(255) NULL` | data field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_eligibility`

- Purpose: Current readable eligibility state and block reason per trade_date/ticker_id.
- Primary/identity key: `(trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Primary pre-trade eligibility source if trade_date <= signal/asof date.

| Column | Type / contract | Field role |
|---|---|---|
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `eligible` | `TINYINT(1) NOT NULL` | data field |
| `reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_runs`

- Purpose: Lifecycle/run audit header for source acquisition, backfill, recompute, promote and coverage-gate operations.
- Primary/identity key: `run_id`
- Identifier key: `run_id`
- Date/as-of key: `trade_date_requested/effective`
- Selection safety: Audit/run metadata; not trading selection feature unless explicitly whitelisted.

| Column | Type / contract | Field role |
|---|---|---|
| `run_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `trade_date_requested` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `trade_date_effective` | `DATE NULL` | date/time / as-of or audit metadata |
| `lifecycle_state` | `ENUM('PENDING','RUNNING','FINALIZING','COMPLETED','FAILED','CANCELLED') NOT NULL` | state/reason/audit field |
| `terminal_status` | `ENUM('SUCCESS','HELD','FAILED') NULL` | state/reason/audit field |
| `quality_gate_state` | `ENUM('PENDING','PASS','FAIL','BLOCKED') NOT NULL DEFAULT 'PENDING'` | state/reason/audit field |
| `publishability_state` | `ENUM('NOT_READABLE','READABLE') NOT NULL DEFAULT 'NOT_READABLE'` | state/reason/audit field |
| `stage` | `ENUM('INGEST_BARS','PUBLISH_BARS','COMPUTE_INDICATORS','BUILD_ELIGIBILITY','HASH','SEAL','FINALIZE') NOT NULL` | data field |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `request_mode` | `VARCHAR(32) NULL` | data field |
| `source_name` | `VARCHAR(64) NULL` | data field |
| `source_provider` | `VARCHAR(64) NULL` | data field |
| `source_input_file` | `VARCHAR(255) NULL` | data field |
| `source_timeout_seconds` | `INT NULL` | data field |
| `source_retry_max` | `INT NULL` | data field |
| `source_attempt_count` | `INT NULL` | data field |
| `source_success_after_retry` | `TINYINT(1) NULL` | data field |
| `source_retry_exhausted` | `TINYINT(1) NULL` | data field |
| `source_final_http_status` | `INT NULL` | data field |
| `source_final_reason_code` | `VARCHAR(64) NULL` | data field |
| `source_file_hash` | `VARCHAR(64) NULL` | data field |
| `source_file_hash_algorithm` | `VARCHAR(32) NULL` | data field |
| `source_file_size_bytes` | `BIGINT UNSIGNED NULL` | data field |
| `source_file_row_count` | `INT UNSIGNED NULL` | data field |
| `coverage_universe_count` | `INT NULL` | data field |
| `coverage_available_count` | `INT NULL` | data field |
| `coverage_missing_count` | `INT NULL` | data field |
| `coverage_ratio` | `DECIMAL(12,6) NULL` | data field |
| `coverage_min_threshold` | `DECIMAL(12,6) NULL` | data field |
| `coverage_gate_state` | `ENUM('PASS','FAIL','NOT_EVALUABLE') NULL` | data field |
| `coverage_threshold_mode` | `VARCHAR(32) NULL` | data field |
| `coverage_universe_basis` | `VARCHAR(64) NULL` | data field |
| `coverage_contract_version` | `VARCHAR(64) NULL` | data field |
| `coverage_missing_sample_json` | `JSON NULL` | data field |
| `bars_rows_written` | `INT NULL` | data field |
| `indicators_rows_written` | `INT NULL` | data field |
| `eligibility_rows_written` | `INT NULL` | data field |
| `invalid_bar_count` | `INT NULL` | data field |
| `invalid_indicator_count` | `INT NULL` | data field |
| `hard_reject_count` | `INT NULL` | data field |
| `warning_count` | `INT NULL` | data field |
| `notes` | `TEXT NULL` | data field |
| `bars_batch_hash` | `VARCHAR(64) NULL` | data field |
| `indicators_batch_hash` | `VARCHAR(64) NULL` | data field |
| `eligibility_batch_hash` | `VARCHAR(64) NULL` | data field |
| `config_version` | `VARCHAR(64) NULL` | data field |
| `config_hash` | `VARCHAR(64) NULL` | data field |
| `config_snapshot_ref` | `VARCHAR(255) NULL` | data field |
| `supersedes_run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `publication_version` | `INT UNSIGNED NULL` | data field |
| `is_current_publication` | `TINYINT(1) NOT NULL DEFAULT 0` | data field |
| `correction_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `promote_mode` | `VARCHAR(32) NULL` | data field |
| `publish_target` | `VARCHAR(64) NULL` | data field |
| `final_reason_code` | `VARCHAR(64) NULL` | data field |
| `sealed_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `sealed_by` | `VARCHAR(64) NULL` | data field |
| `seal_note` | `VARCHAR(255) NULL` | data field |
| `started_at` | `DATETIME NOT NULL` | data field |
| `finished_at` | `DATETIME NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_run_events`

- Purpose: Append-only run event log with stage/severity/reason payloads.
- Primary/identity key: `event_id`
- Identifier key: `run_id`
- Date/as-of key: `event_time/trade_date_requested`
- Selection safety: Audit/log only.

| Column | Type / contract | Field role |
|---|---|---|
| `event_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date_requested` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `event_time` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `stage` | `VARCHAR(64) NOT NULL` | data field |
| `event_type` | `VARCHAR(64) NOT NULL` | data field |
| `severity` | `ENUM('INFO','WARN','ERROR') NOT NULL` | data field |
| `reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `message` | `VARCHAR(255) NULL` | data field |
| `event_payload_json` | `LONGTEXT NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_publications`

- Purpose: Publication/seal records for current and superseded readable datasets.
- Primary/identity key: `publication_id`
- Identifier key: `run_id`
- Date/as-of key: `trade_date`
- Selection safety: Publication state; used to ensure readable state, not selection alpha.

| Column | Type / contract | Field role |
|---|---|---|
| `publication_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `publication_version` | `INT UNSIGNED NOT NULL` | data field |
| `is_current` | `TINYINT(1) NOT NULL DEFAULT 0` | data field |
| `supersedes_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `previous_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `replaced_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `seal_state` | `ENUM('SEALED','UNSEALED') NOT NULL DEFAULT 'UNSEALED'` | data field |
| `bars_batch_hash` | `VARCHAR(64) NULL` | data field |
| `indicators_batch_hash` | `VARCHAR(64) NULL` | data field |
| `eligibility_batch_hash` | `VARCHAR(64) NULL` | data field |
| `source_file_hash` | `VARCHAR(64) NULL` | data field |
| `source_file_hash_algorithm` | `VARCHAR(32) NULL` | data field |
| `source_file_size_bytes` | `BIGINT UNSIGNED NULL` | data field |
| `source_file_row_count` | `INT UNSIGNED NULL` | data field |
| `sealed_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_current_publication_pointer`

- Purpose: Canonical pointer from trade_date to the current readable publication.
- Primary/identity key: `trade_date`
- Identifier key: `publication_id`
- Date/as-of key: `trade_date`
- Selection safety: Publication pointer; use to resolve current readable state.

| Column | Type / contract | Field role |
|---|---|---|
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `publication_version` | `INT UNSIGNED NOT NULL` | data field |
| `sealed_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_dataset_corrections`

- Purpose: Correction workflow state and publication lineage for reprocessing/corrections.
- Primary/identity key: `correction_id`
- Identifier key: `prior_run/new_run`
- Date/as-of key: `trade_date`
- Selection safety: Correction audit only.

| Column | Type / contract | Field role |
|---|---|---|
| `correction_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `prior_run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `new_run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `baseline_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `replacement_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `correction_reason_code` | `VARCHAR(64) NOT NULL` | data field |
| `correction_reason_note` | `TEXT NULL` | data field |
| `status` | `ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','FAILED','REJECTED','CANCELLED','CLOSED') NOT NULL` | state/reason/audit field |
| `requested_by` | `VARCHAR(64) NOT NULL` | data field |
| `requested_at` | `DATETIME NOT NULL` | data field |
| `approved_by` | `VARCHAR(64) NULL` | data field |
| `approved_at` | `DATETIME NULL` | data field |
| `published_at` | `DATETIME NULL` | data field |
| `execution_count` | `INT UNSIGNED NOT NULL DEFAULT 0` | data field |
| `last_executed_at` | `DATETIME NULL` | data field |
| `current_consumed_at` | `DATETIME NULL` | data field |
| `final_outcome_note` | `TEXT NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_bars_history`

- Purpose: Publication-bound immutable history for equity EOD bars.
- Primary/identity key: `(publication_id,trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Audit/history; current consumer read should prefer current published rows/pointer unless replaying a locked publication.

| Column | Type / contract | Field role |
|---|---|---|
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `open` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `high` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `low` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `close` | `DECIMAL(20,4) NOT NULL` | raw price/volume market data |
| `volume` | `BIGINT NOT NULL` | raw price/volume market data |
| `adj_close` | `DECIMAL(20,4) NULL` | raw price/volume market data |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_indicators_history`

- Purpose: Publication-bound immutable history for equity indicators.
- Primary/identity key: `(publication_id,trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Audit/history; safe only when locked publication is explicit.

| Column | Type / contract | Field role |
|---|---|---|
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `is_valid` | `TINYINT(1) NOT NULL` | data field |
| `invalid_reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `indicator_set_version` | `VARCHAR(64) NOT NULL` | data field |
| `sector_code` | `VARCHAR(8) NULL` | identifier / relationship key |
| `dv20_idr` | `DECIMAL(24,2) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `atr14_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `vol_ratio` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc5` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc10` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `roc20` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `hh20` | `DECIMAL(20,4) NULL` | data field |
| `ll20` | `DECIMAL(20,4) NULL` | data field |
| `ma20` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma50` | `DECIMAL(20,4) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_to_hh20_pct` | `DECIMAL(20,10) NULL` | data field |
| `close_to_ll20_pct` | `DECIMAL(20,10) NULL` | data field |
| `range_20_pct` | `DECIMAL(20,10) NULL` | data field |
| `range_position_20_pct` | `DECIMAL(20,10) NULL` | data field |
| `close_vs_ma20_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `close_vs_ma50_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `ma20_slope_pct` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `rs_20_vs_ihsg` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `sector_roc20` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `rs_20_vs_sector` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `sector_rs_20_vs_ihsg` | `DECIMAL(20,10) NULL` | derived indicator / pre-trade safe only when as-of bounded |
| `corporate_action_flag` | `TINYINT(1) NULL` | data field |
| `corporate_action_types` | `VARCHAR(255) NULL` | data field |
| `trading_status_code` | `VARCHAR(64) NULL` | data field |
| `is_suspended` | `TINYINT(1) NULL` | data field |
| `is_uma` | `TINYINT(1) NULL` | data field |
| `event_risk_flag` | `TINYINT(1) NULL` | data field |
| `event_risk_reasons` | `VARCHAR(255) NULL` | data field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `eod_eligibility_history`

- Purpose: Publication-bound immutable history for eligibility rows.
- Primary/identity key: `(publication_id,trade_date,ticker_id)`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date`
- Selection safety: Audit/history; safe only when locked publication is explicit.

| Column | Type / contract | Field role |
|---|---|---|
| `publication_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `eligible` | `TINYINT(1) NOT NULL` | data field |
| `reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `run_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `md_session_snapshots`

- Purpose: Intraday/session snapshot storage for manual/live snapshot evidence.
- Primary/identity key: `snapshot_id`
- Identifier key: `ticker_id`
- Date/as-of key: `trade_date/captured_at`
- Selection safety: Confirm/intraday snapshot evidence; not EOD backtest selection unless explicitly modeled.

| Column | Type / contract | Field role |
|---|---|---|
| `snapshot_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `snapshot_slot` | `VARCHAR(32) NOT NULL` | data field |
| `ticker_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `captured_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |
| `last_price` | `DECIMAL(18,4) NULL` | data field |
| `prev_close` | `DECIMAL(18,4) NULL` | data field |
| `chg_pct` | `DECIMAL(18,10) NULL` | data field |
| `volume` | `BIGINT UNSIGNED NULL` | raw price/volume market data |
| `day_high` | `DECIMAL(18,4) NULL` | data field |
| `day_low` | `DECIMAL(18,4) NULL` | data field |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `reason_code` | `VARCHAR(64) NULL` | state/reason/audit field |
| `error_note` | `VARCHAR(255) NULL` | data field |
| `created_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `updated_at` | `DATETIME NULL` | date/time / as-of or audit metadata |

### `md_replay_daily_metrics`

- Purpose: Daily replay/evidence metrics for determinism, coverage and publishability verification.
- Primary/identity key: `replay_id + trade_date`
- Identifier key: `replay suite/case`
- Date/as-of key: `trade_date`
- Selection safety: Evaluation/audit only; not pre-trade selection input.

| Column | Type / contract | Field role |
|---|---|---|
| `replay_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `trade_date_effective` | `DATE NULL` | date/time / as-of or audit metadata |
| `replay_suite` | `VARCHAR(128) NULL` | data field |
| `replay_case` | `VARCHAR(128) NULL` | data field |
| `fixture_id` | `VARCHAR(128) NULL` | identifier / relationship key |
| `fixture_version` | `VARCHAR(64) NULL` | data field |
| `fixture_schema_version` | `VARCHAR(64) NULL` | data field |
| `fixture_source` | `VARCHAR(128) NULL` | data field |
| `fixture_created_at` | `VARCHAR(64) NULL` | data field |
| `source` | `VARCHAR(32) NOT NULL` | data field |
| `source_mode` | `VARCHAR(32) NULL` | data field |
| `source_name` | `VARCHAR(64) NULL` | data field |
| `source_provider` | `VARCHAR(64) NULL` | data field |
| `source_timeout_seconds` | `INT NULL` | data field |
| `source_retry_max` | `INT NULL` | data field |
| `source_attempt_count` | `INT NULL` | data field |
| `source_success_after_retry` | `TINYINT(1) NULL` | data field |
| `source_retry_exhausted` | `TINYINT(1) NULL` | data field |
| `source_final_http_status` | `INT NULL` | data field |
| `source_final_reason_code` | `VARCHAR(64) NULL` | data field |
| `source_input_file` | `VARCHAR(255) NULL` | data field |
| `status` | `ENUM('SUCCESS','HELD','FAILED') NOT NULL` | state/reason/audit field |
| `publishability_state` | `ENUM('READABLE','NOT_READABLE') NULL` | state/reason/audit field |
| `publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `publication_run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `comparison_result` | `ENUM('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED') NOT NULL` | data field |
| `replay_status` | `ENUM('PASS','FAIL','BLOCKED') NOT NULL` | data field |
| `comparison_note` | `VARCHAR(255) NULL` | data field |
| `artifact_changed_scope` | `VARCHAR(64) NULL` | data field |
| `config_identity` | `VARCHAR(128) NULL` | data field |
| `publication_version` | `INT UNSIGNED NULL` | data field |
| `is_current_publication` | `TINYINT(1) NULL` | data field |
| `correction_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `correction_status` | `VARCHAR(32) NULL` | data field |
| `correction_outcome` | `VARCHAR(32) NULL` | data field |
| `correction_reseal_status` | `VARCHAR(64) NULL` | data field |
| `correction_publication_switch` | `TINYINT(1) NULL` | data field |
| `baseline_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `candidate_publication_id` | `BIGINT UNSIGNED NULL` | date/time / as-of or audit metadata |
| `expected_correction_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `expected_correction_status` | `VARCHAR(32) NULL` | data field |
| `expected_correction_outcome` | `VARCHAR(32) NULL` | data field |
| `expected_correction_reseal_status` | `VARCHAR(64) NULL` | data field |
| `expected_correction_publication_switch` | `TINYINT(1) NULL` | data field |
| `expected_baseline_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `expected_candidate_publication_id` | `BIGINT UNSIGNED NULL` | date/time / as-of or audit metadata |
| `coverage_universe_count` | `INT NULL` | data field |
| `coverage_available_count` | `INT NULL` | data field |
| `coverage_missing_count` | `INT NULL` | data field |
| `coverage_ratio` | `DECIMAL(12,6) NULL` | data field |
| `coverage_min_threshold` | `DECIMAL(12,6) NULL` | data field |
| `coverage_gate_state` | `VARCHAR(16) NULL` | data field |
| `coverage_threshold_mode` | `VARCHAR(32) NULL` | data field |
| `coverage_universe_basis` | `VARCHAR(64) NULL` | data field |
| `coverage_contract_version` | `VARCHAR(64) NULL` | data field |
| `coverage_missing_sample_json` | `JSON NULL` | data field |
| `bars_rows_written` | `INT NULL` | data field |
| `indicators_rows_written` | `INT NULL` | data field |
| `eligibility_rows_written` | `INT NULL` | data field |
| `eligible_count` | `INT NULL` | data field |
| `invalid_bar_count` | `INT NULL` | data field |
| `invalid_indicator_count` | `INT NULL` | data field |
| `warning_count` | `INT NULL` | data field |
| `hard_reject_count` | `INT NULL` | data field |
| `bars_batch_hash` | `VARCHAR(64) NULL` | data field |
| `indicators_batch_hash` | `VARCHAR(64) NULL` | data field |
| `eligibility_batch_hash` | `VARCHAR(64) NULL` | data field |
| `seal_state` | `ENUM('SEALED','UNSEALED') NOT NULL` | data field |
| `sealed_at` | `DATETIME NULL` | date/time / as-of or audit metadata |
| `expected_status` | `ENUM('SUCCESS','HELD','FAILED') NULL` | data field |
| `expected_terminal_status` | `ENUM('SUCCESS','HELD','FAILED') NULL` | data field |
| `expected_publishability_state` | `ENUM('READABLE','NOT_READABLE') NULL` | data field |
| `expected_trade_date_effective` | `DATE NULL` | date/time / as-of or audit metadata |
| `expected_seal_state` | `ENUM('SEALED','UNSEALED') NULL` | data field |
| `expected_source_mode` | `VARCHAR(32) NULL` | data field |
| `expected_source_name` | `VARCHAR(64) NULL` | data field |
| `expected_source_provider` | `VARCHAR(64) NULL` | data field |
| `expected_source_timeout_seconds` | `INT NULL` | data field |
| `expected_source_retry_max` | `INT NULL` | data field |
| `expected_source_attempt_count` | `INT NULL` | data field |
| `expected_source_success_after_retry` | `TINYINT(1) NULL` | data field |
| `expected_source_retry_exhausted` | `TINYINT(1) NULL` | data field |
| `expected_source_final_http_status` | `INT NULL` | data field |
| `expected_source_final_reason_code` | `VARCHAR(64) NULL` | data field |
| `expected_source_input_file` | `VARCHAR(255) NULL` | data field |
| `expected_source_file_hash` | `VARCHAR(128) NULL` | data field |
| `expected_source_file_hash_algorithm` | `VARCHAR(32) NULL` | data field |
| `expected_source_file_size_bytes` | `BIGINT UNSIGNED NULL` | data field |
| `expected_source_file_row_count` | `INT UNSIGNED NULL` | data field |
| `expected_config_identity` | `VARCHAR(128) NULL` | data field |
| `expected_publication_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `expected_publication_run_id` | `BIGINT UNSIGNED NULL` | identifier / relationship key |
| `expected_publication_version` | `INT UNSIGNED NULL` | data field |
| `expected_is_current_publication` | `TINYINT(1) NULL` | data field |
| `expected_coverage_universe_count` | `INT NULL` | data field |
| `expected_coverage_available_count` | `INT NULL` | data field |
| `expected_coverage_missing_count` | `INT NULL` | data field |
| `expected_coverage_ratio` | `DECIMAL(12,6) NULL` | data field |
| `expected_coverage_min_threshold` | `DECIMAL(12,6) NULL` | data field |
| `expected_coverage_gate_state` | `VARCHAR(16) NULL` | data field |
| `expected_coverage_threshold_mode` | `VARCHAR(32) NULL` | data field |
| `expected_coverage_universe_basis` | `VARCHAR(64) NULL` | data field |
| `expected_coverage_contract_version` | `VARCHAR(64) NULL` | data field |
| `expected_coverage_missing_sample_json` | `JSON NULL` | data field |
| `expected_bars_batch_hash` | `VARCHAR(64) NULL` | data field |
| `expected_indicators_batch_hash` | `VARCHAR(64) NULL` | data field |
| `expected_eligibility_batch_hash` | `VARCHAR(64) NULL` | data field |
| `expected_reason_code_counts_json` | `LONGTEXT NULL` | data field |
| `mismatch_summary` | `LONGTEXT NULL` | data field |
| `mismatch_count` | `INT NULL` | data field |
| `mismatch_reason_codes_json` | `JSON NULL` | data field |
| `mismatches_json` | `LONGTEXT NULL` | data field |
| `expected_context_json` | `LONGTEXT NULL` | data field |
| `actual_context_json` | `LONGTEXT NULL` | data field |
| `ignored_volatile_fields_json` | `JSON NULL` | data field |
| `deterministic_fields_checked_json` | `JSON NULL` | data field |
| `final_reason_code` | `VARCHAR(64) NULL` | data field |
| `created_at` | `DATETIME NOT NULL` | date/time / as-of or audit metadata |

### `md_replay_reason_code_counts`

- Purpose: Aggregated reason-code counts by replay/date.
- Primary/identity key: `(replay_id,trade_date,reason_code)`
- Identifier key: `reason_code`
- Date/as-of key: `trade_date`
- Selection safety: Evaluation/audit only.

| Column | Type / contract | Field role |
|---|---|---|
| `replay_id` | `BIGINT UNSIGNED NOT NULL` | identifier / relationship key |
| `trade_date` | `DATE NOT NULL` | date/time / as-of or audit metadata |
| `reason_code` | `VARCHAR(64) NOT NULL` | state/reason/audit field |
| `reason_count` | `INT NOT NULL` | data field |

## Watchlist Consumer Notes

- Watchlist PLAN/CONFIRM and backtest must read Market Data via published/current readable state unless a locked artifact explicitly allows another source.
- For market-index regime reconstruction, use `market_benchmark_indicators` with `benchmark_code=IHSG`, not `eod_indicators`.
- Backtest return/path fields are evaluation-only. They must never be used to define selection, source reconstruction, regime fields, or candidate eligibility.
- OOS rows, OOS returns, OOS bad months, and future raw path prices are forbidden for IS tuning or candidate selection.

## When This Dictionary Must Be Updated

Update this dictionary when any migration adds/renames/removes a table/column, when a feature discovers a missing field mapping, or when a module starts using a table for a new purpose. Update the related governance/tracker docs in the same session.
