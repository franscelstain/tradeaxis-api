# Indicator Computation Specification (EOD)

## Role of this document
This file is an implementation-facing companion specification.
The normative owner for indicator semantics remains `../book/EOD_Indicators_Contract.md`, with formula authority in `EOD_Indicators_Formula_Spec.md`.

This file may clarify computation order, warmup handling, and storage-facing implementation detail, but it must not redefine indicator identity, publication-context behavior, validity ownership, or downstream-read semantics that are already locked in the book-level contracts.

## Input source
Input comes only from canonical `eod_bars` for valid trading days.
Rows from `eod_invalid_bars` must never participate in indicator computation.
Nullable `sector_code` context comes only from `ticker_sector_memberships` joined to active `market_data_sectors` for the requested trade date; missing membership must remain NULL and must not be forward-filled or faked.
Nullable sector-rotation context comes only from `market_benchmark_indicators` for the active sector index code on D; missing sector-index bars/benchmark indicators must leave `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` NULL.
Nullable event-risk context comes only from `market_data_corporate_actions`, canonical `market_data_trading_status_events`, and the seeded dictionary `market_data_trading_status_event_types`. Corporate actions and `UMA` are exact-date context. `SUSPENDED`, `SUSPENSION_OBSERVED`, and `SPECIAL_MONITORING_START` are stateful source-backed event states that carry forward until `UNSUSPENDED` or `SPECIAL_MONITORING_END`. `SUSPENDED` and `SUSPENSION_OBSERVED` both resolve to `BAR_NOT_REQUIRED`; special monitoring and UMA remain bar-required event-risk context. Missing source rows/state must leave `corporate_action_flag`, `corporate_action_types`, `trading_status_code`, `is_suspended`, `is_uma`, `event_risk_flag`, and `event_risk_reasons` NULL. `ACTIVE` is not a source event and must not be fabricated from absence. `eod_indicators.trading_status_code` stores one canonical primary code only (`SUSPENDED`, `SUSPENSION_OBSERVED`, `UNSUSPENDED`, `SPECIAL_MONITORING_START`, `SPECIAL_MONITORING_END`, `UMA`, or NULL); composite risk context belongs in `event_risk_reasons`.

## Trading-day ordering (LOCKED)
For each ticker, bars are ordered by market-calendar trading day ascending.
All windows below are counted in trading days.

Notation:
- `D` = effective trade date being computed
- `D[-1]` = previous trading day
- `D[-N]` = Nth prior trading day relative to D
- `window(D, N)` = `D[-(N-1)] ... D`, inclusive, total N rows

Window loader contract:
- equity indicator loaders must resolve their start boundary from `market_calendar`, not calendar-day subtraction
- benchmark/sector indicator loaders must use the same market-calendar dependency rule
- requested date must be present as an active trading day in `market_calendar`; otherwise computation must fail safely before publication
- insufficient prior trading-day span at the beginning of the available dataset is not a global error; loaders use the earliest available trading date and each indicator applies its own NULL rule
- a valid loader window does not fake valid prices; per-ticker indicators remain NULL only for the fields whose dependencies are missing, insufficient, or invalid

## Price basis (LOCKED)
`P(D)` is defined as:
- `adj_close`, when `PRICE_BASIS_DEFAULT=ADJ_CLOSE` and `adj_close IS NOT NULL`
- otherwise `close`

This fallback is applied independently per date in the required lookback window.
`ATR` and `TR` always use real `high`, `low`, and previous real `close`.

## Baseline formulas (LOCKED)
### 1) Turnover normalization
`turnover_idr(D) = close(D) * volume(D) * LOT_SIZE`
Where `LOT_SIZE` is taken from the config registry. Default for IDX equities is 100.

### 2) `dv20_idr`
`dv20_idr(D) = AVG(turnover_idr(x))` over `window(D, 20)`.
Requires 20 canonical bars including D.

### 3) `TR(D)`
`TR(D) = MAX(high(D) - low(D), ABS(high(D) - close(D[-1])), ABS(low(D) - close(D[-1])))`
Requires current bar and previous canonical bar.

### 4) `ATR14 Wilder`
Seed at the first date with 14 available TR values:
`ATR14_seed = AVG(TR(x))` over the first 14 TR observations.
Recursive form afterward:
`ATR14(D) = ((ATR14(D[-1]) * 13) + TR(D)) / 14`
Requires trading-day continuity per ticker.

Warmup implication:
- because `TR(D)` itself requires `close(D[-1])`, the first computable `ATR14_seed` date is the trade date that has 15 canonical bars available for the ticker
- before that point, `ATR14` and `atr14_pct` are NULL

### 5) `atr14_pct`
`atr14_pct(D) = ATR14(D) / P(D)`
If `close(D) <= 0` the source bar is invalid and the indicator must not be computed.

### 6) `vol_ratio`
`vol_ratio(D) = volume(D) / AVG(volume(x))` over `window(D[-1], 20)`
This uses the current day volume divided by the average volume of the **20 prior trading days excluding D**.
Requires 21 canonical bars total: D plus D[-1]..D[-20].

### 7) `roc5`, `roc10`, `roc20`
`roc5(D) = (P(D) / P(D[-5])) - 1`
`roc10(D) = (P(D) / P(D[-10])) - 1`
`roc20(D) = (P(D) / P(D[-20])) - 1`
Requires both current price basis and the requested lookback price basis.
These are pure ratios, not percentage-multiplied-by-100 fields.

### 8) `hh20` and `ll20`
`hh20(D) = MAX(high(x))` over `window(D, 20)`.
`ll20(D) = MIN(low(x))` over `window(D, 20)`.
These are based on real highs/lows, not adjusted price basis.

### 9) Range structure
`close_to_hh20_pct(D) = ((P(D) - hh20(D)) / hh20(D)) * 100`
`close_to_ll20_pct(D) = ((P(D) - ll20(D)) / ll20(D)) * 100`
`range_20_pct(D) = ((hh20(D) - ll20(D)) / ll20(D)) * 100`
`range_position_20_pct(D) = ((P(D) - ll20(D)) / (hh20(D) - ll20(D))) * 100`

If `ll20 <= 0`, percentage fields that divide by `ll20` are NULL. If `hh20 - ll20 <= 0`, `range_position_20_pct` is NULL.

### 10) Sector rotation context
`sector_roc20(D)` is the active sector-index benchmark `roc_20` for the ticker's source-backed `sector_code` on D.
`rs_20_vs_sector(D) = (roc20(D) * 100) - sector_roc20(D)`.
`sector_rs_20_vs_ihsg(D) = sector_roc20(D) - IHSG_roc_20(D)`.

`roc20` on the equity row remains a ratio. `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` are percentage/percentage-point fields aligned with benchmark `roc_20`.

### 11) Event-risk source context
`corporate_action_flag(D)` is `1` when at least one corporate-action source row exists for ticker/date D.
`corporate_action_types(D)` is a deterministic comma-separated list of source-backed action types for D.
`trading_status_code(D)` is a single deterministic primary canonical source-backed projection code, never a comma-separated list. Allowed non-null values are `SUSPENDED`, `SUSPENSION_OBSERVED`, `UNSUSPENDED`, `SPECIAL_MONITORING_START`, `SPECIAL_MONITORING_END`, and `UMA`; no-source/no-active-trading-status rows remain NULL. Exact `UNSUSPENDED` and `SPECIAL_MONITORING_END` source events may appear only on their source dates; active carry-forward state is represented by the current primary code for stateful statuses.
`is_suspended(D)` and `is_uma(D)` are derived nullable projection fields, not source-event columns. `SUSPENDED` and `SUSPENSION_OBSERVED` set/carry `is_suspended=1` until `UNSUSPENDED`; `UMA` sets `is_uma=1` only on the exact event date. `SPECIAL_MONITORING_START` / `SPECIAL_MONITORING_END` carry or clear special-monitoring event-risk context but do not create or clear suspension.
`event_risk_flag(D)` is `1` when corporate action, suspension, UMA, special-monitoring source context/state, or another independent event-risk context exists. It may be `0` on exact clear events such as `UNSUSPENDED` or `SPECIAL_MONITORING_END` when no independent risk state remains active. It remains `NULL` when no event-risk source row/state exists for the ticker/date.
`event_risk_reasons(D)` is a deterministic comma-separated list of source-backed risk reasons and is the only place where multi-risk context is combined.

## Runtime proof - 2026-07-05 trading-status projection normalization
Operator-local final validation proved the canonical projection contract: targeted projection tests, audit-doc guards, StaticGuard, and full MarketData PHPUnit passed; current-indicator recompute from existing current bars passed for the affected historical/current range through 2026-06-29; and global DB validation returned zero legacy `trading_status_code` values. The final non-null current code set is canonical-only: `SPECIAL_MONITORING_END`, `SPECIAL_MONITORING_START`, `SUSPENDED`, `UMA`, and `UNSUSPENDED` in the current data state, with `SUSPENSION_OBSERVED` allowed by contract when a bar/indicator row exists for a suspension-observed ticker/date.

## Source/master immutability during recompute (LOCKED)
Indicator recompute from existing data, if introduced as a future approved command, must be read-only against source/master tables and `eod_bars`. Publication-bound indicator artifacts may be regenerated from existing source/master state.

Therefore, source context fields such as sector, corporate action, trading status, suspension, UMA, and event-risk may be recalculated in the new publication from existing source rows. This must not be misread as importing or mutating the source tables themselves.

## Nullability and missing-bar policy (LOCKED)
- No forward-fill.
- No calendar-gap interpolation.
- Indicator nullability is per field, not per row: the fields whose dependencies are satisfied must still be computed even when another field is NULL.
- Missing required history for a specific formula => that formula output is NULL and its validity/reason context reflects insufficient history; it must not fail the whole publication date.
- A ticker newly listed after the dataset start accumulates indicators gradually according to the bars actually available since `listed_date`; MA20, MA50, ROC20, ATR14, and volume-derived fields may become non-NULL on different dates.
- A zero OHLCV placeholder for a ticker active on a valid trading day is allowed only as a publication-completeness representation of a missing provider row. It is not a valid market price input for price-based indicators.
- When `open/high/low/close/adj_close <= 0` because a row is a missing-provider placeholder, price-based and turnover indicators depending on that input must be NULL or invalid for that field; other fields with valid dependencies may still compute.

## Rounding/storage (LOCKED)
Store using output column precision defined in schema.
Hash serialization formatting is governed separately by `../book/Hash_Number_Formatting_LOCKED.md`.

---

## Amendment 2026-05-26 - Mutation dependency horizon

Indicator recomputation impact must be resolved in market-calendar trading days.

The baseline dependency horizon must include all active baseline dependencies:
- `dv20_idr`: 20 trading days inclusive
- `atr14_pct`: 14 Wilder TR values plus prior close dependency
- `vol_ratio`: current date plus 20 prior trading days
- `roc5`: current date plus D[-5]
- `roc10`: current date plus D[-10]
- `roc20`: current date plus D[-20]
- `hh20`: 20 trading days inclusive
- `ll20`: 20 trading days inclusive
- `ma20`: 20 trading days inclusive
- `ma50`: 50 trading days inclusive

The runtime resolver must use a single horizon source derived from indicator config plus the MA50 floor. For the current baseline registry this resolves to `max_indicator_dependency_trading_days=50`.

If a changed bar occurs on trading date T, affected indicator dates are T through the later downstream trading dates up to the dependency horizon, capped by the last available canonical bar date. If affected downstream dates include a readable publication, downstream derived artifacts require correction/republication handling rather than silent mutation.

---

## Amendment 2026-05-27 - Execution proof for impact reprocess

Affected-date detection is not enough. After a changed historical bar is accepted, non-readable affected dates must run indicator recompute. The current execution scope is full affected date, because the indicator compute service writes deterministic date-scoped artifacts.

Execution proof fields:
- `indicator_reprocess_execution_summary.execution_state`
- `indicator_reprocess_execution_summary.reprocessed_trade_date_count`
- `indicator_reprocess_execution_summary.reprocess_scope`
- `indicator_reprocess_execution_summary.blocked_reason_code`
- `indicator_reprocess_execution_summary.failure_reason_code`

`EXECUTED` is valid only when compute ran. `NOOP` is valid for unchanged bars/no affected dates. `BLOCKED` is valid for readable affected dates that require correction.


## Amendment 2026-06-05 - Market-calendar window loading
MA50 and sector-rotation audit found that repository/window loading must be locked separately from formula semantics.

Formula rules did not change:
- `ma50` requires 50 canonical trading bars for the ticker
- `roc20` requires current price basis and D[-20]
- sector rotation requires source-backed sector benchmark indicators for D

Implementation rules are now explicit:
- load equity bars using a market-calendar trading-date window large enough for the maximum configured dependency horizon when available
- when the requested date is near the beginning of the dataset and fewer prior trading dates exist, use the available window and let each indicator emit NULL according to its own dependency rule
- load benchmark/sector bars using the same market-calendar trading-date window rule
- lifecycle source-acquisition warmup must be based on trading days, not calendar days, while historical dataset bootstrap may start at the first available trading date
- missing sector-index bars for D leave sector fields NULL until the sector source is imported and the affected date is recomputed/promoted

This amendment prevents long IDX holiday periods from starving rolling indicators such as MA50 when sufficient historical OHLC bars exist, without turning expected early-dataset/ticker-listed-date insufficient history into a publication error.
