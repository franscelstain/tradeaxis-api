# WS C33 Data Path Replay Proof

## Purpose

C33 follows C32 by proving whether the four C32 missing D1-D5 raw OHLC path rows are now readable from the current canonical market-data path.

C33 is not tuning, not candidate reselection, not best-of-OOS, not catalog promotion, not source acquisition, and not production rollout.

## Input C32 Artifact

```text
input_c32_artifact=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
expected_c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
expected_c32_conclusion=C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
expected_c32_data_path_status=C32_DATA_PATH_REMEDIATION_REQUIRED
```

## Boundary

```text
DATA_PATH_REPLAY_PROOF_ONLY=true
READ_ONLY_CURRENT_EOD_BARS_REPLAY_PROOF=true
NO_SOURCE_ACQUISITION=true
NO_BAR_INGEST=true
NO_SOURCE_MASTER_WRITE=true
NO_EOD_BARS_WRITE=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C32_MUTATION=true
production_ready=false
```

## Runtime Result

```text
PHPUNIT_C33=PASS
OK (15 tests, 145 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (505 tests, 11382 assertions)

C33_RUNTIME=COMPLETED
status=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
artifact_path=storage/app/watchlist/backtest/c33-data-path-replay-proof.json
artifact_hash=84bb77871515643b203de644fd34b4c748d1b2af
file_sha1=1B0558C823732649DC7487154E5045BE86A160CC
production_ready=0
```

## C32 Lock Validation

```text
expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
actual_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
c32_hash_match=1
c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
c32_diagnostic_conclusion=C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
c32_data_path_remediation_status=C32_DATA_PATH_REMEDIATION_REQUIRED
```

## Replay Scope

```text
required_path_scope=D1_TO_D5_RAW_OHLC_PATH
replay_row_count=4
affected_trade_dates=2025-06-04,2025-08-15
affected_entry_dates=2025-06-05,2025-08-19
affected_tickers=BBSI,MICE
affected_param_ids=151,152
affected_source_codes=R09
```

Replay rows:

```text
2025-06-04 MICE param_id=151 entry_date=2025-06-05 path_dates=2025-06-05,2025-06-10,2025-06-11,2025-06-12,2025-06-13 status=PASS
2025-06-04 MICE param_id=152 entry_date=2025-06-05 path_dates=2025-06-05,2025-06-10,2025-06-11,2025-06-12,2025-06-13 status=PASS
2025-08-15 BBSI param_id=151 entry_date=2025-08-19 path_dates=2025-08-19,2025-08-20,2025-08-21,2025-08-22,2025-08-25 status=PASS
2025-08-15 BBSI param_id=152 entry_date=2025-08-19 path_dates=2025-08-19,2025-08-20,2025-08-21,2025-08-22,2025-08-25 status=PASS
```

## Replay Summary

```text
data_path_replay_status=C33_DATA_PATH_REPLAY_PASS
data_completeness_gate_after_replay=PASS
replay_pass_count=4
replay_fail_count=0
replay_blocked_count=0
missing_path_date_count=0
invalid_path_date_count=0
replay_reason=WS_BT_C33_D1_TO_D5_RAW_OHLC_PATH_REPLAY_PASS count=4
```

Publication/run evidence:

```text
MICE path publications=37816,37817,37818,37819,37820 runs=37669,37670,37671,37672,37673
BBSI path publications=37865,37866,37867,37868,37869 runs=37718,37719,37720,37721,37722
```

## Decision

```text
actual_lookahead_fix_required=false
selection_leak_fix_required=false
oos_tuning_allowed=false
profile_reselection_allowed=false
production_promotion_allowed=false
production_ready=false
```

## Diagnostic Conclusion

```text
C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE
```

## Next Step

```text
C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING
```

C33 clears the data-path replay proof for the C32 missing path scope only. It does not by itself make the watchlist production-ready and does not rerun or pass the full controlled OOS gate. C34 must diagnose bad-month robustness after clean data evidence, still without OOS tuning or production promotion.
