-- NON-NORMATIVE SQL ARTIFACT
-- REVIEWER RULE: semantic changes must be introduced in normative markdown first.
-- Dokumen owner semantik tetap berada pada file markdown normatif terkait.
-- Jika ada konflik, file normatif markdown selalu menang dan SQL ini harus disesuaikan.

-- Implementation artifact for Weekly Swing backtest schema.
-- Normative schema and calibration owner:
-- docs/watchlist/strategy/weekly_swing/12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md
--
-- Column semantics, scope, and backtest-related meaning must follow the
-- normative Weekly Swing backtest documents.
-- This SQL file realizes schema implementation and does not replace
-- normative ownership.

-- 08_WS_BACKTEST_SCHEMA_DDL.sql
-- Schema backtest untuk policy Weekly Swing (WS)
-- MariaDB 10.4 / InnoDB
--
-- Catatan desain:
-- - policy_code untuk saat ini dikunci 'WS' lewat prosedur runner (bukan constraint).
-- - Semua hasil evaluasi param grid disimpan agar bisa diaudit dan diulang.
-- - Tabel dibuat minimal, tapi cukup untuk: grid param → eval → picks per asof date.

CREATE TABLE IF NOT EXISTS watchlist_bt_param_grid (
  param_id INT NOT NULL AUTO_INCREMENT,
  policy_code VARCHAR(16) NOT NULL,
  catalog_code VARCHAR(64) NOT NULL,
  catalog_version VARCHAR(16) NOT NULL,
  catalog_hash CHAR(40) NOT NULL,
  row_code VARCHAR(64) NOT NULL,
  row_hash CHAR(40) NOT NULL,
  rationale TEXT NOT NULL,
  -- entry-quality guardrails and scoring inputs
  min_dv20_idr BIGINT UNSIGNED NOT NULL,
  max_dv20_idr BIGINT UNSIGNED NULL,
  dv20_strong_idr BIGINT UNSIGNED NOT NULL,
  min_vol_ratio DECIMAL(10,6) NOT NULL,
  max_vol_ratio DECIMAL(20,6) NULL,
  strong_vol_ratio DECIMAL(10,6) NOT NULL,
  min_atr14_pct DECIMAL(10,6) NOT NULL,
  max_atr14_pct DECIMAL(10,6) NOT NULL,
  max_signal_tick_risk_expansion_pct DECIMAL(10,6) NULL,
  atr_ideal_low DECIMAL(10,6) NOT NULL,
  atr_ideal_high DECIMAL(10,6) NOT NULL,
  roc_lo DECIMAL(10,6) NOT NULL,
  roc_hi DECIMAL(10,6) NOT NULL,
  mom_roc20_soft_min DECIMAL(10,6) NOT NULL,
  bo_near_below_pct DECIMAL(10,6) NOT NULL,
  bo_max_ext_pct DECIMAL(10,6) NOT NULL,
  -- scoring weights; canonical invariant total = 1
  w_momentum DECIMAL(10,6) NOT NULL,
  w_volume DECIMAL(10,6) NOT NULL,
  w_breakout DECIMAL(10,6) NOT NULL,
  w_risk DECIMAL(10,6) NOT NULL,
  -- fixed execution-risk axes for R2 entry-quality calibration
  stop_atr_mult DECIMAL(10,6) NOT NULL,
  min_rr DECIMAL(10,6) NOT NULL,
  -- fixed grouping counts and calibrated grouping quantile cutoffs
  top_picks_target INT UNSIGNED NOT NULL,
  secondary_target INT UNSIGNED NOT NULL,
  top_min_score_q DECIMAL(10,6) NOT NULL,
  top_max_score_total DECIMAL(10,6) NULL,
  secondary_min_score_q DECIMAL(10,6) NOT NULL,
  notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (param_id),
  UNIQUE KEY UQ_bt_grid_catalog_row (policy_code, catalog_code, row_code),
  KEY IDX_bt_grid_catalog (policy_code, catalog_code, param_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS watchlist_bt_eval (
  eval_id BIGINT NOT NULL AUTO_INCREMENT,
  policy_code VARCHAR(16) NOT NULL,
  catalog_code VARCHAR(64) NOT NULL,
  catalog_version VARCHAR(16) NOT NULL,
  catalog_hash CHAR(40) NOT NULL,
  param_id INT NOT NULL,
  eval_model VARCHAR(96) NOT NULL,
  eval_model_hash CHAR(40) NOT NULL,
  implementation_version VARCHAR(64) NOT NULL,
  implementation_hash CHAR(40) NOT NULL,
  evidence_pipeline_version VARCHAR(64) NOT NULL,
  evidence_pipeline_hash CHAR(40) NOT NULL,
  paramset_hash CHAR(40) NOT NULL,

  -- evaluation window metadata
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  days_covered SMALLINT UNSIGNED NOT NULL,

  -- core top-bucket metrics (used for calibration)
  picks_count INT NOT NULL,
  avg_ret_net_top DECIMAL(10,6) NOT NULL,
  win_rate_top DECIMAL(10,6) NOT NULL,

  -- distribution & downside metrics
  median_ret_net_top DECIMAL(10,6) NOT NULL,
  p25_ret_net_top DECIMAL(10,6) NOT NULL,
  p75_ret_net_top DECIMAL(10,6) NOT NULL,
  min_ret_net_top DECIMAL(10,6) NOT NULL,
  max_ret_net_top DECIMAL(10,6) NOT NULL,

  -- stability metrics
  periods_count TINYINT UNSIGNED NOT NULL,
  period_fail_count TINYINT UNSIGNED NOT NULL,
  month_win_rate_min DECIMAL(10,6) NOT NULL,
  month_avg_ret_net_min DECIMAL(10,6) NOT NULL,

  -- optional extra stats
  avg_ret_net_all DECIMAL(10,6) NULL,
  win_rate_all DECIMAL(10,6) NULL,
  median_ret_net_all DECIMAL(10,6) NULL,
  p25_ret_net_all DECIMAL(10,6) NULL,
  p75_ret_net_all DECIMAL(10,6) NULL,
  min_ret_net_all DECIMAL(10,6) NULL,
  max_ret_net_all DECIMAL(10,6) NULL,

  -- immutable official-evidence manifest identity
  picks_hash CHAR(40) NULL,
  universe_count INT NULL,
  universe_hash CHAR(40) NULL,
  cutoff_count INT NULL,
  cutoffs_hash CHAR(40) NULL,
  evidence_manifest_hash CHAR(40) NULL,
  market_data_lineage_hash CHAR(40) NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (eval_id),
  UNIQUE KEY UQ_bt_eval_catalog_param_window (
    policy_code, catalog_code, catalog_version, param_id,
    eval_model, eval_model_hash, implementation_version, implementation_hash,
    evidence_pipeline_version, evidence_pipeline_hash,
    paramset_hash, from_date, to_date
  ),
  KEY IDX_bt_eval_catalog_rank (
    policy_code, catalog_code, avg_ret_net_top,
    median_ret_net_top, month_win_rate_min, p25_ret_net_top, win_rate_top
  ),
  CONSTRAINT FK_bt_eval_param FOREIGN KEY (param_id) REFERENCES watchlist_bt_param_grid(param_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS watchlist_bt_oos_eval_ws (
  oos_id BIGINT NOT NULL AUTO_INCREMENT,
  policy_code VARCHAR(16) NOT NULL,
  policy_version VARCHAR(32) NOT NULL,
  eval_model VARCHAR(96) NOT NULL,
  param_id_best_is INT NOT NULL,
  is_eval_id BIGINT NOT NULL,

  -- in-sample window (where it was selected)
  from_date_is DATE NOT NULL,
  to_date_is DATE NOT NULL,

  -- out-of-sample window (where it was proven)
  from_date_oos DATE NOT NULL,
  to_date_oos DATE NOT NULL,
  days_covered_oos SMALLINT UNSIGNED NOT NULL,

  -- OOS metrics (same core set as bt_eval top bucket)
  picks_count_oos INT NOT NULL,
  avg_ret_net_top_oos DECIMAL(10,6) NOT NULL,
  win_rate_top_oos DECIMAL(10,6) NOT NULL,
  median_ret_net_top_oos DECIMAL(10,6) NOT NULL,
  p25_ret_net_top_oos DECIMAL(10,6) NOT NULL,
  month_win_rate_min_oos DECIMAL(10,6) NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (oos_id),  
  UNIQUE KEY UQ_bt_oos_policy_param_windows (
    policy_code, policy_version, eval_model, param_id_best_is, is_eval_id,
    from_date_is, to_date_is, from_date_oos, to_date_oos
  ),
  KEY IDX_bt_oos_rank (policy_code, avg_ret_net_top_oos, win_rate_top_oos, median_ret_net_top_oos),
  

  CONSTRAINT FK_bt_oos_param_best_is FOREIGN KEY (param_id_best_is) REFERENCES watchlist_bt_param_grid(param_id),
  CONSTRAINT FK_bt_oos_is_eval FOREIGN KEY (is_eval_id) REFERENCES watchlist_bt_eval(eval_id)
) ENGINE=InnoDB;

-- Picks yang dihasilkan per asof_eod_date dan param_id (untuk audit dan analisis detail)
CREATE TABLE IF NOT EXISTS watchlist_bt_picks_ws (
  pick_id BIGINT NOT NULL AUTO_INCREMENT,
  policy_code VARCHAR(16) NOT NULL,
  param_id INT NOT NULL,
  asof_eod_date DATE NOT NULL,
  ticker_id BIGINT NOT NULL,
  bucket_code VARCHAR(16) NOT NULL, -- TOP_PICKS | SECONDARY
  -- outcome (ret_net) harus sudah include biaya & slippage versi backtest yang disepakati
  ret_net DECIMAL(10,6) NOT NULL,
  pass_guard TINYINT NOT NULL,
  score_total DECIMAL(10,6) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (pick_id),
  KEY IDX_bt_picks_date (policy_code, asof_eod_date, param_id),
  KEY IDX_bt_picks_param_ticker (policy_code, param_id, ticker_id),
  CONSTRAINT FK_bt_picks_param FOREIGN KEY (param_id) REFERENCES watchlist_bt_param_grid(param_id)
) ENGINE=InnoDB;

/* ===================== Weekly Swing Backtest Universe (AUDIT) ===================== */
CREATE TABLE IF NOT EXISTS watchlist_bt_universe_ws (
  asof_eod_date   DATE NOT NULL,
  ticker_id       INT  NOT NULL,

  -- data-quality (required fields available)
  required_ok     TINYINT(1) NOT NULL,
  missing_fields  VARCHAR(255) NULL,

  -- guardrails (evaluated using the active paramset thresholds)
  guard_ok        TINYINT(1) NOT NULL,
  eligible_ok     TINYINT(1) NOT NULL,

  -- metrics snapshot (for equivalence debugging)
  dv20_idr        BIGINT NULL,
  atr14_pct       DECIMAL(10,6) NULL,
  vol_ratio       DECIMAL(20,6) NULL,
  signal_close_price DECIMAL(20,6) NULL,
  signal_tick_risk_expansion_pct DECIMAL(10,6) NULL,

  -- canonical reason for NOT eligible (must follow priority in doc 15)
  reason_code     VARCHAR(32) NULL,

  PRIMARY KEY (asof_eod_date, ticker_id),

  KEY idx_bt_univ_ws_req (asof_eod_date, required_ok),
  KEY idx_bt_univ_ws_elig (asof_eod_date, eligible_ok),
  KEY idx_bt_univ_ws_reason (asof_eod_date, reason_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS watchlist_bt_cutoffs_ws (
  policy_code VARCHAR(16) NOT NULL,
  param_id INT NOT NULL,
  asof_eod_date DATE NOT NULL,

  top_cutoff_score DECIMAL(10,6) NOT NULL,
  secondary_cutoff_score DECIMAL(10,6) NOT NULL,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (policy_code, param_id, asof_eod_date),
  KEY IDX_bt_cutoffs_date (policy_code, asof_eod_date, param_id),
  CONSTRAINT FK_bt_cutoffs_param FOREIGN KEY (param_id) REFERENCES watchlist_bt_param_grid(param_id)
) ENGINE=InnoDB;
/* ===================== C171 Versioned Official IS Evidence ===================== */
-- Implemented by migration:
-- 2026_07_25_000001_version_watchlist_official_backtest_evidence_and_paramset_identity.php
--
-- Identity rules:
-- 1. Every picks/universe/cutoff row belongs to exactly one watchlist_bt_eval.eval_id.
-- 2. watchlist_bt_eval owns the immutable evidence manifest hashes.
-- 3. DRAFT params_hash, IS paramset_hash, OOS paramset_hash, eval_model_hash,
--    implementation_hash, and IS evidence_manifest_hash must be equal before promotion.
-- 4. Legacy support rows may not be assigned an eval_id by inference. Non-empty
--    unversioned tables require an explicit remediation/backfill session.
-- 5. Paramset payload/provenance is immutable. Only DRAFT->ACTIVE and
--    ACTIVE->DEPRECATED status transitions are permitted by the DB guard.
--
-- New watchlist_bt_eval identity columns:
-- eval_model_hash CHAR(40), implementation_version VARCHAR(64),
-- implementation_hash CHAR(40), picks_hash CHAR(40), universe_count INT,
-- universe_hash CHAR(40), cutoff_count INT, cutoffs_hash CHAR(40),
-- evidence_manifest_hash CHAR(40), market_data_lineage_hash CHAR(40).
--
-- C171 C01 evidence-pipeline versioning (migration
-- 2026_07_28_000002_version_c171_tick_risk_evidence_pipeline.php):
-- evidence_pipeline_version VARCHAR(64), evidence_pipeline_hash CHAR(40).
-- These fields version evidence construction independently from the immutable
-- strategy implementation identity. Legacy evals remain immutable under V1;
-- corrected tick-risk propagation reruns use V2 and receive new eval_id values.
--
-- New support-table identity:
-- watchlist_bt_picks_ws.eval_id + row_hash
-- watchlist_bt_universe_ws.eval_id + policy_code + param_id + row_hash
-- watchlist_bt_cutoffs_ws.eval_id + row_hash
-- Composite uniqueness is scoped by eval_id so multiple official evaluations
-- cannot overwrite or share support evidence.
