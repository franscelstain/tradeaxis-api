# Legacy Semantic Extract — LX-MD-0025-EVD-03

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `EVIDENCE`
- Source range: `L85-L106`
- Extract body SHA1: `E2B3F7488A1150EBAA51B1DF218BDA62486061F8`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Validation matrix

| Command | Result | Tests | Assertions | Status |
|---|---:|---:|---:|---|
| `php -l app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l app/Application/MarketData/Services/MarketDataEvidenceExportService.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l tests/Unit/MarketData/MarketDataEvidenceExportServiceTest.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php -l tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php` | No syntax errors detected | n/a | n/a | PASS_STATIC |
| `php vendor/bin/phpunit --version` | blocked by missing `dom`, `mbstring`, `xml`, `xmlwriter` | n/a | n/a | BLOCKED_CONTAINER_RUNTIME_ENV |
| `vendor/bin/phpunit tests/Unit/MarketData/EvidenceHistoricalLineageCompletenessStaticGuardTest.php` | OK | 5 | 51 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"` | OK | 52 | 906 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"` | OK | 45 | 743 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"` | OK | 68 | 1336 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Publication"` | OK | 103 | 1252 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Pointer"` | OK | 79 | 1147 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Readable"` | OK | 57 | 426 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "ReadSide"` | OK | 13 | 258 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"` | OK | 49 | 359 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "Integration"` | OK | 91 | 1450 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"` | OK | 135 | 2952 | PASS_OPERATOR_LOCAL |
| `vendor/bin/phpunit tests/Unit/MarketData` | OK | 403 | 5542 | PASS_OPERATOR_LOCAL |


<!-- LEGACY_EXTRACT_BODY_END -->
