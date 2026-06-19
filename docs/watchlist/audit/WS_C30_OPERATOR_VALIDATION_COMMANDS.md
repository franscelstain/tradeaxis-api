# WS C30 Operator Validation Commands

C30 validation must run only against the locked C29 failed artifact. Do not claim PASS until the commands below are actually run in the supported operator/CI PHP baseline.

## PHPUnit C30 Only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC30"
```

Expected marker:

```text
PHPUNIT_C30=PASS
```

If the command cannot run:

```text
PHPUNIT_C30=NOT_RUN
C30_OPERATOR_VALIDATION_REQUIRED=true
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
C30_OPERATOR_VALIDATION_REQUIRED=true
```

## C30 Runtime

```powershell
php artisan watchlist:backtest-c30-oos-failure-attribution `
  --c29-artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json `
  --expected-c29-hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9 `
  --output=storage/app/watchlist/backtest/c30-oos-failure-attribution.json `
  --progress
```

If replacing an existing artifact intentionally, add:

```powershell
  --overwrite
```

Expected completed marker:

```text
status=C30_ATTRIBUTION_COMPLETED
```

Expected blocked markers:

```text
status=C30_BLOCKED_MISSING_C29_ARTIFACT
status=C30_BLOCKED_C29_HASH_MISMATCH
status=C30_BLOCKED_UNEXPECTED_C29_STATUS
```

If the runtime command cannot be executed:

```text
C30_RUNTIME=NOT_RUN
C30_OPERATOR_VALIDATION_REQUIRED=true
```

## Read C30 Artifact Summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c30-oos-failure-attribution.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  attribution_verdict,
  production_ready,
  expected_c29_hash,
  actual_c29_hash,
  c29_hash_match,
  c29_status |
  Format-List
```

## Classification Summary

```powershell
$run.classification_summary | Format-List
```

Expected fields:

```text
total_oos_pick_rows
reported_lookahead_violation_count
actual_lookahead_violation_count
selection_leak_count
missing_path_count
non_evaluable_pick_count
clean_evaluable_pick_count
```

## Clean Metrics

```powershell
$run.clean_metrics | Format-List
```

## Bad Month Breakdown

```powershell
$run.bad_month_summary |
  Sort-Object trade_month |
  Format-Table -AutoSize
```

Expected C29 bad months to inspect:

```text
2025-06
2025-08
2026-03
```

## Source Branch Breakdown

```powershell
$run.source_branch_summary |
  Sort-Object selected_source_code |
  Format-Table -AutoSize
```

Expected branches to inspect:

```text
R09
G21
G16
```

## Missing Path Rows

```powershell
$run.missing_path_rows |
  Select-Object trade_month,trade_date,ticker,param_id,selected_source_code,missing_path_reason_code |
  Format-Table -AutoSize
```

## Actual Lookahead Violation Rows

```powershell
$run.actual_lookahead_violation_rows |
  Format-Table -AutoSize
```

## Selection Leak Rows

```powershell
$run.selection_leak_rows |
  Format-Table -AutoSize
```

## Diagnostics

```powershell
$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

## Artifact Hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c30-oos-failure-attribution.json -Algorithm SHA1
```

Record the resulting hash only after the artifact exists.

## No Overclaim Rule

```text
DO_NOT_CLAIM_PHPUNIT_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_FULL_WATCHLIST_PASS_IF_NOT_RUN=true
DO_NOT_CLAIM_C30_RUNTIME_COMPLETED_IF_NOT_RUN=true
DO_NOT_PROMOTE_C30_TO_PRODUCTION=true
production_ready=false
```

## Final Operator Evidence

Operator validation executed successfully in the supported project environment.

```text
PHPUNIT_C30=PASS
OK (16 tests, 104 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
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
classification_total_oos_pick_rows=132
classification_reported_lookahead_violation_count=4
classification_actual_lookahead_violation_count=0
classification_selection_leak_count=0
classification_missing_path_count=4
classification_non_evaluable_pick_count=4
classification_clean_evaluable_pick_count=128
C30_FINAL_STATUS=C30_ATTRIBUTION_COMPLETED
```

C30 official conclusion: reported C29 lookahead violations are missing-path/non-evaluable rows, not actual lookahead leak rows. The final verdict remains mixed because clean bad-month / branch weakness is still present.
