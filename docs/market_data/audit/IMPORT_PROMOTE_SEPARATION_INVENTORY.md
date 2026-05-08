# Import vs Promote Separation Inventory

Status: ENFORCED by static/runtime guards; local PHPUnit validation pending before DONE/LOCKED.

| Area | Import Path | Promote Path | Gate | Pointer Side Effect | Status | Gap | Patch | Test Result |
|---|---|---|---|---|---|---|---|---|
| manual file ingest | `request_mode=import_only`, `source_mode=manual_file` | `request_mode=promote`, `source_mode=manual_file` | promote requires coverage/hash/seal/finalize | import forbidden | ENFORCED | local PHPUnit pending | run context + evidence/replay/command/static guard | php -l only |
| API ingest | `request_mode=import_only`, `source_mode=api` | `request_mode=promote`, `source_mode=api` | promote requires coverage/hash/seal/finalize | import forbidden | ENFORCED | local PHPUnit pending | source identity preserved; failure remains reason-coded | php -l only |
| daily command | calls `importDaily()` | none | no publish gate because import-only | forbidden | ENFORCED | local PHPUnit pending | output includes request/import/promote/pointer status | php -l only |
| promote command | none | calls `promoteDaily()` | coverage before downstream stages, then hash/seal/finalize | only after finalize success | ENFORCED | local PHPUnit pending | request_mode persisted as `promote` | php -l only |
| bars ingest | write imported/canonical/candidate context | seed for promote | no publishability claim | blocked by import-only assertion | ENFORCED | local PHPUnit pending | `IMPORT_ONLY_NOT_PROMOTED` event | php -l only |
| indicators compute | forbidden under `import_only` | allowed under promote/full publish/correction | stage guard | no direct pointer switch | ENFORCED | local PHPUnit pending | `REQUEST_MODE_IMPORT_BLOCKED_FROM_PROMOTE` | php -l only |
| eligibility build | forbidden under `import_only` | allowed under promote/full publish/correction | stage guard | no direct pointer switch | ENFORCED | local PHPUnit pending | request mode validation | php -l only |
| coverage evaluation | not required for import-only as publish proof | mandatory before promote continuation | coverage gate | no direct pointer switch | ENFORCED | local PHPUnit pending | promote starts with coverage | php -l only |
| hash | forbidden under `import_only` | mandatory before seal/finalize | deterministic hash | none | ENFORCED | local PHPUnit pending | stage guard + static guard | php -l only |
| seal | forbidden under `import_only` | mandatory before finalize | seal preconditions | none | ENFORCED | local PHPUnit pending | stage guard + existing seal checks | php -l only |
| finalize | forbidden under `import_only` | mandatory before readable/current | publishability + pointer validation | only here after success | ENFORCED | local PHPUnit pending | stage guard + existing finalize checks | php -l only |
| publication candidate | allowed as non-current artifact context | promoted only after gate | promote gate | import candidate must stay non-current | ENFORCED | local PHPUnit pending | import-only candidate current assertion | php -l only |
| current publication promotion | forbidden | allowed after finalize success | run/publication/pointer validation | yes, promote only | ENFORCED | local PHPUnit pending | no call from import path + static guard | php -l only |
| pointer switch | forbidden | allowed after finalize success | post-switch resolver validation | yes, promote only | ENFORCED | local PHPUnit pending | import-only readable/current/pointer assertion | php -l only |
| correction request | not publish | not publish | approval rules | none | EXISTING | none found | no change | not run |
| correction approve | not publish | not publish | approval rules | none | EXISTING | none found | no change | not run |
| correction run | import context cannot publish | replacement requires promote/finalize validation | correction baseline/replacement rules | preserve baseline on failure/unchanged | ENFORCED | local PHPUnit pending | request_mode `correction` / `promote` retained | php -l only |
| correction unchanged | import cannot publish | preserve baseline | artifact comparison | no switch | EXISTING | local PHPUnit pending | no weakening | not run |
| correction failed | import cannot publish | preserve baseline | fail-safe | no switch | EXISTING | local PHPUnit pending | no weakening | not run |
| correction published | forbidden from import | explicit promote/finalize only | correction linkage | switch only if valid | ENFORCED | local PHPUnit pending | static guard covers import publish block | php -l only |
| replay | compares expected/actual request/source/import/promote/pointer context | verifies promoted expected pointer switch | mismatch reason codes | detects unexpected switch | ENFORCED | local PHPUnit pending | added import/promote context | php -l only |
| evidence | exports import/promote boundary | exports promote gate state | evidence proof | records pointer switch bool | ENFORCED | local PHPUnit pending | added `import_promote_boundary` | php -l only |
| session snapshot | read-side must stay pointer-resolved | depends on current readable publication | read-side contract | no import shortcut | EXISTING | local PHPUnit pending | no direct change | not run |
| read-side consumer | import artifacts not readable | pointer-resolved publication only | read contract | no import shortcut | EXISTING | local PHPUnit pending | no direct change | not run |
| command output | prints import/promote statuses | prints gate/readiness status | operator-visible | prints pointer_switched | ENFORCED | local PHPUnit pending | abstract summary extended | php -l only |
| static guard | asserts boundary files/strings | asserts promote gate strings | static proof | detects import side effects | ENFORCED | local PHPUnit pending | new static guard | php -l only |
