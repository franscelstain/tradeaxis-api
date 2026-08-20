# Legacy Semantic Extract — LX-MD-0025-CTX-01

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `CONTEXT`
- Source range: `L46-L55`
- Extract body SHA1: `54EB351A2472536927DF9C0A68C636C23A3C4E64`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Historical publication risk matrix

| File | Method | Pattern | Evidence Path? | Current Pointer Dependency | Historical Risk | Action | Status |
|---|---|---|---:|---:|---|---|---|
| `MarketDataEvidenceExportService.php` | `resolvePublicationForRun()` | previously current-readable resolver | yes | yes before patch | historical sealed run could not be proven after replacement | switched to evidence audit resolver | PATCHED |
| `EodEvidenceRepository.php` | `dominantReasonCodes()` / `exportEligibilityRows()` | current pointer readable context | yes before patch | yes before patch | historical evidence CSV/reason codes could be empty due current pointer dependency | added evidence-specific publication-scoped methods | PATCHED |
| `MarketDataEvidenceExportService.php` | `buildPointerContext()` | assumed any publication means current pointer | yes | implicit before patch | historical evidence could be labeled as current | added historical pointer status/reason | PATCHED |
| `exportReplayEvidence()` | replay output | replay metric only | yes | no current lookup | historical context fields incomplete | added replay publication audit context fields | PATCHED |
| `exportCorrectionEvidence()` | correction output | baseline/candidate ids | yes | no current lookup | proof not lineage-validated | added historical baseline/candidate proof | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
