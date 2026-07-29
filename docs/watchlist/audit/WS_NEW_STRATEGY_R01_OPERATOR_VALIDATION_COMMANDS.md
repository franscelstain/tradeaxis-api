# WS New Strategy R01 Operator Validation Commands

Jalankan dari root repository.

## 1. Focused tests

```powershell
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingNewStrategyR01ResearchDiagnosticTest.php
```

## 2. C171 closure regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingC171FinalFailedNotReadyClosureTest.php
```

## 3. Full C171 and R01 regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "C171|NewStrategyR01"
```

## 4. Runtime diagnostic

```powershell
php artisan watchlist:weekly-swing-new-strategy-r01-diagnostic `
  --operator-approved `
  --approval-reference=WS_NEW_STRATEGY_R01_OPERATOR_APPROVED_READ_ONLY_RESEARCH `
  --overwrite `
  --progress
```

## 5. Full Watchlist regression

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Required runtime assertions

```text
status=WS_NEW_STRATEGY_R01_DIAGNOSTIC_COMPLETED
source_eval_id=204
source_param_set_id=11
official_pick_replay_parity.pass=true
official_pick_replay_parity.mismatch_count=0
draft_paramset_created=false
official_is_runtime_invoked=false
oos_runtime_invoked=false
oos_table_read=false
paramset_promoted=false
plan_run_created=false
production_ready=false
database_boundary_counts_before=database_boundary_counts_after
```

Artifact resmi:

```text
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic.json
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-trades.csv
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-segments.csv
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-winner-loser.csv
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-monthly-yearly.csv
storage/app/watchlist/backtest/ws-new-strategy-r01-diagnostic-hypothesis-lock.json
```

## Final validation result

```text
R01_RUNTIME=PASS
R01_FOCUSED_PHPUNIT=PASS_3_TESTS_35_ASSERTIONS
R01_C171_REGRESSION=PASS_63_TESTS_695_ASSERTIONS
R01_FULL_WATCHLIST_PHPUNIT=PASS_7137_TESTS_48447_ASSERTIONS
```
