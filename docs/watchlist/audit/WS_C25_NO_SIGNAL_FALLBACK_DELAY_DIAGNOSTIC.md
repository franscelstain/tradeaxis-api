# WS C25 No-Signal Fallback and Next-Open Delay Diagnostic

C25 is closed as an IS-only diagnostic success and as a catalog-candidate diagnostic handoff to C26.

C25 did not create a catalog, did not run OOS, did not promote any paramset, did not mutate C01-C24, and did not change the canonical execution model.

## Why C25 exists

C25 was created after C24 proved that C23 R09 nearly closed the C22 S06 average-return gap but still failed on median, p25, and win-rate. C24 identified two material gap components:

```text
dominant_gap_component=no_rule_profit_signal_before_fallback
secondary_gap_component=next_open_delay_after_close_signal
```

C25 measured whether those gaps could be reduced by realistic, non-lookahead diagnostic candidates:

```text
no-signal fallback compression / damage control
R15/R16 downside-combo comparison
preplanned intraday target/protection diagnostics
next-open-delay rows-only comparison
no-signal fallback rows-only comparison
```

All C25 profiles are diagnostic/gap-analysis profiles only. None are production rules.

## Prior evidence carried into C25

```text
C19 proved sample recovery was possible but sample-qualified frontier quality still failed.
C20 proved date/regime gating was not enough to create a catalog candidate.
C21 proved the major issue was execution/exit/stop/hold behavior, not entry quality alone.
C22 proved a first-profit-capture shadow direction exists, but C22 S06 is not a production rule.
C23 proved a non-lookahead first-profit-capture rule candidate exists, but the C22 shadow gap was not acceptable.
C24 explained the remaining C22-vs-C23 gap and identified no-signal fallback as dominant plus next-open delay as secondary.
```

## Final implementation status

```text
C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
C25_DIAGNOSTIC_RUNTIME_PASS=true
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

C25 is finalized as diagnostic evidence, not as production readiness.

## Source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC25NoSignalFallbackDelayDiagnoseCommand.php

Command signature:
watchlist:backtest-c25-no-signal-fallback-delay-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC25StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## Input artifact model

C25 reads completed diagnostic artifacts and does not mutate catalog/source selection:

```text
C23 required:
storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json

C24 required:
storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json

C21 optional but useful for derived MFE/MAE intraday diagnostics:
storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json
```

If C23 or C24 is missing, C25 must return `BLOCKED`; it must not reconstruct evidence. C21 is optional. If C21 is missing, C25 still runs artifact-mode diagnostics but must mark intraday-derived limitations explicitly.

## Data availability note

Operator evidence confirmed:

```text
c23_all_param_artifact_available=true
c24_all_param_artifact_available=true
c21_path_artifact_available=true
canonical_baseline_available=true
c22_shadow_s06_available_or_recomputable=true
c23_r09_rows_available=true
c23_r15_rows_available=true
c23_r16_rows_available=true
d1_to_d5_ohlc_available=false
d1_to_d5_close_return_available=true
derived_mfe_mae_available=true
next_open_after_close_signal_available=true
intraday_high_low_available=false
intraday_high_low_derived_from_c21_mfe_mae_available=true
market_calendar_continuity_available=true
published_price_availability_available=true
missing_companion_row_count=0
```

The C23 artifact does not carry raw D1-D5 open/high/low/close fields. C25 keeps raw OHLC fields null and uses C21-derived MFE/MAE when available. Therefore preplanned intraday profiles remain diagnostic approximations unless a later candidate diagnostic validates against raw OHLC/order-level data.

## Diagnostic profiles

```text
C25_G00_CANONICAL_BASELINE
C25_G01_C22_S06_SHADOW_BENCHMARK
C25_G02_C23_R09_BASELINE_BRIDGE
C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR
C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR
C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN
C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN
C25_G07_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN_IF_MAE_LT_2PCT
C25_G08_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN_IF_MAE_LT_2PCT
C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL
C25_G10_R09_PLUS_NO_SIGNAL_D4_DAMAGE_CONTROL
C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL
C25_G12_R09_PLUS_R16_STYLE_DOWNSIDE_CONTROL
C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
C25_G14_PREPLANNED_INTRADAY_TARGET_0_75PCT
C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT
C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
C25_G17_PREPLANNED_TARGET_0_75PCT_WITH_STOP_1_50PCT
C25_G18_PREPLANNED_TARGET_1_00PCT_WITH_STOP_2_00PCT
C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09
C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09
C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
```

All C25_Gxx profiles are diagnostic/gap-analysis profiles. None may become a production catalog row inside C25.

## Operator validation evidence

### PHPUnit

```text
PHPUNIT_C25=PASS
OK (6 tests, 90 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (419 tests, 10446 assertions)
```

### Focused runtime

```text
C25_FOCUSED_RUNTIME_PASS=true
C25_FOCUSED_ARTIFACT_HASH=7bd6221bdd7993d9897a4d9bfaf23db22800f263
C25_FOCUSED_EVALUATED_PICKS=394
C25_FOCUSED_PATH_MISSING=45
C25_FOCUSED_PROFILE_COUNT=12

no_signal_fallback_fix_found=true
next_open_delay_fix_found=true
distribution_balance_candidate_found=true
intraday_preplanned_order_candidate_found=true
exit_rule_path_still_viable=true
selection_quality_revisit_needed=false
c26_catalog_candidate_diagnostic_recommended=false
```

Focused runtime gave positive diagnostic signal but did not yet recommend C26 because it only covered the focused param subset.

### All-param runtime

```text
C25_ALL_PARAM_RUNTIME_PASS=true
C25_ALL_PARAM_ARTIFACT_HASH=d464c5bcce398c5405b069ef277d696a10598288
C25_INPUT_C23_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C25_INPUT_C24_ARTIFACT_HASH=feabfbe720d39155a3d741e509cc69cade3ef31c
C25_ALL_PARAM_EVALUATED_PICKS=1575
C25_ALL_PARAM_PATH_MISSING=45
C25_ALL_PARAM_PROFILE_COUNT=22

canonical_avg_ret_net=-0.4690%
c22_s06_avg_ret_net=-0.0162%
c23_r09_avg_ret_net=-0.0217%
c23_r15_p25_ret_net=-0.9309%
c23_r16_p25_ret_net=-0.9195%

no_signal_fallback_count=295
next_open_delay_count=264

best_profile_code_by_avg=C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN
best_profile_code_by_median=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
best_profile_code_by_p25=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
best_profile_code_by_distribution_balance=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
best_no_signal_fallback_profile=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
best_next_open_delay_profile=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
```

All-param decision:

```text
decision_status=C25_GAP_FIX_CANDIDATE_FOUND
no_signal_fallback_fix_found=true
next_open_delay_fix_found=true
distribution_balance_candidate_found=true
intraday_preplanned_order_candidate_found=true
exit_rule_path_still_viable=true
selection_quality_revisit_needed=false
c26_catalog_candidate_diagnostic_recommended=true
catalog_allowed=false
oos_allowed=false
production_ready=0
```

## Key candidate comparison

C25 final candidate interpretation uses absolute profile distribution metrics, not per-pick p25-delta fields.

```text
C25_G02_C23_R09_BASELINE_BRIDGE
avg=-0.0217%
median=-0.0500%
p25=-2.1245%
win_rate=47.17%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0

C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR
avg=-0.0855%
median=-0.0500%
p25=-0.9309%
win_rate=41.59%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0

C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR
avg=-0.0981%
median=-0.0500%
p25=-0.9195%
win_rate=44.32%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0

C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
avg=-0.2257%
median=+0.4493%
p25=-0.0500%
win_rate=73.21%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
preplanned_order_count=1124

C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
avg=-0.0789%
median=+0.9581%
p25=-1.7163%
win_rate=57.59%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
preplanned_order_count=766

C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
avg=+0.0045%
median=+0.9487%
p25=-0.4499%
win_rate=63.17%
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
preplanned_order_count=906
```

Interpretation:

```text
Primary balanced C26 candidate:
C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT

Primary defensive/distribution comparator:
C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT

Next-open-delay comparator:
C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT

Downside comparators:
C23_R15 and C23_R16
```

G21 is the main C26 candidate because it is the best balanced profile: average is slightly positive, median is strong, p25 is materially better than R09, win-rate is materially higher than R09, and both lookahead and ambiguous intraday sequence counts are zero.

G13 is the best defensive/distribution profile: p25 is near break-even and win-rate is high, but average is negative across all params. It should be a defensive comparator rather than the main balanced candidate.

G16 is useful for next-open-delay analysis, but p25 remains too deep to use as the primary global candidate.

R15/R16 remain important downside comparators, but their average and win-rate tradeoff is worse than G21/G13.

## Param consistency evidence

Param consistency supports G21 as the primary balanced candidate.

```text
R09 median across params: -0.0500%
R09 p25 range: about -1.6757% to -2.4494%
R09 win-rate range: 42.42% to 49.62%

G13 median across params: about +0.449%
G13 p25: mostly -0.0500% or positive small
G13 win-rate range: 70.23% to 77.27%
G13 avg: negative in all 12 params

G16 median range: about +0.4816% to +1.4469%
G16 p25 remains deep: about -0.7906% to -2.2235%
G16 win-rate range: 55.47% to 60.61%

G21 median across params: about +0.948%
G21 p25 range: about -0.0501% to -0.6792%
G21 win-rate range: 60.90% to 65.91%
G21 avg positive in 7 of 12 params
```

This is enough to justify C26 as an IS-only catalog-candidate diagnostic, but not enough to justify OOS or production.

## Bucket conclusions

### No-signal fallback

```text
bucket_code=no_rule_profit_signal_before_fallback
count=295
R09/canonical avg=-2.7366%
R09/canonical median=-2.4306%
R09/canonical p25=-3.4201%
R09/canonical win_rate=0.34%
```

G21 was the best no-signal repair candidate:

```text
C25_G21 bucket avg=-0.4549%
C25_G21 bucket median=-0.0501%
C25_G21 bucket p25=-1.2491%
C25_G21 bucket win_rate=38.64%
C25_G21 loss_reduction_rate=89.83%
C25_G21 ambiguous_intraday_sequence_count=0
```

Conclusion: no-signal fallback is not a dead end. It can be materially reduced by a combined intraday target plus no-signal D3 damage-control style candidate.

### Next-open delay

```text
bucket_code=next_open_delay_after_close_signal
count=264
R09 avg=-0.0398%
C22 S06 avg=+1.3728%
avg_signal_close_to_next_open_gap=+1.4125%
profit_lost_to_next_open_rate=100%
```

Best next-open-delay profile:

```text
C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
avg_delta_vs_r09=+0.8948%
median stronger than R09
ambiguous_intraday_sequence_count=0
```

Conclusion: next-open delay can be reduced by preplanned intraday target diagnostics, but G16 is not the best global candidate because p25 remains weak.

## Lookahead and execution safety

C25 final all-param safety markers:

```text
lookahead_violation_count=0
lookahead_safe=true
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
preplanned_order_threshold_fixed_before_path_evaluation=true
close_signal_same_day_exit_forbidden=true
```

C25 did use derived high/low-like MFE/MAE evidence for preplanned order diagnostics, but it did not use that information for ticker selection or trade-date selection.

## Final C25 decision

```text
C25_SOURCE_IMPLEMENTED=true
PHPUNIT_C25=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C25_FOCUSED_RUNTIME_PASS=true
C25_ALL_PARAM_RUNTIME_PASS=true
C25_ALL_PARAM_ARTIFACT_HASH=d464c5bcce398c5405b069ef277d696a10598288
C25_ALL_PARAM_EVALUATED_PICKS=1575
C25_ALL_PARAM_PATH_MISSING=45
C25_ALL_PARAM_PROFILE_COUNT=22

C25_NO_SIGNAL_FALLBACK_FIX_FOUND=true
C25_NEXT_OPEN_DELAY_FIX_FOUND=true
C25_DISTRIBUTION_BALANCE_CANDIDATE_FOUND=true
C25_INTRADAY_PREPLANNED_ORDER_CANDIDATE_FOUND=true
C25_EXIT_RULE_PATH_STILL_VIABLE=true
C25_SELECTION_QUALITY_REVISIT_NEEDED=false
C25_C26_CATALOG_CANDIDATE_DIAGNOSTIC_RECOMMENDED=true

C25_PRIMARY_BALANCED_CANDIDATE=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
C25_DEFENSIVE_COMPARATOR=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
C25_NEXT_OPEN_DELAY_COMPARATOR=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
C25_DOWNSIDE_COMPARATORS=C23_R15,C23_R16

C25_LOOKAHEAD_VIOLATION_COUNT=0
C25_G21_AMBIGUOUS_INTRADAY_SEQUENCE_COUNT=0
C25_G13_AMBIGUOUS_INTRADAY_SEQUENCE_COUNT=0

C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C26_CATALOG_CANDIDATE_DIAGNOSTIC_IS_ONLY
```

## Boundaries preserved

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C25_CATALOG_CODE=NOT_CREATED
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C24_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
NO_C23_REOPEN=true
NO_C24_REOPEN=true
```

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

## Next step

Run C26 as a catalog-candidate diagnostic only.

C26 must test whether G21 can be lifted from a diagnostic profile into a valid catalog-candidate rule while staying IS-only, non-lookahead, and stable across param/month/bucket slices. C26 must compare G21 against G13, G16, R09, R15, and R16. C26 must not run OOS, must not promote production, and must not mutate C01-C25.
