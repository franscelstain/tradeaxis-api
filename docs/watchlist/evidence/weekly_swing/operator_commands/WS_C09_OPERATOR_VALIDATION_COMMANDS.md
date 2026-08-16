# WS C09 Operator Validation Commands

Status: OPERATOR_REFERENCE / C09_RUNTIME_DIAGNOSTIC_ONLY / OOS_NOT_RUN

## PHP Lint

Commands:

```text
php -l app\Application\Watchlist\Services\WatchlistBacktestIsFailureDrilldownService.php
php -l app\Console\Commands\Watchlist\RunBacktestIsDiagnoseBatchCommand.php
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownServiceTest.php
php -l tests\Unit\Watchlist\WatchlistBacktestIsFailureDrilldownStaticGuardTest.php
```

PASS criteria:

```text
No syntax errors detected
exit_code=0
```

## PHPUnit

Commands:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestIsFailureDrilldown"
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC07"
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected markers from the C09 implementation run:

```text
WatchlistBacktestIsFailureDrilldown = OK (6 tests, 118 assertions)
WatchlistBacktestC07 = OK (10 tests, 376 assertions)
Full Watchlist = OK (302 tests, 6597 assertions)
exit_code=0
```

## Read-Only Source Coverage Audit

Command:

```text
php -r "`$app=require 'bootstrap/app.php'; `$db=`$app->make('db'); `$out=[]; `$out['corporate_actions_source_total']=`$db->table('market_data_corporate_actions')->whereBetween('action_date',['2023-01-02','2025-05-21'])->count(); `$out['trading_status_source_total']=`$db->table('market_data_trading_status_events')->whereBetween('trade_date',['2023-01-02','2025-05-21'])->count(); `$q=function() use (`$db){ return `$db->table('eod_indicators')->whereBetween('trade_date',['2023-01-02','2025-05-21']); }; `$out['indicator_total']=`$q()->count(); `$out['indicator_corporate_action_types_present']=`$q()->whereNotNull('corporate_action_types')->where('corporate_action_types','<>','')->count(); `$out['indicator_event_risk_reasons_present']=`$q()->whereNotNull('event_risk_reasons')->where('event_risk_reasons','<>','')->count(); `$out['indicator_trading_status_code_present']=`$q()->whereNotNull('trading_status_code')->where('trading_status_code','<>','')->count(); echo json_encode(`$out, JSON_PRETTY_PRINT).PHP_EOL;"
```

Expected markers from the C09 implementation run:

```text
corporate_actions_source_total=262
trading_status_source_total=1469
indicator_total=501386
indicator_corporate_action_types_present=243
indicator_event_risk_reasons_present=28746
indicator_trading_status_code_present=69560
```

This command is read-only. It must not write artifacts or run OOS.

## Batched C07 Nullable Context Drilldown

Command:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c09-batched-c07-nullable-context-drilldown --summary=storage/app/watchlist/backtest/c09-batched-c07-nullable-context-summary.csv --overwrite
```

Expected markers:

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
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
exit_code=0
```

PASS criteria:

```text
summary CSV is written
12 scoped JSON artifacts are written
all summary rows status=DONE
all summary rows missing_runtime_evidence_fields is empty
all summary rows nullable_runtime_no_positive_evidence_fields=corporate_action_flag|corporate_action_types|event_risk_reasons
all summary rows next_focus=STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG
all summary rows next_decision=NEXT_CATALOG_NOT_DESIGNED
all summary rows oos_executed=0
all summary rows production_ready=0
no best IS binding is created
no OOS command is run
```

Current implementation evidence:

```text
summary_path=storage/app/watchlist/backtest/c09-batched-c07-nullable-context-summary.csv
summary_sha1=4A317C890F416619FA2F24396D1EC9DDDE8CC3AB
docs_artifact=docs/watchlist/evidence/weekly_swing/artifacts/c09-batched-c07-nullable-context-summary.csv
```

## OOS Guard

OOS command must not be run in this session.

Fail validation if any command writes or claims:

```text
oos_executed=1
production_ready=1
param_id_best_is=<non-empty>
best_is_binding_hash=<non-empty>
```

C07 remains rejected as a strategy-quality catalog. C09 is runtime diagnostic evidence only.
