# Indicator Computation Specification (EOD)

## Role of this document
This file is an implementation-facing companion specification.
The normative owner for indicator semantics remains `../book/EOD_Indicators_Contract.md`, with formula authority in `EOD_Indicators_Formula_Spec.md`.

This file may clarify computation order, warmup handling, and storage-facing implementation detail, but it must not redefine indicator identity, publication-context behavior, validity ownership, or downstream-read semantics that are already locked in the book-level contracts.

## Input source
Input comes only from canonical `eod_bars` for valid trading days.
Rows from `eod_invalid_bars` must never participate in indicator computation.

## Trading-day ordering (LOCKED)
For each ticker, bars are ordered by market-calendar trading day ascending.
All windows below are counted in trading days.

Notation:
- `D` = effective trade date being computed
- `D[-1]` = previous trading day
- `D[-N]` = Nth prior trading day relative to D
- `window(D, N)` = `D[-(N-1)] ... D`, inclusive, total N rows

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

### 7) `roc20`
`roc20(D) = (P(D) / P(D[-20])) - 1`
Requires both `P(D)` and `P(D[-20])`.
This is a pure ratio, not a percentage-multiplied-by-100 field.

### 8) `hh20`
`hh20(D) = MAX(high(x))` over `window(D, 20)`.
This is based on real highs, not adjusted price basis.

## Null policy (LOCKED)
- No forward-fill.
- No zero-fill.
- No calendar-gap interpolation.
- Missing required history => indicator NULL and `is_valid=0`.
- Optional/non-baseline indicators may be NULL without invalidating the row only if explicitly declared in a future registry version.

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
- `roc20`: current date plus D[-20]
- `hh20`: 20 trading days inclusive
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
