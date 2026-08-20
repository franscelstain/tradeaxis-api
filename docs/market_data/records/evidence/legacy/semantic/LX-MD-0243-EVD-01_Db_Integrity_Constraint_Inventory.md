# Legacy Semantic Extract — LX-MD-0243-EVD-01

- Source ID: `LS-MD-0243`
- Original path: `tests/Db_Integrity_Constraint_Inventory.md`
- Original SHA1: `F8EC0B923A05E4141D9FEF6A1E71E132AA698D5B`
- Extract role: `EVIDENCE`
- Source range: `L39-L73`
- Extract body SHA1: `128791EB24BA19FB33026B0E7DFFC7C933E425B8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation policy

Final validation has passed locally. Future changes touching market-data schema, repository read paths, pointer/publication lifecycle, reason codes, or SQLite mirror must re-run at minimum:

- `php artisan migrate:fresh --env=testing`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Schema"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Migration"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Reason"`
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"`
- `vendor/bin/phpunit tests/Unit/MarketData`

## Final validation result

Operator-local validation supplied after fix2:

- `vendor/bin/phpunit tests/Unit/MarketData --filter "Repository"` -> OK (38 tests, 220 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` -> OK (65 tests, 837 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` -> OK (90 tests, 1007 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Coverage"` -> OK (48 tests, 527 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` -> OK (91 tests, 1443 assertions)
- `vendor/bin/phpunit tests/Unit/MarketData` -> OK (305 tests, 3795 assertions)

Final status: `DONE` / `LOCKED`.

---


<!-- LEGACY_EXTRACT_BODY_END -->
