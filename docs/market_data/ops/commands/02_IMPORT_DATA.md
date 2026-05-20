# `market-data:eod-bars:ingest`

## Official role
Stage command ini tetap milik **IMPORT PHASE**.

## Scope
Command ini hanya menangani ingest bars:
- baca source
- normalize/map
- dedup
- validate
- tulis canonical bars
- tulis invalid rows
- tulis telemetry minimum

## Boundary
Command ini tidak boleh:
- compute indicators
- build eligibility
- hash
- seal
- finalize

## Runtime request mode

Default `market-data:eod-bars:ingest` remains `request_mode=import_only`. For the documented stage-by-stage publish runtime proof, operators must opt in explicitly:

```text
php artisan market-data:eod-bars:ingest --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --request_mode=full_publish
```

Invalid request modes fail closed with:

```text
status=BLOCKED
reason_code=COMMAND_INVALID_REQUEST_MODE
```
