# WS C35 IS Robustness Redesign Diagnostic

## Purpose

C35 diagnoses whether the C34 bad-month / branch robustness weakness is visible inside IS evidence only. C35 is not OOS tuning, not OOS proof, not candidate reselection from OOS, not best-of-OOS, not production catalog creation, and not production rollout.

## Input C34 Artifact

```text
input_c34_artifact=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
expected_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
expected_c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
expected_c34_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
```

Repository source of truth uses `storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json` and status `C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED`. Any alternate prompt alias/path is context-only unless the matching artifact exists in the workspace.

## C34 Evidence Summary

Final operator runtime validated the C34 source artifact lock:

```text
expected_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
actual_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
c34_hash_match=true
c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
c34_final_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
production_ready=false
```

C34 problem statement for C35:

```text
target_branches=G21,G16
bad_months_oos_for_context_only=2025-06,2025-08,2026-03
g21_c34_class=C34_BRANCH_ROBUSTNESS_FAILURE_CONFIRMED
g16_c34_class=C34_BRANCH_BAD_MONTH_CONCENTRATION_REVIEW
```

## Boundary

```text
IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC=true
C34_ARTIFACT_HASH_LOCK=true
C34_BAD_MONTHS_CONTEXT_ONLY=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_CANDIDATE_RESELECTION=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C34_MUTATION=true
production_ready=false
oos_data_used_for_tuning=false
```

Canonical execution model remains locked:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## IS-only Rule

C35 reads IS diagnostic rows only from the existing C28 IS artifact:

```text
is_evidence_source=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
IS=2023-01-02 sampai 2025-05-21
OOS reserved=2025-05-22 sampai 2026-05-29
oos_data_used_for_tuning=false
```

Runtime periods touching `2025-05-22` or later must block with `C35_BLOCKED_IS_PERIOD_TOUCHES_OOS_RESERVED`.

## OOS Context-only Rule

The C34 bad months `2025-06`, `2025-08`, and `2026-03` are retained only inside `source_c34_problem_statement.bad_months_oos_for_context_only`. They must not be used to tune thresholds, select candidates, reselect profiles, or build a production catalog.

## Runtime Validation Result

Final operator validation completed successfully:

```text
PHPUNIT_C35=PASS
PHPUNIT_C35_RESULT=OK (11 tests, 106 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (529 tests, 11607 assertions)
ARTISAN_C35_RUNTIME=COMPLETED
C35_FINAL_STATUS=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
C35_ARTIFACT_PATH=storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
C35_ARTIFACT_HASH=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
C35_FILE_SHA1=733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B
production_ready=false
```

Runtime command output:

```text
status=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
reason_code=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
artifact_path=storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
artifact_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
production_ready=0
expected_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
actual_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
c34_hash_match=1
c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
c34_final_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
diagnostic_conclusion=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
next_step_recommendation=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
is_evidence_total_rows=15750
is_evidence_g21_rows=1770
is_evidence_g16_rows=1320
```

## IS Evidence Summary

```text
source=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
total_rows=15750
g21_rows=1770
g16_rows=1320
months_covered=27
evidence_available=true
```

## G21 IS Diagnostic Summary

```text
selected_source_code=G21
bucket_code=no_rule_profit_signal_before_fallback
count=1770
avg_ret_net=-0.003595020808694389
median_ret_net=-0.0005014793641241662
p25_ret_net=-0.012856775520699408
win_rate=0.38305084745762713
month_win_rate_min=0
month_avg_ret_net_min=-0.030795380692896064
bad_month_like_count=17
dominant_exit_reason=raw_damage_control_no_profit_d2_exit_d3_open
dominant_failure_mode=G21_NO_PROFIT_FALLBACK_NEGATIVE_AVG_LOW_WIN_RATE
is_weakness_confirmed=true
```

Interpretation: G21 weakness is confirmed in IS. It is the primary C36 redesign target because the no-rule-profit fallback branch has negative average return, low win rate, bad-month-like concentration, and a dominant no-profit D2/D3 fallback exit reason.

## G16 IS Diagnostic Summary

```text
selected_source_code=G16
bucket_code=next_open_delay_after_close_signal
count=1320
avg_ret_net=0.011291069675265837
median_ret_net=0.015366845779139255
p25_ret_net=-0.0005000750112516877
win_rate=0.7196969696969697
month_win_rate_min=0
month_avg_ret_net_min=-0.009164590269622934
bad_month_like_count=5
dominant_exit_reason=raw_preplanned_intraday_target_hit
dominant_delay_damage_mode=NEGATIVE_DELTA_VS_R09_CLUSTER
dominant_failure_mode=G16_NEXT_OPEN_DELAY_DAMAGE_CLUSTER
is_weakness_confirmed=true
```

Interpretation: G16 remains positive in aggregate IS evidence, but next-open-delay damage concentration is visible. It is a secondary C36 redesign target, not a branch to discard outright.

## IS Bad-month-like Summary

C35 found 9 IS bad-month-like rows in the combined target branch scope:

```text
2023-03 count=168 avg_ret_net=-0.012346978309652848 win_rate=0.10714285714285714 dominant_branch=G21
2023-09 count=76 avg_ret_net=-0.0031002649161361927 win_rate=0.07894736842105263 dominant_branch=G16
2024-04 count=124 avg_ret_net=-0.010873174063964229 win_rate=0.3387096774193548 dominant_branch=G21
2024-05 count=40 avg_ret_net=-0.010844177605719306 win_rate=0.3 dominant_branch=G21
2024-06 count=145 avg_ret_net=-0.001504950049667082 win_rate=0.3724137931034483 dominant_branch=G21
2024-09 count=153 avg_ret_net=-0.002486227958386385 win_rate=0.45751633986928103 dominant_branch=G21
2024-10 count=118 avg_ret_net=-0.003217331579673016 win_rate=0.15254237288135594 dominant_branch=G16
2024-12 count=150 avg_ret_net=-0.0005623812136834282 win_rate=0.56 dominant_branch=G21
2025-02 count=115 avg_ret_net=-0.008007862274629591 win_rate=0.21739130434782608 dominant_branch=G21
```

Full rows are stored in `is_bad_month_like_summary` inside the C35 artifact.

## IS Branch-month Matrix

C35 generated branch-month matrix rows across G21 no-rule-profit fallback and G16 next-open-delay target scope. Important readings:

```text
G21 2023-03 count=168 avg_ret_net=-0.012346978309652848 win_rate=0.10714285714285714
G16 2023-09 count=70 avg_ret_net=-0.004318287623233582 win_rate=0
G21 2024-04 count=114 avg_ret_net=-0.011783101613434781 win_rate=0.3684210526315789
G21 2024-07 count=18 avg_ret_net=-0.030795380692896064 win_rate=0
G16 2024-10 count=70 avg_ret_net=-0.00364200987617398 win_rate=0
G21 2025-02 count=90 avg_ret_net=-0.014133684905694478 win_rate=0
```

Full rows are stored in `is_branch_month_matrix` inside the C35 artifact.

## Ticker Failure Cluster

C35 generated ticker failure cluster rows. Notable clusters:

```text
G21 BSIM loss_count=150 avg_loss_ret_net=-0.0049947344666767 months=2023-03,2024-02
G21 JAST loss_count=54 avg_loss_ret_net=-0.017736984712598873 months=2024-09
G21 DNET loss_count=48 avg_loss_ret_net=-0.011658286589328963 months=2023-03,2025-01
G21 TAPG loss_count=42 avg_loss_ret_net=-0.03224024506603728 months=2024-04
G21 TOOL loss_count=42 avg_loss_ret_net=-0.019725857569910306 months=2025-02
G16 SGRO loss_count=55 avg_loss_ret_net=-0.005358677518831201 months=2023-09
G16 YULE loss_count=55 avg_loss_ret_net=-0.004498875281179705 months=2024-10
```

Full rows are stored in `is_ticker_failure_cluster` inside the C35 artifact.

## Redesign Hypotheses

C35 generated these hypotheses from IS evidence only:

```text
C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK source=G21 support=STRONG_IS_SUPPORT next=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G21_FALLBACK_NO_PROFIT_BRANCH
C35_HYP_G21_FALLBACK_EXIT_TOO_LATE source=G21 support=STRONG_IS_SUPPORT next=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G21_FALLBACK_EXIT_TIMING
C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE source=G16 support=MODERATE_IS_SUPPORT next=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_G16_NEXT_OPEN_DELAY_BRANCH
C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER source=G21_G16 support=MODERATE_IS_SUPPORT next=C36_IS_REGIME_GATED_CANDIDATE_FORMATION
```

These are hypotheses, not final candidates and not production rules.

## Diagnostic Conclusion

```text
C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
```

## Next Step Recommendation

```text
C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
```

C36 should form controlled redesign candidates from IS evidence only. Primary focus is G21 fallback/no-profit branch redesign. Secondary focus is G16 next-open-delay damage control. Regime-gated filtering is allowed only from IS bad-month-like evidence. Do not run OOS proof until an IS-controlled candidate is valid.

## Artifact Output

```text
storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
artifact_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
file_sha1=733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B
```

## Status Akhir C35

```text
C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
production_ready=false
oos_data_used_for_tuning=false
```

C35 did not perform OOS tuning, did not run OOS proof, did not create best-of-OOS selection, did not create a production catalog, did not promote, and did not mutate PLAN/CONFIRM behavior.
