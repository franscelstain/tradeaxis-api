# WS C25 Operator Validation Commands

C25 validation is complete for source, PHPUnit, focused runtime, and all-param runtime.

C25 remains IS-only. Do not run OOS, do not create a C25 catalog, and do not change canonical execution rules.

## Final C25 status

```text
C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C25=PASS
FULL_WATCHLIST_PHPUNIT=PASS
C25_FOCUSED_RUNTIME_PASS=true
C25_ALL_PARAM_RUNTIME_PASS=true
C25_CATALOG_IMPLEMENTATION_DEFERRED=true
C25_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## PHPUnit validation evidence

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC25"
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

......                                                              6 / 6 (100%)

Time: 00:03.011, Memory: 18.00 MB

OK (6 tests, 90 assertions)
```

Command:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Result:

```text
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

419 / 419 (100%)

Time: 00:06.969, Memory: 38.00 MB

OK (419 tests, 10446 assertions)
```

## Focused C25 diagnostic command

```powershell
php artisan watchlist:backtest-c25-no-signal-fallback-delay-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --param-ids=148,152,155 `
  --diagnostic-profile-codes=C25_G00_CANONICAL_BASELINE,C25_G01_C22_S06_SHADOW_BENCHMARK,C25_G02_C23_R09_BASELINE_BRIDGE,C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR,C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR,C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN,C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN,C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL,C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL,C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT,C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT,C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-focused.json `
  --overwrite
```

Focused result:

```text
status=PASS
reason_code=WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY
scope=IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-focused.json
artifact_hash=7bd6221bdd7993d9897a4d9bfaf23db22800f263
diagnostic_profile_count=12
profile_scope=EXPLICIT
evaluated_picks_count=394
path_missing_count=45
c23_input_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
c24_input_artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
no_signal_fallback_count=68
next_open_delay_count=66
no_signal_fallback_fix_found=1
next_open_delay_fix_found=1
distribution_balance_candidate_found=1
intraday_preplanned_order_candidate_found=1
exit_rule_path_still_viable=1
selection_quality_revisit_needed=0
c26_catalog_candidate_diagnostic_recommended=0
c25_catalog_implementation_deferred=1
c25_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## All-param C25 diagnostic command

```powershell
$profiles = "C25_G00_CANONICAL_BASELINE,C25_G01_C22_S06_SHADOW_BENCHMARK,C25_G02_C23_R09_BASELINE_BRIDGE,C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR,C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR,C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN,C25_G06_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN,C25_G07_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN_IF_MAE_LT_2PCT,C25_G08_NO_SIGNAL_FALLBACK_EXIT_D4_OPEN_IF_MAE_LT_2PCT,C25_G09_R09_PLUS_NO_SIGNAL_D3_DAMAGE_CONTROL,C25_G10_R09_PLUS_NO_SIGNAL_D4_DAMAGE_CONTROL,C25_G11_R09_PLUS_R15_STYLE_DOWNSIDE_CONTROL,C25_G12_R09_PLUS_R16_STYLE_DOWNSIDE_CONTROL,C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT,C25_G14_PREPLANNED_INTRADAY_TARGET_0_75PCT,C25_G15_PREPLANNED_INTRADAY_TARGET_1_00PCT,C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT,C25_G17_PREPLANNED_TARGET_0_75PCT_WITH_STOP_1_50PCT,C25_G18_PREPLANNED_TARGET_1_00PCT_WITH_STOP_2_00PCT,C25_G19_NEXT_OPEN_DELAY_ROWS_ONLY_R09,C25_G20_NO_SIGNAL_FALLBACK_ROWS_ONLY_R09,C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT"

php -d memory_limit=2048M artisan watchlist:backtest-c25-no-signal-fallback-delay-diagnose `
  --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C17_2026_06 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --diagnostic-profile-codes=$profiles `
  --input-c23-artifact=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --input-c24-artifact=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --input-c21-artifact=storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json `
  --progress `
  --output=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json `
  --overwrite
```

All-param result:

```text
status=PASS
reason_code=WS_BT_C25_NO_SIGNAL_FALLBACK_DELAY_DIAGNOSTIC_READY
scope=IS_ONLY_NO_SIGNAL_FALLBACK_AND_NEXT_OPEN_DELAY_DIAGNOSTIC
artifact_path=storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json
artifact_hash=d464c5bcce398c5405b069ef277d696a10598288
diagnostic_profile_count=22
profile_scope=EXPLICIT
evaluated_picks_count=1575
path_missing_count=45
c23_input_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
c24_input_artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
canonical_avg_ret_net=-0.0046903074630424
c22_s06_avg_ret_net=-0.00016239014891423
c23_r09_avg_ret_net=-0.00021743307264814
c23_r15_p25_ret_net=-0.0093088434012312
c23_r16_p25_ret_net=-0.0091951154943429
no_signal_fallback_count=295
next_open_delay_count=264
best_profile_code_by_avg=C25_G05_NO_SIGNAL_FALLBACK_EXIT_D3_OPEN
best_profile_code_by_median=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
best_profile_code_by_p25=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
best_profile_code_by_distribution_balance=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
best_no_signal_fallback_profile=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
best_next_open_delay_profile=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
no_signal_fallback_fix_found=1
next_open_delay_fix_found=1
distribution_balance_candidate_found=1
intraday_preplanned_order_candidate_found=1
exit_rule_path_still_viable=1
selection_quality_revisit_needed=0
c26_catalog_candidate_diagnostic_recommended=1
c25_catalog_implementation_deferred=1
c25_catalog_code=NOT_CREATED
oos_service_invoked=0
oos_repository_invoked=0
oos_executed=0
production_ready=0
```

## Final candidate review command

```powershell
$run = Get-Content storage/app/watchlist/backtest/c25-no-signal-fallback-delay-diagnostic-all-param.json | ConvertFrom-Json

$codes = @(
  "C25_G02_C23_R09_BASELINE_BRIDGE",
  "C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR",
  "C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR",
  "C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT",
  "C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT",
  "C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT"
)

$run.profile_summary |
  Where-Object { $codes -contains $_.profile_code } |
  Select-Object `
    profile_code,
    profile_family,
    evaluated_picks_count,
    @{n='avg_pct';e={[math]::Round($_.avg_ret_net * 100, 4)}},
    @{n='median_pct';e={[math]::Round($_.median_ret_net * 100, 4)}},
    @{n='p25_pct';e={[math]::Round($_.p25_ret_net * 100, 4)}},
    @{n='win_rate_pct';e={[math]::Round($_.win_rate * 100, 2)}},
    lookahead_violation_count,
    ambiguous_intraday_sequence_count,
    preplanned_order_count |
  Format-List
```

Final candidate evidence:

```text
C25_G02_C23_R09_BASELINE_BRIDGE: avg=-0.0217%, median=-0.0500%, p25=-2.1245%, win_rate=47.17%
C25_G03_C23_R15_DOWNSIDE_COMBO_COMPARATOR: avg=-0.0855%, median=-0.0500%, p25=-0.9309%, win_rate=41.59%
C25_G04_C23_R16_DOWNSIDE_COMBO_COMPARATOR: avg=-0.0981%, median=-0.0500%, p25=-0.9195%, win_rate=44.32%
C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT: avg=-0.2257%, median=+0.4493%, p25=-0.0500%, win_rate=73.21%, ambiguous=0
C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT: avg=-0.0789%, median=+0.9581%, p25=-1.7163%, win_rate=57.59%, ambiguous=0
C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT: avg=+0.0045%, median=+0.9487%, p25=-0.4499%, win_rate=63.17%, ambiguous=0
```

## Final next step

```text
NEXT_STEP=C26_CATALOG_CANDIDATE_DIAGNOSTIC_IS_ONLY
PRIMARY_C26_CANDIDATE=C25_G21_COMBINED_R09_INTRADAY_TARGET_1PCT_AND_NO_SIGNAL_D3_EXIT
DEFENSIVE_COMPARATOR=C25_G13_PREPLANNED_INTRADAY_TARGET_0_50PCT
NEXT_OPEN_DELAY_COMPARATOR=C25_G16_PREPLANNED_INTRADAY_TARGET_1_50PCT
DOWNSIDE_COMPARATORS=C23_R15,C23_R16
```

C26 must not run OOS, must not promote production, and must not mutate C01-C25.
