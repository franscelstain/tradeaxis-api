# Legacy Semantic Extract — LX-MD-0025-GOV-01

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `GOVERNANCE`
- Source range: `L10-L27`
- Extract body SHA1: `74D2573CE828A2922956DBF648CA7E302824EDA2`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Scope

This inventory records the hardening that separates the consumer read resolver from the evidence audit resolver. The consumer read resolver tetap current-pointer-only. The evidence audit resolver is selector-scoped and lineage-validated so a historical sealed publication can be proven after it is no longer the current pointer.

This session does not weaken the read-side consumer contract. Consumer/API/dashboard/session snapshot paths must still use the current readable publication pointer through `resolveCurrentReadablePublicationForTradeDate()` / `findReadableCurrentPublicationForRun()` and must not read historical non-current publication data as consumer data.

## Existing contract owner

| Existing Contract / Test / Doc | Role | Current Status | Relevance | Reuse / Extend / Do Not Touch |
|---|---|---|---|---|
| `READ_SIDE_CONSUMER_CURRENT_POINTER_CONTRACT` | Consumer read source of truth | LOCKED prior session | Must remain current-pointer-only | Do not weaken |
| `RUN_PUBLICATION_POINTER_LINKAGE_CONTRACT` | Run → publication → pointer linkage | LOCKED prior session | Defines mirror/linkage invariants | Extend evidence proof only |
| `PRODUCTION_VALIDATION_CONTRACT` | Runtime proof authority | LOCKED prior session | DONE/LOCKED requires operator-local proof | Preserve status rules |
| `EvidenceExportCompletenessStaticGuardTest` | Existing evidence completeness guard | ENFORCED prior session | Existing evidence output sections | Extend with historical lineage guard |
| `MarketDataEvidenceExportService` | Evidence export surface | PATCHED this session | Needed historical resolver usage | Patch evidence path only |
| `EodEvidenceRepository` | Evidence repository | PATCHED this session | Owns audit resolver | Extend safely |
| `EodPublicationRepository` | Consumer/current pointer resolver | UNCHANGED consumer contract | Must stay current-pointer-only | Do not change consumer resolver |


<!-- LEGACY_EXTRACT_BODY_END -->
