# WS C57 Regime Field Reconstruction Continuation — IS Only

## Purpose

C57 continues the final C56 result with a narrow IS-only objective: reconstruct the missing market-index regime fields, re-check regime robustness, and carry forward the C56 anchor candidates without OOS tuning, OOS proof, production rollout, catalog promotion, or PLAN/CONFIRM mutation.

Required market-index fields:

- `market_index_roc20`
- `market_index_ma20_slope_pct`

Supporting regime fields that remain validated:

- `sector_roc20`
- `rs_20_vs_ihsg`
- `rs_20_vs_sector`
- `roc20`
- `ma20_slope_pct`
- `atr14_pct`
- `vol_ratio`

## Locked inputs

### C56 input

- Artifact: `storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json`
- Expected artifact hash: `f7edab247dc824dcd33a15f00575dd04f76f4786`
- Required status: `C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED`
- Required next step: `C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY`
- Required production flag: `production_ready=false`

### C55 input

- Artifact: `storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json`
- Expected artifact hash: `a4145d6f356e678d0dadf95be5d356198ebfed79`
- Expected file SHA1: `18875FCAD7FD7CDA6607BB09A60917E853E68D2B`

### C54 input

- Artifact: `storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json`
- Expected artifact hash: `8c71a4352a1024dbe985e0f0bb6329f5e1545150`
- Expected file SHA1: `75410BB1A30A32FFFF9661CAD6818C13E044F7E5`

### C53 input

- Artifact: `storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json`
- Expected artifact hash: `6a1749d723e16b7efdb8aa1d7510388a9475d12c`
- Expected file SHA1: `E35FEFB78B6F1931E54169BD8AABE286CB6F08C2`

### C52 input

- Artifact: `storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json`
- Expected artifact hash: `5dbe51c9d18b175e65cddb60336baf43d6833b72`
- Expected file SHA1: `DADE6518BFF3912D8A43D7C67073FB803F7CF878`

## C56 evidence summary

C56 completed technical validation and runtime as an IS-only continuation. It improved rolling stability from zero full rolling-pass candidates in C55 to four full rolling-pass candidates in C56, but still produced `candidate_ready_for_c57_count=0` because regime field reconstruction was not fully evaluable and concentration/loss-cluster validation still failed.

C56 final markers carried into C57:

- `status=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED`
- `diagnostic_conclusion=C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS`
- `next_step_recommendation=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY`
- `production_ready=false`
- `direct_oos_proof_recommended=false`
- `oos_proof_unlocked=false`

## C56 root cause summary

C56 left a specific market-index reconstruction gap. Seven of nine required regime fields were evaluable. Two required market-index fields had zero row coverage:

- `market_index_roc20`: `0/15750`
- `market_index_ma20_slope_pct`: `0/15750`

The root cause for C57 is therefore not a full candidate redesign from scratch. It is the missing as-of-safe market-index source reconstruction layer.

## C56 regime field gap summary

C56 regime field reconstruction summary carried forward:

- `required_field_count=9`
- `evaluable_field_count=7`
- `missing_field_count=2`
- `regime_field_coverage_min=0`
- `regime_fully_evaluable=false`
- `market_index_regime_fields_reconstructed=false`
- `asof_safe=true`
- `future_lookup_detected=false`
- `oos_rows_requested=0`
- `reconstruction_pass=false`

## C56 rolling improvement summary

C56 rolling summary carried forward:

- `rolling_candidate_count=26`
- `rolling_full_pass_required=true`
- `candidate_full_rolling_pass_count=4`

C57 must verify whether this rolling improvement is retained after market-index regime reconstruction. It must not create failed-window exclusion rules.

## C56 concentration/loss-cluster failure summary

C56 concentration/loss-cluster result carried forward:

- `concentration_validation_pass_candidate_count=0`
- `loss_cluster_pass_candidate_count=0`

C57 replays the anchor candidates and reports whether this remains the next blocker after market-index fields are fixed.

## C56 LOO summary

C56 leave-one-month-out summary carried forward:

- `loo_candidate_count=26`
- `loo_validation_required=true`
- `candidate_loo_pass_count=2`

C57 reports LOO state for the C56 anchors as diagnostic evidence only.

## C56 source reconstruction summary

C56 source reconstruction remained read-only, as-of-safe, and IS-only:

- `source_evidence_artifact=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json`
- `source_mode=C28_PICK_DIAGNOSTIC_ROWS`
- `source_is_rows=15750`
- `pre_trade_source_mode=DATABASE_AS_OF_SIGNAL_DATE_JOIN_WITH_SECTOR`
- `reconstructed_source_row_count=15750`
- `ret_net_evaluation_only=true`
- `source_bias_validation_pass=true`
- `sector_metadata_reconstruction_pass=true`
- `oos_rows_requested=0`

## C57 boundaries

C57 is constrained to:

- IS-only regime field reconstruction continuation.
- C56/C55/C54/C53/C52 locked lineage.
- Read-only market-index source discovery.
- As-of-safe reconstruction by signal date, trade date, or previous published trading day not after the row date.
- No `MAX(trade_date)` style latest-row selection.
- No future lookup.
- No OOS rows.
- No OOS return selection.
- No OOS bad-month tuning.
- No failed-window exclusion rule.
- No adverse-month exclusion rule.
- No ticker or sector exclusion rule derived from failure attribution.
- No gate relaxation.
- No production catalog.
- No promotion.
- No PLAN/CONFIRM mutation.
- No mutation to C01-C56 artifacts.
- `production_ready=false` always.

Canonical execution model remains:

- `ENTRY=NEXT_OPEN`
- `EXIT=STOP_TP_OR_TIME`
- `HOLD=5`
- `FEE=IDR_FIXED`
- `SLIP=0`
- `GAP=OPEN`
- `PX=IDX_BANDS`

## IS and reserved OOS periods

IS validation period:

- `from=2023-01-02`
- `to=2025-05-21`

Reserved OOS period, not used by C57:

- `from=2025-05-22`
- `to=2026-05-29`

## Market index source discovery rule

C57 attempts and records read-only lookup candidates:

1. `market_benchmark_indicators`
2. `market_benchmark_bars`
3. `eod_indicators` joined to discovered index ticker identifiers
4. `eod_bars` joined to discovered index ticker identifiers and computed when indicator fields are missing
5. `market_calendar` previous trading-day fallback bounded by signal/trade date
6. published EOD read model, if present
7. artifact fallback only when as-of-safe and IS-only

Identifier candidates checked include:

- `IHSG`
- `JCI`
- `COMPOSITE`
- `IDX Composite`
- `^JKSE`
- `JKSE`
- `IHSG.JK`
- `market_index`
- `composite_index`

## Market index reconstruction as-of-safe rule

C57 reconstructs market-index fields per source row using exact signal/trade date first. If exact data is missing, the fallback is the previous published trading day not after the row date. Future lookup fails validation.

If indicators are missing but benchmark bars exist, C57 computes:

- `market_index_roc20` from historical close versus close 20 trading bars earlier.
- `market_index_ma20_slope_pct` from current MA20 versus previous MA20.

## Regime field coverage validation

C57 output contains:

- `regime_field_reconstruction_summary`
- `regime_field_coverage_results`
- `missing_regime_field_results`
- `asof_safety_validation_results`

C57 is fully evaluable only when all nine required fields pass coverage, as-of safety, future-lookup, and OOS-row checks.

## Anchor candidate definitions

Primary anchors:

- `C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION`
- `C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE`
- `C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08`
- `C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08`
- `C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER`
- `C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER`

Comparator-only anchors:

- `C56_R00_C55_R00_NEAR_PASS_REPLAY_COMPARATOR`
- `C56_R01_C55_R01_NEAR_PASS_REPLAY_COMPARATOR`
- `C56_R03_C55_R19_LOSS_CLUSTER_REPLAY_COMPARATOR`
- `C56_R04_C55_R20_C52_ANCHOR_COMPARATOR_ONLY`

Comparator-only rows must not be selected as production candidates.

## Candidate replay and validation layers

C57 artifact includes:

- `candidate_replay_results`
- `concentration_dependency_validation_results`
- `rolling_validation_results`
- `rolling_validation_summary`
- `leave_one_month_out_results`
- `leave_one_month_out_summary`
- `regime_robustness_validation_results`
- `regime_robustness_validation_summary`
- `material_difference_validation_results`
- `source_reconstruction_bias_check`
- `candidate_scorecard`
- `candidate_safety_audit`
- `not_evaluable_reasons`

Return/path fields remain evaluation-only after locked selection. They are not used for candidate formation or market-index reconstruction.

## C58 readiness decision

C57 can only recommend one of the following IS-only next steps:

- `C58_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C57_RECONSTRUCTION`
- `C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY`
- `C58_MARKET_INDEX_EVIDENCE_EXPANSION_OR_SOURCE_RECONSTRUCTION_IS_ONLY`
- `C58_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY`
- `C58_ROLLING_STABILITY_RECHECK_AFTER_REGIME_RECONSTRUCTION_IS_ONLY`
- `C58_SHARED_CORE_REVERSION_REDESIGN_REQUIRED`
- `C58_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY`

C57 must not recommend OOS proof directly.

## Runtime result

Runtime in this container was not executed through Artisan because the container PHP runtime is unsupported by the repository guard:

- `ENV_UNSUPPORTED_PHP_VERSION`
- Current container PHP: `8.4.16`
- Required repository baseline: `>=7.3 and <8.4`

PHPUnit was also not executable in this container because required PHP extensions are missing:

- `dom`
- `mbstring`
- `xml`
- `xmlwriter`

Operator validation is required in the project PHP environment.

## Artifact output

Expected output path:

- `storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json`

Artifact hash must be taken from a real operator runtime artifact only. Do not claim C57 PASS or runtime COMPLETED unless the validation commands are run successfully in the supported environment.

## Final status

Implementation status: `IMPLEMENTED_OPERATOR_VALIDATION_REQUIRED`

Production status: `production_ready=false`

C57 does not perform OOS tuning, OOS proof, catalog promotion, production rollout, PLAN/CONFIRM mutation, or C01-C56 artifact mutation.

## C57 fix2 implementation note

C57 fix2 repairs the market-index reconstruction layer after operator probing showed that IHSG data exists in `market_benchmark_indicators` and `market_benchmark_bars` for the IS window.

Fix2 changes:

- Loads C57 source rows from locked C56 `source_reconstruction_summary.source_evidence_artifact` when runtime options do not inject `source_rows`.
- Supports C28 `pick_diagnostic_rows` as the locked IS source-row universe.
- Extracts source-row dates from `signal_date`, `trade_date`, `date`, or `published_date`.
- Records `required_date_count`, `required_date_min`, `required_date_max`, `required_date_sample`, `source_row_date_field_detected`, `source_row_min_date`, and `source_row_max_date`.
- Uses `market_benchmark_indicators.roc_20` as the primary source for `market_index_roc20`.
- Uses `market_benchmark_indicators.ma20_slope_pct` as the primary source for `market_index_ma20_slope_pct`.
- Uses `market_benchmark_bars` as fallback computation source for missing market-index fields.
- Supports `market_calendar.cal_date` for previous-trading-day fallback discovery.
- Keeps non-market regime-field coverage anchored to C56 when locked C28 diagnostic rows do not carry the reconstructed indicator fields.

The fix remains IS-only, read-only, as-of-safe, and does not change OOS, production, catalog, PLAN/CONFIRM, or C01-C56 artifacts.
