# WS C20 Regime and Trade-Date Quality Gate Diagnostic

C20 starts a new diagnostic concept after C19. It is **not** C19 tuning, not a C20 production catalog, and not an OOS proof.

## Why C20 exists

C19 ended with a useful but blocked result:

```text
C19_SAMPLE_RECOVERY_SOLVED=true
C19_PRICE_EVALUATION_CONFIRMED=true
C19_QUALITY_SIGNAL_FOUND=true
C19_QUALITY_CORE_SAMPLE_TOO_SMALL=true
C19_SAMPLE_QUALIFIED_FRONTIER_QUALITY_FAILED=true
C19_CATALOG_CANDIDATE_FAILED=true
C19_CATALOG_CODE=NOT_CREATED
C19_STOP_TUNING=true
OOS_NOT_RUN=true
production_ready=0
```

The sample-quality frontier showed that the high-quality core can exist, but it is too small. When sample is forced back toward the old 120+ evaluated-pick zone, quality turns negative again. That means the next question is not only "which ticker should be selected", but also "whether the trade_date is suitable for any buy recommendation".

## What C20 tests

C20 tests whether an IS-only trade-date/regime gate can improve return quality by blocking weak market/regime dates before canonical price evaluation.

Allowed behavior:

```text
no-pick days
no-pick weeks
no-pick months when regime is weak
```

Forbidden behavior:

```text
ticker blacklist
month blacklist
sector whitelist
future price/exit/return as gate input
best-of-failed binding
catalog promotion
OOS
PLAN/RECOMMENDATION/CONFIRM mutation
```

## Implemented source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC20RegimeTradeDateDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC20RegimeTradeDateDiagnoseCommand.php

Command signature:
watchlist:backtest-c20-regime-trade-date-diagnose
```

The command is registered in `app/Console/Kernel.php` and supports:

```text
--catalog-code=
--from=
--to=
--param-ids=
--profiles=
--profile-codes=
--progress
--output=
--overwrite
--max-params=
--max-profiles=
```

## Diagnostic profiles

```text
C20_G00_BASELINE_NO_DATE_GATE
C20_G01_MARKET_MOMENTUM_SAFE
C20_G02_BREADTH_HEALTHY
C20_G03_VOLATILITY_RISK_OFF_FILTER
C20_G04_SECTOR_CONFIRMATION
C20_G05_COMBINED_REGIME_QUALITY
C20_G06_NO_PICK_DAY_ALLOWED_QUALITY_FIRST
```

`C20_G00` is the no-date-gate baseline. Other profiles gate trade dates using same-date EOD information only.

## Data availability model

C20 records `data_availability` in the output artifact:

```json
{
  "ihsg_proxy_available": true,
  "sector_proxy_available": true,
  "breadth_proxy_available": true,
  "candidate_distribution_available": true,
  "candidate_metric_fallback_supported": true,
  "notes": []
}
```

When IHSG benchmark context is not readable, C20 does not invent it. Market profiles fall back to same-date candidate metrics and mark that fallback in `trade_date_gate_summary`.

When sector metrics are not found, C20 does not whitelist sectors. Sector confirmation falls back to aggregate relative-strength metrics and records the fallback.

## Gate input contract

C20 gate input is limited to information available by `trade_date` EOD:

```text
IHSG benchmark context for trade_date, when readable
same-date candidate count
same-date avg/median quality score
same-date avg/median ROC20
same-date avg/median ATR14 percent
same-date avg/median volume ratio
same-date aggregate sector/relative-strength metrics, when available
```

C20 freezes selected trade candidates and date-gate decisions before price read. Price is used only afterward for canonical evaluation.

## Price evaluation model

Canonical model remains unchanged:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

The service uses `WatchlistBacktestRuntimeArtifactService` and `WatchlistBacktestMetricsService` for price evaluation, like C19 Tahap 4/5 diagnostics.

## Artifact schema

The output artifact has:

```text
artifact_type=C20_REGIME_TRADE_DATE_DIAGNOSTIC
status=PASS|BLOCKED
reason_code=WS_BT_C20_REGIME_TRADE_DATE_DIAGNOSTIC_READY
scope=IS_ONLY_REGIME_TRADE_DATE_DIAGNOSTIC
source_catalog
source_evidence
data_availability
regime_profiles
profile_summaries
sample_quality_table
trade_date_gate_summary
monthly_evaluated_distribution
decision
safety_boundaries
```

Each profile summary includes trade-date gate counts, no-pick day count, proposed/evaluated count, return metrics, period fail count, exit reason counts, sample gate, quality gate, and gate notes.

## Decision gates

C20 compares profiles against the C19 sample-qualified baseline:

```text
evaluated=124
avg=-0.18%
median=-0.05%
p25=-1.82%
win=43.55%
period_fail=13
```

Quality improvement gate:

```text
avg_ret_net_top > -0.18%
median_ret_net_top >= -0.05%
win_rate_top > 43.55%
period_fail_count < 13
```

Promising continue gate:

```text
evaluated_picks_count >= 100
avg_ret_net_top > -0.18%
median_ret_net_top >= -0.05%
win_rate_top >= 45%
period_fail_count < 13
```

Quality target gate:

```text
evaluated_picks_count >= 120
avg_ret_net_top >= 0
median_ret_net_top >= 0
win_rate_top >= 45%
period_fail_count <= 10
```

Small-sample profiles may be reported as `best_any_sample_profile`, but they are not allowed to become the main continuation decision. The artifact separately records:

```text
best_any_sample_profile
best_promising_sample_profile
best_sample_qualified_profile
best_quality_target_profile
```

## Final operator validation result

C20 has been runtime-validated by the operator. It is closed as a diagnostic success and strategy-candidate failure.

Validation evidence:

```text
PHPUNIT_C20=PASS
OK (6 tests, 84 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (391 tests, 9327 assertions)

C20_FOCUSED_4_PROFILE=PASS
artifact_hash=dac6ff71cee04be7b1c4ddcfd06a899808a89167
profiles_with_quality_improvement=1
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_FOCUSED_7_PROFILE=PASS
artifact_hash=29a9743052de2b3164653a85a93e57e22a607dbe
profiles_with_quality_improvement=2
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_ALL_PARAM_7_PROFILE=PASS
artifact_hash=8f8eec9913c107f22ec1f395eed9386da41756c0
profile_count=7
profile_scope=EXPLICIT
best_any_sample_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_promising_sample_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_sample_qualified_profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_quality_target_profile_code=
profiles_with_quality_improvement=4
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0
c20_catalog_implementation_deferred=1
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Final all-param decision:

```text
decision_status=C20_DATE_GATE_NOT_ENOUGH
catalog_allowed=false
oos_allowed=false
next_step=Stop C20 as diagnostic failed unless a new non-lookahead regime data source is added.
best_any_sample_profile=C20_G03_VOLATILITY_RISK_OFF_FILTER / param_id=148 / row_code=03_SCORE_70_85_LOW_ATR_NEG_ROC20
best_promising_sample_profile=C20_G03_VOLATILITY_RISK_OFF_FILTER / param_id=148 / row_code=03_SCORE_70_85_LOW_ATR_NEG_ROC20
best_sample_qualified_profile=C20_G03_VOLATILITY_RISK_OFF_FILTER / param_id=148 / row_code=03_SCORE_70_85_LOW_ATR_NEG_ROC20
best_quality_target_profile=null
small_sample_cannot_be_main_decision=true
```

Best profile metrics from the final all-param 7-profile artifact:

```text
profile_code=C20_G03_VOLATILITY_RISK_OFF_FILTER
param_id=148
row_code=03_SCORE_70_85_LOW_ATR_NEG_ROC20
evaluated_picks_count=124
avg_ret_net_top=-0.0018095754889618039
median_ret_net_top=-0.0004998750312421895
win_rate_top=0.43548387096774194
period_fail_count=13
```

Interpretation:

- C20 can block trade dates and can produce limited quality improvement in some profiles.
- The improvement is not enough to reach `PROMISING_CONTINUE_TO_C20_TUNING`.
- No profile reached `quality_target_reached`.
- The best profile still has negative average return, slightly negative median return, sub-45% win rate, and `period_fail_count=13`.
- Therefore the C20 regime/date gate hypothesis is not enough with the currently available non-lookahead features.

## Final C20 status

```text
C20_SOURCE_IMPLEMENTED=true
C20_RUNTIME_VALIDATION_REQUIRED=false
C20_DIAGNOSTIC_RUNTIME_PASS=true
C20_7_PROFILE_ALL_PARAM_PASS=true
C20_DATE_GATE_NOT_ENOUGH=true
C20_REGIME_DATE_GATE_STRATEGY_FAILED=true
C20_CATALOG_CANDIDATE_FAILED=true
C20_CATALOG_CODE=NOT_CREATED
C20_STOP_TUNING=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C19_STOP_TUNING_PRESERVED=true
C01_TO_C19_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_DESIGN
```

C20 must not be tuned further unless a new non-lookahead regime data source is introduced. Current C20 outputs do not authorize catalog creation, OOS, production readiness, or C19 reopening.
