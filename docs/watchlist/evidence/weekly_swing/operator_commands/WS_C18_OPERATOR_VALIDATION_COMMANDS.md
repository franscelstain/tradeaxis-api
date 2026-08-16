# WS C18 Operator Validation Commands

## Scope

These commands validate and finalize C18 Fase A diagnostic-first implementation. C18 has no catalog, no seeder, no C18 param grid, no OOS proof, and no production readiness.

C18 is finalized as:

```text
C18_DIAGNOSTIC_FIRST=true
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## C18 identity

```text
SOURCE_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
SOURCE_CATALOG_VERSION=C17
SOURCE_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C18_ARTIFACT_TYPE=C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC
C18_PHASE=C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT
```

## 1. PHPUnit C18 funnel diagnostic tests

Manual command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC18Funnel"
```

Operator evidence, 2026-06-16:

```text
OK (6 tests, 95 assertions)
```

Pass criteria:

- C18 diagnostic service emits `artifact_type=C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC`.
- Artifact scope is `IS_ONLY_DIAGNOSTIC`.
- C18 diagnostic uses C17 as source catalog and does not create a C18 catalog.
- C17 catalog hash remains `d411bfbee6fb14c17d821aa92e7e0fea06925d67`.
- OOS and production markers remain false.
- Static guard confirms C18 command is registered but not scheduled.
- Static guard confirms no C18 seed command/seeder/catalog exists.

Expected exit code: `0`.

## 2. Full Watchlist PHPUnit

Manual command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Operator evidence, 2026-06-16:

```text
OK (372 tests, 9051 assertions)
```

Expected exit code: `0`.

## 3. Command capability check

Manual command:

```powershell
php artisan help watchlist:backtest-c18-funnel-diagnose
```

Operator evidence confirmed these options:

```text
--catalog-code
--from
--to
--output
--param-ids
--deep-funnel
--progress-every
--overwrite
```

## 4. Runtime-first full 12 diagnostic

Manual command:

```powershell
php artisan watchlist:backtest-c18-funnel-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c18-funnel-diagnostic-runtime-first-full-12.json `
  --overwrite
```

Operator evidence:

```text
status=PASS
reason_code=WS_BT_C18_FUNNEL_DIAGNOSTIC_READY
scope=IS_ONLY_DIAGNOSTIC
source_catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
diagnostic_param_count=12
max_evaluated_picks_count=42
max_recommended_count_before_price_evaluation=0
params_with_empty_evaluation_months=12
c18_catalog_implementation_deferred=1
c18_catalog_decision_status=C18_CATALOG_IMPLEMENTATION_DEFERRED
artifact_hash=b03a79896f3cfd985f6462bd1456494eaac8e405
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Pass criteria:

- exit code `0`;
- `status=PASS`;
- `scope=IS_ONLY_DIAGNOSTIC`;
- all 12 C17 rows are diagnosed;
- C18 catalog remains deferred;
- OOS markers remain zero;
- `production_ready=0`.

## 5. Deep funnel best sample row — param 150

Manual command:

```powershell
php artisan watchlist:backtest-c18-funnel-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=150 `
  --deep-funnel `
  --progress-every=25 `
  --output=storage/app/watchlist/backtest/c18-funnel-diagnostic-param-150-deep.json `
  --overwrite
```

Operator evidence:

```text
status=PASS
reason_code=WS_BT_C18_FUNNEL_DIAGNOSTIC_READY
diagnostic_param_count=1
max_evaluated_picks_count=42
max_recommended_count_before_price_evaluation=46
params_with_empty_evaluation_months=1
c18_catalog_implementation_deferred=1
artifact_hash=8b47719f082525a71346aeafd67a5927c1ed1bdd
oos_executed=0
production_ready=0
```

Deep funnel result:

```text
raw=402887
candidate_eligible=40342
scored=40342
top=64
secondary=0
recommended=46
requested_pairs=218
evaluated=42
```

Top contributor evidence:

```text
dv20_guard=1248306
volume_guard=1110514
grouping_cutoff=725090
atr_guard=650785
price_availability_or_boundary_censoring=12
```

## 6. Deep funnel best return row — param 149

Manual command:

```powershell
php artisan watchlist:backtest-c18-funnel-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=149 `
  --deep-funnel `
  --progress-every=25 `
  --output=storage/app/watchlist/backtest/c18-funnel-diagnostic-param-149-deep.json `
  --overwrite
```

Operator evidence:

```text
status=PASS
reason_code=WS_BT_C18_FUNNEL_DIAGNOSTIC_READY
diagnostic_param_count=1
max_evaluated_picks_count=35
max_recommended_count_before_price_evaluation=38
params_with_empty_evaluation_months=1
c18_catalog_implementation_deferred=1
artifact_hash=3dd342f47f7e1397d7ec8defb9e15af26184ca33
oos_executed=0
production_ready=0
```

Deep funnel result:

```text
raw=402887
eligible=39594
scored=39594
top=83
secondary=0
recommended=38
requested_pairs=184
evaluated=35
```

Monthly collapse examples:

```text
2024-07 raw=18813 eligible=1935 scored=1935 top=1 recommended=0
2023-08 raw=16764 eligible=1473 scored=1473 top=2 recommended=0
2025-01 raw=15548 eligible=1376 scored=1376 top=4 recommended=0
2024-11 raw=16322 eligible=1347 scored=1347 top=0 recommended=0
2023-04 raw=10294 eligible=1173 scored=1173 top=0 recommended=0
2024-06 raw=14603 eligible=1161 scored=1161 top=2 recommended=0
```

## 7. Final artifact review conclusion

C18 Fase A evidence is sufficient to close C18 as diagnostic-first result:

```text
RAW_CANDIDATE_NOT_INSUFFICIENT=true
SCORING_POOL_AVAILABLE=true
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
MONTHLY_EMPTY_CAUSED_BY_SELECTION_COLLAPSE=true
PRICE_AVAILABILITY_NOT_PRIMARY=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
```

## Claim rules after finalization

```text
C18_FUNNEL_DIAGNOSTIC_IMPLEMENTED=true
C18_FUNNEL_DIAGNOSTIC_RUNTIME_VALIDATED=true
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_IMPLEMENTED_SOURCE_LEVEL=false for catalog
C18_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

Do not create a C18 catalog. Next work should be C19 strategy model redesign.
