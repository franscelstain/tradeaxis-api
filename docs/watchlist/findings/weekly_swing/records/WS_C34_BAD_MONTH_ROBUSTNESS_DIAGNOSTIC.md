# WS C34 Bad Month Robustness Diagnostic

## Purpose

C34 follows C33 by diagnosing the bad-month and source-branch robustness failures after the C32 data-path blocker has been cleared.

C34 is not tuning, not candidate reselection, not best-of-OOS, not catalog promotion, not market-data replay, and not production rollout.

## Input Artifacts

```text
input_c33_artifact=storage/app/watchlist/backtest/c33-data-path-replay-proof.json
expected_c33_hash=84bb77871515643b203de644fd34b4c748d1b2af
expected_c33_status=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
expected_c33_conclusion=C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE
expected_c33_replay_status=C33_DATA_PATH_REPLAY_PASS

input_c32_artifact=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
expected_c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
expected_c32_bad_month_status=C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
```

## Boundary

```text
BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_ONLY=true
FILE_ARTIFACT_DIAGNOSTIC_ONLY=true
NO_MARKET_DATA_REPLAY=true
NO_DB_READ=true
NO_DB_WRITE=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C33_MUTATION=true
production_ready=false
```

## Runtime Result

```text
PHPUNIT_C34=PASS
OK (13 tests, 119 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (518 tests, 11501 assertions)

C34_RUNTIME=COMPLETED
status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
artifact_path=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
artifact_hash=1dcf355095334796c2f4558823a1882e71e3ed30
file_sha1=71897A94B665CAF2C5A632915FE5B48AE99726A2
production_ready=0
```

## Artifact Locks

```text
expected_c33_hash=84bb77871515643b203de644fd34b4c748d1b2af
actual_c33_hash=84bb77871515643b203de644fd34b4c748d1b2af
c33_hash_match=1
c33_status=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
c33_data_path_replay_status=C33_DATA_PATH_REPLAY_PASS

expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
actual_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
c32_hash_match=1
c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
```

## Bad Month Diagnostic Rows

```text
2025-06 clean_rows=10 missing_before_c33=2 data_path_cleared_by_c33=true win_rate=0 dominant_branch=G21 dominant_ticker=GWSA class=CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED severity=HIGH_RISK
2025-08 clean_rows=7 missing_before_c33=2 data_path_cleared_by_c33=true win_rate=0 dominant_branch=G21 dominant_ticker=SMKL class=CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED severity=HIGH_RISK
2026-03 clean_rows=4 missing_before_c33=0 data_path_cleared_by_c33=null win_rate=0 dominant_branch=G16 dominant_ticker=BINA class=CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED severity=HIGH_RISK
```

## Branch Robustness Rows

```text
G16 clean=18 missing_before_c33=0 avg_ret_net=0.00737983091926925 win_rate=0.6111111111111112 clean_bad_month_contribution=7 aggregate_weakness=false robustness_flag=true class=C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW
G21 clean=80 missing_before_c33=0 avg_ret_net=-0.007043371221106404 win_rate=0.3375 clean_bad_month_contribution=14 aggregate_weakness=true robustness_flag=true class=C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED
R09 clean=30 missing_before_c33=4 data_path_cleared_by_c33=true avg_ret_net=0.03326022962746119 win_rate=1 clean_bad_month_contribution=0 aggregate_weakness=false robustness_flag=false class=DATA_PATH_CLEARED_BRANCH_REVIEW_ONLY
```

## Decision

```text
bad_month_robustness_status=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
bad_month_failure_count=3
data_path_cleared_bad_month_count=2
branch_robustness_flag_count=2
aggregate_branch_weakness_count=1
bad_months_requiring_review=2025-06,2025-08,2026-03
branches_requiring_review=G16,G21
strategy_robustness_redesign_required=true
oos_tuning_allowed=false
profile_reselection_allowed=false
production_promotion_allowed=false
production_ready=false
```

## Diagnostic Conclusion

```text
C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
```

## Next Step

```text
C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING
```

C34 confirms the remaining blocker is clean bad-month/branch robustness, not data-path completeness. It does not unlock production readiness and does not authorize OOS tuning or best-of-OOS selection.
