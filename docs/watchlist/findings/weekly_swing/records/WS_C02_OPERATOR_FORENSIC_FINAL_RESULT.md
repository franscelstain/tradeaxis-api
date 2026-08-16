# WS C02 Operator Forensic Final Result

## Status

```text
C02_IMPLEMENTATION_PASS / C02_OPERATOR_VALIDATION_PASS / C02_IS_EXECUTION_PASS / C02_IS_QUALITY_FAIL / C02_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY / C03_REQUIRED
POST_DOCS_VALIDATION_PASS
```

## Scope

This document records operator-supplied validation and final forensic review for:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
catalog_version=C02
catalog_count=8
catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
```

This document is audit/status evidence only. It is not OOS proof, not promotion evidence, and not production-readiness evidence.

## Operator validation summary

```text
C02 PHPUnit=PASS / OK (12 tests, 391 assertions) / exit code 0
Full Watchlist PHPUnit=PASS / OK (238 tests, 4182 assertions) / exit code 0
C02 seed=PASS / inserted_count=8 / updated_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / oos_executed=0 / production_ready=0
```

## Post-docs validation evidence

After the C02 final documentation and forensic CSV sync, the operator reran the focused C02 test and the full Watchlist suite. This validates the docs/static guard state after the documentation-only update. It is not a new seed, not a new calibration, not OOS proof, and not production-readiness evidence.

```text
scope=DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02" = PASS / OK (12 tests, 391 assertions) / Time 00:01.281 / Memory 14.00 MB / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (238 tests, 4182 assertions) / Time 00:04.431 / Memory 24.00 MB / exit code 0
post_docs_validation_verdict=PASS
```

## C02 IS calibration summary

Both C02 IS calibration runs executed on the frozen IS window and produced the same artifact hash.

```text
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
run_1.status=C02_GRID_FAILED_IS_QUALITY
run_2.status=C02_GRID_FAILED_IS_QUALITY
run_1.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
run_2.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
hash_equal=1
is_valid_param_count=0
is_failed_param_count=8
param_id_best_is=<empty>
best_is_binding_hash=<empty>
production_ready=0
```

## Boundary and immutability proof

```text
strict_is_boundary_all_evaluations=1
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
catalog_hash_matches=true
r1_immutable=true
r2_immutable=true
c01_immutable=true
no_oos_market_read=true
no_oos_table_mutation=true
```

## Failure distribution

Every C02 parameter failed all three quality families.

```text
WS_BT_EVAL_DOWNSIDE_FAIL=8
WS_BT_EVAL_ROBUST_RETURN_FAIL=8
WS_BT_EVAL_STABILITY_FAIL=8
```

Representative gate detail from `00_C01_NEAREST_GATE_REFERENCE`:

```text
average_return_positive=false
median_return_non_negative=false
minimum_coverage=true
minimum_trade_count=true
monthly_average_floor=false
monthly_win_rate_floor=false
p25_downside_bound=false
```

Thresholds used by the artifact:

```text
min_days_covered=390
min_month_avg_ret_net_min=-0.01
min_month_win_rate_min=0.45
min_p25_ret_net_top=-0.03
min_trades=120
```

## Per-row forensic summary

| Param | Row code | Avg ret net top | Median ret net top | P25 ret net top | Month win min | Picks | Period fail | Verdict |
|---:|---|---:|---:|---:|---:|---:|---:|---|
| 45 | `00_C01_NEAREST_GATE_REFERENCE` | -0.1727% | -2.1046% | -5.4058% | 14.0351% | 1435 | 20/27 | rejected |
| 46 | `01_NEAR_BREAKOUT_MODERATE_LIQUIDITY` | 0.0921% | -1.7164% | -5.4627% | 17.5439% | 1422 | 18/27 | rejected |
| 47 | `02_MID_LIQUIDITY_VOLUME_BALANCED` | -0.1681% | -2.0027% | -5.3678% | 17.8571% | 1429 | 20/27 | rejected |
| 48 | `03_STRICT_NEAR_BREAKOUT_LOW_CHASE` | -0.0525% | -1.7688% | -5.5866% | 20.0000% | 1360 | 20/27 | rejected |
| 49 | `04_LOW_ATR_MID_ROC_STABILITY` | -0.1652% | -1.7888% | -4.9668% | 17.5000% | 1430 | 21/27 | rejected |
| 50 | `05_VOLUME_NOT_SPIKE_RISK_FIRST` | -0.1030% | -1.7640% | -5.2137% | 14.2857% | 1431 | 19/27 | rejected |
| 51 | `06_BROAD_SAMPLE_NEAR_BREAKOUT` | 0.1810% | -1.7164% | -5.4058% | 14.0351% | 1425 | 19/27 | rejected |
| 52 | `07_STABILITY_PROXY_SECTOR_REVIEW` | -0.0694% | -1.8353% | -5.5662% | 23.2143% | 1423 | 22/27 | rejected |

## Interpretation

C02 had enough coverage and enough trades. It failed because the selected trade population still had negative median return, weak downside tail, and unstable monthly performance. C02 is not close enough to rescue with minor threshold relaxation.

Best reference rows are still not valid:

- `param_id=51` had the best average return and win-rate profile, but still failed median, p25 downside, and monthly stability.
- `param_id=52` had the best monthly stability proxy, but still failed return, downside, and stability gates and remained far below the 45% monthly win-rate floor.

## Final decision

```text
C02_REJECTED_AS_STRATEGY_CATALOG
NO_C02_BEST_IS_BINDING
NO_C02_OOS_ELIGIBILITY
NO_PRODUCTION_READY_CLAIM
C03_REQUIRED
```

## Required next work

- Preserve R1/R2/C01/C02 as immutable evidence.
- Do not mutate C02 to force a pass.
- Do not run OOS for C02.
- Design C03 as a new catalog identity from C02 forensic metrics.
- C03 should change candidate filtering/parameter design, not merely lower canonical gates.
- Keep OOS unread until a future catalog produces a valid frozen IS binding.
