# Finding — `F-MD-B19-A001-012`

- ID: `F-MD-B19-A001-012`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised / resolved: 2026-10-17
- Severity: `P2`
- Status: `RESOLVED`
- Class: `PRODUCTION_CLASSIFICATION_BOUNDARY_DEFECT`
- Predicate: `MD-S075-R0260`

## Demonstrated defect

The real comparator correctly produces MATCH/PASS, EXPECTED_DEGRADE/PASS, MISMATCH/FAIL, UNEXPECTED/FAIL and prerequisite NOT_ADMISSIBLE/BLOCKED. `ReplayResultRepository::upsertMetric()` nevertheless trusted an explicit replay_status over that mapping, and `MarketDataEvidenceExportService::exportReplayEvidence()` trusted a contradictory persisted status. Their default mapping also treated an unknown comparison_result as BLOCKED. Ten focused red cases demonstrated both boundaries accepting four contradictory pairs and an unknown comparison. The separate actual run status is not replay_status and must never substitute for it.

## Bounded correction and proof

Both boundaries now validate comparison_result against the existing five-value persisted vocabulary and reject an explicit disagreement before writing/updating a metric or creating export files. A NULL replay_status on an otherwise valid historical row is derived from comparison_result for read compatibility; exporting does not rewrite that row. A NULL supplied at the write boundary is stored with its derived classification. No new comparison or reason vocabulary is introduced.

`B19ReplayResultSemanticPipelineTest` borrows the retained real persisted run/publication/capture/lineage fixture, then executes the real verifier, repository and JSON exporter. Literal fixtures are authored independently of actual outputs and versioned as b19-v1/b19-v2. They are behavior-contract probes, not a new R0025 golden fixture or candidate admission. Positive/negative outcome, missing expected context, unavailable runtime observation identity, self-generated oracle refusal, historical NULL and invalid-pair cases execute end to end. `E-MD-B19-A001-018` records the red/green and new mutation proof.

The accepted stored-field fixture's synthetic comparison_result=`BLOCKED` was not a production enum member; its row now uses the production name `NOT_ADMISSIBLE`, with replay_status still BLOCKED. This corrects only that fixture vocabulary. The accepted field-provenance test is re-executed; its other expectations and prior immutable evidence remain intact.

## Impact

`ReplayResultRepository.php` is the sixth executable-build file differing from candidate-v5; `MarketDataEvidenceExportService.php` was already in the set. `MD-DEP-0025` remains successor-pending ACTIVE. Invalid legacy contradictions are refused rather than silently recategorized; no stored history is migrated. No strategy, schema, candidate-v5 or MD-B18 closure change. `F-MD-B19-A001-010` and `F-MD-B19-A001-011` remain OPEN.
