# Legacy Semantic Extract — LX-MD-0179-CTX-03

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `CONTEXT`
- Source range: `L41-L117`
- Extract body SHA1: `A63ED78E7128B8BC995778A41B4CBF7412ECB31C`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Official role
`market-data:backfill:lifecycle` adalah command **FULL LIFECYCLE RANGE ORCHESTRATION** untuk range trading date, terutama `source_mode=api`.

Command ini berbeda dari `market-data:backfill`:
- `market-data:backfill` tetap import-only.
- `market-data:backfill:lifecycle` menjalankan import -> promote -> evidence -> replay fixture -> replay verify per requested trading date.

## API range acquisition
Untuk `source_mode=api`, command memakai `source_acquisition_mode=range_window`.

Contoh:

```text
php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-31 --source_mode=api --with-evidence --with-replay
```

Expected planning fields:
- `source_acquisition_mode=range_window`
- `requested_start`
- `requested_end`
- `warmup_start`
- `window_count`
- `estimated_http_requests`
- `ticker_count`
- `trading_dates`
- `mode`
- `with_evidence`
- `with_replay`

Warmup rule:
- `warmup_start` must be resolved from `market_calendar` using configured `MARKET_DATA_API_BACKFILL_WARMUP_TRADING_DAYS`, ending at the first requested trading date.
- `warmup_start` must not be calculated from `subDays()` or fixed calendar-day subtraction.
- If the first requested date is not an active trading day, or the trading calendar cannot provide the required prior warmup window, the lifecycle command must fail fast instead of publishing indicators with starved history.
- Warmup rows may be acquired/imported as support history, but they are not requested publication targets unless they are also inside the requested date range.

## Manual file historical range acquisition
`source_mode=manual_file` boleh memakai file per tanggal dari local source directory atau satu file eksplisit gabungan lewat `--input_file` **hanya untuk workflow terencana seperti historical development fill, historical backfill, correction/republication, atau replay-oriented reconstruction**. Kemampuan range ini **bukan operational continuity path**.

Untuk **operational recovery** setelah kegagalan provider/daily acquisition, `manual_file` dibatasi sebagai **controlled one-date rescue**; operator tidak boleh memakai range multi-hari sebagai pengganti provider continuity.

Contoh satu CSV gabungan untuk **planned historical backfill**:

```text
php artisan market-data:backfill:lifecycle 2026-06-01 2026-06-30 --source_mode=manual_file --input_file=storage/app/market_data/manual/eod-bars-2026-06.csv --with-evidence --with-replay
```

CSV gabungan wajib tetap memiliki kolom `trade_date`. Saat workflow historical yang sah menjalankan lifecycle range, adapter manual file memfilter baris berdasarkan requested `trade_date` yang sedang diproses, lalu promote/coverage/evidence/replay tetap berjalan per tanggal. Setiap penerimaan data tetap tunduk pada immutable source-observation, correction/revision, coverage, lineage, config, seal, dan replay contract; range capability tidak memberi izin overwrite atau bypass gate.

## Lifecycle order
Per requested `trade_date`, command runs chronologically:
- immutable observations persist and unsealed candidate bars are materialized
- promote and coverage gate
- indicators
- eligibility
- hash
- seal
- finalize
- evidence export
- replay fixture generate
- replay verify

Replay fixture/verify is allowed only when the run is `SUCCESS`, `READABLE`, coverage `PASS`, sealed, and evidence export succeeded.

## Resume/checkpoint
The command writes:
- `market_data_backfill_lifecycle_summary.json`
- `lifecycle_checkpoint.json`
- `source_acquisition_cache.json` for API range acquisition

Resume behavior:

```text
php artisan market-data:backfill:lifecycle 2026-05-01 2026-05-07 --source_mode=api --resume --only-failed -vvv
```

The cache/checkpoint allows already acquired windows and verified dates to be skipped by the lifecycle orchestrator.


<!-- LEGACY_EXTRACT_BODY_END -->
