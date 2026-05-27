# `market-data:daily`

## Official role
`market-data:daily` adalah command **IMPORT PHASE** untuk **1 requested trade date spesifik**.

## Date-driven contract
Command ini wajib menerima tanggal target eksplisit dan tidak boleh ditafsir recent-only hanya karena provider default memiliki query window tertentu.

## Contract
Command ini hanya boleh menjalankan:
- acquisition
- ticker-level processing
- mapping
- dedup
- validation
- write `eod_bars`
- write invalid rows
- write import telemetry
- write bars coverage evidence minimum

Command ini **tidak boleh** menjalankan:
- indicators
- eligibility
- hash
- seal
- finalize

## Operator meaning
Suksesnya `market-data:daily` berarti import selesai/tercatat untuk requested date.
Itu **bukan** berarti requested date sudah readable.
Untuk readability/publishability, operator harus lanjut ke `market-data:promote`.

## Amendment 2026-05-26 - Mutation impact summary
`market-data:daily` tetap import-only, tetapi import-only bukan berarti impact-nya tidak dicatat.

Output/summary artifact harus menampilkan mutation impact fields jika EOD bars ditulis:
- `bar_mutation_changed_count`
- `bar_mutation_inserted_count`
- `bar_mutation_updated_count`
- `bar_mutation_unchanged_count`
- `affected_trade_date_count`
- `affected_start_date`
- `affected_end_date`
- `indicator_reprocess_state`
- `publication_impact_state`

Jika requested date historis mempengaruhi date yang sudah readable, command harus melaporkan `publication_impact_state=REQUIRES_REPUBLICATION` dan tidak boleh membuat publication lama berubah diam-diam.

## Amendment 2026-05-27 - Impact execution output
`market-data:daily` remains import-only for publication, but changed EOD bars now execute derived reprocess for affected non-readable dates.

Additional output fields may appear:
- `indicator_reprocess_execution_state`
- `indicator_reprocessed_trade_date_count`
- `eligibility_reprocess_execution_state`
- `eligibility_reprocessed_trade_date_count`
- `publication_reprocess_state`

`indicator_reprocess_execution_state=EXECUTED` means indicators and eligibility were recomputed; it does not mean the date became readable. If `publication_reprocess_state=BLOCKED_REQUIRES_CORRECTION`, run the historical correction flow before any readable replacement.

## Amendment 2026-05-27 - Full-publish downstream publication reprocess
The import-only command contract above remains unchanged. Import-only daily execution must not promote or switch pointers.

When the daily pipeline is invoked in full-publish service mode (`runDaily`/full publish rather than import-only command semantics), affected downstream dates that are not already readable may be promoted through the existing promote flow after the primary requested date finalizes. That flow owns coverage, hash, seal, finalize, and pointer-readability guards.

Already-readable affected dates remain blocked with `AFFECTED_PUBLICATION_REQUIRES_CORRECTION` and require correction/republication.
