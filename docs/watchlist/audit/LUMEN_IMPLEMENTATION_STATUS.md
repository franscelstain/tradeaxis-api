# Watchlist Lumen Implementation Status

## Document Purpose

Dokumen ini mencatat status implementasi watchlist pada codebase Lumen. Dokumen ini adalah status tracker, bukan owner behavior bisnis.

Behavioral owner tetap:

1. `docs/watchlist/system/policy.md`
2. `docs/watchlist/system/README.md`
3. `docs/watchlist/system/policies/weekly_swing/**`
4. `docs/watchlist/system/implementation/weekly_swing/**` untuk translation guidance
5. `docs/watchlist/audit/**` untuk audit guardrail dan status tracking

## ACTIVE SESSION

Session:
`WATCHLIST - C22 EXIT CAPTURE SHADOW DIAGNOSTIC SOURCE IMPLEMENTATION`

Current status:

`C22_SOURCE_IMPLEMENTED / C22_RUNTIME_VALIDATION_REQUIRED / C22_CATALOG_CODE_NOT_CREATED / C21_EXECUTION_SIGNAL_FOUND_PRESERVED / C20_DATE_GATE_NOT_ENOUGH_PRESERVED / C19_CATALOG_CANDIDATE_FAILED_PRESERVED / C01_TO_C21_IMMUTABLE / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C22 source-level implementation result:

- `WatchlistBacktestC22ExitCaptureShadowDiagnosticService` exists as an IS-only exit-capture shadow diagnostic;
- `RunBacktestC22ExitCaptureShadowDiagnoseCommand` exists as `watchlist:backtest-c22-exit-capture-shadow-diagnose`;
- the command is registered in `app/Console/Kernel.php`;
- C22 service/static guard tests exist and require operator PHPUnit validation;
- C22 reuses fixed recommendation candidates from the C19 selection diagnostic path before reading D+1 through D+5 OHLC;
- future path price is used only for measurement after ticker and trade_date are fixed;
- C22 compares canonical baseline against shadow exit profiles for fixed exits, first profitable close, profit lock, breakeven stop, trailing protection, closer targets, and stop-distance variants;
- C22 writes canonical summary, per-shadow-profile summary, family summaries, data availability, decision flags, and safety boundaries;
- C22 did not create a catalog, seeder, seed command, repository approval, or factory catalog mapping;
- C22 did not mutate C01-C21;
- C22 did not run OOS and did not set `production_ready=1`.

C22 source files:

```text
app/Application/Watchlist/Services/WatchlistBacktestC22ExitCaptureShadowDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC22ExitCaptureShadowDiagnoseCommand.php
tests/Unit/Watchlist/WatchlistBacktestC22ExitCaptureShadowDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC22StaticGuardTest.php
docs/watchlist/audit/WS_C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC.md
docs/watchlist/audit/WS_C22_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c22-exit-capture-shadow-diagnostic-source-summary.json
docs/watchlist/system/policies/weekly_swing/_refs/WS_EXIT_CAPTURE_SHADOW_C22_DESIGN_NOTE.md
```

C22 implemented shadow profiles:

```text
C22_S00_CANONICAL_BASELINE
C22_S01_EXIT_D1_CLOSE
C22_S02_EXIT_D2_CLOSE
C22_S03_EXIT_D3_CLOSE
C22_S04_EXIT_D4_CLOSE
C22_S05_EXIT_D5_CLOSE_ONLY
C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_S07_PROFIT_LOCK_AFTER_MFE_0_75PCT
C22_S08_PROFIT_LOCK_AFTER_MFE_1_00PCT
C22_S09_PROFIT_LOCK_AFTER_MFE_1_50PCT
C22_S10_BREAKEVEN_AFTER_MFE_1_00PCT
C22_S11_TRAILING_FROM_MFE_1_50PCT_GIVEBACK_0_75PCT
C22_S12_TARGET_CLOSE_1_00PCT
C22_S13_TARGET_CLOSE_1_50PCT
C22_S14_TARGET_CLOSE_2_00PCT
C22_S15_STOP_LOSS_1_50PCT_SHADOW
C22_S16_STOP_LOSS_2_00PCT_SHADOW
C22_S17_STOP_LOSS_2_50PCT_SHADOW
```

C22 source-level boundary status:

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C22_CATALOG_CODE=NOT_CREATED
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C21_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
future_path_price_used_for_selection=false
shadow_exit_used_for_selection=false
shadow_ret_net_used_for_selection=false
mfe_mae_used_for_selection=false
```

C22 validation requirement:

```text
PHPUNIT_C22=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C22_FOCUSED_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_ALL_PARAM_RUNTIME_PASS=OPERATOR_VALIDATION_REQUIRED
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
```

C22 conclusion at source level:

```text
C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
C22_RUNTIME_VALIDATION_REQUIRED=true
C22_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN
C22_EXIT_CAPTURE_SIGNAL_FOUND=NOT_RUN
C22_CATALOG_IMPLEMENTATION_DEFERRED=true
C22_CATALOG_CODE=NOT_CREATED
C21_EXECUTION_SIGNAL_FOUND_PRESERVED=true
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C01_TO_C21_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=RUN_C22_OPERATOR_VALIDATION
```

C22 is implemented as a diagnostic source path only. It does not unlock catalog creation, OOS, promotion, production readiness, C21 tuning, C20 reopening, C19 reopening, or canonical execution-model mutation.

## PRIOR SESSION - C21 FINAL ENTRY/EXIT BEHAVIOR DIAGNOSTIC RESULT

Current status:

`C21_SOURCE_IMPLEMENTED / C21_PHPUNIT_PASS / FULL_WATCHLIST_PHPUNIT_PASS / C21_RUNTIME_VALIDATED / C21_EXECUTION_SIGNAL_FOUND / C21_ENTRY_PROBLEM_REJECTED / C21_EXIT_PROBLEM_SUSPECTED / C21_STOP_PROBLEM_SUSPECTED / C21_HOLD_PERIOD_PROBLEM_SUSPECTED / C21_REGIME_EXPLANATION_NOT_SUPPORTED / C21_CATALOG_CODE_NOT_CREATED / C20_DATE_GATE_NOT_ENOUGH_PRESERVED / C19_CATALOG_CANDIDATE_FAILED_PRESERVED / C01_TO_C20_IMMUTABLE / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C21 final source and runtime result:

- `WatchlistBacktestC21EntryExitBehaviorDiagnosticService` exists as an IS-only entry/exit behavior diagnostic;
- `RunBacktestC21EntryExitBehaviorDiagnoseCommand` exists as `watchlist:backtest-c21-entry-exit-behavior-diagnose`;
- the command is registered in `app/Console/Kernel.php`;
- C21 service/static guard tests exist and operator validation passed;
- C21 reads fixed recommendation candidates from C19 selection diagnostic output before reading future path prices;
- future OHLC path is used only for measurement after ticker and trade_date are fixed;
- C21 computes signal close, next-open entry, D+1 through D+5 returns, MFE/MAE, stop/target timing, exit reason, gave-back-profit, never-profitable, entry-gap, and C20_G03 segmentation context;
- C20 G03 is segmentation/explanation only and is not a filter, catalog gate, or production rule;
- C21 did not create a catalog, seeder, seed command, repository approval, or factory catalog mapping;
- C21 did not mutate C01-C20;
- C21 did not run OOS and did not set `production_ready=1`.

C21 source files:

```text
app/Application/Watchlist/Services/WatchlistBacktestC21EntryExitBehaviorDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC21EntryExitBehaviorDiagnoseCommand.php
tests/Unit/Watchlist/WatchlistBacktestC21EntryExitBehaviorDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC21StaticGuardTest.php
docs/watchlist/audit/WS_C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC.md
docs/watchlist/audit/WS_C21_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c21-entry-exit-behavior-diagnostic-source-summary.json
docs/watchlist/audit/_artifacts/c21-final-diagnostic-result-summary.json
docs/watchlist/system/policies/weekly_swing/_refs/WS_ENTRY_EXIT_BEHAVIOR_C21_DESIGN_NOTE.md
```

Operator validation evidence:

```text
PHPUNIT_C21=PASS: OK (6 tests, 173 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (397 tests, 9500 assertions)

C21_FOCUSED_RUNTIME_PASS=true
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-focused.json
artifact_hash=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
profile_count=4
profile_scope=EXPLICIT
evaluated_picks_count=1576
path_missing_count=44
avg_entry_gap_pct=0.00029376446222333
median_entry_gap_pct=0
never_profitable_rate=0.24111675126904
gave_back_profit_rate=0.52791878172589
gap_open_loss_rate=0.2258883248731
exit_stop_count=672
exit_target_count=564
exit_hold_count=340
median_mfe_5d=0.012919645092204
median_mae_5d=-0.018750732450486

C21_ALL_PARAM_RUNTIME_PASS=true
artifact_path=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json
artifact_hash=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
profile_count=8
profile_scope=EXPLICIT
evaluated_picks_count=12600
path_missing_count=360
avg_entry_gap_pct=0.00096323026370276
median_entry_gap_pct=0
never_profitable_rate=0.22603174603175
gave_back_profit_rate=0.55365079365079
gap_open_loss_rate=0.23555555555556
exit_stop_count=5824
exit_target_count=4320
exit_hold_count=2456
median_mfe_5d=0.014354066985646
median_mae_5d=-0.021739130434783
```

C21 final diagnostic decision:

```text
diagnostic_signal_found=1
entry_problem_suspected=0
exit_problem_suspected=1
stop_problem_suspected=1
hold_period_problem_suspected=1
regime_explains_execution_problem=0
```

C21 interpretation:

```text
ENTRY_GAP_MAIN_PROBLEM=false
EXIT_CAPTURE_PROBLEM=true
STOP_BEHAVIOR_PROBLEM=true
HOLD_PERIOD_PROBLEM=true
C20_G03_REGIME_EXPLANATION=false
```

C21 source-level and runtime boundary status:

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C21_CATALOG_CODE=NOT_CREATED
C21_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C20_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
future_path_price_used_for_selection=false
c20_g03_used_as_filter=false
```

C21 conclusion:

```text
C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C21=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C21_FOCUSED_RUNTIME_PASS=true
C21_FOCUSED_ARTIFACT_HASH=d80111aa07a0cb20ec7b4e087be0d4e4c3191fa8
C21_ALL_PARAM_RUNTIME_PASS=true
C21_ALL_PARAM_ARTIFACT_HASH=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
C21_DIAGNOSTIC_RUNTIME_PASS=true
C21_EXECUTION_SIGNAL_FOUND=true
ENTRY_PROBLEM_SUSPECTED=false
EXIT_PROBLEM_SUSPECTED=true
STOP_PROBLEM_SUSPECTED=true
HOLD_PERIOD_PROBLEM_SUSPECTED=true
REGIME_EXPLAINS_EXECUTION_PROBLEM=false
C21_CATALOG_IMPLEMENTATION_DEFERRED=true
C21_CATALOG_CODE=NOT_CREATED
C20_DATE_GATE_NOT_ENOUGH_PRESERVED=true
C19_CATALOG_CANDIDATE_FAILED_PRESERVED=true
C01_TO_C20_IMMUTABLE=true
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C22_EXIT_CAPTURE_SHADOW_DIAGNOSTIC
```

C21 is closed as a useful diagnostic success and catalog-candidate failure. The next work must not tune C21 into a catalog, run OOS, alter canonical entry/exit behavior, reopen C20, or promote C20_G03. The next diagnostic direction is C22 exit-capture shadow analysis using fixed recommendations only.

## PRIOR SESSION - C20 FINAL REGIME AND TRADE-DATE QUALITY GATE DIAGNOSTIC RESULT

Session:
`WATCHLIST - C20 FINAL REGIME AND TRADE-DATE QUALITY GATE DIAGNOSTIC RESULT`

Current status:

`C20_SOURCE_IMPLEMENTED / C20_PHPUNIT_PASS / FULL_WATCHLIST_PHPUNIT_PASS / C20_RUNTIME_VALIDATED / C20_DATE_GATE_NOT_ENOUGH / C20_REGIME_DATE_GATE_STRATEGY_FAILED / C20_CATALOG_CANDIDATE_FAILED / C20_CATALOG_CODE_NOT_CREATED / C20_STOP_TUNING / C19_CATALOG_CANDIDATE_FAILED_PRESERVED / C01_TO_C19_IMMUTABLE / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C20 final source and runtime result:

- `WatchlistBacktestC20RegimeTradeDateDiagnosticService` exists as an IS-only regime/trade-date gate diagnostic;
- `RunBacktestC20RegimeTradeDateDiagnoseCommand` exists as `watchlist:backtest-c20-regime-trade-date-diagnose`;
- C20 service/static guard tests exist and operator validation passed;
- C20 uses C19 proposed selection output as source selection base, then gates `trade_date` before canonical price evaluation;
- C20 allows no-pick days/weeks/months when the trade-date gate blocks weak regime dates;
- C20 records data availability for IHSG proxy, sector proxy, breadth proxy, and candidate distribution;
- C20 did not create a catalog, seeder, seed command, repository approval, or factory mapping;
- C20 did not mutate C19/C18/C17/C16/C15/C14/C01-C07/R1/R2;
- C20 did not run OOS and did not set `production_ready=1`.

Operator validation evidence:

```text
PHPUNIT_C20=PASS: OK (6 tests, 84 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (391 tests, 9327 assertions)

C20_FOCUSED_4_PROFILE=PASS
artifact_path=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-focused.json
artifact_hash=dac6ff71cee04be7b1c4ddcfd06a899808a89167
profile_count=4
profiles_with_quality_improvement=1
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_FOCUSED_7_PROFILE=PASS
artifact_path=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-all-7-profile-focused.json
artifact_hash=29a9743052de2b3164653a85a93e57e22a607dbe
profile_count=7
profiles_with_quality_improvement=2
profiles_with_promising_continue=0
profiles_with_quality_target_reached=0

C20_ALL_PARAM_7_PROFILE=PASS
artifact_path=storage/app/watchlist/backtest/c20-regime-trade-date-diagnostic-all-7-profile-all-param.json
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

C20 final decision:

```text
decision_status=C20_DATE_GATE_NOT_ENOUGH
catalog_allowed=false
oos_allowed=false
next_step=Stop C20 as diagnostic failed unless a new non-lookahead regime data source is added.
best_profile=C20_G03_VOLATILITY_RISK_OFF_FILTER
best_profile_param_id=148
best_profile_row_code=03_SCORE_70_85_LOW_ATR_NEG_ROC20
best_profile_evaluated_picks_count=124
best_profile_avg_ret_net_top=-0.0018095754889618039
best_profile_median_ret_net_top=-0.0004998750312421895
best_profile_win_rate_top=0.43548387096774194
best_profile_period_fail_count=13
best_quality_target_profile=null
small_sample_cannot_be_main_decision=true
```

C20 conclusion:

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

C20 is closed as a useful diagnostic failure for the current regime/date gate hypothesis. The next work must not tune C20 thresholds, promote a C20 catalog, or run OOS. The next diagnostic direction is C21 entry/exit behavior, including `ENTRY=NEXT_OPEN`, stop/target/time exit behavior, gap handling, MFE/MAE, and D+1 to D+5 return path.

## PRIOR SESSION - C19 FINAL STRATEGY MODEL REDESIGN AND PRICE DIAGNOSTIC

C19 is closed as diagnostic success but catalog-candidate failure. No C19 tuning, repeat IS proof, OOS, or catalog path is open.

Final C19 evidence preserved:

```text
PHPUNIT_C19=PASS: OK (13 tests, 192 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (385 tests, 9243 assertions)
C19_TAHAP_5C_FRONTIER_FOCUSED=PASS: artifact_hash=971d1186bff72e185db59dc1c223d423186a7ad4
C19_TAHAP_5C_FRONTIER_ALL_PARAM=PASS: artifact_hash=18ae8b1f1dcfc5ddecc2279d3c9fd0ce69079e6d
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

## PRIOR SESSION - C18 FINAL DIAGNOSTIC-FIRST FUNNEL AND MONTHLY COVERAGE RESULT

Current status:

`C18_DIAGNOSTIC_FIRST / C18_PHASE_A_DIAGNOSTIC_DONE / C18_FUNNEL_DIAGNOSTIC_RUNTIME_VALIDATED / C18_CATALOG_IMPLEMENTATION_DEFERRED / C17_UNCHANGED / C01_TO_C17_IMMUTABLE / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C18 final identity:

```text
C18_PHASE=C18_PHASE_A_DIAGNOSTIC_FIRST_FUNNEL_AUDIT
C18_ARTIFACT_TYPE=C18_FUNNEL_AND_MONTHLY_COVERAGE_DIAGNOSTIC
C18_DIAGNOSTIC_COMMAND=watchlist:backtest-c18-funnel-diagnose
C18_SOURCE_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
C18_SOURCE_CATALOG_VERSION=C17
C18_SOURCE_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

C18 implementation result:

- added `WatchlistBacktestC18FunnelDiagnosticService`;
- added `RunBacktestC18FunnelDiagnoseCommand`;
- registered the command in Console Kernel;
- added runtime-first default diagnostic mode;
- added optional `--deep-funnel` and `--progress-every` for expensive per-date funnel diagnosis;
- added C18 funnel diagnostic and static guard tests;
- added/finalized C18 audit/operator/policy docs;
- did not create C18 catalog, seeder, seed command, repository approval, or factory mapping;
- did not mutate C17/C16/C15/C14/C01-C07/R1/R2;
- did not run OOS;
- did not set `production_ready=1`.

Operator validation evidence provided on 2026-06-16:

```text
PHPUNIT_C18_FUNNEL=PASS: OK (6 tests, 95 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (372 tests, 9051 assertions)
COMMAND_HELP_CONFIRMED_OPTIONS=--deep-funnel,--progress-every
```

Runtime-first full 12 diagnostic evidence:

```text
status=PASS
reason_code=WS_BT_C18_FUNNEL_DIAGNOSTIC_READY
scope=IS_ONLY_DIAGNOSTIC
diagnostic_param_count=12
max_evaluated_picks_count=42
max_recommended_count_before_price_evaluation=0
params_with_empty_evaluation_months=12
c18_catalog_implementation_deferred=1
artifact_hash=b03a79896f3cfd985f6462bd1456494eaac8e405
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

Deep funnel evidence for the best sample row `param_id=150`:

```text
status=PASS
artifact_hash=8b47719f082525a71346aeafd67a5927c1ed1bdd
raw=402887
eligible=40342
scored=40342
top=64
secondary=0
recommended=46
requested_pairs=218
evaluated=42
```

Deep funnel evidence for the best return/controlled pullback row `param_id=149`:

```text
status=PASS
artifact_hash=3dd342f47f7e1397d7ec8defb9e15af26184ca33
raw=402887
eligible=39594
scored=39594
top=83
secondary=0
recommended=38
requested_pairs=184
evaluated=35
```

C18 final root-cause conclusion:

```text
RAW_CANDIDATE_NOT_INSUFFICIENT=true
SCORING_POOL_AVAILABLE=true
PRIMARY_ROOT_CAUSE=selection_collapse_after_scored_pool
SECONDARY_ROOT_CAUSE=volume_dv20_atr_entry_quality_grouping_guards_too_restrictive
MONTHLY_EMPTY_CAUSED_BY_SELECTION_COLLAPSE=true
PRICE_AVAILABILITY_NOT_PRIMARY=true
```

C18 final decision:

```text
C18_DIAGNOSTIC_FIRST=true
C18_PHASE_A_DIAGNOSTIC_DONE=true
C18_FUNNEL_DIAGNOSTIC_RUNTIME_VALIDATED=true
C18_CATALOG_IMPLEMENTATION_DEFERRED=true
C18_CATALOG_CODE=NOT_CREATED
C17_UNCHANGED=true
C01_TO_C17_IMMUTABLE=true
WATCHLIST_SCOPE_ONLY=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
OOS_NOT_RUN=true
production_ready=0
```

C18 Fase B catalog implementation is not approved. The next work should be C19 strategy model redesign, not another C18 catalog iteration. C19 must address grouping/recommendation collapse from large scored pools, `SECONDARY=0`, and volume/DV20/ATR/entry-quality/ROC guard strictness without lowering canonical gates, using blacklist/whitelist shortcuts, running OOS, or setting production ready.

## PRIOR SESSION - C17 FINAL OPERATOR VALIDATION AND STRATEGY QUALITY RESULT


Session:
`WATCHLIST - C17 FINAL OPERATOR VALIDATION AND STRATEGY QUALITY RESULT SESSION`

Current status:

`C17_IMPLEMENTED_SOURCE_LEVEL / C17_RUNTIME_VALIDATED / C17_PHPUNIT_PASS / FULL_WATCHLIST_PHPUNIT_PASS / C17_SEED_PASS / C17_DIAGNOSE_BATCH_PASS / C17_IS_CALIBRATION_DETERMINISTIC / C17_GRID_FAILED_IS_QUALITY / C17_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C17 final implementation identity:

```text
C17_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06
C17_CATALOG_VERSION=C17
C17_CATALOG_COUNT=12
C17_CATALOG_HASH=d411bfbee6fb14c17d821aa92e7e0fea06925d67
C17_RUNTIME_EXTENSION_MODE=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
C17_WORKING_CONCEPT=C17_QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16
```

C17 runtime validation evidence provided by operator:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC17"
OK (11 tests, 579 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (366 tests, 8956 assertions)

php artisan watchlist:backtest-c17-param-grid-seed
status=PASS
catalog_count=12
catalog_hash=d411bfbee6fb14c17d821aa92e7e0fea06925d67
inserted_count=12
updated_count=0
existing_count=0
c16_immutable=1
oos_executed=0
production_ready=0

php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c17-drilldown --summary=storage/app/watchlist/backtest/c17-drilldown-summary.csv --overwrite
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
diagnostic_param_count=12
ready_count=12
blocked_count=0
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0

php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c17-is-run-1.json
status=C17_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
oos_executed=0
production_ready=0

php artisan watchlist:backtest-is-calibrate --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 --from=2023-01-02 --to=2025-05-21 --output=storage/app/watchlist/backtest/c17-is-run-2.json
status=C17_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
oos_executed=0
production_ready=0
```

C17 final IS quality result:

```text
C17_GRID_FAILED_IS_QUALITY=true
reason_code=WS_BT_C17_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
artifact_hash=23c30d70aeefa88701de8d9a59dd9217ee340ae6
best_is_binding=null
param_id_best_is=null
OOS_NOT_RUN=true
production_ready=0
```

C17 failure distribution:

| Reason code | Count |
| --- | ---: |
| `WS_BT_EVAL_MIN_TRADES_FAIL` | 12 |
| `WS_BT_EVAL_ROBUST_RETURN_FAIL` | 5 |
| `WS_BT_EVAL_STABILITY_FAIL` | 12 |

C17 diagnostic interpretation:

- C17 passed engineering/runtime validation, but failed IS strategy-quality validation.
- Minimum trade count remains the hard blocker: threshold is `120`, while the largest C17 row produced only `42` picks.
- Monthly stability remains failed for all rows: `month_win_rate_min=0` for every C17 row, versus required minimum `0.45`.
- Worst monthly average return remains below the canonical floor `-0.01`; observed row-level worst month averages range from `-0.038226` to `-0.022407`.
- Downside gate improved versus C16: C17 has `WS_BT_EVAL_DOWNSIDE_FAIL=0`, with all rows passing `p25_ret_net_top >= -0.03`.
- Robust-return failure remains in `5` rows and is concentrated in the negative-ROC20 / one-R derived branches.
- C17 must not proceed to OOS because `is_valid_param_count=0` and `best_is_binding=null`.

Top C17 rows by average return:

| Param | Row | Picks | Avg net | Median net | P25 net | Win rate | Worst month avg | Failures |
| ---: | --- | ---: | ---: | ---: | ---: | ---: | ---: | --- |
| 149 | `04_DV20_2B_6B_CONTROLLED_PULLBACK` | 35 | 0.008152 | 0.012427 | -0.012497 | 65.71% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 154 | `09_MID_DV20_LOWER_VOLUME_GUARDED` | 16 | 0.007882 | 0.013650 | -0.002502 | 62.50% | -0.022407 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 145 | `00_C16_140_SCORE_65_80_MID_DV20_ONE_R` | 35 | 0.007509 | 0.009399 | -0.008772 | 57.14% | -0.033502 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 148 | `03_SCORE_70_85_LOW_ATR_NEG_ROC20` | 28 | 0.005751 | 0.010993 | -0.000500 | 67.86% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 150 | `05_DV20_25_75_SCORE_68_82` | 42 | 0.004921 | 0.010993 | -0.016792 | 54.76% | -0.033550 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 152 | `07_VOL_150_250_ONE_R_LOW_ATR` | 26 | 0.002450 | 0.006692 | -0.019023 | 53.85% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 151 | `06_VOL_150_250_LOW_ATR_NEG_ROC20` | 25 | 0.001634 | 0.006692 | -0.017164 | 52.00% | -0.029619 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 147 | `02_NEG_ROC20_ONE_R_SCORE_68_82` | 17 | -0.000317 | 0.006692 | -0.020298 | 52.94% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 156 | `11_C16_143_DERIVED_ONE_R_SCORE_70_85` | 19 | -0.000336 | 0.006692 | -0.021458 | 52.63% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 155 | `10_C16_134_DERIVED_NEG_ROC20_SCORE_68_82` | 15 | -0.000492 | 0.009399 | -0.021675 | 53.33% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 153 | `08_SCORE_70_85_DV20_2B_6B_ROC20_COOLING` | 19 | -0.001047 | 0.006692 | -0.021675 | 52.63% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |
| 146 | `01_NEG_ROC20_SCORE_65_80_DV20_2B_6B` | 17 | -0.001111 | 0.006692 | -0.020298 | 52.94% | -0.038226 | `WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL` |

Top C17 rows by sample recovery:

| Param | Row | Picks | Avg net | Win rate | Worst month avg | Period fail/periods |
| ---: | --- | ---: | ---: | ---: | ---: | ---: |
| 150 | `05_DV20_25_75_SCORE_68_82` | 42 | 0.004921 | 54.76% | -0.033550 | 6/20 |
| 145 | `00_C16_140_SCORE_65_80_MID_DV20_ONE_R` | 35 | 0.007509 | 57.14% | -0.033502 | 5/20 |
| 149 | `04_DV20_2B_6B_CONTROLLED_PULLBACK` | 35 | 0.008152 | 65.71% | -0.029619 | 4/20 |
| 148 | `03_SCORE_70_85_LOW_ATR_NEG_ROC20` | 28 | 0.005751 | 67.86% | -0.038226 | 5/19 |
| 152 | `07_VOL_150_250_ONE_R_LOW_ATR` | 26 | 0.002450 | 53.85% | -0.029619 | 6/13 |
| 151 | `06_VOL_150_250_LOW_ATR_NEG_ROC20` | 25 | 0.001634 | 52.00% | -0.029619 | 6/13 |

C17 final decision:

```text
C17_RUNTIME_VALIDATED=true
C17_REJECTED_AS_STRATEGY_CATALOG=true
NEXT_CATALOG_REQUIRED=true
NEXT_CATALOG_NOT_DESIGNED=true
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

C17 next work is now superseded by active C18 Fase A diagnostic-first work. C18 must first analyze funnel and monthly coverage root cause before any catalog is designed. Any future C18 catalog must be based on IS diagnostic evidence and must not lower canonical gates, mutate C17/C16/C15/C14/C01-C07/R1/R2, use ticker/month blacklist, sector whitelist, best-of-failed binding, or run OOS before a valid IS candidate exists.

## C16 FINAL BASELINE RETAINED

C16 remains immutable and unchanged. C16 final status stays `C16_GRID_FAILED_IS_QUALITY`, `OOS_NOT_RUN=true`, and `production_ready=0`. C17 used C16 only as diagnostic direction, not as binding or promotion.

## C16 final operator validation evidence

Operator PHPUnit validation after follow-up patches:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC16"
OK (12 tests, 553 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (355 tests, 8377 assertions)
```

Operator seed validation:

```text
php artisan watchlist:backtest-c16-param-grid-seed
status=PASS
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06
catalog_version=C16
catalog_count=12
catalog_hash=0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2
inserted_count=0
updated_count=0
existing_count=12
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
c15_immutable=1
oos_executed=0
production_ready=0
```

Operator diagnose-batch validation:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c16-drilldown --summary=storage/app/watchlist/backtest/c16-drilldown-summary.csv --overwrite
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
catalog_version=C16
catalog_count=12
catalog_hash=0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2
diagnostic_param_count=12
ready_count=12
blocked_count=0
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

Operator IS calibration validation was run twice and deterministic:

```text
run_1_status=C16_GRID_FAILED_IS_QUALITY
run_2_status=C16_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_C16_NO_VALID_IS_CANDIDATE
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C16_2026_06
catalog_version=C16
catalog_count=12
catalog_hash=0ad1289f79d78787cdca275f0b3f3e2ba90bf8f2
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
artifact_hash=63698d0c809a1f2124d8218273ba4d34d9c78deb
production_ready=0
```

The two IS calibration artifacts were deterministic because both runs produced the same final artifact hash:

```text
c16_is_run_1_artifact_hash=63698d0c809a1f2124d8218273ba4d34d9c78deb
c16_is_run_2_artifact_hash=63698d0c809a1f2124d8218273ba4d34d9c78deb
```

## C16 final strategy-quality result

C16 failed canonical IS strategy-quality gates:

```text
C16_GRID_FAILED_IS_QUALITY=true
reason_code=WS_BT_C16_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
best_is_binding=null
param_id_best_is=null
OOS_ELIGIBLE=false
OOS_NOT_RUN=true
production_ready=0
```

C16 failure distribution from `all_evaluations`:

```text
WS_BT_EVAL_MIN_TRADES_FAIL=12
WS_BT_EVAL_STABILITY_FAIL=12
WS_BT_EVAL_ROBUST_RETURN_FAIL=2
WS_BT_EVAL_DOWNSIDE_FAIL=1
```

Interpretation:

- C16 is technically validated and reached canonical gates for all 12 rows.
- C16 failed as a strategy catalog because sample count and monthly stability were insufficient for every row.
- The primary blockers are `WS_BT_EVAL_MIN_TRADES_FAIL` and `WS_BT_EVAL_STABILITY_FAIL`, not a broad runtime/data failure.
- C16 must not be promoted, OOS-tested, or marked production-ready.
- C16 should be preserved as failed IS evidence and used only as diagnostic input for a future C17 catalog.

Top failed-but-informative C16 rows:

```text
param_id=140 row_code=07_ONE_R_TARGET_MID_DV20 picks=18 avg=0.011211943757134253 median=0.01964352860021483 p25=-0.0005000262656370382 win_rate=0.6666666666666666 month_win_rate_min=0 month_avg_ret_net_min=-0.020496921903774473 reasons=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id=134 row_code=01_STRICT_CORE_NEGATIVE_ROC20 picks=9 avg=0.010945814734692241 median=0.016024647147672825 p25=0.009806374143193075 win_rate=0.7777777777777778 month_win_rate_min=0 month_avg_ret_net_min=-0.023051207110541698 reasons=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id=143 row_code=10_NEGATIVE_ROC20_ONE_R_TIGHT picks=9 avg=0.010183644001474135 median=0.009806374143193075 p25=-0.0005000750112516877 win_rate=0.6666666666666666 month_win_rate_min=0 month_avg_ret_net_min=-0.00876254113692971 reasons=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id=137 row_code=04_DV20_TO_6B_STRICT_SCORE_WINDOW picks=23 avg=0.009360533427912102 median=0.012426717442501378 p25=-0.00849797552112197 win_rate=0.6956521739130435 month_win_rate_min=0 month_avg_ret_net_min=-0.02961925251413419 reasons=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id=141 row_code=08_DV20_TO_7_5B_STRICT_RECOVERY picks=27 avg=0.008633804146019317 median=0.015121058474093186 p25=-0.01669320666024352 win_rate=0.6296296296296297 month_win_rate_min=0 month_avg_ret_net_min=-0.02961925251413419 reasons=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_STABILITY_FAIL
```

## C16 final next action

```text
NEXT_ACTION=DOCUMENT_C16_FINAL_AND_DESIGN_C17
C16_MUTATION_ALLOWED=false
C16_OOS_ALLOWED=false
C16_PROMOTION_ALLOWED=false
C17_DESIGN_DIRECTION=QUALITY_PRESERVING_SAMPLE_RECOVERY_FROM_C16_FAILURE_EVIDENCE
```

C17 should use C16 rows `140`, `134`, `143`, `137`, and `141` as diagnostic anchors only. C17 must be a new immutable catalog and must not lower canonical gates, mutate C16, use best-of-failed binding, blacklist tickers/months, or run OOS until a valid IS candidate exists.

C16 result is recorded in:

```text
docs/watchlist/audit/WS_C16_QUALITY_RECOVERY_DESIGN_RESULT.md
docs/watchlist/audit/WS_C16_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c16-source-implementation-summary.json
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C16_DESIGN_NOTE.md
```

## PRIOR SESSION - C15 FINAL EVIDENCE SESSION

Session:
`WATCHLIST - C15 FINAL EVIDENCE SESSION`

Current status:

`C15_IMPLEMENTED / C15_RUNTIME_PAYLOAD_FIX4_VALIDATED / IS_QUALITY_FAILED / C15_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

C15 final implementation evidence:

- immutable catalog `WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06` exists with `catalog_version=C15`, `catalog_count=12`, and `catalog_hash=cc07324262151783dc6b5583ebd91a96c0d0527d`;
- runtime extension `C15_CONTROLLED_PULLBACK_MID_LIQUIDITY_ANTI_OVEREXTENSION` is implemented and receives the required runtime payload after fix4;
- C15 keeps controlled ROC5 pullback, mid-DV20 range, volume spike control, neutral/cooling ROC20 range, and score upper cap behavior;
- C15 does not mutate R1/R2/C01/C02/C03/C04/C05/C06/C07/C14 historical catalog identities;
- C15 does not use collapsed diagnostic axes as promotion proof;
- C15 did not run OOS and remains `production_ready=0`.

### C15 post-fix4 operator validation evidence

Operator-provided validation after fix4 recorded:

```text
WatchlistBacktestC15: OK (10 tests, 534 assertions)
WatchlistCandidateUniverseService: OK (5 tests, 68 assertions)
WatchlistScoringService: OK (9 tests, 107 assertions)
Full Watchlist suite: OK (341 tests, 7771 assertions)
C15 diagnose-batch: status=PASS, ready_count=12, blocked_count=0
C15 fix4 drilldown: missing_runtime_evidence_fields empty for all 12 rows
C15 IS calibration run 1: status=C15_GRID_FAILED_IS_QUALITY, reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
C15 IS calibration run 2: status=C15_GRID_FAILED_IS_QUALITY, reason_code=WS_BT_C15_NO_VALID_IS_CANDIDATE
C15 deterministic artifact_hash=1b96a2c38c0aacced72e441bb8d0ecaff045eabf
strict_is_boundary_all_evaluations=1
no_oos_market_data_read=True
no_oos_table_mutation=True
OOS_NOT_RUN
production_ready=0
```

C15 failed locked IS quality gates honestly, not because of runtime missing metrics:

```text
is_valid_param_count=0
is_failed_param_count=12
failure_reason_codes=WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
all_rows_reached_canonical_gates=True
eval_count=12
best_of_failed_forbidden=True
param_id_best_is=
best_is_binding_hash=
```

C15 strategy-quality interpretation:

- best failed anchors were `param_id=122` and `param_id=130` because both had positive average return, positive median return, controlled p25 downside, and win-rate above 60%;
- both anchors failed minimum-trade and monthly-stability gates, especially `month_win_rate_min=0`;
- sample-recovery rows such as `param_id=129` and `param_id=132` increased trade count but degraded median/average quality and still failed monthly stability;
- `score` bucket `0.7..0.8` and `vol_ratio` bucket `1.5..2` were the most useful diagnostic patterns;
- `score` bucket `0.8..0.9` and low-volume buckets `1.0..1.5` repeatedly degraded quality;
- C15 is therefore rejected as a strategy-quality catalog and should not be promoted, manually selected, or sent to OOS.

C15 decision:

```text
C15_CATALOG_CREATED=true
C15_CATALOG_CODE=WS_BT_GRID_DOWNSIDE_STABILITY_C15_2026_06
C15_CATALOG_VERSION=C15
C15_CATALOG_COUNT=12
C15_CATALOG_HASH=cc07324262151783dc6b5583ebd91a96c0d0527d
C15_SEED_STATUS=PASS
C15_RUNTIME_PAYLOAD_STATUS=PASS
C15_DRILLDOWN_STATUS=PASS_RUNTIME_READY
C15_IS_CALIBRATION_STATUS=C15_GRID_FAILED_IS_QUALITY
C15_VALID_PARAM_COUNT=0
C15_FAILED_PARAM_COUNT=12
C15_BEST_FAILED_ANCHORS=122,130
C15_STRATEGY_DECISION=REJECTED_AS_IS_QUALITY_CATALOG
C15_NEXT_ACTION=C16_SAMPLE_RECOVERY_AND_STABILITY_DESIGN_FROM_C15_EVIDENCE
OOS_NOT_RUN
production_ready=0
```

C15 result is recorded in:

```text
docs/watchlist/audit/WS_C15_STRATEGY_QUALITY_ROOT_CAUSE_FINAL_RESULT.md
docs/watchlist/audit/WS_C15_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c15-final-evidence-summary.json
docs/watchlist/audit/_artifacts/c15-fix4-param-summary.csv
```

## PRIOR SESSION - C14 VARIABLE RISK-EXIT CATALOG SESSION

Session:
`WATCHLIST - C14 VARIABLE RISK-EXIT CATALOG SESSION`

Status:
`C14_IMPLEMENTED_SEEDED_DETERMINISTIC / IS_QUALITY_FAILED / C14_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C14 implementation evidence:

- C14 created a new strategy catalog from C13 support, without mutating R1/R2/C01/C02/C03/C04/C05/C06/C07;
- C14 catalog identity: `WS_BT_GRID_DOWNSIDE_STABILITY_C14_2026_06`, version `C14`, count `12`, hash `079430de7c94fd0226d0f3b47d5eb1e9f906fd6a`;
- C14 uses C13 `VARIABLE_RISK_EXIT_AXIS_V1` support for `risk.stop_atr_mult` and `risk.min_rr`;
- C14 keeps `backtest.holding_days`, `backtest.target_pct`, `backtest.stop_pct`, and sector filters blocked;
- C14 reuses the C07 candidate-selection confirmation layer and fixes the runtime enrichment scope so C14 receives the same C07 optional metrics when the C07 extension is active;
- C14 seed command passed with all historical immutable markers set to `1` through C07;
- C14 IS calibration was run twice for `2023-01-02..2025-05-21` and produced deterministic canonical artifact hash `70d021daafc254fb2ed826ff05015d42bac5dd8d`;
- C14 final IS result: `status=C14_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C14_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=12`;
- C14 failure reasons: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- forensic evidence: `minimum_trade_count_passed=12/12`, `minimum_coverage_passed=12/12`, `median_return_non_negative_passed=0/12`, `p25_downside_bound_passed=5/12`, `monthly_win_rate_floor_passed=0/12`, `monthly_average_floor_passed=0/12`;
- metric ranges: `picks_count=729..1359`, `median_ret_net_top=-1.5648%..-0.4848%`, `p25_ret_net_top=-3.5375%..-2.6583%`, `month_win_rate_min=14.81%..30.77%`;
- C14 did not select a best-of-failed binding: `param_id_best_is=` and `best_is_binding_hash=` are empty;
- C14 keeps `oos_executed=0` and `production_ready=0`;
- validation passed: `WatchlistBacktestC14` = `OK (10 tests, 458 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, `WatchlistBacktestExitAxisSupport` = `OK (11 tests, 59 assertions)`, full Watchlist = `OK (329 tests, 7186 assertions)`.

C14 decision:

```text
C14_REJECTED_AS_STRATEGY_QUALITY_CATALOG
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C14 result is recorded in:

```text
docs/watchlist/audit/WS_C14_VARIABLE_RISK_EXIT_CATALOG_FINAL_RESULT.md
docs/watchlist/audit/WS_C14_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c14-is-run-1.json
docs/watchlist/audit/_artifacts/c14-is-run-2.json
docs/watchlist/audit/_artifacts/c14-forensic-summary.csv
```

## PRIOR SESSION - C13 EXIT AXIS SUPPORT SESSION

Session:
`WATCHLIST - C13 EXIT AXIS SUPPORT SESSION`

Status:
`C13_EXIT_AXIS_SUPPORT_READY / STRATEGY_CATALOG_NOT_CREATED / C07_REJECTED_AS_STRATEGY_CATALOG / FUTURE_CATALOG_DEFINITION_WORK_AUTHORIZED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Prior C13 support evidence:

- no new strategy catalog was created; C13 is an exit-axis support implementation and audit session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C13 command `watchlist:backtest-exit-axis-support-audit` reads the C12 redesign-contract JSON and writes a C13 support-audit JSON artifact;
- C13 command result: `status=PASS`, `reason_code=WS_BT_C13_EXIT_AXIS_SUPPORT_READY`;
- source C12 artifact hash: `04d4e2f230685962fadd1bc26c294cbaed10f38b`;
- C13 artifact hash is deterministic across two runs: `73ba035edfa22f19b4b3525ee3f522241fbae291`;
- C13 docs artifact file SHA1: `11548827E3DD8249BBE3FDAA2F545816A01FA31C`;
- C13 implements support for future variable risk-exit axes `risk.stop_atr_mult` and `risk.min_rr`;
- C13 keeps `backtest.holding_days`, `backtest.target_pct`, and `backtest.stop_pct` blocked for first-phase catalogs;
- C13 preserves fixed execution/grouping guards for R1/R2/C01/C02/C03/C04/C05/C06/C07 and keeps the legacy drift error message;
- C13 keeps `catalog_creation_authorized=0`, `exit_model_catalog_authorized=0`, `strategy_catalog_created=0`, `oos_executed=0`, and `production_ready=0`;
- next required step is `CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY`;
- validation passed: `WatchlistBacktestExitAxisSupport` = `OK (11 tests, 59 assertions)`, `WatchlistBacktestR2ParamGridParamsetFactory` = `OK (12 tests, 106 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, `WatchlistBacktestExitModelRedesignContract` = `OK (3 tests, 33 assertions)`, `WatchlistBacktestExitModelContractAudit` = `OK (3 tests, 34 assertions)`, full Watchlist = `OK (319 tests, 6728 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C13 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C13_STRATEGY_CATALOG_CREATED=false
CATALOG_CREATION_AUTHORIZED=false
FUTURE_CATALOG_DEFINITION_WORK_AUTHORIZED=true
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_REQUIRED_STEP=CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C13 result is recorded in:

```text
docs/watchlist/audit/WS_C13_EXIT_AXIS_SUPPORT_FINAL_RESULT.md
docs/watchlist/audit/WS_C13_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c13-exit-axis-support-audit.json
```

## PRIOR SESSION - C12 EXIT MODEL REDESIGN CONTRACT SESSION

Session:
`WATCHLIST - C12 EXIT MODEL REDESIGN CONTRACT SESSION`

Status:
`C12_EXIT_MODEL_REDESIGN_CONTRACT_READY / CATALOG_CREATION_NOT_AUTHORIZED / C07_REJECTED_AS_STRATEGY_CATALOG / C12_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C12 diagnostic evidence:

- no new strategy catalog was created; C12 is a contract-only exit-model redesign session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C12 command `watchlist:backtest-exit-model-redesign-contract` reads the C11 contract-audit JSON and writes a C12 redesign-contract JSON artifact;
- C12 command result: `status=PASS`, `reason_code=WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY`;
- source C11 artifact hash: `4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea`;
- C12 artifact hash is deterministic across two runs: `04d4e2f230685962fadd1bc26c294cbaed10f38b`;
- C12 docs artifact file SHA1: `B3575122DB69A0CA8EAD4D3C78B328687C2CC894`;
- C12 marks `design_contract_ready=1`, but keeps `catalog_creation_authorized=0` and `exit_model_catalog_authorized=0`;
- allowed first-phase future implementation axes are `risk.min_rr` and `risk.stop_atr_mult`, because both are represented in official schema/factory/runtime metrics but fixed for C01-C07;
- blocked first-phase axes are `backtest.holding_days` and `backtest.target_pct|backtest.stop_pct`;
- next required step is `IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG`;
- validation passed: `WatchlistBacktestExitModelRedesignContract` = `OK (3 tests, 33 assertions)`, `WatchlistBacktestExitModelContractAudit` = `OK (3 tests, 34 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (308 tests, 6669 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C12 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C12_STRATEGY_CATALOG_CREATED=false
CATALOG_CREATION_AUTHORIZED=false
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_REQUIRED_STEP=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C12 result is recorded in:

```text
docs/watchlist/audit/WS_C12_EXIT_MODEL_REDESIGN_CONTRACT_FINAL_RESULT.md
docs/watchlist/audit/WS_C12_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c12-exit-model-redesign-contract.json
```

## PRIOR SESSION - C11 EXIT MODEL CONTRACT AUDIT SESSION

Session:
`WATCHLIST - C11 EXIT MODEL CONTRACT AUDIT SESSION`

Status:
`C11_EXIT_MODEL_CONTRACT_AUDIT_READY / EXIT_MODEL_CATALOG_NOT_AUTHORIZED / C07_REJECTED_AS_STRATEGY_CATALOG / C11_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C11 diagnostic evidence:

- no new strategy catalog was created; C11 is an IS-only exit-model contract audit session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C11 command `watchlist:backtest-exit-model-contract-audit` reads the C10 summary CSV and writes a JSON audit artifact;
- C11 command result: `status=PASS`, `reason_code=WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY`, `summary_row_count=12`;
- source C10 summary SHA1: `04ee547ee3f982901cabe23e55078868f14104c9`;
- C11 artifact hash is deterministic across two runs: `4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea`;
- C11 docs artifact file SHA1: `E00E9BA960E50CE1E32ABA717BDFBD1EC0BE54A4`;
- exit totals remain weak: `hit_target_total=2585`, `hit_stop_total=4927`, `timeout_hold_expired_total=6858`, target hit share `17.99%`, stop-or-timeout share `82.01%`;
- best C10 metrics still fail locked gates: median `-0.6993%`, p25 `-3.4276%`, monthly win-rate minimum `25.00%`;
- exit-model catalog authorization is explicitly false because current code/factory/runtime contract blocks unsafe exit-axis catalog creation;
- validation passed: `WatchlistBacktestExitModelContractAudit` = `OK (3 tests, 34 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, `WatchlistBacktestIsFailureDrilldown` = `OK (6 tests, 123 assertions)`, full Watchlist = `OK (305 tests, 6636 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C11 blocking reasons:

```text
C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT
PUBLISHED_RUNTIME_FORCES_HOLD_5
PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS
C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES
C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET
```

C11 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C11_STRATEGY_CATALOG_CREATED=false
EXIT_MODEL_CATALOG_AUTHORIZED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C11 result is recorded in:

```text
docs/watchlist/audit/WS_C11_EXIT_MODEL_CONTRACT_AUDIT_FINAL_RESULT.md
docs/watchlist/audit/WS_C11_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c11-exit-model-contract-audit.json
```

## PRIOR SESSION - C10 EXIT MODEL DIAGNOSTIC / STRATEGY QUALITY DECISION GATE SESSION

Session:
`WATCHLIST - C10 EXIT MODEL DIAGNOSTIC / STRATEGY QUALITY DECISION GATE SESSION`

Status:
`C10_EXIT_MODEL_DIAGNOSTIC_EXECUTED / C07_BATCHED_DRILLDOWN_EXECUTED / C07_REJECTED_AS_STRATEGY_CATALOG / C10_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C10 diagnostic evidence:

- no new strategy catalog was created; C10 is an IS-only exit-model diagnostic session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C10 added diagnostic-only exit outcome evidence to scoped drilldown artifacts and the batch CSV: `hit_target_count`, `hit_stop_count`, `timeout_hold_expired_count`, `exit_model_diagnostic_summary`, and `per_param_exit_outcomes`;
- C10 batch command executed all 12 C07 params and wrote 12 scoped JSON artifacts plus a summary CSV;
- batch command result: `status=PASS`, `reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY`, `diagnostic_param_count=12`, `ready_count=12`, `blocked_count=0`;
- summary artifact SHA1: `04EE547EE3F982901CABE23E55078868F14104C9`;
- C10 batch metrics still fail strategy quality: picks `728..1355`, median return `-1.0279%..-0.6993%`, p25 return `-4.0156%..-3.4276%`, monthly win-rate minimum `17.86%..25.00%`;
- exit outcomes show targets are hit less often than stops/time-expiry: `hit_target_count=168..249`, `hit_stop_count=315..504`, `timeout_hold_expired_count=443..667`;
- `missing_runtime_evidence_fields` remains empty for C07 batch artifacts;
- nullable no-positive fields remain explicit: `corporate_action_flag`, `corporate_action_types`, and `event_risk_reasons`;
- next focus remains `STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG`;
- next decision remains `NEXT_CATALOG_NOT_DESIGNED`;
- validation passed: `WatchlistBacktestIsFailureDrilldown` = `OK (6 tests, 123 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (302 tests, 6602 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C10 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C10_STRATEGY_CATALOG_CREATED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C10 result is recorded in:

```text
docs/watchlist/audit/WS_C10_EXIT_MODEL_DIAGNOSTIC_FINAL_RESULT.md
docs/watchlist/audit/WS_C10_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c10-batched-c07-exit-model-summary.csv
```

## PRIOR SESSION - C09 NULLABLE EVENT CONTEXT RUNTIME COVERAGE SESSION

Session:
`WATCHLIST - C09 NULLABLE EVENT CONTEXT RUNTIME COVERAGE SESSION`

Status:
`C09_NULLABLE_EVENT_CONTEXT_CLASSIFIED / C07_BATCHED_DRILLDOWN_EXECUTED / C07_REJECTED_AS_STRATEGY_CATALOG / C09_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C09 diagnostic evidence:

- no new strategy catalog was created; C09 is a runtime diagnostic semantics session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- read-only IS source coverage confirmed corporate-action context exists in the DB but is sparse: `market_data_corporate_actions=262`, `market_data_trading_status_events=1469`, `eod_indicators=501386`, `corporate_action_types_present=243`, `event_risk_reasons_present=28746`, `trading_status_code_present=69560`;
- IS-only drilldown now distinguishes `AVAILABLE_NULLABLE_NO_POSITIVE_RUNTIME_EVIDENCE` from `FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE`;
- C09 batch command executed all 12 C07 params and wrote 12 scoped JSON artifacts plus a summary CSV;
- batch command result: `status=PASS`, `reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY`, `diagnostic_param_count=12`, `ready_count=12`, `blocked_count=0`;
- summary artifact SHA1: `4A317C890F416619FA2F24396D1EC9DDDE8CC3AB`;
- C09 batch metrics still fail strategy quality: picks `728..1355`, median return `-1.0279%..-0.6993%`, p25 return `-4.0156%..-3.4276%`, monthly win-rate minimum `17.86%..25.00%`;
- `missing_runtime_evidence_fields` is now empty for C07 batch artifacts;
- nullable no-positive fields are explicit: `corporate_action_flag`, `corporate_action_types`, and `event_risk_reasons`;
- next focus changed from runtime payload enrichment to `STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG`;
- validation passed: `WatchlistBacktestIsFailureDrilldown` = `OK (6 tests, 118 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (302 tests, 6597 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C09 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C09_STRATEGY_CATALOG_CREATED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C09 result is recorded in:

```text
docs/watchlist/audit/WS_C09_NULLABLE_EVENT_CONTEXT_RUNTIME_COVERAGE_FINAL_RESULT.md
docs/watchlist/audit/WS_C09_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c09-batched-c07-nullable-context-summary.csv
```

## PRIOR SESSION - C08 RUNTIME PAYLOAD ENRICHMENT AND BATCHED C07 FAILURE DRILLDOWN SESSION

Session:
`WATCHLIST - C08 RUNTIME PAYLOAD ENRICHMENT AND BATCHED C07 FAILURE DRILLDOWN SESSION`

Status:
`C08_RUNTIME_PAYLOAD_ENRICHED / C07_BATCHED_DRILLDOWN_EXECUTED / C07_REJECTED_AS_STRATEGY_CATALOG / C08_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C08 diagnostic evidence:

- no new strategy catalog was created; C08 is a runtime diagnostic/enrichment session;
- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- runtime payload enrichment now carries `corporate_action_types`, `trading_status_code`, and `event_risk_reasons` through the diagnostic path when source-backed values exist;
- nullable market-data semantics remain intact: missing source context is not converted into `0`;
- new IS-only batch command `watchlist:backtest-is-diagnose-batch` executed all 12 C07 params and wrote 12 scoped JSON artifacts plus a summary CSV;
- batch command result: `status=PASS`, `reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY`, `diagnostic_param_count=12`, `ready_count=12`, `blocked_count=0`;
- summary artifact SHA1: `49101D6AA702A898A3F691A7553823A8DFB2F125`;
- C07 batch metrics still fail strategy quality: picks `728..1355`, median return `-1.0279%..-0.6993%`, p25 return `-4.0156%..-3.4276%`, monthly win-rate minimum `17.86%..25.00%`;
- after enrichment, `trading_status_code`, `event_risk_flag`, `is_suspended`, and `is_uma` are available in runtime evidence, while `corporate_action_flag`, `corporate_action_types`, and `event_risk_reasons` remain missing in evaluated C07 trades;
- validation passed: `WatchlistBacktestIsFailureDrilldown` = `OK (5 tests, 107 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (301 tests, 6586 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C08 decision:

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C08_STRATEGY_CATALOG_CREATED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C08 result is recorded in:

```text
docs/watchlist/audit/WS_C08_RUNTIME_PAYLOAD_AND_BATCHED_C07_DRILLDOWN_FINAL_RESULT.md
docs/watchlist/audit/WS_C08_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c08-batched-c07-drilldown-summary.csv
```

## PRIOR SESSION - C07 SCOPED FAILURE DRILLDOWN / NEXT-CATALOG DECISION GATE SESSION

Session:
`WATCHLIST - C07 SCOPED FAILURE DRILLDOWN / NEXT-CATALOG DECISION GATE SESSION`

Status:
`C07_SCOPED_DRILLDOWN_IMPLEMENTED / C07_SCOPED_DRILLDOWN_EXECUTED / C07_SCOPED_DRILLDOWN_DETERMINISTIC / C08_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C07 scoped drilldown evidence:

- C07 remains rejected as a strategy-quality catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- the IS-only diagnostic command now supports explicit scoped filters `--param-id` and `--row-code` so heavy drilldowns can be run without full-catalog timeout;
- scoped C07 drilldown was executed for `param_id=102` and `param_id=106`, each with two deterministic runs;
- param 102 artifact hash and file SHA1 were stable: `c362ff6682a69b8db145887214b137e786ea731a` / `27A86FD7737628F549134E3951E60C353E143AC5`;
- param 106 artifact hash and file SHA1 were stable: `f7a91a3e9dc1c3ab13aedd04a7daabf51f90201e` / `61A9E01CA23E5B292790323B5E22EB1BD7B7A720`;
- both scoped rows retained negative average/median return, p25 downside below `-3%`, and monthly win-rate minimum far below `45%`;
- runtime trade evidence was available for most C07 feature fields, but `corporate_action_flag` was missing in scoped evidence;
- risk and volume score components had positive directional association, while momentum was inversely associated and breakout was weak/inconsistent;
- C08 was not created because scoped evidence does not justify another same-shape threshold catalog;
- validation passed after scoped drilldown changes: `WatchlistBacktestIsFailureDrilldown` = `OK (5 tests, 84 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (301 tests, 6563 assertions)`;
- OOS was not run, no best-of-failed binding was selected, and production readiness remains false.

C07 scoped drilldown summary:

```text
param_102=05_ANTI_REVERSAL_NOT_OVEREXTENDED / picks=1017 / median=-0.6993% / p25=-3.4831% / month_win_min=25.00%
param_106=09_LOW_ATR_RANGE_SECTOR / picks=986 / median=-0.7569% / p25=-3.4276% / month_win_min=20.59%
next_focus=RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

C07 scoped drilldown decision:

```text
C08_NOT_CREATED
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

Scoped drilldown result is recorded in:

```text
docs/watchlist/audit/WS_C07_SCOPED_FAILURE_DRILLDOWN_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c07-scoped-drilldown-summary.csv
```

## PRIOR SESSION - C07 STRATEGY-QUALITY REDESIGN / RUNTIME FEATURE AUDIT SESSION

Session:
`WATCHLIST - C07 STRATEGY-QUALITY REDESIGN / RUNTIME FEATURE AUDIT SESSION`

Status:
`C07_IMPLEMENTED / C07_SEED_PASS / C07_IS_EXECUTION_PASS / C07_IS_QUALITY_FAIL / C07_REJECTED_AS_STRATEGY_CATALOG / C07_DETERMINISTIC_TWO_RUN / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C07 final evidence:

- C07 is a new catalog identity, not a patch to C06: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C07 uses newly audited runtime-supported feature axes: `roc_5`, `roc_10`, `close_to_ll20_pct`, `range_20_pct`, `range_position_20_pct`, `sector_roc20`, `rs_20_vs_sector`, `sector_rs_20_vs_ihsg`, and event-risk flags, plus existing score/trend/setup guards;
- C07 does not add a sector filter; sector-relative fields are used only as continuous confirmation metrics;
- C07 implementation validation passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"` = PASS / `OK (10 tests, 376 assertions)`;
- full Watchlist PHPUnit passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist` = PASS / `OK (300 tests, 6544 assertions)`;
- C07 seed passed: `inserted_count=12`, `updated_count=0`, `existing_count=0`;
- R1/R2/C01/C02/C03/C04/C05/C06 immutability was preserved during C07 seed;
- C07 IS calibration run 1 and run 2 both executed and produced the same deterministic artifact hash `c562d0a37ec7911c17c50072413fbbae25bb6114`;
- C07 IS quality failed deterministically: `status=C07_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C07_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=12`;
- C07 failure reason family: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- C07 did not invoke OOS: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- C07 has no frozen best IS binding: `param_id_best_is=` empty and `best_is_binding_hash=` empty;
- C07 production readiness remains false: `production_ready=0`.

C07 final forensic summary:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=12,WS_BT_EVAL_ROBUST_RETURN_FAIL=12,WS_BT_EVAL_STABILITY_FAIL=12
```

C07 final decision state:

```text
C07_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C07 is not eligible for OOS because it has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. OOS remains `NOT_RUN` and must not be claimed PASS.

## PRIOR SESSION - C06 MODERATE-CAP CANDIDATE-SELECTION IMPLEMENTATION SESSION

Session:
`WATCHLIST - C06 MODERATE-CAP CANDIDATE-SELECTION IMPLEMENTATION SESSION`

Status:
`C06_IMPLEMENTED / C06_SEED_PASS / C06_IS_EXECUTION_PASS / C06_IS_QUALITY_FAIL / C06_REJECTED_AS_STRATEGY_CATALOG / C06_DETERMINISTIC_TWO_RUN / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C06 final evidence:

- C06 is a new catalog identity, not a patch to C05: `WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06`, version `C06`, count `12`, hash `6c93d67fb77319a02cecc3d96fd99bb0e139a1ac`;
- C06 uses only runtime-supported candidate-selection axes: DV20 upper/lower runtime bounds, volume upper/lower runtime bounds, ATR band, ROC band, close-to-HH20 setup band, score component pass-count/average floor, and trend pass-count floor;
- C06 does not add a sector filter; sector remains diagnostic-only;
- C06 implementation validation passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC06"` = PASS / `OK (13 tests, 503 assertions)`;
- full Watchlist PHPUnit passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist` = PASS / `OK (290 tests, 6168 assertions)`;
- C06 seed passed: `inserted_count=12`, `updated_count=0`, `existing_count=0`;
- R1/R2/C01/C02/C03/C04/C05 immutability was preserved during C06 seed;
- C06 IS calibration run 1 and run 2 both executed and produced the same deterministic artifact hash `ede8ca6f53ea49141a5e047e6094b7a282cdb232`;
- C06 IS quality failed deterministically: `status=C06_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=12`;
- C06 failure reason family: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_MIN_TRADES_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- C06 did not invoke OOS: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- C06 has no frozen best IS binding: `param_id_best_is=` empty and `best_is_binding_hash=` empty;
- C06 production readiness remains false: `production_ready=0`.

C06 final forensic summary:

```text
picks_count=9..214
median_ret_net_top=-1.6757%..1.6637%
p25_ret_net_top=-3.4390%..-0.6101%
month_win_rate_min=0.00%..0.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=5,WS_BT_EVAL_MIN_TRADES_FAIL=9,WS_BT_EVAL_ROBUST_RETURN_FAIL=10,WS_BT_EVAL_STABILITY_FAIL=12
```

C06 final decision state:

```text
C06_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C06 is not eligible for OOS because it has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. OOS remains `NOT_RUN` and must not be claimed PASS.

## PRIOR SESSION - C05 SOFT SAMPLE-AWARE CANDIDATE-SELECTION IMPLEMENTATION SESSION

Session:
`WATCHLIST - C05 SOFT SAMPLE-AWARE CANDIDATE-SELECTION IMPLEMENTATION SESSION`

Status:
`C05_IMPLEMENTED / C05_SEED_PASS / C05_IS_EXECUTION_PASS / C05_IS_QUALITY_FAIL / C05_REJECTED_AS_STRATEGY_CATALOG / C05_DETERMINISTIC_TWO_RUN / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C05 final evidence:

- C05 is a new catalog identity, not a patch to C04: `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06`, version `C05`, count `12`, hash `476af5dde18079b1270556bc44bbc632edd46e27`;
- C05 uses only runtime-supported candidate-selection axes and a soft pass-count/average floor to address C04 sample collapse;
- C05 does not add a sector filter; sector remains diagnostic-only;
- C05 implementation validation passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC05"` = PASS / `OK (13 tests, 523 assertions)`;
- full Watchlist PHPUnit passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist` = PASS / `OK (277 tests, 5665 assertions)`;
- C05 seed passed: `inserted_count=12`, `updated_count=0`, `existing_count=0`;
- R1/R2/C01/C02/C03/C04 immutability was preserved during C05 seed;
- C05 IS calibration run 1 and run 2 both executed and produced the same deterministic artifact hash `f8288cb2d395e397f433dae854c0ad80b4650a8d`;
- C05 IS quality failed deterministically: `status=C05_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C05_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=12`;
- C05 failure reason family: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- C05 did not invoke OOS: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- C05 has no frozen best IS binding: `param_id_best_is=` empty and `best_is_binding_hash=` empty;
- C05 production readiness remains false: `production_ready=0`.

C05 final forensic summary:

```text
picks_count=370..886
median_ret_net_top=-1.6122%..-0.7301%
p25_ret_net_top=-4.0209%..-3.2708%
month_win_rate_min=0.00%..18.75%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=12,WS_BT_EVAL_ROBUST_RETURN_FAIL=12,WS_BT_EVAL_STABILITY_FAIL=12
```

C05 final decision state:

```text
C05_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C05 is not eligible for OOS because it has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. OOS remains `NOT_RUN` and must not be claimed PASS.

## PRIOR SESSION - C04 IS CANDIDATE-SELECTION REDESIGN AND IMPLEMENTATION SESSION

Session:
`WATCHLIST - C04 IS CANDIDATE-SELECTION REDESIGN AND IMPLEMENTATION SESSION`

Status:
`C04_IMPLEMENTED / C04_SEED_PASS / C04_IS_EXECUTION_PASS / C04_IS_QUALITY_FAIL / C04_REJECTED_AS_STRATEGY_CATALOG / C04_DETERMINISTIC_TWO_RUN / OOS_NOT_RUN / NOT_PRODUCTION_READY / C05_REQUIRED_IF_CONTINUED`.

Current C04 final evidence:

- C04 is a new catalog identity, not a patch to C03: `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06`, version `C04`, count `10`, hash `0ce3a313c45432c5a4d607def12b3f774988f324`;
- C04 uses only runtime-supported candidate-selection axes: score components, trend/relative-strength fields, ROC band, close-to-HH20 setup band, and existing grouping quantiles;
- C04 does not add a sector filter; sector remains diagnostic-only;
- C04 implementation validation passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04"` = PASS / `OK (14 tests, 499 assertions)`;
- full Watchlist PHPUnit passed in the current workspace: `vendor\bin\phpunit tests\Unit\Watchlist` = PASS / `OK (264 tests, 5142 assertions)`;
- C04 seed passed: `inserted_count=10`, `updated_count=0`, `existing_count=0`;
- R1/R2/C01/C02/C03 immutability was preserved during C04 seed: `r1_immutable=1`, `r2_immutable=1`, `c01_immutable=1`, `c02_immutable=1`, `c03_immutable=1`;
- C04 IS calibration run 1 and run 2 both executed and produced the same deterministic artifact hash `fe964ee879dddc8aa8a83372e8c2d05aed5e8259`;
- C04 IS quality failed deterministically: `status=C04_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=10`;
- C04 failure reason family: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_MIN_TRADES_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- C04 did not invoke OOS: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- C04 has no frozen best IS binding: `param_id_best_is=` empty and `best_is_binding_hash=` empty;
- C04 production readiness remains false: `production_ready=0`.

C04 files added or extended:

```text
app/Application/Watchlist/Services/WatchlistBacktestC04ParamGridCatalog.php
app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php
app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php
app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php
app/Application/Watchlist/Services/WatchlistPlanGroupingService.php
app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php
app/Console/Commands/Watchlist/SeedBacktestC04ParamGridCommand.php
database/seeders/Watchlist/WatchlistBacktestC04ParamGridSeeder.php
app/Console/Kernel.php
tests/Unit/Watchlist/WatchlistBacktestC04ParamGridCatalogTest.php
tests/Unit/Watchlist/WatchlistBacktestC04ParamGridParamsetFactoryTest.php
tests/Unit/Watchlist/WatchlistBacktestC04StaticGuardTest.php
docs/watchlist/audit/WS_C04_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/WS_C04_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c04-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C04_DESIGN_NOTE.md
```

Current-session validation output:

```text
php -l C04/modified Watchlist PHP files = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC04" = PASS / OK (14 tests, 499 assertions) / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (264 tests, 5142 assertions) / exit code 0
php artisan watchlist:backtest-c04-param-grid-seed = PASS / catalog_count=10 / inserted_count=10 / updated_count=0 / existing_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / c02_immutable=1 / c03_immutable=1 / oos_executed=0 / production_ready=0
C04 IS calibration run 1 = C04_GRID_FAILED_IS_QUALITY / WS_BT_C04_NO_VALID_IS_CANDIDATE / valid=0 / failed=10 / artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259 / OOS guards clean / production_ready=0
C04 IS calibration run 2 = C04_GRID_FAILED_IS_QUALITY / WS_BT_C04_NO_VALID_IS_CANDIDATE / valid=0 / failed=10 / artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259 / OOS guards clean / production_ready=0
```

C04 IS calibration deterministic markers:

```text
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
strict_is_boundary_all_evaluations=1
artifact_hash_run_1=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
artifact_hash_run_2=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
```

C04 final decision state:

```text
C04_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C04 is not eligible for OOS because it has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. OOS remains `NOT_RUN` and must not be claimed PASS.

C04 forensic summary:

```text
picks_count=82..176
median_ret_net_top=-1.2712%..-0.0501%
p25_ret_net_top=-3.8881%..-3.0868%
month_win_rate_min=0.00%..0.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=10,WS_BT_EVAL_MIN_TRADES_FAIL=7,WS_BT_EVAL_ROBUST_RETURN_FAIL=10,WS_BT_EVAL_STABILITY_FAIL=10
```

Next required work if continued:

- C05 must be a new catalog identity, not a mutation of C04;
- C05 must preserve R1/R2/C01/C02/C03/C04 immutability;
- C05 must not loosen canonical IS gates or add unsupported sector filters;
- C05 should keep C04's useful average/p25 improvement direction while restoring meaningful sample size and directly addressing monthly stability.

## PRIOR SESSION - C03 OPERATOR VALIDATION AND IS QUALITY FORENSIC FINALIZATION SESSION

Session:
`WATCHLIST - C03 OPERATOR VALIDATION AND IS QUALITY FORENSIC FINALIZATION SESSION`

Status:
`C03_IMPLEMENTED / C03_OPERATOR_VALIDATION_PASS / C03_SEED_PASS / C03_IS_EXECUTION_PASS / C03_IS_QUALITY_FAIL / C03_REJECTED_AS_STRATEGY_CATALOG / C03_DETERMINISTIC_TWO_RUN / OOS_NOT_RUN / NOT_PRODUCTION_READY / C04_REQUIRED`.

Current C03 final evidence:

- C03 is a new catalog identity, not a patch to C02: `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06`, version `C03`, count `10`, hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`;
- C03 implementation unit/static validation passed in the operator runtime: `vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC03"` = PASS / `OK (12 tests, 461 assertions)`;
- full Watchlist PHPUnit passed in the operator runtime: `vendor\bin\phpunit tests\Unit\Watchlist` = PASS / `OK (250 tests, 4643 assertions)`;
- C03 seed passed: `inserted_count=10`, `updated_count=0`, `existing_count=0`;
- R1/R2/C01/C02 immutability was preserved during C03 seed: `r1_immutable=1`, `r2_immutable=1`, `c01_immutable=1`, `c02_immutable=1`;
- C03 IS calibration run 1 and run 2 both executed in the operator runtime and produced the same deterministic artifact hash `649e8fead0c57262307f749a4776f053f5ccd0f8`;
- C03 IS quality failed deterministically: `status=C03_GRID_FAILED_IS_QUALITY`, `reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE`, `is_valid_param_count=0`, `is_failed_param_count=10`;
- C03 failure reason family was unchanged from C02 at aggregate command-output level: `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- C03 did not invoke OOS: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- C03 has no frozen best IS binding: `param_id_best_is=` empty and `best_is_binding_hash=` empty;
- C03 production readiness remains false: `production_ready=0`.

C03 files added or extended:

```text
app/Application/Watchlist/Services/WatchlistBacktestC03ParamGridCatalog.php
app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php
app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php
app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php
app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php
app/Console/Commands/Watchlist/SeedBacktestC03ParamGridCommand.php
database/seeders/Watchlist/WatchlistBacktestC03ParamGridSeeder.php
app/Console/Kernel.php
tests/Unit/Watchlist/WatchlistBacktestC03ParamGridCatalogTest.php
tests/Unit/Watchlist/WatchlistBacktestC03ParamGridParamsetFactoryTest.php
tests/Unit/Watchlist/WatchlistBacktestC03StaticGuardTest.php
docs/watchlist/audit/WS_C03_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/WS_C03_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c03-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C03_DESIGN_NOTE.md
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C04_DESIGN_INPUT_NOTE.md
```

Authoring-environment validation that was performed before operator validation:

```text
php -l C03/modified Watchlist PHP files = PASS
php /tmp/c03_smoke.php = PASS / exit code 0 / catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06 / catalog_version=C03 / catalog_count=10 / catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800 / factory_rows=10
```

Operator-provided validation output on supported project environment:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC03" = PASS / OK (12 tests, 461 assertions) / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (250 tests, 4643 assertions) / exit code 0
php artisan watchlist:backtest-c03-param-grid-seed = PASS / catalog_count=10 / inserted_count=10 / updated_count=0 / existing_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / c02_immutable=1 / oos_executed=0 / production_ready=0
C03 IS calibration run 1 = C03_GRID_FAILED_IS_QUALITY / WS_BT_C03_NO_VALID_IS_CANDIDATE / valid=0 / failed=10 / artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8 / OOS guards clean / production_ready=0
C03 IS calibration run 2 = C03_GRID_FAILED_IS_QUALITY / WS_BT_C03_NO_VALID_IS_CANDIDATE / valid=0 / failed=10 / artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8 / OOS guards clean / production_ready=0
```

C03 seed identity snapshot:

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
catalog_version=C03
catalog_count=10
catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r2_catalog_count=12
r2_catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
c01_catalog_count=8
c01_catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
c02_catalog_count=8
c02_catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
```

C03 IS calibration deterministic markers:

```text
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
strict_is_boundary_all_evaluations=1
artifact_hash_run_1=649e8fead0c57262307f749a4776f053f5ccd0f8
artifact_hash_run_2=649e8fead0c57262307f749a4776f053f5ccd0f8
```

C03 final decision state:

```text
C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C03 is not eligible for OOS because it has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. OOS remains `NOT_RUN` and must not be claimed PASS.

Forensic artifact detail:

```text
storage/app/watchlist/backtest/c03-is-run-1.json and c03-is-run-2.json are available in the current workspace. Per-row metrics were extracted into docs/watchlist/audit/_artifacts/c03-forensic-summary.csv and storage/app/watchlist/backtest/c03-forensic-summary.csv.
```

Next required work:

- C04 must be a new catalog identity, not a mutation of C03;
- C04 must be based on C02 + C03 failure evidence;
- C04 must change the candidate-selection axis and not merely tighten C03 numeric values;
- C04 must not loosen canonical IS quality gates to make weak candidates pass;
- C04 must not introduce unsupported sector filters;
- C04 remains IS-only until it produces at least one valid IS candidate with a non-empty best IS binding.

## PRIOR SESSION — C02 DOWNSIDE STABILITY OPERATOR FORENSIC FINALIZATION SESSION

Session:
`WATCHLIST - C02 DOWNSIDE STABILITY OPERATOR FORENSIC FINALIZATION SESSION`

Status:
`C02_IMPLEMENTATION_PASS / C02_OPERATOR_VALIDATION_PASS / C02_IS_EXECUTION_PASS / C02_IS_QUALITY_FAIL / C02_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY / C03_REQUIRED`.

Current C02 final evidence:

- source ZIP/workspace evidence was read before C02 implementation; C02 was derived from current C01 drilldown evidence, not from a prior-session assumption;
- R1 remains immutable historical evidence: `WS_BT_GRID_BOOTSTRAP_2026_06`, version `R1`, count `24`, hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c`;
- R2 remains immutable historical evidence: `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06`, version `R2`, count `12`, hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5`;
- C01 remains immutable failed-IS evidence: `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06`, version `C01`, count `8`, hash `604ac98f6f193a4c317d4f25582deada84682846`;
- C02 is implemented as `WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06`, version `C02`, count `8`, hash `7287c438e15bd03d6beb4796e4d5159ecd8ed59a`;
- C02 uses C01 runtime drilldown review focus: anti-chase / moderate-liquidity-volume / near-breakout / sector-aware stability;
- C02 does not add unsupported sector filters; sector evidence is retained as diagnostic-only existing-axis proxy with `sector_filter_used=false`;
- C02 seed command preserves R1/R2/C01 immutability markers;
- C02 IS calibration remains IS-only and did not read or mutate OOS;
- C02 final forensic result is recorded in `docs/watchlist/audit/WS_C02_OPERATOR_FORENSIC_FINAL_RESULT.md`.

Authoring-environment validation actually performed before operator validation:

```text
php -l C02 PHP files and modified Watchlist PHP files = PASS
php /tmp/c02_smoke.php = PASS / exit code 0 / catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a / factory_rows=8
```

Authoring-environment validation blockers:

```text
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC02" = BLOCKED / exit code 1 / missing extensions: dom, mbstring, xml, xmlwriter
php artisan list = BLOCKED / exit code 2 / ENV_UNSUPPORTED_PHP_VERSION / current PHP 8.4.16, project requires PHP >= 7.3 and < 8.4
```

Operator-provided validation output on supported project environment:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02" = PASS / OK (12 tests, 391 assertions) / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (238 tests, 4182 assertions) / exit code 0
php artisan watchlist:backtest-c02-param-grid-seed = PASS / catalog_count=8 / inserted_count=8 / updated_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / oos_executed=0 / production_ready=0
```

Post-docs validation evidence supplied after C02 final documentation update:

```text
scope=DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02" = PASS / OK (12 tests, 391 assertions) / Time 00:01.281 / Memory 14.00 MB / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (238 tests, 4182 assertions) / Time 00:04.431 / Memory 24.00 MB / exit code 0
post_docs_validation_verdict=PASS
```

This post-docs validation confirms the C02 final documentation/forensic CSV sync did not break C02 static guards or the full Watchlist unit suite. It is not a new seed, not a new calibration, not OOS proof, and not production-readiness evidence.

Operator-provided C02 IS calibration output:

```text
run_1.status=C02_GRID_FAILED_IS_QUALITY
run_1.reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
run_1.catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
run_1.catalog_version=C02
run_1.catalog_count=8
run_1.catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
run_1.is_from=2023-01-02
run_1.is_to=2025-05-21
run_1.is_trading_date_count=562
run_1.is_valid_param_count=0
run_1.is_failed_param_count=8
run_1.is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
run_1.param_id_best_is=<empty>
run_1.best_is_binding_hash=<empty>
run_1.strict_is_boundary_all_evaluations=1
run_1.oos_service_invoked=0
run_1.oos_repository_invoked=0
run_1.oos_table_unchanged=1
run_1.oos_executed=0
run_1.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
run_1.production_ready=0

run_2.status=C02_GRID_FAILED_IS_QUALITY
run_2.reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
run_2.is_valid_param_count=0
run_2.is_failed_param_count=8
run_2.artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
run_2.production_ready=0
```

C02 forensic final summary:

- artifact root keys were verified: `all_evaluations`, `best_is_binding`, `catalog_manifest`, `gate_summary`, `is_window_manifest`, `meta`, `no_oos_read_proof`, `validation`, and related proof sections;
- artifact version is `WATCHLIST_C02_IS_CALIBRATION_V1`;
- `valid_count=0`, `failed_count=8`, and `best_is_binding_empty=true`;
- `failure_reason_distribution` is `WS_BT_EVAL_DOWNSIDE_FAIL=8`, `WS_BT_EVAL_ROBUST_RETURN_FAIL=8`, `WS_BT_EVAL_STABILITY_FAIL=8`;
- every C02 row failed all three quality families;
- `minimum_coverage=true` and `minimum_trade_count=true`, so C02 failed because strategy quality was poor, not because sample/data coverage was insufficient;
- C02 had `days_covered=506..508`, `picks_count=1360..1435`, `win_rate_top=39.44%..41.82%`, negative median return on all rows, `p25_ret_net_top=-4.97%..-5.59%`, `month_win_rate_min=14.03%..23.21%`, and `period_fail_count=18..22` of `27`;
- best average-return reference row was `param_id=51`, `row_code=06_BROAD_SAMPLE_NEAR_BREAKOUT`, with `avg_ret_net_top=0.180984%`, but it still failed median return, downside, and stability gates;
- best stability-proxy reference row was `param_id=52`, `row_code=07_STABILITY_PROXY_SECTOR_REVIEW`, with `month_win_rate_min=23.214286%`, still far below the `45%` floor and still failed return/downside gates.

C02 OOS-proof eligibility:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF — C02 has no valid IS param, no best IS binding, and no best IS binding hash.
```

Promotion eligibility:

```text
NOT_ELIGIBLE — C02 strategy quality failed; OOS proof is missing; production_ready remains false.
```

Required next work:

- preserve R1/R2/C01/C02 as immutable historical evidence;
- do not mutate C02 to force a pass;
- do not run OOS for C02;
- design a new `C03` catalog from C02 forensic metrics;
- C03 must change candidate filtering/parameter design, not merely loosen canonical gates;
- keep file-16 canonical gates unchanged unless a separate owner-approved contract session explicitly changes those gates;
- continue to treat OOS and production readiness as unavailable until a future catalog produces a valid frozen IS binding.

## PRIOR SESSION — C01 DIAGNOSTIC PAYLOAD EXPANSION

Session:
`WATCHLIST - C01 IS FAILURE DRILLDOWN PAYLOAD EXPANSION SESSION`

Status:
`DONE for C01 IS failure drilldown diagnostic runtime scope / LOCAL_C01_IS_FAILURE_DRILLDOWN_EXECUTED / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Current C01 IS failure drilldown evidence:

- source ZIP/workspace evidence was read; no assumption from prior sessions is used without a current file;
- R1 remains immutable historical evidence: `WS_BT_GRID_BOOTSTRAP_2026_06`, version `R1`, count `24`, hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c`;
- R2 remains immutable historical evidence: `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06`, version `R2`, count `12`, hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5`, artifact hash `8a8521fc9a3726d90f2b77506532a1e5392def8b`;
- C01 remains immutable failed-IS evidence: `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06`, version `C01`, count `8`, hash `604ac98f6f193a4c317d4f25582deada84682846`;
- C01 two-run artifacts remain deterministic in this workspace: file SHA1 run 1 `04f6c664a0c9006c16542a8380034a0a633041dc`, file SHA1 run 2 `04f6c664a0c9006c16542a8380034a0a633041dc`, artifact hash `c8505ce5a9045629234a685984d9138b3990c775`;
- C01 has `is_valid_param_count=0`, `is_failed_param_count=8`, and no best IS binding;
- expanded the IS-only diagnostic command/service `watchlist:backtest-is-diagnose` to generate a deeper file artifact without OOS service/repository dependencies;
- diagnostic artifact surface is file-only and now includes per-param, gate-gap, ticker/month/date, setup/ATR/score, breakout/momentum/volume/liquidity/sector, score-component, runtime-consumed parameter, runtime field availability, data-quality, and no-OOS leakage sections;
- C01 drilldown run 1 and run 2 were generated locally with exit code `0`;
- C01 drilldown file SHA1 run 1 `a34f6efaca2fdd16a052637a5e455013b60244cd`;
- C01 drilldown file SHA1 run 2 `a34f6efaca2fdd16a052637a5e455013b60244cd`;
- C01 drilldown canonical artifact hash run 1 `1212405907b33c98b787f473af07472fa74b2508`;
- C01 drilldown canonical artifact hash run 2 `1212405907b33c98b787f473af07472fa74b2508`;
- C01 drilldown `is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753`;
- runtime trade/evaluation payload now exports `close_to_hh20_pct`, `roc20`, `vol_ratio`, `dv20_idr`, `sector_code`, and score components from existing market-data/scoring/PLAN evidence into strategy trades, so the breakout/momentum/volume/liquidity/sector/score-component diagnostic buckets are `DERIVED_FROM_RUNTIME_EVIDENCE`;
- derived diagnostic review snapshot is recorded in `_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md`; candidate focus for a future review was anti-chase / moderate-liquidity-volume / near-breakout / sector-aware stability; at that historical session boundary C02 remained `NOT_DESIGNED`, and this is superseded by the current C02 final result above;
- no C02 or new-focus catalog is created in this session; the newly derived diagnostic buckets are evidence for review, not promotion or OOS proof;
- OOS was not run or read; promotion remains impossible.

Local validation actually performed in this environment:

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php = PASS
php -l app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownServiceTest.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownStaticGuardTest.php = PASS
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestIsFailureDrilldown" = PASS / 4 tests / 65 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestC01" = PASS / 12 tests / 381 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestIsCalibration" = PASS / 3 tests / 26 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestMetricsServiceTest" = PASS / 15 tests / 113 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestPublishedPrice" = PASS / 18 tests / 177 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestOos" = PASS / 24 tests / 228 assertions
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktest" = PASS / 134 tests / 2903 assertions
vendor/bin/phpunit tests/Unit/Watchlist = PASS / 226 tests / 3791 assertions
vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataPublishedEodSeries" = PASS / 7 tests / 37 assertions
vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataTradingCalendar" = PASS / 4 tests / 16 assertions
vendor/bin/phpunit tests/Unit/MarketData --filter "MarketDataWatchlistReadModelTest" = PASS / 3 tests / 41 assertions
php artisan watchlist:backtest-is-diagnose run 1 = PASS / exit code 0 / status=DONE
php artisan watchlist:backtest-is-diagnose run 2 = PASS / exit code 0 / status=DONE
```

No migration, seed, OOS proof, calibration rerun, database write proof, or production-readiness proof was performed in this session.

Historical baselines remain preserved and are not downgraded:

- `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`;
- `FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`;
- `LOCAL_C01_IS_CALIBRATION_EXECUTED / C01_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`;
- post-gap-fix local validation is `WatchlistBacktestC01` 12/381, `WatchlistBacktest` 134/2903, and full Watchlist 226/3791; counts increased because C01 drilldown and feature-evidence tests were added.

OOS-proof eligibility:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
```

Promotion eligibility:

```text
NOT_ELIGIBLE — OOS proof missing
```

Superseded prior one-run note next session label:

`WATCHLIST — C01 IS FAILURE DRILLDOWN OPERATOR RUNTIME EXECUTION SESSION`

Current required next session:

`WATCHLIST - C01 DERIVED DIAGNOSTIC REVIEW BEFORE NEXT CATALOG SESSION`

Required next-session boundary:

- keep C01 failed-IS interpretation unchanged unless new runtime evidence exists;
- do not run OOS;
- do not mutate R1, R2, or C01;
- do not lower file-16 acceptance gates;
- do not create a best-of-failed binding;
- review the derived breakout/momentum/volume/liquidity/sector/score-component buckets before designing any next semantic catalog;
- do not create C02 unless feature-level root-cause focus is explicitly selected from the derived runtime evidence;
- status starts as `NOT_PRODUCTION_READY` and promotion remains `NOT_ELIGIBLE — OOS proof missing`.

## Source of Truth ZIP

- Source ZIP: `tradeaxis-api.zip`
- Session date: `2026-06-11`
- Latest local validation date: `2026-06-11`
- Scope classification: C01 IS failure drilldown payload expansion completed at code, unit/static, and local IS-only runtime diagnostic scope; OOS remains unread and production readiness is not claimed.

## Current Implementation Baseline

| Area | Status | Notes |
|---|---|---|
| Current status | `DONE for downside/stability C01 calibration execution infrastructure / LOCAL_C01_IS_CALIBRATION_EXECUTED / C01_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY` | C01 seed and two-run IS executed; infrastructure is deterministic, but all C01 rows failed canonical IS quality gates. |
| Main feature code | `DONE for C01 implementation and IS execution infrastructure` | Explicit C01 catalog, repository allowlist, paramset projection, seed command/seeder, and IS artifact labels are implemented without mutating R1/R2. |
| Runtime API | `NOT_STARTED` | No API endpoint created, by scope. |
| Artisan command surface | `C01 SEED AND IS COMMANDS EXECUTED` | `watchlist:backtest-c01-param-grid-seed` passed; `watchlist:backtest-is-calibrate` ran twice for C01 and returned failed-quality evidence. |
| Database schema | `MIGRATION APPLIED` | Catalog identity, R2 fields, and catalog-aware eval identity are deployed; R1/R2 coexistence is proven. |
| Backtest engine | `R2 STRICT-IS PATH EXECUTED; C01 STRICT-IS PATH EXECUTED` | Hard IS boundary stayed `2023-01-02..2025-05-21`; C01 max requested date stayed `2025-05-21`. |
| Recommendation engine | `DONE for Phase 5 unit/static scope` | Recommendation remains derived only from PLAN; calibration/OOS does not mutate recommendation membership. |
| PLAN grouping engine | `DONE for Phase 4 scope + deterministic BT quantile support` | Official grid quantiles are deterministic and runtime-tested. |
| Scoring engine | `R2 ENTRY-QUALITY AXES EXECUTED / QUALITY FAIL; C01 EXECUTED / QUALITY FAIL` | C01 reuses registry-owned consumed axes, but 0 of 8 rows passed canonical IS gates. |
| Market-data consumer read model | `DONE for published-price runtime scope + R2 STRICT-IS READ PROOF` | R2 did not read after the explicit IS boundary and did not invoke OOS services/repositories. |
| Candidate universe / liquidity-risk gates | `R2 MAPPING AND INVARIANTS EXECUTED` | Runtime consumers and cross-field guards are verified; no valid R2 candidate survived all gates. |
| Test coverage | `PASS` | R2 regression suites and full Watchlist suite pass: full Watchlist `209 tests / 3330 assertions`. |
| Artifact/log output | `R2 IS ARTIFACT PRODUCED; C01 IS ARTIFACT PRODUCED` | Two R2 IS artifacts produced identical hash `8a8521fc9a3726d90f2b77506532a1e5392def8b`; two C01 IS artifacts produced identical hash `c8505ce5a9045629234a685984d9138b3990c775`. |
| Production readiness | `NOT_READY` | R2 and C01 have no valid IS parameter; OOS proof and promotion remain impossible. |

## Existing Docs Discovered

The ZIP already contains a substantial watchlist documentation baseline:

- root docs: `docs/watchlist/README.md`, `docs/watchlist/LAYER_ACTIVATION_RULE.md`;
- root system policy: `docs/watchlist/system/policy.md`, `docs/watchlist/system/README.md`;
- audit guardrails: `docs/watchlist/audit/README.md`, `WATCHLIST_AUDIT_FOUNDATION.md`, `WATCHLIST_SCOPE_LOCK.md`, `WATCHLIST_OWNER_MATRIX.md`, `WATCHLIST_AUDIT_CHECKLIST_FINAL.md`, `WATCHLIST_AUDIT_PROMPT_STANDARD.md`, `WATCHLIST_CHANGE_IMPACT_MATRIX.md`;
- implementation audit guardrails: `docs/watchlist/audit/implementation/**`;
- Weekly Swing policy docs: `docs/watchlist/system/policies/weekly_swing/**`;
- shared policy docs: `docs/watchlist/system/policies/_shared/**`;
- implementation guidance: `docs/watchlist/system/implementation/weekly_swing/**`;
- support docs/artifacts: `_refs`, `examples`, `fixtures`, `db`, SQL files, JSON fixtures.

## Owner Hierarchy Summary

The active owner hierarchy for watchlist is:

1. `docs/watchlist/README.md` — root overview and navigation.
2. `docs/watchlist/system/policy.md` — highest behavioral/governance owner for watchlist.
3. `docs/watchlist/LAYER_ACTIVATION_RULE.md` — layer activation and audit classification rule.
4. `docs/watchlist/system/policies/weekly_swing/**` — domain policy owner for active Weekly Swing strategy.
5. `docs/watchlist/system/implementation/weekly_swing/**` — implementation translation only, not business owner.
6. `docs/watchlist/audit/**` — audit governance, status, checklist, prompt, and tracker.
7. `docs/watchlist/audit/implementation/**` — implementation audit guardrail.
8. `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` — actual Lumen implementation progress and evidence tracker.
9. `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` — contract lock/status tracker.
10. `docs/watchlist/system/policies/weekly_swing/db/**` — persistence/schema support, not owner of business rules.
11. `docs/watchlist/system/policies/weekly_swing/_refs/**`, `examples/**`, `fixtures/**` — support artifacts only.

Rules:

- Audit docs must not replace policy owner docs.
- `docs/watchlist/system/policy.md` remains the root behavioral owner.
- `LUMEN_IMPLEMENTATION_STATUS.md` records progress only.
- `LUMEN_CONTRACT_TRACKER.md` tracks contracts derived from system/policy docs and valid upstream market-data contracts.

## Market-Data Dependency

Watchlist depends on market-data as the official upstream data source.

Watchlist must consume:

- sealed publication;
- `SUCCESS` run;
- `READABLE` publication;
- coverage `PASS`;
- valid current publication pointer;
- valid publication/run mirror;
- valid indicator rows;
- valid eligibility rows.

Watchlist must not consume:

- raw provider response;
- raw staging table;
- unsealed `eod_bars`;
- unsealed `eod_indicators`;
- unsealed `eod_eligibility`;
- `MAX(trade_date)` shortcut;
- latest available row without publication pointer;
- indicator rows with required null values;
- invalid indicator rows.

Market-data production-ready does not automatically make watchlist production-ready. Watchlist must prove its own read contract, scoring contract, backtest contract, and runtime behavior.

## Created Governance Files

| File | Status | Purpose |
|---|---|---|
| `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md` | `DONE` for initial foundation | Defines update rules, status taxonomy, evidence rule, anti-overclaim, docs sync, market-data dependency, and readiness claim rules. |
| `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md` | `DONE` for initial foundation | Tracks current implementation status, evidence, validation, gaps, and roadmap. |
| `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md` | `DONE` for initial foundation | Defines baseline contracts WL-CONTRACT-001 through WL-CONTRACT-015. |
| `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php` | `DONE` for initial foundation | Guards existence and critical wording of the three governance tracker docs. |

## Phase 1 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` | `DONE` for Phase 1 | Watchlist application read model over market-data consumer surface. Fails closed when market-data is not readable and excludes invalid/incomplete candidate rows. |
| `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` | `UPDATED` | Upstream consumer row source hardened for publication/run scope, active tickers, valid indicators, non-null required fields, and eligibility. |
| `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php` | `DONE` for Phase 1 | Covers valid candidates, fail-closed market-data readiness, and invalid/incomplete row rejection. |
| `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php` | `DONE` for Phase 1 | Guards no raw/latest/MAX(date) bypass and docs sync for read model session. |

## Phase 2 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` | `DONE` for Phase 2 | Builds deterministic PLAN candidate universe from Phase 1 read-model candidates and applies WS liquidity, ATR/risk, and volume participation guards. |
| `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php` | `DONE` for Phase 2 | Covers accepted/rejected candidate paths, canonical reason priority, source fail-closed behavior, nested paramset value shape, and ATR fraction-unit rejection. |
| `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php` | `DONE` for Phase 2 | Guards no raw/latest/MAX(date) bypass, required reason codes, default paramset baseline, and docs sync for candidate universe session. |
| `tests/Support/MarketData/SeedsConsumerReadModelFixture.php` | `UPDATED` | Corrects fixture `atr14_pct` to fractional value (`0.021`) matching market-data indicator computation and WS policy units. |

## Phase 3 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistScoringService.php` | `DONE / LOCAL PASS` | Computes deterministic PLAN scoring from Phase 2 candidate universe rows only; Phase 3 baseline validation is local PASS at the start of this session. |
| `tests/Unit/Watchlist/WatchlistScoringServiceTest.php` | `DONE / LOCAL PASS` | Covers weighted score computation, exclusion, fail-closed source readiness, range clamp, ATR unit drift, deterministic tie-break, output contracts, and no recommendation/confirm/execution fields. |
| `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php` | `DONE / LOCAL PASS` | Guards scoring service boundary, no raw/latest/MAX(date) access, reason-code parity, deterministic sort keys, and docs sync. |
| `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` | `UPDATED` | Preserves scoring metrics and `ticker_id` from Phase 1 output while keeping Phase 2 gate semantics unchanged. |
| `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` | `UPDATED` | Passes through `ticker_id` when upstream provides it. |
| `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` | `UPDATED` | Adds `ticker_id` to the publication-scoped watchlist read rows for deterministic tie-break input only. |

## Phase 4 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php` | `DONE for Phase 4 unit/static scope` | Consumes Phase 3 scored output and maps valid scored candidates into deterministic PLAN groups `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`. |
| `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php` | `DONE for Phase 4 unit/static scope` | Covers deterministic grouping, fail-closed source readiness, invalid scored items, top/secondary overflow, low-score AVOID, metadata traceability, contracts, tie-breaks, dedupe, forbidden output fields, and invalid grouping paramsets. |
| `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php` | `DONE for Phase 4 unit/static scope` | Guards scoring-only consumption, no raw/latest/MAX(date) bypass, no recommendation/confirm/execution/backtest leakage, reason-code docs sync, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md` | `UPDATED` | Adds PLAN grouping reason codes as PLAN-only diagnostics/membership codes. |
| `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md` | `UPDATED` | Adds boundary reference that PLAN grouping reason codes are not final recommendation reason codes. |
| `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md` | `UPDATED` | Clarifies that `policy_version` / `schema_version` field names are contract labels, not application versioning claims. |
| `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md` | `UPDATED` | Clarifies that bootstrap labels do not use `_V1` and support fixture suffixes are artifact identifiers only. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Adds support seed rows for PLAN grouping reason-code parity. |

## Phase 5 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistRecommendationService.php` | `DONE for Phase 5 unit/static scope` | Consumes Phase 4 PLAN grouping output and builds deterministic recommendation output only from PLAN `TOP_PICKS` and `SECONDARY`. |
| `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php` | `DONE for Phase 5 unit/static scope` | Covers PLAN-only source, fail-closed source readiness, empty recommendation behavior, dynamic target cap, capital-free mode, capital-aware feasibility, deterministic tie-breaks, metadata traceability, output contracts, invalid paramsets, and confirm/execution/backtest boundary. |
| `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php` | `DONE for Phase 5 unit/static scope` | Guards PLAN grouping-only consumption, no raw/latest/MAX(date) bypass, no confirm/execution/portfolio/backtest leakage, reason-code docs sync, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Synchronizes recommendation support seed rows with owner docs for `WS_REC_BORDERLINE`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, and `WS_REC_CAPITAL_AWARE`. |


## Phase 6 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php` | `DONE for Phase 6 unit/static scope` | Consumes immutable PLAN candidate binding and recommendation membership snapshot, then adds CONFIRM overlay metadata without mutating recommendation membership, rank, score, label, or hash. |
| `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php` | `DONE for Phase 6 unit/static scope` | Covers recommended and non-recommended PLAN candidate confirm, unknown/non-PLAN diagnostics, source metadata preservation, immutability, and forbidden portfolio/execution/backtest/API/command fields. |
| `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php` | `DONE for Phase 6 unit/static scope` | Guards PLAN/recommendation-only consumption, no raw/latest/MAX(date) bypass, no allocation/execution/backtest/runtime leakage, reason-code docs sync, and Lumen audit tracker sync. |

## Phase 7 Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php` | `DONE for Phase 7 unit/static scope` | Consumes PLAN grouping, recommendation, and confirm overlay outputs through explicit replay windows; preserves no-lookahead, deterministic replay, publication-aware alignment, and explainable foundation output without runtime persistence. |
| `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php` | `DONE for Phase 7 unit/static scope` | Covers no-lookahead failure, deterministic replay, empty recommendation behavior, confirm-overlay diagnostic boundary, unknown/rejected confirm evidence, explainable output shape, and no portfolio/broker/order/runtime surface leakage. |
| `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php` | `DONE for Phase 7 unit/static scope` | Guards PLAN/recommendation/confirm-only consumption, no raw/latest/`MAX(trade_date)` bypass, no allocation/order/runtime surface, backtest reason-code traceability, artifact manifest references, and Lumen audit tracker sync. |
| `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md` | `UPDATED` | Adds CONFIRM overlay foundation reason-code owner entries. |
| `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md` | `UPDATED` | Adds CONFIRM overlay foundation reason-code semantics and immutability boundary wording. |
| `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md` | `UPDATED` | Adds boundary reference that CONFIRM reason codes are not final recommendation reason codes and cannot mutate recommendation fields. |
| `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql` | `UPDATED` | Synchronizes support seed rows for CONFIRM overlay foundation reason-code parity. |


## Runtime Artifact and Metrics Created / Updated Files

| File | Status | Purpose |
|---|---|---|
| `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Builds deterministic runtime-safe backtest artifact output from Phase 7 payload; includes official artifact manifest references, input manifest, metrics, diagnostics, validation, artifact hash, and JSON export foundation without command/API/production schema. |
| `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Builds fail-safe metrics from backtest payload and explicit published EOD price series + trading-calendar input only; emits missing price/calendar diagnostics instead of raw/latest fallback. |
| `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Covers artifact shape, deterministic hash, fail-safe metric diagnostics, source-payload preservation, boundary flags, and JSON export foundation. |
| `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Covers missing price/calendar fail-safe behavior, time-exit evaluation using explicit published input, target/stop/hold-expired counts, return metrics, and determinism. |
| `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php` | `DONE for runtime artifact and metrics foundation unit/static scope` | Guards no raw/staging/unsealed market-data bypass, no latest/`MAX(trade_date)` shortcut, no API/command/schema/execution leakage, and Lumen audit docs sync. |

Runtime artifact/metrics diagnostic codes in this session are internal backtest diagnostics, not canonical WS recommendation reason codes:

- `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`
- `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`
- `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`
- `WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED`

## Active Gaps

| Severity | Gap | Impact |
|---|---|---|
| `STRATEGY_QUALITY_BLOCKED` | C07 has `is_valid_param_count=0`, empty `param_id_best_is`, and empty `best_is_binding_hash`; scoped drilldowns for params 102 and 106 still fail robust return/downside/stability. | C07 cannot advance to OOS and must remain rejected as a strategy-quality catalog. |
| `OOS_NOT_ELIGIBLE` | OOS is intentionally not run for C07 because no valid frozen IS binding exists. | No OOS PASS, promotion review, or production-ready claim may be made. |
| `RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG` | Scoped drilldown found `corporate_action_flag` missing and only scoped two C07 rows. | C08 should not be created as a same-shape threshold retune; next work should enrich/complete runtime diagnostic payload or define a distinct strategy family/exit model. |

## First Implementation Roadmap

### Phase 0 — Governance Foundation

- Create audit governance.
- Create implementation status.
- Create contract tracker.
- Map existing docs.
- Define owner hierarchy.

Status: `DONE` for initial foundation.

### Phase 1 — Market-Data Consumer Read Model

- Read from current readable publication only.
- No raw/latest bypass.
- Validate required indicators.
- Validate eligibility.
- Add static guard tests.

Status: `DONE` for code + unit/static tests. Contracts remain `PARTIAL` until watchlist command/API runtime proof and artifact/log evidence exist.

### Phase 2 — Watchlist Candidate Universe

- Define universe rules.
- Define liquidity/risk filters.
- Define eligibility from market-data.
- Add tests.

Status: `DONE` for deterministic candidate universe + liquidity/risk/volume gate code and unit/static tests. Contracts remain `PARTIAL` until command/API runtime proof and artifact/log evidence exist.

### Phase 3 — Scoring Engine Foundation

- Define score factors.
- Define weight/paramset.
- Deterministic scoring.
- Explainable score breakdown.
- Add tests.

Status: `DONE / LOCAL PASS` for Phase 3 unit/static scope. Contracts remain not `LOCKED` until watchlist command/API runtime proof and artifact/log evidence exist.

### Phase 4 — PLAN Grouping + TOP_PICKS/SECONDARY

- Consume Phase 3 scored output.
- Produce PLAN group semantics `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and `AVOID`.
- Apply deterministic sort, threshold, limit, and dedupe contracts.
- Preserve source scoring metadata and paramset traceability.
- Add tests.

Status: `DONE for Phase 4 unit/static scope`. This is not final recommendation, confirm, API/command runtime, persistence runtime, or production readiness.

### Phase 5 — Final Recommendation Layer Foundation

- Consume Phase 4 PLAN grouping output.
- Produce `meta`, `items`, and `summary` recommendation output.
- Select only from PLAN `TOP_PICKS` and `SECONDARY`.
- Preserve empty recommendation behavior.
- Preserve availability without CONFIRM.
- Add tests.

Status: `DONE for Phase 5 unit/static scope`. This is not confirm, API/command runtime, persistence runtime, backtest, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 6 — Confirm Overlay Foundation

- Bind CONFIRM eligibility to candidate PLAN.
- Allow recommended and non-recommended PLAN candidates to confirm.
- Preserve recommendation immutability.
- Add tests.

Status: `DONE for Phase 6 unit/static scope`. Confirm overlay implementation and static/unit coverage are covered by the local full watchlist PHPUnit proof. This is not API/command runtime, persistence runtime, backtest runtime, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 7 — Backtest Strategy Engine

- Consume immutable PLAN grouping output, recommendation output, and confirm overlay output.
- Use explicit replay windows.
- Preserve no-lookahead by rejecting future-effective source outputs.
- Preserve deterministic replay ordering.
- Produce explainable foundation output with diagnostics and official artifact-manifest references.
- Add unit/static tests.

Status: `DONE for Phase 7 unit/static scope`. Service, tests, static guard, docs sync, PHP lint, and local PHPUnit proof exist. This is not API/command runtime, persistence runtime, completed pricing metric engine, artifact persistence, portfolio allocation, broker instruction, execution, or production readiness.

### Phase 8 — Portfolio-Aware Integration

- Current holding awareness.
- Position sizing guidance.
- Risk exposure.
- No execution automation unless explicitly designed.

Status: `NOT_STARTED`.

### Phase 9 — API/Command Surface

- Published-price Artisan proof command implemented with explicit `--from`, `--to`, and `--output`.
- No API endpoint and no scheduler.
- Deterministic JSON output contract exists at service level.
- Official Artisan/database evidence remains blocked in this sandbox.

Status: `PARTIAL / RUNTIME_BLOCKED`.

### Phase 10 — Production Readiness Audit

- Historical full PHPUnit baseline preserved; current patch PHPUnit still required under supported PHP.
- Grouped static validation passes.
- Controlled service/read-surface artifact proof passes.
- Official runtime command/database proof, OOS proof, and production operating proof remain missing.
- Docs sync is current for this patch.

Status: `IN_PROGRESS / NOT_READY`.

## Evidence Log

### 2026-05-28 — WATCHLIST — AUDIT GOVERNANCE + LUMEN TRACKER FOUNDATION

Status: `DONE` for governance foundation.

Evidence:

- New governance/tracker files created.
- Existing audit README and owner matrix synchronized with new tracker files.
- Lightweight docs static guard added.
- Operator local validation passed: `WatchlistAuditGovernanceStaticGuardTest` — 5 tests, 44 assertions.
- No scoring/recommendation/backtest/API/command implementation created.

### 2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION

Status: `DONE` for Phase 1 read model scope; `PARTIAL` for readiness-critical contracts.

Evidence:

- `WatchlistMarketDataConsumerReadService` created under `app/Application/Watchlist/Services`.
- Watchlist service consumes `MarketDataWatchlistReadService` instead of reading DB/raw market-data directly.
- Service returns candidate universe metadata with `source_contract`, required indicator list, publication/run metadata, and reason-coded readiness.
- Service fails closed when market-data has no readable publication.
- Service rejects invalid, non-eligible, or incomplete rows even if such rows appear in an upstream payload.
- Upstream market-data watchlist repository now filters publication/run scoped rows, active ticker rows, eligible rows, `ind.is_valid = 1`, `invalid_reason_code IS NULL`, and required indicator fields non-null.
- Static guard blocks raw DB reads, raw market-data table names, latest shortcuts, and `MAX(trade_date)` patterns in watchlist application code.
- No scoring/recommendation/backtest/API/command logic was created.

### 2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION

Status: `DONE` for Phase 2 candidate universe + liquidity/risk/volume gate scope; `PARTIAL` for readiness-critical contracts.

Evidence:

- `WatchlistCandidateUniverseService` created under `app/Application/Watchlist/Services`.
- Candidate universe consumes `WatchlistMarketDataConsumerReadService` only; it does not read DB/raw market-data directly.
- Default paramset baseline follows WS active policy values: `min_dv20_idr=1000000000`, `dv20_strong_idr=5000000000`, `min_vol_ratio=1.2`, `min_atr14_pct=0.02`, `max_atr14_pct=0.12`, `atr_ideal_low=0.035`, `atr_ideal_high=0.075`.
- Guard output uses canonical WS reason codes: `WS_DATA_MISSING`, `WS_LIQ_FAIL`, `WS_ATR_LOW`, `WS_ATR_HIGH`, `WS_VOLR_FAIL`, plus informational `WS_LIQ_STRONG`, `WS_LIQ_BORDER`, `WS_RISK_IDEAL`, `WS_RISK_HIGH`, `WS_RISK_LOW`.
- Output includes production/backtest-equivalence fields: `required_ok`, `guard_ok`, `eligible_plan`, `canonical_fail_reason_code`, `missing_fields`, `gate_metrics`, and `gate_thresholds`.
- Service rejects invalid ATR paramset units above 1.0 to prevent percent-point/fraction drift.
- Static guard extends no raw/latest/MAX(date) coverage to the candidate universe service.
- No final scoring/recommendation/backtest/API/command logic was created.

### 2026-05-29 — WATCHLIST — SCORING ENGINE FOUNDATION EXECUTION SESSION

Status: `PARTIAL` until local PHPUnit confirms Phase 3 scoring unit/static guards; readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistScoringService` created under `app/Application/Watchlist/Services`.
- Scoring consumes `WatchlistCandidateUniverseService` only; it does not read DB/raw market-data directly and does not consume `WatchlistMarketDataConsumerReadService` directly.
- Output includes `source_contract`, `score_contract`, `paramset_snapshot`, `score_components`, `score_weights`, `factor_breakdown`, `reason_codes`, and deterministic `ranking_keys`.
- Component scores implemented: `score_momentum`, `score_breakout`, `score_volume`, and `score_risk`, each clamped to `0..1`.
- `score_total` uses deterministic `WEIGHTED_MEAN` with bootstrap weights: momentum `0.30`, breakout `0.30`, volume `0.20`, risk `0.20`.
- Deterministic sort keys implemented: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Scoring rejects candidates that are not `eligible_plan=true` and `guard_ok=true`, rejects missing scoring metrics, and rejects ATR unit drift where `atr14_pct > 1`.
- Candidate universe output now preserves scoring metrics from Phase 1 rows so scoring does not bypass Phase 2.
- `ticker_id` is passed through the read model/repository strictly for deterministic tie-break input.
- No recommendation membership, confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, or runtime artifact output was created.

### 2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION

Status: `DONE for Phase 4 unit/static scope`; local validation in this session passed and readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistPlanGroupingService` created under `app/Application/Watchlist/Services`.
- PLAN grouping consumes `WatchlistScoringService` only; it does not read DB/raw market-data directly and does not consume `WatchlistCandidateUniverseService` or `WatchlistMarketDataConsumerReadService` directly.
- Output includes `source_contract`, `group_contract`, `paramset_snapshot`, `groups`, `excluded`, and deterministic `summary`.
- Default bootstrap grouping contract uses `PLAN_GROUPING_DETERMINISTIC`, top-picks min score `0.70` max `5`, secondary min score `0.55` max `10`, watch-only min score `0.40` max `20`, and avoid low-score boundary `0.40`.
- PLAN groups implemented: `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID`.
- Deterministic sort keys preserved from Phase 3 scoring: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Duplicate `ticker_id` is deduplicated by deterministic best item before active PLAN group assignment.
- Scoring excluded candidates and invalid scored items do not enter active PLAN groups; they remain diagnostics via `AVOID`.
- PLAN grouping reason codes added: `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, `WS_PLAN_AVOID_EXCLUDED`.
- No final recommendation membership, recommendation label, confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, persistence runtime, or runtime artifact output was created.

### 2026-06-05 — WATCHLIST — FINAL RECOMMENDATION LAYER FOUNDATION EXECUTION SESSION

Status: `DONE for Phase 5 unit/static scope`; local validation in this session passed and readiness-critical contracts remain `PARTIAL`.

Evidence:

- `WatchlistRecommendationService` created under `app/Application/Watchlist/Services`.
- Recommendation consumes `WatchlistPlanGroupingService` only; it does not read DB/raw market-data directly and does not consume scoring, candidate-universe, or market-data read services directly.
- Output includes `meta`, `items`, and `summary`, matching the recommendation owner docs shape.
- Recommendation source universe is limited to PLAN groups `TOP_PICKS` and `SECONDARY`; `WATCH_ONLY` and diagnostics `AVOID` do not enter recommendation evaluation.
- Default recommendation contract uses `PLAN_DERIVED_DETERMINISTIC`, dynamic count mode `THRESHOLD_AND_CAP`, min recommendation score `0.70`, borderline min score `0.55`, max recommended items `3`, and deterministic sort keys `recommendation_score_desc`, `plan_rank_asc`, `plan_group_priority_asc`, and `ticker_id_asc`.
- Empty recommendation is valid and sets `empty_recommendation_flag = true` when `recommended_count = 0`, even if prioritized PLAN groups are non-empty.
- `CAPITAL_FREE` mode works without capital input.
- Limited `CAPITAL_AWARE` mode supports deterministic affordability feasibility from explicit capital input/minimum-lot values without creating portfolio allocation, suggested lots, broker instruction, or execution logic.
- Recommendation output ignores confirm-like fields if malformed upstream payloads include them and does not emit confirm state/status.
- Recommendation reason codes are explainable: `WS_REC_SELECTED`, `WS_REC_NOT_SELECTED`, `WS_REC_BORDERLINE`, `WS_REC_EMPTY_SET`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, `WS_REC_CAPITAL_AWARE`, `WS_REC_CAPITAL_INSUFFICIENT`, and `WS_REC_MIN_LOT_NOT_AFFORDABLE`.
- No confirm overlay, portfolio allocation, order instruction, execution action, backtest metric, API, command, scheduler, persistence runtime, or runtime artifact output was created.


### 2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION

Status: `DONE for Phase 6 unit/static scope` after local full watchlist PHPUnit proof; readiness-critical production contracts remain `PARTIAL` / `NOT_READY`.

Evidence:

- `WatchlistConfirmOverlayService` created and bound to immutable PLAN candidate output from `WatchlistPlanGroupingService`.
- Service uses `WatchlistRecommendationService` only as immutable recommendation membership snapshot.
- Recommended PLAN candidates can be confirmed without changing recommendation membership, rank, score, label, or hash.
- Non-recommended active PLAN candidates can be confirmed without becoming recommended.
- Unknown/non-active candidate evidence is rejected into diagnostics/excluded output.
- Service output contains `source_contract`, `confirm_contract`, `immutability_contract`, `items`, `excluded`, and `summary`.
- Static guard covers no raw market-data, no latest/`MAX(trade_date)`, and no portfolio/execution/backtest/API/command leakage.


### 2026-06-08 — WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION LOCAL VALIDATION UPDATE

Session:
`WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION`

Status: `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`.

Local validation proof:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
OK (13 tests, 152 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (104 tests, 1034 assertions)
```

Notes:

- Phase 7 is DONE for unit/static scope only.
- Empty recommendation behavior is fixed and validated: no active trades/evaluations are created and `WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION` is emitted for empty recommendation runs.
- Production readiness remains `NOT_READY` because runtime API/command, persisted artifacts/logs, production schema, completed pricing metric engine, portfolio-aware integration, and walk-forward/OOS proof do not exist yet.

## 2026-06-09 — WATCHLIST — BACKTEST RUNTIME ARTIFACT AND METRICS LOCAL VALIDATION UPDATE

Status: `DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`.

Local validation proof:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

Validation impact:

- The metrics float-output correction is validated; the time-exit metrics case now passes with the required float contract.
- All runtime artifact/metrics unit and static guard tests pass.
- The full watchlist suite increased from 104 to 116 tests and remains green.
- The upstream market-data watchlist read-model guard remains green.
- Phase 6 and Phase 7 baselines remain DONE for their unit/static scopes.
- No contract is promoted to `LOCKED`, and production readiness remains `NOT_READY`, because command/API runtime proof, production persisted artifact evidence, production schema, and walk-forward/OOS proof are still missing.

## Validation Log

Validation performed in this session and later local proof:

```text
php -l app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php
php -d zend.assertions=1 -d assert.exception=1 /tmp/confirm_smoke.php
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistConfirmOverlay"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
vendor\bin\phpunit tests\Unit\Watchlist
```

Observed validation result in the original sandbox and local runtime:

```text
php -l app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php: PASS
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php: PASS
php -l tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php: PASS
Direct confirm overlay smoke test: PASS
vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistConfirmOverlay": BLOCKED in original sandbox only
Reason: PHPUnit requires PHP extensions dom, json, libxml, mbstring, tokenizer, xml, xmlwriter; original sandbox PHP is missing dom, mbstring, xml, and xmlwriter. Local full watchlist PHPUnit proof has since passed.
```

Local PHPUnit proof upgrades Phase 7 to DONE for unit/static scope. No `LOCKED` or production-ready claim is made because runtime API/command proof and persisted artifact/log evidence are still missing.

## Latest Completed Local Validation

This section preserves the last completed local baseline before the current published-price patch. It is historical evidence and does not claim that the new tests were executed in the sandbox.

The required local validation for the current runtime artifact and metrics foundation scope is complete:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
# OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
# OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
# OK (3 tests, 41 assertions)
```

Validated outcomes:

- runtime artifact and metrics tests pass;
- full watchlist regression suite passes;
- market-data watchlist consumer read-model test remains green;
- no regression is observed in Phase 1 through Phase 7 unit/static coverage;
- documentation static guards pass as part of the full watchlist suite;
- local PHPUnit was no longer pending for that historical runtime-artifact foundation session; the current published-price patch still requires supported-environment PHPUnit execution.

## Production Readiness Status

`NOT_READY`.

Reason:

- historical Phase 6, Phase 7, runtime artifact, metrics, and published-price runtime baselines remain valid and are not downgraded;
- canonical schema, 24-row grid, execution-price semantics, PHPUnit, and full-range IS runtime are validated;
- all 24 R1 parameters failed one or more canonical IS quality gates;
- no valid `param_id_best_is` or immutable best-IS binding exists;
- OOS did not execute and no OOS acceptance artifact exists;
- no promotion was executed and promotion eligibility is `NOT_ELIGIBLE — OOS proof missing`;
- no portfolio, broker, scheduler, API, or production-execution scope was added;
- no contract is `LOCKED` and `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.

Watchlist is not production-ready.

## Next Required Sessions

Required next session:

`WATCHLIST — C03 IS QUALITY CATALOG DESIGN AND IMPLEMENTATION SESSION`

Why this is next:

- R1, R2, C01, and C02 all failed to produce a valid IS binding.
- C02 is now fully implemented and operator-validated for tests/seed/execution, but rejected as a strategy-quality catalog.
- C02 produced deterministic IS artifacts, but `valid_count=0`, `failed_count=8`, and `best_is_binding_empty=true`.
- Every C02 param failed downside, robust-return, and stability gates.
- C02 failure is not caused by insufficient coverage or insufficient trade count; `minimum_coverage=true` and `minimum_trade_count=true`.
- Post-docs validation after the C02 final documentation/forensic CSV sync passed `WatchlistBacktestC02` and the full `tests/Unit/Watchlist` suite; no runtime/catalog/seed/calibration changes were made in that documentation-only sync.
- OOS remains ineligible because there is no frozen best-IS binding.

Required target:

- update/retain C02 as immutable rejected strategy-quality evidence;
- design C03 as a new catalog identity, not a mutation of C02;
- use C02 forensic metrics to reduce weak picks and improve median return, p25 downside, and monthly stability;
- preserve R1/R2/C01/C02 identities and hashes;
- use IS data only; reserved OOS must remain unread;
- keep file-16 canonical gates unchanged unless separately owner-approved;
- do not create best-of-failed, active paramset, promotion, production-ready claim, or OOS run.

Anti-ambiguity naming rule:

```text
R1/R2 = historical aliases only.
C01 = executed historical failed-IS catalog for DOWNSIDE_STABILITY.
C02 = implemented and operator-validated but rejected failed-IS catalog for DOWNSIDE_STABILITY.
R3/R4/R5 naming = deprecated for new catalog identity.
Future same-focus catalog code = WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06.
Future changed-focus catalog code = WS_BT_GRID_<FOCUS>_C01_YYYY_MM.
Future evidence run code = WS_BT_<IS|OOS>_<FOCUS>_C##_RUN_##.
```

## Runtime Artifact and Metrics Foundation Update — 2026-06-08

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / NOT_PRODUCTION_READY`.

Evidence added:

- `WatchlistBacktestRuntimeArtifactService.php` builds deterministic runtime artifact shape with official manifest references, input manifest, metrics, diagnostics, validation, and artifact hash.
- `WatchlistBacktestMetricsService.php` builds metrics from backtest output and explicit published EOD price/calendar input only.
- Missing official price/calendar inputs fail safe with `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`, `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`, and `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`.
- Unit/static tests added for artifact service, metrics service, and static boundary guard.
- No command/API/scheduler/migration/production schema was added.
- No raw/staging/unsealed market-data reader, latest shortcut, or `MAX(trade_date)` shortcut was added.
- No portfolio allocation, position sizing final, broker instruction, order recommendation, or execution automation was added.

Validation note:

- Local PHPUnit validation is complete and PASS: `WatchlistBacktest` 25 tests / 286 assertions; full watchlist 116 tests / 1168 assertions; `MarketDataWatchlistReadModelTest` 3 tests / 41 assertions.
- The earlier metrics float-output and audit baseline-marker failures were corrected, and the complete requested validation set now passes.

Production readiness:

- Watchlist Production Ready remains `NO`.
- Contracts are not promoted to `LOCKED` because command/API runtime proof, production persisted artifact evidence, production schema, and walk-forward/OOS proof are still missing.

## Published Price Series Runtime Integration Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`PARTIAL — implementation and controlled service runtime proof complete / official Artisan and database runtime proof blocked / NOT_PRODUCTION_READY`.

Implemented files:

- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`;
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`;
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`;
- `app/Infrastructure/Persistence/MarketData/MarketCalendarRepository.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`;
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`;
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`;
- `app/Console/Kernel.php`.

Added test files:

- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`;
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`.

Modified test files:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`;
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`.

Audit docs updated:

- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`.

Runtime evidence written:

- `storage/app/watchlist/backtest/published-price-service-proof-run-1.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-run-2.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-missing-exit.json`;
- `storage/app/watchlist/backtest/published-price-service-proof-evidence.json`;
- `storage/app/watchlist/backtest/published-price-read-surface-proof-evidence.json`.

Validation evidence:

- `php -l`: PASS for 17 changed/new PHP source and test files, 0 failures;
- grouped static validation: PASS, 0 failures;
- controlled application-service runtime proof: PASS, 25 direct assertions;
- controlled market-data application read-surface proof: PASS, 21 direct assertions;
- strategy paramset snapshot regression smoke: PASS, 4 direct assertions;
- command argument fail-safe smoke without Artisan bootstrap: PASS, 4 direct assertions;
- canonical artifact hash run 1: `bb2268bbc053d7aa85fd5a400e834c519cfd3429`;
- canonical artifact hash run 2: `bb2268bbc053d7aa85fd5a400e834c519cfd3429`;
- canonical hash equality: PASS;
- file SHA-1 differs because `generated_at`, `executed_at`, and output path are intentionally non-hashed metadata;
- metric required fields are available for the controlled proof, but calibration validity is false because the proof has only one evaluated trade and does not meet file 16 gates;
- missing publication and future-effective source fail closed; missing exit OHLC remains a reason-coded skip with `ret_net = null`.

Sandbox blockers:

- `php artisan watchlist:backtest-published-price-proof --from=2026-05-19 --to=2026-05-19 --output=storage/app/watchlist/backtest/command-proof-blocked.json --overwrite` exits `2` with `ENV_UNSUPPORTED_PHP_VERSION`; project requires PHP `>= 7.3` and `< 8.4`, sandbox is PHP `8.4.16`, and no command artifact is written;
- each requested PHPUnit command exits `1` before test discovery because required extensions `dom`, `mbstring`, `xml`, and `xmlwriter` are absent; test and assertion counts are therefore unavailable for the current patch;
- official database-backed command proof therefore remains `BLOCKED`.

Historical pre-closure owner conflict — RESOLVED by the later gap-closure update:

- file 12 locks TP/SL/time-exit up to D+5;
- file 16 states fixed holding as its default evaluation model;
- the closure update explicitly aligns file 16 to file 12 rule-based TP/SL/time-exit semantics;
- metric sufficiency remains `PARTIAL` only until the current closure-patch PHPUnit and runtime rerun are completed.

Production readiness:

- `NOT_PRODUCTION_READY`;
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`;
- no contract is promoted to `LOCKED`.



## Published Price Runtime Proof and Gap Closure Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Status:
`PARTIAL — LOCAL_RUNTIME_PROOF_PASS for the operator-tested pre-closure build / zero-volume tradability and canonical metric-threshold closure implemented / current coverage-fix patch rerun required / NOT_PRODUCTION_READY`.

### Operator PHPUnit evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice"
OK (13 tests, 87 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (39 tests, 375 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (130 tests, 1257 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPublishedEodSeries"
OK (6 tests, 29 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataTradingCalendar"
OK (4 tests, 16 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

The first PublishedEodSeries attempt exposed a test-fixture error caused by inserting a historical publication row into live `eod_bars`, whose canonical key is `(trade_date, ticker_id)`. The fixture was corrected to place the non-current row in `eod_bars_history`; the rerun passed 6 tests / 29 assertions. No production reader defect was found.

### Official operator runtime evidence before closure patch

Replay window:

```text
from=2026-05-21
to=2026-05-29
replay_date_count=5
calendar_date_count=10
required_price_date_count=9
resolved_price_date_count=9
evaluated_trade_count=13
diagnostic_count=2
```

Two command runs completed with `status=PASS`. Canonical artifact hash matched:

```text
run 1 artifact_hash=03dce5cbd7176a6065dc711e0d9907a2279f9cc3
run 2 artifact_hash=03dce5cbd7176a6065dc711e0d9907a2279f9cc3
hash equality=PASS
```

File SHA-1 differed because output path and execution metadata are intentionally excluded from the canonical artifact hash. Publication evidence covered 10/10 required dates from `2026-05-21` through `2026-06-08`; each date resolved a current pointer with `SEALED`, `SUCCESS`, `READABLE`, coverage `PASS`, and current-publication identity.

The two non-fatal diagnostics were:

```text
2026-05-21 KING BT_SKIP_MISSING_OHLC_EXIT
2026-05-26 BKDP BT_SKIP_MISSING_OHLC_EXIT
```

Operator bar inspection confirmed inactive/non-trading rows: equal OHLC with `volume = 0`, followed by unavailable bars. This exposed a semantic gap: a published row is not automatically an executable backtest fill.

### Closure implemented after operator proof

- Entry and exit fills now require numeric `volume >= 1`.
- Zero-volume rows remain valid published market-data rows but are ignored for TP/SL and cannot become entry, exit, or synthetic zero-return fills.
- Added canonical diagnostics `BT_SKIP_NO_TRADABLE_ENTRY` and `BT_SKIP_NO_TRADABLE_EXIT`.
- BKDP-like D+1 zero-volume cases now fail at entry; zero-volume days inside a KING-like exit horizon are recorded in `ignored_non_tradable_exit_dates`.
- Runtime paramset snapshot now carries all required `eval` thresholds.
- Canonical bootstrap floors are `min_trades = 120`, `min_trades_oos = 40`, downside `-0.03`, monthly win-rate `0.45`, and monthly average `-0.01`.
- `min_days_covered = 0` is only a dynamic sentinel; metrics resolves it to `ceil(70% * total_trading_days_in_window)` and writes both configured and effective thresholds.
- Runtime export fails closed with `WS_BT_EVAL_METRICS_MISSING` when required thresholds remain unresolved.
- File 16 is synchronized with file 12: active execution is TP/SL with deterministic stop priority and time-exit at a maximum five-trading-day horizon; fixed holding is not the active default.

Controlled closure-patch validation confirms:

```text
php -l: PASS for all 9 closure-patch PHP source/test files
static parity/safety validation: PASS, 20 assertions
gap-closure metrics harness: PASS, 12 assertions
controlled runtime determinism harness: PASS, 10 assertions
canonical hash run 1: e2d725378e6df67ffa579017fdbb2399e8bdc322
canonical hash run 2: e2d725378e6df67ffa579017fdbb2399e8bdc322
hash equality: PASS
file SHA equality: false, expected because output path/execution metadata are non-hashed

default_thresholds_resolved=true
min_trades=120
min_days_covered effective=ceil(70% * replay days)
zero-volume entry => BT_SKIP_NO_TRADABLE_ENTRY, ret_net=null
zero-volume final exit => BT_SKIP_NO_TRADABLE_EXIT, ret_net=null
normal positive-volume trade => evaluated
```

### Dynamic coverage correction after first closure rerun

The first operator PHPUnit rerun correctly failed the sentinel test because the implementation used the same requested replay-date count as both observed `days_covered` and total-window denominator. That made `minimum_coverage` always true whenever the explicit window existed.

Corrected runtime semantics:

```text
total_trading_days_in_window = count(explicit replay trading dates)
days_covered = distinct replay dates with >= 1 metrics_ready trade
             + explicit WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID dates
all candidates skipped on a date = not covered
```

Controlled proof after the correction:

```text
1 covered day / 10 requested days => effective floor 7 => minimum_coverage=false
7 covered days / 10 requested days => effective floor 7 => minimum_coverage=true
valid empty-recommendation day => counted once as covered
coverage pass still does not bypass min_trades/return/stability gates
```

The corrected service and regression tests require operator PHPUnit and two-run command rerun before the current patch can be promoted.

### Current boundary

The operator runtime evidence above proves the pre-closure implementation. Because execution semantics and hashed paramset metadata changed afterward, the current closure patch must be rerun locally. Its artifact hash is expected to differ from `03dce5...`, but two identical closure-patch runs must still match each other.

Production readiness remains `NOT_PRODUCTION_READY`. No contract is promoted to `LOCKED`; walk-forward/OOS and production operating proof remain outstanding.

## Published Price Runtime Proof Final Closure Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Final status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

### Final operator PHPUnit evidence

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestPublishedPrice"
OK (17 tests, 146 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestMetricsServiceTest"
OK (8 tests, 63 assertions)

vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (48 tests, 497 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (139 tests, 1379 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataPublishedEodSeries"
OK (6 tests, 29 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataTradingCalendar"
OK (4 tests, 16 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

### Final official command proof

```text
command: watchlist:backtest-published-price-proof
replay_from: 2026-05-21
replay_to: 2026-05-29
run_count: 2
status: PASS for both runs
replay_date_count: 5
calendar_date_count: 10
required_price_date_count: 9
resolved_price_date_count: 9
evaluated_trade_count: 13
diagnostic_count: 2
metric_required_fields_available: 1
metric_thresholds_resolved: 1
metric_min_trades: 120
metric_min_days_covered: 4
metric_coverage_threshold_rule: CEIL_70_PERCENT_OF_TOTAL_TRADING_DAYS
days_covered: 5
total_trading_days_in_window: 5
minimum_coverage: true
metric_calibration_valid: 0
canonical_artifact_hash_run_1: 0eaa353d20df901c4f372c0000951408578bf302
canonical_artifact_hash_run_2: 0eaa353d20df901c4f372c0000951408578bf302
canonical_hash_equality: true
production_ready: 0
```

`metric_calibration_valid=0` is expected and correct for this smoke window because 13 evaluated trades do not meet `min_trades=120`. Threshold resolution, coverage calculation, runtime orchestration, and deterministic artifact generation passed.

### Final zero-volume diagnostics

- KING (`trade_date=2026-05-21`) emitted `BT_SKIP_MISSING_OHLC_EXIT`; zero-volume dates `2026-05-25`, `2026-05-26`, and `2026-05-29` were recorded in `ignored_non_tradable_exit_dates`, no synthetic exit was created, and no zero return was fabricated.
- BKDP (`trade_date=2026-05-26`) emitted `BT_SKIP_NO_TRADABLE_ENTRY` with `entry_volume=0`; the trade was never treated as entered.

### Final scope conclusion

- Official trading-calendar runtime read: PASS.
- Exact-date current-readable published EOD OHLCV runtime read: PASS.
- Publication lineage: PASS for 10/10 required dates through `2026-06-08`.
- Immutable strategy output before future-price evaluation: PASS.
- Zero-volume non-tradable handling: PASS.
- Metric threshold binding and dynamic coverage: PASS.
- Deterministic two-run canonical hash: PASS.
- JSON runtime evidence export: PASS.
- Portfolio/execution leakage: none introduced.
- Walk-forward/OOS proof: not started.
- Production operating proof: not available.
- Overall watchlist status remains `NOT_PRODUCTION_READY`.

Earlier references in this document to a required closure/coverage rerun are historical and are superseded by this final closure update.

Next session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`.

## Walk-Forward/OOS Implementation Unit-Static Update — 2026-06-09

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Session status:
`DONE for walk-forward/OOS implementation unit-static scope / LOCAL_SMOKE_PASS / OFFICIAL_RUNTIME_PROOF_BLOCKED / NOT_PRODUCTION_READY`.

### TRACE result

```text
official trading calendar
→ official current-readable published EOD series
→ WatchlistBacktestPublishedPriceRuntimeService
→ WatchlistBacktestStrategyService
→ WatchlistBacktestMetricsService
→ official watchlist_bt_param_grid
→ IS-only calibration and watchlist_bt_eval
→ deterministic 70/30 split
→ immutable best-IS binding
→ OOS one-param evaluation without re-tuning
→ watchlist_bt_oos_eval_ws
→ deterministic JSON evidence
```

No raw market-data reader, latest/`MAX(trade_date)` shortcut, hidden current-date default, PLAN/RECOMMENDATION/CONFIRM mutation, paramset status mutation, portfolio allocation, order, broker, scheduler, or API endpoint was introduced.

### Contract drift closed

- file 17 now locks `is_count=floor(0.70*N)` and assigns the remainder to OOS;
- file 16 and file 12 now end canonical ranking with `param_id ASC`;
- file 20 and `PROMOTE_PARAMSET.sql` now require the canonical minimum OOS count, default `40`, rather than `picks_count_oos > 0`;
- the OOS fixture now uses only owner acceptance gates;
- OOS DDL now records `is_eval_id` with a foreign key to `watchlist_bt_eval`.

### Implementation evidence

Created:

- `WatchlistBacktestOosSplitService`;
- `WatchlistBacktestIsCalibrationService`;
- `WatchlistBacktestOosProofService`;
- official param-grid, IS-evaluation, and OOS-evaluation repositories;
- `RunBacktestOosProofCommand` and Kernel registration;
- seven OOS/quantile PHPUnit test files.

Updated:

- published-price runtime with an internal explicit-window evaluation surface;
- strategy paramset propagation and canonical eval model;
- PLAN grouping with deterministic daily quantile cutoffs for official BT-grid fields;
- owner contracts, DDL, promotion SQL, fixture, and audit trackers.

### Validation evidence

```text
PHP lint: PASS for every changed/new PHP file
controlled OOS smoke: PASS / 35 assertions
controlled grouping quantile smoke: PASS / 6 assertions
new OOS PHPUnit source: 20 test methods / 118 assertion-expectation call sites
official Artisan attempt 1: exit 2 / unsupported PHP 8.4.16 / no artifact
official Artisan attempt 2: exit 2 / unsupported PHP 8.4.16 / no artifact
requested PHPUnit scopes: exit 1 before discovery / missing dom, mbstring, xml, xmlwriter
```

The controlled smoke proves split odd/even behavior, canonical ranking/tie-break, immutable binding, no OOS selection leakage, OOS gates, missing-metric fail-closed behavior, exact-duplicate idempotency, conflicting-duplicate rejection, no promotion mutation, and canonical hash equality across INSERTED/IDEMPOTENT persistence status. It does not replace supported-environment PHPUnit or official DB-backed command evidence.

### Runtime and promotion conclusion

```text
Official OOS runtime evidence: BLOCKED
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
OOS_ACCEPTANCE_FAIL: NOT CLAIMED (OOS did not execute)
Promotion eligibility: NOT_ELIGIBLE — OOS proof missing
Production ready: false
```

No contract is promoted to `LOCKED`.


## OOS Supported-Runtime Finding and Gap-Closure Implementation Update — 2026-06-09

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Current status:
`DONE for OOS runtime gap-closure implementation unit/static scope / OPERATOR_RERUN_REQUIRED / NOT_PRODUCTION_READY`.

### Operator evidence received before this closure patch

```text
watchlist:backtest-oos-proof command registration: PASS
WatchlistBacktestOos: 19 tests / 117 assertions / PASS
WatchlistBacktestIsCalibration: 3 tests / 17 assertions / PASS
WatchlistPlanGroupingQuantileCutoff: 1 test / 6 assertions / PASS
WatchlistBacktest: 70 tests / 631 assertions / PASS
Full Watchlist: 162 tests / 1519 assertions / PASS
MarketDataPublishedEodSeries: 6 tests / 29 assertions / PASS
MarketDataTradingCalendar: 4 tests / 16 assertions / PASS
MarketDataWatchlistReadModelTest: 3 tests / 41 assertions / PASS
```

The first long-range attempt (`2023-01-02` through `2026-05-29`) exhausted the 512 MB PHP memory limit while materializing published prices. A shorter supported-runtime attempt (`2025-01-02` through `2026-05-29`) completed the chronological split and IS calibration:

```text
IS: 2025-01-02 through 2025-12-17 / 229 trading dates
OOS: 2025-12-18 through 2026-05-29 / 99 trading dates
param_grid_count: 1
is_valid_param_count: 0
picks_count: 629
IS coverage: PASS
average return: PASS
median return: FAIL
p25 downside: FAIL
monthly win-rate floor: FAIL
monthly average floor: FAIL
OOS: not started because no valid IS binding existed
```

The existing one-row baseline was correctly rejected. No best-of-failed selection and no OOS retuning occurred.

### Confirmed implementation gaps closed by this patch

- Added a deterministic, curated, 24-row canonical WS parameter catalog and idempotent database seed command/seeder/SQL.
- Added official grid columns for `stop_atr_mult` and `min_rr`; both are bound into the runtime paramset.
- Propagated `atr14_pct` and level inputs through recommendation/backtest candidates.
- Added canonical ATR/RR stop/target fallback when PLAN has no explicit levels.
- Replaced date/ticker cartesian published-price loading with exact frozen candidate date/ticker pair reads.
- Removed per-grid temporary JSON writes during IS calibration and released iteration memory.
- Corrected runtime metadata to `PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE` / `TARGETED_DATE_TICKER_MAP`.
- Added compact deterministic worst/best trade evidence with entry/exit and publication lineage to each IS evaluation reference.
- Versioned `watchlist_bt_eval` identity with `eval_model` and `paramset_hash`, and OOS identity with `is_eval_id`, so the earlier `eval_id=1` evidence can remain while corrected semantics are rerun; no deletion or overwrite is required.
- Added migrations and synchronized SQL for fresh and existing databases.

### Current validation boundary

The code and documentation patch has local syntax/static validation in the packaging environment. Official PHPUnit and database-backed rerun for this closure patch must be executed by the operator under the supported project PHP/runtime. Historical operator PASS evidence above is preserved but does not prove the new closure patch.

Required next operator sequence:

```text
migrate schema
seed canonical grid
run OOS/backtest/full-Watchlist/MarketData PHPUnit
run one explicit OOS proof
if best IS exists and OOS executes, run the same proof a second time
compare canonical hashes and persistence ids/status
```

Current conclusion:

```text
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
OOS_ACCEPTANCE_FAIL: NOT CLAIMED for the corrected multi-grid runtime
Promotion eligibility: NOT_ELIGIBLE — corrected OOS proof missing
Production ready: false
```

Watchlist is not production-ready.

## OOS Post-Deployment Regression Root-Cause Correction — 2026-06-10

Supported operator deployment confirmed the canonical grid seed itself was healthy:

```text
catalog_count=24
inserted_count=0
updated_count=0
existing_count=24
param_grid_count=24
WatchlistBacktestParamGrid: 2 tests / 535 assertions / PASS
```

The subsequent suites exposed three source-level regressions in the uploaded source of truth:

1. `WatchlistBacktestOosStaticGuardTest` duplicated the obsolete literal `18` even though the catalog and SQL seed contain 24 rows.
2. `WatchlistBacktestStrategyService::DEFAULT_PARAMSET` had no nested risk defaults, so a standalone strategy replay emitted `stop_atr_mult=null` and `min_rr=null`.
3. `WatchlistBacktestPublishedPriceRuntimeService` passed runtime metadata into strategy replay but trusted the returned payload to echo it. Test doubles and legacy payloads could therefore omit `pricing_model` / `price_read_mode`, causing artifact drift and an undefined index.

Root corrections:

- added `WatchlistBacktestParamGridCatalog::CATALOG_COUNT=24` and made catalog/SQL static guards derive from it;
- added exact persisted-catalog validation with fail-closed code `WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH`;
- added canonical strategy defaults `risk.stop_atr_mult=1.5` and `risk.min_rr=1.5`, with nested risk resolution;
- bound published-price runtime metadata onto the returned strategy payload before the frozen strategy hash and before price reads;
- synchronized top-level/meta paramset snapshots and trade runtime metadata without fabricating missing eval thresholds;
- synchronized owner contract, implementation flow, test guidance, and audit trackers.

Packaging-environment validation:

```text
PHP lint: PASS for all changed PHP files
controlled root-cause smoke: 15 assertions / PASS
official PHPUnit: not executable in packaging environment because dom, mbstring, xml, xmlwriter are unavailable
```

Operator rerun is required. No `LOCAL_OOS_PROOF_PASS`, promotion eligibility, or production-ready claim is made.


## OOS Full-Window Operator Result and Grid Cross-Field Closure — 2026-06-10

Supported operator validation after the post-deployment correction:

```text
WatchlistBacktestParamGrid: 2 tests / 535 assertions / PASS
WatchlistBacktestOos: 24 tests / 174 assertions / PASS
WatchlistBacktestStrategy: 15 tests / 191 assertions / PASS
WatchlistBacktestPublishedPrice: 18 tests / 155 assertions / PASS
WatchlistBacktest: 79 tests / 1252 assertions / PASS
Full Watchlist: 171 tests / 2140 assertions / PASS
```

The full explicit OOS command then executed without memory exhaustion:

```text
from=2023-01-02
split IS=2023-01-02..2025-05-21 / 562 trading dates
split OOS=2025-05-22..2026-05-29 / 242 trading dates
param_grid_count=24
is_valid_param_count=0
is_failed_param_count=24
is_max_picks_count=1513
is_max_days_covered=513
reason_code=WS_BT_OOS_PROOF_MISSING
```

Static source analysis identified a technical failure mixed into the honest statistical failures: 19 strict catalog rows have `max_atr14_pct < 0.075`, while the previous row-to-paramset merge retained active `atr_ideal_high=0.075`. Candidate/scoring validation therefore rejected those rows as internally contradictory before daily replay, surfacing aggregate `WATCHLIST_BACKTEST_SOURCE_NOT_READY`.

Root correction:

- introduced `WatchlistBacktestParamGridParamsetFactory` as the single row-to-runtime-paramset boundary;
- locked deterministic companion-band projection `CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR`;
- records resolved values in `bt_grid_resolution`;
- guarantees `min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct` for all 24 rows;
- added catalog-wide PHPUnit/static guards.

This correction removes a technical false rejection. It does not weaken metric gates and does not guarantee that any parameter will pass IS. The operator must rerun the same full command. If all 24 rows execute but still fail robust-return/downside/stability gates, that result is an honest strategy-calibration failure, not a runtime defect.

Current status:

```text
UNIT/REGRESSION BASELINE: PASS
FULL-WINDOW TECHNICAL EXECUTION: PASS
GRID CROSS-FIELD CORRECTION: IMPLEMENTED / OPERATOR RERUN REQUIRED
LOCAL_OOS_PROOF_PASS: NOT CLAIMED
PROMOTION: NOT_ELIGIBLE
NOT_PRODUCTION_READY
```

## Execution-Price Corrected Full-Range R1 IS Final Result — 2026-06-10

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Final session status:
`DONE for OOS execution infrastructure / EXECUTION_PRICE_CORRECTION_VALIDATED / FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`.

### Final operator validation

```text
WatchlistBacktestParamGrid: 4 tests / 636 assertions / PASS
WatchlistBacktestMetricsServiceTest: 15 tests / 113 assertions / PASS
WatchlistBacktestPublishedPrice: 18 tests / 177 assertions / PASS
WatchlistBacktestOos: 24 tests / 186 assertions / PASS
WatchlistBacktest: 87 tests / 1430 assertions / PASS
Full Watchlist: 179 tests / 2318 assertions / PASS
```

### Final supported-runtime evidence

```text
requested_from=2023-01-02
requested_to=2026-05-29
split_rule=FLOOR_70_PERCENT_IS_REMAINDER_OOS
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
oos_from=2025-05-22
oos_to=2026-05-29
oos_trading_date_count=242
param_grid_count=24
is_valid_param_count=0
is_failed_param_count=24
is_max_picks_count=1445
is_max_days_covered=513
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
artifact_hash=f4ec8464f08515b31d7d26636851acea930307d6
production_ready=0
exit_code=1
```

The run completed the full IS evaluation. Exit code `1` is the correct fail-closed result for `WS_BT_OOS_PROOF_MISSING` when no parameter passes every IS gate. It is not a runtime or database defect.

### Diagnostics and quality conclusion

- no per-evaluation runtime/source diagnostics were emitted;
- no `WATCHLIST_BACKTEST_SOURCE_NOT_READY`, price-read, OHLC, tradability, or execution failure remained;
- all failures are canonical return/downside/stability gate failures;
- param 9 passes average return only and fails median, downside, and stability;
- param 24 passes downside only and fails average, median, and stability;
- R1 therefore has no eligible best-IS binding and OOS correctly remains unread/unexecuted.

### Evidence retained

- canonical runtime artifact: `storage/app/watchlist/backtest/oos-proof-run-1.json`;
- frozen copy: `storage/app/watchlist/backtest/oos-proof-execution-price-corrected-is-failed.json`;
- IS matrix: `storage/app/watchlist/backtest/oos-is-evaluation-matrix-execution-corrected.csv`;
- canonical artifact hash: `f4ec8464f08515b31d7d26636851acea930307d6`.

### Final boundary

```text
R1_EXECUTION_VALIDATED
R1_GRID_FAILED_IS_QUALITY
NO_VALID_IS_PARAM
OOS_NOT_EXECUTED
NOT_ELIGIBLE_FOR_PROMOTION
NOT_PRODUCTION_READY
```

No owner acceptance rule was weakened, no OOS data was used for selection, no best-of-failed parameter was created, and no contract is promoted to `LOCKED`.

Next session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION SESSION`.

## R2 Entry-Quality Calibration Implementation Update — 2026-06-10

Session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

Status:
`DONE for R2 entry-quality calibration implementation unit-static scope / OPERATOR_R2_IS_RERUN_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Implemented closure:

- immutable R1 catalog count/hash retained at `24` / `9da8b0983c57bde1ce0a1fbf1c119756f8af431c`;
- new explicit R2 catalog `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06`, version `R2`, count `12`, hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5`;
- compact curated entry-quality rows with one R1 control row and fixed execution/exit axes;
- catalog-aware schema/repositories and deterministic R1 backfill;
- explicit runtime mapping for all persisted R2 fields, with cross-field guards and no silent R2 fallback;
- catalog-aware eval identity, exact-rerun idempotence, and conflict fail-closed behavior;
- dedicated `watchlist:backtest-r2-param-grid-seed` and `watchlist:backtest-is-calibrate` commands;
- exact immutable IS window `2023-01-02..2025-05-21` and hard maximum market-data date `2025-05-21`;
- final-five-trading-day entry censoring to preserve HOLD=5 without OOS price reads;
- R1 before/after snapshot proof and OOS-table before/after snapshot proof in the R2 artifact;
- best binding only after every unchanged canonical IS gate passes; no best-of-failed;
- policy, validator, reason-code seed, schema DDL, checklist, artifact manifest, implementation status, and contract tracker synchronized;
- files 16 and 17 were not modified.

Packaging validation:

```text
PHP syntax lint: PASS / 312 PHP files
R2 pure-PHP smoke: PASS / 180 assertions
R1 factory compatibility: PASS / 24 of 24 rows
R1 IS-calibration service compatibility: PASS / exact output equality
R1 catalog hash direct check: PASS
R2 catalog count/hash direct check: PASS
official PHPUnit: BLOCKED before discovery (missing dom, mbstring, xml, xmlwriter; exit 1)
artisan migration/seed/calibration: EXPECTED FAIL-CLOSED (PHP 8.4.16 unsupported; exit 2)
PDO database drivers: unavailable
package installation attempt: BLOCKED (DNS resolution failure)
```

No R2 seed, database migration, IS replay, evaluation rows, best binding, or two-run artifact was fabricated in this environment. Therefore runtime result, OOS-proof eligibility, and R2 quality verdict remain operator-dependent.

Supersession note:

The implementation-only status below was the correct status before supported-operator evidence existed. It is superseded by the final R2 operator result immediately following this section. Do not use `OPERATOR_R2_IS_RERUN_REQUIRED` as the current status after the final evidence block.

Historical pre-runtime status:

```text
OPERATOR_R2_IS_RERUN_REQUIRED
OOS_NOT_READ
OOS_PROOF_ELIGIBILITY=NOT_DETERMINED
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
NOT_PRODUCTION_READY
```

## R2 Entry-Quality Calibration Final Operator Result — 2026-06-10

Session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

Final status:
`DONE for R2 entry-quality calibration execution infrastructure / LOCAL_R2_IS_CALIBRATION_EXECUTED / R2_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Final operator validation

```text
WatchlistBacktestR2ParamGridParamsetFactoryTest: 12 tests / 106 assertions / PASS
WatchlistBacktestR2StaticGuardTest: 5 tests / 53 assertions / PASS
WatchlistBacktestOosPersistenceTest: 3 tests / 13 assertions / PASS
WatchlistBacktestR2: 26 tests / 530 assertions / PASS
WatchlistBacktestOos: 24 tests / 228 assertions / PASS
WatchlistBacktest: 117 tests / 2442 assertions / PASS
Full Watchlist: 209 tests / 3330 assertions / PASS
```

### Migration and seed evidence

```text
migration=2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality
migration_status=Yes
migration_batch=10

R2 seed run 1:
status=PASS
catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
catalog_version=R2
catalog_count=12
catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
inserted_count=12
updated_count=0
existing_count=0
r1_catalog_count=24
r1_catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
r1_immutable=1
exit_code=0

R2 seed run 2:
status=PASS
inserted_count=0
updated_count=0
existing_count=12
r1_immutable=1
exit_code=0
```

Coexistence proof:

```text
R1 catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
R1 catalog_version=R1
R1 catalog_count=24
R1 distinct_row_codes=24
R1 distinct_row_hashes=24
R1 catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c

R2 catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
R2 catalog_version=R2
R2 catalog_count=12
R2 distinct_row_codes=12
R2 distinct_row_hashes=12
R2 catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
```

### Final R2 IS runtime evidence

Both IS calibration runs used the exact same inputs:

```text
catalog_code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
catalog_version=R2
catalog_count=12
catalog_hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
is_from=2023-01-02
is_to=2025-05-21
is_trading_date_count=562
is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
```

Both runs produced the same final result:

```text
status=R2_GRID_FAILED_IS_QUALITY
reason_code=WS_BT_R2_NO_VALID_IS_CANDIDATE
is_valid_param_count=0
is_failed_param_count=12
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=null
best_is_binding_hash=null
artifact_hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
production_ready=0
```

No-OOS proof:

```text
max_requested_market_data_date=2025-05-21
max_allowed_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=1
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
```

### Final R2 verdict

R2 infrastructure, schema, seed, strict-IS runtime, deterministic artifacting, no-OOS boundary, and test coverage are accepted for this scope. R2 strategy/catalog quality failed because no R2 row passed every canonical IS gate.

This is not an OOS acceptance failure. OOS remained unread and unexecuted. The result must be preserved as failed-IS evidence.

### Final R2 boundary

```text
LOCAL_R2_IS_CALIBRATION_EXECUTED
R2_GRID_FAILED_IS_QUALITY
NO_VALID_R2_IS_PARAM
NO_BEST_IS_BINDING
NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
OOS_NOT_READ
NOT_ELIGIBLE_FOR_PROMOTION — OOS proof missing
NOT_PRODUCTION_READY
```

No file-16 acceptance gate was changed. No best-of-failed parameter was selected. No R1/R2 catalog identity may be mutated to make this result appear better.

### Catalog naming decision

`R1` and `R2` remain valid only as historical aliases and backward-compatible evidence labels. They must not become the future naming pattern. New calibration catalogs must not be named `R3`, `R4`, `R5`, or later.

Future naming rule:

```text
catalog code: WS_BT_GRID_<FOCUS>_C##_YYYY_MM
IS evidence:  WS_BT_IS_<FOCUS>_C##_RUN_##
OOS evidence: WS_BT_OOS_<FOCUS>_C##_RUN_##
```

Recommended next catalog focus, if diagnostics justify a new catalog:

```text
WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

Next session:
`WATCHLIST — WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION`.

## Downside/Stability C01 Implementation Unit-Static Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 IMPLEMENTATION UNIT-STATIC SESSION`

Status:
`DONE for downside/stability C01 implementation unit-static scope / OPERATOR_C01_IS_RERUN_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
C01 catalog code: WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog version: C01
C01 catalog count: 8
C01 catalog hash: 604ac98f6f193a4c317d4f25582deada84682846
C01 seed command: watchlist:backtest-c01-param-grid-seed
C01 IS artifact version: WATCHLIST_C01_IS_CALIBRATION_V1
C01 IS artifact scope: WEEKLY_SWING_DOWNSIDE_STABILITY_C01_IS_ONLY
C01 runtime status: C01_GRID_FAILED_IS_QUALITY
OOS status: OOS_NOT_READ
PHPUnit C01: 12 tests / 381 assertions / exit 0
PHPUnit Backtest filter: 130 tests / 2829 assertions / exit 0
PHPUnit full Watchlist: 222 tests / 3717 assertions / exit 0
MarketData required filters: 7/37, 4/16, 3/41 / exit 0
```

### Implemented files

- `app/Application/Watchlist/Services/WatchlistBacktestC01ParamGridCatalog.php`
- `app/Application/Watchlist/Services/WatchlistBacktestParamGridParamsetFactory.php`
- `app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationExecutionService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestIsCalibrationService.php`
- `app/Infrastructure/Persistence/Watchlist/WatchlistBacktestParamGridRepository.php`
- `app/Console/Commands/Watchlist/SeedBacktestC01ParamGridCommand.php`
- `database/seeders/Watchlist/WatchlistBacktestC01ParamGridSeeder.php`
- `tests/Unit/Watchlist/WatchlistBacktestC01ParamGridCatalogTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestC01ParamGridParamsetFactoryTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestC01StaticGuardTest.php`

### Boundary

C01 implementation initially did not run seed, migration, IS calibration, OOS proof, promotion, portfolio, broker, order, or production-trading flows. The later C01 seed/IS validation result below supersedes the runtime `NOT_RUN` portion of this unit-static section. Promotion remains `NOT_ELIGIBLE - OOS proof missing`.

## Downside/Stability C01 Diagnostic-Design Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION`

Status:
`DONE for downside/stability C01 diagnostic-design scope / C01_IMPLEMENTATION_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence read

```text
r2-is-run-1.json present=true
r2-is-run-2.json present=true
r2 file SHA1 equality=true
r2 file SHA1=124d41bfe9635de633d38dd959336b5a8d1b146f
r2 canonical artifact hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
r1-final-is-failed.json present=false
r1-final-is-evaluation-matrix.csv present=false
available R1 comparison artifact=oos-is-evaluation-matrix-execution-corrected.csv
```

### Diagnostic conclusion

- R2 infrastructure/runtime remains PASS and R2 strategy/catalog quality remains FAIL.
- All 12 R2 rows passed minimum trade count and coverage, then failed robust-return, downside, and stability gates.
- The R2 artifact contains no runtime/source diagnostics and preserves strict IS boundary `max_requested_market_data_date=2025-05-21`.
- The failure is not an OOS acceptance failure and OOS remained unread.
- Available R1 corrected IS matrix supports low/ultra-low ATR as a relevant downside axis, but it also shows stability/robust-return remain unsolved; therefore no best-of-failed parameter is selected.

### C01 design result

Reference note:
`docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md`

```text
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_count=8
catalog_hash=b746748945df595171b45d44c7c3fbbaa199a9f4
implementation_status=C01_IMPLEMENTATION_REQUIRED
runtime_status=NOT_RUN
oos_proof_eligibility=NOT_DETERMINED
promotion_eligibility=NOT_ELIGIBLE - OOS proof missing
production_ready=false
```

The design is finite, curated, deterministic, and uses only registry-owned runtime-consumed axes. It keeps execution semantics fixed:

```text
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
```

### Files changed

- created `docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md`;
- updated `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`;
- updated `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`.

### Validation boundary

No PHP code, migration, seeder, database rows, or runtime command was changed in this diagnostic-design scope. C01 IS calibration is not executable until a later implementation session adds the catalog to code and persistence allowlists. No PHPUnit or Artisan runtime PASS is claimed for C01.

## Downside/Stability C01 Seed And IS Two-Run Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 SEED AND IS TWO-RUN VALIDATION SESSION`

Status:
`DONE for downside/stability C01 calibration execution infrastructure / LOCAL_C01_IS_CALIBRATION_EXECUTED / C01_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
C01 seed status=PASS
C01 seed exit_code=0
C01 inserted_count=8
C01 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog_version=C01
C01 catalog_count=8
C01 catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
R1 immutable=PASS
R2 immutable=PASS
C01 IS run 1 status=C01_GRID_FAILED_IS_QUALITY
C01 IS run 2 status=C01_GRID_FAILED_IS_QUALITY
C01 IS command exit_codes=1,1
C01 IS artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
C01 IS file SHA1 run 1=04F6C664A0C9006C16542A8380034A0A633041DC
C01 IS file SHA1 run 2=04F6C664A0C9006C16542A8380034A0A633041DC
C01 valid IS rows=0
C01 failed IS rows=8
C01 failure classes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
max_requested_market_data_date=2025-05-21
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

### Checklist

| Item | Status | Notes |
|---|---|---|
| C01 seed | `PASS` | Inserted 8 rows and preserved R1/R2. |
| C01 two-run determinism | `PASS` | File SHA1, artifact hash, catalog hash, date hash, evaluations, eval IDs, and none-binding are equal. |
| C01 quality gates | `FAIL` | All 8 rows failed downside, robust-return, and stability gates. |
| Best IS binding | `NOT_CREATED` | No valid C01 IS parameter; no best-of-failed binding. |
| OOS proof | `NOT_RUN` | OOS was not read, invoked, or written. |
| Promotion | `NOT_ELIGIBLE` | OOS proof missing and C01 has no valid IS parameter. |

No next catalog was created in this session. Any further catalog design must be a separate session.


## C01 Failure Diagnostic Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION`

Status:
`DONE for C01 failure diagnostic scope / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Reference note:
`docs/watchlist/system/policies/weekly_swing/_refs/WS_C01_FAILURE_DIAGNOSTIC_NOTE.md`

### Evidence

```text
C01 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog_version=C01
C01 catalog_count=8
C01 catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
C01 artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
C01 file_sha1_run_1=04F6C664A0C9006C16542A8380034A0A633041DC
C01 file_sha1_run_2=04F6C664A0C9006C16542A8380034A0A633041DC
C01 file_sha1_equal=true
C01 is_valid_param_count=0
C01 is_failed_param_count=8
C01 best_is_binding=null
failure_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
max_requested_market_data_date=2025-05-21
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

### Diagnostic conclusion

- C01 did not fail because coverage or trade count was too low. Every row has `508` covered days and at least `1382` picks.
- C01 failed because all rows still have negative average return, negative median return, p25 downside below `-0.03`, month-win minimum far below `0.45`, and month-average minimum below `-0.01`.
- Best observed C01 average is `-0.001727`; best p25 is `-0.044179`; best month-win minimum is `0.228070`. None reaches the canonical gate.
- The artifact supports `SCORE_RANKING`/`SETUP_FILTER` suspicion, but does not include trade-level, ticker-level, or setup-bucket drilldown needed to safely choose that as the next catalog focus.
- No C02 or new-focus catalog was created. The correct next move is IS-only drilldown diagnostics, not another catalog.

### Eligibility

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

### Validation boundary

No PHP code, migration, seeder, database row, runtime command, OOS command, PLAN, RECOMMENDATION, or CONFIRM behavior was changed in this diagnostic update. Local Artisan/PHPUnit execution is `BLOCKED` in this container because `php artisan list` returns `ENV_UNSUPPORTED_PHP_VERSION` for PHP `8.4.16`; therefore no local Artisan/PHPUnit PASS is claimed by the assistant. Supported-operator PHPUnit evidence was later provided for this exact diagnostic-sync state: `WatchlistBacktestC01` 12 tests / 381 assertions / exit 0, `WatchlistBacktest` filter 130 tests / 2829 assertions / exit 0, and full `tests\Unit\Watchlist` 222 tests / 3717 assertions / exit 0.

## C01 IS Failure Drilldown Unit-Static Implementation Result - 2026-06-11

### Evidence

- Added IS-only drilldown service: `app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php`.
- Added command: `app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php`.
- Registered command in `app/Console/Kernel.php`.
- Added unit/static tests for service and command guardrails.
- Added reference note: `docs/watchlist/system/policies/weekly_swing/_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md`.
- Existing C01 artifacts remain immutable and deterministic: catalog hash `604ac98f6f193a4c317d4f25582deada84682846`, artifact hash `c8505ce5a9045629234a685984d9138b3990c775`, two file SHA1 values `04f6c664a0c9006c16542a8380034a0a633041dc`.
- Locked file 16 and 17 SHA1 values remain `31299d858b68ee351ae898f4c9380d8753a65d8a` and `39519a391158a7b2dcf7b6e989079788d61669be`.

### Implemented diagnostic output

The command is designed to produce a deterministic file-only artifact with:

```text
per_param_status
per_param_failure_codes
per_param_key_metrics
nearest_gate_gap
worst_gate_gap
ticker_loss_cluster_summary
ticker_profit_cluster_summary
month_failure_cluster_summary
month_profit_cluster_summary
trade_date_failure_cluster_summary
setup_bucket_summary
atr_bucket_summary
score_bucket_summary
param_axis_effectiveness_summary
dead_parameter_or_silent_default_summary
data_quality_diagnostic_summary
no_oos_leakage_summary
next_focus_recommendation
```

Superseded limitation: this historical session found that runtime trade/evaluation payload did not yet export `close_to_hh20_pct`, `roc20`, `vol_ratio`, `dv20_idr`, `sector_code`, or score components. The active `2026-06-11` C01 payload expansion now exports those fields into diagnostic strategy trades and derives the feature buckets from runtime evidence.

### Validation boundary

Actually run locally:

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php = PASS
php -l app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownServiceTest.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownStaticGuardTest.php = PASS
isolated stubbed PHP smoke for deterministic hash/file equality = PASS
```

Blocked locally:

```text
php artisan list = BLOCKED / ENV_UNSUPPORTED_PHP_VERSION / PHP 8.4.16; required >=7.3 and <8.4
php vendor/bin/phpunit tests/Unit/Watchlist --filter "WatchlistBacktestIsFailureDrilldown" = BLOCKED / missing extensions: dom, mbstring, xml, xmlwriter
```

No PHPUnit PASS, Artisan PASS, DB runtime proof, or C01 drilldown runtime artifact is claimed in this environment.

### Current conclusion

```text
DONE for C01 IS failure drilldown unit-static implementation scope
OPERATOR_C01_IS_DRILLDOWN_RUNTIME_REQUIRED
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

OOS-proof eligibility remains:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
```

Promotion eligibility remains:

```text
NOT_ELIGIBLE — OOS proof missing
```


## C01 IS Failure Drilldown Workspace Artifact Review - 2026-06-11

### Evidence inspected from current ZIP/workspace

```text
storage/app/watchlist/backtest/c01-is-failure-drilldown-run-1.json
file_sha1=db0a8498faca15e49871ee3b33ab420075cac156
artifact_hash=c2cfd4d8a438108cd53636bccf4303b12e243de7
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
catalog_version=C01
catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
catalog_count=8
is_from=2023-01-02
is_to=2025-05-21
max_requested_market_data_date=2025-05-21
strict_is_boundary_all_evaluations=true
oos_service_invoked=false
oos_repository_invoked=false
oos_table_unchanged=true
oos_executed=false
production_ready=false
best_is_binding=null
```

The artifact contains all required drilldown top-level sections, including per-param status/failure/metrics, gate gaps, ticker/month/date clusters, setup/ATR/score buckets, parameter-axis effectiveness, data-quality diagnostics, no-OOS leakage summary, and next-focus recommendation.

### Runtime finding from available one-run artifact

```text
per_param_failure_distribution:
- WS_BT_EVAL_DOWNSIDE_FAIL=8
- WS_BT_EVAL_ROBUST_RETURN_FAIL=8
- WS_BT_EVAL_STABILITY_FAIL=8

next_focus_recommendation.decision=NEXT_CATALOG_NOT_DESIGNED
next_focus_recommendation.focus=DIAGNOSTIC_PAYLOAD_ENRICHMENT_BEFORE_C02
```

Top observed loss clusters from the available artifact are ticker-level and month-level, but breakout, momentum, volume, liquidity, and sector root-cause conclusions remain blocked by payload gaps because `close_to_hh20_pct`, `roc20`, `vol_ratio`, `dv20_idr`, `sector_code`, and score components are not exported in the current trade/evaluation payload.

### Validation boundary

Actually run locally in this session:

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestIsFailureDrilldownService.php = PASS
php -l app/Console/Commands/Watchlist/RunBacktestIsDiagnoseCommand.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownServiceTest.php = PASS
php -l tests/Unit/Watchlist/WatchlistBacktestIsFailureDrilldownStaticGuardTest.php = PASS
php artisan list = BLOCKED / ENV_UNSUPPORTED_PHP_VERSION / PHP 8.4.16
php vendor/bin/phpunit --version = BLOCKED / missing extensions: dom,mbstring,xml,xmlwriter
```

No PHPUnit PASS, Artisan diagnostic execution PASS, DB proof, OOS proof, or two-run deterministic drilldown proof is claimed by this assistant.

### Current conclusion

```text
DONE for C01 IS failure drilldown workspace one-run artifact review scope
C01_IS_DRILLDOWN_RUN1_AVAILABLE
OPERATOR_TWO_RUN_PROOF_REQUIRED
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_READ
NOT_PRODUCTION_READY
```

OOS-proof eligibility remains:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
```

Promotion eligibility remains:

```text
NOT_ELIGIBLE — OOS proof missing
```


2026-06-13 C16 static guard follow-up: patched WatchlistBacktestC15StaticGuardTest regex to match literal `$extendedCatalogVersions` with an escaped PCRE dollar. Operator full Watchlist failure showed the previous regex was parsed as `/$extendedCatalogVersions.../`, so this patch keeps the C15 guard intent while allowing C16 in the extended catalog list. Runtime PASS is still operator-validation required.

2026-06-14 C16 seed follow-up: operator PHPUnit validation passed (`WatchlistBacktestC15StaticGuardTest` 5/5, full `tests/Unit/Watchlist` 354/354, 8371 assertions). C16 seed then blocked with `WS_BT_R2_CATALOG_INVALID: catalog_code is not an approved immutable catalog`. Root cause identified as `WatchlistBacktestParamGridRepository::assertKnownCatalogIdentity()` missing C16 in the approved immutable catalog map. Patched repository C16 approval and added a C16 static guard. Seed PASS was operator-validation-required at that point and is now superseded by final C16 evidence: seed PASS, diagnose-batch PASS, and deterministic IS calibration failure are recorded in the active session.


## Audit Append - 2026-06-15 C16 final operator validation

C16 final operator validation is now recorded as runtime-validated but strategy-quality failed. PHPUnit C16 and full Watchlist suites passed, seed passed, diagnose-batch passed, and IS calibration run 1/run 2 were deterministic with artifact hash `63698d0c809a1f2124d8218273ba4d34d9c78deb`. C16 remains `OOS_NOT_RUN` and `production_ready=0` because `is_valid_param_count=0` and `best_is_binding=null`.

## Audit Append - C19 Tahap 5 Quality Recovery Tuning Diagnostic

C19 Tahap 5 source-level diagnostic has been implemented to compare quality-recovery profiles after Tahap 4 proved evaluated-sample recovery but return quality remained negative. This is not a catalog/promotion step.

Implemented source:

```text
app/Application/Watchlist/Services/WatchlistBacktestC19QualityRecoveryDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC19QualityRecoveryDiagnoseCommand.php
```

Updated source:

```text
app/Application/Watchlist/Services/WatchlistBacktestC19ProposedSelectionPriceDiagnosticService.php
app/Console/Kernel.php
```

Key markers:

```text
C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC=true
IS_ONLY_QUALITY_RECOVERY_DIAGNOSTIC=true
quality_profiles_use_price_outcome_for_selection=false
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

Validation boundary:

```text
php lint changed PHP files = PASS
PHPUnit/runtime command = OPERATOR_VALIDATION_REQUIRED
```

Tahap 5 must record only results that help the next decision. A profile that merely preserves sample but keeps negative return quality must be recorded as useful negative evidence, not promoted as catalog evidence.

## Audit Append - C19 Tahap 5B Hybrid Quality Backfill Diagnostic

C19 Tahap 5B has been implemented as a source-level diagnostic patch after Tahap 5A showed useful quality evidence but no quality-target PASS.

Operator-provided Tahap 5A evidence that drives this patch:

```text
Q02_NO_SCORE_OVEREXTENSION_RECOVERY param 148: evaluated=53, avg=0.00%, median=+0.55%, p25=-1.92%, win=52.83%, period_fail=8
Q00_TAHAP_4_BASELINE param 148: evaluated=124, avg=-0.18%, median=-0.05%, p25=-1.82%, win=43.55%, period_fail=13
```

Conclusion: no-overextension is a strong quality signal, but the strict quality pool is too small. Tahap 5B therefore adds hybrid profiles using a strict quality core plus controlled backfill, and repairs decision ranking so tiny-sample averages cannot be treated as best decision evidence.

Changed source level:

```text
app/Application/Watchlist/Services/WatchlistBacktestC19ProposedSelectionPriceDiagnosticService.php
app/Application/Watchlist/Services/WatchlistBacktestC19QualityRecoveryDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC19QualityRecoveryDiagnoseCommand.php
tests/Unit/Watchlist/WatchlistBacktestC19QualityRecoveryDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC19StaticGuardTest.php
```

New diagnostic profiles:

```text
Q07_NO_OVEREXTENSION_CORE_WITH_DOWNSIDE_BACKFILL_120
Q08_NO_OVEREXTENSION_CORE_WITH_MONTHLY_FLEX_BACKFILL
Q09_LOW_ATR_NEG_ROC20_CORE_WITH_NO_OVEREXTENSION_BACKFILL
Q10_HYBRID_Q02_Q04_Q05_BACKFILL_125
```

Boundary remains unchanged:

```text
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
quality_profiles_use_price_outcome_for_selection=false
```

Runtime validation required:

```text
PHPUNIT_C19_FILTER=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
C19_TAHAP_5B_FOCUSED_DIAGNOSTIC=OPERATOR_VALIDATION_REQUIRED
```

## Audit Append - C19 Tahap 5C Sample-Quality Frontier Diagnostic

C19 Tahap 5C has been implemented as an IS-only sample-quality frontier diagnostic after Tahap 5B proved that hybrid backfill profiles still failed to reach the 120 evaluated-pick sample target.

Source-level changes:

```text
app/Application/Watchlist/Services/WatchlistBacktestC19ProposedSelectionPriceDiagnosticService.php
app/Application/Watchlist/Services/WatchlistBacktestC19QualityRecoveryDiagnosticService.php
app/Console/Commands/Watchlist/RunBacktestC19QualityRecoveryDiagnoseCommand.php
tests/Unit/Watchlist/WatchlistBacktestC19QualityRecoveryDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC19StaticGuardTest.php
docs/watchlist/audit/WS_C19_QUALITY_RECOVERY_TUNING_DIAGNOSTIC.md
docs/watchlist/audit/WS_C19_OPERATOR_VALIDATION_COMMANDS.md
```

New frontier profiles:

```text
Q11_FRONTIER_L0_STRICT_NO_OVEREXTENSION_CORE
Q12_FRONTIER_L1_LOW_ATR_NO_OVEREXTENSION_90
Q13_FRONTIER_L2_DOWNSIDE_BACKFILL_110
Q14_FRONTIER_L3_CONTROLLED_OVEREXTENSION_125
Q15_FRONTIER_L4_BASELINE_BOUNDARY_135
```

Status markers:

```text
C19_TAHAP_5C_SAMPLE_QUALITY_FRONTIER_SOURCE_IMPLEMENTED=true
C19_TAHAP_5C_OPERATOR_VALIDATION_REQUIRED=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

No runtime proof is claimed by this source patch. Operator must run PHPUnit and the frontier diagnostic commands before any conclusion.
## Watchlist C19 final diagnostic closure

C19 is closed as diagnostic success and catalog-candidate failure.

Operator evidence:

```text
PHPUNIT_C19=PASS: OK (13 tests, 192 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (385 tests, 9243 assertions)
C19_TAHAP_5C_FRONTIER_FOCUSED=PASS: artifact_hash=971d1186bff72e185db59dc1c223d423186a7ad4
C19_TAHAP_5C_FRONTIER_ALL_PARAM=PASS: artifact_hash=18ae8b1f1dcfc5ddecc2279d3c9fd0ce69079e6d
profiles_with_sample_target_reached=2
profiles_with_quality_improvement=0
profiles_with_quality_target_reached=0
```

Final frontier result:

```text
L0/L1 quality-positive core: evaluated=53, avg=0.00%, median=+0.55%, win=52.83%, sample_gate=false
L2 larger backfill: evaluated=104, avg=-0.18%, median=-0.05%, win=42.31%, sample_gate=false
L3 sample-qualified: evaluated=121, avg=-0.18%, median=-0.05%, win=39.67%, quality_gate=false
L4 sample-qualified baseline boundary: evaluated=124, avg=-0.18%, median=-0.05%, win=43.55%, quality_gate=false
```

C19 final status:

```text
C19_DIAGNOSTIC_SUCCESS=true
C19_SAMPLE_RECOVERY_SOLVED=true
C19_PRICE_EVALUATION_CONFIRMED=true
C19_QUALITY_SIGNAL_FOUND=true
C19_QUALITY_CORE_SAMPLE_TOO_SMALL=true
C19_SAMPLE_QUALIFIED_FRONTIER_QUALITY_FAILED=true
C19_CATALOG_CANDIDATE_FAILED=true
C19_CATALOG_IMPLEMENTATION_DEFERRED=true
C19_CATALOG_CODE=NOT_CREATED
C19_STOP_TUNING=true
OOS_NOT_RUN=true
production_ready=0
```

Next implementation direction is C20 regime/trade-date quality gate design. C19 does not unlock OOS proof, promotion, production readiness, or catalog creation.
