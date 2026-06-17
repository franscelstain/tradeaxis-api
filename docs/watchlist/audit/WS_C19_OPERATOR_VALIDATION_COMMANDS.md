# WS C19 Operator Validation Commands

## Purpose

These commands validate C19 Fase A/B v3. C19 remains diagnostic/prototype only. It must not run OOS, must not seed a catalog, must not promote, and must not set production readiness.

## 1. PHPUnit C19 Filter

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC19"
```

Expected:

```text
OK (... tests, ... assertions)
no PHP Warning about incompatible C19 fake service method signatures
```

Pass criteria:

- PHPUnit exits with code `0`.
- C19 diagnostic/service/static guard tests pass.
- C19 v3 mapping tests pass.
- No PHP signature compatibility warning is emitted from C19 test fake classes.
- No C19 source-level catalog is required.

Fail criteria:

- any failure or error;
- any PHP signature compatibility warning from C19 fake service classes;
- missing static guard against OOS;
- any C19 catalog/seeder/seed command appears;
- C18/C17/C16/C15/C14/C01-C07/R1/R2 mutation detected.

## 2. Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected:

```text
OK (... tests, ... assertions)
no PHP Warning about incompatible C19 fake service method signatures
```

Pass criteria:

- PHPUnit exits with code `0`.
- Existing Watchlist behavior remains green.
- No PHP signature compatibility warning is emitted from C19 fake service classes.
- No C19 change mutates earlier C01-C18 behavior.

Fail criteria:

- any prior watchlist test fails;
- any PHP signature compatibility warning from C19 fake service classes;
- C18 tests regress;
- C17 hash/count changes;
- OOS or production readiness marker changes.

## 3. C19 v3 Full Source Selection Diagnose

```powershell
php artisan watchlist:backtest-c19-selection-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c19-selection-model-redesign-analysis-v3.json `
  --overwrite
```

Expected console markers:

```text
status=PASS
reason_code=WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY
scope=IS_ONLY_DIAGNOSTIC
diagnostic_param_count=12
params_with_proposed_secondary_recovery=<integer>
params_with_non_unknown_drop_reasons=<integer>
c19_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Expected diagnostic improvement over v2:

```text
max_current_top_count should be mapped from actual PlanGrouping group arrays
max_current_recommended_count should be mapped from Recommendation summary.recommended_count
max_proposed_secondary_count should be > 0 if recovery buffer finds valid scored candidates
max_proposed_recommended_count should be > 0 if selector simulation works
dominant_current_drop_reasons should not collapse to UNKNOWN-only
```

Fail criteria:

- command exits non-zero;
- OOS marker is not zero;
- `production_ready` is not `0`;
- artifact is missing required source-path fields;
- `debug_output_keys` is missing;
- proposed selector produces no recovery path at all;
- command creates/seeds/promotes a C19 catalog.

## 4. Focused Param 149/150 Diagnose

```powershell
php artisan watchlist:backtest-c19-selection-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=149,150 `
  --output=storage/app/watchlist/backtest/c19-selection-model-redesign-param-149-150-v3.json `
  --overwrite
```

Expected:

```text
status=PASS
reason_code=WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY
scope=IS_ONLY_DIAGNOSTIC
diagnostic_param_count=2
params_with_proposed_secondary_recovery=<integer>
params_with_non_unknown_drop_reasons=2 expected
c19_catalog_implementation_deferred=1
oos_executed=0
production_ready=0
```

Use this to compare directly against C18 deep funnel rows 149 and 150.

## 5. Artifact Checks

```powershell
$artifact = Get-Content storage/app/watchlist/backtest/c19-selection-model-redesign-analysis-v3.json | ConvertFrom-Json
$artifact.cross_param_summary
$artifact.diagnostics | Select-Object param_id,row_code,current_path,proposed_path
$artifact.diagnostics | Select-Object -First 1 | ForEach-Object { $_.debug_output_keys }
$artifact.diagnostics | Select-Object -First 1 | ForEach-Object { $_.dominant_current_drop_reasons }
$artifact.diagnostics | ForEach-Object { $_.monthly_distribution } | Format-Table -AutoSize
```

Must show:

```text
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
C19_V3_DIAGNOSTIC_MAPPING_FIXED=true
C19_V3_SELECTOR_SIMULATION_FROM_SCORED_POOL=true
SECONDARY_ZERO_CAUSE=design_cutoff_guard_behavior_not_runtime_bug
```

## 6. Catalog Gate

Do not implement C19 catalog from this diagnostic alone.

Catalog creation requires a later session with:

```text
C19_RUNTIME_MODE_IMPLEMENTED=true
C19_PRICE_EVALUATED_IS_RUN_1_PASS=true
C19_PRICE_EVALUATED_IS_RUN_2_PASS=true
MIN_TRADES_GATE_MET=true
MONTHLY_STABILITY_GATE_MET=true
DOWNSIDE_GATE_NOT_LOWERED=true
OOS_NOT_RUN=true
```


## C19 v3.1 Rerun After Unit-Fixture Patch

The first operator rerun of v3 produced one failure in `WatchlistBacktestC19SelectionModelRedesignAnalysisServiceTest::test_it_builds_c19_selection_redesign_artifact_without_catalog_or_oos` because the fake scored fixture did not contain any candidate that could become SECONDARY after fatal component-balance checks. Re-run the same validation commands after applying v3.1.

Expected C19 filter result:

```text
OK (... tests, ... assertions)
```

Expected full Watchlist result:

```text
OK (... tests, ... assertions)
```

Expected C19 v3.1 diagnostic markers:

```text
status=PASS
reason_code=WS_BT_C19_SELECTION_MODEL_ANALYSIS_READY
scope=IS_ONLY_DIAGNOSTIC
max_proposed_secondary_count > 0
max_proposed_recommended_count > 0
params_with_proposed_secondary_recovery > 0
oos_executed=0
production_ready=0
c19_catalog_implementation_deferred=1
```

## 7. C19 Tahap 4 Price Diagnostic — Full Source Catalog

```powershell
php artisan watchlist:backtest-c19-proposed-selection-price-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c19-proposed-selection-price-diagnostic-run-1.json `
  --overwrite
```

Expected console markers:

```text
status=PASS
reason_code=WS_BT_C19_PRICE_DIAGNOSTIC_READY
scope=IS_ONLY_PRICE_DIAGNOSTIC
diagnostic_param_count=12
max_proposed_recommended_count=<integer>
max_requested_pairs_count=<integer>
max_evaluated_picks_count=<integer>
max_price_missing_count=<integer>
params_with_evaluated_sample_target_reached=<integer>
c19_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Pass criteria:

- command exits with code `0`;
- artifact type is `C19_PROPOSED_SELECTION_PRICE_DIAGNOSTIC`;
- scope is `IS_ONLY_PRICE_DIAGNOSTIC`;
- proposed recommendations are converted into frozen trade candidates before price read;
- price metrics are produced through the canonical runtime artifact/metrics services;
- `oos_executed=0`;
- `production_ready=0`;
- C19 catalog remains `NOT_CREATED`.

Fail criteria:

- command exits non-zero;
- output tries to run OOS;
- output sets production readiness;
- command seeds/promotes/creates C19 catalog;
- canonical model changes from `ENTRY=NEXT_OPEN`, `EXIT=STOP_TP_OR_TIME`, `HOLD=5`, `FEE=IDR_FIXED`, `SLIP=0`, `GAP=OPEN`, `PX=IDX_BANDS`.

## 8. C19 Tahap 4 Price Diagnostic — Focused Param 149/150

```powershell
php artisan watchlist:backtest-c19-proposed-selection-price-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=149,150 `
  --output=storage/app/watchlist/backtest/c19-proposed-selection-price-diagnostic-param-149-150.json `
  --overwrite
```

Use this to validate the C18 deep-funnel reference rows after C19 proposed-selection recovery.

Artifact inspection:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c19-proposed-selection-price-diagnostic-run-1.json | ConvertFrom-Json

$run.diagnostics |
    Select-Object `
        param_id,
        row_code,
        @{n='proposed';e={$_.selection_counts.proposed_recommended_count}},
        @{n='requested_pairs';e={$_.price_evaluation_counts.requested_pairs_count}},
        @{n='evaluated';e={$_.price_evaluation_counts.evaluated_picks_count}},
        @{n='missing';e={$_.price_evaluation_counts.price_missing_count}},
        @{n='avg';e={$_.return_metrics.avg_ret_net_top}},
        @{n='median';e={$_.return_metrics.median_ret_net_top}},
        @{n='p25';e={$_.return_metrics.p25_ret_net_top}},
        @{n='win';e={$_.return_metrics.win_rate_top}},
        @{n='month_win_min';e={$_.return_metrics.month_win_rate_min}},
        @{n='month_avg_min';e={$_.return_metrics.month_avg_ret_net_min}} |
    Sort-Object evaluated -Descending |
    Format-Table -AutoSize
```

Catalog remains forbidden until price-evaluated IS evidence is reviewed and repeatable.

## 9. C19 Tahap 5 — Quality Recovery Tuning Diagnostic

Run C19 tests:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC19"
```

Run full Watchlist tests:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Run Tahap 5 fast smoke diagnostic first. Do not run all profiles until this passes:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148 `
  --profile-codes=Q00_TAHAP_4_BASELINE,Q05_DOWNSIDE_AWARE_SCORE_120 `
  --progress `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-smoke-148.json `
  --overwrite
```

Run focused Tahap 5 diagnostic after smoke PASS:

```powershell
php artisan watchlist:backtest-c19-quality-recovery-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,149,150,152 `
  --profile-codes=Q00_TAHAP_4_BASELINE,Q05_DOWNSIDE_AWARE_SCORE_120,Q06_MONTHLY_QUALITY_CAP_120 `
  --progress `
  --output=storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-focused.json `
  --overwrite
```

Expected console markers:

```text
status=PASS
reason_code=WS_BT_C19_QUALITY_RECOVERY_DIAGNOSTIC_READY
scope=IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC
profile_count=<integer>
profile_scope=<FAST_DEFAULT|EXPLICIT|ALL_PROFILES_EXPLICIT>
max_params=<integer-or-empty>
best_profile_code=<profile>
best_avg_ret_net_top=<float>
best_evaluated_picks_count=<integer>
profiles_with_sample_target_reached=<integer>
profiles_with_quality_improvement=<integer>
profiles_with_quality_target_reached=<integer>
c19_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Artifact ranking query:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c19-quality-recovery-tuning-diagnostic-run-1.json | ConvertFrom-Json

$run.profile_summaries |
    Select-Object `
        profile_code,
        @{n='best_param';e={$_.best_param.param_id}},
        @{n='row';e={$_.best_param.row_code}},
        @{n='evaluated';e={$_.best_param.evaluated_picks_count}},
        @{n='avg';e={[math]::Round($_.best_param.avg_ret_net_top * 100, 2)}},
        @{n='median';e={[math]::Round($_.best_param.median_ret_net_top * 100, 2)}},
        @{n='p25';e={[math]::Round($_.best_param.p25_ret_net_top * 100, 2)}},
        @{n='win';e={[math]::Round($_.best_param.win_rate_top * 100, 2)}},
        @{n='period_fail';e={$_.best_param.period_fail_count}},
        @{n='sample_ok';e={$_.quality_gate.sample_target_reached}},
        @{n='quality_ok';e={$_.quality_gate.quality_target_reached}} |
    Format-Table -AutoSize
```

Pass criteria:

- command exits with code `0`;
- artifact type is `C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC`;
- scope is `IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC`;
- quality profiles use selector-time inputs only;
- no OOS service/repository/table path is used;
- `production_ready=0`;
- C19 catalog remains `NOT_CREATED`.

Decision rule:

- If no profile reaches quality target, record best improvements as next redesign evidence only.
- If a profile reaches quality target, run a separate repeat IS proof before any catalog discussion.
