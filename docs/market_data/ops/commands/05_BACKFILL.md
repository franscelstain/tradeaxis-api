# `market-data:backfill`

## Official role
`market-data:backfill` adalah command **IMPORT PHASE** untuk **rentang tanggal spesifik**.

## Date-driven contract
Backfill adalah jalur inti historical ingestion.
Command ini wajib dipahami sebagai mekanisme resmi untuk memproses range trading date apa pun yang sah, bukan sekadar fitur tambahan untuk data recent.

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
