# WS C15 Operator Validation Commands

Status: C15_FINAL_OPERATOR_EVIDENCE_RECORDED / IS_QUALITY_FAILED / C15_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY

## Final C15 Operator Evidence

This document now records the final C15 validation evidence. Commands remain here for replay/reference, but C15 is no longer waiting for operator validation.

Final decision:

```text
C15_RUNTIME_PAYLOAD_STATUS=PASS
C15_DRILLDOWN_STATUS=PASS_RUNTIME_READY
C15_IS_CALIBRATION_STATUS=C15_GRID_FAILED_IS_QUALITY
C15_VALID_PARAM_COUNT=0
C15_STRATEGY_DECISION=REJECTED_AS_IS_QUALITY_CATALOG
OOS_NOT_RUN
production_ready=0
```

## Scope

C15 is an immutable IS research catalog plus runtime guard extension.

Catalog identity:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
candidate_selection_extension=C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION
```

No C15 command output authorizes production readiness. OOS was not run and remains unauthorized.

## 1. C15 PHPUnit filter

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC15"
```

Recorded operator result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

..........                                                        10 / 10 (100%)

Time: 00:00.152, Memory: 18.00 MB

OK (10 tests, 534 assertions)
```

Status: PASS.

## 2. C15 supporting service PHPUnit filters

Commands:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistCandidateUniverseService"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistScoringService"
```

Recorded operator result:

```text
WatchlistCandidateUniverseService: OK (5 tests, 68 assertions)
WatchlistScoringService: OK (9 tests, 107 assertions)
```

Status: PASS.

## 3. Full Watchlist regression

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Recorded operator result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

...............................................................  63 / 341 ( 18%)
............................................................... 126 / 341 ( 36%)
............................................................... 189 / 341 ( 55%)
............................................................... 252 / 341 ( 73%)
............................................................... 315 / 341 ( 92%)
..........................                                      341 / 341 (100%)

Time: 00:01.756, Memory: 30.00 MB

OK (341 tests, 7771 assertions)
```

Status: PASS.

## 4. C15 seed

Command:

```powershell
php artisan watchlist:backtest-c15-param-grid-seed
```

Recorded operator result:

```text
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
inserted_count=12
updated_count=0
existing_count=0
r1_immutable=1
r2_immutable=1
c01_immutable=1
c02_immutable=1
c03_immutable=1
c04_immutable=1
c05_immutable=1
c06_immutable=1
c07_immutable=1
c14_immutable=1
oos_executed=0
production_ready=0
```

Status: PASS.

## 5. C15 fix4 drilldown

Command:

```powershell
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c15-fix4-drilldown --summary=storage/app/watchlist/backtest/c15-fix4-drilldown-summary.csv --overwrite
```

Recorded operator result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
diagnostic_param_count=12
ready_count=12
blocked_count=0
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

Recorded summary interpretation:

```text
picks_count=33..179
missing_runtime_evidence_fields=<empty for all rows>
next_focus=STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

Status: PASS as runtime-ready drilldown, not PASS as strategy quality.

## 6. C15 IS calibration run 1

Command:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c15-is-fix4-run-1.json --overwrite
```

Recorded operator result:

```text
status=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
catalog_version=C15
catalog_count=12
catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
production_ready=0
```

Status: PASS as deterministic honest failed-quality evaluation, FAIL as strategy-quality candidate.

## 7. C15 IS calibration run 2

Command:

```powershell
php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c15-is-fix4-run-2.json --overwrite
```

Recorded operator result:

```text
status=C15_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
deterministic=true
```

Status: PASS for rerun determinism.

## 8. Gate inspection summary

Recorded operator inspection:

```text
best_of_failed_forbidden=True
canonical_gates_unchanged=True
failed_count=12
valid_count=0
failure_reason_distribution:
  WS_BT_EVAL_MIN_TRADES_FAIL=11
  WS_BT_EVAL_ROBUST_RETURN_FAIL=8
  WS_BT_EVAL_STABILITY_FAIL=12
persistence_manifest.eval_count=12
persistence_manifest.exact_rerun_idempotent=True
validation.all_rows_evaluated_or_reason_coded=True
validation.all_rows_reached_canonical_gates=True
validation.no_oos_market_data_read=True
validation.no_oos_table_mutation=True
validation.production_ready=False
```

## 9. OOS boundary

```text
OOS_NOT_RUN
OOS_NOT_AUTHORIZED
NOT_ELIGIBLE_FOR_OOS_PROOF_NO_VALID_IS_PARAMETER
```

OOS remains blocked because C15 produced:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
production_ready=0
```

## 10. Next step

```text
NEXT_ACTION=C16_SAMPLE_RECOVERY_AND_STABILITY_DESIGN_FROM_C15_EVIDENCE
```

C16, if implemented, must be a new immutable catalog. C15 must not be edited to look successful.
