# WS C32 Data Path And Bad Month Robustness Diagnostic

## Purpose

C32 follows C31 by splitting the next work into two explicit non-tuning tracks:

1. data-path remediation proof for missing D1-D5 raw OHLC path rows;
2. bad-month robustness diagnostic for clean bad-month and branch weakness.

C32 is not tuning, not candidate reselection, not best-of-OOS, not catalog promotion, and not production rollout.

## Input C31 Artifact

```text
input_c31_artifact=storage/app/watchlist/backtest/c31-controlled-gate-reclassification.json
expected_c31_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
expected_c31_status=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
expected_c31_conclusion=C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK
expected_c31_proof_status=C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS
```

## Boundary

```text
DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_ONLY=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C31_MUTATION=true
production_ready=false
```

Return and bad-month evidence are used only for diagnostic attribution after the locked C29/C30/C31 evidence exists. C32 does not select or recommend a production profile.

## Runtime Result

```text
PHPUNIT_C32=PASS
OK (12 tests, 107 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (490 tests, 11237 assertions)

C32_RUNTIME=COMPLETED
status=C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED
artifact_path=storage/app/watchlist/backtest/c32-data-path-and-bad-month-diagnostic.json
artifact_hash=4bd92dfcf70dd0b02398d3ecf62d08c0356292ab
file_sha1=49F4A138BEF5B18841119F255F39ACDC2F97445B
production_ready=0
```

## C31 Lock Validation

```text
expected_c31_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
actual_c31_hash=4c6203621ed53ade368328a3aad567cbfc12f3a0
c31_hash_match=1
c31_status=C31_CONTROLLED_GATE_RECLASSIFICATION_COMPLETED
c31_reclassification_conclusion=C31_RECLASSIFICATION_CONFIRMED_MISSING_PATH_NOT_LOOKAHEAD_LEAK
c31_controlled_proof_status=C31_CONTROLLED_OOS_PROOF_FAILED_DATA_COMPLETENESS_AND_ROBUSTNESS
```

## Data Path Remediation Scope

```text
data_path_remediation_status=C32_DATA_PATH_REMEDIATION_REQUIRED
missing_path_count=4
affected_trade_dates=2025-06-04,2025-08-15
affected_entry_dates=2025-06-05,2025-08-19
affected_tickers=BBSI,MICE
affected_param_ids=151,152
affected_source_codes=R09
missing_path_reason=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING count=4
required_remediation_action=C32_REPLAY_RAW_OHLC_D1_TO_D5_FOR_MISSING_PATH_ROWS_BEFORE_GATE_RETEST
can_claim_data_completeness_pass=false
can_claim_oos_pass=false
```

Missing path replay rows:

```text
2025-06-04 MICE param_id=151 entry_date=2025-06-05 row_code=06_VOL_150_250_LOW_ATR_NEG_ROC20 source=R09
2025-06-04 MICE param_id=152 entry_date=2025-06-05 row_code=07_VOL_150_250_ONE_R_LOW_ATR source=R09
2025-08-15 BBSI param_id=151 entry_date=2025-08-19 row_code=06_VOL_150_250_LOW_ATR_NEG_ROC20 source=R09
2025-08-15 BBSI param_id=152 entry_date=2025-08-19 row_code=07_VOL_150_250_ONE_R_LOW_ATR source=R09
```

## Bad Month Robustness Diagnostic Scope

```text
bad_month_robustness_status=C32_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
```

Bad months:

```text
2025-06: data_path_affected=true clean_robustness_failure=true failure_class=MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE dominant_branch=G21 dominant_ticker=GWSA
2025-08: data_path_affected=true clean_robustness_failure=true failure_class=MIXED_DATA_PATH_AND_CLEAN_ROBUSTNESS_FAILURE dominant_branch=G21 dominant_ticker=SMKL
2026-03: data_path_affected=false clean_robustness_failure=true failure_class=CLEAN_BAD_MONTH_ROBUSTNESS_FAILURE dominant_branch=G16 dominant_ticker=BINA
```

Branch diagnostic scope:

```text
G16: data_path_affected=false robustness_diagnostic_flag=true bad_month_contribution_count=7 clean_bad_month_contribution_count=7 failure_class=CLEAN_BRANCH_ROBUSTNESS_REVIEW
G21: data_path_affected=false robustness_diagnostic_flag=true bad_month_contribution_count=14 clean_bad_month_contribution_count=14 failure_class=CLEAN_BRANCH_ROBUSTNESS_REVIEW
R09: data_path_affected=true robustness_diagnostic_flag=false bad_month_contribution_count=4 clean_bad_month_contribution_count=0 failure_class=DATA_PATH_AFFECTED_BRANCH
```

## Split Decision

```text
actual_lookahead_fix_required=false
selection_leak_fix_required=false
data_path_remediation_required=true
bad_month_robustness_diagnostic_required=true
oos_tuning_allowed=false
profile_reselection_allowed=false
production_promotion_allowed=false
production_ready=false
```

## Diagnostic Conclusion

```text
C32_SPLIT_CONFIRMED_DATA_PATH_REMEDIATION_AND_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_REQUIRED
```

## Next Step

```text
C33_DATA_PATH_REPLAY_PROOF_THEN_C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_NO_OOS_TUNING
```

C33 should prove the missing D1-D5 raw OHLC path remediation and only then allow a controlled gate retest. C34 should diagnose bad-month robustness after clean data evidence, still without OOS tuning or production promotion.
