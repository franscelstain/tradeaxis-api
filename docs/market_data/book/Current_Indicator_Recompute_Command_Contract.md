# Current Indicator Recompute Command Contract

Status: LOCKED_RUNTIME_FULL_RANGE_AND_FINAL_REPLAY_PASS

## Purpose

`market-data:eod-indicators:recompute-current` exists for the case where current OHLCV/source data is already official and the operator only needs to recompute publication-bound indicators.

This command is intentionally different from `market-data:backfill:lifecycle`.

- `market-data:backfill:lifecycle` acquires/imports source data before lifecycle publication.
- `market-data:eod-indicators:recompute-current` must not acquire source data, must not ingest bars, and must not update source/master tables.

## Command

```bash
php artisan market-data:eod-indicators:recompute-current <start_date> <end_date> \
  --force_replace_reason="indicator_recompute_from_existing_current_bars" \
  --with-evidence \
  --with-replay \
  --continue-on-error \
  -vvv
```

Dry-run:

```bash
php artisan market-data:eod-indicators:recompute-current <start_date> <end_date> \
  --force_replace_reason="indicator_recompute_from_existing_current_bars" \
  --dry-run \
  -vvv
```

## Read/write boundary

The command is read-only for source/master data:

- no API/source acquisition;
- no bar ingest;
- no writes to `eod_bars`;
- no writes to ticker master;
- no writes to sector membership/source tables;
- no writes to sector-index source bars;
- no writes to corporate-action source tables;
- no writes to trading-status source tables.

The command may write official publication lifecycle artifacts:

- a correction-current run;
- a candidate publication seeded from the current readable publication;
- `eod_bars_history` snapshot rows for the candidate publication;
- recomputed `eod_indicators_history` rows;
- recomputed `eod_eligibility_history` rows;
- run/publication hashes;
- seal/finalize/current pointer state when lifecycle validation passes;
- evidence/replay artifacts when requested.

## Indicator field behavior

Source/master read-only does not mean publication-bound context fields are frozen.

Default recompute behavior recalculates publication-bound fields from the existing source/master state. This includes technical fields and context fields such as:

- `sector_code`;
- `sector_roc20`;
- `rs_20_vs_sector`;
- `sector_rs_20_vs_ihsg`;
- `corporate_action_flag`;
- `corporate_action_types`;
- `trading_status_code`;
- `is_suspended`;
- `is_uma`;
- `event_risk_flag`;
- `event_risk_reasons`.

The command must not fabricate missing source. If sector benchmark bars are missing for a date, sector-rotation fields remain `NULL`. If event-risk source rows are absent, event-risk fields remain source-null according to the event-risk contract.

## Nullability rule

Insufficient history is not a whole-date error.

- `ma20` is `NULL` when fewer than 20 valid close inputs exist.
- `ma50` is `NULL` when fewer than 50 valid close inputs exist.
- `roc20` is `NULL` when the dependency close is unavailable/invalid.
- sector rotation is `NULL` when benchmark sector source/dependency is unavailable.
- zero OHLCV placeholders are publication coverage rows, not valid price inputs for indicators.

## Lifecycle rule

The command must use correction-current publication flow. The existing current readable publication remains safe until the candidate is hashed, sealed, finalized, and pointer validation passes.

If recompute fails, the prior current publication remains current.


## Evidence behavior

When recompute produces changed artifacts, run evidence is exported for the new successful readable run/publication.

When recompute produces unchanged artifacts, the correction-current lifecycle preserves the prior current publication and discards the candidate replacement. In that valid no-op case there is no new publication owned by the recompute run, so the command must export correction evidence instead of failing run evidence with `EVIDENCE_PUBLICATION_NOT_FOUND`. The current publication remains pointer-resolved and the recompute case is still successful.

## Validation

Final operator validation on 2026-06-07:

- `php artisan market-data:eod-indicators:recompute-current --help` -> PASS.
- `php artisan list market-data` -> 30 commands including `market-data:eod-indicators:recompute-current`.
- `CommandSurfaceSafetyStaticGuardTest.php` -> OK (6 tests, 126 assertions).
- `OpsCommandSurfaceRuntimeMatrixStaticGuardTest.php` -> OK (6 tests, 129 assertions).
- `OperationalReadinessStaticGuardTest.php` -> OK (10 tests, 250 assertions).
- `AuditDocsSynchronizationStaticGuardTest.php` -> OK (11 tests, 644 assertions).
- `ProductionValidationRuntimeProofStaticGuardTest.php` -> OK (15 tests, 491 assertions).
- Full `vendor\bin\phpunit tests\Unit\MarketData` -> OK (640 tests, 9539 assertions), Time 01:03.530, Memory 48.00 MB.
- Dry-run `2023-01-02` through `2026-06-04` -> 807/807 success with all source/bar/master write flags false.
- Runtime smoke `2023-01-02` -> SUCCESS, READABLE, coverage PASS, prior current publication safely preserved for unchanged output.
- Full-range runtime recompute `2023-01-02` through `2026-06-04` -> `processed_count=807`, `success_count=807`, `failed_count=0`, `skipped_count=0`, `all_passed=1`.
- Boundary proof -> `source_acquisition_executed=false`, `bar_ingest_executed=false`, `source_master_write_executed=false`, `eod_bars_write_executed=false`.
- Evidence routing -> 757 replacement publications used run evidence and 50 unchanged/preserved-current outcomes used correction evidence; all 807 evidence exports were `ADMITTED_COMPLETE`.
- Embedded and independent full-range current evidence/replay -> `processed_count=807`, `success_count=807`, `failed_count=0`, `error_count=0`, `all_passed=1`; every case is `comparison_result=MATCH`, `replay_status=PASS`, `mismatch_count=0`.

Latest docs-review validation on 2026-06-08:

- Full `vendor\bin\phpunit` -> OK (641 tests, 9547 assertions), Time 00:37.358, Memory 48.00 MB.
- This refresh updates the active validation count and does not reopen the 2026-06-07 full-range runtime recompute/evidence/replay lock.

Authoritative recompute artifact:

`storage/app/market_data/evidence/indicator_recompute_current/2023-01-02_to_2026-06-04_20260607_103904/indicator_recompute_current_summary.json`

Authoritative embedded replay artifact:

`storage/app/market_data/evidence/indicator_recompute_current/2023-01-02_to_2026-06-04_20260607_103904/full_range_current_evidence_replay/market_data_full_range_current_evidence_replay_summary.json`

Independent final reconciliation artifact:

`storage/app/market_data/evidence/indicator_recompute_current/full_range_current_2023-01-02_to_2026-06-04/market_data_full_range_current_evidence_replay_summary.json`

## Operational rerun rule

Do not rerun the historical full range merely because the command exists. Rerun affected dates after a change to current OHLCV, indicator formula/dependency logic, sector benchmark or membership source, corporate-action/trading-status source context, eligibility logic, or publication/hash/seal behavior. After a mutating recompute, run `market-data:evidence-replay:full-range-current` over the affected current range as final reconciliation.
