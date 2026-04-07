# TEST MATRIX COVERAGE GATE

## UNIT / SERVICE LEVEL

### PASS
- 900/900 → PASS
- 890/900 (>= threshold) → PASS

### FAIL
- 880/900 (< threshold) → FAIL

### PRIORITY FAIL
- overall PASS tapi priority FAIL → FAIL

### BLOCKED
- expected = 0 → BLOCKED

## FINALIZE DECISION
- FAIL + fallback → HELD + NOT_READABLE
- FAIL tanpa fallback → FAILED + NOT_READABLE
- PASS → SUCCESS + READABLE
- BLOCKED tanpa fallback → FAILED + NOT_READABLE

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
  - membuktikan finalize path untuk `BLOCKED`
  - run final: `FAILED + NOT_READABLE + BLOCKED`
  - pointer/current publication tidak dipromosikan
  - finalize event sinkron dengan `RUN_COVERAGE_NOT_EVALUABLE`
