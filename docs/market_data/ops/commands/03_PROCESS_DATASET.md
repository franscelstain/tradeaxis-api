# PROCESS DATASET COMMAND FAMILY

## Official role after split
Dokumen ini sekarang merepresentasikan command-command **PROMOTE PHASE** awal:

- `market-data:promote` sebagai surface resmi promote
- stage internal yang dapat dipakai promote:
  - `market-data:eod-indicators:compute`
  - `market-data:eod-eligibility:build`
  - `market-data:audit:hash`

## Boundary
Command-command ini membaca bars hasil import.
Mereka bukan source acquisition commands.
