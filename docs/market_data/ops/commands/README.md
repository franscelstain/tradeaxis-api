# COMMAND DOCUMENTATION INDEX

Canonical operator source of truth:

- `../OPERATIONAL_RUNBOOK.md`

The command-specific docs below remain supporting material. They must not contradict the operational runbook, command signatures, evidence export behavior, replay behavior, or locked market-data contracts.

## Import commands
1. `01_DAILY_PIPELINE.md`
2. `02_IMPORT_DATA.md`
5. `05_BACKFILL.md`

## Promote commands
4. `04_FINALIZE_AND_PUBLISH.md`

## Evidence, replay, correction, and session snapshot commands
6. `06_CORRECTION.md`
7. `07_REPLAY_AND_EVIDENCE.md`
8. `08_SESSION_SNAPSHOT.md`

## Detected runtime command surface

Presence in this list means the command is registered, not approved. The two prohibited legacy apply surfaces are called out below and block production relock.

- `market-data:daily`
- `market-data:eod-bars:ingest`
- `market-data:eod-indicators:compute`
- `market-data:eod-indicators:recompute-current`
- `market-data:eod-eligibility:build`
- `market-data:audit:hash`
- `market-data:dataset:seal`
- `market-data:run:finalize`
- `market-data:promote`
- `market-data:backfill:lifecycle`
- `market-data:backfill:missing-tickers`
- `market-data:evidence:export`
- `market-data:evidence-replay:full-range-current`
- `market-data:sector-indexes:ingest-api`
- `market-data:sector-indexes:import-bars`
- `market-data:sectors:import-memberships`
- `market-data:events:import-corporate-actions`
- `market-data:events:record-authoritative-terms`
- `market-data:market-structure:record-authoritative-rules`
- `market-data:trading-status:record-authoritative-snapshot`
- `market-data:corpus:admit-conformant-suffix`
- `market-data:corpus:reconstruct-current`
- `market-data:events:import-trading-status`
- `market-data:replay:verify`
- `market-data:replay:smoke`
- `market-data:replay:backfill`
- `market-data:replay:fixture:generate`
- `market-data:backfill`
- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`
- `market-data:correction:request`
- `market-data:correction:approve`
- `market-data:correction:run`
- `market-data:current-publication:repair`
- `market-data:provider:smoke`
- `market-data:detect-price-scale-breaks`
- `market-data:repair-price-scale-stretches`
- `market-data:events:derive-corporate-actions`

### Prohibited until removed or redesigned

- `market-data:repair-price-scale-stretches`: direct `--apply` mutation of `eod_bars`/history is forbidden.
- `market-data:events:derive-corporate-actions`: may at most write an unverified detector candidate; it may not create/update verified actions or factors from price behavior.

## Notes

- penamaan file lama dipertahankan untuk stabilitas dokumentasi
- semantik resmi tetap mengikuti split **IMPORT PHASE** vs **PROMOTE PHASE**
- command surface resmi juga sekarang dikunci sebagai **date-driven**
- `market-data:promote` adalah satu-satunya command promote
- operator tidak boleh memakai raw/staging/latest/MAX(date) sebagai proof readability
- semua failure/hold/not-readable state harus ditindaklanjuti dari reason code dan runbook

## Current indicator recompute lock

`market-data:eod-indicators:recompute-current` is runtime-proven as the no-reimport indicator maintenance path. Runtime proof: full MarketData PHPUnit 640 tests / 9539 assertions on 2026-06-07, full-range recompute 807/807 success, and final current evidence/replay 807/807 MATCH/PASS. Latest docs-review validation on 2026-06-08 reran `vendor\bin\phpunit` and passed with OK (641 tests, 9547 assertions). It must remain read-only toward source/master tables and `eod_bars`.
