# WS C170 Operator Validation Commands

C170 is fail-closed IS remediation. Do not insert OOS rows, promote the DRAFT, or run PLAN/CONFIRM.

## C28 Execution-Route Revalidation

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c28-rule-revision-tiebreak-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --candidate-profile-code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY `
  --input-c27-artifact=storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json `
  --output=storage/app/watchlist/backtest/c170-c28-g05-execution-route-revalidation.json `
  --overwrite
```

Executed result:

```text
status=PASS
artifact_hash=1ef90eea6d196db0584ca8ff8da77064a8405e89
evaluated_picks_count=1575
lookahead_violation_count=1575
future_derived_route_count=1575
execution_time_route_availability_pass=0
candidate_failure_reason_codes=LOOKAHEAD_OR_PATH_SAFETY_WEAK,FUTURE_DERIVED_BUCKET_ROUTE_NOT_EXECUTABLE
c28_revised_candidate_ready=0
c29_oos_proof_recommended=0
oos_executed=0
production_ready=0
```

## C29 Pre-OOS Guard

```powershell
php artisan watchlist:backtest-c29-oos-proof `
  --c28-artifact=storage/app/watchlist/backtest/c170-c28-g05-execution-route-revalidation.json `
  --expected-c28-hash=1ef90eea6d196db0584ca8ff8da77064a8405e89 `
  --candidate-profile-code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY `
  --from=2025-05-22 `
  --to=2026-05-29 `
  --output=storage/app/watchlist/backtest/c170-c29-future-route-blocked.json `
  --overwrite
```

Expected and executed exit code is `1`:

```text
status=C29_BLOCKED_INVALID_C28_SOURCE
reason_code=WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN
artifact_hash=55cda589a69a204078a631ffe74a8f60b15e080d
c28_hash_match=1
production_ready=0
```

## Live Official Evidence State

```text
watchlist_bt_param_grid=156
watchlist_bt_eval=186
watchlist_bt_picks_ws=0
watchlist_bt_universe_ws=0
watchlist_bt_cutoffs_ws=0
watchlist_bt_oos_eval_ws=0
```

```text
watchlist_bt_picks_ws.eval_id=absent
watchlist_bt_universe_ws.eval_id=absent
watchlist_bt_cutoffs_ws.eval_id=absent
```

## Focused Tests

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/Watchlist `
  --filter 'WatchlistBacktestC28|WatchlistBacktestC29|WeeklySwingParamsetPersistenceAndPromotion'
```

Executed result:

```text
OK (42 tests, 309 assertions)
```

## Final Regression

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/Watchlist
git diff --check
```

Executed result:

```text
PHPUNIT_WATCHLIST_FULL=OK (7066 tests, 47680 assertions)
GIT_DIFF_CHECK=PASS
```

The locked owner documents `17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md` and `18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md` were left byte-for-byte unchanged. C170 corrections are recorded in the audit/status documents instead.
