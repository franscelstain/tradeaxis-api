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
