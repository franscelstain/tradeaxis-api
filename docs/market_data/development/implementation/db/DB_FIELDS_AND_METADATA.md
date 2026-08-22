# DB Fields & Metadata (Coverage Gate)

## Purpose

## V2 strategy synchronization boundary (LOCKED 2026-08-08)

This file is a **target metadata contract plus transitional runtime inventory**. Any legacy column, enum, request-mode label, or lookup behavior described below is compatibility evidence only when it conflicts with the V2 owner contracts. New schema/API surfaces must bind stable `listing_id`/`instrument_id`, immutable source/revision identities, complete config provenance, and point-in-time semantics. Compatibility `ticker_id`, `repair_candidate`, current-master `is_active`, or dictionary-only expected-bar decisions do **not** satisfy V2 semantics.

Define the minimum DB/runtime metadata that must be persisted or emitted so the locked coverage-gate contract is audit-visible and implementable.

This document does not own the coverage formula.
Formula and outcome semantics are owned by `../book/EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`.

## Required eod_runs fields (LOCKED minimum)
The `eod_runs` record for a requested trade date must make these values audit-visible:

- `knowledge_cutoff_at` DATETIME NULL
  Immutable as-known coordinate captured for a new run after legacy identity projection completes. `NULL` on a legacy run preserves the historical fact that it was unbounded; readers must not manufacture a later cutoff, and runtime must not resume execution on that same run identity.
- `coverage_universe_count` INT NULL  
  Raw temporal-universe count before verified `NOT_EXPECTED` exclusions; it is not the denominator.
- `coverage_universe_hash` CHAR(64) NULL
  Deterministic identity of the ordered temporal universe, its basis, and requested trade date.
- `coverage_bar_not_expected_count` INT NULL
  Listings excluded only by verified point-in-time `NOT_EXPECTED` evidence.
- `coverage_expected_count` INT NULL
  Fail-safe denominator: temporal universe minus verified `NOT_EXPECTED`, retaining `UNKNOWN`.
- `coverage_expectation_unknown_count` INT NULL
  Denominator members whose expectation evidence is unresolved; zero means measured zero, while `NULL` means not measured.
- `coverage_delivered_count` INT NULL
  Traceably delivered expected listing/date observations used as the coverage numerator.
- `coverage_delivered_valid_count` INT NULL
  Delivered observations that also passed canonical validation; it remains distinct from delivery coverage.
- `coverage_available_count` INT NULL  
  Compatibility metric for canonical availability. It must not replace or be exported as `coverage_delivered_count`.
- `coverage_missing_count` INT NULL  
  Missing expected delivery count. The governing identity is `coverage_expected_count - coverage_delivered_count`, never raw universe minus canonical availability.
- `coverage_ratio` DECIMAL(12,6) NULL  
  Evaluated ratio before any UI-only rounding.
- `coverage_min_threshold` DECIMAL(12,6) NULL  
  Threshold actually used by the run.
- `coverage_gate_state` ENUM/VARCHAR NULL  
  Final allowed values: `PASS`, `FAIL`, `NOT_EVALUABLE`. Legacy persisted/input `BLOCKED` must be normalized to `NOT_EVALUABLE` before output or final persistence, with raw legacy value exposed only as explicit legacy/raw metadata.
- `coverage_threshold_mode` VARCHAR(32) NULL  
  Initial locked value: `MIN_RATIO`.
- `coverage_universe_basis` VARCHAR(64) NULL  
  Initial locked value: `ACTIVE_LISTED_EQUITY_AS_OF_DATE`.
- `coverage_contract_version` VARCHAR(64) NULL  
  Contract/config identity for audit and replay clarity.
- `coverage_missing_sample_json` JSON/TEXT NULL  
  Optional but recommended sample of missing stable listing identities plus display symbols for operator evidence. Sampling must not replace the official counts.
- `coverage_excluded_sample_json` JSON/TEXT NULL
  Bounded sample of listings removed by verified `NOT_EXPECTED` evidence; `NULL` remains distinct from a measured empty sample.

## Why these fields are required
They close the ambiguity that previously allowed coverage to be discussed without proving:
- what denominator was used
- what numerator was used
- what threshold was used
- why the gate ended in `PASS`, `FAIL`, or `NOT_EVALUABLE`

## Required config metadata linkage
The persisted coverage values must stay explainable from runtime config. At minimum, config must expose:
- `MARKET_DATA_COVERAGE_MIN`
- `MARKET_DATA_COVERAGE_THRESHOLD_MODE`
- `MARKET_DATA_COVERAGE_UNIVERSE_BASIS`
- `MARKET_DATA_COVERAGE_CONTRACT_VERSION`

## Replay / evidence mirror
If replay or evidence tables mirror run metrics, they should also mirror the same coverage metadata fields so proof artifacts can explain the original gate decision without re-deriving it.

## Anti-ambiguity notes
- `coverage_ratio` alone is not sufficient.
- `coverage_gate_state` alone is not sufficient.
- provider row count must never substitute for `coverage_delivered_count`.
- raw `coverage_universe_count` must never substitute for `coverage_expected_count`.
- canonical `coverage_available_count` must never substitute for traceable `coverage_delivered_count`.
- eligibility row count must never substitute for any delivery count.


## Required eod_runs source traceability fields (session hardening minimum)
The `eod_runs` record must also expose first-class traceability fields so source context is queryable without parsing logs only:

- `source` VARCHAR/ENUM NOT NULL  
  Logical source mode used by the run, for example `api` or `manual_file`.
- `request_mode` VARCHAR(32) NULL  
  Explicit import/promote intent for the run. Target semantic values include `import_only`, `promote`, `full_publish`, `correction`, `correction_candidate`, `replay_verify`, and `evidence_export`. A persisted/runtime `repair_candidate` value is a legacy compatibility label for `correction_candidate`; it must not authorize in-place content repair. `import_only` must not be interpreted as consumer-readable publication proof.
- `source_name` VARCHAR(64) NULL  
  Logical source identity such as `API_FREE` or `LOCAL_FILE`.
- `source_provider` VARCHAR(64) NULL  
  Upstream/provider identity when relevant.
- `source_input_file` VARCHAR(255) NULL  
  Manual input path/reference when manual source mode is used.
- `source_timeout_seconds` INT NULL
- `source_retry_max` INT NULL
- `source_attempt_count` INT NULL
- `source_success_after_retry` BOOLEAN/TINYINT NULL
- `source_retry_exhausted` BOOLEAN/TINYINT NULL
- `source_final_http_status` INT NULL
- `source_final_reason_code` VARCHAR(64) NULL

These fields do not replace run events or evidence artifacts; they make minimum source provenance audit-visible directly from DB state.

## Required eod_runs linkage/publishability fields (session hardening minimum)
To avoid implicit-only linkage, `eod_runs` should also persist:

- `publication_id` BIGINT NULL  
  Publication row produced/resolved by the run.
- `publication_version` INT NULL
- `correction_id` BIGINT NULL  
  Official correction request reference when the run is part of correction flow.
- `final_reason_code` VARCHAR(64) NULL  
  Final consumer/audit-visible reason code aligned with the terminal outcome.

This keeps run/publication linkage and publishability reasoning queryable even when operators are not reading event payload JSON.

## Required analytical product identity

Every indicator-producing run and sealed publication must persist one coherent analytical identity:

- `price_product_code = STRUCTURAL_ADJUSTED`
- `price_product_version` identifying the selected product implementation
- `factor_set_hash` as the deterministic SHA-256 identity of the complete run/window factor set, including the empty/no-op set

Every `eod_indicators` and `eod_indicators_history` row must mirror those three values. Sector-relative rows additionally bind `sector_membership_id` so their point-in-time classification fact is auditable. A cumulative factor of one does not change the product to `RAW`, and provider `adj_close` is source evidence rather than an analytical fallback.

## Required exchange-market-structure metadata

Authority recording uses three non-output tables:

- `md_exchange_market_structure_revisions`: `market_structure_revision_id`, `rule_uid`,
  `revision_number`, `rule_type`, `exchange_code`, `market_segment`, `instrument_scope_code`,
  `coverage_scope_json`, `effective_from`, `effective_to`, `minimum_price_idr`,
  `verification_state`, `source_uid`, `source_observation_id`, `source_reference`, `content_hash`,
  `recorded_at`, and `supersedes_revision_id`;
- `md_exchange_price_band_tiers`: revision identity, ordered price bounds with explicit
  inclusivity, and separate `upper_limit_percent` / `lower_limit_percent`;
- `md_exchange_tick_size_tiers`: revision identity, ordered price bounds with explicit
  inclusivity, `tick_size_idr`, and `maximum_price_step_idr`.

The locked Stage 7 scope is standard equity on the IDX Main, Development, and New Economy boards
in the Regular Market. Acceleration and Special Monitoring are explicitly excluded because their
rules differ. Missing or unrecognized board identity is `FAIL_CLOSED`; the authority tables must
not be applied to output until the separately governed reconstruction stage supplies an unambiguous
point-in-time board mapping and binds the selected revision.

For Stage 7 authority, the accepted `source_observation_id` is admissible only when its capture pair
stores the verified response status, content type, exact document SHA-256/ref/byte length, schema
fingerprint, and bounded sample. A legacy observation containing manifest metadata alone cannot be
reused. Its correction appends a new revision and observation pair with both supersession links;
the old rows remain immutable history.

## Required Stage 8 reconstruction metadata

The one-time current-corpus reconstruction persists its decisions separately from legacy facts:

- `md_source_scale_assessments` records the immutable per-provider/listing/corporate-action
  classification (`AS_TRADED`, `PROVIDER_BACK_ADJUSTED`, or `UNKNOWN`), evidence-set hash,
  effective boundary, recorded time, revision number, and supersession identity;
- `md_adjustment_factor_decisions` records, for every authoritative event considered by a factor
  set, whether its factor was applied or held and binds the exact source-scale assessment;
- `md_publication_market_structure_bindings` records one point-in-time board/tier resolution per
  publication/listing, including explicit fail-closed states and the selected band, floor, and tick
  revision IDs only when resolution succeeded;
- `md_stage8_reconstruction_campaigns` freezes the date scope, pre-campaign maximum publication,
  baseline target-set hash, lifecycle state, result, and timestamps;
- `md_stage8_reconstruction_targets` freezes each date's baseline publication/run/version, sealed
  artifact hashes and independent snapshot hashes, then records correction and replacement
  identities without changing the baseline rows.

New bar rows bind `source_scale_state` and `source_scale_assessment_id`. Eligibility rows bind the
market-structure resolution state and selected revision IDs. Publication plus
`md_publication_lineage_bindings` carry the same three SHA-256 set identities:
`source_scale_assessment_set_hash`, `market_structure_revision_set_hash`, and
`factor_decision_set_hash`. Legacy rows remain nullable; a new Stage 8 publication may seal only
when its own bindings are complete.

A blocked campaign and an unsealed failed candidate are immutable execution evidence, not a current
publication. They must have a terminal correction/run reason, and normal reads continue to follow
the unchanged current pointer until a readable sealed replacement succeeds.

### Stage 8 conformant-corpus admission boundary

`md_corpus_admission_decisions` separates the intentional dataset start from the earliest measured
consumer-readable suffix. Each immutable decision stores market/product scope,
`intentional_dataset_start`, `admitted_from`, `measured_through`, the locked threshold/source mode,
the exact status snapshot and transition-search observations, the measurement campaign, input and
status-set hashes, algorithm version, complete measurement JSON, state/reason, supersession, and
recorded time.

An active decision binds `md_stage8_reconstruction_campaigns.admission_decision_id`,
`eod_runs.corpus_admission_decision_id`, `eod_publications.corpus_admission_decision_id`, and
`md_publication_lineage_bindings.corpus_admission_decision_id`. The same ID is required end to end
for every admitted current publication. `eod_eligibility` and `eod_eligibility_history` additionally
bind `trading_status_revision_id` and `trading_status_source_observation_id` whenever verified
full-session status changes bar expectation.

Admission never relabels history or moves `intentional_dataset_start`. Dates before `admitted_from`
remain immutable but are not consumer-readable and cannot seed indicator warm-up. A blocked
full-range measurement campaign may be marked `SUPERSEDED` only by an explicit hashed admission
decision; its attempts remain untouched. Stage 8 campaign supersession is recorded through
`supersedes_campaign_id`/`superseded_at`, not deletion.

---

## 2026-04-26 — DB Schema Sync Metadata Addendum

Status: **TRANSITIONAL RUNTIME INVENTORY / V2 TARGET OWNED BY `DB_Schema_And_Migration_Sync_Contract_LOCKED.md`**

The structures below record the runtime shape synchronized in 2026-04/06. Where they use current `ticker_id`, overwrite/upsert business keys, non-revisioned source rows, or session-snapshot keys without `listing_id`, those lines are **historical/transitional inventory, not V2 target semantics**. The V2 target must add stable listing identity and immutable observation/revision/publication bindings before implementation conformance can be granted.

Newly documented / synchronized table metadata:

### `tickers` — legacy runtime projection

Runtime owner: ticker universe / coverage universe lookup.  
Repository: `TickerMasterRepository`.

**V2 target:** this table is a compatibility/current-state projection only. Historical universe authority lives in temporal issuer/instrument/listing/symbol revisions. `is_active` and current `ticker_code` cannot define an as-of universe.

Legacy runtime fields:
- `ticker_id`
- `ticker_code`
- `company_name`
- `company_logo`
- `listed_date`
- `delisted_date`
- `board_code`
- `exchange_code`
- `is_active`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `ticker_id`
- unique key `ticker_code`

### `market_calendar`

Runtime owner: trading date resolution.  
Repository: `MarketCalendarRepository`.

Required fields:
- `cal_date`
- `is_trading_day`
- `holiday_name`
- `session_open_time`
- `session_close_time`
- `breaks_json`
- `source`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `cal_date`
- index `market_calendar_trading_idx (is_trading_day, cal_date)`

### `market_data_sectors`

Runtime owner: source-backed sector taxonomy for upstream market-data context.
Repository: `SectorClassificationRepository`.

**V2 target:** membership binds stable `listing_id`, temporal interval, source observation/revision, known time, and authority class. Reclassification closes the old revision and opens a new one; accepted history is not overwritten.

Legacy runtime fields:
- `sector_code`
- `sector_name`
- `sector_index_code`
- `classification_system`
- `effective_from`
- `effective_to`
- `is_active`
- `source_name`
- `source_ref`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `sector_code`
- index `(classification_system, is_active, sector_code)`
- index `(sector_index_code)`

### Sector index benchmark source

Runtime owner: sector-index benchmark master and bars used to populate nullable sector-rotation fields.
Tables:
- `market_benchmarks`
- `market_benchmark_bars`
- `market_benchmark_indicators`

Required sector benchmark codes:
- `IDXENERGY`
- `IDXBASIC`
- `IDXINDUST`
- `IDXNONCYC`
- `IDXCYCLIC`
- `IDXHEALTH`
- `IDXFINANCE`
- `IDXPROPERT`
- `IDXTECHNO`
- `IDXINFRA`
- `IDXTRANS`

Sector benchmark rows use provider `manual_sector_index_csv` unless a future audited provider is added. They must not be fetched through the Yahoo equity/benchmark API unless a verified provider symbol exists.

### `ticker_sector_memberships` — governed point-in-time fact

Runtime owner: append-only, as-known listing-to-sector membership used to populate nullable `eod_indicators.sector_code`, then resolve sector-index benchmark context for nullable `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg`.
Repository: `SectorClassificationRepository`.

Required fields:
- `membership_id`
- `ticker_id` (legacy compatibility reference)
- `listing_id` (stable resolution identity)
- `sector_code`
- `classification_system`
- `effective_from`
- `effective_to`
- `source_name`
- `source_ref`
- `source_authority_class`
- `recorded_at` (known-time boundary)
- `supersedes_membership_id`
- `operator_name`
- `reason_code`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `membership_id`
- unique key `(listing_id, classification_system, effective_from, recorded_at)`
- index `(ticker_id, classification_system, effective_from, effective_to)`
- index `(sector_code, classification_system, effective_from)`
- index `(listing_id, classification_system, effective_from, effective_to)`
- index `(recorded_at, source_authority_class)`
- index `(supersedes_membership_id)`

Resolver semantics are fail-closed: only exchange-authoritative or governed operator-entered revisions known by the requested `known_at` may resolve; missing/ambiguous membership and overlapping authoritative intervals produce `UNKNOWN`. Reclassification appends a closing revision for the prior interval and a new membership revision; it never updates the historical fact in place.

### `market_data_corporate_actions` — legacy runtime projection

Runtime owner: source-backed corporate action context used to populate nullable `eod_indicators.corporate_action_flag`, `corporate_action_types`, `event_risk_flag`, and `event_risk_reasons`.
Repository: `EventRiskSourceRepository`.
Import command: `market-data:events:import-corporate-actions`.

**V2 target:** corporate-action source observations and event/factor revisions are append-only and bind stable `listing_id`; the legacy same-key upsert/`updated_at` shape below is not correction authority.

Legacy runtime fields:
- `corporate_action_id`
- `ticker_id`
- `ticker_code`
- `action_date`
- `action_type`
- `source_name`
- `source_ref`
- `notes`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `corporate_action_id`
- unique key `(ticker_id, action_date, action_type, source_name)`
- index `(action_date, ticker_id)`
- index `(action_type, action_date)`

### `market_data_trading_status_event_types`

Runtime/transitional dictionary for trading-status event classification. Operators may input an `event_type_code`, and the dictionary may classify risk family/transition behavior. **It must not determine bar expectation by event type alone.** Authoritative `EXPECTED`/`NOT_EXPECTED`/`UNKNOWN` resolution belongs to the point-in-time trading-status/session contract and requires source-backed temporal evidence; `NOT_EXPECTED` requires verified evidence covering the full Regular-Market session.
Repository: `EventRiskSourceRepository`.

Canonical event type catalog:
- `SUSPENDED`: `risk_family=SUSPENSION`, `transition_type=START`, `carries_forward=1`; **bar expectation remains conditional on verified effective interval/full-session evidence**.
- `SUSPENSION_OBSERVED`: `risk_family=SUSPENSION`, `transition_type=OBSERVED`, `carries_forward=1`; proves only the source observation it actually carries. A long-suspension/current list does not retroactively prove every historical session.
- `UNSUSPENDED`: `risk_family=SUSPENSION`, `transition_type=END`, `clears_risk_family=SUSPENSION`; expected-bar resolution still uses the effective interval for the session.
- `SPECIAL_MONITORING_START`: `risk_family=SPECIAL_MONITORING`, `transition_type=START`, `carries_forward=1`; it is a risk fact and does not by itself alter expected-bar membership.
- `SPECIAL_MONITORING_END`: `risk_family=SPECIAL_MONITORING`, `transition_type=END`, `clears_risk_family=SPECIAL_MONITORING`.
- `UMA`: `risk_family=UMA`, `transition_type=POINT_IN_TIME`, `carries_forward=0`; it is a risk fact and does not by itself alter expected-bar membership.

Legacy runtime `expected_bar_policy` values may remain during migration, but they are **non-authoritative compatibility metadata**. A V2 resolver must persist/derive expectation from listing + trading session + temporal status revision and must expose `UNKNOWN` when full-session evidence is absent or conflicting.

Required fields:
- `event_type_code`
- `risk_family`
- `transition_type`
- `expected_bar_policy`
- `carries_forward`
- `clears_risk_family`
- `description`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `event_type_code`
- index `(risk_family, transition_type)`
- index `(expected_bar_policy)`

### `market_data_trading_status_events` — legacy runtime projection

Transitional runtime owner: source-backed trading-status event rows. V2 target identity is `listing_id` (plus `instrument_id` where useful), not current ticker identity. Each observation/revision must preserve source evidence, known/captured time, effective interval/session coverage, and revision identity. The legacy runtime table may still store ticker/date/event fields during migration. It intentionally does not store denormalized semantic columns such as `status_code`, `status_effect`, `event_risk_scope`, `coverage_exclusion_flag`, `is_suspended`, or `is_uma`; those meanings are resolved from `market_data_trading_status_event_types`.
Repository: `EventRiskSourceRepository`.
Import command: `market-data:events:import-trading-status`.

**V2 target:** stable `listing_id`, immutable source observation/revision, known/captured time, effective interval and full-session evidence are mandatory for authoritative point-in-time resolution. The legacy business key below must not decide historical truth or bar expectation by itself.

Daily import compatibility contract:
- required: `ticker_code`, `trade_date`, `event_type_code`
- optional: `source_name`, `source_ref`, `notes`
- sample file: `docs/market_data/examples/trading_status_daily.csv`
- forbidden legacy headers: `status_code`, `is_suspended`, `is_uma`, `status_effect`, `event_risk_scope`, `coverage_exclusion_flag`, `expected_bar_policy`

Required fields:
- `trading_status_id`
- `ticker_id`
- `ticker_code`
- `trade_date`
- `event_type_code`
- `source_name`
- `source_ref`
- `notes`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `trading_status_id`
- unique key `(ticker_id, trade_date, event_type_code, source_name)`
- index `(trade_date, ticker_id)`
- index `(event_type_code, trade_date)`

### `md_session_snapshots` — legacy runtime projection

Runtime owner: intraday/session snapshot persistence.  
Repository: `SessionSnapshotRepository`.

**V2 target:** snapshot identity uses stable `listing_id` and binds the publication/config context used to resolve effective-date scope. The legacy `(trade_date, snapshot_slot, ticker_id)` uniqueness below is migration inventory only.

Legacy runtime fields:
- `snapshot_id`
- `trade_date`
- `snapshot_slot`
- `ticker_id`
- `captured_at`
- `last_price`
- `prev_close`
- `chg_pct`
- `volume`
- `day_high`
- `day_low`
- `source`
- `run_id`
- `reason_code`
- `error_note`
- `created_at`
- `updated_at`

Required constraint/index:
- primary key `snapshot_id`
- unique key `(trade_date, snapshot_slot, ticker_id)`
- index `(trade_date, snapshot_slot)`
- index `(captured_at)`

### `md_replay_daily_metrics`

Replay expected-context fields are part of the DB schema contract and must stay synchronized with `ReplayResultRepository` and `ReplayVerificationService`.

Replay result status is explicit and must stay synchronized across runtime persistence, command output, and replay evidence export:

- `replay_status`: `PASS` for `MATCH`/`EXPECTED_DEGRADE`, `FAIL` for `MISMATCH`/`UNEXPECTED`, and `BLOCKED` for `NOT_ADMISSIBLE` as well as missing fixture/context/runtime prerequisites.
- `comparison_result`: `NOT_ADMISSIBLE` means the verifier refused to judge, not that it judged and found nothing wrong. It is emitted when the fixture's expectation was derived from the run being verified, where agreement would prove only that the run equals itself. It maps to `replay_status = BLOCKED` and must never be counted as a pass. Added 2026-08-11 with `F-025`; before that the value existed in code but not in the column, so an inadmissible replay failed to persist rather than being recorded.

Correction lifecycle replay fields are part of `md_replay_daily_metrics` and must remain synchronized with `ReplayResultRepository`, `ReplayVerificationService`, and replay evidence export:

- `correction_id`
- `correction_status`
- `correction_outcome`
- `correction_reseal_status`
- `correction_publication_switch`
- `baseline_publication_id`
- `candidate_publication_id`
- `expected_correction_id`
- `expected_correction_status`
- `expected_correction_outcome`
- `expected_correction_reseal_status`
- `expected_correction_publication_switch`
- `expected_baseline_publication_id`
- `expected_candidate_publication_id`

## Temporal interval convention (MD-B05-A001)

`Tickers_and_Identity_Dependency_Contract_LOCKED.md` permits either an exclusive or an inclusive
interval end and requires the choice to be one documented convention.
`Sector_Classification_Contract_LOCKED.md` states the same requirement for membership. The
convention was consistent in code and stated nowhere, which is the half of the requirement that was
not met.

Two record families, each internally consistent:

| Family | Tables | Column type | End boundary |
|---|---|---|---|
| Temporal identity | `md_listing_symbols`, `md_listing_boards`, `md_provider_symbol_mappings` | `DATETIME` | **Exclusive.** An interval covers `T` when `effective_from <= T 23:59:59` and (`effective_to IS NULL` or `effective_to > T 00:00:00`). A move recorded at `D 00:00:00` resolves the prior interval on `D-1` and the new one on `D`. |
| Sector membership | `ticker_sector_memberships` | `DATE` | **Inclusive.** An interval covers `T` when `effective_from <= T` and (`effective_to IS NULL` or `effective_to >= T`). A reclassification effective `R` closes the prior row at `R-1` and opens the new one at `R`. |

Both are asserted by execution rather than left to reading: `ListingBoardAndSegmentTemporalityTest`
and `TemporalIdentityFixturesTest` pin the exclusive boundary on each side of a move, and
`SectorMembershipTemporalFactTest` pins the inclusive one. A change to either convention breaks a
test rather than silently shifting historical answers by one session.

`NULL` in `effective_to` means the interval is open, never that the record is current-state. It
asserts that no closure was recorded, which is equally consistent with nothing having happened and
with nothing having been captured — the distinction
`Sector_Classification_Contract_LOCKED.md` draws in its capability boundary.

### `md_listing_boards` — temporal board and market segment

Runtime owner: effective-dated board and market-segment context for a listing.
Repository: `TemporalIdentityRepository`.

Board and market segment were single mutable columns on `md_listings`. Recording a move meant
overwriting one of them, which changed the answer for every historical date at once, and the
universe query filtered on the current segment — so a listing that was Regular on `T` and moved
afterwards silently left `T`'s universe. `md_listings.board_code` and `md_listings.market_segment`
remain as the cached current-state projection the identity contract permits; historical resolution
reads only this table.

Required fields:
- `listing_board_id`
- `listing_id`
- `market_segment`
- `board_code`
- `effective_from`
- `effective_to`
- `recorded_at` (known-time boundary)
- `retracted_at`
- `source_observation_id`
- `source_ref`
- `change_reason`

Required constraint/index:
- primary key `listing_board_id`
- unique key `(listing_id, effective_from, recorded_at)`
- index `(listing_id, effective_from, effective_to)`
- index `(market_segment, effective_from, effective_to)`

Resolver semantics are fail-closed: a listing with no interval covering `T` is not resolved from the
cached columns, and two intervals covering `T` raise `LISTING_BOARD_CONTEXT_AMBIGUOUS` rather than
preferring one.

## Database Dictionary Cross-Reference

For full table/column purpose, field role, date-key, identifier-key, and as-of usage rules, read:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
```

This document remains the coverage-gate metadata addendum. It does not replace the operational database dictionary.
