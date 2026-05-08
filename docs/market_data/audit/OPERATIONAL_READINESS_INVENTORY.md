# Operational Readiness Inventory

Status: LOCKED_LOCAL_PHPUNIT_PASS after operator-local targeted/full PHPUnit and artisan command evidence.
Contract: OPERATIONAL_READINESS_CONTRACT.
Active implementation: Operational Readiness.

This inventory records operational readiness gaps, patches, and final local validation evidence from the 2026-05-08 session. It is intentionally operator-facing: if an operator cannot run the flow from documented commands, expected output, reason code, next action, evidence export, and replay proof, the item is not ready.

| Area | Current State | Required State | Gap | Patch | Evidence | Status |
|---|---|---|---|---|---|---|
| Runbook | Several command-specific docs existed, but no single canonical operational runbook. | One operator source of truth covering full flow. | Operator had to stitch docs together. | Added `docs/market_data/ops/OPERATIONAL_RUNBOOK.md`. | Static trace + `OperationalReadinessStaticGuardTest.php`; local PHPUnit PASS. | LOCKED |
| Command coverage | Command safety inventory listed commands. | Runbook must document every registered market-data command. | No single checklist with purpose/input/output/next action. | Runbook command coverage matrix added. | Static guard compares registered command list to runbook. | LOCKED |
| Daily flow | Import/promote split existed. | Operator must know daily import-only is not publish. | Needed single flow with pass/fail criteria. | Daily operational flow documented. | Static guard checks daily/import-only/promote terms. | LOCKED |
| Manual file flow | Manual file policy existed. | Manual file must not bypass coverage/promote/finalize. | Needed operator steps and stop rules. | Manual file import-only and promote sections added. | Static guard checks manual file/import-only/promote coverage. | LOCKED |
| Promote flow | Promote command existed. | Promote must follow coverage/hash/seal/finalize/pointer gates. | Needed expected output and stop criteria. | Promote expected output and failure rules documented. | Static guard checks gate terms. | LOCKED |
| Correction flow | Correction command docs existed. | Request/approve/run/evidence/replay lifecycle must be runnable. | Needed operator status map. | Correction lifecycle section added. | Static guard checks correction request/approve/run and lifecycle states. | LOCKED |
| Backfill flow | Backfill command existed. | Backfill must remain import-only unless explicitly promoted. | Needed range prerequisites and proof rules. | Backfill section added. | Static guard checks backfill and no-readable implication. | LOCKED |
| Session snapshot flow | Snapshot commands existed. | Snapshot must be pointer-resolved only. | Needed capture/purge docs and no raw/latest fallback. | Session snapshot section added. | Static guard checks snapshot and forbidden shortcuts. | LOCKED |
| Evidence export | Evidence service/tests existed. | Operator must export proof without DB query. | Needed metadata checklist. | Evidence export flow/checklist added. | Static guard checks run/publication/pointer/coverage/source/reason/correction/replay metadata. | LOCKED |
| Replay verification | Replay service/tests existed. | Replay must be proof mechanism. | Needed verify/smoke/backfill steps and mismatch handling. | Replay verification section added. | Static guard checks replay verify/smoke/backfill and mismatch terms. | LOCKED |
| Terminal state handling | Fail-safe contract existed. | HELD/FAILED/NOT_READABLE/READABLE must have action. | Needed operator action table. | Terminal state handling table added. | Static guard checks states and `next action`. | LOCKED |
| Reason code handling | Registry/seed existed. | Operator must decide from reason code. | Needed reason-code handling map. | Reason-code handling table added. | Static guard checks reason-code conditions. | LOCKED |
| Manual DB policy | Repair command existed. | Manual DB action must be exceptional and documented. | Needed explicit policy and forbidden actions. | Manual DB action policy added. | Static guard checks manual DB policy. | LOCKED |
| Forbidden shortcuts | Multiple locked contracts existed. | Ops docs must explicitly forbid raw/staging/latest/MAX(date). | Needed operator-level no-shortcut list. | Forbidden shortcuts section added. | Static guard checks exact forbidden shortcut terms. | LOCKED |
| Audit docs | Previous active session was Audit Docs Synchronization. | Current operational session must be recorded without deleting history. | Active session/current working entry needed update. | Updated implementation status and contract tracker with Operational Readiness. | Static trace + patched audit-docs guard. | LOCKED |
| Static guard | No operational-readiness-specific guard existed. | Test must guard runbook/command/audit sync. | Missing guard. | Added `OperationalReadinessStaticGuardTest.php`. | `php -l` passed; local PHPUnit PASS. | LOCKED |
| Manual validation | Operator-local validation evidence supplied. | Targeted/full suite plus artisan command discovery/help must pass before DONE/LOCKED. | Closed by local evidence. | Recorded completed validation commands and results. | OperationalReadiness OK (10 tests, 196 assertions); full MarketData OK (368 tests, 4927 assertions); artisan list/help PASS. | LOCKED |

## Current decision

Operational readiness is LOCKED in this ZIP. Container validation remains static because uploaded ZIP has no `vendor/`, but operator-local targeted/full MarketData PHPUnit and artisan command discovery/help evidence has been supplied and recorded.

## Regression reconciliation

Operational readiness touches all prior market-data contracts. The runbook explicitly preserves read-side pointer enforcement, coverage gate enforcement, publishability state integrity, finalize/pointer determinism, run/publication/pointer linkage, correction lifecycle safety, source/provider resilience, manual file policy, evidence export completeness, replay determinism, command surface safety, logging/traceability/reason codes, DB integrity, behavioral test coverage, hash/seal integrity, import/promote separation, fail-safe behavior, and audit docs synchronization. No historical audit entry was deleted.

## Manual validation completed

Completed local commands:

- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (41 tests, 718 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (38 tests, 643 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> OK (65 tests, 1287 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` -> OK (5 tests, 108 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (368 tests, 4927 assertions)
- `php artisan list | findstr market-data` -> PASS, 19 market-data commands listed


## Final local validation evidence

- `vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"` -> OK (10 tests, 196 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` -> OK (47 tests, 348 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` -> OK (41 tests, 718 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` -> OK (38 tests, 643 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` -> OK (65 tests, 1287 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"` -> OK (5 tests, 108 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (368 tests, 4927 assertions)
- `php artisan list | findstr market-data` -> PASS, 19 market-data commands listed
- Command help spot checks -> PASS for `market-data:daily`, `market-data:promote`, `market-data:evidence:export`, `market-data:replay:verify`, `market-data:correction:request`, `market-data:correction:approve`, and `market-data:correction:run`
