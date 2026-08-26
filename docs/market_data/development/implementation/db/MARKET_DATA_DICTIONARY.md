# Market Data Database Dictionary

Status: **strategy-corrected transitional dictionary; production enforcement open**.

## Reading rule

The logical model below is authoritative for meaning. Physical rollout is the base `Database_Schema_MariaDB.sql` plus forward migrations. Fields introduced by `2026_08_02_000001_add_market_data_strategy_v2_foundation.php` are nullable during adoption; null provenance/config/factor/publication bindings cannot pass seal/readability.

Stable `listing_id` is the historical security key. Legacy `ticker_id`/`ticker_code`, live tables, and current master rows are compatibility projections and may not be used to infer point-in-time identity.

## Time vocabulary

- `effective_from`/`effective_to` or a market date: when a fact applies in market time.
- `recorded_at`: when the platform knew/recorded the revision.
- `retracted_at`: when that recorded assertion ceased to be an accepted as-known revision.
- `created_at`: storage event, not a substitute for either semantic time.

Publication replay uses frozen revision identities. As-known replay resolves both effective and recorded time against its target/knowledge cutoff.

## Acquisition and configuration

### `md_source_observations`

Immutable evidence envelope for every response/file/failed attempt.

| Field group | Meaning |
|---|---|
| `source_observation_id`, `observation_uid` | stable storage and external identities |
| `run_id`, `attempt_uid`, `requested_trade_date` | execution context |
| `source_name`, `provider`, `provider_symbol`, `provider_mapping_id` | selected source and temporal routing context |
| `sanitized_request_identity` | URL/file/request identity with credentials removed |
| `response_status`, `content_type`, `source_timestamp`, `acquired_at` | transport/source time evidence |
| `schema_fingerprint`, `adapter_version` | decoding contract |
| `payload_hash`, `payload_ref`, `bounded_payload_body` | retained content identity/location; bounded body is optional |
| `outcome_state`, `reason_code` | complete/partial/failed/stale/schema-invalid/quarantined outcome |
| `supersedes_observation_id` | refetch/correction lineage; never overwrite predecessor |

Payload and request fields must not contain secrets. Canonical rows link only to an accepted observation; non-accepted rows remain evidence.

### `md_config_snapshots`

Immutable full output-affecting configuration.

`snapshot_uid`, schema/serialization versions, canonical `resolved_config_json`, `config_hash`, registry revision, effective/recorded timestamps, build, environment profile, resolver version, and creation time are mandatory. Secret values are absent; only sanitized credential profile/revision identifiers may appear.

The same snapshot binds run, artifacts, publication lineage, manifest, and seal.

## Temporal identity

### `md_issuers`

Stable legal entity: `issuer_id`, `issuer_uid`, legal name, recorded/created times.

### `md_instruments`

Stable financial instrument issued by an issuer: `instrument_id`, `instrument_uid`, `issuer_id`, instrument type, currency, recorded/created times.

### `md_listings`

Exchange listing of an instrument: stable `listing_id`/`listing_uid`, `instrument_id`, exchange/board, listed/delisted dates, recorded/created times. Delisted rows remain historical members where applicable.

### `md_listing_symbols`

Bitemporal exchange/display symbol assertions: listing, symbol/type, effective interval, recorded/retracted time, and source observation. Symbol reuse is allowed across non-overlapping listing/revision context; symbol text is never identity.

### `md_provider_symbol_mappings`

Bitemporal provider routing: listing, provider/provider symbol, effective interval, recorded/retracted time, source observation, mapping revision. `.JK` or any suffix is mapping data, not an inferred identity rule.

### Legacy `tickers`

Compatibility/current projection. `is_active`, `ticker_code`, company name, board/exchange, and listed/delisted dates do not replace the temporal identity tables. Historical queries must not filter by today's `is_active` or join by current code alone.

## Calendar, status, sector, and benchmarks

### `md_market_calendar_revisions`

Immutable market/date revision with timezone `Asia/Jakarta`, session state/open/close/completion, recorded time, source observation, and supersession. It owns latest completed Regular-Market session semantics.

### `market_calendar`

Legacy current calendar projection (`cal_date`, trading-day/session fields). It may serve current operations only when its revision binding is explicit; it is not as-known replay authority.

### `md_trading_status_revisions`

Bitemporal listing status with `bar_expectation_state`, full-session verification, effective interval, recorded/retracted time, source observation, and supersession. Only verified full-session status can produce `BAR_NOT_EXPECTED`; absence/unknown cannot.

### Legacy trading-status tables

`market_data_trading_status_event_types` remains a registry/projection and `market_data_trading_status_events` retains source events. Historical/readability logic migrates to immutable listing-bound revisions; current ticker code and event absence cannot create “active/no risk.”

### Sector and benchmark tables

`market_data_sectors`, `ticker_sector_memberships`, `market_benchmarks`, `market_benchmark_bars`, and `market_benchmark_indicators` hold source-backed taxonomy/membership/index context. Membership and benchmark revisions used by output must be frozen/as-known. Missing sector/benchmark data yields dependent null reasons, never fabricated values.

## Corporate actions and price products

### `md_corporate_action_revisions`

Immutable `(event_uid, revision_number)` lifecycle: listing, type, lifecycle/verification state, ex/cum/record/payment dates, versioned terms JSON, source observation, effective/recorded times, and superseded revision.

Only authoritative or explicitly operator-verified complete revisions are factor-eligible. Synthetic price-break candidates remain unverified/quarantined.

### `md_adjustment_factor_sets`

Immutable analytical product factor-set identity: product, factor formula version, full config snapshot, state, content hash, and recorded/created times.

### `md_adjustment_factors`

Per-listing effective factor intervals with coherent price factor, optional volume factor, and mandatory verified corporate-action revision link. Append-only revisions; no raw bar rewrite.

### Legacy `market_data_corporate_actions`

Transitional observation/projection. Dates/terms/factor-like columns are not by themselves verified factor authority. `adjustment_source` must never authorize `DERIVED_FROM_PRICE_SERIES` behavior.

### `market_data_price_scale_breaks`

Detector evidence only: discontinuity measurements, match/review context, and detector version. It has no repair factor/range/count/time fields and must never drive direct updates of `eod_bars` or `eod_bars_history`.

## Canonical bar artifacts

### `eod_bars_history`

Authoritative immutable `publication_id × trade_date × listing` snapshot. During transition it also carries legacy `ticker_id`.

Core fields are Regular-Market `RAW` OHLCV plus optional source-backed `previous_close`, `traded_value_idr_actual`, `trade_count_actual`, board/session/status and timestamps. `source_observation_id`, config snapshot, canonicalization version, product code, and quality state preserve lineage.

OHLC must be positive and internally consistent; volume is non-negative. Missing/invalid observations are separate evidence. Provider `adj_close` is legacy lineage only and cannot become RAW close or structural adjustment input.

### `eod_bars`

Replaceable current projection of the active publication. It shares bar meaning/bindings but is not historical authority. Consumers use the read gateway, not this table directly.

### `eod_invalid_bars`

Rejected normalized/canonical candidates with observation/run/date/listing context, values, reason set, and duplicate relation. A rejected row never leaks into a readable artifact and is never “fixed” in place.

## Indicators and daily metrics

### `eod_indicators_history`

Authoritative immutable publication-bound indicators. Bindings include listing, config snapshot, factor set, coherent `STRUCTURAL_ADJUSTED` product, formula/indicator registry version, recursive ATR state reference, field null reasons, sector/event/status context, and run.

`adv20_traded_value_idr_actual` uses complete source-backed actual traded value. `adv20_close_volume_proxy_idr` is explicitly `RAW close × RAW volume`; legacy `dv20_idr` aliases only the proxy. `atr14`/`atr14_pct` use the stable Wilder seed/state and are not sliding-window reseeded.

### `eod_indicators`

Current projection of the active publication. It is not replay or historical authority.

### Benchmark/daily aggregates

Aggregates expose actual and proxy values separately and include temporal universe, completeness/partialness, source publication/config, and reason metadata. Cross-sectional totals do not become a substitute for per-listing delivery/quality.

## Eligibility

### `eod_eligibility_history`

One immutable row per publication/date/listing containing separate universe membership, bar expectation, delivery, canonical quality, liquidity, temporal status, event risk, final upstream data-usability decision, complete reasons JSON, config, and run/publication context. Existing eligibility field/table names are compatibility naming and must not encode downstream tradability or watchlist policy.

The legacy `eligible` and primary `reason_code` fields are compatibility summaries. Eligibility is not alpha/ranking and does not alone imply publication readability.

### `eod_eligibility`

Current projection only. Consumers receive the publication-bound read model.

## Runs, publications, and lineage

### `eod_runs`

Execution lifecycle and outcome, requested/effective dates, source/retry evidence, independent expected/unknown/delivered/delivered-valid coverage counts, quality counts, artifact hashes, full config snapshot, observation manifest, correction/publication context, activation/freshness state, and latest expected/acquired/canonicalized/readable dates.

`coverage_universe_count` remains required raw-universe evidence and is never the denominator.
`coverage_available_count` and legacy interpretations of `coverage_missing_count` are compatibility
metrics; new decisions use explicit expected/unknown/delivered/delivered-valid fields. Compatibility
fields must not shrink the denominator, impersonate another stored field during export, or satisfy
full-config proof. `config_version/hash/ref` and repair-like status values retain their separately
documented transitional boundaries.

### `eod_run_events`

Append-only structured stage/reason evidence. Logs do not replace domain tables or immutable observations.

### `eod_publications`

Immutable versioned publication metadata: run/date/version/supersession, hashes, seal, config/factor/observation/manifest binding, product/read-model version, and readiness state. `is_current` is compatibility metadata; pointer is current authority.

### `md_publication_lineage_bindings`

One row per publication binding config/factor plus observation, identity, calendar, status, event revision-set hashes, formula/build/read-model versions. All non-applicable nullable factor fields are explicit; required bindings are non-null before seal.

### `eod_current_publication_pointer`

Atomic normal-read authority. One pointer per product/date scope is the target model; the legacy date-only key requires expansion before multiple products/markets are activated. A switch never changes snapshot content.

### `eod_dataset_corrections`

Audited request/approval/candidate/reseal/pointer lifecycle. Legacy `REPAIR_*` values must not authorize mutation and require cleanup/enforcement migration. Original and replacement publication IDs remain distinct.

### `md_corpus_admission_decisions`

Immutable Stage 8 decision that distinguishes the intentional historical start from the first
measured conformant/readable suffix. It records market/product scope, `intentional_dataset_start`,
`admitted_from`, `measured_through`, coverage threshold/source mode, accepted status snapshot and
transition-search observations, measurement campaign, canonical measurement/status hashes,
algorithm version, complete measurement JSON, state/reason, supersession, and recorded time.

Its ID binds the reconstruction campaign, owning run, sealed publication, and publication lineage.
Eligibility rows bind the exact status revision/source observation used for verified
`BAR_NOT_EXPECTED` decisions. Dates before the active boundary remain immutable/non-readable and
cannot be indicator warm-up; admission is never a relabel or movement of dataset start.

### `md_stage8_reconstruction_campaigns` and `md_stage8_reconstruction_targets`

Campaigns freeze exact scope, baseline maximum publication, target-set hash, admission and campaign
supersession identities, state/result, and lifecycle times. Targets preserve per-date baseline
publication/run/version plus three sealed hashes and three provenance snapshot hashes, then append
correction/replacement identities and terminal checkpoint state. A superseded or failed attempt is
retained; only the normal correction/seal/finalize lifecycle can replace a current pointer.

## Replay and evidence tables

`md_replay_daily_metrics`, `md_replay_reason_code_counts`, session snapshots, and evidence artifacts retain explicit replay mode, fixture/knowledge context, frozen identities/config/revisions, expected/actual states, hashes, reasons, and admission result. Existing replay tables are transitional until they bind all V2 inputs.

## Consumer mapping

Normal consumers read the versioned market-data gateway, whose minimum DTO binds exactly one publication and exposes RAW facts, coherent structural-adjusted product, indicators, data-usability facts, lineage, requested/effective dates, readiness, and freshness. Direct current/history/master joins are forbidden.

## Synchronization gate

Update this dictionary with every semantic/migration change. Production relock additionally requires repositories and SQLite mirror to adopt every used field, followed by MariaDB clean-install/upgrade dumps and semantic negative tests. The current nullable V2 foundation is implementation progress, not closure.

## B11 verified corporate-action lifecycle evidence

### `md_price_scale_break_candidates`
Append-only diagnostic discontinuity candidates bound to stable listing identity, adjacent verified calendar observations, publication/source-observation references, detector/config identity, and an explicit diagnostic linkage state. A row is **not** a verified corporate action, ex-date, action type, or factor.

### `md_price_scale_break_candidate_reviews`
Append-only operator review revisions. `DISMISSED` requires positive source evidence; `LINKED_VERIFIED_FACTOR` requires an explicit V2 corporate-action revision and only releases quarantine when a governed adjustment-factor row binds that exact revision.

### `md_corporate_action_reconciliations`
Bidirectional exchange/CSD reconciliation evidence for recorded verified corporate actions. `scope_complete=0` is a qualified incomplete check and may not support an action-complete historical claim. Full-scope qualification must begin at the intentional Market Data dataset start.
