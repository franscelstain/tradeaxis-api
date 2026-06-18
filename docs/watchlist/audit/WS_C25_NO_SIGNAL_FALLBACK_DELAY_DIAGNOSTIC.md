# WS C25 No-Signal Fallback and Next-Open Delay Diagnostic

C25 is an IS-only diagnostic layer after C24. It does not create a catalog, does not run OOS, does not promote a paramset, and does not change the canonical execution model.

C25 exists because C24 proved that C23 R09 almost closes the C22 S06 average-return gap, but still fails on median, p25, and win-rate. The remaining gap has two material components:

```text
dominant_gap_component=no_rule_profit_signal_before_fallback
secondary_gap_component=next_open_delay_after_close_signal
```

C25 therefore measures whether the remaining gap can be reduced by realistic diagnostic candidates:

```text
no-signal fallback compression / damage control
R15/R16 downside-combo comparison
preplanned intraday target/protection diagnostics
next-open delay rows-only comparison
no-signal fallback rows-only comparison
```

These are measurement profiles only. They are not production rules.

## Prior evidence carried into C25

```text
C19 proved sample recovery was possible but sample-qualified frontier quality still failed.
C20 proved date/regime gating was not enough to create a catalog candidate.
C21 proved the major issue was execution/exit/stop/hold behavior, not entry quality alone.
C22 proved a first-profit-capture shadow direction exists, but C22 S06 is not a production rule.
C23 proved a non-lookahead first-profit-capture rule candidate exists, but the C22 shadow gap was not acceptable.
C24 explained the remaining C22-vs-C23 gap and identified no-signal fallback as dominant plus next-open delay as secondary.
```

## Source implementation status

```text
C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
C25_RUNTIME_VALIDATION_REQUIRED=true
C25_DIAGNOSTIC_RUNTIME_PASS=NOT_RUN_BY_OPERATOR
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

The source patch adds C25 implementation and tests. Runtime validation must still be run by the operator in the local repo environment before any C25 conclusion is treated as evidence.

## Implemented source components

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

If C23 or C24 is missing, C25 must return `BLOCKED`; it must not reconstruct evidence. If C21 is missing, C25 still runs artifact-mode diagnostics but marks intraday target/protection profiles with explicit source limitation/fallback behavior.

## Data availability note

The C23 artifact carries fixed pick-rule rows, canonical/C22/R09/R15/R16 returns, MFE/MAE summary fields, first profitable close, and C23 profile outputs. It does not carry raw D1-D5 OHLC fields.

C25 therefore records:

```text
d1_to_d5_ohlc_available=false
intraday_high_low_available=false
derived_mfe_mae_available=true only when C21 path artifact is readable
```

Preplanned intraday target/protection profiles use C21-derived MFE/MAE when available. The artifact must keep `intraday_sequence_known=false`; when target and stop are both touched in the same daily candle, conservative stop-first policy is used.

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

## Artifact contract

C25 writes:

```text
artifact_type=C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC
status=PASS|BLOCKED
reason_code=WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY
scope=IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC
source_catalog
source_evidence
data_availability
diagnostic_profiles
pick_diagnostic_rows
baseline_summaries
bucket_summary
no_signal_fallback_summary
next_open_delay_summary
r09_vs_r15_r16_summary
profile_summary
param_consistency_summary
month_stability_summary
lookahead_safety_summary
decision
safety_boundaries
artifact_hash
```

C25 intentionally includes pick-level diagnostic rows because the next decision depends on whether no-signal rows, next-open delay rows, and intraday-derived rows behave differently.

## Decision gates

C25 separates four diagnostic outcomes:

```text
no_signal_fallback_fix_found
next_open_delay_fix_found
distribution_balance_candidate_found
intraday_preplanned_order_candidate_found
```

`exit_rule_path_still_viable` can only become true if at least one of those gates is true, evidence is not limited to one param, and lookahead violations are zero.

`c26_catalog_candidate_diagnostic_recommended` can only become true if the exit-rule path remains viable and at least one non-lookahead profile improves canonical median or p25. Even then, C25 still cannot create a catalog or run OOS.

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

## Validation status in this patch

Syntax validation run in the sandbox:

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticService.php
php -l app/Console/Commands/Watchlist/RunBacktestC25NoSignalFallbackDelayDiagnoseCommand.php
php -l tests/Unit/Watchlist/WatchlistBacktestC25NoSignalFallbackDelayDiagnosticServiceTest.php
php -l tests/Unit/Watchlist/WatchlistBacktestC25StaticGuardTest.php
php -l app/Console/Kernel.php
```

Result:

```text
No syntax errors detected
```

PHPUnit was attempted in the sandbox but could not run because required PHP extensions are unavailable:

```text
PHPUnit requires dom, mbstring, xml, xmlwriter extensions.
PHPUNIT_C25=OPERATOR_VALIDATION_REQUIRED
FULL_WATCHLIST_PHPUNIT=OPERATOR_VALIDATION_REQUIRED
```

No runtime PASS is claimed by this source patch.
