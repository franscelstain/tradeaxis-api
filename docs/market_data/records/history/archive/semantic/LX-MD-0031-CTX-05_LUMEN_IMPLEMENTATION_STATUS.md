# Legacy Semantic Extract — LX-MD-0031-CTX-05

- Source ID: `LS-MD-0031`
- Original path: `audit/LUMEN_IMPLEMENTATION_STATUS.md`
- Original SHA1: `3FDDFFF0B53D431DAA71A8545F87173445A616F7`
- Extract role: `CONTEXT`
- Source range: `L4132-L4181`
- Extract body SHA1: `D228F0F4F605136BCFC778E0A9FF46143E966C92`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## 2026-05-25 - API BACKFILL RANGE FULL LIFECYCLE ORCHESTRATION

[STATUS]
- Historical interim status was not accepted as production runtime proof before lifecycle runtime evidence was captured.
- Later lifecycle command proof, command-surface proof, and full global lock entries supersede this interim production-runtime claim.
- Static/unit proof had been added for range-window acquisition, command surface separation, replay gating, and forbidden fallback patterns.

[ROOT_CAUSE_CONFIRMED]
- Existing `market-data:backfill` is import-only and loops each trading date through `MarketDataBackfillService`.
- Existing Yahoo API source acquisition fanned out per ticker for a single requested date and its parser returned the first row matching that one `trade_date`.
- Existing `MarketDataPipelineService::completeIngest()` called bars ingest inside `DB::transaction`; because `EodBarsIngestService::ingest()` performed source fetch internally, HTTP acquisition could be held inside the DB transaction.

[IMPLEMENTED_CHANGE]
- Added `PublicApiEodBarsAdapter::fetchOrLoadEodBarsRange()` for Yahoo Finance range-window acquisition.
- Yahoo URLs now support `period1` / `period2` precision for arbitrary date windows.
- Yahoo chart parser now reads all timestamp/quote rows, converts timestamps using the exchange/platform timezone, filters requested trading dates, skips invalid null OHLCV rows, and groups output by `trade_date`.
- Added `ApiBackfillRangeAcquisitionService` to split configurable windows and produce `source_acquisition_batch_id`, `source_acquisition_mode=range_window`, warmup/requested/window context, estimated request count, rows grouped by date, and date-level acquisition telemetry.
- Added `BackfillLifecycleOrchestrator` and command `market-data:backfill:lifecycle` for date-chronological import -> promote -> evidence -> fixture -> replay verification orchestration.
- Added `EodBarsIngestService::acquireSourceRows()` and `ingestAcquiredRows()`; `MarketDataPipelineService::completeIngest()` now performs source acquisition before the short DB persistence transaction.
- Added `MarketDataPipelineService::importDailyFromAcquiredRows()` for range acquisition reuse without per-date Yahoo refetch.

[CONTRACT_NOT_CHANGED]
- Existing `market-data:backfill` remains import-only.
- `manual_file` single-date and range behavior remains per-date/per-file.
- Existing `market-data:daily` remains single-date import semantics.
- Each requested `trade_date` still receives its own run context; range acquisition does not create a single run for the whole range.

[NEW_CONFIG]
- `MARKET_DATA_API_BACKFILL_WINDOW_DAYS=90`
- `MARKET_DATA_API_BACKFILL_WARMUP_DAYS=120`
- `MARKET_DATA_API_BACKFILL_CONCURRENCY=5`
- `MARKET_DATA_API_BACKFILL_MAX_DATES_PER_RUN=20`
- `MARKET_DATA_API_BACKFILL_COLLECT_ALL_ERRORS=false`
- `MARKET_DATA_API_BACKFILL_DEFAULT_ERROR_POLICY=stop_on_error`

[VALIDATION_ADDED]
- `tests/Unit/MarketData/PublicApiEodBarsAdapterTest.php` covers Yahoo multi-date grouping and no date fanout.
- `tests/Unit/MarketData/ApiBackfillRangeAcquisitionServiceTest.php` covers one-window plans, split windows, and window-by-ticker request scaling.
- `tests/Unit/MarketData/ApiBackfillLifecycleStaticGuardTest.php` covers lifecycle command registration, import-only backfill separation, range-window service usage, replay gating, and no `MAX(trade_date)` fallback in new range lifecycle code.
- Command surface proof: `php artisan list market-data` shows `market-data:backfill:lifecycle`.
- Plan proof: `php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --plan` returned `source_acquisition_mode=range_window`, `warmup_start=2026-01-01`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`, `status=PLAN_ONLY`.
- Migration proof: `php artisan migrate --env=testing --force` -> `Nothing to migrate.`
- Full unit proof: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (542 tests, 8371 assertions), Time 00:19.424, Memory 42.00 MB.

[REMAINING_RISK]
- Warmup rows are imported as import-only support rows when present so indicator history can resolve from persisted bars; they are not promoted/evidence/replayed as requested targets by the lifecycle command.
- Runtime provider/network behavior, DB migration compatibility, and full lifecycle command execution still require operator runtime validation before this scope can be marked `DONE`.

---


<!-- LEGACY_EXTRACT_BODY_END -->
