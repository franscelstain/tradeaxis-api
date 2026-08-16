# WS C18 Funnel and Monthly Coverage Diagnostic Result

## Scope

C18 is finalized as **diagnostic-first analysis**, not as a new strategy catalog.

C18 uses the C17 final rejected catalog as the diagnostic source:

```text
SOURCE_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
SOURCE_CATALOG_VERSION=C17
SOURCE_CATALOG_COUNT=12
SOURCE_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
```

C17 remains immutable. C18 does **not** create `WS_BT_GRID_DOWNSIDE_STABILITY_C18_2026_06`, does **not** seed/promote/bind a parameter, does **not** run OOS, and does **not** set `production_ready=1`.

## Initial C17 artifact interpretation

The C17 final artifact is read as `all_evaluations`, not `is_calibration.evaluations`.

C17 final strategy-quality result:

```text
C17_GRID_FAILED_IS_QUALITY=true
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
max_picks_count=42
canonical_min_trades=120
OOS_NOT_RUN=true
production_ready=0
```

Failure distribution:

| Reason code | Count |
| --- | ---: |
| `WS_BT_EVAL_MIN_TRADES_FAIL` | 12 |
| `WS_BT_EVAL_STABILITY_FAIL` | 12 |
| `WS_BT_EVAL_ROBUST_RETURN_FAIL` | 5 |
| `WS_BT_EVAL_DOWNSIDE_FAIL` | 0 |

Interpretation:

- C17 did not fail because of runtime wiring, missing payload, OOS leakage, or downside.
- C17 failed strategy quality.
- The main blocker is sample collapse: the best C17 row produced only `42` evaluated picks, while the canonical gate requires `120`.
- Monthly stability failed for every row; `month_win_rate_min=0` means the diagnostic must distinguish empty months from negative months.
- C18 catalog iteration is not justified until funnel-level evidence shows where sample is lost.

## Fase A implementation

Added C18 diagnostic command:

```powershell
php artisan watchlist:backtest-c18-funnel-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c18-funnel-diagnostic.json `
  --overwrite
```

Added source components:

```text
App\Application\Watchlist\Services\WatchlistBacktestC18FunnelDiagnosticService
App\Console\Commands\Watchlist\RunBacktestC18FunnelDiagnoseCommand
```

The command supports two execution levels:

```text
runtime-first default = faster runtime/monthly diagnostic, no per-date CandidateUniverse loop
--deep-funnel         = expensive per-date CandidateUniverse/Scoring/Grouping diagnostic
--progress-every=N   = progress output for deep funnel
```

## Diagnostic artifact contract

Artifact type:

```text
artifact_type=C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC
scope=IS_ONLY_DIAGNOSTIC
phase=C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT
```

The diagnostic artifact records, per source C17 param/row:

```text
raw_trading_dates_count
strategy_trade_dates_count
raw_ticker_date_candidate_count
after_candidate_universe_filter_count
after_score_runtime_guard_count
after_grouping_top_picks_count
after_grouping_secondary_count
after_grouping_active_count
recommendation_source_plan_item_count
recommended_count_before_price_evaluation
requested_ticker_date_pair_count
evaluated_picks_count
boundary_censored_count
period_count
period_fail_count
monthly_pick_distribution
monthly_win_rate_distribution
monthly_average_return_distribution
monthly_empty_or_failed_periods
filter_drop_reason_distribution
drop_reason_category_distribution
top_drop_contributors
runtime_evaluation_summary
root_cause_signals
```

## Operator validation evidence

Operator validation completed on 2026-06-16.

### PHPUnit

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC18Funnel"
OK (6 tests, 95 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (372 tests, 9051 assertions)
```

### Command capability

`php artisan help watchlist:backtest-c18-funnel-diagnose` confirmed these C18 diagnostic options:

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

### Runtime-first full 12 diagnostic

Command:

```powershell
php artisan watchlist:backtest-c18-funnel-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c18-funnel-diagnostic-runtime-first-full-12.json `
  --overwrite
```

Operator output summary:

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

Full 12 per-param runtime-first result:

| Param | Row code | Evaluated picks |
| ---: | --- | ---: |
| 150 | `05_DV20_25_75_SCORE_68_82` | 42 |
| 149 | `04_DV20_2B_6B_CONTROLLED_PULLBACK` | 35 |
| 145 | `00_C16_140_SCORE_65_80_MID_DV20_ONE_R` | 35 |
| 148 | `03_SCORE_70_85_LOW_ATR_NEG_ROC20` | 28 |
| 152 | `07_VOL_150_250_ONE_R_LOW_ATR` | 26 |
| 151 | `06_VOL_150_250_LOW_ATR_NEG_ROC20` | 25 |
| 156 | `11_C16_143_DERIVED_ONE_R_SCORE_70_85` | 19 |
| 153 | `08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING` | 19 |
| 146 | `01_NEG_ROC20_SCORE_65_80_DV20_2B_6B` | 17 |
| 147 | `02_NEG_ROC20_ONE_R_SCORE_68_82` | 17 |
| 154 | `09_MID_DV20_LOWER_VOLUME_GUARDED` | 16 |
| 155 | `10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82` | 15 |

Monthly failure aggregation showed severe distribution collapse. These months failed as empty months across all 12 rows:

```text
2023-01
2023-02
2023-04
2023-08
2024-07
```

Other broad failures included:

```text
2025-01 empty month in 11 rows
2025-03 stability fail in 11 rows
2023-03 empty month in 10 rows
2024-06 empty month in 10 rows
2024-11 empty month in 10 rows
2025-02 stability fail in 10 rows
```

## Deep funnel evidence

Deep funnel was intentionally run only for the best sample row and best return row. Running all 12 deep rows is not required for C18 final decision.

### Param 150 — best sample row

Command produced:

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

Deep funnel count:

| Param | Row code | Raw | Eligible | Scored | TOP | Secondary | Recommended | Requested pairs | Evaluated |
| ---: | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| 150 | `05_DV20_25_75_SCORE_68_82` | 402887 | 40342 | 40342 | 64 | 0 | 46 | 218 | 42 |

Top contributors for param 150:

| Contributor | Count |
| --- | ---: |
| `dv20_guard` | 1248306 |
| `volume_guard` | 1110514 |
| `other` | 731482 |
| `grouping_cutoff` | 725090 |
| `atr_guard` | 650785 |
| `score_window` | 49403 |
| `close_to_hh20_or_breakout_extension_guard` | 40342 |
| `roc5_guard` | 30923 |
| `roc20_guard` | 24390 |
| `price_availability_or_boundary_censoring` | 12 |

Reason distribution detail for param 150 included:

| Reason code | Count |
| --- | ---: |
| `WS_VOLR_FAIL` | 1089340 |
| `WS_LIQ_STRONG` | 728235 |
| `WS_PLAN_AVOID_EXCLUDED` | 725090 |
| `WS_ATR_HIGH` | 564372 |
| `WS_LIQ_FAIL` | 345848 |
| `WS_RISK_IDEAL` | 339348 |
| `WS_RISK_HIGH` | 221077 |
| `WS_LIQ_BORDER` | 140356 |
| `WS_ATR_LOW` | 86371 |
| `WATCHLIST_C17_ENTRY_QUALITY_FLOOR_FAIL` | 80548 |
| `WATCHLIST_C17_DV20_SAMPLE_RECOVERY_RANGE_FAIL` | 33867 |
| `WATCHLIST_C17_ROC5_CONTROLLED_PULLBACK_RANGE_FAIL` | 30923 |
| `WATCHLIST_C17_ROC20_SEGMENT_RANGE_FAIL` | 24390 |
| `WATCHLIST_C17_VOLUME_RECOVERY_RANGE_FAIL` | 21174 |

Important note: some labels such as `WS_LIQ_STRONG`, `WS_RISK_IDEAL`, `WS_PLAN_TOP_PICK`, and `WS_REC_SELECTED` can be status/reason labels emitted by the pipeline, not necessarily negative failures. The negative blockers that require design attention are volume/DV20/ATR/entry-quality/ROC/grouping collapse markers.

### Param 149 — best return/controlled pullback row

Command produced:

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

Deep funnel count:

| Param | Row code | Raw | Eligible | Scored | TOP | Secondary | Recommended | Requested pairs | Evaluated |
| ---: | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| 149 | `04_DV20_2B_6B_CONTROLLED_PULLBACK` | 402887 | 39594 | 39594 | 83 | 0 | 38 | 184 | 35 |

Monthly collapse examples for param 149:

| Month | Raw | Eligible | Scored | TOP | Recommended |
| --- | ---: | ---: | ---: | ---: | ---: |
| 2024-07 | 18813 | 1935 | 1935 | 1 | 0 |
| 2023-08 | 16764 | 1473 | 1473 | 2 | 0 |
| 2025-01 | 15548 | 1376 | 1376 | 4 | 0 |
| 2024-11 | 16322 | 1347 | 1347 | 0 | 0 |
| 2023-04 | 10294 | 1173 | 1173 | 0 | 0 |
| 2024-06 | 14603 | 1161 | 1161 | 2 | 0 |

## Root-cause conclusion

C18 diagnostic evidence confirms:

```text
RAW_CANDIDATE_NOT_INSUFFICIENT=true
SCORING_POOL_AVAILABLE=true
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
MONTHLY_EMPTY_CAUSED_BY_SELECTION_COLLAPSE=true
PRICE_AVAILABILITY_NOT_PRIMARY=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
```

Rationale:

- Raw candidate supply is large (`402887` for deep rows).
- Eligible/scored candidate supply remains large (`39594` to `40342`).
- The collapse occurs after scoring: scored pool falls to only `64` to `83` TOP candidates, then `38` to `46` recommended candidates, then `35` to `42` evaluated picks.
- Several months have more than 1000 scored candidates but zero recommended candidates.
- Price availability/boundary censoring is not the primary blocker (`price_availability_or_boundary_censoring=12` in param 150 deep evidence).
- SECONDARY remained `0` in both deep rows, indicating no sample buffer path is active for this strategy shape.

## Final C18 decision

```text
C18_DIAGNOSTIC_FIRST=true
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_RUNTIME_FIRST_FULL_12_PASS=true
C18_DEEP_FUNNEL_PARAM_150_PASS=true
C18_DEEP_FUNNEL_PARAM_149_PASS=true
C18_ROOT_CAUSE_CONFIRMED=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
C17_UNCHANGED=true
C01_TO_C17_IMMUTABLE=true
WATCHLIST_SCOPE_ONLY=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
OOS_NOT_RUN=true
production_ready=0
```

C18 Fase B catalog implementation is **not approved**. The next step is broader C19 strategy model redesign, not another small parameter catalog iteration.

## Required next work

C19 must focus on strategy model redesign, not catalog churn:

```text
Audit PlanGrouping collapse: ~40k scored candidates to only 64-83 TOP.
Audit Recommendation collapse: 64-83 TOP to only 38-46 recommended.
Investigate why SECONDARY remains 0 and whether a risk-controlled buffer path is missing.
Review volume/DV20/ATR/entry-quality/ROC guards without lowering canonical gates.
Add monthly coverage-aware selection without month blacklist.
Preserve C17 downside behavior.
Do not run OOS until a valid IS candidate exists.
Do not set production_ready=1.
```
