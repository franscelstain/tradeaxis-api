# Watchlist START_HERE and Build Sequence Navigation — 2026-08-18

## Role

Documentation-architecture history record. This document is not a strategy or implementation owner.

## Change

Added a single entry point and explicit end-to-end construction order for Weekly Swing:

- `docs/watchlist/START_HERE.md` — first page / strategy book order / build navigation.
- `docs/watchlist/implementation/weekly_swing/WS_IMPLEMENTATION_BUILD_SEQUENCE.md` — technical build orchestration `WS-B00..WS-B12` mapped to canonical `WS-S00..WS-S11`.

Updated active indexes/governance to point new contributors to the single entry point.

## Reason

Semantic filenames intentionally no longer encode chronology. A new contributor therefore needs an explicit first page and authoritative sequence rather than inferring order from filenames, campaign history, or old technical numbering.

## No Strategy Change

This change does not alter Weekly Swing eligibility, scoring, ranking, Top Picks, CONFIRM, backtest, OOS, friction, or production-proof semantics. Canonical strategy owners remain unchanged.

## Result

- Strategy reading order: Chapter 1..14.
- Implementation order: `WS-B00..WS-B12`.
- Core runtime completion: final qualified ranked Top Picks (`WS-S04` / `WS-B05`), with delivery hardening in `WS-B06`.
- CONFIRM remains optional/non-blocking (`WS-S05` / `WS-B07`).
- Proof path remains `WS-S06..WS-S11`.
