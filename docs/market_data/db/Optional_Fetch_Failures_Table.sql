-- Optional V2 per-listing/source-symbol acquisition-failure evidence.
-- Append-only diagnostic table; stable listing_id is the canonical mapped identity.
-- ticker_id is retained only as optional compatibility/display metadata.

CREATE TABLE IF NOT EXISTS eod_fetch_failures (
  fetch_failure_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  trade_date DATE NOT NULL,
  listing_id BIGINT UNSIGNED NULL,
  ticker_id BIGINT UNSIGNED NULL,
  source_symbol VARCHAR(64) NULL,
  provider_mapping_ref VARCHAR(255) NULL,
  run_id BIGINT UNSIGNED NOT NULL,
  source_observation_id BIGINT UNSIGNED NULL,
  source VARCHAR(32) NOT NULL,
  failure_reason_code VARCHAR(64) NOT NULL,
  failure_note VARCHAR(255) NULL,
  retry_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (fetch_failure_id),
  KEY idx_fetch_fail_run (run_id),
  KEY idx_fetch_fail_trade_listing (trade_date, listing_id),
  KEY idx_fetch_fail_trade_source_symbol (trade_date, source, source_symbol),
  KEY idx_fetch_fail_observation (source_observation_id),
  CONSTRAINT fk_fetch_fail_run FOREIGN KEY (run_id) REFERENCES eod_runs(run_id)
) ENGINE=InnoDB;
