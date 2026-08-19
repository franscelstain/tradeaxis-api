# Watchlist Database Dictionary

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


Status: `WATCHLIST_OWNED_PERSISTENCE_REFERENCE`

Last updated: 2026-08-17

This document describes **Watchlist-owned persistence only**. It does not authorize Watchlist to consume Market Data tables directly. Market Data intake semantics are owned by `../MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md` and the producer-facing Market Data consumer read contracts.

## Executable Schema Status

Migration `2026_07_24_000001_create_watchlist_runtime_paramset_and_plan_schema.php` has been executed on the local runtime database and owns these currently implemented core tables:

- `watchlist_fail_codes`;
- `watchlist_reason_codes`;
- `watchlist_param_sets`;
- `watchlist_plan_runs`;
- `watchlist_plan_items`.

The migration also installs four MySQL append-only guards for PLAN runs/items. The current runtime state contains one DRAFT Weekly Swing paramset, zero ACTIVE paramsets, zero PLAN runs, and zero PLAN items.

The CONFIRM tables listed below remain normative target schema and are not created by the C169 migration. Recommendation persistence has no owner-approved physical table contract yet, so no recommendation table is inferred or created.

## Mandatory Market Data Consumer Rule

- Runtime Market Data intake goes through `../MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`, not producer tables.
- Watchlist must preserve producer requested/effective date, publication/read-model identity, readiness/freshness, `data_usable`, and active field semantics.
- Physical Market Data table names are not part of this dictionary's runtime contract.
- Backtest return/path fields are evaluation-only and forbidden as selection inputs.
- OOS rows/returns/bad months are forbidden for IS tuning or candidate selection.

## Watchlist Core Tables

| Table | Purpose | Important keys/date |
|---|---|---|
| `watchlist_fail_codes` | Fail-code registry for Watchlist rule failures. | description_id |
| `watchlist_reason_codes` | Reason-code registry for policy/item explanations. | policy_code, short_id, description_id |
| `watchlist_param_sets` | Versioned Watchlist policy parameter sets and provenance. | param_set_id, policy_code |
| `watchlist_plan_runs` | PLAN run header and data/publication evidence. | plan_run_id, policy_code, asof_eod_date, plan_trade_date, param_set_id |
| `watchlist_plan_items` | PLAN selected ticker rows and display/scoring metadata. | plan_item_id, plan_run_id, policy_code, trade_date, ticker_id |
| `watchlist_confirm_checks` | CONFIRM check/run header. | confirm_check_id, plan_run_id, policy_code |
| `watchlist_confirm_items` | CONFIRM item rows for selected tickers. | confirm_item_id, confirm_check_id, ticker_id |
| `watchlist_confirm_snapshots` | Snapshot header for runtime/intraday confirm evidence. | snapshot_id, policy_code, trade_date |
| `watchlist_confirm_snapshot_items` | Snapshot item rows for runtime market state. | snapshot_item_id, snapshot_id, ticker_code, ticker_id |

## Watchlist Core Columns

### `watchlist_fail_codes`

| Column | Type / contract |
|---|---|
| `fail_code` | `VARCHAR(64) NOT NULL` |
| `scope` | `ENUM('PLAN','RECOMMENDATION','CONFIRM','SHARED') NOT NULL` |
| `severity` | `ENUM('INFO','WARN','ERROR') NOT NULL` |
| `description_id` | `TEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_reason_codes`

| Column | Type / contract |
|---|---|
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `reason_code` | `VARCHAR(64) NOT NULL` |
| `scope` | `ENUM('PLAN','RECOMMENDATION','CONFIRM','BT') NOT NULL` |
| `severity` | `ENUM('INFO','WARN','BLOCK') NOT NULL` |
| `short_id` | `VARCHAR(32) NOT NULL` |
| `description_id` | `TEXT NOT NULL` |
| `description_en` | `TEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_param_sets`

| Column | Type / contract |
|---|---|
| `param_set_id` | `BIGINT NOT NULL AUTO_INCREMENT` |
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `policy_version` | `VARCHAR(64) NOT NULL` |
| `schema_version` | `VARCHAR(64) NOT NULL` |
| `hash_contract` | `LONGTEXT NOT NULL` |
| `provenance_json` | `LONGTEXT NOT NULL` |
| `status` | `ENUM('DRAFT','ACTIVE','DEPRECATED') NOT NULL DEFAULT 'DRAFT'` |
| `params_json` | `LONGTEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `updated_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP` |

### `watchlist_plan_runs`

| Column | Type / contract |
|---|---|
| `plan_run_id` | `BIGINT NOT NULL AUTO_INCREMENT` |
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `policy_version` | `VARCHAR(64) NOT NULL` |
| `asof_eod_date` | `DATE NOT NULL` |
| `plan_trade_date` | `DATE NOT NULL` |
| `param_set_id` | `BIGINT NOT NULL` |
| `run_status` | `ENUM('OK','NO_TRADE','FAILED') NOT NULL` |
| `data_batch_hash` | `CHAR(64) NOT NULL` |
| `hash_count` | `INT NOT NULL DEFAULT 0` |
| `missing_required_count` | `INT NOT NULL DEFAULT 0` |
| `processed_count` | `INT NOT NULL DEFAULT 0` |
| `eligible_count` | `INT NOT NULL DEFAULT 0` |
| `supersedes_plan_run_id` | `BIGINT NULL` |
| `is_active` | `ENUM('Yes','No') NOT NULL DEFAULT 'Yes'` |
| `fail_code` | `VARCHAR(64) NULL` |
| `run_metrics_json` | `LONGTEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_plan_items`

| Column | Type / contract |
|---|---|
| `plan_item_id` | `BIGINT NOT NULL AUTO_INCREMENT` |
| `plan_run_id` | `BIGINT NOT NULL` |
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `trade_date` | `DATE NOT NULL` |
| `ticker_id` | `BIGINT NOT NULL` |
| `ticker_code` | `VARCHAR(16) NULL` |
| `group_semantic` | `ENUM('TOP_PICKS','SECONDARY','WATCH_ONLY','AVOID') NOT NULL` |
| `display_bucket` | `ENUM('SHOW','HIDE') NOT NULL` |
| `selection_reason_code` | `VARCHAR(64) NOT NULL` |
| `score_total` | `DECIMAL(10,6) NOT NULL DEFAULT 0` |
| `scores_json` | `LONGTEXT NOT NULL` |
| `inputs_json` | `LONGTEXT NOT NULL` |
| `plan_levels_json` | `LONGTEXT NOT NULL` |
| `reason_codes_json` | `LONGTEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_confirm_checks`

| Column | Type / contract |
|---|---|
| `confirm_check_id` | `BIGINT NOT NULL AUTO_INCREMENT` |
| `plan_run_id` | `BIGINT NOT NULL` |
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `policy_version` | `VARCHAR(64) NOT NULL` |
| `checked_at` | `DATETIME NOT NULL` |
| `snapshot_age_sec` | `INT NOT NULL DEFAULT 0` |
| `run_status` | `ENUM('OK','FAILED') NOT NULL` |
| `fail_code` | `VARCHAR(64) NULL` |
| `run_metrics_json` | `LONGTEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_confirm_items`

| Column | Type / contract |
|---|---|
| `confirm_item_id` | `BIGINT NOT NULL AUTO_INCREMENT` |
| `confirm_check_id` | `BIGINT NOT NULL` |
| `ticker_id` | `BIGINT NOT NULL` |
| `label` | `ENUM('CONFIRMED','NEUTRAL','CAUTION','DELAY') NOT NULL` |
| `runtime_json` | `LONGTEXT NOT NULL` |
| `reason_codes_json` | `LONGTEXT NOT NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_confirm_snapshots`

| Column | Type / contract |
|---|---|
| `snapshot_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `policy_code` | `VARCHAR(16) NOT NULL` |
| `trade_date` | `DATE NOT NULL` |
| `captured_at` | `DATETIME NOT NULL` |
| `inserted_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `source` | `VARCHAR(32) NOT NULL DEFAULT 'manual'` |
| `note` | `TEXT NULL` |
| `snapshot_hash` | `CHAR(64) NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |

### `watchlist_confirm_snapshot_items`

| Column | Type / contract |
|---|---|
| `snapshot_item_id` | `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT` |
| `snapshot_id` | `BIGINT UNSIGNED NOT NULL` |
| `ticker_code` | `VARCHAR(16) NOT NULL` |
| `ticker_id` | `BIGINT UNSIGNED NULL` |
| `last_price` | `INT UNSIGNED NOT NULL` |
| `chg_pct` | `DECIMAL(8,4) NOT NULL` |
| `volume_shares` | `BIGINT UNSIGNED NOT NULL` |
| `turnover_idr` | `BIGINT UNSIGNED NOT NULL` |
| `item_hash` | `CHAR(64) NULL` |
| `created_at` | `DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `REFERENCES` | `watchlist_confirm_snapshots(snapshot_id)` |
| `ON` | `DELETE RESTRICT` |
| `ON` | `UPDATE RESTRICT` |

## Watchlist Backtest Tables

The Watchlist backtest tables are migration-owned in `database/migrations/2026_06_09_000001_create_watchlist_backtest_oos_schema.php` and follow-up migrations. Their role is calibration/evaluation evidence; production PLAN/CONFIRM behavior must not be mutated by backtest-only tables.

| Table | Purpose | Selection safety |
|---|---|---|
| `watchlist_bt_param_grid` | Candidate parameter/catalog grid. | IS selection config only when created from predeclared rules, not OOS returns. |
| `watchlist_bt_eval` | IS evaluation aggregate metrics. | Evaluation-only metrics; returns must not reselect outside allowed IS calibration design. |
| `watchlist_bt_oos_eval_ws` | OOS proof aggregate metrics. | OOS proof/evaluation only; forbidden for tuning/reselection. |
| `watchlist_bt_picks_ws` | Backtest pick rows including return evaluation. | `ret_net` is evaluation-only and forbidden for source/selection reconstruction. |
| `watchlist_bt_universe_ws` | Backtest universe/eligibility cache. | Pre-trade safe only for as-of fields; no future path. |
| `watchlist_bt_cutoffs_ws` | Backtest score cutoff cache. | Valid only for locked IS catalog logic. |

C171 state note: official picks, universe, and cutoff rows are versioned by `eval_id` and have been persisted for canonical IS evidence. `watchlist_bt_eval` owns paramset/model/implementation identity and immutable evidence-manifest hashes. The C171 C01 tick-risk repair additionally versions evidence construction through `evidence_pipeline_version` and `evidence_pipeline_hash`; historical evals remain legacy V1 while corrected reruns use V2 and receive new eval IDs. Backtest tables remain non-production evidence surfaces and do not authorize OOS, promotion, PLAN, CONFIRM, or runtime activation.
