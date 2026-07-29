# C171 Final Bounded Remediation Operator Commands

No migration is required.

## Focused validation

```powershell
vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171FinalBoundedRemediationParamGridCatalogTest"

vendor\bin\phpunit tests\Unit\Watchlist `
  --filter "WatchlistBacktestC171StaticGuardTest"

vendor\bin\phpunit tests\Unit\Watchlist --filter "C171"
vendor\bin\phpunit tests\Unit\Watchlist
```

## Persist the final catalog

The six exact files `c171-c01-v3-official-is-paramset-{5,7,8,9,10,11}.json` and the exact summary CSV must already exist under `storage/app/watchlist/backtest`.

```powershell
php artisan watchlist:backtest-c171-persist-final-bounded-remediation-draft-catalog `
  --source-eval-id=204 `
  --source-param-set-id=11 `
  --artifact-dir=storage/app/watchlist/backtest `
  --summary-csv=storage/app/watchlist/backtest/c171-c01-v3-official-is-summary.csv `
  --approval-reference=C171_OPERATOR_APPROVED_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTENCE_ONLY `
  --operator-approved `
  --output-dir=storage/app/watchlist/backtest/c171-final-bounded-remediation-draft-catalog `
  --output=storage/app/watchlist/backtest/c171-final-bounded-remediation-draft-catalog.json `
  --overwrite `
  --progress
```

Expected boundary markers:

```text
status=C171_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTED_CLOSURE_RULE_LOCKED
catalog_row_count=3
additional_c171_candidate_catalog_allowed=0
official_is_runtime_invoked=0
oos_runtime_invoked=0
paramset_promoted=0
plan_run_created=0
production_ready=0
```

After persistence, run official IS once for the three returned `param_set_id` values. If none passes every canonical IS gate, close C171 as `C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION`; do not create another catalog.


## Operator result

The final catalog was persisted as paramsets `12,13,14` and official IS completed as evals `205,206,207`. All three failed canonical gates. Continue only with `WS_C171_FINAL_FAILED_NOT_READY_CLOSURE_OPERATOR_COMMANDS.md`; no additional candidate catalog is allowed.
