# `market-data:backfill`

## Official role
`market-data:backfill` adalah command **IMPORT PHASE** untuk **rentang tanggal spesifik**.

## Date-driven contract
Backfill adalah jalur inti historical ingestion.
Command ini wajib dipahami sebagai mekanisme resmi untuk memproses range trading date apa pun yang sah, bukan sekadar fitur tambahan untuk data recent.

`start_date` dan `end_date` adalah input operator yang wajib. Implementasi boleh membuat argumen parser menjadi opsional hanya agar command dapat mengembalikan `status=BLOCKED` dan `reason_code=COMMAND_MISSING_REQUIRED_INPUT` ketika input hilang; itu tidak mengubah contract bahwa kedua tanggal wajib diberikan.

## V2 import boundary
For every date, acquisition first appends immutable source observations/attempts and resolves stable `listing_id`. Import may build or merge an **unsealed candidate projection**; it may not overwrite source observations, sealed history, or readable publication artifacts. A historical date that is already readable requires correction/republication lineage before changed truth can become current.

## Contract
Command ini hanya boleh menjalankan:
- iterasi trading date
- immutable source acquisition/import evidence per date
- stable-listing mapping and unsealed candidate-bar materialization
- invalid/rejected evidence persistence
- telemetry persistence
- bars delivery/coverage evidence minimum

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

Historical runtime proof for `2026-05-01` to `2026-05-07` (retained as execution history; not V2 conformance proof):
- Plan: `source_acquisition_mode=range_window`, `window_count=2`, `estimated_http_requests=1826`, `ticker_count=913`, `trading_dates=4`.
- Diagnose-source: `PARTIAL_SUCCESS`, `failed_ticker_count=1`, `failed_window_count=1`.
- Full lifecycle: 4/4 requested dates readable with evidence exported, fixture generated, and replay verified.
- Resume-only-failed: `FAILED_RETRY_BLOCKED` for `WBSA` HTTP 400 in window `2026-01-01` to `2026-03-31`, with failed checkpoint/retry counts reported explicitly.

## Source cache format
`source_acquisition_cache.json` uses `cache_format=source_acquisition_resume_v2_slim`.

This cache is intentionally slim:
- stores row counts and telemetry summaries
- stores failed checkpoint retry accounting
- stores bounded/sanitized failure samples
- does not store full `rows_by_trade_date`
- does not store full `source_acquisition_checkpoints`
- does not store raw provider payloads

The slim cache is **not** the immutable source-observation store. Provider response/envelope/hash/provenance required by the source-observation contract must be retained through the governed observation/evidence surface even when the resume cache omits the raw payload.

`source_acquisition_checkpoint.json` remains the full retry identity artifact. `source_acquisition_diagnostics.json.reason_code` must match the explicit summary reason or the deterministic failed-checkpoint reason chosen from the retry scope.

---

# `market-data:backfill:missing-tickers`

## Official role
`market-data:backfill:missing-tickers` mempertahankan nama command legacy, tetapi semantik V2-nya adalah **MISSING LISTING FULL LIFECYCLE ORCHESTRATION** untuk stable `listing_id`/trade-date yang expected tetapi belum delivered dalam publication-bound artifact. `ticker_code`/`ticker_id` hanya input/display compatibility yang harus di-resolve point-in-time.

Command ini digunakan ketika **temporal listing universe** untuk suatu trade date menyatakan listing berada dalam scope expected-bar, tetapi baseline/current readable publication tidak memiliki valid delivered bar untuk listing tersebut. Current `is_active`/ticker master tidak boleh menentukan historical universe. Bila tanggal sudah readable, setiap perubahan truth harus menghasilkan correction/republication lineage; command tidak mengedit artifact current secara in-place.

Contoh plan non-mutating:

```text
php artisan market-data:backfill:missing-tickers 2026-06-03 2026-06-03 --source_mode=api --plan -vvv
```

Contoh mutating lifecycle untuk ticker tertentu:

```text
php artisan market-data:backfill:missing-tickers 2023-01-02 2025-10-31 --source_mode=api --ticker_codes=ABCD --with-evidence --with-replay -vvv
```

## Lifecycle behavior
- `--plan` menghitung gap dari temporal `listing_id` universe + verified expected-bar state versus delivered bars pada publication context yang dipilih, tanpa menulis data.
- Normal execution memakai API range acquisition hanya untuk source symbol/listing gap yang telah di-resolve point-in-time.
- Setiap response/refetch baru di-append sebagai immutable source observation. Existing rows untuk tanggal yang sudah memiliki readable publication direferensikan dari explicit baseline publication/observation lineage; mereka **bukan** disamarkan menjadi source payload baru.
- Candidate penuh dibentuk sebagai unsealed correction/publication candidate, kemudian melalui coverage, analytical price product, indicators, data-usability/eligibility projection, hash, seal, finalize, evidence export, fixture generation, dan replay verify.
- Temporal sector membership, corporate-action/factor revisions, trading-status facts, and event-risk context are resolved as-of the publication/replay knowledge context; they are not inferred from mutable current masters.

## Operator meaning
Selesainya command ini dengan `with-evidence`/`with-replay` berarti gap ticker yang diproses sudah melalui proof lifecycle yang sama seperti backfill lifecycle, bukan sekadar raw import.

Jika `--ticker_codes` tidak diberikan, command memindai semua ticker universe untuk tanggal yang diminta. Gunakan `--ticker_codes` saat operator baru menambahkan ticker tertentu dan ingin menghindari source acquisition seluruh missing universe.

## Historical mutation telemetry compatibility
Backfill and lifecycle commands may ingest observations for dates older than already available/readable dates. The field names below are retained for runtime compatibility; `mutation`/`updated` mean changes to an **unsealed candidate projection**, never in-place mutation of observation or sealed history. Target identity metrics use `listing`, while ticker metrics are compatibility-only.

When candidate bars change, command output and summary artifacts may expose:
- `bar_mutation_changed_count`
- `bar_mutation_inserted_count`
- `bar_mutation_updated_count`
- `bar_mutation_unchanged_count`
- `affected_listing_count` (target)
- `affected_ticker_count` (legacy compatibility)
- `affected_trade_date_count`
- `affected_start_date`
- `affected_end_date`
- `max_indicator_dependency_trading_days`
- `indicator_reprocess_state`
- `publication_impact_state`

Important states:
- `NOOP_UNCHANGED_BARS`: accepted observations do not change the candidate analytical truth; no sealed/history mutation occurs.
- `REPROCESS_REQUIRED_REQUESTED_DATES_ONLY`: changed bars affect only the imported/requested date set currently visible to the resolver.
- `REPROCESS_REQUIRED_WITH_DOWNSTREAM_IMPACT`: changed historical bars may affect later indicator dates and downstream derived artifacts must be handled before they are trusted.
- `REQUIRES_REPUBLICATION`: at least one affected date is already readable and must go through correction/reseal/republication before consumer-visible replacement.

`--resume --only-failed` must not fake apply recovered ticker rows by replacing an entire date artifact with a partial retry result. A successful recovered-checkpoint apply requires a dedicated partial-row recovery/correction path before derived artifacts are recomputed.

## Amendment 2026-05-27 - Recovered apply and execution summary
### V2 interpretation of recovered-row apply (LOCKED 2026-08-08)

The May telemetry names below describe the legacy implementation surface. Under the current strategy, a retry success first creates a new immutable source observation. Any `partial-upsert` is permitted only as a merge into an **unsealed candidate/workspace projection** keyed by stable `listing_id`; it is not permission to overwrite immutable observations, canonical history bound to a sealed publication, or publication snapshots. An already-readable affected date always goes through correction/republication.

Target telemetry should expose `affected_listing_count`; `affected_ticker_count` may remain only as a compatibility metric while legacy identity is present.

`--resume --only-failed` retry success now proceeds beyond source acquisition when recovered rows are present.

Expected recovery output includes:
- `recovered_row_apply_state`
- `recovered_row_count`
- `bar_mutation_changed_count`
- `indicator_reprocess_execution_state`
- `indicator_reprocessed_trade_date_count`
- `eligibility_reprocess_execution_state`
- `publication_reprocess_state`

Recovered immutable observations are merged into the unsealed candidate projection by stable listing/date. Existing candidate rows for other listings on the same date must remain present. The historical implementation may report this operation as a ticker/date partial-upsert, but that label does not authorize immutable/history overwrite. If the recovered rows are unchanged, the command reports `UNCHANGED`/`NOOP_UNCHANGED_BARS` and does not recompute unnecessarily. If affected dates are already readable, lifecycle/full-publish reprocess must use correction-current republication with explicit correction lineage, or report a blocked/failed correction reason without mutating the current pointer silently.

## Amendment 2026-05-27 - Hash/seal/finalize for affected non-readable dates
For `market-data:backfill:lifecycle`, changed historical bars that affect downstream non-readable dates may now continue from `PENDING_PROMOTE` into the existing promote flow. The promote flow recomputes coverage/indicators/eligibility as needed, then hashes, seals, finalizes, and validates readability.

When `--with-evidence` or `--with-replay` is enabled, lifecycle publication reprocess may export evidence and replay proof for affected non-readable dates that were republished. Already-readable affected dates remain correction-blocked and are not silently republished.

## Amendment 2026-05-27 - Import-only execution output and readable auto-correction
Plain `market-data:backfill` remains import-only and must not switch current pointers by itself. However, when import-only writes EOD bars and impact execution has populated run notes, the command output and `market_data_backfill_summary.json` must expose the execution-layer surface, including:

- `indicator_reprocess_execution_state`
- `indicator_reprocessed_trade_date_count`
- `indicator_reprocessed_trade_dates`
- `eligibility_reprocess_execution_state`
- `eligibility_reprocessed_trade_date_count`
- `eligibility_reprocessed_trade_dates`
- `publication_reprocess_state`
- `publication_reprocess_republished_trade_date_count`
- `publication_reprocess_republished_trade_dates`
- `publication_reprocess_candidate_trade_dates`
- `publication_reprocess_readable_correction_candidate_trade_dates`
- `publication_reprocess_blocked_trade_dates`
- `publication_reprocess_failed_trade_dates`
- `publication_reprocess_blocked_reason_code`
- `publication_reprocess_failure_reason_code`
- `recovered_row_apply_state`
- `recovered_row_count`

For lifecycle/full-publish publication reprocess, an already-readable affected date may now be auto-corrected only through the existing correction-current lifecycle. The system must create and approve a correction request with `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`, use the pointer-resolved baseline publication, and run correction-current promote. It must not use normal full-publish to replace an already-readable date.


---

## Amendment 2026-05-27 - Final validation lock for import-only output and readable auto-correction

Final local validation confirms both backfill-related cleanup targets are locked:

- `BackfillLifecyclePublicationReprocess` -> OK (4 tests, 19 assertions).
- `OutOfOrderImportImpact` -> OK (7 tests, 107 assertions).
- `Backfill` -> OK (49 tests, 339 assertions).
- Full MarketData suite -> OK (585 tests, 8713 assertions).

Operational rule after this lock:

- Plain `market-data:backfill` remains import-only, but it must expose execution-layer reprocess fields in command output and summary when run notes include them.
- Lifecycle/full-publish publication reprocess may auto-correct an already-readable affected downstream date only through the correction-current lifecycle.
- The correction-current path must preserve baseline lineage and must not bypass coverage, hash, seal, finalize, pointer, evidence, or replay guards.
- Normal full-publish must not replace an already-readable affected date directly.


## Amendment 2026-06-05 - Market-calendar lifecycle warmup
`market-data:backfill:lifecycle` must treat source-acquisition warmup as a trading-day dependency.

Required behavior:
- read requested trading dates from `market_calendar`
- resolve warmup start through the configured trading-day window, not wall-clock days
- keep requested-date lifecycle boundaries per trading date
- block invalid or insufficient calendar dependency with an explicit reason instead of silently producing NULL rolling indicators

Operational implication:
- if MA50 or ROC20-based outputs are unexpectedly NULL while OHLC history exists, verify `market_calendar` coverage and lifecycle warmup-window resolution before changing indicator formulas
- sector rotation remains source-backed; `sector_roc20` for a date still requires sector-index bars and benchmark indicators for that same date
