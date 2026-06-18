# WS C26 Catalog Candidate Diagnostic

C26 is closed as an IS-only catalog-candidate diagnostic. It did not create a production catalog, did not run OOS, did not promote any paramset, did not mutate C01-C25, and did not change the canonical execution model.

## Why C26 Exists

C26 exists because C25 found a balanced exit-behavior candidate but still left one critical implementation question open: whether `C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT` is stable enough to become a later catalog-candidate rule, and whether raw OHLC/high-low validation is required before that lift.

Prior evidence carried into C26:

```text
C19 proved sample recovery was possible but the sample-qualified frontier quality failed.
C20 proved date/regime gating was not enough for a catalog candidate.
C21 proved execution/exit/stop/hold behavior was the main problem area.
C22 proved first-profit-capture shadow behavior exists, but C22 S06 is not a production rule.
C23 proved a non-lookahead first-profit-capture rule candidate exists, while the C22 shadow gap remained unacceptable.
C24 explained the remaining C22-vs-C23 gap: no-signal fallback was dominant and next-open delay was secondary.
C25 proved G21 is the best balanced diagnostic candidate, G13 is defensive, G16 helps delay rows, and C26 is warranted.
```

## Source Components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC26CatalogCandidateDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC26CatalogCandidateDiagnoseCommand.php

Command signature:
watchlist:backtest-c26-catalog-candidate-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC26CatalogCandidateDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC26StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## Diagnostic Profiles

```text
C26_G00_CANONICAL_BASELINE
C26_G01_C22_S06_SHADOW_BENCHMARK
C26_G02_C23_R09_BASELINE_BRIDGE
C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE
C26_G04_C25_G13_DEFENSIVE_DISTRIBUTION_COMPARATOR
C26_G05_C25_G16_NEXT_OPEN_DELAY_COMPARATOR
C26_G06_C23_R15_DOWNSIDE_COMPARATOR
C26_G07_C23_R16_DOWNSIDE_COMPARATOR
C26_G08_G21_WITH_PARAM_CONSISTENCY_GATE
C26_G09_G21_WITH_MONTH_STABILITY_GATE
C26_G10_G21_WITH_BUCKET_STABILITY_GATE
C26_G11_G21_WITH_RAW_OHLC_REQUIRED_GATE
C26_G12_G21_WITH_NO_DERIVED_MFE_MAE_DEPENDENCY_GATE
C26_G13_G21_VS_G13_DEFENSIVE_TIEBREAK
C26_G14_G21_VS_G16_DELAY_TIEBREAK
C26_G15_G21_VS_R15_R16_DOWNSIDE_TIEBREAK
C26_G16_CATALOG_CANDIDATE_READINESS_SCORE
```

All C26 profiles are diagnostic/candidate-readiness measurements only. None are production rules, selection filters, catalog rows, or OOS gates.

## Runtime Evidence

PHPUnit:

```text
PHPUNIT_C26=PASS
OK (6 tests, 136 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (425 tests, 10582 assertions)
```

Focused runtime used the C25 all-param artifact filtered to params `148,152,155`:

```text
C26_FOCUSED_RUNTIME_PASS=true
C26_FOCUSED_ARTIFACT_HASH=b1897f7cf82e2fd56bf79ed1bf7edda5f2cb75f9
C26_FOCUSED_EVALUATED_PICKS=394
C26_FOCUSED_PATH_MISSING=45
C26_FOCUSED_PROFILE_COUNT=12
```

All-param runtime:

```text
C26_ALL_PARAM_RUNTIME_PASS=true
C26_ARTIFACT_PATH=storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json
C26_ARTIFACT_HASH=e31ee7fd9bfc0cfb05b88ce5ff6fcbc9111d4b56
C26_INPUT_C21_ARTIFACT_HASH=d6c6c72d51b40a0c852ce9bbc6a452c55920df13
C26_INPUT_C23_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C26_INPUT_C24_ARTIFACT_HASH=feabfbe720d39155a3d741e509cc69cade3ef31c
C26_INPUT_C25_ARTIFACT_HASH=d464c5bcce398c5405b069ef277d696a10598288
C26_EVALUATED_PICKS=1575
C26_PATH_MISSING=45
C26_PROFILE_COUNT=17
```

The first default `php artisan` focused attempt hit PHP's 512 MB memory limit while decoding the large C25 JSON. The validated focused and all-param runtime commands used `php -d memory_limit=2048M`, consistent with earlier large all-param diagnostic runs.

## Candidate Result

All-param C26 metrics:

```text
R09 avg=-0.0217%, median=-0.0500%, p25=-2.1245%, win_rate=47.17%
G21 avg=+0.0045%, median=+0.9487%, p25=-0.4499%, win_rate=63.17%
G13 avg=-0.2257%, median=+0.4493%, p25=-0.0500%, win_rate=73.21%
G16 avg=-0.0789%, median=+0.9581%, p25=-1.7163%, win_rate=57.59%
```

Readiness gates:

```text
g21_primary_candidate_ready=true
g13_defensive_candidate_ready=true
g16_next_open_delay_component_ready=true
raw_ohlc_validation_required=true
derived_mfe_mae_dependency_detected=true
c27_catalog_candidate_implementation_recommended=true
c27_requires_raw_ohlc_validation_first=true
exit_rule_path_still_viable=true
selection_quality_revisit_needed=false
```

Stability:

```text
g21_param_pass_count=8
g21_param_fail_count=4
g21_month_pass_count=24
g21_month_fail_count=3
g21_bucket_pass_count=4
g21_bucket_fail_count=0
lookahead_violation_count=0
ambiguous_intraday_sequence_count=0
```

Interpretation:

```text
G21 is stable enough for C27 catalog-candidate implementation work, but only with raw OHLC/high-low validation first.
G13 is ready only as a defensive candidate/comparator because its p25 and win-rate are strong while average remains negative.
G16 is ready as a next-open-delay component/comparator, not as the primary global rule.
R15/R16 remain downside comparators and p25 guard references.
```

## Data Availability

C26 confirmed:

```text
c25_all_param_artifact_available=true
c23_all_param_artifact_available=true
c24_all_param_artifact_available=true
c21_path_artifact_available=true
g21_rows_available=true
g13_rows_available=true
g16_rows_available=true
r09_rows_available=true
r15_rows_available=true
r16_rows_available=true
canonical_baseline_available=true
c22_shadow_s06_available_or_recomputable=true
raw_high_low_available=false
d1_to_d5_ohlc_available=false
derived_mfe_mae_available=true
derived_mfe_mae_dependency_rate=57.52%
```

C26 does not claim raw OHLC validation. The C25/C21 artifacts available in the workspace do not prove raw D1-D5 open/high/low/close execution validation for preplanned intraday orders. Therefore C27 must add raw OHLC/high-low validation before catalog-candidate rule implementation can be considered complete.

## Boundary Preserved

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C26_CATALOG_CODE=NOT_CREATED
C26_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
NO_C01_TO_C25_MUTATION=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
NO_C23_REOPEN=true
NO_C24_REOPEN=true
NO_C25_REOPEN=true
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

## Final Decision

```text
C26_DECISION_STATUS=C26_RAW_OHLC_VALIDATION_REQUIRED
C26_PRIMARY_CANDIDATE=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
C26_DEFENSIVE_COMPARATOR=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
C26_NEXT_OPEN_DELAY_COMPARATOR=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
C26_C27_CATALOG_CANDIDATE_IMPLEMENTATION_RECOMMENDED=true
C26_C27_REQUIRES_RAW_OHLC_VALIDATION_FIRST=true
C26_CATALOG_IMPLEMENTATION_DEFERRED=true
C26_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C27_CATALOG_CANDIDATE_IMPLEMENTATION_WITH_RAW_OHLC_VALIDATION_FIRST_IS_ONLY
```
