# Indicator Registry — Baseline (LOCKED)

This registry defines only the upstream baseline indicator set that downstream consumers may expect.
It does **not** define downstream screening, scoring, grouping, ranking, or portfolio logic.

## Mandatory baseline indicators
- `dv20_idr` using 20-day inclusive turnover average
- `atr14_pct` using 14-day Wilder ATR on real OHLC
- `vol_ratio` using current-day volume divided by average of prior 20 trading-day volumes
- `roc5` using `P(D)` versus `P(D[-5])`
- `roc10` using `P(D)` versus `P(D[-10])`
- `roc20` using `P(D)` versus `P(D[-20])`
- `hh20` using real high over the last 20 trading days inclusive of D
- `ll20` using real low over the last 20 trading days inclusive of D
- `close_to_hh20_pct`, `close_to_ll20_pct`, `range_20_pct`, and `range_position_20_pct` as 20-day range structure context
- `sector_code` as nullable source-backed IDX-IC sector membership context, not a technical formula
- `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` as nullable source-backed sector-index context

## Validity rule
If any mandatory baseline indicator is NULL because required history is unavailable, then:
- `eod_indicators.is_valid=0`
- `invalid_reason_code=IND_INSUFFICIENT_HISTORY`
- `eod_eligibility.eligible=0`
- `reason_code=ELIG_INSUFFICIENT_HISTORY`

`sector_code` is nullable and does not by itself invalidate the row. Missing sector membership means no source-backed sector classification exists for the ticker/date yet; implementations must not fabricate a placeholder sector.

`sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` are nullable and do not by themselves invalidate the row. Missing sector-index history means no source-backed sector rotation value exists for that date yet; implementations must not fabricate or forward-fill sector values.

---

## Amendment 2026-05-26 - Dependency horizon registry

The out-of-order import impact resolver must account for the active baseline indicator horizon in trading days.

Baseline dependency requirements:
- `dv20_idr`: 20
- `atr14_pct`: 15 conservative dependency days because TR needs prior close
- `vol_ratio`: 21 because it uses current day volume and the prior 20 trading days
- `roc5`: 6 because it uses D and D[-5]
- `roc10`: 11 because it uses D and D[-10]
- `roc20`: 21 because it uses D and D[-20]
- `hh20`: 20
- `ll20`: 20
- `ma20`: 20
- `ma50`: 50

The current maximum dependency horizon is `50` trading days. This is the source-of-truth floor used by the resolver unless a future registry version introduces a longer indicator dependency.

---

## Amendment 2026-05-27 - Reprocess executor registry note

The impact reprocess executor uses the same baseline dependency horizon resolved from this registry/config contract. It may recompute full affected dates even when only a subset of tickers changed. This is an intentional conservative execution scope until a per-ticker indicator writer is introduced.

The executor must not mark readable affected dates as recomputed in live current artifacts; those dates remain correction/republication cases.
