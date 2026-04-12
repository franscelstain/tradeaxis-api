# FINALIZE AND PUBLISH COMMAND FAMILY

Finalize/publish family berada di dalam **PROMOTE PHASE**.

## Official command
- `market-data:promote`

## Promote-owned stages
Di dalam jalur promote, implementation boleh memanggil stage internal berikut:
- coverage validation
- indicators
- eligibility
- hash
- seal
- finalize

Stage internal itu bukan command operator terpisah pada baseline ini.

## Boundary rule
Tidak ada finalize/publish action yang boleh dijalankan oleh `market-data:daily` atau `market-data:backfill`.
