# Legacy Semantic Extract — LX-MD-0028-EVD-01

- Source ID: `LS-MD-0028`
- Original path: `audit/HASH_SEAL_DATASET_INTEGRITY_INVENTORY.md`
- Original SHA1: `C8D94C9D62FC23B2978DB75D31E851645BBF5CCF`
- Extract role: `EVIDENCE`
- Source range: `L62-L80`
- Extract body SHA1: `2CCAF1AB17DF37967142E959132892E1407B4BC0`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Historical manual validation command list
Run locally:

```bash
vendor/bin/phpunit tests/Unit/MarketData/HashSealDatasetIntegrityStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData/DeterministicHashServiceTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "Hash"
vendor/bin/phpunit tests/Unit/MarketData --filter "Seal"
vendor/bin/phpunit tests/Unit/MarketData --filter "Integrity"
vendor/bin/phpunit tests/Unit/MarketData --filter "Finalize"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "StaticGuard"
vendor/bin/phpunit tests/Unit/MarketData
```



<!-- LEGACY_EXTRACT_BODY_END -->
