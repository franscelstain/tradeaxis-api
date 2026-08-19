# WS C34 Operator Validation Commands

C34 validation must run only against the locked C33 data-path replay proof artifact and the C32 bad-month source artifact linked by C33. Do not claim PASS until the commands below are actually run in the supported operator/CI PHP baseline.

## PHPUnit C34 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC34"
```

Expected marker:

```text
PHPUNIT_C34=PASS
```

If the command cannot run:

```text
PHPUNIT_C34=NOT_RUN
C34_OPERATOR_VALIDATION_REQUIRED=true
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
FULL_WATCHLIST_PHPUNIT=PASS
```

If the command cannot run:

```text
FULL_WATCHLIST_PHPUNIT=NOT_RUN
C34_OPERATOR_VALIDATION_REQUIRED=true
```

## C34 Runtime

```powershell
php artisan watchlist:backtest-c34-bad-month-robustness-diagnostic `
  --c33-artifact=storage/app/watchlist/backtest/c33-data-path-replay-proof.json `
  --expected-c33-hash=84bb77871515643b203de644fd34b4c748d1b2af `
  --c32-artifact=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json `
  --expected-c32-hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab `
  --output=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json `
  --progress
```

If replacing an existing artifact intentionally, add:

```powershell
  --overwrite
```

Expected completed marker:

```text
status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
```

Expected blocked markers:

```text
status=C34_BLOCKED_MISSING_C33_ARTIFACT
status=C34_BLOCKED_C33_HASH_MISMATCH
status=C34_BLOCKED_UNEXPECTED_C33_STATUS
status=C34_BLOCKED_UNEXPECTED_C33_CONCLUSION
status=C34_BLOCKED_C33_DATA_PATH_REPLAY_NOT_PASS
status=C34_BLOCKED_C33_DATA_COMPLETENESS_GATE_NOT_PASS
status=C34_BLOCKED_MISSING_C32_ARTIFACT
status=C34_BLOCKED_C32_HASH_MISMATCH
status=C34_BLOCKED_UNEXPECTED_C32_STATUS
status=C34_BLOCKED_UNEXPECTED_C32_CONCLUSION
status=C34_BLOCKED_UNEXPECTED_C32_BAD_MONTH_STATUS
status=C34_BLOCKED_NO_BAD_MONTH_SCOPE
```

If runtime cannot be executed:

```text
C34_RUNTIME=NOT_RUN
C34_OPERATOR_VALIDATION_REQUIRED=true
```

## Read C34 Artifact Summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json -Raw | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  expected_c33_hash,
  actual_c33_hash,
  c33_hash_match,
  c33_status,
  c33_data_path_replay_status,
  expected_c32_hash,
  actual_c32_hash,
  c32_hash_match,
  c32_status,
  bad_month_robustness_status,
  diagnostic_conclusion,
  next_step |
  Format-List
```

## Bad Month Rows

```powershell
$run.bad_month_diagnostic_rows |
  Select-Object trade_month,clean_rows,missing_path_rows_before_c33,data_path_cleared_by_c33,win_rate,dominant_branch,dominant_ticker,bad_month_failure_class,severity |
  Format-Table -AutoSize
```

Expected rows:

```text
2025-06 CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED
2025-08 CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_AFTER_DATA_PATH_CLEARED
2026-03 CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED
```

## Branch Robustness Rows

```powershell
$run.branch_robustness_rows |
  Select-Object selected_source_code,clean_count,missing_count_before_c33,data_path_cleared_by_c33,avg_ret_net,win_rate,clean_bad_month_contribution_count,aggregate_weakness_flag,robustness_diagnostic_flag,branch_failure_class |
  Format-Table -AutoSize
```

Expected branch classes:

```text
G16=C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW
G21=C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED
R09=DATA_PATH_CLEARED_BRANCH_REVIEW_ONLY
```

## Robustness Decision

```powershell
$run.robustness_decision | ConvertTo-Json -Depth 5
```

Expected C34 decision:

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

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json -Algorithm SHA1
```

Record the file SHA1 only after the artifact exists. The artifact also includes its stable `artifact_hash`.

## Final Operator Evidence

Validation executed successfully in this workspace.

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
expected_c33_hash=84bb77871515643b203de644fd34b4c748d1b2af
actual_c33_hash=84bb77871515643b203de644fd34b4c748d1b2af
c33_hash_match=1
c33_status=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
c33_data_path_replay_status=C33_DATA_PATH_REPLAY_PASS
expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
actual_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
c32_hash_match=1
c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
bad_month_robustness_status=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
bad_month_failure_count=3
branch_robustness_flag_count=2
strategy_robustness_redesign_required=1
diagnostic_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
next_step=C35_IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC_NO_OOS_TUNING
```

## No Overclaim Rule

```text
DO_NOT_CLAIM_PHPUNIT_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_FULL_WATCHLIST_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_C34_RUNTIME_COMPLETED_IF_NOT_RUN=true
DO_NOT_PROMOTE_C34_TO_PRODUCTION=true
DO_NOT_TUNE_FROM_C34_BAD_MONTH_EVIDENCE=true
production_ready=false
```
