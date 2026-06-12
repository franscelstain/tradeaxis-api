# WS C08 Runtime Payload And Batched C07 Drilldown Final Result

Status: C08_RUNTIME_PAYLOAD_ENRICHED / C07_BATCHED_IS_DRILLDOWN_EXECUTED / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_RUN

## Scope

This session did not create a new strategy catalog. C08 is a runtime diagnostic/enrichment session after C07 failed IS quality.

Hard boundaries:

```text
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
strategy_catalog_created=false
OOS=NOT_RUN
production_ready=0
best_of_failed_binding_selected=false
```

R1/R2/C01/C02/C03/C04/C05/C06/C07 remain immutable historical evidence. This session did not change prior catalog rows, prior catalog hashes, prior IS quality results, or any OOS boundary.

## Runtime Payload Enrichment

The market-data repository already selected source-backed event-risk context fields:

```text
corporate_action_flag
corporate_action_types
trading_status_code
is_suspended
is_uma
event_risk_flag
event_risk_reasons
```

The C08 code change carries the string context fields through the watchlist runtime diagnostic payload:

```text
corporate_action_types
trading_status_code
event_risk_reasons
```

It also derives `corporate_action_flag=1` only when `corporate_action_types` is non-empty and the explicit flag is missing. It does not convert source absence into `0`; nullable market-data semantics are preserved.

## Batched C07 Drilldown

New diagnostic command:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c08-batched-c07-drilldown --summary=storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv --overwrite
```

Executed result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
catalog_version=C07
catalog_count=12
catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_path=D:\Laravel\watchlist\tradeaxis-api/storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv
output_dir=D:\Laravel\watchlist\tradeaxis-api/storage/app/watchlist/backtest/c08-batched-c07-drilldown
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

The batch command writes one IS-only JSON artifact per C07 row and a summary CSV. It does not call OOS service, OOS repository, active/latest catalog fallback, or promotion code.

## Artifact Summary

Summary CSV:

```text
storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv
docs/watchlist/audit/_artifacts/c08-batched-c07-drilldown-summary.csv
summary_sha1=49101D6AA702A898A3F691A7553823A8DFB2F125
json_artifact_count=12
```

Metric ranges from the executed batch:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
next_focus=RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

Best observed median row in the batch:

```text
param_id=102
row_code=05_ANTI_REVERSAL_NOT_OVEREXTENDED
picks_count=1017
median_ret_net_top=-0.6993%
p25_ret_net_top=-3.4831%
month_win_rate_min=25.00%
artifact_hash=dfd855c441881914c83720eeaf6ee2cfa54856a6
file_sha1=88c66760c8255f5d9c7fe5b0def8309d64f13923
```

Best observed p25 downside row in the batch:

```text
param_id=106
row_code=09_LOW_ATR_RANGE_SECTOR
picks_count=986
median_ret_net_top=-0.7569%
p25_ret_net_top=-3.4276%
month_win_rate_min=20.59%
artifact_hash=2939e990f1654ec366417ad5269ffee2b82818b8
file_sha1=e2d738c721f198e9e50aa86843021fa624de97a9
```

Both best rows remain below the locked strategy-quality gates: median return is negative, p25 downside is below `-3%`, and monthly win-rate minimum is far below `45%`.

## Runtime Field Availability

After C08 enrichment, the C07 batched drilldown confirms these fields are available in runtime evidence:

```text
close_to_hh20_pct
close_to_ll20_pct
dv20_idr
event_risk_flag
is_suspended
is_uma
range_20_pct
range_position_20_pct
roc5
roc10
roc20
rs_20_vs_sector
score_components
sector_code
sector_roc20
sector_rs_20_vs_ihsg
trading_status_code
vol_ratio
```

Remaining missing runtime evidence fields across all 12 C07 scoped artifacts:

```text
corporate_action_flag
corporate_action_types
event_risk_reasons
```

Interpretation: the pass-through gap for `trading_status_code` and core event-risk flags is closed, but evaluated C07 trades still do not expose source-backed corporate-action context. This remains a runtime/data coverage gap and must not be treated as strategy-quality success.

## Decision

```text
C07_REMAINS_REJECTED_AS_STRATEGY_QUALITY_CATALOG
C08_STRATEGY_CATALOG_CREATED=false
NEXT_CATALOG_NOT_DESIGNED
OOS_NOT_ELIGIBLE
OOS_NOT_RUN
NOT_PRODUCTION_READY
```

C07 has no valid IS candidate, no `param_id_best_is`, and no `best_is_binding_hash`. The C08 diagnostic session therefore does not unlock OOS.

## Validation

Executed in this workspace:

```text
php -l app\Application\Watchlist\Services\WatchlistMarketDataConsumerReadService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistCandidateUniverseService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistScoringService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestStrategyService.php = PASS
php -l app\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService.php = PASS
php -l app\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand.php = PASS
php -l app\Console\Kernel.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownServiceTest.php = PASS
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownStaticGuardTest.php = PASS
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown" = PASS / OK (5 tests, 107 assertions)
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07" = PASS / OK (10 tests, 376 assertions)
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (301 tests, 6586 assertions)
```

OOS was not run and must not be claimed PASS.
