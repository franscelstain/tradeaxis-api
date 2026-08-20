# Indices and Constraints Contract (LOCKED)

## Purpose
Define the minimum index, uniqueness, and integrity expectations required so Market Data Platform remains deterministic, queryable, and safe for publication resolution.

This document complements:
- `Database_Schema_MariaDB.sql`
- `Database_Schema_Contracts_MariaDB.md`

## Required unique-key semantics (LOCKED)

### Canonical bars
Must enforce:
- one row per `(trade_date, listing_id)` within the resolved canonical revision/publication identity

### Indicators
Must enforce:
- one row per `(trade_date, listing_id)` per immutable publication/product binding

### Data usability / eligibility compatibility projection
Must enforce:
- one row per `(trade_date, listing_id)` per publication/read-product binding

`ticker_id` may remain as a legacy compatibility column/index only while its invariant equivalence to the stable `listing_id` is documented and tested. It is not a target key for new schema surfaces.

### Replay reason-code counts
Must enforce:
- one row per `(replay_id, trade_date, reason_code)`

### Publication versioning
If explicit publication table is used, must enforce:
- one row per `(trade_date, publication_version)`

## Required supporting indexes

### Bars
Recommended:
- `(listing_id, trade_date)`
- optional compatibility `(ticker_id, trade_date)` only while legacy projection exists
- `(run_id)`

### Indicators
Recommended:
- `(listing_id, trade_date)`
- optional compatibility `(ticker_id, trade_date)` only while legacy projection exists
- `(run_id)`
- `(invalid_reason_code)`

### Eligibility / data usability
Recommended:
- `(listing_id, trade_date)`
- optional compatibility `(ticker_id, trade_date)` only while legacy projection exists
- `(run_id)`
- `(reason_code)`

### Runs
Recommended:
- `(trade_date_requested, lifecycle_state)`
- `(trade_date_requested, terminal_status)`
- `(trade_date_effective, terminal_status)`
- `(trade_date_effective, publishability_state)`
- `(quality_gate_state)`
- `(stage)`
- `(trade_date_effective, is_current_publication)`
- `(supersedes_run_id)`

### Publications
Recommended:
- `(trade_date, is_current)`
- `(run_id)`
- `(supersedes_publication_id)`

### Corrections
Recommended:
- `(trade_date, status)`
- `(prior_run_id)`
- `(new_run_id)`
- `(baseline_publication_id)`
- `(replacement_publication_id)`
- `(baseline_publication_id, replacement_publication_id)`

## Integrity semantics that may require application enforcement
Some invariants are logically mandatory even if MariaDB cannot express them as a partial unique constraint.

These include:
- exactly one current publication per trade date
- no ambiguous publication switch
- no simultaneous old/new current publication after correction
- no publish-critical mixed-run artifact resolution

## Foreign-key guidance
Foreign keys are recommended where operationally safe, for example:
- publication -> run
- correction.prior_run_id -> run
- correction.new_run_id -> run
- correction.baseline_publication_id -> publication
- correction.replacement_publication_id -> publication
- indicator.invalid_reason_code -> reason-code registry (if registry design supports it)
- eligibility.reason_code -> reason-code registry (if registry design supports it)

If hard FK constraints are not used for operational reasons, semantic linkage must still be enforced by application/service logic.

## Queryability rule (LOCKED)
Indexes must support at minimum:
- consumer-readable publication resolution
- replay result lookup
- correction trail inspection
- run/event audit investigation
- per-date artifact validation

## Anti-ambiguity rule (LOCKED)
Lack of a direct partial unique index does not weaken the contract.
The invariant must still be enforced deterministically by transaction or procedure discipline.
---

## 2026-04-26 — Schema Sync Index Addendum

Status: LOCKED / HISTORICAL RUNTIME SHAPE WHERE IT USES `ticker_id`

The following indexes/constraints are now included in the DB schema sync contract:

- `tickers`: `PRIMARY KEY (ticker_id)`, `UNIQUE KEY ticker_code (ticker_code)`
- `market_calendar`: `PRIMARY KEY (cal_date)`, `KEY market_calendar_trading_idx (is_trading_day, cal_date)`
- **Historical 2026-04-26 runtime shape:** `md_session_snapshots` used `UNIQUE KEY (trade_date, snapshot_slot, ticker_id)`. **V2 target:** uniqueness is `(trade_date, snapshot_slot, listing_id)`; `ticker_id` is compatibility-only until migrated.

SQLite mirror must include equivalent indexes where Laravel/SQLite supports them.

---

## 2026-05-19 - DB Schema / Migration Sync Addendum

Status: LOCKED INDEX INTENT / MIGRATION-SYNCED

The current runtime schema, migration chain, and SQLite mirror must include these additional queryability constraints:

- `eod_runs`: `idx_runs_coverage_gate_state (coverage_gate_state)`
- `eod_runs`: `idx_runs_request_mode (request_mode)`
- `eod_runs`: `idx_runs_effective_readable_contract (trade_date_effective, terminal_status, publishability_state, coverage_gate_state, is_current_publication)`
- `eod_runs`: `idx_runs_publication_id (publication_id)`
- `eod_runs`: `idx_runs_correction_id (correction_id)`
- `eod_runs`: `idx_runs_promote_mode (promote_mode)`
- `eod_runs`: `idx_runs_publish_target (publish_target)`
- `eod_runs`: `idx_runs_final_reason_code (final_reason_code)`
- `eod_runs`: `idx_runs_source_name (source_name)`
- `eod_runs`: `idx_runs_source_file_hash (source_file_hash)`
- `eod_runs`: `idx_runs_source_identity (source, source_name, source_provider, source_file_hash)`
- `eod_publications`: `idx_publication_readable_lookup (trade_date, is_current, seal_state, publication_version, run_id)`
- `eod_publications`: `idx_publication_run_trade_date (run_id, trade_date, publication_id)`
- `eod_publications`: `idx_publication_previous (previous_publication_id)`
- `eod_publications`: `idx_publication_replaced (replaced_publication_id)`
- `eod_publications`: `idx_publication_source_file_hash (source_file_hash)`
- `eod_current_publication_pointer`: `PRIMARY KEY (trade_date)`
- `eod_current_publication_pointer`: `uq_current_publication_pointer_publication (publication_id)`
- `eod_current_publication_pointer`: `idx_current_publication_pointer_run (run_id)`
- `eod_current_publication_pointer`: `idx_current_publication_pointer_run_version (run_id, publication_version)`
- `eod_dataset_corrections`: `idx_corr_trade_date_status_execution (trade_date, status, execution_count)`
- `eod_dataset_corrections`: `idx_corr_prior_new_run (prior_run_id, new_run_id)`
- `eod_dataset_corrections`: `idx_corr_baseline_publication (baseline_publication_id)`
- `eod_dataset_corrections`: `idx_corr_replacement_publication (replacement_publication_id)`
- `eod_dataset_corrections`: `idx_corr_baseline_replacement_publication (baseline_publication_id, replacement_publication_id)`
- **V2 target** `eod_bars`, `eod_indicators`, and data-usability/eligibility projection: publication-scoped lookup indexes `(publication_id, trade_date, listing_id)`. A legacy `(publication_id, trade_date, ticker_id)` index may coexist only as transitional compatibility.
- `md_replay_daily_metrics`: replay status, publishability, publication identity, effective date, comparison, coverage gate, artifact scope, publication version, and config identity indexes

Current-publication uniqueness is owned by `eod_current_publication_pointer.trade_date` plus unique `publication_id`. `eod_publications.is_current` is retained only as a mirror/cache marker and must never compete with the pointer table for authoritative current-state resolution.
