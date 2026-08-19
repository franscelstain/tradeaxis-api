# Weekly Swing — Market Data Intake Implementation Contract

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Role

Technical translation of `../../authority/strategy/WS_MARKET_DATA_INPUT_REQUIREMENTS.md`. This document does not own Market Data semantics or Weekly Swing thresholds.

## Allowed Source Surface

Runtime Watchlist repository/adapter reads only the producer-facing versioned read gateway/DTO defined by Market Data. It must not query Market Data raw/canonical/current/history/benchmark/status/publication tables as a normal consumer path.

Producer authority:
- `../../../market_data/book/CONSUMER_READ_CONTRACT_LOCKED.md`
- `../../../market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `../../../market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `../../../market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `../../../market_data/registry/Indicator_Registry_Baseline_LOCKED.md`

## Logical Intake DTO

The Watchlist adapter must preserve at least these logical fields when supplied by the producer contract:

### Snapshot context
- `requested_trade_date`
- governed calendar/session identity and completed-session relation when exposed
- `effective_trade_date`
- `publication_id` / publication version
- `read_model_version`
- `readiness_state`
- `freshness_state`
- `evaluated_at`
- config/factor/formula/indicator-set identities/hashes where exposed
- lineage reference

### Per-listing context
- stable `listing_id` / instrument identity
- temporal universe/listing membership and board/segment identity
- presentation symbol/code
- `data_usable` and upstream reason set
- RAW OHLCV fields exposed by read model
- active indicator fields and per-field validity/null reasons
- liquidity actual/proxy fields with explicit basis
- temporal status/event/sector/benchmark facts exposed by read model
- point-in-time exchange market-structure facts (minimum price, applicable tick/fraction tier, upper/lower price-band tier/revision) when requested by the active executable-price model

The internal DTO may rename transport fields for code style, but must retain a one-to-one semantic mapping and cannot become a new contract owner.

## Current-PLAN Readiness Decision Table

| Producer response | Watchlist current PLAN behavior |
|---|---|
| `READABLE + FRESH + effective=requested` | accept snapshot and continue |
| `READABLE + STALE/DEGRADED` with prior effective date | expose/record stale context if useful, but do not generate a PLAN labeled as requested date |
| `HELD` / `FAILED` / `BUILDING` / `SUPERSEDED` / `NOT_AVAILABLE` | no new PLAN from that requested date; return availability/not-ready outcome |
| mixed publication/version rows | reject intake as integrity error |
| missing publication/read-model identity required by contract | reject intake as integrity error |

The adapter does not inspect producer run/pointer tables to override the response.

## Per-Row Decision Table

| Upstream row state | Active required features | WS-S02 input state |
|---|---|---|
| `data_usable=false` | any | not recommendation-eligible; preserve reason facts |
| `data_usable=true` | all valid | may enter WS absolute eligibility |
| `data_usable=true` | one active required field null/invalid | not recommendation-eligible; do not zero-fill |
| `data_usable=true` | only optional field missing | evaluate independent active rules normally |

## Canonical Semantic Field Mapping

| Watchlist logical meaning | Producer semantic field | Compatibility handling |
|---|---|---|
| liquidity selection baseline | `adv20_close_volume_proxy_idr` | `dv20_idr` accepted only when producer contract/version declares it the exact alias |
| actual traded-value context | `adv20_traded_value_idr_actual` | never fallback silently from/to proxy |
| participation ratio | `vol_ratio_20` | older serialized `vol_ratio` may map only when producer version/formula proves identical semantics |
| risk volatility | `atr14_pct` | no local recompute |
| baseline momentum | `roc20` | no local recompute |
| breakout proximity | `close_to_hh20_pct` | no local close/hh reconstruction from internal tables |
| optional range position | `range_position_20_pct` | required only if active paramset uses it |
| calendar/session | producer governed Regular-Market calendar/session completion | no local weekday/holiday inference |
| temporal universe/listing | publication-bound membership/listing interval/board identity | no current-master projection into history |
| exchange executable-price structure | effective-dated min price, tick/fraction tier, upper/lower band revision | use only through governed producer context/reference adapter; no current-tier hardcode |
| sector rotation context | `sector_code`, `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg` | never reconstruct current sector/benchmark tables |
| producer usability | `data_usable` | legacy `eligible` maps only to upstream usability, never WS eligibility |

Any transport alias not explicitly proven equivalent by the producer read-model version is an integration gap, not something the Watchlist adapter may guess.

If an active Weekly Swing identity requires a benchmark/regime fact not exposed by the selected producer read-model version, the adapter reports that dependency unavailable. It must not query `market_benchmark_*` internals or recreate the benchmark formula locally.

## Lineage Persistence

PLAN/replay evidence must persist or hash-bind enough snapshot identity to prove what Market Data publication was used, including requested/effective dates, publication identity, and read-model/config/formula identity available from the producer response.

Watchlist persistence does not need to mirror producer internal `run_id`, pointer row, or table primary keys unless they are explicitly part of the consumer response contract.

## Historical Replay

Backtest/OOS adapter requests exact/as-known data under the producer replay/read contract. A missing historical date is preserved as missing/insufficient evidence. No `latest`, `MAX(date)`, current-master, or prior-date substitution.

## CONFIRM

This EOD intake adapter does not promise D+1 intraday/current data. CONFIRM must use a separately governed decision-time adapter if one exists. Absence of that adapter/data yields the optional CONFIRM availability state and does not fail the EOD core.

## Forbidden Implementation Patterns

- direct `eod_bars`, `eod_indicators`, `eod_eligibility`, benchmark/status/current-pointer queries as normal Watchlist runtime intake;
- `MAX(trade_date)` / `latest()` date discovery;
- producer-internal run/pointer checks used as a replacement for consumer readiness;
- local indicator/adjustment/sector/status/data-usability recomputation;
- actual/proxy liquidity substitution without new strategy identity;
- default `0` for missing required features;
- mixing rows from multiple publication/config/formula identities.

## Alignment Status

This contract is the current translation target. Existing code/schema/guidance that assumes direct Market Data table mappings remains `ALIGNMENT_PENDING` until audited and changed.
