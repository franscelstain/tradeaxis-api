# EOD Indicators Contract (LOCKED)

## Purpose
Define the authoritative upstream indicator artifact for one trade date D.

This contract governs:
- indicator row identity
- minimum fields
- publication-context semantics
- validity semantics
- dependency semantics
- null/invalid behavior
- deterministic interpretation

This document complements:
- `../indicators/EOD_Indicators_Formula_Spec.md`
- indicator test fixtures
- eligibility contracts

## Output identity
For the live current readable indicators table, there must be at most one indicator row per `(trade_date, ticker_id)`.

Minimum logical row identity:
- `trade_date`
- `ticker_id`

`publication_id` is mandatory publication context for the current readable row, but it is not a second competing live-table identity.
Historical publication-bound snapshots belong in history tables or publication evidence, not as duplicate live current rows.

## Minimum fields
Required minimum fields:
- `trade_date`
- `ticker_id`
- `publication_id`
- `is_valid`
- `invalid_reason_code`
- `indicator_set_version`
- `dv20_idr`
- `atr14_pct`
- `vol_ratio`
- `roc5`
- `roc10`
- `roc20`
- `hh20`
- `ll20`
- `close_to_hh20_pct`
- `close_to_ll20_pct`
- `range_20_pct`
- `range_position_20_pct`
- `run_id`

Equivalent naming is allowed only if semantics remain identical.

## Current-state publication-context rule (LOCKED)
For the live readable table `eod_indicators`:
- each row must belong to exactly one sealed publication context
- `publication_id` must be non-null
- the row must represent the current readable state for `(trade_date, ticker_id)`
- superseded publication row sets must not remain side-by-side in the live current table

## Upstream-only rule (LOCKED)
These indicators are upstream derived data.
They are not:
- signals
- rankings
- watchlist groups
- portfolio actions
- execution instructions

## Validity semantics
- `is_valid = 1` means all mandatory indicator fields required by the active upstream contract are valid for that row
- `is_valid = 0` means the row exists, but one or more mandatory readiness conditions failed

When invalid:
- `invalid_reason_code` must be populated
- blocked downstream readiness must be explainable without guessing

## One-row rule (LOCKED)
The live current artifact must emit at most one row per `(trade_date, ticker_id)`.
Duplicate live indicator rows for the same key are forbidden.

## Dependency summary table (LOCKED)

| Indicator | Input dependency | Window traversal | Warmup rule | Null rule | Blocking invalid reason |
|---|---|---|---|---|---|
| `dv20_idr` | `basis_close(X)`, `volume(X)` for `D[-19] ... D` | trading-day | 20 valid bars including D | `NULL` if required history missing | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `atr14_pct` | `high(X)`, `low(X)`, `basis_close(prev(X))`, `basis_close(D)` | trading-day | 15 bars for first ATR14 output | `NULL` if seed or dependency chain invalid | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `vol_ratio` | `volume(D)` and `volume(D[-20] ... D[-1])` | trading-day | 21 bars total | `NULL` if prior-20 unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `roc5` | `basis_close(D)`, `basis_close(D[-5])` | trading-day | 6 bars total | `NULL` if `D[-5]` unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `roc10` | `basis_close(D)`, `basis_close(D[-10])` | trading-day | 11 bars total | `NULL` if `D[-10]` unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `roc20` | `basis_close(D)`, `basis_close(D[-20])` | trading-day | 21 bars total | `NULL` if `D[-20]` unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `hh20` | `high(X)` for `D[-19] ... D` | trading-day | 20 valid bars including D | `NULL` if required dependency unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `ll20` | `low(X)` for `D[-19] ... D` | trading-day | 20 valid bars including D | `NULL` if required dependency unavailable | `IND_INSUFFICIENT_HISTORY`, `IND_MISSING_DEPENDENCY_BAR`, `IND_INVALID_BAR_INPUT` |
| `range_20_pct` | `hh20`, `ll20` | trading-day | 20 valid bars including D | `NULL` if `ll20 <= 0`; otherwise may be `0` for flat range | same as `hh20`/`ll20` |
| `range_position_20_pct` | `basis_close(D)`, `hh20`, `ll20` | trading-day | 20 valid bars including D | `NULL` if `hh20 - ll20 <= 0` | same as `hh20`/`ll20` |

## Price basis rule (LOCKED)
Where closing-price basis is required, use per-date fallback:
- `adj_close`
- otherwise `close`

This must be applied separately on each dependency date.

## Trading-day rule (LOCKED)
Lookbacks and windows must be evaluated on ordered trading-day sequence.
Calendar subtraction is forbidden.

## Invalid reason semantics
Preferred meanings:
- `IND_INSUFFICIENT_HISTORY`:
  history not yet long enough for the indicator’s locked warmup
- `IND_MISSING_DEPENDENCY_BAR`:
  a required trading-day dependency row should exist but is missing
- `IND_INVALID_BAR_INPUT`:
  required bar input exists but is invalid for computation
- `IND_COMPUTE_ERROR`:
  compute logic/runtime failed unexpectedly

## Row existence rule (LOCKED)
If implementation chooses to materialize indicator rows even when invalid:
- the row must remain uniquely keyed by `(trade_date, ticker_id)` in the live current table
- `publication_id` must still be populated
- `is_valid = 0`
- `invalid_reason_code` must explain why

Implementation must not silently omit rows if downstream contracts expect explicit invalid-state rows.

## Determinism rule (LOCKED)
Given identical canonical bars, calendar ordering, config semantics, and indicator-set version, the indicator row for `(trade_date, ticker_id)` must be identical across reruns within the same publication outcome.

## Eligibility interaction
Eligibility consumers must use this indicator artifact as published.
They must not recompute indicators ad hoc from bars at read time.

## Anti-ambiguity rule (LOCKED)
The following are forbidden:
- multiple live indicator rows for the same `(trade_date, ticker_id)`
- invalid row with empty invalid reason
- non-`NULL` output produced through guessed or missing dependencies
- downstream read logic inferring validity from field non-nullness alone while ignoring `is_valid` and `invalid_reason_code`
- live readable indicator rows with `publication_id IS NULL`

---

## Amendment 2026-05-27 - Affected-date reprocess execution

When EOD bar mutation impact resolution finds affected non-readable dates, the system must actually recompute indicators for those dates. Reporting `REPROCESS_REQUIRED_*` is detection only; execution proof requires `indicator_reprocess_execution_summary`.

Execution contract:
- `execution_state=EXECUTED` only after indicator recompute runs for the affected date set.
- `reprocess_scope=FULL_DATE` is acceptable when the current compute service is date-scoped.
- `execution_state=NOOP` is valid only for unchanged bars or no affected dates.
- `execution_state=BLOCKED` is required when an affected date is already readable and must go through correction.
- `execution_state=FAILED` must include `failure_reason_code`.

Consumer read must not treat stale pre-mutation indicator rows as current proof after a changed historical bar is accepted.

## Amendment 2026-05-27 - Publication-stage follow-through

For affected dates that are not already readable/current, successful indicator and eligibility reprocess may be followed by the normal promote flow. That follow-through recomputes hash/seal/finalize artifacts through the existing publication pipeline.

For affected dates that are already readable/current, indicator rows must not be silently overwritten as a live replacement. The correction/republication lifecycle remains required.
