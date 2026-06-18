# WS C24 C22 Shadow Gap Bridge Diagnostic

C24 is an IS-only diagnostic that explains the remaining gap between the best C23 non-lookahead rule candidate and the C22 first-profitable-close shadow benchmark. It reads the completed C23 all-param artifact only; it does not recompute selection, does not read new price paths, does not create a catalog, does not run OOS, and does not change production readiness.

The C24 candidate under review is:

```text
C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
```

C24 treats C22 `S06_FIRST_PROFITABLE_CLOSE_EXIT` as a benchmark carried inside the C23 artifact. C22 S06 is still not a production rule because it exits on the first profitable close inside the future path.

## Current status

```text
C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_SOURCE_IMPLEMENTED=true
PHPUNIT_C24_FILTER=PASS
C24_COMMAND_REGISTERED=PASS
C24_RUNTIME_VALIDATED=true
C24_ALL_PARAM_RUNTIME_PASS=true
C24_ARTIFACT_HASH=feabfbe720d39155a3d741e509cc69cade3ef31c
C24_INPUT_C23_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C24_CANDIDATE_PROFILE=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
C24_EVALUATED_PICKS=1575
C24_DECISION_STATUS=C24_C22_SHADOW_GAP_STILL_MATERIAL
C24_GAP_BRIDGE_EXPLAINED=true
C24_DOMINANT_GAP_COMPONENT=no_rule_profit_signal_before_fallback
C24_CATALOG_CODE=NOT_CREATED
C24_CATALOG_IMPLEMENTATION_DEFERRED=true
C24_CATALOG_ALLOWED=false
OOS_NOT_RUN=true
production_ready=0
NO_C01_TO_C23_MUTATION=true
```

C24 completed the requested gap bridge. The remaining C22 shadow gap is explained, but it is still material. C24 therefore does not authorize catalog creation, OOS, promotion, or production readiness.

## Implemented source components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC24C22ShadowGapBridgeDiagnoseCommand.php

Command signature:
watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC24StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## C24 artifact surface

C24 writes a compact aggregate artifact. It intentionally does not copy the large C23 `pick_rule_rows` array into its output.

```text
artifact_type
source_evidence
candidate_profile_code
candidate_profile_summary
canonical_summary
c22_shadow_s06_summary
metric_bridge_summary
row_gap_summary
gap_component_summary
segment_summaries
decision
safety_boundaries
artifact_hash
```

The runtime artifact is:

```text
storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json
artifact_size_bytes=35555
artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
```

## Metric bridge result

```text
candidate_avg_ret_net=-0.00021743307264814
candidate_median_ret_net=-0.00049987503124219
candidate_p25_ret_net=-0.021244659600659
candidate_win_rate=0.47174603174603

c22_shadow_s06_avg_ret_net=-0.00016239014891423
c22_shadow_s06_median_ret_net=0.0042799597180262
c22_shadow_s06_p25_ret_net=-0.0082526173206962
c22_shadow_s06_win_rate=0.59619047619048

avg_gap_vs_c22_s06=0.000055042923733914
median_gap_vs_c22_s06=0.0047798347492684
p25_gap_vs_c22_s06=0.012992042279963
win_rate_gap_vs_c22_s06=0.12444444444444

avg_capture_ratio_vs_c22_s06=0.98784365528006
median_capture_ratio_vs_c22_s06=0.43032380598996
p25_capture_ratio_vs_c22_s06=0.16167366271912
win_rate_capture_ratio_vs_c22_s06=0.38940809968847

rows_where_c22_beats_candidate_rate=0.35492063492063
```

Interpretation:

```text
C23 R09 almost closes the average-return gap versus C22 S06.
C23 R09 does not close the median, p25, or win-rate gap enough.
The remaining shadow gap is still material.
```

## Gap component result

```text
candidate_matches_or_beats_c22:
  count=1016
  avg_gap_c22_minus_candidate=-0.009983558340572178
  rows_where_c22_beats_candidate_rate=0

next_open_delay_after_close_signal:
  count=264
  avg_gap_c22_minus_candidate=0.014125436205220044
  median_gap_c22_minus_candidate=0.007998000499875032
  rows_where_c22_beats_candidate_rate=1

no_rule_profit_signal_before_fallback:
  count=295
  avg_gap_c22_minus_candidate=0.022036856680420897
  median_gap_c22_minus_candidate=0.01743749060480032
  rows_where_c22_beats_candidate_rate=1
```

Dominant actual gap component:

```text
no_rule_profit_signal_before_fallback
```

C24 excludes `candidate_matches_or_beats_c22` from dominant-gap selection because it is a non-gap bucket.

## Worst month slices by average C22-minus-candidate gap

```text
2025-03 avg_gap=0.010114821520934032 c22_beats_rate=0.36666666666666664
2024-02 avg_gap=0.009980920219381009 c22_beats_rate=0.6833333333333333
2024-09 avg_gap=0.00856234587403824 c22_beats_rate=0.47368421052631576
2023-04 avg_gap=0.005438521846569727 c22_beats_rate=0.3
2023-10 avg_gap=0.005002921507205143 c22_beats_rate=0.42105263157894735
```

These slices are diagnostic only. They are not month filters or blacklist instructions.

## Validation evidence in this source patch

Actually run in this session:

```text
php -l app/Application/Watchlist/Services/WatchlistBacktestC24C22ShadowGapBridgeDiagnosticService.php
No syntax errors detected

php -l app/Console/Commands/Watchlist/RunBacktestC24C22ShadowGapBridgeDiagnoseCommand.php
No syntax errors detected

vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC24"
OK (4 tests, 64 assertions)

vendor\bin\phpunit.bat tests/Unit/Watchlist --filter "WatchlistBacktestC23"
OK (6 tests, 490 assertions)

vendor\bin\phpunit.bat tests/Unit/Watchlist
OK (413 tests, 10356 assertions)

php artisan list | Select-String -Pattern "watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose"
command_registered=true
```

C24 runtime command:

```powershell
php -d memory_limit=2048M artisan watchlist:backtest-c24-c22-shadow-gap-bridge-diagnose `
  --input=storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json `
  --output=storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json `
  --overwrite
```

Runtime result:

```text
status=PASS
reason_code=WS_BT_C24_C22_SHADOW_GAP_BRIDGE_DIAGNOSTIC_READY
artifact_hash=feabfbe720d39155a3d741e509cc69cade3ef31c
c23_artifact_hash=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
candidate_profile_code=C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
evaluated_picks_count=1575
dominant_gap_component=no_rule_profit_signal_before_fallback
c24_gap_bridge_explained=1
c24_catalog_code=NOT_CREATED
oos_executed=0
production_ready=0
```

## Preserved boundaries

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C24_CATALOG_CODE=NOT_CREATED
C24_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_C01_TO_C23_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
NO_C23_REOPEN=true
catalog_allowed=false
oos_allowed=false
reads_c23_artifact_only=true
future_path_price_used_for_selection=false
candidate_ret_used_for_selection=false
c22_shadow_s06_used_for_selection=false
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
```

## C24 conclusion

```text
C24_GAP_BRIDGE_EXPLAINED=true
C24_DECISION_STATUS=C24_C22_SHADOW_GAP_STILL_MATERIAL
C24_CATALOG_ALLOWED=false
C24_OOS_ALLOWED=false
NEXT_STEP=LATER_DIAGNOSTIC_ONLY_FOR_NEXT_OPEN_DELAY_AND_NO_SIGNAL_FALLBACK
```

C24 explains why C23 R09 still falls short of C22 S06: the average gap is almost bridged, but no-signal fallback rows and next-open delay after close-signal rows keep median, p25, and win-rate gaps material. The next work, if continued, should remain diagnostic only and focus on those two components.
