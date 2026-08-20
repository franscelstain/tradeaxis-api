# Legacy Semantic Extract — LX-MD-0252-EVD-02

- Source ID: `LS-MD-0252`
- Original path: `tests/PHPUNIT_TEST_MATRIX.md`
- Original SHA1: `FEC9F51F5D950AD3C0DB1B40F3E0D3C4CD966FFA`
- Extract role: `EVIDENCE`
- Source range: `L140-L153`
- Extract body SHA1: `D818823289F28C03D063745D54C80F4A22FBD235`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## FINAL VALIDATION - IMPORT-ONLY BACKFILL OUTPUT + READABLE AUTO-CORRECTION

Final local validation after the correction-current static guard fix:

- `vendor\bin\phpunit tests\Unit\MarketData --filter "BackfillLifecyclePublicationReprocess"` -> OK (4 tests, 19 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "OutOfOrderImportImpact"` -> OK (7 tests, 107 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Backfill"` -> OK (49 tests, 339 assertions).
- `php vendor/bin/phpunit tests/Unit/MarketData` -> OK (585 tests, 8713 assertions), Time 00:20.142, Memory 44.00 MB.

Locked assertions:

- Publication reprocess keeps `correction_current` visible and enforced for already-readable affected-date auto-correction.
- Import-only backfill exposes execution-layer output fields.
- Full MarketData suite passes after the patch.

<!-- LEGACY_EXTRACT_BODY_END -->
