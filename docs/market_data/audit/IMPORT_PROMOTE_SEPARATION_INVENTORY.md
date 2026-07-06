# Import vs Promote Separation Inventory

Status: DONE / LOCKED_LOCAL_PHPUNIT_PASS.

| Area | Import Path | Promote Path | Gate | Pointer Side Effect | Status | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|---|
| manual file ingest | `request_mode=import_only`, `source_mode=manual_file` | `request_mode=promote`, `source_mode=manual_file` | promote requires coverage/hash/seal/finalize | import forbidden | ENFORCED | local PHPUnit PASS | run context + evidence/replay/command/static guard | targeted + full MarketData PHPUnit PASS |
| API ingest | `request_mode=import_only`, `source_mode=api` | `request_mode=promote`, `source_mode=api` | promote requires coverage/hash/seal/finalize | import forbidden | ENFORCED | local PHPUnit PASS | source identity preserved; failure remains reason-coded | targeted + full MarketData PHPUnit PASS |
| daily command | calls `importDaily()` | none | no publish gate because import-only | forbidden | ENFORCED | local PHPUnit PASS | output includes request/import/promote/pointer status | targeted + full MarketData PHPUnit PASS |
| promote command | none | calls `promoteDaily()` | coverage before downstream stages, then hash/seal/finalize | only after finalize success | ENFORCED | local PHPUnit PASS | request_mode persisted as `promote` | targeted + full MarketData PHPUnit PASS |
| bars ingest | write imported/canonical/candidate context | seed for promote | no publishability claim | blocked by import-only assertion | ENFORCED | local PHPUnit PASS | `IMPORT_ONLY_NOT_PROMOTED` event + import-only closure as `COMPLETED/NULL/NOT_READABLE` | targeted + full MarketData PHPUnit PASS |
| active run ownership | active process only; stale `RUNNING` is cancelled before new owning run reuse/create | promote uses explicit promote run/seed | stale TTL | no readable side effect | ENFORCED | local PHPUnit PASS | `STALE_ACTIVE_RUN_CANCELLED` reason/event | targeted + full MarketData PHPUnit PASS |
| indicators compute | forbidden under `import_only` | allowed under promote/full publish/correction | stage guard | no direct pointer switch | ENFORCED | local PHPUnit PASS | `REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE` | targeted + full MarketData PHPUnit PASS |
| eligibility build | forbidden under `import_only` | allowed under promote/full publish/correction | stage guard | no direct pointer switch | ENFORCED | local PHPUnit PASS | request mode validation | targeted + full MarketData PHPUnit PASS |
| coverage evaluation | not required for import-only as publish proof | mandatory before promote continuation | coverage gate | no direct pointer switch | ENFORCED | local PHPUnit PASS | promote starts with coverage | targeted + full MarketData PHPUnit PASS |
| hash | forbidden under `import_only` | mandatory before seal/finalize | deterministic hash | none | ENFORCED | local PHPUnit PASS | stage guard + static guard | targeted + full MarketData PHPUnit PASS |
| seal | forbidden under `import_only` | mandatory before finalize | seal preconditions | none | ENFORCED | local PHPUnit PASS | stage guard + existing seal checks | targeted + full MarketData PHPUnit PASS |
| finalize | forbidden under `import_only` | mandatory before readable/current | publishability + pointer validation | only here after success | ENFORCED | local PHPUnit PASS | stage guard + existing finalize checks | targeted + full MarketData PHPUnit PASS |
| publication candidate | allowed as non-current artifact context | promoted only after gate | promote gate | import candidate must stay non-current | ENFORCED | local PHPUnit PASS | import-only candidate current assertion | targeted + full MarketData PHPUnit PASS |
| current publication promotion | forbidden | allowed after finalize success | run/publication/pointer validation | yes, promote only | ENFORCED | local PHPUnit PASS | no call from import path + static guard | targeted + full MarketData PHPUnit PASS |
| pointer switch | forbidden | allowed after finalize success | post-switch resolver validation | yes, promote only | ENFORCED | local PHPUnit PASS | import-only readable/current/pointer assertion | targeted + full MarketData PHPUnit PASS |
| correction request | not publish | not publish | approval rules | none | EXISTING | none found | no change | full MarketData PHPUnit PASS |
| correction approve | not publish | not publish | approval rules | none | EXISTING | none found | no change | full MarketData PHPUnit PASS |
| correction run | import context cannot publish | replacement requires promote/finalize validation | correction baseline/replacement rules | preserve baseline on failure/unchanged | ENFORCED | local PHPUnit PASS | request_mode `correction` / `promote` retained | targeted + full MarketData PHPUnit PASS |
| correction unchanged | import cannot publish | preserve baseline | artifact comparison | no switch | EXISTING | local PHPUnit PASS | no weakening | full MarketData PHPUnit PASS |
| correction failed | import cannot publish | preserve baseline | fail-safe | no switch | EXISTING | local PHPUnit PASS | no weakening | full MarketData PHPUnit PASS |
| correction published | forbidden from import | explicit promote/finalize only | correction linkage | switch only if valid | ENFORCED | local PHPUnit PASS | static guard covers import publish block | targeted + full MarketData PHPUnit PASS |
| replay | compares expected/actual request/source/import/promote/pointer context | verifies promoted expected pointer switch | mismatch reason codes | detects unexpected switch | ENFORCED | local PHPUnit PASS | added import/promote context | targeted + full MarketData PHPUnit PASS |
| evidence | exports import/promote boundary | exports promote gate state | evidence proof | records pointer switch bool | ENFORCED | local PHPUnit PASS | added `import_promote_boundary` | targeted + full MarketData PHPUnit PASS |
| session snapshot | read-side must stay pointer-resolved | depends on current readable publication | read-side contract | no import shortcut | EXISTING | local PHPUnit PASS | no direct change | full MarketData PHPUnit PASS |
| read-side consumer | import artifacts not readable | pointer-resolved publication only | read contract | no import shortcut | EXISTING | local PHPUnit PASS | no direct change | full MarketData PHPUnit PASS |
| command output | prints import/promote statuses | prints gate/readiness status | operator-visible | prints pointer_switched | ENFORCED | local PHPUnit PASS | abstract summary extended | targeted + full MarketData PHPUnit PASS |
| static guard | asserts boundary files/strings | asserts promote gate strings | static proof | detects import side effects | ENFORCED | local PHPUnit PASS | new static guard | targeted + full MarketData PHPUnit PASS |


## Production-Ready Reconciliation Addendum

Current canonical status for this scope is LOCKED in `LUMEN_CONTRACT_TRACKER.md`. Historical pending/local-validation wording above has been reconciled for the import-only run closure and stale active run recovery patch.

Latest operator-local validation for this patch scope:

- `MarketDataPipelineServiceTest.php --filter "complete_ingest"` -> OK (5 tests, 6 assertions).
- `MarketDataPipelineServiceTest.php --filter "recovered_rows_partial"` -> OK (1 test, 2 assertions).
- `MarketDataPipelineIntegrationTest.php --filter "import_only"` -> OK (2 tests, 29 assertions).
- `MarketDataPipelineIntegrationTest.php --filter "stale_running"` -> OK (1 test, 8 assertions).
- `LoggingTraceabilityReasonCodesStaticGuardTest.php` -> OK (7 tests, 134 assertions).
- `ImportPromoteSeparationStaticGuardTest.php` -> OK (6 tests, 147 assertions).
- `AuditDocsSynchronizationStaticGuardTest.php --filter "test_reason_code_registry_and_seed_are_synchronized"` -> OK (1 test, 4 assertions).
- Full `tests/Unit/MarketData` -> OK (649 tests, 9598 assertions).
