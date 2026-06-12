# WS C03 Operator Forensic Final Result

Status: C03_OPERATOR_VALIDATED / C03_IS_QUALITY_FAILED / C03_REJECTED_AS_STRATEGY_CATALOG
OOS status: NOT_RUN
Production readiness: false
Last updated: 2026-06-11

## 1. Verdict

```text
C03_IMPLEMENTED
C03_OPERATOR_VALIDATED
C03_SEEDED
C03_IS_EXECUTED
C03_DETERMINISTIC_TWO_RUN
C03_FAILED_IS_QUALITY
C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
C04_REQUIRED
```

C03 is an implementation success but a strategy-quality failure. It must not be promoted, and it must not be used to unlock OOS.

## 2. Catalog identity

| Field | Value |
| --- | --- |
| catalog_code | `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06` |
| catalog_version | `C03` |
| catalog_count | `10` |
| catalog_hash | `29e15ceab1b3f7dc31a21f339ac6ab7483e14800` |

## 3. Operator validation evidence

The operator provided the following command outputs from the supported project runtime.

### 3.1 PHPUnit C03 filter

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC03"
OK (12 tests, 461 assertions)
```

### 3.2 Full Watchlist PHPUnit

```text
vendor\bin\phpunit tests\Unit\Watchlist
OK (250 tests, 4643 assertions)
```

### 3.3 C03 seed

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
inserted_count=10
updated_count=0
existing_count=0
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_count=12
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
c01_catalog_count=8
c01_catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
c02_catalog_count=8
c02_catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
oos_executed=0
production_ready=0
```

## 4. C03 IS calibration evidence

### 4.1 Run 1

```text
status=C03_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
production_ready=0
```

### 4.2 Run 2

```text
status=C03_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=10
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
production_ready=0
```

## 5. Determinism assessment

C03 is deterministic for the evidence supplied:

| Marker | Run 1 | Run 2 | Verdict |
| --- | --- | --- | --- |
| status | `C03_GRID_FAILED_IS_QUALITY` | `C03_GRID_FAILED_IS_QUALITY` | SAME |
| reason_code | `WS_BT_C03_NO_VALID_IS_CANDIDATE` | `WS_BT_C03_NO_VALID_IS_CANDIDATE` | SAME |
| catalog_hash | `29e15ceab1b3f7dc31a21f339ac6ab7483e14800` | `29e15ceab1b3f7dc31a21f339ac6ab7483e14800` | SAME |
| is_trading_date_hash | `581dd450ebcbd56cb3a1c066c9fc80bbccb3a753` | `581dd450ebcbd56cb3a1c066c9fc80bbccb3a753` | SAME |
| is_valid_param_count | `0` | `0` | SAME |
| is_failed_param_count | `10` | `10` | SAME |
| artifact_hash | `649e8fead0c57262307f749a4776f053f5ccd0f8` | `649e8fead0c57262307f749a4776f053f5ccd0f8` | SAME |
| OOS guards | clean | clean | SAME |

## 6. Quality failure assessment

C03 failed on the same aggregate quality family as C02:

```text
WS_BT_EVAL_DOWNSIDE_FAIL
WS_BT_EVAL_ROBUST_RETURN_FAIL
WS_BT_EVAL_STABILITY_FAIL
```

This means the C03 design change did not produce even one strategy-quality IS candidate. The correct interpretation is not that C03 implementation is broken; the implementation is validated. The failure is strategy quality.

## 7. Forensic artifact detail

The JSON artifacts are available in the current workspace:

```text
storage/app/watchlist/backtest/c03-is-run-1.json
storage/app/watchlist/backtest/c03-is-run-2.json
```

Per-row C03 metrics have been extracted into:

```text
docs/watchlist/audit/_artifacts/c03-forensic-summary.csv
storage/app/watchlist/backtest/c03-forensic-summary.csv
```

C03 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=10
WS_BT_EVAL_ROBUST_RETURN_FAIL=10
WS_BT_EVAL_STABILITY_FAIL=10
```

Per-row metric ranges from `c03-is-run-1.json`:

```text
picks_count=1104..1432
days_covered=503..508
avg_ret_net_top=-0.3368%..0.1810%
median_ret_net_top=-1.7639%..-1.0634%
p25_ret_net_top=-5.4058%..-3.7924%
month_win_rate_min=10.00%..20.69%
win_rate_top=38.45%..41.82%
```

This confirms C03 had meaningful sample size, but its candidate selection remained too weak: all medians were negative, all p25 downside values were worse than the `-3%` threshold, and monthly stability remained far below the required floor.

## 8. Final decision

```text
C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C03 must not advance to OOS because:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
```

OOS remains `NOT_RUN`. Production readiness remains `false`.

## 9. Required next step

The next same-focus attempt must be C04.

C04 requirements:

- create a new catalog identity and version; do not mutate C03;
- preserve R1/R2/C01/C02/C03 immutability;
- use C02 and C03 as failed quality evidence;
- do not loosen canonical gates;
- do not add unsupported sector filters;
- change candidate-selection axis/logic using only runtime-supported fields;
- stay IS-only until at least one valid IS candidate exists.

C04 should investigate whether the current runtime candidate filter is systematically selecting weak/reversal-trap candidates. A pure numeric tightening of C03 is not enough unless per-parameter artifact evidence proves otherwise.
