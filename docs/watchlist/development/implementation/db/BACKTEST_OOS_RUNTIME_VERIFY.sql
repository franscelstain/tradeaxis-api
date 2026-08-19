-- Read-only verification for the chronological 70/30 OOS runtime schema.

SELECT DATABASE() AS active_database;

SHOW TABLES LIKE 'watchlist_bt_param_grid';
SHOW TABLES LIKE 'watchlist_bt_eval';
SHOW TABLES LIKE 'watchlist_bt_oos_eval_ws';

SHOW COLUMNS FROM watchlist_bt_param_grid;
SHOW COLUMNS FROM watchlist_bt_eval;
SHOW COLUMNS FROM watchlist_bt_oos_eval_ws;

SHOW INDEX FROM watchlist_bt_param_grid WHERE Key_name = 'UQ_bt_grid_policy_payload';
SHOW INDEX FROM watchlist_bt_eval WHERE Key_name = 'UQ_bt_eval_policy_param_window';
SHOW INDEX FROM watchlist_bt_oos_eval_ws WHERE Key_name = 'UQ_bt_oos_policy_param_windows';

SELECT COUNT(*) AS ws_param_grid_count
FROM watchlist_bt_param_grid
WHERE policy_code = 'WS';

SELECT
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
  secondary_min_score_q,
  COUNT(*) AS duplicate_count
FROM watchlist_bt_param_grid
GROUP BY
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
HAVING COUNT(*) > 1;

SELECT
  eval_id,
  policy_code,
  param_id,
  eval_model,
  paramset_hash,
  from_date,
  to_date,
  picks_count,
  created_at
FROM watchlist_bt_eval
WHERE policy_code = 'WS'
ORDER BY eval_id DESC
LIMIT 20;

SELECT
  oos_id,
  policy_code,
  param_id_best_is,
  is_eval_id,
  eval_model,
  from_date_is,
  to_date_is,
  from_date_oos,
  to_date_oos,
  picks_count_oos,
  created_at
FROM watchlist_bt_oos_eval_ws
WHERE policy_code = 'WS'
ORDER BY oos_id DESC
LIMIT 20;
