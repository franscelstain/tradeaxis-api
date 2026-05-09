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

## Registered command surface

- `market-data:daily`
- `market-data:eod-bars:ingest`
- `market-data:eod-indicators:compute`
- `market-data:eod-eligibility:build`
- `market-data:audit:hash`
- `market-data:dataset:seal`
- `market-data:run:finalize`
- `market-data:promote`
- `market-data:evidence:export`
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

## Notes

- penamaan file lama dipertahankan untuk stabilitas dokumentasi
- semantik resmi tetap mengikuti split **IMPORT PHASE** vs **PROMOTE PHASE**
- command surface resmi juga sekarang dikunci sebagai **date-driven**
- `market-data:promote` adalah satu-satunya command promote
- operator tidak boleh memakai raw/staging/latest/MAX(date) sebagai proof readability
- semua failure/hold/not-readable state harus ditindaklanjuti dari reason code dan runbook
