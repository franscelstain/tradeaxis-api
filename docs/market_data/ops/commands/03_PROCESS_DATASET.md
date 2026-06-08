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

`market-data:eod-indicators:recompute-current` is not a raw stage command. It is an operator command that creates a correction-current publication from existing current readable bars, recomputes publication-bound indicators/eligibility, hashes, seals, finalizes, and switches the current pointer only when validation passes.

It must not perform source acquisition, bar ingest, source/master writes, or `eod_bars` writes.

## Runtime proof

Final runtime proof on 2026-06-07: full MarketData PHPUnit passed at 640 tests / 9539 assertions; `market-data:eod-indicators:recompute-current 2023-01-02 2026-06-04` completed 807/807 trading dates with zero failures and no source/master/OHLCV writes; final current evidence/replay completed 807/807 MATCH/PASS with zero mismatches. Latest docs-review validation on 2026-06-08 reran `vendor\bin\phpunit` and passed with OK (641 tests, 9547 assertions).
