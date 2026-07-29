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
- `corporate_action_flag`, `corporate_action_types`, `trading_status_code`, `is_suspended`, `is_uma`, `event_risk_flag`, and `event_risk_reasons` as nullable source-backed event-risk context, not screening/scoring logic

## Validity rule
If any mandatory baseline indicator is NULL because required history is unavailable, then:
- `eod_indicators.is_valid=0`
- `invalid_reason_code=IND_INSUFFICIENT_HISTORY`
- `eod_eligibility.eligible=0`
- `reason_code=ELIG_INSUFFICIENT_HISTORY`

`sector_code` is nullable and does not by itself invalidate the row. Missing sector membership means no source-backed sector classification exists for the ticker/date yet; implementations must not fabricate a placeholder sector.

`sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg` are nullable and do not by themselves invalidate the row. Missing sector-index history means no source-backed sector rotation value exists for that date yet; implementations must not fabricate or forward-fill sector values.

Event-risk context fields are nullable and do not by themselves invalidate the row. Missing corporate-action/trading-status source rows or active source state means no source-backed event-risk context exists for that ticker/date yet; implementations must not fabricate `event_risk_flag=0` from absence. A `0` flag is valid only when an explicit source row/state reports non-risk status such as `ACTIVE`, `NORMAL`, `UNSUSPENDED`, or special-monitoring exit and no independent risk state remains active. Suspension and special-monitoring rows carry forward as independent source-backed risk states until their matching recognized clear event appears.

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

---

## Amendment 2026-07-29 - Corporate action window contamination

### Problem this amendment closes

Corporate action context was resolved on the exact `action_date` only. A price-scaling action such as
a stock split therefore left every dependent indicator window silently wrong for up to the full
dependency horizon, while `corporate_action_flag` marked a single day.

The failure is arithmetic, not policy. For a 1:4 split, `roc20` reports roughly -75%, `ma50` mixes two
price scales for 50 trading days, `hh20` retains a pre-split high, and `vol_ratio` reports roughly 4x
because share volume quadrupled. `vol_ratio` is the most damaging case: it does not merely degrade
data, it manufactures a volume-expansion signal that never happened.

Deciding what to do about a contaminated ticker belongs to the consumer. Refusing to publish a number
that is arithmetically meaningless belongs to this domain.

### Continuity impact source (LOCKED)

Price and volume continuity impact must be resolved from `market_data_corporate_action_types` as
defined in `Corporate_Action_Type_Registry_LOCKED.md`. Implementations must not infer impact from the
`action_type` string.

Unmapped action types resolve fail-safe to `SCALED` for both price and volume.

### Contamination predicate (LOCKED)

Let `W` be an indicator's contamination horizon in trading days, inclusive of `D`. Its window is:

    [ D[-(W-1)] ... D ]

A corporate action at trade date `A` contaminates that indicator if and only if:

    D[-(W-1)] < A <= D

Both bounds are deliberate:

- `A > D[-(W-1)]` — if `A` equals the window start, every bar in the window already sits on the
  post-action scale, so the window is clean. Quarantining it would discard valid data.
- `A <= D` — a future-dated action must never influence the current row. This preserves the existing
  no-future-leakage rule.

`A` is resolved on the trading-day sequence from `market_calendar`, never by calendar-day
subtraction, consistent with the traversal rule in `EOD_Indicators_Formula_Spec.md`.

### Per-indicator contamination horizons (LOCKED)

An indicator is contaminated by `price_continuity_impact != NONE`, by
`volume_continuity_impact != NONE`, or by both, according to which inputs it consumes.

| Indicator | Horizon `W` | Price-scale sensitive | Volume-scale sensitive |
|---|---:|:---:|:---:|
| `dv20_idr` | 20 | yes | yes |
| `atr14_pct` | see ATR note | yes | no |
| `vol_ratio` | 21 | no | yes |
| `roc5` | 6 | yes | no |
| `roc10` | 11 | yes | no |
| `roc20` | 21 | yes | no |
| `hh20` | 20 | yes | no |
| `ll20` | 20 | yes | no |
| `ma20` | 20 | yes | no |
| `ma50` | 50 | yes | no |
| `close_to_hh20_pct` | 20 | yes | no |
| `close_to_ll20_pct` | 20 | yes | no |
| `range_20_pct` | 20 | yes | no |
| `range_position_20_pct` | 20 | yes | no |
| `close_vs_ma20_pct` | 20 | yes | no |
| `close_vs_ma50_pct` | 50 | yes | no |
| `ma20_slope_pct` | 25 | yes | no |
| `rs_20_vs_ihsg` | 21 | yes | no |
| `rs_20_vs_sector` | 21 | yes | no |

`ma20_slope_pct` uses `ma20(D)` and `ma20(D[-5])`. The union of both windows spans `D[-24] ... D`,
so its horizon is 25, not 20.

`rs_20_vs_ihsg` and `rs_20_vs_sector` inherit `roc20`'s horizon because they are derived from the
equity `roc20` term. The benchmark term is not affected by a single ticker's corporate action.

`sector_code`, `sector_roc20`, and `sector_rs_20_vs_ihsg` are sector-index derived and are not
contaminated by an equity corporate action.

### ATR contamination horizon — resolved by widening, not by rewriting history

This registry declares `atr14_pct` to have a dependency horizon of 15 trading days. The current
implementation does not honour that declaration.

`IndicatorVectorService::wilderAtr` computes true range across every loaded bar, seeds the average
from the first 14 true ranges of the loaded window, then applies Wilder recursion forward to `D`.
`EodIndicatorsComputeService` loads a window of at least 60 bars. The value at `D` therefore depends
on roughly 60 bars, not 15, and the seed position moves with the configured load window.

The consequence for this amendment is direct: quarantining `atr14_pct` for 15 trading days would
declare the indicator clean while a split 40 trading days earlier still propagates through the
recursion.

Two resolutions were admissible:

1. Bound the ATR seed to the declared 15-day dependency horizon, making the declared horizon true,
   then apply `W = 15`.
2. Keep the current cumulative seed and set the ATR contamination horizon to the full loaded-bar
   dependency, making the quarantine truthful at the cost of a longer window.

**Resolution 2 is adopted.** Resolution 1 would change the computed `atr14_pct` value for every
historical row, including rows inside already sealed publications. That is a silent rewrite of
published historical data, which `docs/market_data/README.md` forbids outright. Correcting the ATR
seed remains desirable, but it is a deliberate correction-and-reseal exercise with its own audit
trail, not a side effect of adding a quarantine.

Under Resolution 2 the ATR contamination horizon is supplied by the caller as the actual loaded bar
window, currently `max(indicator windows, 55) + 5`. No stored value changes.

The declared 15-day dependency horizon in the table above therefore remains inaccurate for
`atr14_pct`, and the load-window sensitivity of the ATR seed remains an open determinism concern
independent of corporate actions. Both are recorded here so the next change to ATR starts from the
real behaviour rather than the declared one.

### Output rule (LOCKED)

For each `(trade_date, ticker_id)`:

1. Every contaminated indicator field is written as `NULL`.
2. Uncontaminated indicator fields are computed and written normally. Contamination is evaluated per
   indicator, not per row.
3. If at least one contaminated field is a mandatory baseline indicator, then
   `eod_indicators.is_valid=0` and `invalid_reason_code=IND_CORPORATE_ACTION_DISCONTINUITY`.
4. Eligibility for such a row is `eligible=0` with `reason_code=ELIG_CORPORATE_ACTION_DISCONTINUITY`.
5. `eod_indicators.corporate_action_window_reasons` records which actions caused the quarantine, as a
   sorted comma-joined list of `ACTION_TYPE_CODE@YYYY-MM-DD` tokens. It is `NULL` when no
   contamination applies.

Field 5 exists because a NULL indicator alone is ambiguous. `ma50` may be NULL because history is
still short, or because a split 30 trading days ago poisoned its window. Those are different
operational situations with different resolutions, and an audit trail that cannot separate them is
not an audit trail. The row-level `invalid_reason_code` cannot carry this either, since it reports a
single cause while contamination is evaluated per field.

The token carries the action date as well as the type because the contaminated field set is only
derivable from the pair. Given the tokens, the type dictionary, and the horizon table above, a
verifier can reconstruct the exact expected NULL pattern without re-reading source tables.

`corporate_action_window_reasons` is deliberately **excluded from `indicators_batch_hash`**. The
contamination decision itself is already hash-protected: `invalid_reason_code` and the NULL pattern
of the indicator fields are all in the hashed column set, so a replay that disagrees about
contamination still fails hash verification. Adding the annotation to the hash column list would
invalidate every existing sealed publication hash while adding no integrity that is not already
covered.

`IND_CORPORATE_ACTION_DISCONTINUITY` takes precedence over `IND_INSUFFICIENT_HISTORY` when both
apply. Reporting insufficient history for a ticker that actually split would misdescribe a known,
explainable cause as an unknown one, and would remove the operator's ability to find affected tickers
by reason code.

It does **not** take precedence over HARD structural codes such as `IND_MISSING_DEPENDENCY_BAR` or
`IND_INVALID_BAR_INPUT`. A missing or invalid canonical bar is a genuine data hole that an operator
must repair, whereas contamination resolves on its own as the window rolls forward. The more
actionable cause stays in `invalid_reason_code`; the quarantine trail is still recorded in
`corporate_action_window_reasons`, so no information is lost.

### Interaction with existing exact-date event risk (LOCKED)

`corporate_action_flag`, `corporate_action_types`, `trading_status_code`, `is_suspended`, `is_uma`,
`event_risk_flag`, and `event_risk_reasons` keep their existing exact-date semantics and are
unchanged by this amendment.

Contamination is tracked separately. A row 30 trading days after a split has
`corporate_action_flag = NULL` because nothing happened on that date, while its `ma50` is still
contaminated. Both statements are true simultaneously, and the model must be able to express both.

### Determinism requirement (LOCKED)

Contamination resolution is part of indicator determinism. For identical canonical bars, identical
corporate-action source rows, and an identical type dictionary, the contaminated field set must be
identical. Replay verification must reproduce the same NULL pattern and the same reason codes.

### Forbidden behavior (LOCKED)

- Forward-filling, interpolating, or otherwise reconstructing a contaminated indicator value.
- Emitting a contaminated indicator with `IND_INSUFFICIENT_HISTORY`.
- Applying contamination based on a future-dated corporate action.
- Substituting provider `adj_close` for quarantine. It does not repair `high`, `low`, or `volume`, so
  `hh20`, `ll20`, `atr14_pct`, and `vol_ratio` remain contaminated regardless of price basis.
