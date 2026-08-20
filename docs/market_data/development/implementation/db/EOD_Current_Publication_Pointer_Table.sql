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
-- 1. This table is the sole authoritative DB-facing current-publication pointer.
-- 2. `trade_date` as primary key guarantees at most one pointer row per trade date.
-- 3. `publication_id` is unique so one publication cannot be current for multiple trade dates.
-- 4. `publication_id` has an explicit FK to `eod_publications(publication_id)`.
-- 5. `run_id` and `publication_version` are pointer mirror metadata and are indexed for
--    repository validation against `eod_publications` and `eod_runs`.
-- 6. Consumer-readable publication resolution must use this pointer table first and must
--    validate publication/run mirror state, coverage PASS, `SUCCESS + READABLE`, and seal proof.
-- 7. `eod_publications.is_current` and `eod_runs.is_current_publication` are mirror/cache flags;
--    they are not competing current-publication mechanisms.
