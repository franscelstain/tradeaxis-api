# Legacy Semantic Extract — LX-MD-0239-EVD-01

- Source ID: `LS-MD-0239`
- Original path: `tests/Behavioral_Test_Coverage_Inventory.md`
- Original SHA1: `742C746586E0501E4D5B9983BCDE37F7B3DFC30F`
- Extract role: `EVIDENCE`
- Source range: `L42-L63`
- Extract body SHA1: `2B3EE36BA28B5DEDD94459C0AF3DE00C15655D08`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## Existing behavioral proof source

Primary lifecycle proof must come from:

- `MarketDataPipelineIntegrationTest`
- `PublicationRepositoryIntegrationTest`
- `ReadablePublicationReadContractIntegrationTest`
- `CorrectionRepositoryIntegrationTest`
- `ReplayResultRepositoryIntegrationTest`
- `MarketDataSqliteSchemaSyncTest`

## Mock-heavy tests that must not be counted as lifecycle proof

The following tests are still useful, but only as support/surface tests:

- `OpsCommandSurfaceTest` because it mocks `MarketDataPipelineService`, `MarketDataBackfillService`, `SessionSnapshotService`, `ReplaySmokeSuiteService`, `ReplayBackfillService`, and evidence repositories.
- `MarketDataEvidenceExportServiceTest` because it mocks persistence repositories for focused export-shape assertions.
- `ReplayEvidenceExportServiceTest` because it mocks evidence/publication/correction repositories for focused replay export assertions.
- `ReplaySmokeSuiteServiceTest` and `ReplayBackfillServiceTest` because they mock replay/evidence services for orchestration behavior.
- `SessionSnapshotServiceTest` because it mocks publication/run/snapshot repositories and local adapter.
- `MarketDataBackfillServiceTest` because it mocks pipeline service for orchestration failure behavior.


<!-- LEGACY_EXTRACT_BODY_END -->
