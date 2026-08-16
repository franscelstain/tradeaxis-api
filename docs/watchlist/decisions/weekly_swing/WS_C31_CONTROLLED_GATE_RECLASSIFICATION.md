# WS C31 Controlled Gate Reclassification

## Purpose

C31 performs controlled reclassification of the failed C29 gates using the locked C29 OOS proof artifact and the locked C30 attribution artifact.

C31 is not tuning, not candidate reselection, not best-of-OOS, not catalog promotion, and not production rollout. It separates C29's reported lookahead gate failure into actual lookahead leak, selection leak, data completeness, and strategy robustness gates.

## Input Artifacts

```text
input_c29_artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
expected_c29_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
c29_expected_status=C29_OOS_PROOF_FAILED

input_c30_artifact=storage/app/watchlist/backtest/c30-oos-failure-attribution.json
expected_c30_hash=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
c30_expected_status=C30_ATTRIBUTION_COMPLETED
```

## C29 Failed Evidence Summary

```text
status=C29_OOS_PROOF_FAILED
production_ready=0
evaluated_picks_count=128
avg_ret_net=0.004431048028766952
median_ret_net=0.0052763819095477385
p25_ret_net=-0.007561518832148093
win_rate=0.53125
month_win_rate_min=0
month_avg_ret_net_min=-0.04048987753061734
lookahead_violation_count=4
```

C29 failed:

```text
WS_BT_C29_GATE_FAIL_MONTH_WIN_RATE_PASS
WS_BT_C29_GATE_FAIL_LOOKAHEAD_PASS
```

## C30 Attribution Evidence Summary

```text
status=C30_ATTRIBUTION_COMPLETED
attribution_verdict=MIXED_DATA_AND_STRATEGY_FAILURE
production_ready=0
total_oos_pick_rows=132
reported_lookahead_violation_count=4
actual_lookahead_violation_count=0
selection_leak_count=0
missing_path_count=4
non_evaluable_pick_count=4
clean_evaluable_pick_count=128
```

Clean metrics:

```text
clean_evaluated_picks_count=128
clean_avg_ret_net=0.004431048028766952
clean_median_ret_net=0.0052763819095477385
clean_p25_ret_net=-0.007561518832148093
clean_win_rate=0.53125
clean_month_win_rate_min=0
clean_month_avg_ret_net_min=-0.04048987753061734
```

## Boundary

```text
CONTROLLED_GATE_RECLASSIFICATION_ONLY=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C30_MUTATION=true
production_ready=false
```

Return, realized exit result, MFE/MAE, future path price, or profile return are not used for pick or profile selection. C31 reads existing C29/C30 evidence and writes a new C31 artifact only.

Canonical execution model remains:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Separated Gate Model

```text
reported_lookahead_gate=FAIL
reason=C31_REPORTED_LOOKAHEAD_GATE_FAIL_FROM_C29_COUNT

actual_lookahead_gate=PASS
reason=C31_ACTUAL_LOOKAHEAD_GATE_PASS_NO_ACTUAL_LEAK

selection_leak_gate=PASS
reason=C31_SELECTION_LEAK_GATE_PASS_NO_SELECTION_LEAK

data_completeness_gate=FAIL
reason=C31_DATA_COMPLETENESS_GATE_FAIL_MISSING_PATH

month_win_rate_gate=FAIL
reason=C31_MONTH_WIN_RATE_GATE_FAIL_SOURCE_MONTH_ZERO

clean_month_win_rate_gate=FAIL
reason=C31_CLEAN_MONTH_WIN_RATE_GATE_FAIL_CLEAN_MONTH_ZERO

overall_controlled_oos_gate=FAIL
reason=C31_CONTROLLED_OOS_GATE_FAIL_DATA_COMPLETENESS_AND_ROBUSTNESS
```

## Bad Month Summary

```text
2025-06: total_rows=12 clean_rows=10 missing_path_rows=2 win_rate=0 dominant_branch=G21
2025-08: total_rows=9 clean_rows=7 missing_path_rows=2 win_rate=0 dominant_branch=G21
2026-03: total_rows=4 clean_rows=4 missing_path_rows=0 win_rate=0 dominant_branch=G16
```

## Source Branch Summary

```text
G16: count=18 clean_count=18 missing_count=0 non_evaluable_count=0
G21: count=80 clean_count=80 missing_count=0 non_evaluable_count=0
R09: count=34 clean_count=30 missing_count=4 non_evaluable_count=4
```

## Missing Path Rows

```text
2025-06-04 MICE param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-06-04 MICE param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
```

Actual lookahead violation rows: `0`.

Selection leak rows: `0`.

## Runtime Result

Validation was executed in this workspace.

```text
PHPUNIT_C31=PASS
OK (14 tests, 126 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (478 tests, 11130 assertions)

C31_RUNTIME=COMPLETED
status=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
artifact_path=storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json
artifact_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
file_sha1=B9EC57659113EFED3B99E9DC22235E44398A5DA2
```

## Reclassification Conclusion

```text
C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK
```

C31 confirms that C29's reported lookahead violations are missing D1-D5 raw OHLC path rows, not actual lookahead/future-data leaks, while the data completeness gate still fails.

## Controlled Proof Status

```text
C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS
```

C31 does not unlock OOS pass or production readiness.

## Final Status

```text
C31_FINAL_STATUS=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
production_ready=false
```

## Next Step

Split the next work into data-path remediation proof and bad-month robustness diagnostic. C32 should not tune from OOS, create best-of-OOS, promote a catalog, or change production PLAN/CONFIRM behavior.
