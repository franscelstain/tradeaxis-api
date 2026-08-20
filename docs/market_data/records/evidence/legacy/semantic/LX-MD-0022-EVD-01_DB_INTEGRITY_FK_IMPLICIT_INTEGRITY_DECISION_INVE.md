# Legacy Semantic Extract — LX-MD-0022-EVD-01

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `EVIDENCE`
- Source range: `L139-L149`
- Extract body SHA1: `E55BB055DD0898CB2798641F71D36DA736FB9111`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation Matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS |
| `php vendor/bin/phpunit tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | Blocked in container: missing `dom`, `mbstring`, `xml`, `xmlwriter` | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |
| `vendor/bin/phpunit tests/Unit/MarketData/DbIntegrityFkImplicitIntegrityDecisionStaticGuardTest.php` | OK | 5 | 434 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "DbIntegrity"` | OK | 11 | 874 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 146 | 3470 | PASS_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 416 | 6066 | PASS_LOCAL |


<!-- LEGACY_EXTRACT_BODY_END -->
