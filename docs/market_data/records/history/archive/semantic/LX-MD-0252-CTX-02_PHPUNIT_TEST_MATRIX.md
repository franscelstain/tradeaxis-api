# Legacy Semantic Extract — LX-MD-0252-CTX-02

- Source ID: `LS-MD-0252`
- Original path: `tests/PHPUNIT_TEST_MATRIX.md`
- Original SHA1: `FEC9F51F5D950AD3C0DB1B40F3E0D3C4CD966FFA`
- Extract role: `CONTEXT`
- Source range: `L58-L109`
- Extract body SHA1: `44A6CB287FF71E5F0600B84E10C3A88ED56F0DBA`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## OUT-OF-ORDER IMPORT IMPACT

- `EodBarsMutationImpactResolverTest::test_unchanged_upsert_is_noop_for_indicator_and_publication_impact`
  - membuktikan idempotent/unchanged bars menghasilkan `NOOP_UNCHANGED_BARS`.

- `EodBarsMutationImpactResolverTest::test_historical_changed_bar_resolves_downstream_trading_dates_with_indicator_horizon`
  - membuktikan changed historical bar menghitung affected downstream dates memakai trading days dan horizon indikator baseline 50.

- `EodBarsMutationImpactResolverTest::test_readable_affected_publication_requires_republication_not_silent_update`
  - membuktikan affected readable publication menghasilkan `REQUIRES_REPUBLICATION` dan `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`, bukan silent update.

- `OutOfOrderImportImpactStaticGuardTest`
  - membuktikan replacement EOD bars membuat mutation summary sebelum delete/replace.
  - membuktikan ingest/command/evidence surfaces membawa mutation/impact summaries.
  - membuktikan resolver memakai market calendar, dependency horizon, dan readable-publication correction state.

Latest validation:
- `vendor\bin\phpunit tests\Unit\MarketData --filter "EodBarsMutationImpactResolver"` -> OK (3 tests, 13 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "OutOfOrderImportImpact"` -> OK (3 tests, 32 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData` -> OK (568 tests, 8560 assertions).

## OUT-OF-ORDER IMPORT IMPACT EXECUTION

- `EodArtifactRepositoryPartialUpsertTest`
  - proves recovered row partial upsert preserves unrelated tickers on the same trade date.
  - proves unchanged partial upsert is idempotent and reports `changed_bar_count=0`.

- `MarketDataImpactReprocessExecutorTest`
  - proves changed historical bars execute indicator recompute and eligibility rebuild for affected downstream dates.
  - proves unchanged bars do not recompute.
  - proves readable affected dates block with correction reason instead of silent recompute.

- `MarketDataPipelineServiceTest::test_recovered_rows_partial_apply_runs_impact_reprocess_execution`
  - proves recovered retry rows are applied through the pipeline and impact execution summaries are written to run notes.

- `OutOfOrderImportImpactStaticGuardTest`
  - now also guards partial recovered apply, executor presence, and command output execution fields.

Latest targeted validation:
- `MarketDataImpactReprocessExecutor` -> OK (3 tests, 11 assertions).
- `EodArtifactRepositoryPartialUpsert` -> OK (2 tests, 14 assertions).
- `OutOfOrderImportImpact` -> OK (5 tests, 57 assertions).
- `Recovered` -> OK (7 tests, 56 assertions).
- `Resume` -> OK (8 tests, 61 assertions).
- Full suite: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (576 tests, 8624 assertions).
- Post-doc rerun full suite: OK (576 tests, 8624 assertions), Time 00:19.910, Memory 42.00 MB.

Publication lifecycle proof boundary:
- Current tests prove recovered apply, mutation detection, affected-date detection, indicator recompute execution, eligibility rebuild execution, non-readable downstream promotion, and readable-publication correction-current republication.
- Mixed impacted dates are locked: non-readable dates remain normal publication reprocess candidates, while already-readable dates become correction-current candidates with explicit `republication_mode` and `correction_id` evidence.
- Full out-of-order publication lifecycle lock requires these E2E executor-to-orchestrator/pipeline tests to remain green with the full MarketData suite.


<!-- LEGACY_EXTRACT_BODY_END -->
