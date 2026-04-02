# Commands and Runbook (Market Data Platform, LOCKED)

## Purpose
Lock the minimum commands and operator checkpoints required so the platform can produce upstream market data that is canonical, validated, deterministic, auditable, and safe for consumer modules.

This document does not define screening, scoring, grouping, ranking, strategy logic, or real-time streaming.

## General command rules (LOCKED)
- all commands must run within a clear `run_id` context
- commands for the same requested date must respect run ownership rules
- commands must write minimum structured evidence to `eod_run_events`
- no command may commit final `SUCCESS` before seal exists
- output-affecting parameters must come from the effective registry snapshot, not undocumented runtime overrides

## Minimum commands

### 1. `market-data:eod-bars:ingest`
#### Purpose
Acquire and canonicalize EOD bars for requested date T.

#### Minimum input
- `requested_date=T`
- source mode (`api|manual_file|manual_entry`)

#### Active default operating notes
- current default source mode for the active codebase is `api`
- current default API provider for the active codebase is `yahoo_finance`
- Yahoo requests for IDX symbols append `.JK` inside the provider adapter
- `manual_file` remains a valid fallback/operator mode and is still required by some controlled runbook paths such as minimum session-snapshot runtime

#### Minimum output
- valid bars -> `eod_bars`
- invalid bars -> `eod_invalid_bars`
- telemetry counts -> `eod_runs`
- audit events -> `eod_run_events`

### 2. `market-data:eod-indicators:compute`
#### Purpose
Compute deterministic indicators from canonical bars.

#### Minimum input
- `requested_date=T`
- `indicator_set_version`

#### Minimum output
- indicator artifact -> `eod_indicators`
- updated run/event telemetry

### 3. `market-data:eod-eligibility:build`
#### Purpose
Build one eligibility row per universe ticker for date T or resolved candidate D as defined by the upstream flow.

#### Minimum input
- `requested_date=T`
- coverage universe snapshot for T/D according to contract

#### Minimum output
- `eod_eligibility`
- run/event telemetry

### 4. `market-data:audit:hash`
#### Purpose
Compute content hashes over consumer-visible artifacts.

#### Minimum input
- `requested_date=T`
- candidate effective date `D`

#### Minimum output
- `bars_batch_hash`
- `indicators_batch_hash`
- `eligibility_batch_hash`

### 5. `market-data:dataset:seal`
#### Purpose
Seal a coherent consumer-readable candidate dataset.

#### Minimum input
- `requested_date=T`
- candidate effective date `D`

#### Minimum output
- seal metadata for the candidate dataset
- updated run/event evidence

### 6. `market-data:run:finalize`
#### Purpose
Resolve terminal run status and effective-date readability.

#### Minimum input
- `requested_date=T`

#### Minimum output
- final `terminal_status`
- resolved `trade_date_effective`
- final run evidence

### 7. `market-data:daily`
#### Purpose
Execute the daily sequence:
- ingest
- compute
- eligibility
- hash
- seal
- finalize

### 8. `market-data:backfill`
#### Purpose
Historical backfill/recompute per trading-date range.

#### Minimum input
- `start_date`
- `end_date`
- optional `source_mode`
- optional deterministic `output_dir`

#### Minimum behavior
- resolve trading dates from `market_calendar`, not weekday guessing
- execute the locked daily pipeline semantics once per resolved trading date
- stop non-zero when a date fails unless operator explicitly opts into continue-on-error behavior

#### Minimum output
- one summary artifact `market_data_backfill_summary.json`
- per-date observed status/run_id in the summary artifact

### 9. `market-data:session-snapshot`
#### Purpose
Capture optional non-streaming session snapshot aligned to readable effective trade date.

#### Minimum input
- `trade_date` aligned to a readable current publication
- `snapshot_slot`
- `source_mode=manual_file` for minimum runtime on this command even though the platform-wide EOD ingestion default is now `api`
- `input_file` containing upstream-safe session snapshot rows keyed by `ticker_code`
- optional deterministic `output_dir`

#### Minimum behavior
- resolve scope from `eod_eligibility` for effective trade date D, not downstream picks or watchlists
- require a readable current publication for D before capture is allowed
- write snapshot rows to `md_session_snapshots`
- write structured session-snapshot evidence to `eod_run_events` using the owning readable run context
- for default locked slots, rows outside the configured slot-tolerance window must be recorded as skipped partial-state rows, not treated as EOD failure
- failure or partial scope must never mutate EOD publication readiness

#### Minimum output
- session snapshot rows in `md_session_snapshots`
- one summary artifact `market_data_session_snapshot_summary.json` carrying at minimum: `trade_date`, `trade_date_effective`, `publication_id`, `run_id`, `scope_count`, `captured_count`, `skipped_count`, `slot_tolerance_minutes`, `slot_anchor_time` when applicable, and `slot_miss_count`

### 10. `market-data:session-snapshot:purge`
#### Purpose
Purge session snapshot rows according to retention policy.

#### Minimum input
- optional explicit `before_date`
- optional deterministic `output_dir`

#### Minimum output
- deleted row count
- one summary artifact `market_data_session_snapshot_purge_summary.json` containing `cutoff_timestamp`, explicit `cutoff_source` (`explicit_before_date` or `default_retention_days`), `deleted_rows`, and the supporting cutoff field (`before_date` or `retention_days`)

### 11. `market-data:replay:verify`
#### Purpose
Verify one executed market-data run against a replay fixture package and persist replay proof rows to `md_replay_*`.

#### Minimum input
- `run_id`
- `fixture_path` pointing to a valid fixture package with `manifest.json` and expected replay outputs
- optional explicit `replay_id` when operator needs deterministic replay identity

#### Built-in smoke fixtures
- `storage/app/market_data/replay-fixtures/valid_case`
- `storage/app/market_data/replay-fixtures/broken_manifest_case`
- `storage/app/market_data/replay-fixtures/missing_file_case`
- `storage/app/market_data/replay-fixtures/reason_code_mismatch_case`

Expected smoke outcomes:
- `valid_case` must pass for a compatible completed run with `MATCH`
- `reason_code_mismatch_case` must complete with `MISMATCH`
- `broken_manifest_case` must fail because required manifest fields are missing
- `missing_file_case` must fail because `manifest.files` declares `expected/missing.json` but that file is intentionally absent

#### Minimum output
- one row in `md_replay_daily_metrics` for the verified requested date
- synchronized `md_replay_reason_code_counts` rows
- replay evidence exportable through `market-data:evidence:export --replay_id=...`
- replay evidence pack now preserves explicit `expected_state` and `actual_state`, not just a flat replay summary

### 12. `market-data:replay:smoke`
#### Purpose
Execute the built-in replay smoke suite against one completed run and write a suite summary artifact.

#### Minimum input
- `run_id`
- optional `fixture_root` when operator wants to override the built-in fixture directory root
- optional `output_dir` when operator wants deterministic suite artifact placement

#### Built-in fixture cases
- `valid_case` must observe `MATCH`
- `reason_code_mismatch_case` must observe `MISMATCH`
- `broken_manifest_case` must fail with `ERROR`
- `missing_file_case` must fail with `ERROR`

#### Minimum output
- one suite summary JSON artifact describing all built-in cases and whether each case passed
- replay evidence bundles for successful positive cases (`MATCH` / `MISMATCH`)
- non-zero exit when any smoke case deviates from its expected outcome

---
### 13. `market-data:replay:backfill`
#### Purpose
Execute fixture-aware historical replay verification across a trading-date range without requiring one-by-one manual replay commands.

#### Minimum input
- `start_date`
- `end_date`
- optional `fixture_case` (default `valid_case`)
- optional `fixture_root` when operator wants to override built-in fixture directory root
- optional deterministic `output_dir`
- optional `continue_on_error` when operator wants resumable range execution

#### Minimum behavior
- resolve trading dates from `market_calendar`, not weekday guessing
- for each trading date resolve the current readable publication pointer/run context
- execute replay verification against the selected fixture case
- export replay evidence for successful per-date cases
- stop non-zero when a date fails unless operator explicitly opts into continue-on-error behavior

#### Minimum output
- one summary artifact `market_data_replay_backfill_summary.json`
- per-date observed outcome, `run_id`, and `replay_id` in the summary artifact

---

## Operator checkpoints per command

### `market-data:eod-bars:ingest`
#### Verify before continuing
- requested date is correct
- owner run is correct
- source mode is expected
- acquisition completed or degraded state is explicit

#### Expected outputs
- canonical bars exist or explicit gap/failure evidence exists
- invalid bars stored when applicable
- run/event counts updated

#### Stop publish if
- source contract is untrustworthy
- coverage impact is unresolved
- canonical bars are incomplete in a way that may break locked gates

---

### `market-data:eod-indicators:compute`
#### Verify before continuing
- canonical bars for required keys/dates are available
- indicator set version is correct
- dependency windows are resolvable

#### Expected outputs
- one indicator row per `(trade_date, ticker_id)` where implementation chooses to materialize
- invalid-state rows are explicit
- run/event evidence updated

#### Stop publish if
- mandatory indicator artifact is incomplete
- compute failure is unresolved
- dependency ambiguity remains

---

### `market-data:eod-eligibility:build`
#### Verify before continuing
- coverage universe basis is correct
- indicator artifact is ready enough to evaluate eligibility
- row cardinality expectation is known

#### Expected outputs
- exactly one eligibility row per universe ticker/date
- blocked rows carry registered reason codes
- row counts match expectation

#### Stop publish if
- eligibility artifact is missing
- row cardinality is broken
- reason-code semantics are ambiguous

---

### `market-data:audit:hash`
#### Verify before continuing
- bars/indicators/eligibility candidate artifacts are coherent
- no mixed-run artifact set is being hashed
- serialization inputs follow locked contract

#### Expected outputs
- three content hashes populated
- event trail records successful hash generation

#### Stop publish if
- any mandatory hash is missing
- serialization ambiguity exists
- identical-content replay unexpectedly produces different hash

---

### `market-data:dataset:seal`
#### Verify before continuing
- hash set exists
- candidate dataset is coherent
- run ownership is still valid
- seal preconditions are met

#### Expected outputs
- seal metadata written
- candidate dataset marked sealed in the locked publication sense

#### Stop publish if
- seal write fails
- candidate hash set is incomplete
- publication target is ambiguous

---

### `market-data:run:finalize`
#### Verify before continuing
- cutoff policy is satisfied
- seal exists if final success is to be committed
- effective-date resolution is clear
- prior publication safety is preserved

#### Expected outputs
- final terminal status
- effective trade date
- final event evidence

#### Stop publish if
- success would be unsealed
- effective-date resolution is ambiguous
- current publication state would become ambiguous

---
### `market-data:replay:verify`
#### Verify before continuing
- `run_id` resolves to one completed execution context
- fixture package contains `manifest.json` and required expected files
- every entry listed under `manifest.files` resolves to an actual file in the package
- replay intent is proof/verification, not production publication switch

#### Expected outputs
- persisted replay metric row for requested trade date
- persisted replay reason-code counts
- persisted expected replay context for status/effective-date/seal/config/hash/reason-code expectation so evidence export can preserve both expected and actual state
- fixture-declared expected reason-code distribution now participates in replay compare, including explicit empty-set expectation
- replay classification explains match/degrade/mismatch outcome

#### Stop publish if
- fixture package is incomplete or malformed
- run context cannot be resolved coherently
- replay proof would be written without clear expected outcome

---
### `market-data:replay:backfill`
#### Verify before continuing
- requested replay range resolves to at least one trading date in `market_calendar`
- every trading date can resolve a current readable publication/run context
- selected fixture case is present under the fixture root
- operator intent is replay proof batching, not publication mutation

#### Expected outputs
- one replay verification outcome per resolved trading date
- replay evidence exported for successful per-date cases
- one deterministic summary artifact `market_data_replay_backfill_summary.json`

#### Stop publish if
- a trading date cannot resolve a readable current publication and operator did not opt into continue-on-error
- fixture case is missing or malformed
- replay range summary cannot distinguish expected vs observed outcome per date

---

## Exit discipline (LOCKED)
- technical stage success without publish-readiness may still leave run `HELD` or non-final
- hard failure -> non-success run outcome
- no command may disguise partial publish as final readable `SUCCESS`

## Operator flow for daily run (LOCKED)
1. run `market-data:daily --latest`
2. inspect final run state
3. verify hash presence if candidate success path
4. verify seal exists before treating date as readable
5. if `HELD` or `FAILED`, confirm fallback safety to prior readable sealed date
6. preserve evidence for anomalies

## Locking and ownership rule (LOCKED)
- one requested date must have one active writer owner for publish-critical stages
- hash/seal/finalize must be executed by the owning run context
- correction/backfill must not silently override current readable publication