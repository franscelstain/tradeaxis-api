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
