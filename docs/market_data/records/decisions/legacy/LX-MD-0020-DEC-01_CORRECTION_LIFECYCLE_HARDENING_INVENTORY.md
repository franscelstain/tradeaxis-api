# Legacy Semantic Extract — LX-MD-0020-DEC-01

- Source ID: `LS-MD-0020`
- Original path: `audit/CORRECTION_LIFECYCLE_HARDENING_INVENTORY.md`
- Original SHA1: `CF3BD55641F75EDA47DC3EB456D1824632863949`
- Extract role: `DECISION`
- Source range: `L16-L39`
- Extract body SHA1: `EF2D39976DC78772F1A6AC35B5CD847E3E7BFDDC`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Decision Matrix

| Correction Area | Contract Required | Code Enforces | Test Proves | Runtime Proves | Gap |
|---|---|---|---|---|---|
| correction request | request must have valid current sealed readable PASS baseline | `RequestCorrectionCommand` resolves `findCorrectionBaselinePublicationForTradeDate` before create | `CorrectionCommandsTest` baseline success/block | `correction_id=3`, baseline publication `5`, baseline run `6` | none |
| correction approval | only approved correction can execute | `RunCorrectionCommand` status guard + repository status guard | `CorrectionCommandsTest`, `MarketDataPipelineIntegrationTest` | `correction_id=3` approved by `codex` | none |
| execution eligibility | approved, date-match, non-consumed, mode valid | `EodCorrectionRepository::canExecuteCorrection` + pipeline baseline recheck | integration/static guards | run `8` executed after approval | none |
| baseline publication lookup | pointer-resolved current readable baseline | `EodPublicationRepository::findCorrectionBaselinePublicationForTradeDate` | `CorrectionLifecycleSafetyStaticGuardTest` | baseline publication `5`, run `6` | none |
| baseline readable requirement | SUCCESS/READABLE/SEALED/PASS + mirror | baseline resolver predicates | static guard + pipeline tests | request printed baseline ids | none |
| coverage gate requirement | PASS required before readable/publish | finalize/coverage services | Coverage/Correction filters | run `8` coverage PASS | none |
| seal/finalize requirement | candidate cannot become current unless sealed/finalized | publication repository + pipeline finalize | Publication/Finalize/Pointer filters | unchanged path did not promote candidate | none |
| pointer replacement | replacement only after valid changed candidate | `promoteCandidateToCurrent` baseline guard | pipeline pointer failure tests | pointer stayed publication `5`/run `6` | none |
| unchanged correction behavior | preserve current pointer, no reseal, no candidate switch | pipeline discard + command/evidence switch false | command/evidence/pipeline tests | `candidate_publication_id=7` discarded; `candidate_publication_switch=false`; evidence `publication_switch=0` | none |
| failed correction behavior | failed candidate never current | pipeline restore/fail-safe handling + command failure handling | `CorrectionCommandsTest`, `CorrectionRepositoryIntegrationTest`, `MarketDataPipelineIntegrationTest` failure cases | `correction_id=4`, candidate `run_id=11`, status `FAILED`, baseline pointer preserved | none |
| reseal guard | sealed publication immutable | `assertPublicationMutable` | repository/static tests | unchanged proof did not reseal baseline | none |
| force replace guard | force requires reason | promote command guard | command static/ops tests | promote help shows force reason options | none |
| repair candidate guard | repair apply requires reason | repair command guard | `OpsCommandSurfaceTest` + static guard | apply without reason blocked | none |
| run/publication/correction linkage | correction links prior/new run and baseline/replacement publication | correction repository + evidence repository | repository/evidence/pipeline tests | correction `3`: prior run `6`, new run `8`, baseline publication `5`, replacement null | none |
| evidence export linkage | correction evidence records lineage and unchanged result | evidence export service | `CorrectionEvidenceExportServiceTest` | correction `3` export ADMITTED_COMPLETE | none |
| replay verification linkage | replay stores/compares correction context when fixture resolves | replay service/repository preserved-baseline semantics | `ReplayVerificationServiceTest`, `ReplayEvidenceExportServiceTest`, Replay filter/static guard | fixture generation for run `8` succeeded; verify `replay_id=10` PASS/MATCH | none |
| command output | operator sees baseline, candidate, switch, reason | correction/repair commands | command tests | request/run/repair outputs captured | none |
| audit docs entry | active session and canonical entry synchronized | ledger + static guard | audit docs guard after docs update | correction scope locked; older aggregate production-ready downgrade is superseded by later proof-pack and global-lock entries | none |
| contract tracker entry | one canonical correction contract | tracker + static guard | audit docs guard after docs update | `CORRECTION_LIFECYCLE_SAFETY_CONTRACT -> LOCKED` | none |


<!-- LEGACY_EXTRACT_BODY_END -->
