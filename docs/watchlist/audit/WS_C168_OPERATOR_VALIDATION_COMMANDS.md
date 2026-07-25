# C168 Operator Validation Commands

## Source and Focused Tests

```powershell
php -l app/Application/Watchlist/Services/WeeklySwingWatchlistRuntimeService.php
php -l app/Console/Commands/Watchlist/GenerateWeeklySwingWatchlistCommand.php
php -l tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeServiceTest.php
php -l tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeStaticGuardTest.php

php vendor/bin/phpunit tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeServiceTest.php
php vendor/bin/phpunit tests/Unit/Watchlist/WeeklySwingWatchlistRuntimeStaticGuardTest.php

php artisan list --raw | Select-String '^watchlist:weekly-swing-generate'
```

## Controlled Runtime Execution

First inspect the target path and do not overwrite an unrelated artifact:

```powershell
$tradeDate = '2026-07-23'
$output = "storage/app/watchlist/runtime/c168-weekly-swing-watchlist-$tradeDate.json"
Test-Path -LiteralPath $output
```

Execute without production activation:

```powershell
php artisan watchlist:weekly-swing-generate `
  --trade-date=2026-07-23 `
  --progress `
  --no-interaction
```

Use `--overwrite` only after verifying that the exact date-specific artifact is the intended replacement.

## Artifact Verification

```powershell
$output = 'storage/app/watchlist/runtime/c168-weekly-swing-watchlist-2026-07-23.json'
$artifact = Get-Content -LiteralPath $output -Raw | ConvertFrom-Json

$artifact.status
$artifact.source_lineage | ConvertTo-Json
$artifact.watchlist_tickers | ConvertTo-Json
$artifact.watchlist_rows |
  Select-Object ticker_code,ticker_id,ticker_name,close_price,recommendation_rank,recommendation_score,plan_group,score_total |
  Format-Table -AutoSize
$artifact.pipeline_stages | ConvertTo-Json -Depth 8
Get-FileHash -LiteralPath $output -Algorithm SHA1
```

Expected local proof:

```text
STATUS=C168_WEEKLY_SWING_WATCHLIST_RUNTIME_INTEGRATION_GAP_REMEDIATION_PASSED_REAL_TICKER_WATCHLIST_GENERATED
TRADE_DATE_EFFECTIVE=2026-07-23
PUBLICATION_ID=67009
PUBLICATION_VERSION=5
RUN_ID=66354
WATCHLIST_TICKERS=FUTR,SMIL,INPS
OUTPUT_HASH=fa89e71a6087bf5bc0716ebd51b0d02b8c295521
OUTPUT_FILE_SHA1=61958F0C67D8719658AECB3A553E158898B36E30
PRODUCTION_RUNTIME_ACTIVATED=0
PLAN_CONFIRM_MUTATED=0
CONTROLLED_ROLLOUT_EXECUTED=0
OFFICIAL_OUTPUT_PUBLISHED=0
```

Ticker membership may change on a later readable publication. Acceptance depends on valid lineage and deterministic stage/output guards, not on preserving these three ticker symbols forever.

## Regression

```powershell
php vendor/bin/phpunit --filter 'WeeklySwingWatchlistRuntime|WatchlistMarketDataConsumerReadService|WatchlistCandidateUniverseService|WatchlistScoringService|WatchlistPlanGroupingService|WatchlistRecommendationService|MarketDataWatchlistReadModel|MarketDataPipelineIntegration'
php vendor/bin/phpunit tests/Unit/Watchlist
git diff --check
```
