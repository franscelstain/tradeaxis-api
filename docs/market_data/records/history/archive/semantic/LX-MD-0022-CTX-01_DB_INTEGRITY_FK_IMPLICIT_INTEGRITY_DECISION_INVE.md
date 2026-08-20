# Legacy Semantic Extract — LX-MD-0022-CTX-01

- Source ID: `LS-MD-0022`
- Original path: `audit/DB_INTEGRITY_FK_IMPLICIT_INTEGRITY_DECISION_INVENTORY.md`
- Original SHA1: `BA5CB0819D76C0ADAEA2600174DA40EF3CFF16A3`
- Extract role: `CONTEXT`
- Source range: `L103-L129`
- Extract body SHA1: `1C37750A2277C0276686CDABF57A62D0DDB17728`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## FK Candidate Matrix

| FK Candidate | From Table | From Column | To Table | To Column | On Delete/Update | Add / Reject / Defer | Reason |
|---|---|---|---|---|---|---|---|
| pointer publication | `eod_current_publication_pointer` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Stable target relation |
| bars history publication | `eod_bars_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| indicators history publication | `eod_indicators_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| eligibility history publication | `eod_eligibility_history` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Add already present | Immutable proof relation |
| live bars publication | `eod_bars` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Current live table is replaceable and phase-dependent; use implicit guard |
| live indicators publication | `eod_indicators` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Same as live bars |
| live eligibility publication | `eod_eligibility` | `publication_id` | `eod_publications` | `publication_id` | restrict/default | Reject for now | Same as live bars |
| live artifact ticker | live artifact tables | `ticker_id` | `tickers` | `ticker_id` | restrict/default | Defer with reason | Requires data cleanup/operator DB introspection and test strategy before physical FK |
| publication run | `eod_publications` | `run_id` | `eod_runs` | `run_id` | restrict/default | Reject for now | Avoid circular lifecycle/finalize issue; mirror guard is stronger than FK alone |
| correction prior/new run/publication | `eod_dataset_corrections` | nullable lineage ids | runs/publications | ids | restrict/default | Reject for now | Nullable phase-dependent lifecycle relation |

## Implicit Guard Matrix

| Guard | File | Method | Relation Protected | Failure Behavior | Test Coverage | Status |
|---|---|---|---|---|---|---|
| Live sealed baseline mutation guard | `EodArtifactRepository.php` | `assertLiveArtifactMutationAllowed` | current live artifact vs sealed/current/readable publication | Throws `SEALED_DATASET_MUTATION_BLOCKED` | Hash/seal and static guards | Present |
| Current pointer integrity reasons | `EodPublicationRepository.php` | `determineCurrentIntegrityViolationReasons` | pointer/publication/run/coverage/readable mirror | Returns reason list / fails pointer resolution | `CurrentPointerIntegrityScanTest` drives 17 broken states through the scan, the consumer read, and the repair command | Present |
| Post-switch pointer validation | `EodPublicationRepository.php` | `assertCurrentPointerResolvedAfterSwitch` | finalize pointer target | Throws reason-coded runtime error | Publication/finalize tests | Present |
| Publication integrity context | `EodPublicationRepository.php` | `assertPublicationIntegrityContextComplete` | publication/run hash/seal state | Throws before unsafe publish | Hash/seal/finalize tests | Present |
| Evidence audit resolver | `EodEvidenceRepository.php` | `resolvePublicationForEvidenceAudit` | historical publication proof | Reason-coded missing/mismatch context | Evidence historical guard | Present |
| Replay actual-state resolver | `ReplayVerificationService.php` | `resolvePublicationForReplayActualState` | historical replay publication proof | Reason-coded replay mismatch/failure | Replay historical guard | Present |
| Correction lifecycle repository | `EodCorrectionRepository.php` | correction status/linkage methods | correction prior/new/baseline/replacement linkage | Refuses invalid lifecycle transition | Correction tests/static guard | Present |


<!-- LEGACY_EXTRACT_BODY_END -->
