# Legacy Semantic Extract — LX-MD-0003-EVD-02

- Source ID: `LS-MD-0003`
- Original path: `audit/AUDIT_DOCS_SYNCHRONIZATION_INVENTORY.md`
- Original SHA1: `29D6BAB13EE1A62947406EB10F568D260DB48E34`
- Extract role: `EVIDENCE`
- Source range: `L78-L93`
- Extract body SHA1: `714B65656ED6D1307E34DCC1492621F3B6A0CB22`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation State

| Validation | Result | Tests | Assertions | Notes |
|---|---:|---:|---:|---|
| Static trace | COMPLETED | - | - | Audit docs, registry/seed, and static guard surfaces were inspected. |
| `php -l tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | PASS | - | - | Container syntax validation only. |
| `vendor/bin/phpunit tests/Unit/MarketData/AuditDocsSynchronizationStaticGuardTest.php` | OK | 9 | 153 | Operator-local PASS. |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "AuditDocs"` | OK | 9 | 153 | Operator-local PASS. |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 93 | 2160 | Operator-local PASS. |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK | 39 | 678 | Operator-local PASS. |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 358 | 4711 | Operator-local full suite PASS. |

Latest carried local full-suite baseline remains recorded for regression continuity: full `vendor/bin/phpunit tests/Unit/MarketData` OK (349 tests, 4558 assertions) from the Fail-Safe Behavior / No Silent Failure session. That baseline is not a new container PHPUnit run.

---


<!-- LEGACY_EXTRACT_BODY_END -->
