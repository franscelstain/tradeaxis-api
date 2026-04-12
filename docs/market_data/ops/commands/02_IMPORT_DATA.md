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
