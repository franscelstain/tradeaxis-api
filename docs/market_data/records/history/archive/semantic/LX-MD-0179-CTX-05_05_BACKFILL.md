# Legacy Semantic Extract — LX-MD-0179-CTX-05

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `CONTEXT`
- Source range: `L167-L271`
- Extract body SHA1: `26D7C2C81C4155EFB1E6451B7E1124200EC80361`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
