-- =========================================================
-- Market Data Platform (EOD) — Core MariaDB Schema
-- STRATEGY-CORRECTED TRANSITIONAL DDL; NOT A PRODUCTION-RELOCK CLAIM
-- =========================================================

-- IMPORTANT
-- This file is executed by the 2026-03-22 core migration and therefore preserves the
-- deployable legacy baseline. The current target shape is this baseline plus every later
-- forward migration, especially:
--   2026_08_02_000001_add_market_data_strategy_v2_foundation.php
-- That migration adds immutable observations, full config snapshots, bitemporal identity /
-- calendar / status revisions, corporate-action and factor revisions, actual EOD fields,
-- explicit eligibility facts, and publication lineage bindings. Nullable rollout fields do
-- not satisfy sealing/readability until backfilled and enforced by a later migration.

-- =========================================================
-- Ticker master universe
-- =========================================================

CREATE TABLE IF NOT EXISTS tickers (
  ticker_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticker_code VARCHAR(10) NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  company_logo VARCHAR(255) NULL,
  listed_date DATE NULL,
  delisted_date DATE NULL,
  board_code VARCHAR(10) NULL,
  exchange_code VARCHAR(10) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (ticker_id),
  UNIQUE KEY ticker_code (ticker_code)
) ENGINE=InnoDB;

-- =========================================================
-- Market calendar
-- =========================================================

CREATE TABLE IF NOT EXISTS market_calendar (
  cal_date DATE NOT NULL,
  is_trading_day TINYINT(1) NOT NULL DEFAULT 1,
  holiday_name VARCHAR(120) NULL,
  session_open_time VARCHAR(5) NULL,
  session_close_time VARCHAR(5) NULL,
  breaks_json TEXT NULL,
  source VARCHAR(120) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (cal_date),
  KEY market_calendar_trading_idx (is_trading_day, cal_date)
) ENGINE=InnoDB;

-- =========================================================
-- Sector taxonomy and ticker membership
-- =========================================================

CREATE TABLE IF NOT EXISTS market_data_sectors (
  sector_code VARCHAR(8) NOT NULL,
  sector_name VARCHAR(120) NOT NULL,
  sector_index_code VARCHAR(32) NULL,
  classification_system VARCHAR(32) NOT NULL DEFAULT 'IDX-IC',
  effective_from DATE NOT NULL DEFAULT '2021-01-25',
  effective_to DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  source_name VARCHAR(64) NOT NULL DEFAULT 'idx',
  source_ref VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (sector_code),
  KEY idx_market_data_sectors_system_active_code (classification_system, is_active, sector_code),
  KEY idx_market_data_sectors_index_code (sector_index_code)
) ENGINE=InnoDB;

INSERT INTO market_data_sectors
  (sector_code, sector_name, sector_index_code, classification_system, effective_from, effective_to, is_active, source_name, source_ref, created_at, updated_at)
VALUES
  ('A', 'Energy', 'IDXENERGY', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('B', 'Basic Materials', 'IDXBASIC', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('C', 'Industrials', 'IDXINDUST', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('D', 'Consumer Non-Cyclicals', 'IDXNONCYC', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('E', 'Consumer Cyclicals', 'IDXCYCLIC', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('F', 'Healthcare', 'IDXHEALTH', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('G', 'Financials', 'IDXFINANCE', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('H', 'Properties & Real Estate', 'IDXPROPERT', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('I', 'Technology', 'IDXTECHNO', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('J', 'Infrastructures', 'IDXINFRA', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('K', 'Transportation & Logistic', 'IDXTRANS', 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('Z', 'Listed Investment Product', NULL, 'IDX-IC', '2021-01-25', NULL, 1, 'idx', 'https://www.idx.id/en/products/stocks/', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
  sector_name = VALUES(sector_name),
  sector_index_code = VALUES(sector_index_code),
  classification_system = VALUES(classification_system),
  effective_from = VALUES(effective_from),
  effective_to = VALUES(effective_to),
  is_active = VALUES(is_active),
  source_name = VALUES(source_name),
  source_ref = VALUES(source_ref),
  updated_at = VALUES(updated_at);

CREATE TABLE IF NOT EXISTS ticker_sector_memberships (
  membership_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticker_id BIGINT UNSIGNED NOT NULL,
  -- The four NOT NULL columns below were nullable until 2026_08_10_000001. Two of them compose
  -- uq_sector_membership_listing_effective_known, and MySQL does not treat NULLs as equal in a
  -- unique index, so while they admitted NULL the constraint could be bypassed by omitting them.
  listing_id BIGINT UNSIGNED NOT NULL,
  sector_code VARCHAR(8) NOT NULL,
  classification_system VARCHAR(32) NOT NULL DEFAULT 'IDX-IC',
  effective_from DATE NOT NULL,
  effective_to DATE NULL,
  source_name VARCHAR(64) NOT NULL,
  source_ref VARCHAR(255) NULL,
  source_authority_class VARCHAR(32) NOT NULL,
  recorded_at DATETIME NOT NULL,
  supersedes_membership_id BIGINT UNSIGNED NULL,
  operator_name VARCHAR(128) NULL,
  reason_code VARCHAR(64) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (membership_id),
  UNIQUE KEY uq_sector_membership_listing_effective_known (listing_id, classification_system, effective_from, recorded_at),
  KEY idx_ticker_sector_membership_ticker_date (ticker_id, classification_system, effective_from, effective_to),
  KEY idx_ticker_sector_membership_sector_date (sector_code, classification_system, effective_from),
  KEY idx_sector_membership_listing_effective (listing_id, classification_system, effective_from, effective_to),
  KEY idx_sector_membership_known_authority (recorded_at, source_authority_class),
  KEY idx_sector_membership_supersedes (supersedes_membership_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS market_data_corporate_actions (
  corporate_action_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticker_id BIGINT UNSIGNED NOT NULL,
  ticker_code VARCHAR(16) NOT NULL,
  action_date DATE NOT NULL,
  action_type VARCHAR(64) NOT NULL,
  source_name VARCHAR(64) NOT NULL DEFAULT 'manual_corporate_action_csv',
  source_ref VARCHAR(255) NULL,
  -- As-known coordinate added 2026-08-11 (F-028). Without it EventRiskSourceRepository had
  -- nothing to filter on and every replay saw every row regardless of its knowledge cutoff.
  recorded_at DATETIME NULL,
  notes VARCHAR(255) NULL,
  price_adjustment_factor DECIMAL(20,10) NULL,
  volume_adjustment_factor DECIMAL(20,10) NULL,
  ex_date DATE NULL,
  cum_date DATE NULL,
  ratio_from DECIMAL(20,6) NULL,
  ratio_to DECIMAL(20,6) NULL,
  dividend_per_share DECIMAL(20,4) NULL,
  adjustment_source VARCHAR(32) NULL,
  adjustment_note VARCHAR(255) NULL,
  continuity_check_status VARCHAR(32) NULL,
  observed_gap_pct DECIMAL(12,6) NULL,
  continuity_checked_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (corporate_action_id),
  UNIQUE KEY uq_md_corp_action_ticker_date_type_source (ticker_id, action_date, action_type, source_name),
  KEY idx_md_corp_action_date_ticker (action_date, ticker_id),
  KEY idx_md_corp_action_type_date (action_type, action_date),
  KEY idx_md_corp_action_ex_date (ticker_id, ex_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS market_data_price_scale_breaks (
  price_scale_break_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticker_id BIGINT UNSIGNED NOT NULL,
  ticker_code VARCHAR(16) NOT NULL,
  trade_date DATE NOT NULL,
  previous_close DECIMAL(20,4) NOT NULL,
  open_price DECIMAL(20,4) NOT NULL,
  implied_ratio DECIMAL(20,10) NOT NULL,
  ratio_direction VARCHAR(16) NOT NULL,
  inferred_ratio DECIMAL(12,4) NULL,
  inferred_ratio_error_pct DECIMAL(12,6) NULL,
  break_type VARCHAR(32) NOT NULL,
  match_status VARCHAR(16) NOT NULL,
  matched_corporate_action_id BIGINT UNSIGNED NULL,
  matched_action_type VARCHAR(64) NULL,
  review_status VARCHAR(16) NOT NULL DEFAULT 'DETECTED',
  review_note VARCHAR(255) NULL,
  reviewed_by VARCHAR(64) NULL,
  reviewed_at DATETIME NULL,
  detection_contract_version VARCHAR(64) NOT NULL,
  detected_at DATETIME NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (price_scale_break_id),
  UNIQUE KEY uq_md_price_scale_break_ticker_date (ticker_id, trade_date),
  KEY idx_md_price_scale_break_date_ticker (trade_date, ticker_id),
  KEY idx_md_price_scale_break_status (match_status, review_status),
  KEY idx_md_price_scale_break_type (break_type)
) ENGINE=InnoDB;

-- B11 append-only candidate/review evidence. These rows are diagnostic evidence only and
-- never authorize a corporate action, ex-date, factor, or in-place RAW/history repair.
CREATE TABLE IF NOT EXISTS md_price_scale_break_candidates (
  candidate_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  candidate_uid CHAR(64) NOT NULL,
  listing_id BIGINT UNSIGNED NOT NULL,
  prior_trade_date DATE NOT NULL,
  current_trade_date DATE NOT NULL,
  prior_publication_id BIGINT UNSIGNED NULL,
  current_publication_id BIGINT UNSIGNED NULL,
  prior_source_observation_id BIGINT UNSIGNED NULL,
  current_source_observation_id BIGINT UNSIGNED NULL,
  prior_close DECIMAL(24,8) NOT NULL,
  current_open DECIMAL(24,8) NOT NULL,
  diagnostic_ratio DECIMAL(24,12) NOT NULL,
  ratio_direction VARCHAR(32) NOT NULL,
  inferred_ratio DECIMAL(24,12) NULL,
  inferred_ratio_error_pct DECIMAL(18,8) NULL,
  candidate_classification VARCHAR(64) NOT NULL,
  continuity_verdict VARCHAR(64) NOT NULL,
  market_calendar_adjacent TINYINT(1) NOT NULL DEFAULT 0,
  detector_version VARCHAR(64) NOT NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  linkage_state VARCHAR(64) NOT NULL DEFAULT 'NO_LINKAGE_CANDIDATE',
  possible_corporate_action_revision_id BIGINT UNSIGNED NULL,
  review_state VARCHAR(32) NOT NULL DEFAULT 'DETECTED',
  detected_at DATETIME NOT NULL,
  supersedes_candidate_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (candidate_id),
  UNIQUE KEY uq_md_psbc_uid (candidate_uid),
  KEY idx_md_psbc_listing_date (listing_id, current_trade_date, detected_at),
  KEY idx_md_psbc_review_continuity (review_state, continuity_verdict),
  KEY idx_md_psbc_action_revision (possible_corporate_action_revision_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS md_price_scale_break_candidate_reviews (
  candidate_review_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  candidate_id BIGINT UNSIGNED NOT NULL,
  revision_number INT UNSIGNED NOT NULL,
  review_state VARCHAR(32) NOT NULL,
  evidence_source_observation_id BIGINT UNSIGNED NULL,
  corporate_action_revision_id BIGINT UNSIGNED NULL,
  reviewer VARCHAR(128) NOT NULL,
  review_note TEXT NOT NULL,
  recorded_at DATETIME NOT NULL,
  supersedes_review_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (candidate_review_id),
  UNIQUE KEY uq_md_psbc_review_revision (candidate_id, revision_number),
  KEY idx_md_psbc_review_known (candidate_id, recorded_at),
  KEY idx_md_psbc_review_action_revision (corporate_action_revision_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS md_corporate_action_reconciliations (
  reconciliation_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  reconciliation_uid CHAR(64) NOT NULL,
  scope_start DATE NOT NULL,
  scope_end DATE NOT NULL,
  authority_name VARCHAR(128) NOT NULL,
  authority_class VARCHAR(32) NOT NULL,
  scope_complete TINYINT(1) NOT NULL DEFAULT 0,
  manifest_sha256 CHAR(64) NOT NULL,
  manifest_event_count INT UNSIGNED NOT NULL DEFAULT 0,
  platform_event_count INT UNSIGNED NOT NULL DEFAULT 0,
  missing_platform_count INT UNSIGNED NOT NULL DEFAULT 0,
  unexpected_platform_count INT UNSIGNED NOT NULL DEFAULT 0,
  mismatch_count INT UNSIGNED NOT NULL DEFAULT 0,
  reconciliation_state VARCHAR(48) NOT NULL,
  details_json LONGTEXT NOT NULL,
  recorded_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (reconciliation_id),
  UNIQUE KEY uq_md_action_recon_uid (reconciliation_uid),
  KEY idx_md_action_recon_scope (scope_start, scope_end, authority_class),
  KEY idx_md_action_recon_state (reconciliation_state, recorded_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS market_data_corporate_action_types (
  action_type_code VARCHAR(64) NOT NULL,
  price_continuity_impact VARCHAR(32) NOT NULL,
  volume_continuity_impact VARCHAR(32) NOT NULL,
  share_count_changes TINYINT(1) NOT NULL DEFAULT 0,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (action_type_code),
  KEY idx_md_corp_action_types_price_impact (price_continuity_impact),
  KEY idx_md_corp_action_types_volume_impact (volume_continuity_impact)
) ENGINE=InnoDB;

-- LEGACY/TRANSITIONAL dictionary projection. `expected_bar_policy` is retained for physical
-- compatibility only; under V2 it is NOT authority for coverage expectation. Expected-bar state
-- is resolved separately from point-in-time listing/calendar/full-session status evidence.
CREATE TABLE IF NOT EXISTS market_data_trading_status_event_types (
  event_type_code VARCHAR(64) NOT NULL,
  risk_family VARCHAR(64) NOT NULL,
  transition_type VARCHAR(32) NOT NULL,
  expected_bar_policy VARCHAR(32) NOT NULL,
  carries_forward TINYINT(1) NOT NULL DEFAULT 0,
  clears_risk_family VARCHAR(64) NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (event_type_code),
  KEY idx_md_status_types_family_transition (risk_family, transition_type),
  KEY idx_md_status_types_expected_bar_policy (expected_bar_policy)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS market_data_trading_status_events (
  trading_status_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ticker_id BIGINT UNSIGNED NOT NULL,
  ticker_code VARCHAR(16) NOT NULL,
  trade_date DATE NOT NULL,
  event_type_code VARCHAR(64) NOT NULL,
  source_name VARCHAR(64) NOT NULL DEFAULT 'manual_trading_status_csv',
  source_ref VARCHAR(255) NULL,
  -- As-known coordinate added 2026-08-11 (F-028). Without it EventRiskSourceRepository had
  -- nothing to filter on and every replay saw every row regardless of its knowledge cutoff.
  recorded_at DATETIME NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (trading_status_id),
  UNIQUE KEY uq_md_trading_status_ticker_date_type_source (ticker_id, trade_date, event_type_code, source_name),
  KEY idx_md_trading_status_date_ticker (trade_date, ticker_id),
  KEY idx_md_trading_status_event_type_date (event_type_code, trade_date)
) ENGINE=InnoDB;

-- =========================================================
-- Market benchmark/index master and bars
-- =========================================================

CREATE TABLE IF NOT EXISTS market_benchmarks (
  benchmark_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  benchmark_code VARCHAR(32) NOT NULL,
  benchmark_name VARCHAR(120) NOT NULL,
  provider VARCHAR(64) NOT NULL,
  provider_symbol VARCHAR(64) NOT NULL,
  instrument_type VARCHAR(32) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (benchmark_id),
  UNIQUE KEY uq_market_benchmarks_code (benchmark_code),
  KEY idx_market_benchmarks_provider_symbol (provider, provider_symbol),
  KEY idx_market_benchmarks_active_code (is_active, benchmark_code)
) ENGINE=InnoDB;

INSERT INTO market_benchmarks
  (benchmark_code, benchmark_name, provider, provider_symbol, instrument_type, is_active, created_at, updated_at)
VALUES
  ('IHSG', 'Jakarta Composite Index', 'yahoo_finance', '^JKSE', 'INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXENERGY', 'IDX Sector Energy', 'manual_sector_index_csv', 'IDXENERGY', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXBASIC', 'IDX Sector Basic Materials', 'manual_sector_index_csv', 'IDXBASIC', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXINDUST', 'IDX Sector Industrials', 'manual_sector_index_csv', 'IDXINDUST', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXNONCYC', 'IDX Sector Consumer Non-Cyclicals', 'manual_sector_index_csv', 'IDXNONCYC', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXCYCLIC', 'IDX Sector Consumer Cyclicals', 'manual_sector_index_csv', 'IDXCYCLIC', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXHEALTH', 'IDX Sector Healthcare', 'manual_sector_index_csv', 'IDXHEALTH', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXFINANCE', 'IDX Sector Financials', 'manual_sector_index_csv', 'IDXFINANCE', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXPROPERT', 'IDX Sector Properties & Real Estate', 'manual_sector_index_csv', 'IDXPROPERT', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXTECHNO', 'IDX Sector Technology', 'manual_sector_index_csv', 'IDXTECHNO', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXINFRA', 'IDX Sector Infrastructures', 'manual_sector_index_csv', 'IDXINFRA', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
  ('IDXTRANS', 'IDX Sector Transportation & Logistic', 'manual_sector_index_csv', 'IDXTRANS', 'SECTOR_INDEX', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
  benchmark_name = VALUES(benchmark_name),
  provider = VALUES(provider),
  provider_symbol = VALUES(provider_symbol),
  instrument_type = VALUES(instrument_type),
  is_active = VALUES(is_active),
  updated_at = VALUES(updated_at);

CREATE TABLE IF NOT EXISTS market_benchmark_bars (
  benchmark_bar_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  benchmark_code VARCHAR(32) NOT NULL,
  trade_date DATE NOT NULL,
  open_price DECIMAL(20,4) NOT NULL,
  high_price DECIMAL(20,4) NOT NULL,
  low_price DECIMAL(20,4) NOT NULL,
  close_price DECIMAL(20,4) NOT NULL,
  adjusted_close DECIMAL(20,4) NULL,
  volume BIGINT NULL,
  provider VARCHAR(64) NOT NULL,
  provider_symbol VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (benchmark_bar_id),
  UNIQUE KEY uq_market_benchmark_bars_code_date (benchmark_code, trade_date),
  KEY idx_market_benchmark_bars_code_date (benchmark_code, trade_date),
  KEY idx_market_benchmark_bars_provider_symbol (provider, provider_symbol)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS market_benchmark_indicators (
  benchmark_indicator_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  benchmark_code VARCHAR(32) NOT NULL,
  trade_date DATE NOT NULL,
  roc_20 DECIMAL(20,10) NULL,
  ma20 DECIMAL(20,4) NULL,
  ma50 DECIMAL(20,4) NULL,
  ma20_slope_pct DECIMAL(20,10) NULL,
  close_to_ma20_pct DECIMAL(20,10) NULL,
  close_to_ma50_pct DECIMAL(20,10) NULL,
  is_valid TINYINT(1) NOT NULL DEFAULT 0,
  invalid_reason_code VARCHAR(64) NULL,
  indicator_set_version VARCHAR(64) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (benchmark_indicator_id),
  UNIQUE KEY uq_market_benchmark_indicators_code_date_version (benchmark_code, trade_date, indicator_set_version),
  KEY idx_market_benchmark_indicators_code_date (benchmark_code, trade_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS eod_reason_codes (
  code VARCHAR(64) NOT NULL,
  category VARCHAR(32) NOT NULL,
  description VARCHAR(255) NOT NULL,
  severity ENUM('INFO','WARN','HARD') NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (code),
  KEY idx_reason_codes_category (category),
  KEY idx_reason_codes_active (is_active)
) ENGINE=InnoDB;

-- =========================================================
-- Legacy current canonical-bars projection — NOT historical/source-observation authority
-- Physical ticker_id key is transitional; V2 target identity is stable listing_id.
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_bars (
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  open DECIMAL(20,4) NOT NULL,
  high DECIMAL(20,4) NOT NULL,
  low DECIMAL(20,4) NOT NULL,
  close DECIMAL(20,4) NOT NULL,
  volume BIGINT NOT NULL,
  adj_close DECIMAL(20,4) NULL,
  source VARCHAR(32) NOT NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  publication_id BIGINT UNSIGNED NOT NULL,
  listing_id BIGINT UNSIGNED NULL,
  source_observation_id BIGINT UNSIGNED NULL,
  previous_close DECIMAL(20,4) NULL,
  traded_value_idr_actual DECIMAL(24,2) NULL,
  trade_count_actual BIGINT UNSIGNED NULL,
  board_code VARCHAR(16) NULL,
  session_code VARCHAR(32) NULL,
  source_timestamp DATETIME NULL,
  acquired_at DATETIME NULL,
  canonicalization_version VARCHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  quality_state VARCHAR(32) NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  source_scale_state VARCHAR(32) NULL,
  source_scale_assessment_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (trade_date, ticker_id),
  KEY idx_eod_bars_ticker_date (ticker_id, trade_date),
  KEY idx_eod_bars_run (run_id),
  KEY idx_eod_bars_publication (publication_id),
  KEY idx_eod_bars_publication_date_ticker (publication_id, trade_date, ticker_id)
) ENGINE=InnoDB;

-- LOCKED NOTE
-- eod_bars is a replaceable current projection for a given trade_date.
-- publication_id is mandatory publication context on every live current row,
-- but it is not part of the live-table primary key.
-- It is never historical authority. Historical authority is the immutable observation and
-- publication-bound snapshot lineage introduced by the base/history tables and V2 migration.

-- =========================================================
-- Invalid/rejected source-row evidence
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_invalid_bars (
  invalid_bar_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NULL,
  listing_id BIGINT UNSIGNED NULL,
  source_observation_id BIGINT UNSIGNED NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  source VARCHAR(32) NOT NULL,
  source_row_ref VARCHAR(255) NULL,
  open DECIMAL(20,4) NULL,
  high DECIMAL(20,4) NULL,
  low DECIMAL(20,4) NULL,
  close DECIMAL(20,4) NULL,
  volume BIGINT NULL,
  adj_close DECIMAL(20,4) NULL,
  invalid_reason_code VARCHAR(64) NOT NULL,
  invalid_note VARCHAR(255) NULL,
  loser_of_trade_date DATE NULL,
  loser_of_ticker_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (invalid_bar_id),
  KEY idx_invalid_bars_trade_date_ticker (trade_date, ticker_id),
  KEY idx_invalid_bars_run (run_id),
  KEY idx_invalid_bars_reason_code (invalid_reason_code),
  KEY idx_invalid_bars_source_row_ref (source_row_ref),
  KEY idx_invalid_bars_duplicate_loser (loser_of_trade_date, loser_of_ticker_id)
) ENGINE=InnoDB;

-- =========================================================
-- Indicator artifact
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_indicators (
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  is_valid TINYINT(1) NOT NULL,
  invalid_reason_code VARCHAR(64) NULL,
  indicator_set_version VARCHAR(64) NOT NULL,
  sector_code VARCHAR(8) NULL,
  dv20_idr DECIMAL(24,2) NULL,
  atr14_pct DECIMAL(20,10) NULL,
  vol_ratio DECIMAL(20,10) NULL,
  roc5 DECIMAL(20,10) NULL,
  roc10 DECIMAL(20,10) NULL,
  roc20 DECIMAL(20,10) NULL,
  hh20 DECIMAL(20,4) NULL,
  ll20 DECIMAL(20,4) NULL,
  ma20 DECIMAL(20,4) NULL,
  ma50 DECIMAL(20,4) NULL,
  close_to_hh20_pct DECIMAL(20,10) NULL,
  close_to_ll20_pct DECIMAL(20,10) NULL,
  range_20_pct DECIMAL(20,10) NULL,
  range_position_20_pct DECIMAL(20,10) NULL,
  close_vs_ma20_pct DECIMAL(20,10) NULL,
  close_vs_ma50_pct DECIMAL(20,10) NULL,
  ma20_slope_pct DECIMAL(20,10) NULL,
  rs_20_vs_ihsg DECIMAL(20,10) NULL,
  sector_roc20 DECIMAL(20,10) NULL,
  rs_20_vs_sector DECIMAL(20,10) NULL,
  sector_rs_20_vs_ihsg DECIMAL(20,10) NULL,
  corporate_action_flag TINYINT(1) NULL,
  corporate_action_types VARCHAR(255) NULL,
  trading_status_code VARCHAR(64) NULL,
  is_suspended TINYINT(1) NULL,
  is_uma TINYINT(1) NULL,
  event_risk_flag TINYINT(1) NULL,
  event_risk_reasons VARCHAR(255) NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  publication_id BIGINT UNSIGNED NOT NULL,
  corporate_action_window_reasons VARCHAR(255) NULL,
  listing_id BIGINT UNSIGNED NULL,
  formula_version VARCHAR(64) NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  factor_set_id BIGINT UNSIGNED NULL,
  factor_set_hash CHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  price_product_version VARCHAR(64) NULL,
  liquidity_formula_version VARCHAR(64) NULL,
  sector_membership_id BIGINT UNSIGNED NULL,
  adv20_traded_value_idr_actual DECIMAL(24,2) NULL,
  adv20_close_volume_proxy_idr DECIMAL(24,2) NULL,
  atr14 DECIMAL(20,10) NULL,
  atr_state_ref VARCHAR(128) NULL,
  null_reasons_json TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (trade_date, ticker_id),
  KEY idx_eod_indicators_ticker_date (ticker_id, trade_date),
  KEY idx_eod_indicators_run (run_id),
  KEY idx_eod_indicators_invalid_reason (invalid_reason_code),
  KEY idx_eod_indicators_publication (publication_id),
  KEY idx_eod_indicators_publication_date_ticker (publication_id, trade_date, ticker_id),
  KEY idx_eod_indicators_sector_date (sector_code, trade_date),
  KEY idx_eod_indicators_event_risk_date (event_risk_flag, trade_date)
) ENGINE=InnoDB;

-- TRANSITIONAL NOTE
-- eod_indicators is a legacy current projection. Its physical (trade_date,ticker_id) identity is
-- not the V2 target; stable listing_id plus publication/config/formula/factor/price-product lineage
-- governs semantics. Technical indicators must use the run-wide selected STRUCTURAL_ADJUSTED
-- product; presence of RAW close/adj_close fields does not authorize an alternate basis.

-- =========================================================
-- Eligibility artifact
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_eligibility (
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  eligible TINYINT(1) NOT NULL,
  reason_code VARCHAR(64) NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  publication_id BIGINT UNSIGNED NOT NULL,
  listing_id BIGINT UNSIGNED NULL,
  universe_membership_state VARCHAR(32) NULL,
  bar_expectation_state VARCHAR(32) NULL,
  delivery_state VARCHAR(32) NULL,
  canonical_quality_state VARCHAR(32) NULL,
  liquidity_state VARCHAR(32) NULL,
  temporal_status_state VARCHAR(32) NULL,
  trading_status_revision_id BIGINT UNSIGNED NULL,
  trading_status_source_observation_id BIGINT UNSIGNED NULL,
  event_risk_state VARCHAR(32) NULL,
  source_provenance_state VARCHAR(32) NULL,
  price_basis_state VARCHAR(32) NULL,
  contamination_state VARCHAR(32) NULL,
  indicator_state VARCHAR(32) NULL,
  eligibility_reasons_json TEXT NULL,
  market_structure_resolution_state VARCHAR(48) NULL,
  price_band_revision_id BIGINT UNSIGNED NULL,
  minimum_price_revision_id BIGINT UNSIGNED NULL,
  tick_size_revision_id BIGINT UNSIGNED NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (trade_date, ticker_id),
  KEY idx_eod_eligibility_ticker_date (ticker_id, trade_date),
  KEY idx_eod_eligibility_run (run_id),
  KEY idx_eod_eligibility_reason (reason_code),
  KEY idx_eod_eligibility_publication (publication_id),
  KEY idx_eod_eligibility_publication_date_ticker (publication_id, trade_date, ticker_id)
) ENGINE=InnoDB;

-- TRANSITIONAL NOTE
-- eod_eligibility is a compatibility projection of publication-bound data-usability/factual states.
-- `eligible` is not ranking, alpha, tradability approval, or watchlist policy. Physical ticker_id
-- identity is legacy; the V2 target binds stable listing_id and explicit publication/config context.

-- DB INTEGRITY FK / IMPLICIT INTEGRITY DECISION - LOCKED POLICY
-- Final policy: HYBRID_REQUIRED.
-- Current live artifact tables intentionally keep mandatory run_id/publication_id columns
-- plus publication-scoped indexes, but do not add physical FK constraints to
-- publication/run/ticker in this session because import/promote/correction/replay lifecycles
-- are phase-dependent and validated by repository/service/static/integration guards.
-- Stable immutable proof tables keep explicit publication FK constraints:
-- eod_bars_history, eod_indicators_history, and eod_eligibility_history.
-- Current pointer keeps explicit FK to eod_publications(publication_id), while pointer
-- run/version/readability/coverage mirror checks remain implicit guard invariants.
-- Audit scope note: this decision does not mean the whole schema sync failed; it only
-- closes the live artifact relation risk with an explicit FK-vs-implicit policy.

-- =========================================================
-- Runs with separated state model
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_runs (
  run_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date_requested DATE NOT NULL,
  trade_date_effective DATE NULL,

  lifecycle_state ENUM('PENDING','RUNNING','FINALIZING','COMPLETED','FAILED','CANCELLED') NOT NULL,
  terminal_status ENUM('SUCCESS','HELD','FAILED') NULL,
  quality_gate_state ENUM('PENDING','PASS','FAIL','BLOCKED') NOT NULL DEFAULT 'PENDING',
  publishability_state ENUM('NOT_READABLE','READABLE') NOT NULL DEFAULT 'NOT_READABLE',

  stage ENUM('INGEST_BARS','PUBLISH_BARS','COMPUTE_INDICATORS','BUILD_ELIGIBILITY','HASH','SEAL','FINALIZE') NOT NULL,
  source VARCHAR(32) NOT NULL,
  request_mode VARCHAR(32) NULL,
  source_name VARCHAR(64) NULL,
  source_provider VARCHAR(64) NULL,
  source_input_file VARCHAR(255) NULL,
  source_timeout_seconds INT NULL,
  source_retry_max INT NULL,
  source_attempt_count INT NULL,
  source_success_after_retry TINYINT(1) NULL,
  source_retry_exhausted TINYINT(1) NULL,
  source_final_http_status INT NULL,
  source_final_reason_code VARCHAR(64) NULL,
  source_file_hash VARCHAR(64) NULL,
  source_file_hash_algorithm VARCHAR(32) NULL,
  source_file_size_bytes BIGINT UNSIGNED NULL,
  source_file_row_count INT UNSIGNED NULL,

  coverage_universe_count INT NULL,
  coverage_universe_hash CHAR(64) NULL,
  knowledge_cutoff_at DATETIME NULL,
  coverage_available_count INT NULL,
  coverage_missing_count INT NULL,
  coverage_bar_not_expected_count INT NULL,
  coverage_ratio DECIMAL(12,6) NULL,
  coverage_min_threshold DECIMAL(12,6) NULL,
  coverage_gate_state ENUM('PASS','FAIL','NOT_EVALUABLE') NULL,
  coverage_threshold_mode VARCHAR(32) NULL,
  coverage_universe_basis VARCHAR(64) NULL,
  coverage_contract_version VARCHAR(64) NULL,
  coverage_missing_sample_json JSON NULL,
  coverage_excluded_sample_json TEXT NULL,
  bars_rows_written INT NULL,
  indicators_rows_written INT NULL,
  eligibility_rows_written INT NULL,

  invalid_bar_count INT NULL,
  invalid_indicator_count INT NULL,
  hard_reject_count INT NULL,
  warning_count INT NULL,

  notes TEXT NULL,

  bars_batch_hash VARCHAR(64) NULL,
  indicators_batch_hash VARCHAR(64) NULL,
  eligibility_batch_hash VARCHAR(64) NULL,

  config_version VARCHAR(64) NULL,
  config_hash VARCHAR(64) NULL,
  config_snapshot_ref VARCHAR(255) NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  observation_manifest_hash VARCHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  price_product_version VARCHAR(64) NULL,
  factor_set_hash CHAR(64) NULL,
  coverage_expected_count INT NULL,
  coverage_expectation_unknown_count INT NULL,
  coverage_delivered_count INT NULL,
  coverage_delivered_valid_count INT NULL,
  operational_start_date DATE NULL,
  freshness_state VARCHAR(32) NULL,
  latest_expected_trade_date DATE NULL,
  latest_acquired_trade_date DATE NULL,
  latest_canonicalized_trade_date DATE NULL,
  latest_readable_trade_date DATE NULL,

  supersedes_run_id BIGINT UNSIGNED NULL,
  publication_id BIGINT UNSIGNED NULL,
  publication_version INT UNSIGNED NULL,
  is_current_publication TINYINT(1) NOT NULL DEFAULT 0,
  correction_id BIGINT UNSIGNED NULL,
  promote_mode VARCHAR(32) NULL,
  publish_target VARCHAR(64) NULL,
  final_reason_code VARCHAR(64) NULL,

  sealed_at DATETIME NULL,
  sealed_by VARCHAR(64) NULL,
  seal_note VARCHAR(255) NULL,

  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,

  PRIMARY KEY (run_id),
  KEY idx_runs_requested_lifecycle (trade_date_requested, lifecycle_state),
  KEY idx_runs_requested_terminal (trade_date_requested, terminal_status),
  KEY idx_runs_effective_terminal (trade_date_effective, terminal_status),
  KEY idx_runs_effective_publishability (trade_date_effective, publishability_state),
  KEY idx_runs_gate_state (quality_gate_state),
  KEY idx_runs_coverage_gate_state (coverage_gate_state),
  KEY idx_runs_stage (stage),
  KEY idx_runs_request_mode (request_mode),
  KEY idx_runs_trade_date_current_pub (trade_date_effective, is_current_publication),
  KEY idx_runs_effective_readable_contract (trade_date_effective, terminal_status, publishability_state, coverage_gate_state, is_current_publication),
  KEY idx_runs_supersedes (supersedes_run_id),
  KEY idx_runs_publication_id (publication_id),
  KEY idx_runs_correction_id (correction_id),
  KEY idx_runs_promote_mode (promote_mode),
  KEY idx_runs_publish_target (publish_target),
  KEY idx_runs_final_reason_code (final_reason_code),
  KEY idx_runs_source_name (source_name),
  KEY idx_runs_source_file_hash (source_file_hash),
  KEY idx_runs_source_identity (source, source_name, source_provider, source_file_hash)
) ENGINE=InnoDB;

-- LOCKED SEMANTICS
-- 1. lifecycle_state tracks execution progression, not publish/readability outcome.
-- 2. RUNNING means an active process is currently executing; import-only completion must close as COMPLETED/NOT_READABLE.
-- 3. terminal_status tracks consumer-facing terminal outcome.
-- 4. quality_gate_state tracks gate evaluation.
-- 5. publishability_state tracks readability.
-- 6. These meanings must remain distinct; do not collapse them back into one overloaded status.
-- 7. terminal_status may remain NULL until finalization resolves outcome.
-- 8. publishability_state='READABLE' must never coexist with missing required seal/publication semantics.
-- 9. knowledge_cutoff_at is captured when a new run is created; legacy NULL preserves an unbounded historical claim, must never be lazily restamped, and cannot resume execution on the same run identity.
-- 10. coverage_universe_count is the raw temporal universe; coverage_expected_count is the denominator.
-- 11. Every stored coverage_* field is exported under its own field name before compatibility aliases are added.

-- =========================================================
-- Append-only run event trail
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_run_events (
  event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  run_id BIGINT UNSIGNED NOT NULL,
  trade_date_requested DATE NOT NULL,
  event_time DATETIME NOT NULL,
  stage VARCHAR(64) NOT NULL,
  event_type VARCHAR(64) NOT NULL,
  severity ENUM('INFO','WARN','ERROR') NOT NULL,
  reason_code VARCHAR(64) NULL,
  message VARCHAR(255) NULL,
  event_payload_json LONGTEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (event_id),
  KEY idx_run_events_run_time (run_id, event_time),
  KEY idx_run_events_trade_date_time (trade_date_requested, event_time),
  KEY idx_run_events_stage_time (stage, event_time),
  KEY idx_run_events_reason_code (reason_code),
  KEY idx_run_events_severity_time (severity, event_time)
) ENGINE=InnoDB;

-- =========================================================
-- Explicit publication table
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_publications (
  publication_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date DATE NOT NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  publication_version INT UNSIGNED NOT NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  supersedes_publication_id BIGINT UNSIGNED NULL,
  previous_publication_id BIGINT UNSIGNED NULL,
  replaced_publication_id BIGINT UNSIGNED NULL,
  seal_state ENUM('SEALED','UNSEALED') NOT NULL DEFAULT 'UNSEALED',
  bars_batch_hash VARCHAR(64) NULL,
  indicators_batch_hash VARCHAR(64) NULL,
  eligibility_batch_hash VARCHAR(64) NULL,
  source_file_hash VARCHAR(64) NULL,
  source_file_hash_algorithm VARCHAR(32) NULL,
  source_file_size_bytes BIGINT UNSIGNED NULL,
  source_file_row_count INT UNSIGNED NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  factor_set_id BIGINT UNSIGNED NULL,
  factor_set_hash CHAR(64) NULL,
  observation_manifest_hash VARCHAR(64) NULL,
  -- What the seal covers (F-033, 2026-08-11). FULL = config identity and acquisition
  -- provenance both. ANALYTICAL_ONLY = the run recomputed analytics over existing bars and
  -- had no acquisition manifest to carry forward, so the seal is real but narrower and says
  -- so here instead of in a check that was quietly skipped.
  seal_provenance_scope VARCHAR(32) NULL,
  publication_manifest_hash VARCHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  price_product_version VARCHAR(64) NULL,
  read_model_version VARCHAR(64) NULL,
  readiness_state VARCHAR(32) NULL,
  source_scale_assessment_set_hash CHAR(64) NULL,
  market_structure_revision_set_hash CHAR(64) NULL,
  factor_decision_set_hash CHAR(64) NULL,
  sealed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (publication_id),
  UNIQUE KEY uq_publication_trade_date_version (trade_date, publication_version),
  KEY idx_publication_trade_date_current (trade_date, is_current),
  KEY idx_publication_readable_lookup (trade_date, is_current, seal_state, publication_version, run_id),
  KEY idx_publication_run (run_id),
  KEY idx_publication_run_trade_date (run_id, trade_date, publication_id),
  KEY idx_publication_supersedes (supersedes_publication_id),
  KEY idx_publication_previous (previous_publication_id),
  KEY idx_publication_replaced (replaced_publication_id),
  KEY idx_publication_source_file_hash (source_file_hash),
  KEY idx_publication_trade_date_sealed (trade_date, seal_state, sealed_at)
) ENGINE=InnoDB;

-- IMPORTANT NOTE
-- MariaDB cannot enforce "only one row with is_current=1 per trade_date"
-- as a partial unique index in the same way some other databases can.
-- Therefore the single-current-publication invariant must be enforced by
-- application transaction discipline or locked publication-switch procedure flow.

-- =========================================================
-- Hardened current-publication pointer
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_current_publication_pointer (
  trade_date DATE NOT NULL,
  publication_id BIGINT UNSIGNED NOT NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  publication_version INT UNSIGNED NOT NULL,
  sealed_at DATETIME NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (trade_date),
  UNIQUE KEY uq_current_publication_pointer_publication (publication_id),
  KEY idx_current_publication_pointer_run (run_id),
  KEY idx_current_publication_pointer_run_version (run_id, publication_version),
  CONSTRAINT fk_current_publication_pointer_publication
    FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)
) ENGINE=InnoDB;

-- LOCKED SEMANTICS
-- 1. This table is the hardened DB-facing pointer for "one current publication per trade_date".
-- 2. trade_date as PK guarantees at most one pointer row per trade_date.
-- 3. publication_id uniqueness guarantees one publication cannot be current for multiple trade dates.
-- 4. Consumer-readable current publication resolution must prefer this pointer table where implemented.
-- 5. eod_publications history and eod_runs state remain required supporting evidence; the pointer does not replace them.
-- 6. Runtime integrity is enforced by the pointer primary key, publication unique version,
--    publication/run mirror checks, and repository guards. Physical FK coverage is intentionally
--    selective because correction/evidence/replay lifecycle rows may be created before all
--    terminal linkage fields are known; any non-FK relation must have an implicit guard test.

-- =========================================================
-- Correction request table
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_dataset_corrections (
  correction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date DATE NOT NULL,
  prior_run_id BIGINT UNSIGNED NULL,
  new_run_id BIGINT UNSIGNED NULL,
  baseline_publication_id BIGINT UNSIGNED NULL,
  replacement_publication_id BIGINT UNSIGNED NULL,
  correction_reason_code VARCHAR(64) NOT NULL,
  correction_reason_note TEXT NULL,
  status ENUM('REQUESTED','APPROVED','EXECUTING','RESEALED','REPAIR_ACTIVE','REPAIR_EXECUTED','REPAIR_CANDIDATE','CONSUMED_CURRENT','PUBLISHED','FAILED','REJECTED','CANCELLED','CLOSED') NOT NULL,
  requested_by VARCHAR(64) NOT NULL,
  requested_at DATETIME NOT NULL,
  approved_by VARCHAR(64) NULL,
  approved_at DATETIME NULL,
  published_at DATETIME NULL,
  execution_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_executed_at DATETIME NULL,
  current_consumed_at DATETIME NULL,
  final_outcome_note TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (correction_id),
  KEY idx_corr_trade_date_status (trade_date, status),
  KEY idx_corr_trade_date_status_execution (trade_date, status, execution_count),
  KEY idx_corr_prior_run (prior_run_id),
  KEY idx_corr_new_run (new_run_id),
  KEY idx_corr_prior_new_run (prior_run_id, new_run_id),
  KEY idx_corr_baseline_publication (baseline_publication_id),
  KEY idx_corr_replacement_publication (replacement_publication_id),
  KEY idx_corr_baseline_replacement_publication (baseline_publication_id, replacement_publication_id)
) ENGINE=InnoDB;

-- =========================================================
-- Publication-bound history tables — LEGACY/TRANSITIONAL PHYSICAL SHAPE
-- NOT THE V2 IDENTITY TARGET: the deployable baseline below still uses ticker_id in physical
-- primary keys for compatibility. V2 canonical business identity is stable listing_id (and
-- instrument_id where applicable); relock requires forward migration/backfill/enforcement so
-- publication-bound uniqueness is listing_id-based. Do not infer strategy authority from these
-- legacy CREATE statements.
-- =========================================================

CREATE TABLE IF NOT EXISTS eod_bars_history (
  publication_id BIGINT UNSIGNED NOT NULL,
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  open DECIMAL(20,4) NOT NULL,
  high DECIMAL(20,4) NOT NULL,
  low DECIMAL(20,4) NOT NULL,
  close DECIMAL(20,4) NOT NULL,
  volume BIGINT NOT NULL,
  adj_close DECIMAL(20,4) NULL,
  source VARCHAR(32) NOT NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  listing_id BIGINT UNSIGNED NULL,
  source_observation_id BIGINT UNSIGNED NULL,
  previous_close DECIMAL(20,4) NULL,
  traded_value_idr_actual DECIMAL(24,2) NULL,
  trade_count_actual BIGINT UNSIGNED NULL,
  board_code VARCHAR(16) NULL,
  session_code VARCHAR(32) NULL,
  source_timestamp DATETIME NULL,
  acquired_at DATETIME NULL,
  canonicalization_version VARCHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  quality_state VARCHAR(32) NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  source_scale_state VARCHAR(32) NULL,
  source_scale_assessment_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (publication_id, trade_date, ticker_id),
  KEY idx_bars_history_trade_date (trade_date),
  KEY idx_bars_history_ticker_date (ticker_id, trade_date),
  KEY idx_bars_history_run (run_id),
  CONSTRAINT fk_bars_history_publication
    FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS eod_indicators_history (
  publication_id BIGINT UNSIGNED NOT NULL,
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  is_valid TINYINT(1) NOT NULL,
  invalid_reason_code VARCHAR(64) NULL,
  indicator_set_version VARCHAR(64) NOT NULL,
  sector_code VARCHAR(8) NULL,
  dv20_idr DECIMAL(24,2) NULL,
  atr14_pct DECIMAL(20,10) NULL,
  vol_ratio DECIMAL(20,10) NULL,
  roc5 DECIMAL(20,10) NULL,
  roc10 DECIMAL(20,10) NULL,
  roc20 DECIMAL(20,10) NULL,
  hh20 DECIMAL(20,4) NULL,
  ll20 DECIMAL(20,4) NULL,
  ma20 DECIMAL(20,4) NULL,
  ma50 DECIMAL(20,4) NULL,
  close_to_hh20_pct DECIMAL(20,10) NULL,
  close_to_ll20_pct DECIMAL(20,10) NULL,
  range_20_pct DECIMAL(20,10) NULL,
  range_position_20_pct DECIMAL(20,10) NULL,
  close_vs_ma20_pct DECIMAL(20,10) NULL,
  close_vs_ma50_pct DECIMAL(20,10) NULL,
  ma20_slope_pct DECIMAL(20,10) NULL,
  rs_20_vs_ihsg DECIMAL(20,10) NULL,
  sector_roc20 DECIMAL(20,10) NULL,
  rs_20_vs_sector DECIMAL(20,10) NULL,
  sector_rs_20_vs_ihsg DECIMAL(20,10) NULL,
  corporate_action_flag TINYINT(1) NULL,
  corporate_action_types VARCHAR(255) NULL,
  trading_status_code VARCHAR(64) NULL,
  is_suspended TINYINT(1) NULL,
  is_uma TINYINT(1) NULL,
  event_risk_flag TINYINT(1) NULL,
  event_risk_reasons VARCHAR(255) NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  corporate_action_window_reasons VARCHAR(255) NULL,
  listing_id BIGINT UNSIGNED NULL,
  formula_version VARCHAR(64) NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  factor_set_id BIGINT UNSIGNED NULL,
  factor_set_hash CHAR(64) NULL,
  price_product_code VARCHAR(32) NULL,
  price_product_version VARCHAR(64) NULL,
  liquidity_formula_version VARCHAR(64) NULL,
  sector_membership_id BIGINT UNSIGNED NULL,
  adv20_traded_value_idr_actual DECIMAL(24,2) NULL,
  adv20_close_volume_proxy_idr DECIMAL(24,2) NULL,
  atr14 DECIMAL(20,10) NULL,
  atr_state_ref VARCHAR(128) NULL,
  null_reasons_json TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (publication_id, trade_date, ticker_id),
  KEY idx_indicators_history_trade_date (trade_date),
  KEY idx_indicators_history_ticker_date (ticker_id, trade_date),
  KEY idx_indicators_history_run (run_id),
  KEY idx_eod_indicators_history_sector_date (sector_code, trade_date),
  KEY idx_eod_indicators_history_event_risk_date (event_risk_flag, trade_date),
  CONSTRAINT fk_indicators_history_publication
    FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS eod_eligibility_history (
  publication_id BIGINT UNSIGNED NOT NULL,
  trade_date DATE NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  eligible TINYINT(1) NOT NULL,
  reason_code VARCHAR(64) NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  listing_id BIGINT UNSIGNED NULL,
  universe_membership_state VARCHAR(32) NULL,
  bar_expectation_state VARCHAR(32) NULL,
  delivery_state VARCHAR(32) NULL,
  canonical_quality_state VARCHAR(32) NULL,
  liquidity_state VARCHAR(32) NULL,
  temporal_status_state VARCHAR(32) NULL,
  trading_status_revision_id BIGINT UNSIGNED NULL,
  trading_status_source_observation_id BIGINT UNSIGNED NULL,
  event_risk_state VARCHAR(32) NULL,
  source_provenance_state VARCHAR(32) NULL,
  price_basis_state VARCHAR(32) NULL,
  contamination_state VARCHAR(32) NULL,
  indicator_state VARCHAR(32) NULL,
  eligibility_reasons_json TEXT NULL,
  market_structure_resolution_state VARCHAR(48) NULL,
  price_band_revision_id BIGINT UNSIGNED NULL,
  minimum_price_revision_id BIGINT UNSIGNED NULL,
  tick_size_revision_id BIGINT UNSIGNED NULL,
  config_snapshot_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (publication_id, trade_date, ticker_id),
  KEY idx_eligibility_history_trade_date (trade_date),
  KEY idx_eligibility_history_ticker_date (ticker_id, trade_date),
  KEY idx_eligibility_history_run (run_id),
  CONSTRAINT fk_eligibility_history_publication
    FOREIGN KEY (publication_id) REFERENCES eod_publications(publication_id)
) ENGINE=InnoDB;

-- LOCKED HISTORY NOTE
-- 1. Strategy A is the default production-grade row-history strategy.
-- 2. The *_history tables are immutable publication-bound snapshots.
-- 3. Each snapshot row set belongs to exactly one publication_id.
-- 4. Snapshot rows must be written only for a sealed publication.
-- 5. Snapshot rows must never be updated or deleted in normal operation.

-- =========================================================
-- Intraday/session snapshot storage
-- =========================================================

CREATE TABLE IF NOT EXISTS md_session_snapshots (
  snapshot_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date DATE NOT NULL,
  snapshot_slot VARCHAR(32) NOT NULL,
  ticker_id BIGINT UNSIGNED NOT NULL,
  captured_at DATETIME NOT NULL,
  last_price DECIMAL(18,4) NULL,
  prev_close DECIMAL(18,4) NULL,
  chg_pct DECIMAL(18,10) NULL,
  volume BIGINT UNSIGNED NULL,
  day_high DECIMAL(18,4) NULL,
  day_low DECIMAL(18,4) NULL,
  source VARCHAR(32) NOT NULL,
  run_id BIGINT UNSIGNED NULL,
  reason_code VARCHAR(64) NULL,
  error_note VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  PRIMARY KEY (snapshot_id),
  UNIQUE KEY md_session_snapshots_trade_date_snapshot_slot_ticker_id_unique (trade_date, snapshot_slot, ticker_id),
  KEY md_session_snapshots_trade_date_snapshot_slot_index (trade_date, snapshot_slot),
  KEY md_session_snapshots_captured_at_index (captured_at)
) ENGINE=InnoDB;

-- =========================================================
-- Replay result storage
-- =========================================================

CREATE TABLE IF NOT EXISTS md_replay_daily_metrics (
  replay_id BIGINT UNSIGNED NOT NULL,
  trade_date DATE NOT NULL,
  trade_date_effective DATE NULL,
  replay_suite VARCHAR(128) NULL,
  replay_case VARCHAR(128) NULL,
  fixture_id VARCHAR(128) NULL,
  fixture_version VARCHAR(64) NULL,
  fixture_schema_version VARCHAR(64) NULL,
  fixture_source VARCHAR(128) NULL,
  fixture_created_at VARCHAR(64) NULL,
  source VARCHAR(32) NOT NULL,
  source_mode VARCHAR(32) NULL,
  source_name VARCHAR(64) NULL,
  source_provider VARCHAR(64) NULL,
  source_timeout_seconds INT NULL,
  source_retry_max INT NULL,
  source_attempt_count INT NULL,
  source_success_after_retry TINYINT(1) NULL,
  source_retry_exhausted TINYINT(1) NULL,
  source_final_http_status INT NULL,
  source_final_reason_code VARCHAR(64) NULL,
  source_input_file VARCHAR(255) NULL,
  status ENUM('SUCCESS','HELD','FAILED') NOT NULL,
  publishability_state ENUM('READABLE','NOT_READABLE') NULL,
  publication_id BIGINT UNSIGNED NULL,
  publication_run_id BIGINT UNSIGNED NULL,
  -- NOT_ADMISSIBLE added 2026-08-11 (F-025). W18 gave the verifier a fourth answer — the fixture's
  -- expectation was derived from the run under verification — and without it in the enum every
  -- inadmissible replay died on insert instead of being recorded.
  comparison_result ENUM('MATCH','MISMATCH','EXPECTED_DEGRADE','UNEXPECTED','NOT_ADMISSIBLE') NOT NULL,
  replay_status ENUM('PASS','FAIL','BLOCKED') NOT NULL,
  comparison_note VARCHAR(255) NULL,
  artifact_changed_scope VARCHAR(64) NULL,
  config_identity VARCHAR(128) NULL,
  publication_version INT UNSIGNED NULL,
  is_current_publication TINYINT(1) NULL,
  correction_id BIGINT UNSIGNED NULL,
  correction_status VARCHAR(32) NULL,
  correction_outcome VARCHAR(32) NULL,
  correction_reseal_status VARCHAR(64) NULL,
  correction_publication_switch TINYINT(1) NULL,
  baseline_publication_id BIGINT UNSIGNED NULL,
  candidate_publication_id BIGINT UNSIGNED NULL,
  expected_correction_id BIGINT UNSIGNED NULL,
  expected_correction_status VARCHAR(32) NULL,
  expected_correction_outcome VARCHAR(32) NULL,
  expected_correction_reseal_status VARCHAR(64) NULL,
  expected_correction_publication_switch TINYINT(1) NULL,
  expected_baseline_publication_id BIGINT UNSIGNED NULL,
  expected_candidate_publication_id BIGINT UNSIGNED NULL,
  coverage_universe_count INT NULL,
  coverage_available_count INT NULL,
  coverage_missing_count INT NULL,
  coverage_ratio DECIMAL(12,6) NULL,
  coverage_min_threshold DECIMAL(12,6) NULL,
  coverage_gate_state VARCHAR(16) NULL,
  coverage_threshold_mode VARCHAR(32) NULL,
  coverage_universe_basis VARCHAR(64) NULL,
  coverage_contract_version VARCHAR(64) NULL,
  coverage_missing_sample_json JSON NULL,
  bars_rows_written INT NULL,
  indicators_rows_written INT NULL,
  eligibility_rows_written INT NULL,
  eligible_count INT NULL,
  invalid_bar_count INT NULL,
  invalid_indicator_count INT NULL,
  warning_count INT NULL,
  hard_reject_count INT NULL,
  bars_batch_hash VARCHAR(64) NULL,
  indicators_batch_hash VARCHAR(64) NULL,
  eligibility_batch_hash VARCHAR(64) NULL,
  seal_state ENUM('SEALED','UNSEALED') NOT NULL,
  sealed_at DATETIME NULL,
  expected_status ENUM('SUCCESS','HELD','FAILED') NULL,
  expected_terminal_status ENUM('SUCCESS','HELD','FAILED') NULL,
  expected_publishability_state ENUM('READABLE','NOT_READABLE') NULL,
  expected_trade_date_effective DATE NULL,
  expected_seal_state ENUM('SEALED','UNSEALED') NULL,
  expected_source_mode VARCHAR(32) NULL,
  expected_source_name VARCHAR(64) NULL,
  expected_source_provider VARCHAR(64) NULL,
  expected_source_timeout_seconds INT NULL,
  expected_source_retry_max INT NULL,
  expected_source_attempt_count INT NULL,
  expected_source_success_after_retry TINYINT(1) NULL,
  expected_source_retry_exhausted TINYINT(1) NULL,
  expected_source_final_http_status INT NULL,
  expected_source_final_reason_code VARCHAR(64) NULL,
  expected_source_input_file VARCHAR(255) NULL,
  expected_source_file_hash VARCHAR(128) NULL,
  expected_source_file_hash_algorithm VARCHAR(32) NULL,
  expected_source_file_size_bytes BIGINT UNSIGNED NULL,
  expected_source_file_row_count INT UNSIGNED NULL,
  expected_config_identity VARCHAR(128) NULL,
  expected_publication_id BIGINT UNSIGNED NULL,
  expected_publication_run_id BIGINT UNSIGNED NULL,
  expected_publication_version INT UNSIGNED NULL,
  expected_is_current_publication TINYINT(1) NULL,
  expected_coverage_universe_count INT NULL,
  expected_coverage_available_count INT NULL,
  expected_coverage_missing_count INT NULL,
  expected_coverage_ratio DECIMAL(12,6) NULL,
  expected_coverage_min_threshold DECIMAL(12,6) NULL,
  expected_coverage_gate_state VARCHAR(16) NULL,
  expected_coverage_threshold_mode VARCHAR(32) NULL,
  expected_coverage_universe_basis VARCHAR(64) NULL,
  expected_coverage_contract_version VARCHAR(64) NULL,
  expected_coverage_missing_sample_json JSON NULL,
  expected_bars_batch_hash VARCHAR(64) NULL,
  expected_indicators_batch_hash VARCHAR(64) NULL,
  expected_eligibility_batch_hash VARCHAR(64) NULL,
  expected_reason_code_counts_json LONGTEXT NULL,
  mismatch_summary LONGTEXT NULL,
  mismatch_count INT NULL,
  mismatch_reason_codes_json JSON NULL,
  mismatches_json LONGTEXT NULL,
  expected_context_json LONGTEXT NULL,
  actual_context_json LONGTEXT NULL,
  ignored_volatile_fields_json JSON NULL,
  deterministic_fields_checked_json JSON NULL,
  final_reason_code VARCHAR(64) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (replay_id, trade_date),
  KEY idx_replay_daily_status (replay_id, status),
  KEY idx_replay_daily_publishability (replay_id, publishability_state),
  KEY idx_replay_daily_publication_identity (replay_id, publication_id, publication_version),
  KEY idx_replay_daily_effective (replay_id, trade_date_effective),
  KEY idx_replay_daily_comparison (replay_id, comparison_result),
  KEY idx_replay_daily_replay_status (replay_id, replay_status),
  KEY idx_replay_daily_coverage_gate (replay_id, coverage_gate_state),
  KEY idx_replay_daily_artifact_scope (replay_id, artifact_changed_scope),
  KEY idx_replay_daily_publication_version (replay_id, publication_version),
  KEY idx_replay_daily_config_identity (replay_id, config_identity)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS md_replay_reason_code_counts (
  replay_id BIGINT UNSIGNED NOT NULL,
  trade_date DATE NOT NULL,
  reason_code VARCHAR(64) NOT NULL,
  reason_count INT NOT NULL,
  PRIMARY KEY (replay_id, trade_date, reason_code),
  KEY idx_replay_reason_code (replay_id, reason_code)
) ENGINE=InnoDB;

-- MD-B10-A001: independent current-projection versus current-publication reconciliation evidence.
-- Deployed canonically by 2026_08_24_000001_enforce_sealed_history_and_projection_reconciliation.php.
CREATE TABLE IF NOT EXISTS md_publication_projection_reconciliations (
  reconciliation_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  reconciliation_uid CHAR(64) NOT NULL,
  trade_date DATE NOT NULL,
  publication_id BIGINT UNSIGNED NULL,
  run_id BIGINT UNSIGNED NULL,
  publication_version INT UNSIGNED NULL,
  pointer_state VARCHAR(32) NOT NULL,
  reconciliation_state VARCHAR(32) NOT NULL,
  bars_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  bars_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  bars_missing_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  bars_missing_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  bars_value_mismatch_count INT UNSIGNED NOT NULL DEFAULT 0,
  indicators_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  indicators_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  indicators_missing_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  indicators_missing_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  indicators_value_mismatch_count INT UNSIGNED NOT NULL DEFAULT 0,
  eligibility_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  eligibility_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  eligibility_missing_history_count INT UNSIGNED NOT NULL DEFAULT 0,
  eligibility_missing_projection_count INT UNSIGNED NOT NULL DEFAULT 0,
  eligibility_value_mismatch_count INT UNSIGNED NOT NULL DEFAULT 0,
  orphan_projection_row_count INT UNSIGNED NOT NULL DEFAULT 0,
  mismatch_count INT UNSIGNED NOT NULL DEFAULT 0,
  mismatch_sample_json LONGTEXT NULL,
  reconciliation_hash CHAR(64) NOT NULL,
  checked_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (reconciliation_id),
  UNIQUE KEY uq_md_pub_proj_recon_uid (reconciliation_uid),
  KEY idx_md_pub_proj_recon_date_state (trade_date, reconciliation_state),
  KEY idx_md_pub_proj_recon_pub_checked (publication_id, checked_at),
  KEY idx_md_pub_proj_recon_checked (checked_at)
) ENGINE=InnoDB;
