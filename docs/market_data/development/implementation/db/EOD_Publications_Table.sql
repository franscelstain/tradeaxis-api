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

-- LOCKED SEMANTICS
-- 1. One trade_date may have multiple historical publications over time.
-- 2. Publication identity is `publication_id`; version identity is `(trade_date, publication_version)`.
-- 3. `run_id` links the publication to the owning run. Runtime mirror checks are application-enforced.
-- 4. `supersedes_publication_id`, `previous_publication_id`, and `replaced_publication_id` preserve correction/replacement lineage.
-- 5. Source-file hash fields mirror the immutable source identity captured on the owning run.
-- 6. Hash fields and seal metadata are required for sealed/readable publication proof.
-- 7. `is_current` is a mirror/cache marker only under the hardened pointer model.
-- 8. `eod_current_publication_pointer` is the sole authoritative current-publication pointer.
-- 9. Consumers must not resolve current readable state from `eod_publications.is_current` alone.
-- 10. Unchanged reruns must not create a fake new publication_version.

-- IMPORTANT NOTE
-- MariaDB cannot enforce "only one row with is_current=1 per trade_date" using a partial
-- unique index in the same way some other databases can. In this schema, the invariant is
-- owned by `eod_current_publication_pointer` plus repository transaction and mirror guards.
