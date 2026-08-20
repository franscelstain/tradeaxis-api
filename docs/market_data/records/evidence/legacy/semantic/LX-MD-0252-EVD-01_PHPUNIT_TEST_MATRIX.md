# Legacy Semantic Extract — LX-MD-0252-EVD-01

- Source ID: `LS-MD-0252`
- Original path: `tests/PHPUNIT_TEST_MATRIX.md`
- Original SHA1: `FEC9F51F5D950AD3C0DB1B40F3E0D3C4CD966FFA`
- Extract role: `EVIDENCE`
- Source range: `L26-L57`
- Extract body SHA1: `D3C8B4C314F4564F0F8BC7C1758B2DC2494EF48B`
- Current authority: `NO`
- Current verification effect: `HISTORICAL_ONLY`
- Preservation policy: `EXACT_SOURCE_RANGE_COPY_WITH_METADATA`

<!-- LEGACY_EXTRACT_BODY_START -->
## INTEGRATION / END-TO-END PROOF
- `MarketDataPipelineIntegrationTest::test_run_daily_full_coverage_persists_finalize_coverage_payload_and_readable_publication`
  - membuktikan pipeline nyata `INGEST_BARS -> COMPUTE_INDICATORS -> BUILD_ELIGIBILITY -> HASH -> SEAL -> FINALIZE`
  - run final: `SUCCESS + READABLE`
  - publication current dan pointer requested date terbentuk
  - run + finalize event membawa coverage telemetry lengkap:
    - `coverage_gate_state`
    - `coverage_reason_code`
    - `coverage_available_count`
    - `coverage_universe_count`
    - `coverage_missing_count`
    - `coverage_min_threshold`
    - `coverage_contract_version`

- `MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_with_fallback_holds_requested_date_and_preserves_old_readable_publication`
  - membuktikan low coverage tidak mempromosikan publication requested date
  - run final: `HELD + NOT_READABLE`
  - fallback readable publication lama tetap current dan pointer lama tetap aman
  - finalize event memakai `RUN_COVERAGE_LOW`, bukan lock-conflict palsu

- `MarketDataPipelineIntegrationTest::test_run_daily_low_coverage_without_fallback_finishes_not_readable_and_emits_coverage_reason_code`
  - membuktikan low coverage tanpa fallback berakhir `FAILED + NOT_READABLE`
  - candidate publication tetap non-current
  - pointer requested date tidak dibuat
  - finalize event tetap sinkron dengan `RUN_COVERAGE_LOW`

- `MarketDataPipelineIntegrationTest::test_finalize_blocked_without_universe_stays_not_readable_and_emits_blocked_coverage_reason_code`
  - membuktikan finalize path untuk coverage `NOT_EVALUABLE` dari zero/unsafe universe dan legacy input
  - run final: `FAILED + NOT_READABLE + coverage_gate_state=NOT_EVALUABLE + quality_gate_state=BLOCKED`
  - pointer/current publication tidak dipromosikan
  - finalize event sinkron dengan `RUN_COVERAGE_NOT_EVALUABLE`


<!-- LEGACY_EXTRACT_BODY_END -->
