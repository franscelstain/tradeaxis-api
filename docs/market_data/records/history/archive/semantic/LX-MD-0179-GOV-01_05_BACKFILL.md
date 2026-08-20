# Legacy Semantic Extract — LX-MD-0179-GOV-01

- Source ID: `LS-MD-0179`
- Original path: `ops/commands/05_BACKFILL.md`
- Original SHA1: `7D024D1A49999C8FD30899BF32AC78581D6AE221`
- Extract role: `GOVERNANCE`
- Source range: `L6-L30`
- Extract body SHA1: `59F581D508DBB1725838CF3420EDE52ED620EE96`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
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


<!-- LEGACY_EXTRACT_BODY_END -->
