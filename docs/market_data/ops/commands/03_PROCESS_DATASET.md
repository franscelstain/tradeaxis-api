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

## Current indicator recompute command

`market-data:eod-indicators:recompute-current` is not a raw stage command. Its V2 semantic is an operator command that starts from an explicit current readable **baseline publication**, retains its immutable RAW-bar/observation lineage, resolves the run-wide selected `STRUCTURAL_ADJUSTED` analytical price product with verified factor revisions and config/formula/reference identities, then creates a correction-current publication with recomputed publication-bound indicators and data-usability/eligibility projection. Hash, seal, finalize, and pointer switch occur only when all validation passes.

It must not perform source acquisition, bar ingest, source/master writes, or `eod_bars` writes, and it must not compute technical indicators directly from RAW/provider `adj_close` merely because those fields exist on the baseline.

## Historical runtime proof boundary

The proof below is retained as historical execution evidence only. It predates the V2 stable-identity, immutable-observation, run-wide `STRUCTURAL_ADJUSTED` price-product, full config snapshot, temporal-sector, and AS_KNOWN replay requirements; therefore it does **not** grant current V2 implementation conformance.

Final runtime proof on 2026-06-07: full MarketData PHPUnit passed at 640 tests / 9539 assertions; `market-data:eod-indicators:recompute-current 2023-01-02 2026-06-04` completed 807/807 trading dates with zero failures and no source/master/OHLCV writes; final current evidence/replay completed 807/807 MATCH/PASS with zero mismatches. Latest docs-review validation on 2026-06-08 reran `vendor\bin\phpunit` and passed with OK (641 tests, 9547 assertions).
