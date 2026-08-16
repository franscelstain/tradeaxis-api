# WS C30 OOS Failure Attribution

## Purpose

C30 is a failure attribution diagnostic for the failed C29 OOS proof artifact. It is not a tuning session, not candidate reselection, not best-of-OOS, not catalog promotion, and not production rollout.

C30 answers whether the C29 failure is attributable to actual future-data/lookahead leakage, missing D1-D5 OHLC path rows, non-evaluable rows, branch strategy weakness, bad-month/regime concentration, data quality issues, or a combination of those factors.

## Input C29 Artifact

```text
input_c29_artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
expected_c29_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
expected_c29_status=C29_OOS_PROOF_FAILED
```

C30 must lock the C29 artifact by stable artifact hash before attribution. If the file is missing, hash-mismatched, or not a failed C29 artifact, C30 returns an explicit blocked status and must not continue silently.

Blocked statuses:

```text
C30_BLOCKED_MISSING_C29_ARTIFACT
C30_BLOCKED_C29_HASH_MISMATCH
C30_BLOCKED_UNEXPECTED_C29_STATUS
```

## C29 Failed Evidence Summary

Operator-provided C29 evidence currently recorded in the audit tracker:

```text
PHPUNIT_C29=PASS: OK (13 tests, 132 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (448 tests, 10900 assertions)
C29_RUNTIME=FAIL
status=C29_OOS_PROOF_FAILED
artifact_path=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
artifact_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
expected_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
actual_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
c28_hash_match=1
production_ready=0
```

C29 metrics:

```text
evaluated_picks_count=128
avg_ret_net=0.004431048028767
median_ret_net=0.0052763819095477
p25_ret_net=-0.0075615188321481
win_rate=0.53125
month_win_rate_min=0
month_avg_ret_net_min=-0.040489877530617
lookahead_violation_count=4
```

C29 failed gates:

```text
WS_BT_C29_GATE_FAIL_MONTH_WIN_RATE_PASS
WS_BT_C29_GATE_FAIL_LOOKAHEAD_PASS
```

Known bad months from C29:

```text
2025-06
2025-08
2026-03
```

Known missing path rows from C29 evidence:

```text
2025-06-04 MICE param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-06-04 MICE param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
2025-08-15 BBSI param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING
```

Known no-selection-leak flags from C29 row evidence:

```text
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

## Boundary

```text
FAILURE_ATTRIBUTION_ONLY=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C29_MUTATION=true
production_ready=false
```

C30 must not use return, realized exit result, MFE/MAE, future path price, or profile return as pick-selection input. Return is evaluation/diagnostic only after the C29 pick rows already exist.

Canonical execution model remains inherited and unchanged:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Classification Model

### Missing path / non-evaluable

A row is classified as missing path if any of the following is true:

```text
missing_path_data_flag=true
raw_ohlc_validated_flag=false
missing_path_reason_code is not null/empty
```

A non-evaluable row is any row with missing path or no numeric `profile_ret_net`.

### Selection leak

A row is classified as selection leak if any of the following is true:

```text
future_path_price_used_for_selection=true
profile_ret_net_used_for_selection=true
derived_mfe_mae_used_for_execution=true
```

### Actual lookahead violation

A row is classified as actual lookahead violation only when:

```text
lookahead_safe=false
and row is not missing path
```

or when `lookahead_violation_reason` explicitly indicates a future-data/selection/profile-return/MFE/MAE leak.

Missing OHLC path rows must not be counted as actual lookahead leak rows unless an explicit future-data leak reason exists.

### Clean evaluable

A row is clean evaluable only when:

```text
missing_path=false
actual_lookahead_violation=false
selection_leak=false
profile_ret_net is numeric
```

## Bad Month Attribution

C30 builds `bad_month_summary` from `metrics.month_summary[*].trade_month`, not from a generic `month` field. It also detects months where clean evaluable rows have zero wins.

Expected months from C29 evidence:

```text
2025-06
2025-08
2026-03
```

Each summary row records total rows, clean rows, missing path rows, avg/median/p25/win_rate, dominant branch, and dominant ticker.

## Source Branch Attribution

C30 groups by `selected_source_code` and keeps R09, G21, and G16 separate.

Each branch summary records:

```text
count
clean_count
missing_count
non_evaluable_count
avg_ret_net
median_ret_net
p25_ret_net
win_rate
bad_month_contribution
```

## Ticker Failure Attribution

C30 groups failure rows by ticker, trade_month, and selected_source_code. The reason summary can include missing path reason codes, actual lookahead reasons, selection-leak flags, non-evaluable return, or non-positive return.

## Runtime Result

Official operator validation was executed in the supported project environment and completed C30 attribution.

```text
PHPUNIT_C30=PASS
PHPUnit 9.6.34
OK (16 tests, 104 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
PHPUnit 9.6.34
OK (464 tests, 11004 assertions)

C30_RUNTIME=COMPLETED
status=C30_ATTRIBUTION_COMPLETED
reason_code=C30_ATTRIBUTION_COMPLETED
artifact_path=storage/app/watchlist/backtest/c30-oos-failure-attribution.json
artifact_hash=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
attribution_verdict=MIXED_DATA_AND_STRATEGY_FAILURE
expected_c29_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
actual_c29_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
c29_hash_match=1
c29_status=C29_OOS_PROOF_FAILED
production_ready=0
```

C30 completed without finding actual lookahead/future-data leak rows or selection-leak rows. The four rows reported by C29 as lookahead violations are attributed by C30 to missing D1-D5 raw OHLC path / non-evaluable rows.

## Classification Summary

```text
total_oos_pick_rows=132
reported_lookahead_violation_count=4
actual_lookahead_violation_count=0
selection_leak_count=0
missing_path_count=4
non_evaluable_pick_count=4
clean_evaluable_pick_count=128
```

## Clean Metrics

Clean metrics are computed only from clean evaluable rows after missing-path/non-evaluable rows, actual lookahead rows, and selection-leak rows are excluded.

```text
clean_evaluated_picks_count=128
clean_avg_ret_net=0.004431048028767
clean_median_ret_net=0.0052763819095477
clean_p25_ret_net=-0.0075615188321481
clean_win_rate=0.53125
clean_month_win_rate_min=0
clean_month_avg_ret_net_min=-0.040489877530617
```

## Final Bad Month Attribution

C30 confirms the bad-month concentration remains after the missing-path rows are separated from actual lookahead leak rows.

```text
2025-06: win_rate=0; includes G21 no_rule_profit_signal_before_fallback clean weakness and 2 R09 missing-path rows.
2025-08: win_rate=0; includes G16 next_open_delay_after_close_signal, G21 no_rule_profit_signal_before_fallback, and 2 R09 missing-path rows.
2026-03: win_rate=0; includes G16 next_open_delay_after_close_signal clean weakness.
```

## Final Source Branch Attribution

C30 keeps branch attribution separated instead of creating best-of-OOS or reselecting profiles.

```text
R09: contains the 4 missing-path/non-evaluable rows and otherwise remains a clean-evaluable branch for completed rows.
G21: contributes clean bad-month weakness, especially no_rule_profit_signal_before_fallback concentration.
G16: contributes clean bad-month weakness, especially next_open_delay_after_close_signal concentration.
```

## Artifact Output Path

```text
storage/app/watchlist/backtest/c30-oos-failure-attribution.json
```

Final operator artifact hash:

```text
C30_ARTIFACT_HASH=667b639951d6b566cc9b0fa6cf7dc278db92a8f0
```

## Final Status

```text
C30_SOURCE_IMPLEMENTED=true
C30_PHPUNIT_C30=PASS
C30_FULL_WATCHLIST_PHPUNIT=PASS
C30_RUNTIME=COMPLETED
C30_FINAL_STATUS=C30_ATTRIBUTION_COMPLETED
C30_ATTRIBUTION_VERDICT=MIXED_DATA_AND_STRATEGY_FAILURE
production_ready=false
```

C30 final decision: C29's reported lookahead failure is not confirmed as actual lookahead leakage. It is attributed to missing D1-D5 OHLC path rows. C29 still has real month/branch robustness weakness, so the final verdict is mixed data-completeness and strategy failure.

## Next Step After C30

C30 is final and complete. It does not unlock production readiness.

Next session should be C31 controlled rerun / gate reclassification, not tuning:

```text
C31_RECOMMENDED_SCOPE=CONTROLLED_C29_GATE_RECLASSIFICATION_AND_DATA_COMPLETENESS_RERUN
C31_NOT_TUNING=true
C31_NOT_BEST_OF_OOS=true
C31_MUST_SPLIT_LOOKAHEAD_GATE_FROM_DATA_COMPLETENESS_GATE=true
C31_MUST_KEEP_PRODUCTION_READY_FALSE=true
```

Required C31 direction:

- Patch C29/C31 gate attribution so missing OHLC path rows do not fail `LOOKAHEAD_PASS` as actual leak.
- Add/keep explicit data-completeness gate for missing D1-D5 raw OHLC path rows.
- Rerun the locked C29 proof path as a controlled C31 rerun with the same C28 G05 candidate lock.
- Do not retune, reselect, promote, or create production catalog.
- After data-completeness classification is clean, continue to C32 bad-month / branch robustness diagnostic for G21 and G16 weakness.

C30 does not unlock production readiness.
