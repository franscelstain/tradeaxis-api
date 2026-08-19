# Weekly Swing Backtest OOS Runtime Implementation Contract

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Current Market Data Semantic Override

This technical document may retain legacy physical/parameter tokens for backward compatibility. Current Watchlist interpretation of producer fields is governed by `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`, which delegates semantic ownership to the Market Data producer contracts:

- legacy `dv20_idr` / `*_dv20_idr` tokens apply only to Market Data canonical `adv20_close_volume_proxy_idr` (`RAW close × RAW volume` 20-session proxy); they MUST NOT be interpreted as `adv20_traded_value_idr_actual`;
- legacy serialized `vol_ratio` / `*_vol_ratio` tokens apply to canonical `vol_ratio_20` only when the selected Market Data read-model version declares exact semantic equivalence;
- direct Market Data table names, if preserved below as implementation history/debug context, are not current runtime intake authority;
- a future change from proxy liquidity to actual traded value, or to a different participation formula, is a strategy/proof identity change rather than a transparent field substitution.

Where wording below conflicts with this override or the canonical Weekly Swing strategy, this override + the canonical strategy wins until the implementation document is physically migrated.

> **Doc Role:** IMPLEMENTATION CONTRACT
> Extracted unchanged from the former mixed backtest strategy document.

## OOS Runtime Gap Closure — Deterministic Grid, Evaluation Identity, and Bounded Reads (LOCKED)

The minimum chronological OOS runtime uses one explicit historical window. Operators must not split one requested proof into multiple commands and merge the results, because every command would create a different 70/30 split and a different calibration boundary.

### Canonical grid source

Runtime calibration reads only `watchlist_bt_param_grid`, ordered by `param_id ASC`. The canonical bootstrap catalog is implemented by `WatchlistBacktestParamGridCatalog` and persisted idempotently through:

- `php artisan watchlist:backtest-param-grid-seed`;
- `database/seeders/Watchlist/WatchlistBacktestParamGridSeeder.php`;
- [`db/BACKTEST_PARAM_GRID_SEED.sql`](../db/BACKTEST_PARAM_GRID_SEED.sql) for operator SQL deployment.

The bootstrap catalog is deterministic and curated before OOS execution. The current canonical bootstrap cardinality is `24`, exposed by `WatchlistBacktestParamGridCatalog::CATALOG_COUNT`; catalog code, SQL seed cardinality, repository persistence, and static guards must derive from that single source instead of duplicating a literal count. Any cardinality change requires synchronized owner-doc, catalog, SQL seed, and test updates.

It is not generated from OOS metrics, random search, Bayesian search, or current runtime outcomes. Every row must satisfy:

- scoring weights sum to `1.0`;
- quantiles are inside `0..1` and `top_min_score_q >= secondary_min_score_q`;
- targets are positive integers;
- ATR values use fractional units;
- `stop_atr_mult > 0` and `min_rr > 0`.

The official grid columns include:

```text
min_dv20_idr
max_atr14_pct
min_vol_ratio
w_momentum
w_volume
w_breakout
w_risk
stop_atr_mult
min_rr
top_picks_target
secondary_target
top_min_score_q
secondary_min_score_q
```

### Versioned evaluation identity

`watchlist_bt_eval` identity is:

```text
policy_code + catalog_code + catalog_version + param_id + eval_model
+ eval_model_hash + implementation_version + implementation_hash
+ evidence_pipeline_version + evidence_pipeline_hash
+ paramset_hash + from_date + to_date
```

This identity preserves historical evidence when evaluation semantics or a paramset snapshot changes. An old row must not be overwritten or deleted merely to permit a rerun. Existing unversioned rows are migrated to explicit legacy markers by the gap-closure migration/SQL.

Exact duplicate payloads are idempotent. The same identity with different metrics fails closed. The OOS identity also includes `is_eval_id`, ensuring that an OOS result is bound to one exact frozen IS evaluation and corrected IS semantics can coexist with historical proof rows.

### Memory-bounded published-price read

After PLAN/recommendation candidates are frozen, the runtime builds an exact `trade_date -> ticker_code[]` map for entry/exit evaluation and reads only those date/ticker pairs through the official published EOD read surface. The runtime must not materialize `all candidate tickers × all required dates` when most pairs are not consumed.

Canonical markers:

```text
pricing_model = PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE
price_read_mode = TARGETED_DATE_TICKER_MAP
targeted_date_ticker_read = true
```

These runtime-owned markers are bound to the returned strategy payload **before** the frozen strategy hash is computed and before any future-price read begins. This binding must update the top-level and meta paramset snapshots consistently, while leaving missing canonical evaluation thresholds missing so threshold validation can still fail closed rather than fabricating evidence.

The bootstrap strategy risk defaults remain `risk.stop_atr_mult = 1.5` and `risk.min_rr = 1.5` when those inputs are absent. Explicit grid/runtime values override the defaults; a missing nested `risk` section must not silently produce null trade-candidate risk fields.

IS calibration is in-memory and does not write a temporary JSON file for every grid row. One final proof artifact is exported by the OOS orchestrator. Per-grid iteration state must be released before evaluating the next row.

### Trade evidence in the proof artifact

For every IS evaluation reference, the proof artifact includes compact deterministic extreme-trade evidence (worst and best evaluated trades) with entry/exit dates, prices, volume, stop/target source, ATR/RR inputs, return, and publication lineage. This evidence supports diagnosis without creating an unofficial shadow table. It does not replace the official table allowlist or the separate full coverage requirements for promotion.

## Canonical grid cross-field projection rule (LOCKED)

`watchlist_bt_param_grid.max_atr14_pct` is an explicit grid axis, while the minimum OOS bootstrap schema does not persist separate `atr_ideal_low` and `atr_ideal_high` columns. A grid row must never be combined with incompatible active defaults.

Before strategy/scoring execution, the runtime paramset factory must resolve the companion ATR band deterministically:

```text
min_atr14_pct = canonical active minimum
atr_ideal_high = max(min_atr14_pct, min(canonical default atr_ideal_high, grid.max_atr14_pct))
atr_ideal_low  = max(min_atr14_pct, min(canonical default atr_ideal_low, atr_ideal_high))
```

Canonical marker:

```text
risk_band_rule = CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR
```

Required invariants:

```text
min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct
```

The resolved values and rule must be present in the immutable paramset snapshot under `bt_grid_resolution`. This is a deterministic compatibility projection, not OOS tuning. It may not inspect IS/OOS metrics, prices, or ranking results. A row with `max_atr14_pct < min_atr14_pct` must fail closed as invalid.
