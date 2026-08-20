# Legacy Semantic Extract — LX-MD-0025-GOV-02

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `GOVERNANCE`
- Source range: `L56-L65`
- Extract body SHA1: `13D0773F1BDD45D31B13A8E3711FDE30F3981B69`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Artifact scope matrix

| Artifact Type | Current Evidence Source | Historical Evidence Source | Publication Scoped? | Missing Artifact Behavior | Status |
|---|---|---|---:|---|---|
| eligibility CSV | `eod_eligibility` when current and history absent | `eod_eligibility_history` for non-current | yes | empty export; no current fallback for historical | PATCHED |
| dominant reason codes | run events + current publication-scoped eligibility | run events + historical publication-scoped eligibility | yes | run event reason codes still exported | PATCHED |
| manifest/hash proof | `buildManifestByPublicationId(publication_id)` | same explicit publication id | yes | audit resolver blocks missing hashes | PATCHED |
| correction baseline/candidate | publication ids from correction lineage | `resolvePublicationForEvidenceAudit(publication_id/run_id)` | yes | reason-coded failed proof | PATCHED |
| replay expected/actual | replay metric context | replay metric context | yes | context marked no publication if missing | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
