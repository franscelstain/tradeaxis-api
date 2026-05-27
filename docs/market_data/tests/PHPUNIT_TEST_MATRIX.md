# TEST MATRIX COVERAGE GATE

## UNIT / SERVICE LEVEL

### PASS
- 900/900 → PASS
- 890/900 (>= threshold) → PASS

### FAIL
- 880/900 (< threshold) → FAIL

### PRIORITY FAIL
- overall PASS tapi priority FAIL → FAIL

### NOT_EVALUABLE
- expected = 0 -> coverage_gate_state = NOT_EVALUABLE, quality_gate_state = BLOCKED, publishability_state = NOT_READABLE

## FINALIZE DECISION
- FAIL + fallback → HELD + NOT_READABLE
- FAIL tanpa fallback → FAILED + NOT_READABLE
- PASS → SUCCESS + READABLE
- NOT_EVALUABLE tanpa fallback -> FAILED + NOT_READABLE, quality_gate_state = BLOCKED

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
- Current tests prove recovered apply, mutation detection, affected-date detection, indicator recompute execution, eligibility rebuild execution, and readable-publication safe block.
- Current tests do not prove automatic downstream hash recompute, seal/finalize execution, or automatic correction/republication for already-readable affected dates.
- Full out-of-order publication lifecycle lock requires an additional E2E test scope for hash/seal/republication execution.

## OUT-OF-ORDER IMPORT HASH/SEAL/PUBLICATION REPROCESS

- `BackfillLifecyclePublicationReprocessTest`
  - proves lifecycle publication reprocess consumes `PENDING_PROMOTE` and calls `promoteDaily()` for affected non-readable downstream dates.
  - proves evidence export and replay verification are triggered for automatically republished downstream dates when requested.
  - proves already-readable affected dates block with `AFFECTED_PUBLICATION_REQUIRES_CORRECTION`.
  - proves the primary requested date is not left `PENDING_PROMOTE` after primary promote already handled it.

- `MarketDataPipelineServiceTest::test_run_daily_promotes_non_readable_downstream_impact_dates_after_primary_finalize`
  - proves the full-publish daily pipeline can promote affected downstream non-readable dates after the primary requested date finalizes.

- `OutOfOrderImportImpactStaticGuardTest`
  - guards that lifecycle publication reprocess calls `promoteDaily()`.
  - guards that `promoteDaily()` still includes `completeHash`, `completeSeal`, and `completeFinalize`.

Latest validation:
- `BackfillLifecyclePublicationReprocess` -> OK (3 tests, 11 assertions).
- `MarketDataPipelineService` -> OK (16 tests, 21 assertions).
- `MarketDataImpactReprocessExecutor` -> OK (3 tests, 12 assertions).
- `OutOfOrderImportImpactStaticGuard` -> OK (6 tests, 73 assertions).
- `Daily` -> OK (63 tests, 1236 assertions).
- `Backfill` -> OK (47 tests, 303 assertions).
- `StaticGuard` -> OK (225 tests, 5483 assertions).
- Full suite: `vendor\bin\phpunit tests\Unit\MarketData` -> OK (581 tests, 8654 assertions), Time 00:12.483, Memory 44.00 MB.
- Final post-doc/code full-suite rerun: OK (581 tests, 8654 assertions), Time 00:12.737, Memory 44.00 MB.

Remaining proof boundary:
- Tests prove automatic publication reprocess for affected non-readable dates.
- Automatic correction/republication for already-readable affected dates remains intentionally blocked/manual and is not claimed as locked.


## FINAL VALIDATION - IMPORT-ONLY BACKFILL OUTPUT + READABLE AUTO-CORRECTION

Final local validation after the correction-current static guard fix:

- `vendor\bin\phpunit tests\Unit\MarketData --filter "BackfillLifecyclePublicationReprocess"` -> OK (3 tests, 12 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "OutOfOrderImportImpact"` -> OK (7 tests, 96 assertions).
- `vendor\bin\phpunit tests\Unit\MarketData --filter "Backfill"` -> OK (48 tests, 326 assertions).
- `php vendor/bin/phpunit tests/Unit/MarketData` -> OK (582 tests, 8678 assertions), Time 00:19.091, Memory 42.00 MB.

Locked assertions:

- Publication reprocess keeps `correction_current` visible and enforced for already-readable affected-date auto-correction.
- Import-only backfill exposes execution-layer output fields.
- Full MarketData suite passes after the patch.
