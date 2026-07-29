# C171 Final Failed/Not-Ready Closure Operator Commands

No migration is required. Keep these exact files under `storage/app/watchlist/backtest`:

```text
c171-c01-v3-official-is-paramset-11.json
c171-final-official-is-paramset-12.json
c171-final-official-is-paramset-13.json
c171-final-official-is-paramset-14.json
c171-final-official-is-summary.csv
```

## Validation

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WeeklySwingC171FinalFailedNotReadyClosureTest"

vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171StaticGuardTest"

vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Seal the closure

```powershell
php artisan watchlist:backtest-c171-seal-final-failed-not-ready-closure `
  --anchor-artifact=storage/app/watchlist/backtest/c171-c01-v3-official-is-paramset-11.json `
  --artifact-dir=storage/app/watchlist/backtest `
  --summary-csv=storage/app/watchlist/backtest/c171-final-official-is-summary.csv `
  --approval-reference=C171_OPERATOR_APPROVED_FINAL_FAILED_NOT_READY_CLOSURE_ONLY `
  --operator-approved `
  --output=storage/app/watchlist/backtest/c171-final-failed-not-ready-closure.json `
  --overwrite `
  --progress
```

Expected:

```text
status=C171_FINAL_FAILED_NOT_READY_CLOSURE_SEALED
reason_code=C171_NO_FINAL_CANDIDATE_PASSED_CANONICAL_IS_GATES
final_decision=C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION
c171_topic_closed=1
final_candidate_count=3
final_passing_candidate_count=0
database_identity_verified=1
additional_c171_candidate_catalog_allowed=0
oos_allowed=0
c172_allowed=0
promotion_allowed=0
plan_allowed=0
production_ready=0
official_is_runtime_invoked=0
oos_runtime_invoked=0
oos_table_read=0
database_mutated=0
```

After success, obtain the file SHA1:

```powershell
Get-FileHash storage/app/watchlist/backtest/c171-final-failed-not-ready-closure.json -Algorithm SHA1
```
