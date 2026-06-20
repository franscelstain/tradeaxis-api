# WS C33 Operator Validation Commands

C33 validation must run only against the locked C32 data-path and bad-month diagnostic artifact. Do not claim PASS until the commands below are actually run in the supported operator/CI PHP baseline.

## PHPUnit C33 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC33"
```

Expected marker:

```text
PHPUNIT_C33=PASS
```

If the command cannot run:

```text
PHPUNIT_C33=NOT_RUN
C33_OPERATOR_VALIDATION_REQUIRED=true
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
C33_OPERATOR_VALIDATION_REQUIRED=true
```

## C33 Runtime

```powershell
php artisan watchlist:backtest-c33-data-path-replay-proof `
  --c32-artifact=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json `
  --expected-c32-hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab `
  --output=storage/app/watchlist/backtest/c33-data-path-replay-proof.json `
  --progress
```

If replacing an existing artifact intentionally, add:

```powershell
  --overwrite
```

Expected completed marker:

```text
status=C33_DATA_PATH_REPLAY_PROOF_COMPLETED
```

Expected blocked markers:

```text
status=C33_BLOCKED_MISSING_C32_ARTIFACT
status=C33_BLOCKED_C32_HASH_MISMATCH
status=C33_BLOCKED_UNEXPECTED_C32_STATUS
status=C33_BLOCKED_UNEXPECTED_C32_CONCLUSION
status=C33_BLOCKED_UNEXPECTED_C32_DATA_PATH_STATUS
status=C33_BLOCKED_NO_DATA_PATH_REPLAY_SCOPE
```

If runtime cannot be executed:

```text
C33_RUNTIME=NOT_RUN
C33_OPERATOR_VALIDATION_REQUIRED=true
```

## Read C33 Artifact Summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c33-data-path-replay-proof.json -Raw | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  expected_c32_hash,
  actual_c32_hash,
  c32_hash_match,
  c32_status,
  c32_diagnostic_conclusion,
  c32_data_path_remediation_status,
  data_path_replay_status,
  diagnostic_conclusion,
  next_step |
  Format-List
```

## Replay Scope And Rows

```powershell
$run.replay_scope | Format-List

$run.replay_rows |
  Select-Object trade_date,entry_date,ticker,param_id,row_code,selected_source_code,raw_ohlc_replay_status,raw_ohlc_validated_flag,missing_path_data_flag |
  Format-Table -AutoSize
```

Expected rows:

```text
2025-06-04 MICE param_id=151 status=PASS
2025-06-04 MICE param_id=152 status=PASS
2025-08-15 BBSI param_id=151 status=PASS
2025-08-15 BBSI param_id=152 status=PASS
```

## Replay Dates

```powershell
$run.replay_rows |
  Select-Object trade_date,entry_date,ticker,param_id,@{Name='required_path_dates';Expression={$_.required_path_dates -join ','}},@{Name='missing_path_dates';Expression={$_.missing_path_dates -join ','}},@{Name='invalid_path_dates';Expression={$_.invalid_path_dates -join ','}} |
  Format-Table -AutoSize
```

Expected date windows:

```text
MICE entry_date=2025-06-05 required_path_dates=2025-06-05,2025-06-10,2025-06-11,2025-06-12,2025-06-13
BBSI entry_date=2025-08-19 required_path_dates=2025-08-19,2025-08-20,2025-08-21,2025-08-22,2025-08-25
```

## Replay Summary

```powershell
$run.replay_summary | ConvertTo-Json -Depth 5
$run.data_completeness_gate_after_replay | Format-List
```

Expected C33 decision:

```text
data_path_replay_status=C33_DATA_PATH_REPLAY_PASS
data_completeness_gate_after_replay=PASS
replay_pass_count=4
replay_fail_count=0
replay_blocked_count=0
missing_path_date_count=0
invalid_path_date_count=0
actual_lookahead_fix_required=false
selection_leak_fix_required=false
oos_tuning_allowed=false
profile_reselection_allowed=false
production_promotion_allowed=false
production_ready=false
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c33-data-path-replay-proof.json -Algorithm SHA1
```

Record the file SHA1 only after the artifact exists. The artifact also includes its stable `artifact_hash`.

## Final Operator Evidence

Validation executed successfully in this workspace.

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
expected_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
actual_c32_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
c32_hash_match=1
c32_status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
data_path_replay_status=C33_DATA_PATH_REPLAY_PASS
data_completeness_gate_after_replay=PASS
replay_row_count=4
replay_pass_count=4
replay_fail_count=0
replay_blocked_count=0
diagnostic_conclusion=C33_DATA_PATH_REPLAY_CONFIRMED_D1_TO_D5_RAW_OHLC_AVAILABLE
next_step=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_AFTER_C33_NO_OOS_TUNING
```

## No Overclaim Rule

```text
DO_NOT_CLAIM_PHPUNIT_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_FULL_WATCHLIST_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_C33_RUNTIME_COMPLETED_IF_NOT_RUN=true
DO_NOT_PROMOTE_C33_TO_PRODUCTION=true
DO_NOT_CLAIM_FULL_OOS_PASS_FROM_C33=true
production_ready=false
```
