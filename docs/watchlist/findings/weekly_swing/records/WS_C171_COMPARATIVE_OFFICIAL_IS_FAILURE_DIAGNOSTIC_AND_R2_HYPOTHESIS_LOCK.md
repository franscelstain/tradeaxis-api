# C171 Comparative Official IS Failure Diagnostic and R2 Hypothesis Lock

## Session identity

```text
C171_TOPIC=C171_COMPARATIVE_OFFICIAL_IS_FAILURE_DIAGNOSTIC_AND_R2_HYPOTHESIS_LOCK
C171_PHASE=READ_ONLY_COMPARATIVE_FAILED_IS_DIAGNOSTIC
C171_EXECUTION_MODE=IMPLEMENTATION_FIRST_OPERATOR_RUNTIME_REQUIRED
C171_SOURCE_OF_TRUTH=IMMUTABLE_EVAL_188_TO_193_DATABASE_EVIDENCE_AND_OFFICIAL_JSON_ARTIFACTS
C171_PRODUCTION_READY=0
```

## Why this stage exists

The official C171 baseline and five immutable remediation DRAFTs were executed on the same strict IS window. All six evaluations failed canonical IS gates. Paramset 5 / `eval_id=192` improved average return and several robust metrics most strongly, but median, P25 downside, and monthly stability still failed. A second catalog must not be designed from aggregate metrics alone.

This stage compares the exact versioned evidence before any new DRAFT is created. It is diagnostic/design-lock work only.

## Locked input identities

| Eval | Paramset | Role | Physical artifact SHA1 |
|---:|---:|---|---|
| 188 | 1 | Baseline | `B9A3E74466F05FB7A1504CAFF4C7B06F86DD3F62` |
| 189 | 2 | A — Broad Moderate Score Cap | `894EE0BED787C130A28A51B5D6D7FCD14CB8D26C` |
| 190 | 3 | B — Broad Sample Recovery | `CBA34F0942DD6B79E26418DA91A3B787EDC1B091` |
| 191 | 4 | C — Mid Liquidity Low ATR Score Cap | `6A7A55D8B491C4A637BB8DD529A02B44AA54C119` |
| 192 | 5 | D — Low ATR Balanced | `590889CEA60A31A92B7B5262D7996AF012E7276A` |
| 193 | 6 | E — Lower Volume Balanced | `99A77BD0AFB502C524A731CFF42EC332ED71936A` |

Canonical boundary:

```text
IS_FROM=2023-01-02
IS_TO=2025-05-21
STRICT_IS_BOUNDARY=1
OOS_READ_ALLOWED=0
```

## Database dictionary and field ownership

Before implementation the following were read and cross-checked:

- `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
- `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`
- `docs/watchlist/implementation/weekly_swing/db/BACKTEST_SCHEMA_DDL.sql`
- C171 versioning migrations and official-evidence repository

Touched evidence:

- `watchlist_bt_eval.eval_id` owns evaluation identity and manifest hashes.
- `watchlist_bt_picks_ws.(eval_id,asof_eod_date,ticker_id)` is the immutable official TOP/TOP_PICKS population; `ret_net` is evaluation-only.
- `watchlist_bt_universe_ws` supplies signal-date `atr14_pct`, `dv20_idr`, and `vol_ratio` for diagnostic segmentation only.
- `watchlist_bt_cutoffs_ws` is manifest-verified but not used to route diagnostic outcomes.
- `market_calendar.cal_date` supplies the exact bounded trading calendar.
- `eod_bars` is read only through the published-series service for diagnostic replay.
- `market_benchmark_indicators.trade_date` with `benchmark_code='IHSG'`, configured `indicator_set_version`, and `is_valid=1` supplies decision-time `roc_20` and `ma20_slope_pct` regime segmentation.

No unbounded `MAX(trade_date)` and no future/OOS lookup is permitted.

## Implemented service behavior

Command:

```text
watchlist:backtest-c171-comparative-official-is-failure-diagnostic
```

The service:

1. verifies all six locked physical file SHA1s, recomputes each JSON `artifact_hash`, and validates strict execution-route/boundary markers;
2. verifies exact database evaluation identity and official manifest parity;
3. reads official picks and matching signal-date universe fields by exact `eval_id`;
4. replays only the frozen official picks against current readable published prices;
5. requires every replayed `ret_net` to match the immutable official pick to six decimals and requires entry publication ID/version/run lineage parity;
6. computes all 15 pairwise overlap comparisons and detailed added/removed picks versus baseline;
7. segments monthly stability, score deciles, aggregate and detailed entry-price/tick-normalized stop risk, exit reason, and exact-version IHSG regime;
8. reconciles `metrics.picks_count` with DB official picks and explains the broader `trade_evidence.evaluated_trade_count` population;
9. locks at most three evidence-backed decision-time hypotheses;
10. produces a semantic next catalog identity, never an `R3`/`R4` catalog name.

## Output artifacts

```text
c171-comparative-official-is-failure-diagnostic.json
c171-comparative-official-is-failure-diagnostic-trade-overlap.csv
c171-comparative-official-is-failure-diagnostic-added-removed-trades.csv
c171-comparative-official-is-failure-diagnostic-price-risk-segments.csv
c171-comparative-official-is-failure-diagnostic-monthly-stability.csv
c171-comparative-official-is-failure-diagnostic-score-deciles.csv
c171-comparative-official-is-failure-diagnostic-market-regime.csv
c171-comparative-official-is-failure-diagnostic-exit-distribution.csv
c171-comparative-official-is-failure-diagnostic-population-reconciliation.csv
c171-comparative-official-is-failure-diagnostic-r2-hypothesis-lock.json
```

## Hypothesis-lock rules

A hypothesis may be locked only when its segment evidence is material and replay parity passes.

Allowed focus families:

- `LOW_PRICE_EXECUTION_QUALITY`
- `MARKET_REGIME_QUALITY`
- `SCORE_RANKING_QUALITY`
- `EXIT_DOWNSIDE_CONTROL`

The resulting catalog name follows:

```text
WS_BT_GRID_<SEMANTIC_FOCUS>_C01_2026_07
```

`R2` in this session title is a historical workflow label only. It is not a new numeric catalog identity.

## Fail-closed rules

The stage blocks when:

- any eval/artifact identity differs;
- any database manifest differs from artifact manifest;
- official pick count differs from manifest;
- current published-price replay does not reproduce official `ret_net` to six decimals or exact entry publication lineage;
- the required Market Data schema/calendar/publication is unavailable;
- no material evidence-backed hypothesis survives the lock rules;
- any protected non-OOS database row count changes during execution. The OOS table is not queried at all.

## Forbidden actions

```text
DRAFT_PARAMSET_CREATED=0
DRAFT_PARAMSET_MUTATED=0
OFFICIAL_IS_RUNTIME_INVOKED=0
OOS_RUNTIME_INVOKED=0
OOS_REPOSITORY_INVOKED=0
OOS_TABLE_READ=0
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_CREATED=0
PLAN_RUN_CREATED=0
RECOMMENDATION_PERSISTED=0
CONFIRM_MUTATED=0
PRODUCTION_ACTIVATION_EXECUTED=0
CONTROLLED_ROLLOUT_EXECUTED=0
PRODUCTION_READY=0
C172_ALLOWED=0
```

## Validation status

```text
SOURCE_IMPLEMENTATION=COMPLETED
PHP_LINT_CHANGED_PHP=PASS
PHPUNIT_LOCAL=OPERATOR_VALIDATION_REQUIRED
RUNTIME_DIAGNOSTIC=OPERATOR_VALIDATION_REQUIRED
```

No claim of runtime PASS is made until the operator supplies PHPUnit and command output.
