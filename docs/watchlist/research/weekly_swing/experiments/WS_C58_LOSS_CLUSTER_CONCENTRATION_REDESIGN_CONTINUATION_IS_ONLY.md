# WS C58 — Loss-Cluster Concentration Redesign Continuation IS-Only

Status: `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

Run code: `C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY`

Runtime artifact:

```text
storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json
```

## Scope

C58 is strictly IS-only.

IS window:

```text
2023-01-02..2025-05-21
```

Reserved OOS window remains forbidden:

```text
2025-05-22..2026-05-29
```

C58 does not run OOS proof, does not read OOS returns, does not create a production catalog, does not promote any candidate, and does not mutate PLAN/CONFIRM or C01-C57 artifacts.

## Locked C57 input

C58 starts from locked C57 final evidence:

```text
C57_ARTIFACT=storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json
C57_ARTIFACT_HASH=71230896c2121fcfedddf36dd54c9c03ad462b4d
C57_FILE_SHA1=50272917A107E304F8EEEB874DBC02A881DB0C31
C57_STATUS=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY_COMPLETED
C57_DIAGNOSTIC_CONCLUSION=C57_LOSS_CLUSTER_GAP_REMAINS
C57_NEXT_STEP=C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY
```

C57 market-index/regime reconstruction is considered solved for C58:

```text
required_field_count=9
evaluable_field_count=9
missing_field_count=0
regime_fully_evaluable=true
market_index_roc20_reconstructed=true
market_index_ma20_slope_pct_reconstructed=true
future_lookup_detected=false
oos_rows_requested=0
source_bias_validation_pass=true
```

C58 must not repeat market-index reconstruction. It only carries forward the C57 result and re-evaluates regime robustness with fields fully evaluable.

## Mandatory database dictionary rule

Before any DB-connected implementation, C58 requires the dictionary-read rule. Runtime artifact records `database_dictionary_read_summary` with:

```text
dictionary_read_required=true
market_data_dictionary_path=docs/market_data/db/MARKET_DATA_DICTIONARY.md
database_dictionary_usage_rule_path=docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
dictionary_missing_coverage_detected=false
asof_safe=true
future_lookup_detected=false
oos_rows_requested=0
```

Required checked paths:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/market_data/db/Database_Schema_MariaDB.sql
docs/market_data/db/Database_Schema_Contracts_MariaDB.md
docs/market_data/db/DB_FIELDS_AND_METADATA.md
docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
```

Locked mapping from dictionary:

```text
market_index_roc20 => market_benchmark_indicators.roc_20 where benchmark_code='IHSG'
market_index_ma20_slope_pct => market_benchmark_indicators.ma20_slope_pct where benchmark_code='IHSG'
market_calendar date key => cal_date
```

Missing table/field coverage blocks C58 instead of allowing guessed table or column names.

## Candidate tracks

C58 creates controlled candidates from the C56/C57 anchors, not from a reset.

Track A defensive concentration anchors:

```text
C56_R21_ROLLING_STRESS_SMOOTHER_NO_WINDOW_EXCLUSION
C56_R23_REGIME_COMPLETE_BALANCED_CANDIDATE
```

Track B rolling-pass return anchors:

```text
C56_R09_R00_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08
C56_R10_R01_BRANCH_BUCKET_CAP_50_LOSS_CLUSTER_08
C56_R13_R00_MONTHLY_EXPOSURE_EQUALIZER
C56_R14_R01_MONTHLY_EXPOSURE_EQUALIZER
```

C58 also includes hybrid A/B candidates with explicit lineage.

Candidate examples implemented:

```text
C58_R00_REPLAY_C56_R21_DEFENSIVE_COMPARATOR
C58_R01_REPLAY_C56_R23_BALANCED_COMPARATOR
C58_R02_R21_ADAPTIVE_BRANCH_BUCKET_48_LOSS_10
C58_R03_R23_ROTATION_QUOTA_BRANCH_BUCKET_48
C58_R04_R09_BRANCH_BUCKET_CAP_48_SAMPLE_RECOVERY
C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10
C58_R06_R13_MONTHLY_EQUALIZER_BRANCH_BUCKET_48
C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10
C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING
C58_R09_HYBRID_R23_R14_BALANCED_REGIME
```

All candidates must carry:

```text
candidate_code
parent_candidate_code
candidate_role
selection_rule_summary
pre_trade_fields_used
return_fields_used_for_selection=false
future_path_used_for_selection=false
oos_return_used_for_selection=false
production_ready=false
```

## Gates evaluated

C58 evaluates each candidate against:

- concentration dependency
- loss-cluster share
- rolling stability retention
- leave-one-month-out stability
- regime robustness with C57 fully evaluable fields
- material selection difference
- anti-shared-core
- source-bias/as-of safety

Strict boundaries remain:

```text
no_oos_proof=true
no_oos_tuning=true
no_oos_return_selection=true
no_gate_relaxation=true
no_adverse_month_exclusion_rule=true
no_failed_window_exclusion_rule=true
no_ticker_exclusion_rule=true
no_sector_exclusion_rule=true
no_production_catalog=true
no_plan_confirm_mutation=true
```

## C59 readiness behavior

C58 must not unlock direct OOS proof.

If a candidate passes every IS gate, it can only be marked ready for C59/pre-lock IS review.

If no candidate passes every IS gate, C58 must choose an IS-only continuation and identify the dominant blocker, such as:

```text
C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY
C59_LOO_DEPENDENCY_REDESIGN_CONTINUATION_IS_ONLY
C59_REGIME_ROBUSTNESS_REDESIGN_CONTINUATION_IS_ONLY
```

`production_ready` must remain false in all C58 outcomes.

## Implementation files

```text
app/Application/Watchlist/Services/WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService.php
app/Console/Commands/Watchlist/RunBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyCommand.php
tests/Unit/Watchlist/WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC58StaticGuardTest.php
docs/watchlist/research/weekly_swing/experiments/WS_C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY.md
docs/watchlist/evidence/weekly_swing/operator_commands/WS_C58_OPERATOR_VALIDATION_COMMANDS.md
```

Updated registration/governance files:

```text
app/Console/Kernel.php
docs/watchlist/evidence/weekly_swing/ledgers/LUMEN_IMPLEMENTATION_STATUS.md
docs/watchlist/governance/trackers/LUMEN_CONTRACT_TRACKER.md
docs/watchlist/governance/audit/AUDIT_UPDATE_GOVERNANCE.md
```

## Sandbox validation note

In this container, PHPUnit cannot run because PHP extensions are missing:

```text
missing extensions: dom, mbstring, xml, xmlwriter
```

Syntax checks passed for the new C58 service, command, and tests. Operator validation remains required in the project PHP environment.

## Sandbox smoke result

A direct PHP service smoke was executed against the locked C57 artifact and generated the C58 artifact path. This is not a substitute for operator PHPUnit/artisan validation.

```text
DIRECT_SERVICE_SMOKE=COMPLETED
C58_STATUS=C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
C58_ARTIFACT_HASH=849b661b8d83149b5123106524468ad16b01d3be
C58_DIAGNOSTIC_CONCLUSION=C58_LOSS_CLUSTER_GAP_REMAINS
C58_NEXT_STEP=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
CANDIDATE_READY_FOR_C59_COUNT=0
ROLLING_PASS_CANDIDATE_COUNT=4
CONCENTRATION_PASS_CANDIDATE_COUNT=0
LOSS_CLUSTER_PASS_CANDIDATE_COUNT=0
LOO_PASS_CANDIDATE_COUNT=0
REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

Artisan runtime could not be executed in this sandbox because the container PHP version is unsupported by the project bootstrap:

```text
ENV_UNSUPPORTED_PHP_VERSION
Current PHP: 8.4.16
Required baseline: PHP >= 7.3 and < 8.4
```


## Final operator validation result

C58 has been operator-validated in the project environment and is closed for its scoped IS-only implementation.

Operator validation evidence:

```text
PHPUNIT_C58=PASS OK (12 tests, 430 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (817 tests, 16397 assertions)
C58_RUNTIME=COMPLETED
C58_FINAL_STATUS=C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
C58_REASON_CODE=C58_LOSS_CLUSTER_GAP_REMAINS
C58_ARTIFACT=storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json
C58_ARTIFACT_HASH=80d09de8053659bf01ce5b8b72d9e2d82cdf69dc
C58_FILE_SHA1=FA6FE27604F6CDA664DCF90A251AF41672670700
C57_HASH_MATCH=true
C57_FILE_SHA1_MATCH=true
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

Database dictionary compliance was recorded in the runtime artifact:

```text
DICTIONARY_READ_REQUIRED=true
MARKET_DATA_DICTIONARY_PATH=docs/market_data/db/MARKET_DATA_DICTIONARY.md
DATABASE_DICTIONARY_USAGE_RULE_PATH=docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
DICTIONARY_MISSING_COVERAGE_DETECTED=false
ASOF_SAFE=true
FUTURE_LOOKUP_DETECTED=false
OOS_ROWS_REQUESTED=0
```

C58 generated 10 controlled candidates across replay comparators, Track A defensive concentration lineage, Track B rolling-pass lineage, and hybrid lineage:

```text
C58_R00_REPLAY_C56_R21_DEFENSIVE_COMPARATOR
C58_R01_REPLAY_C56_R23_BALANCED_COMPARATOR
C58_R02_R21_ADAPTIVE_BRANCH_BUCKET_48_LOSS_10
C58_R03_R23_ROTATION_QUOTA_BRANCH_BUCKET_48
C58_R04_R09_BRANCH_BUCKET_CAP_48_SAMPLE_RECOVERY
C58_R05_R10_BRANCH_BUCKET_CAP_45_STRICT_LOSS_10
C58_R06_R13_MONTHLY_EQUALIZER_BRANCH_BUCKET_48
C58_R07_R14_MONTHLY_EQUALIZER_LOSS_CLUSTER_10
C58_R08_HYBRID_R21_R09_DEFENSIVE_ROLLING
C58_R09_HYBRID_R23_R14_BALANCED_REGIME
```

Final C58 gate summary:

```text
CANDIDATE_COUNT=10
CANDIDATE_READY_FOR_C59_COUNT=0
ROLLING_VALIDATION_PASS_CANDIDATE_COUNT=4
CONCENTRATION_VALIDATION_PASS_CANDIDATE_COUNT=0
LOSS_CLUSTER_PASS_CANDIDATE_COUNT=0
LOO_VALIDATION_PASS_CANDIDATE_COUNT=0
REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
MATERIAL_SELECTION_DIFFERENCE_PASS_COUNT=8
ANTI_SHARED_CORE_PASS_COUNT=8
```

Final diagnostic interpretation:

- C58 implementation is valid and operator-validated.
- C57 market-index/regime reconstruction remains solved and retained.
- No OOS rows were requested.
- No future lookup was detected.
- No return, future path, or OOS return was used for selection.
- No production candidate was created.
- No candidate is ready for C59/pre-lock review.
- Dominant blocker remains strict loss-cluster failure, with concentration, LOO dependency, and regime robustness still not passing.
- Weakest regime remains `market_down_or_sideways_high_vol`.

Final C58 decision:

```text
VALIDATION_COMPLETED=true
CANDIDATE_READY_FOR_C59_COUNT=0
C59_RECOMMENDATION=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
DECISION_REASON=loss_cluster_share_remains_above_strict_gate
DIAGNOSTIC_CONCLUSION=C58_LOSS_CLUSTER_GAP_REMAINS
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRODUCTION_READY=false
```

C58 must not be used to unlock OOS proof. The next session must remain IS-only and focus on loss-cluster reduction, branch/bucket concentration repair, LOO dependency reduction, and survival under `market_down_or_sideways_high_vol`.
