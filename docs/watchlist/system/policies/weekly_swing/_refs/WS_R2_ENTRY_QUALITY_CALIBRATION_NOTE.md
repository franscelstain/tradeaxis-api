# WS R2 Entry-Quality Calibration Reference Note

## Status

```text
LAST_UPDATED=2026-06-10
IMPLEMENTATION_SCOPE=DONE
SUPPORTED_OPERATOR_IS_RUNTIME_EXECUTED
LOCAL_R2_IS_CALIBRATION_EXECUTED
R2_GRID_FAILED_IS_QUALITY
OOS_NOT_READ
NOT_PRODUCTION_READY
```

This note records implementation and validation evidence for the separate R2 IS-only calibration session. It is a reference/evidence note, not the owner of behavior. Normative ownership remains in files 04–07, 12–13, 16–18, and 20.

## Preserved R1 Evidence

```text
catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
catalog_version=R1
catalog_count=24
catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R1 runtime artifact hash=f4ec8464f08515b31d7d26636851acea930307d6
R1 valid IS rows=0
R1 failed IS rows=24
R1 OOS executed=false
```

`WatchlistBacktestParamGridCatalog::rows()` and its historical SHA1 remain unchanged. R1 persistence enrichment adds catalog metadata and explicit companion defaults without altering any historical R1 runtime row. A direct compatibility harness compared all 24 enriched R1 rows against the original ZIP factory output and passed `24/24`; a second harness compared the original and updated R1 IS-calibration service outputs and passed exact equality.

## R2 Immutable Catalog Manifest

```text
catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
catalog_version=R2
catalog_count=12
catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
control_row=00_R1_BASELINE_CONTROL
search_mode=CURATED_DETERMINISTIC
random_or_bayesian=false
```

Rows:

1. `00_R1_BASELINE_CONTROL`
2. `01_BALANCED_ENTRY_FILTER`
3. `02_LIQUID_MOMENTUM`
4. `03_VOLUME_BREAKOUT_CONFIRM`
5. `04_LOW_VOLATILITY_QUALITY`
6. `05_RISK_WEIGHTED_ENTRY`
7. `06_NEAR_BREAKOUT_ONLY`
8. `07_STRONG_PARTICIPATION`
9. `08_MOMENTUM_NOT_EXTENDED`
10. `09_HIGH_LIQ_BALANCED_STRICT`
11. `10_DEFENSIVE_ENTRY`
12. `11_CONCENTRATED_ENTRY_QUALITY`

The catalog is frozen in code before operator runtime. Any change after first execution requires a new catalog/version, not an edit to R2. The runtime artifact embeds all curated row values/rationales, the 19 owner-linked axis rationales, and provenance markers showing registry + R1 IS evidence + deterministic engineering rationale with `oos_used=false`.

## Runtime-Consumption Trace

| Axis | Owner/registry | Persisted/factory | Runtime consumer |
|---|---|---|---|
| liquidity minimum/strong DV20 | file 05, `bt_target=true` | grid columns → `paramset.liquidity` | Candidate Universe / Scoring |
| volume minimum/strong ratio | file 05, `bt_target=true` | grid columns → `paramset.volume` | Scoring and volume guard logic |
| ATR min/max/ideal band | file 05, `bt_target=true` | grid columns → `paramset.risk` | Candidate Universe / Scoring |
| ROC and breakout thresholds | file 05, `bt_target=true` | grid columns → `paramset.setup` | Scoring |
| four score weights | file 05, `bt_target=true` | grid columns → `paramset.scoring.weights` | Scoring |
| top/secondary quantiles | file 05, `bt_target=true` | grid columns → `paramset.grouping` | PLAN Grouping |

`volume.strong_vol_ratio` was a real runtime input but was missing from the exhaustive registry summary. The owner registry and validator were corrected before it was admitted as an R2 axis.

## Fixed Execution Snapshot

```text
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
risk.stop_atr_mult=1.50
risk.min_rr=1.50
grouping.top_picks_target=5
grouping.secondary_target=10
```

The grouping count targets are frozen because they are not R2 `bt_target` axes. No exit-axis search, fee/slippage change, gap-fill change, price-band change, or holding-horizon change is part of R2.

## Persistence and Identity Closure

- `watchlist_bt_param_grid` now carries explicit catalog and row identity plus only runtime-consumed R2 columns.
- R1 and R2 are queried by explicit `catalog_code`; no latest/active fallback exists.
- Seed behavior is INSERT-or-idempotent only; immutable conflicts fail closed.
- `watchlist_bt_eval` identity includes policy, catalog code/version, param id, eval model, paramset hash, and exact window. The R2 paramset hash excludes generated `param_id` and is stable across databases for the same immutable catalog row.
- Exact duplicate eval persistence is idempotent; conflicting payloads fail with `WS_BT_EVAL_IDENTITY_CONFLICT`.
- Migration backfills existing R1 rows deterministically and preserves R1 eval rows under the explicit R1 identity.

## Strict IS and No-OOS Design

The command accepts only:

```text
catalog=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
from=2023-01-02
to=2025-05-21
```

The runtime receives `hard_market_data_to_date=2025-05-21`. The final five IS dates cannot start new trades, so their required exits never cross into reserved OOS. Calendar/publication/OHLCV reads remain bounded to the explicit IS window.

The R2 execution path has no dependency on `WatchlistBacktestOosProofService` or `WatchlistBacktestOosEvaluationRepository`. It snapshots `watchlist_bt_oos_eval_ws` before/after for mutation proof but does not insert/update/delete that table.

## Evidence Artifact Contract

The IS-only artifact includes catalog/date/lineage hashes, all evaluations, canonical gate results, optional valid best binding, R1 snapshot equality, OOS table before/after hashes, maximum requested market-data date, and `production_ready=false`.

A best binding is emitted only if at least one row passes every unchanged canonical gate in file 16. No valid row means `WS_BT_R2_NO_VALID_IS_CANDIDATE`, non-zero command exit, and `best_is_binding=null`.

## Validation Performed in Packaging Environment

```text
PHP syntax lint: PASS / 312 PHP files
R2 pure-PHP smoke harness: PASS / 180 assertions
R1 factory compatibility: PASS / 24 of 24 rows
R1 IS-calibration service compatibility: PASS / exact output equality
R1 catalog source hash: 9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog source hash: 0f2eaadaa446980a3d5e48cd498df2a8157c01a5
R2 catalog count: 12
locked file 16 SHA1: 31299d858b68ee351ae898f4c9380d8753a65d8a
locked file 17 SHA1: 39519a391158a7b2dcf7b6e989079788d61669be
```

The smoke harness covers catalog identity/count/hash, all cross-field invariants, explicit factory projection, paramset-hash independence from database `param_id`, immutable eval idempotence/conflict behavior, no-best-of-failed, post-IS mutation invariance, and the real strict published-price path (`resolveTradingDates` only, HOLD=5 entry censoring, maximum requested date `2025-05-21`).

Official PHPUnit could not start because PHP extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are unavailable. Artisan migration/seed/calibration commands fail closed before application bootstrap with `ENV_UNSUPPORTED_PHP_VERSION` because this container runs PHP `8.4.16` while the project requires PHP `>=7.3,<8.4`; no PDO database driver is installed either. Package installation was blocked by DNS resolution. No fabricated database or runtime evidence was created.

## Supported Operator Final Evidence

The supported operator environment executed the required PHPUnit, migration, seed, and two-run IS calibration sequence.

Validation:

```text
WatchlistBacktestR2ParamGridParamsetFactoryTest: PASS / 12 tests / 106 assertions
WatchlistBacktestR2StaticGuardTest: PASS / 5 tests / 53 assertions
WatchlistBacktestOosPersistenceTest: PASS / 3 tests / 13 assertions
WatchlistBacktestR2: PASS / 26 tests / 530 assertions
WatchlistBacktestOos: PASS / 24 tests / 228 assertions
WatchlistBacktest: PASS / 117 tests / 2442 assertions
Full Watchlist: PASS / 209 tests / 3330 assertions
```

Migration/seed:

```text
migration=2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality
migration_status=Yes
migration_batch=10
R2 seed run 1: inserted_count=12, updated_count=0, existing_count=0, exit_code=0
R2 seed run 2: inserted_count=0, updated_count=0, existing_count=12, exit_code=0
R1 immutable proof: r1_immutable=1 on both seed runs
```

R1/R2 coexistence:

```text
R1 catalog=WS_BT_GRID_BOOTSTRAP_2026_06 / version=R1 / count=24 / hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06 / version=R2 / count=12 / hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
```

Two-run R2 IS result:

```text
status=R2_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
is_window=2023-01-02..2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
failure_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
```

No-OOS proof:

```text
max_requested_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
```

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

## Required Operator Validation

Operator validation is complete for R2 IS execution. Do not rerun R2 with changed catalog values. The immutable R2 catalog has already produced a failed-IS quality result.

Final status:

```text
DONE for R2 entry-quality calibration execution infrastructure
LOCAL_R2_IS_CALIBRATION_EXECUTED
R2_GRID_FAILED_IS_QUALITY
OOS_NOT_READ
NOT_PRODUCTION_READY
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
```

## Catalog Naming and Next-Session Rule

`R1` and `R2` are retained only as immutable historical aliases and backward-compatible evidence labels. They must not become the active naming convention for future work.

Do not create `R3`, `R4`, `R5`, or later catalog identities.

Future catalog naming must be semantic:

```text
WS_BT_GRID_<FOCUS>_C##_YYYY_MM
```

Future evidence naming should distinguish run type and catalog attempt:

```text
WS_BT_IS_<FOCUS>_C##_RUN_##
WS_BT_OOS_<FOCUS>_C##_RUN_##
```

The next session must not mutate R1 or R2 and must not run OOS. It must begin with IS failure diagnostics and, only if justified, design a new semantic catalog. Recommended session title:

```text
WATCHLIST — WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION
```

Recommended catalog identity if design proceeds:

```text
WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```
