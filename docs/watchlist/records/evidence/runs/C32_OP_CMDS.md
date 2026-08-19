# WS C32 Operator Validation Commands

C32 validation must run only against the locked C31 controlled gate reclassification artifact. Do not claim PASS until the commands below are actually run in the supported operator/CI PHP baseline.

## PHPUnit C32 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC32"
```

Expected marker:

```text
PHPUNIT_C32=PASS
```

If the command cannot run:

```text
PHPUNIT_C32=NOT_RUN
C32_OPERATOR_VALIDATION_REQUIRED=true
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
C32_OPERATOR_VALIDATION_REQUIRED=true
```

## C32 Runtime

```powershell
php artisan watchlist:backtest-c32-data-path-and-bad-month-diagnostic `
  --c31-artifact=storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json `
  --expected-c31-hash=4c6203621ed53ade368328a3aad567cbfc12f3a0 `
  --output=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json `
  --progress
```

If replacing an existing artifact intentionally, add:

```powershell
  --overwrite
```

Expected completed marker:

```text
status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
```

Expected blocked markers:

```text
status=C32_BLOCKED_MISSING_C31_ARTIFACT
status=C32_BLOCKED_C31_HASH_MISMATCH
status=C32_BLOCKED_UNEXPECTED_C31_STATUS
status=C32_BLOCKED_UNEXPECTED_C31_CONCLUSION
status=C32_BLOCKED_UNEXPECTED_C31_PROOF_STATUS
```

If runtime cannot be executed:

```text
C32_RUNTIME=NOT_RUN
C32_OPERATOR_VALIDATION_REQUIRED=true
```

## Read C32 Artifact Summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  expected_c31_hash,
  actual_c31_hash,
  c31_hash_match,
  c31_status,
  c31_reclassification_conclusion,
  c31_controlled_proof_status,
  data_path_remediation_status,
  bad_month_robustness_status,
  diagnostic_conclusion,
  next_step |
  Format-List
```

## Data Path Remediation Scope

```powershell
$run.data_path_remediation_scope | Format-List

$run.missing_path_replay_rows |
  Select-Object trade_month,trade_date,entry_date,ticker,param_id,row_code,selected_source_code,required_path_scope,missing_path_reason_code,remediation_status |
  Format-Table -AutoSize
```

## Bad Month Robustness Breakdown

```powershell
$run.bad_month_robustness_summary |
  Select-Object trade_month,total_rows,clean_rows,missing_path_rows,win_rate,dominant_branch,dominant_ticker,data_path_affected,clean_robustness_failure,failure_class |
  Format-Table -AutoSize
```

Expected months:

```text
2025-06
2025-08
2026-03
```

## Source Branch Robustness Breakdown

```powershell
$run.source_branch_robustness_summary |
  Select-Object selected_source_code,count,clean_count,missing_count,win_rate,bad_month_contribution_count,clean_bad_month_contribution_count,data_path_affected,robustness_diagnostic_flag,failure_class |
  Format-Table -AutoSize
```

Expected branches:

```text
G16
G21
R09
```

## Split Decision

```powershell
$run.split_decision | Format-List
```

Expected C32 decision:

```text
actual_lookahead_fix_required=false
selection_leak_fix_required=false
data_path_remediation_required=true
bad_month_robustness_diagnostic_required=true
oos_tuning_allowed=false
profile_reselection_allowed=false
production_promotion_allowed=false
production_ready=false
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json -Algorithm SHA1
```

Record the file SHA1 only after the artifact exists. The artifact also includes its stable `artifact_hash`.

## Final Operator Evidence

Validation executed successfully in this workspace.

```text
PHPUNIT_C32=PASS
OK (12 tests, 107 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (490 tests, 11237 assertions)

C32_RUNTIME=COMPLETED
status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
artifact_path=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
artifact_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
file_sha1=49F4A138BEF5B18841119F255F39ACDC2F97445B
production_ready=0
expected_c31_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
actual_c31_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
c31_hash_match=1
c31_status=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
data_path_remediation_status=C32_DATA_PATH_REMEDIATION_REQUIRED
bad_month_robustness_status=C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
diagnostic_conclusion=C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
next_step=C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING
```

## No Overclaim Rule

```text
DO_NOT_CLAIM_PHPUNIT_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_FULL_WATCHLIST_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_C32_RUNTIME_COMPLETED_IF_NOT_RUN=true
DO_NOT_PROMOTE_C32_TO_PRODUCTION=true
production_ready=false
```
