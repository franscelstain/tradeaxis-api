# Legacy Semantic Extract — LX-MD-0025-IMP-01

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `IMPLEMENTATION`
- Source range: `L74-L84`
- Extract body SHA1: `7563E54CA40B91986B51AFB2D33432DFAE4CF543`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Patch matrix

| Gap | File | Change | Why Safe | Test Coverage | Status |
|---|---|---|---|---|---|
| Run evidence depended on current pointer resolver | `MarketDataEvidenceExportService.php` | `resolvePublicationForRun()` now uses `EodEvidenceRepository::resolvePublicationForEvidenceAudit()` | consumer resolver unchanged | unit test + static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Historical artifact export was current-readable scoped | `EodEvidenceRepository.php` | added `dominantReasonCodesForEvidencePublication()`, `exportEligibilityRowsForEvidencePublication()`, `evidenceEligibilityQuery()` | explicit `publication_id`; history table for non-current | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Historical publication output could look like current | `MarketDataEvidenceExportService.php` | added resolution mode/current pointer status/historical flags | explicit audit labels | unit test added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Correction proof lacked resolver validation | `MarketDataEvidenceExportService.php` | added `buildHistoricalPublicationAuditProof()` | reason-coded failure, no fallback | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Replay proof lacked historical lineage labels | `MarketDataEvidenceExportService.php` | added replay publication audit context fields | output-only, no consumer impact | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |
| Audit docs lacked session inventory | `docs/market_data/audit/**` | added inventory and audit entries | append-only | static guard added | DONE_LOCKED_BY_OPERATOR_LOCAL_PHPUNIT |


<!-- LEGACY_EXTRACT_BODY_END -->
