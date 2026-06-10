-- Existing-database closure for the chronological 70/30 OOS runtime.
-- MariaDB 10.4+ / InnoDB.
-- Run once before BACKTEST_PARAM_GRID_SEED.sql when watchlist_bt_param_grid
-- was created from an older DDL that did not include stop_atr_mult/min_rr.

ALTER TABLE watchlist_bt_param_grid
  ADD COLUMN IF NOT EXISTS stop_atr_mult DECIMAL(10,6) NOT NULL DEFAULT 1.500000 AFTER w_risk,
  ADD COLUMN IF NOT EXISTS min_rr DECIMAL(10,6) NOT NULL DEFAULT 1.500000 AFTER stop_atr_mult;

-- Fail closed on duplicate canonical payloads. This statement intentionally
-- fails if duplicate rows already exist and must not silently delete evidence.
ALTER TABLE watchlist_bt_param_grid
  DROP INDEX IF EXISTS UQ_bt_grid_policy_payload;

ALTER TABLE watchlist_bt_param_grid
  ADD UNIQUE KEY UQ_bt_grid_policy_payload (
    policy_code,
    min_dv20_idr,
    max_atr14_pct,
    min_vol_ratio,
    w_momentum,
    w_volume,
    w_breakout,
    w_risk,
    stop_atr_mult,
    min_rr,
    top_picks_target,
    secondary_target,
    top_min_score_q,
    secondary_min_score_q
  );

SHOW COLUMNS FROM watchlist_bt_param_grid;
SHOW INDEX FROM watchlist_bt_param_grid WHERE Key_name = 'UQ_bt_grid_policy_payload';

-- Preserve historical evaluation evidence while allowing the same policy,
-- param and date window to be rerun after a documented evaluation-model or
-- paramset change. Existing rows are explicitly marked as legacy rather than
-- overwritten or deleted.
ALTER TABLE watchlist_bt_eval
  ADD COLUMN IF NOT EXISTS eval_model VARCHAR(96) NOT NULL DEFAULT 'LEGACY_UNVERSIONED' AFTER param_id,
  ADD COLUMN IF NOT EXISTS paramset_hash CHAR(40) NOT NULL DEFAULT '0000000000000000000000000000000000000000' AFTER eval_model;

ALTER TABLE watchlist_bt_eval
  DROP INDEX IF EXISTS UQ_bt_eval_policy_param_window;

ALTER TABLE watchlist_bt_eval
  ADD UNIQUE KEY UQ_bt_eval_policy_param_window (
    policy_code,
    param_id,
    eval_model,
    paramset_hash,
    from_date,
    to_date
  );

SHOW COLUMNS FROM watchlist_bt_eval;
SHOW INDEX FROM watchlist_bt_eval WHERE Key_name = 'UQ_bt_eval_policy_param_window';

-- OOS identity includes the exact frozen IS evaluation. A later corrected IS
-- evaluation for the same date windows must coexist rather than overwrite the
-- earlier OOS evidence.
ALTER TABLE watchlist_bt_oos_eval_ws
  DROP INDEX IF EXISTS UQ_bt_oos_policy_param_windows;

ALTER TABLE watchlist_bt_oos_eval_ws
  ADD UNIQUE KEY UQ_bt_oos_policy_param_windows (
    policy_code,
    policy_version,
    eval_model,
    param_id_best_is,
    is_eval_id,
    from_date_is,
    to_date_is,
    from_date_oos,
    to_date_oos
  );

SHOW INDEX FROM watchlist_bt_oos_eval_ws WHERE Key_name = 'UQ_bt_oos_policy_param_windows';
