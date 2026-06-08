# Market Calendar Requirements Contract (Global Dependency)

## Ownership note
Market Data Platform depends on a shared market-calendar foundation.
This document defines what this domain requires from that dependency.
It does not make `market_data` the owner of the shared calendar itself, and it does not move bars/indicators ownership back to the shared foundation.

## Required fields
- `trade_date`
- `is_trading_day`
- `prev_trading_day`
- `next_trading_day`

## Recommended fields
- `session_open_time`
- `session_close_time`
- `is_half_day`

## Locked usage rules
- Trading-day windows must follow `prev_trading_day` / `next_trading_day`, never subtract calendar days.
- `D[-N]` is resolved by walking the market calendar N times through `prev_trading_day`.
- Indicator dependency windows, benchmark dependency windows, mutation-impact horizons, and lifecycle API warmup windows are all market-calendar dependencies.
- If the requested date is missing from `market_calendar` or is not an active trading day, requested-date processing must not finalize `SUCCESS`.
- If fewer prior trading dates exist because the requested date is near the beginning of the available dataset, processing must use the available trading-date window and allow per-indicator NULL outputs; this is not a calendar integrity failure.
- Calendar-day overfetch may be used only as a provider/network fetch convenience after the authoritative trading-date window has already been resolved; it must not decide the dependency boundary.

## Latest trade-date resolution
- if current date is a trading day and the platform is past cutoff, latest trade date may resolve to today
- otherwise latest trade date resolves to the prior trading day


## Amendment 2026-06-05 - Warmup and indicator dependency windows
The following windows must resolve from `market_calendar` trading-day sequence:
- equity indicator history loaders, including MA50, MA20, ROC20, DV20, ATR14, HH20/LL20, and volume-ratio dependencies
- benchmark/sector indicator history loaders, including sector ROC20 and sector-vs-IHSG context
- lifecycle API source-acquisition warmup start
- out-of-order mutation downstream impact horizon

Required failure behavior:
- a requested date not present as an active trading day must block before indicator compute/promotion
- an insufficient prior trading-day window at dataset start must not block publication by itself; each indicator must emit NULL only when its own dependencies are not met
- missing sector-index source bars remain a source-data gap, not a reason to invent sector rotation values
