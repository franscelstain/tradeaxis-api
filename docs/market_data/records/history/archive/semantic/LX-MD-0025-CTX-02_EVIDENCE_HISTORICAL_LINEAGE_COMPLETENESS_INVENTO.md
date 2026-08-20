# Legacy Semantic Extract — LX-MD-0025-CTX-02

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `CONTEXT`
- Source range: `L66-L73`
- Extract body SHA1: `798E5357C2FF8D0A78EEFD1FABBC88E81CE1C32B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Correction / replay lineage matrix

| Surface | Required Historical Lineage Fields | Present? | Gap | Action |
|---|---|---:|---|---|
| correction evidence | baseline publication/run, candidate publication/run, seal state, current flag, scope, reason code | yes | prior output lacked resolver proof | added `baseline_historical_publication_proof` and `candidate_historical_publication_proof` |
| replay evidence | actual/expected publication id, run id, version, current flag, resolution mode, pointer requirement, artifact scope | yes | replay context did not label historical publication | added `buildReplayPublicationAuditContext()` |
| run evidence | resolution mode, selector, current pointer status, artifact scope, coverage basis, lineage status | yes | current resolver dependency | added evidence audit resolver output fields |


<!-- LEGACY_EXTRACT_BODY_END -->
