# `market-data:eod-bars:ingest`

## Official role
Stage command ini tetap milik **IMPORT PHASE**.

## V2 strategy boundary
The ingest stage first appends immutable source-observation/acquisition evidence, then resolves temporal source symbol → stable `listing_id` and builds an **unsealed canonical candidate**. `ticker_id` is compatibility/display only. “Ingest” must never mean overwrite of immutable observations or sealed publication/history rows.

## Scope
Command ini hanya menangani ingest bars:
- baca source dan append observation/provenance
- resolve stable listing identity
- normalize/map
- dedup
- validate
- materialize unsealed canonical candidate bars
- tulis invalid/rejected evidence
- tulis telemetry minimum

## Boundary
Command ini tidak boleh:
- compute indicators
- build eligibility
- hash
- seal
- finalize

## Runtime request mode

Default `market-data:eod-bars:ingest` remains `request_mode=import_only`. For the documented stage-by-stage publish runtime proof, operators must opt in explicitly. When `source_mode=manual_file` is used for **operational recovery**, the request is limited to one requested trade date; planned historical backfill/correction/replay ranges are governed by the historical backfill contracts and are not continuity fallback:

```text
php artisan market-data:eod-bars:ingest --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --request_mode=full_publish
```

Invalid request modes fail closed with:

```text
status=BLOCKED
reason_code=COMMAND_INVALID_REQUEST_MODE
```
