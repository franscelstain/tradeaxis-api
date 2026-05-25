# `market-data:backfill`

## Official role
`market-data:backfill` adalah command **IMPORT PHASE** untuk **rentang tanggal spesifik**.

## Date-driven contract
Backfill adalah jalur inti historical ingestion.
Command ini wajib dipahami sebagai mekanisme resmi untuk memproses range trading date apa pun yang sah, bukan sekadar fitur tambahan untuk data recent.

`start_date` dan `end_date` adalah input operator yang wajib. Implementasi boleh membuat argumen parser menjadi opsional hanya agar command dapat mengembalikan `status=BLOCKED` dan `reason_code=COMMAND_MISSING_REQUIRED_INPUT` ketika input hilang; itu tidak mengubah contract bahwa kedua tanggal wajib diberikan.

## Contract
Command ini hanya boleh menjalankan:
- iterasi trading date
- acquisition/import bars per date
- invalid-row persistence
- telemetry persistence
- bars coverage evidence minimum

Command ini **tidak boleh** menjalankan:
- indicators
- eligibility
- hash
- seal
- finalize

## Operator meaning
Selesainya `market-data:backfill` berarti data import range sudah dicoba/dicatat.
Itu **bukan** berarti semua tanggal di range tersebut sudah published/readable.

Invalid atau missing date harus gagal aman dengan `status=BLOCKED`; date format salah harus memakai `COMMAND_INVALID_DATE_FORMAT`, dan input wajib yang hilang harus memakai `COMMAND_MISSING_REQUIRED_INPUT`.

---

# `market-data:backfill:lifecycle`

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

## Lifecycle order
Per requested `trade_date`, command runs chronologically:
- import/acquired bars persist
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

## Runtime validation status
This command surface is added with static/unit proof. Production readiness for a specific range still requires an executed operator proof against the target runtime database/provider.

## Source retry diagnostics
For `source_mode=api`, the lifecycle command also writes:
- `source_acquisition_checkpoint.json`
- `source_acquisition_diagnostics.json`

`--resume --only-failed` is a source acquisition retry mode for failed window/ticker checkpoints. Its output must include:
- `failed_checkpoint_total`
- `failed_checkpoint_eligible`
- `failed_checkpoint_retried`
- `retry_success_count`
- `retry_failed_count`
- `failed_checkpoint_skipped`
- `skipped_failed_checkpoint_reasons`

Retry state meaning:
- `RETRY_SUCCESS`: all eligible failed checkpoints were retried successfully.
- `PARTIAL_RETRY_SUCCESS`: some eligible failed checkpoints succeeded and some still failed.
- `FAILED_RETRY_BLOCKED`: retry remained blocked at ticker/window scope and is not readable/replayable.
- `NO_FAILED_CHECKPOINT`: no failed source acquisition checkpoint exists for the requested resume scope.
- `SYSTEMIC_FAILED`: reserved for true global/provider/config failures, not a single ticker HTTP 400 retry failure.

Latest runtime proof for `2026-05-01` to `2026-05-07`:
- Plan: `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`.
- Diagnose-source: `PARTIAL_SUCCESS`, `failed_ticker_count=1`, `failed_window_count=1`.
- Full lifecycle: 4/4 requested dates readable with evidence exported, fixture generated, and replay verified.
- Resume-only-failed: `FAILED_RETRY_BLOCKED` for `WBSA` HTTP 400 in window `2026-01-01` to `2026-03-31`, with failed checkpoint/retry counts reported explicitly.
