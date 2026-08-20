# Legacy Semantic Extract — LX-MD-0025-EVD-02

- Source ID: `LS-MD-0025`
- Original path: `audit/EVIDENCE_HISTORICAL_LINEAGE_COMPLETENESS_INVENTORY.md`
- Original SHA1: `41DCC6ED7C59A480873EA7C1F71EEB470396403D`
- Extract role: `EVIDENCE`
- Source range: `L28-L45`
- Extract body SHA1: `FA51EA367294562337AF600E8957CBEEA2640EF3`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Evidence selector resolver matrix

| Selector | Entrypoint | Resolver Used | Current Pointer Required | Historical Sealed Allowed | Lineage Validation | Artifact Scope | Status |
|---|---|---:|---:|---:|---|---|---|
| `run_id` | `market-data:evidence:export --run_id=...` | `EodEvidenceRepository::resolvePublicationForEvidenceAudit` | false for historical, true only when current pointer matches | yes | run/publication mirror, trade date, seal, coverage, artifact hash | `PUBLICATION_SCOPED` | PATCHED |
| `correction_id` | `market-data:evidence:export --correction_id=...` | correction record + `buildHistoricalPublicationAuditProof()` | false for baseline historical proof | yes | baseline/candidate publication proof reason-coded | `PUBLICATION_SCOPED` | PATCHED |
| `replay_id` + explicit `trade_date` | `market-data:evidence:export --replay_id=... --trade_date=...` | replay metric context | false when metric publication is non-current | yes as replay audit context | expected/actual context carries historical lineage fields | `PUBLICATION_SCOPED` | PATCHED |
| `publication_id` | not exposed as command selector in this ZIP | repository audit resolver supports explicit publication_id | false for historical | yes | selector/publication/run validation | `PUBLICATION_SCOPED` | REPOSITORY_READY |

## Consumer vs evidence resolver matrix

| Resolver | File | Method | Used By | Current Pointer Required | Historical Sealed Allowed | Validation Required | Status |
|---|---|---|---|---:|---:|---|---|
| Consumer resolver | `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` | `resolveCurrentReadablePublicationForTradeDate()` | consumer/API/session snapshot/read paths | yes | no | pointer row, SEALED, SUCCESS, READABLE, coverage PASS, run mirror | UNCHANGED |
| Consumer run resolver | `app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php` | `findReadableCurrentPublicationForRun()` | consumer-like current run resolution | yes | no | pointer row and current mirrors | UNCHANGED |
| Evidence audit resolver | `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit()` | evidence export, correction proof helper | selector-scoped; not required for historical | yes | publication exists, selector matches, run/publication mirror, trade date, seal, coverage telemetry, artifact hashes | PATCHED |
| Evidence artifact export | `app/Infrastructure/Persistence/MarketData/EodEvidenceRepository.php` | `exportEligibilityRowsForEvidencePublication()` | evidence CSV export | no | yes | `publication_id` scoped query; history table for non-current | PATCHED |


<!-- LEGACY_EXTRACT_BODY_END -->
