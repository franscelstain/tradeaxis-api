# WS Tail Risk S01 Operator Validation Commands

## Read-only diagnostic

```powershell
php artisan watchlist:weekly-swing-tail-risk-s01-diagnostic `
  --operator-approved `
  --approval-reference=WS_TAIL_RISK_S01_OPERATOR_APPROVED_READ_ONLY_IS_DIAGNOSTIC `
  --overwrite
```

Expected immutable result:

```text
status=WS_TAIL_RISK_S01_DIAGNOSTIC_COMPLETED
source_eval_id=211
candidate_design_allowed_count=3
official_is_runtime_invoked=0
oos_runtime_invoked=0
oos_table_read=0
artifact_hash=f13e0d2fe4fddd6c16bd4878bfc75d898713e72d
```

## Persist the three locked DRAFTs

```powershell
php artisan watchlist:weekly-swing-tail-risk-s01-persist-draft-catalog `
  --operator-approved `
  --approval-reference=WS_TAIL_RISK_S01_OPERATOR_APPROVED_DRAFT_CATALOG `
  --overwrite
```

Expected DRAFT identities are param sets `20-22`, BT params `174-176`, catalog
hash `cfbcef8b02539e0b90ed8a5f0c38f409edbdf0b4`, and no Official IS/OOS call
during persistence.

## Initial Official IS

Run this command separately for param sets `20`, `21`, and `22`:

```powershell
php artisan watchlist:weekly-swing-tail-risk-s01-official-is `
  --param-set-id=<PARAM_SET_ID> `
  --operator-approved `
  --approval-reference=WS_TAIL_RISK_S01_OPERATOR_APPROVED_OFFICIAL_IS_ONLY `
  --output=storage/app/watchlist/backtest/ws-tail-risk-s01-official-is-paramset-<PARAM_SET_ID>.json `
  --overwrite
```

The historical sealed results are evals `212-214`. All returned non-zero
because every candidate failed at least one unchanged canonical IS gate. A
non-zero process code is therefore expected for these exact failed artifacts.

## Single remediation

```powershell
php artisan watchlist:weekly-swing-tail-risk-s01-persist-remediation-draft `
  --operator-approved `
  --approval-reference=WS_TAIL_RISK_S01_OPERATOR_APPROVED_SINGLE_REMEDIATION_DRAFT `
  --overwrite
```

Expected identity:

```text
catalog_code=WS_BT_GRID_TAIL_RISK_S01_REMEDIATION_2026_07
catalog_hash=6ff1031b7cb5e7023d079cf22f72e35b9ac38b2e
param_set_id=24
bt_param_id=177
remediation_rounds_used=1
remediation_rounds_remaining=0
```

Run its exact Official IS:

```powershell
php artisan watchlist:weekly-swing-tail-risk-s01-official-is `
  --param-set-id=24 `
  --operator-approved `
  --approval-reference=WS_TAIL_RISK_S01_OPERATOR_APPROVED_OFFICIAL_IS_ONLY `
  --output=storage/app/watchlist/backtest/ws-tail-risk-s01-remediation-official-is-paramset-24.json `
  --overwrite
```

Expected sealed status is
`WS_TAIL_RISK_S01_REMEDIATION_OFFICIAL_IS_FAILED_S01_CLOSED`, eval `215`,
artifact hash `716c35b5e2cd59c8f6a2b8f9ddf94eb975cf8c21`, and non-zero process code.

## Mandatory boundary assertions

```text
canonical_is_window_match=true
strict_is_boundary=true
canonical_gates_changed=false
future_derived_route_used=false
oos_runtime_invoked=false
oos_rows_before=oos_rows_after=0
paramset_promoted=false
active_paramset_created=false
plan_run_created=false
production_ready=false
```

## Test commands

```powershell
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingTailRiskS01DiagnosticTest.php
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingTailRiskS01Test.php
vendor\bin\phpunit tests\Unit\Watchlist\WeeklySwingNewStrategyR02Test.php
vendor\bin\phpunit tests\Unit\Watchlist
```

Artifact reruns are idempotent only when all immutable identities and evidence
hashes match. `--overwrite` replaces the local file; it does not authorize
rewriting a conflicting persisted evaluation.
