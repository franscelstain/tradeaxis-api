-- Canonical deterministic Weekly Swing bootstrap parameter grid.
-- Runtime source remains watchlist_bt_param_grid; this file is an idempotent seed artifact.
-- Acceptance gates are not changed by this seed.
-- Requires stop_atr_mult and min_rr columns from BACKTEST_SCHEMA_DDL.sql / migration 2026_06_09_000002.
-- Catalog is declared before OOS execution and must never be altered from OOS outcomes.

START TRANSACTION;

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_01_BASELINE'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.120000 AND
    min_vol_ratio = 1.200000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.500000 AND
    min_rr = 1.500000 AND
    top_picks_target = 5 AND
    secondary_target = 10 AND
    top_min_score_q = 0.800000 AND
    secondary_min_score_q = 0.650000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  1000000000,
  0.120000,
  1.200000,
  0.300000,
  0.200000,
  0.300000,
  0.200000,
  1.500000,
  1.500000,
  5,
  10,
  0.800000,
  0.650000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_01_BASELINE'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.120000 AND
    min_vol_ratio = 1.200000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.500000 AND
    min_rr = 1.500000 AND
    top_picks_target = 5 AND
    secondary_target = 10 AND
    top_min_score_q = 0.800000 AND
    secondary_min_score_q = 0.650000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_02_BALANCED_QUALITY'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.100000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.100000,
  1.500000,
  0.300000,
  0.200000,
  0.350000,
  0.150000,
  1.250000,
  1.500000,
  3,
  5,
  0.850000,
  0.700000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_02_BALANCED_QUALITY'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.100000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_03_BREAKOUT_QUALITY'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.080000,
  1.500000,
  0.250000,
  0.150000,
  0.450000,
  0.150000,
  1.000000,
  2.000000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_03_BREAKOUT_QUALITY'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_04_VOLUME_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.080000,
  2.000000,
  0.200000,
  0.300000,
  0.350000,
  0.150000,
  1.000000,
  1.500000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_04_VOLUME_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_05_HIGH_LIQ_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.400000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.080000,
  1.500000,
  0.250000,
  0.200000,
  0.400000,
  0.150000,
  1.250000,
  2.000000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_05_HIGH_LIQ_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.080000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.400000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_06_LOW_ATR_BALANCED'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.050000,
  1.500000,
  0.300000,
  0.200000,
  0.350000,
  0.150000,
  1.000000,
  1.500000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_06_LOW_ATR_BALANCED'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_07_LOW_ATR_HIGH_VOLUME'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.040000,
  2.000000,
  0.200000,
  0.300000,
  0.350000,
  0.150000,
  1.000000,
  2.000000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_07_LOW_ATR_HIGH_VOLUME'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_08_STRICT_QUALITY'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.250000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.050000,
  2.000000,
  0.250000,
  0.250000,
  0.350000,
  0.150000,
  1.000000,
  1.500000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_08_STRICT_QUALITY'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.250000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_09_MOMENTUM_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.350000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.060000,
  1.500000,
  0.350000,
  0.150000,
  0.350000,
  0.150000,
  1.250000,
  2.000000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_09_MOMENTUM_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.350000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_10_RISK_TILT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.060000,
  1.500000,
  0.250000,
  0.150000,
  0.300000,
  0.300000,
  1.000000,
  1.500000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_10_RISK_TILT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_11_VOLUME_TILT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.350000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  1000000000,
  0.060000,
  2.000000,
  0.200000,
  0.350000,
  0.300000,
  0.150000,
  1.000000,
  2.000000,
  3,
  5,
  0.900000,
  0.750000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_11_VOLUME_TILT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.060000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.350000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.900000 AND
    secondary_min_score_q = 0.750000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_12_BROAD_LOW_ATR'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  1000000000,
  0.050000,
  1.500000,
  0.300000,
  0.200000,
  0.300000,
  0.200000,
  1.000000,
  1.500000,
  3,
  5,
  0.850000,
  0.700000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_12_BROAD_LOW_ATR'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 1000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_13_HIGH_LIQ_MODERATE'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.070000 AND
    min_vol_ratio = 1.200000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.070000,
  1.200000,
  0.300000,
  0.150000,
  0.350000,
  0.200000,
  1.250000,
  1.500000,
  3,
  5,
  0.850000,
  0.700000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_13_HIGH_LIQ_MODERATE'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.070000 AND
    min_vol_ratio = 1.200000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.200000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 1.500000 AND
    top_picks_target = 3 AND
    secondary_target = 5 AND
    top_min_score_q = 0.850000 AND
    secondary_min_score_q = 0.700000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_14_VERY_LOW_ATR'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.040000,
  1.500000,
  0.300000,
  0.200000,
  0.350000,
  0.150000,
  1.000000,
  2.000000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_14_VERY_LOW_ATR'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_15_ULTRA_STRICT_VOLUME'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.035000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.035000,
  2.000000,
  0.200000,
  0.300000,
  0.350000,
  0.150000,
  1.000000,
  2.000000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_15_ULTRA_STRICT_VOLUME'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.035000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_16_ULTRA_STRICT_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.400000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.040000,
  1.500000,
  0.300000,
  0.150000,
  0.400000,
  0.150000,
  1.000000,
  2.500000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_16_ULTRA_STRICT_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.400000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_17_BREAKOUT_MAX'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.070000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.500000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.070000,
  1.500000,
  0.200000,
  0.150000,
  0.500000,
  0.150000,
  1.250000,
  2.000000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_17_BREAKOUT_MAX'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.070000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.500000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.250000 AND
    min_rr = 2.000000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_18_RISK_QUALITY_MAX'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.050000,
  1.500000,
  0.250000,
  0.150000,
  0.300000,
  0.300000,
  1.000000,
  1.500000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_18_RISK_QUALITY_MAX'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.050000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_19_DOWNSIDE_CAP_BALANCED'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.040000,
  1.500000,
  0.300000,
  0.200000,
  0.350000,
  0.150000,
  0.750000,
  1.500000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_19_DOWNSIDE_CAP_BALANCED'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_20_DOWNSIDE_CAP_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.040000,
  1.500000,
  0.250000,
  0.150000,
  0.450000,
  0.150000,
  0.750000,
  2.000000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_20_DOWNSIDE_CAP_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_21_DOWNSIDE_CAP_VOLUME'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 1.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.040000,
  2.000000,
  0.200000,
  0.300000,
  0.350000,
  0.150000,
  0.750000,
  1.500000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_21_DOWNSIDE_CAP_VOLUME'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.040000 AND
    min_vol_ratio = 2.000000 AND
    w_momentum = 0.200000 AND
    w_volume = 0.300000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 1.500000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_22_ULTRA_LOW_ATR_BALANCED'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  2500000000,
  0.030000,
  1.500000,
  0.300000,
  0.200000,
  0.350000,
  0.150000,
  1.000000,
  1.500000,
  2,
  3,
  0.950000,
  0.800000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_22_ULTRA_LOW_ATR_BALANCED'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 2500000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.300000 AND
    w_volume = 0.200000 AND
    w_breakout = 0.350000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 1.500000 AND
    top_picks_target = 2 AND
    secondary_target = 3 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.800000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_23_ULTRA_LOW_ATR_BREAKOUT'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.030000,
  1.500000,
  0.250000,
  0.150000,
  0.450000,
  0.150000,
  1.000000,
  2.000000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_23_ULTRA_LOW_ATR_BREAKOUT'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.450000 AND
    w_risk = 0.150000 AND
    stop_atr_mult = 1.000000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

UPDATE watchlist_bt_param_grid
SET notes = 'WS_BT_GRID_BOOTSTRAP_2026_06_24_ULTRA_LOW_ATR_RISK'
WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000;

INSERT INTO watchlist_bt_param_grid (
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
  notes
)
SELECT
  'WS',
  5000000000,
  0.030000,
  1.500000,
  0.250000,
  0.150000,
  0.300000,
  0.300000,
  0.750000,
  2.000000,
  1,
  2,
  0.950000,
  0.850000,
  'WS_BT_GRID_BOOTSTRAP_2026_06_24_ULTRA_LOW_ATR_RISK'
WHERE NOT EXISTS (
  SELECT 1
  FROM watchlist_bt_param_grid
  WHERE policy_code = 'WS' AND
    min_dv20_idr = 5000000000 AND
    max_atr14_pct = 0.030000 AND
    min_vol_ratio = 1.500000 AND
    w_momentum = 0.250000 AND
    w_volume = 0.150000 AND
    w_breakout = 0.300000 AND
    w_risk = 0.300000 AND
    stop_atr_mult = 0.750000 AND
    min_rr = 2.000000 AND
    top_picks_target = 1 AND
    secondary_target = 2 AND
    top_min_score_q = 0.950000 AND
    secondary_min_score_q = 0.850000
);

COMMIT;

SELECT COUNT(*) AS ws_param_grid_count
FROM watchlist_bt_param_grid
WHERE policy_code = 'WS';
