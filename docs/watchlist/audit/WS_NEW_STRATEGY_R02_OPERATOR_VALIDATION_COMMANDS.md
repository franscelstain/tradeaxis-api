# WS New Strategy R02 Operator Validation Commands

## Focused test

```powershell
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingNewStrategyR02Test.php
```

## Persist locked DRAFT catalog

```powershell
php artisan watchlist:weekly-swing-new-strategy-r02-persist-draft-catalog `
  --operator-approved `
  --approval-reference=WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_MINIMAL_DRAFT_CATALOG `
  --overwrite `
  --progress
```

## Official IS

Jalankan untuk param set `15`, `16`, dan `17` dengan output terpisah:

```powershell
php artisan watchlist:weekly-swing-new-strategy-r02-official-is `
  --param-set-id=<PARAM_SET_ID> `
  --operator-approved `
  --approval-reference=WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_OFFICIAL_IS_ONLY `
  --output=storage/app/watchlist/backtest/ws-new-strategy-r02-official-is-paramset-<PARAM_SET_ID>.json `
  --overwrite `
  --progress
```

## Mandatory assertions

```text
strict_is_boundary=true
canonical_is_window_match=true
official_evidence_persistence_status=INSERTED_OR_IDEMPOTENT
oos_runtime_invoked=false
oos_rows_before=oos_rows_after
paramset_promoted=false
plan_run_created=false
production_ready=false
```

## Single allowed remediation

Persist hanya remediation H2 yang sudah dikunci:

```powershell
php artisan watchlist:weekly-swing-new-strategy-r02-persist-remediation-draft `
  --operator-approved `
  --approval-reference=WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_SINGLE_REMEDIATION_DRAFT `
  --source-is=storage/app/watchlist/backtest/ws-new-strategy-r02-official-is-paramset-16.json `
  --overwrite `
  --progress
```

Jalankan Official IS untuk exact remediation DRAFT:

```powershell
php artisan watchlist:weekly-swing-new-strategy-r02-official-is `
  --param-set-id=19 `
  --operator-approved `
  --approval-reference=WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_OFFICIAL_IS_ONLY `
  --output=storage/app/watchlist/backtest/ws-new-strategy-r02-remediation-official-is-paramset-19.json `
  --overwrite `
  --progress
```

Tambahan assertion remediation:

```text
remediation_count=1
max_remediation_count=1
selection_unchanged_from_h2=true
fixed_execution_before_entry=true
future_derived_route_used=false
canonical_gates_changed=false
OOS_BEFORE_ALL_IS_GATES_PASS=FORBIDDEN
```
