# C169 Operator Validation Commands

## Migration and command registration

```powershell
php artisan migrate:status | Select-String '2026_07_24_000001'
php artisan list --raw | Select-String '^watchlist:weekly-swing'
```

Expected commands:

```text
watchlist:weekly-swing-generate
watchlist:weekly-swing-paramset-import-draft
watchlist:weekly-swing-paramset-promote
```

## Source validation

```powershell
$files = @(
  'database/migrations/2026_07_24_000001_create_watchlist_runtime_paramset_and_plan_schema.php',
  'app/Application/Watchlist/Services/WeeklySwingParamsetValidator.php',
  'app/Application/Watchlist/Services/WeeklySwingParamsetBacktestBindingVerifier.php',
  'app/Application/Watchlist/Services/WeeklySwingParamsetDraftImportService.php',
  'app/Application/Watchlist/Services/WeeklySwingParamsetPromotionService.php',
  'app/Infrastructure/Persistence/Watchlist/WatchlistParamsetRepository.php',
  'app/Console/Commands/Watchlist/ImportWeeklySwingParamsetDraftCommand.php',
  'app/Console/Commands/Watchlist/PromoteWeeklySwingParamsetCommand.php'
)

$files | ForEach-Object { php -l $_ }
```

## Idempotent DRAFT import

```powershell
php artisan watchlist:weekly-swing-paramset-import-draft `
  --input=docs/watchlist/development/implementation/db/PARAMSET_WS_ACTIVE_EXAMPLE.json `
  --bt-param-id=1 `
  --catalog-code=WS_BT_GRID_BOOTSTRAP_2026_06 `
  --source-note='C169 canonical DRAFT binding; no promotion without persisted passing OOS proof' `
  --no-interaction
```

Expected result:

```text
status=DRAFT_PERSISTED
reason_code=WS_PARAMSET_DRAFT_PERSISTED
param_set_id=1
paramset_status=DRAFT
bt_param_id=1
catalog_code=WS_BT_GRID_BOOTSTRAP_2026_06
catalog_version=R1
catalog_hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
row_code=01_BASELINE
row_hash=d0ad2af3a061c42e879922e621ed265b17cb8847
production_ready=0
```

Run the same command again. It must return the same paramset id and must not create a second exact DRAFT.

## Fail-closed promotion proof

The local official OOS table currently contains no row. The command below must fail with exit code 1 and must leave the DRAFT unchanged:

```powershell
php artisan watchlist:weekly-swing-paramset-promote `
  --param-set-id=1 `
  --bt-param-id=1 `
  --oos-id=1 `
  --no-interaction

$LASTEXITCODE
```

Expected result:

```text
status=BLOCKED
reason_code=WS_PARAMSET_PROMOTION_OOS_PROOF_MISSING
production_ready=0
LASTEXITCODE=1
```

## Tests

```powershell
php vendor/bin/phpunit --filter 'WeeklySwingParamset'
php vendor/bin/phpunit --filter 'WeeklySwingParamset|WeeklySwingWatchlistRuntime|WatchlistMarketDataConsumerReadService|WatchlistCandidateUniverseService|WatchlistScoringService|WatchlistPlanGroupingService|WatchlistRecommendationService|WatchlistBacktestOosProof|MarketDataWatchlistReadModel|MarketDataPipelineIntegration'
php vendor/bin/phpunit tests/Unit/Watchlist
git diff --check
```

Validation results:

```text
PHPUNIT_C169_FOCUSED=OK (22 tests, 106 assertions)
PHPUNIT_C168_C169_INTEGRATION_REGRESSION=OK (143 tests, 1990 assertions)
FULL_WATCHLIST_PHPUNIT=OK (7064 tests, 47699 assertions)
GIT_DIFF_CHECK=PASS
```

The promotion command's nonzero exit is expected evidence, not a failed C169 implementation.
