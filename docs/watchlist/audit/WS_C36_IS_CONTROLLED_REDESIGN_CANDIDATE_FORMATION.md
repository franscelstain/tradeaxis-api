# WS C36 IS-Controlled Redesign Candidate Formation

## Purpose

C36 forms controlled redesign candidates from C35 IS evidence only. C36 is not OOS proof, not OOS tuning, not best-of-OOS selection, not production rollout, not catalog promotion, and not PLAN/CONFIRM mutation.

C36 answers whether C35-confirmed G21 and G16 weaknesses can be turned into IS-controlled redesign candidates that still require C37 IS validation / anti-overfit checks before any OOS proof is considered.

## Input C35 Artifact

```text
input_c35_artifact=storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
expected_c35_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
expected_c35_file_sha1=733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B
expected_c35_status=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
expected_c35_diagnostic_conclusion=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
production_ready=false
oos_data_used_for_tuning=false
```

C36 must block if the C35 artifact is missing, the stable hash does not match, the status/conclusion is unexpected, `production_ready` is not false, or C35 has `oos_data_used_for_tuning=true`.

## C35 Evidence Summary

Source evidence inherited from C35:

```text
source_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
IS=2023-01-02 sampai 2025-05-21
OOS reserved=2025-05-22 sampai 2026-05-29
total_rows=15750
g21_rows=1770
g16_rows=1320
months_covered=27
evidence_available=true
```

C35 branch findings:

```text
G21 weakness confirmed=true
G21 bucket=no_rule_profit_signal_before_fallback
G21 dominant_exit_reason=raw_damage_control_no_profit_d2_exit_d3_open
G21 dominant_failure_mode=G21_NO_PROFIT_FALLBACK_NEGATIVE_AVG_LOW_WIN_RATE

G16 weakness confirmed=true
G16 bucket=next_open_delay_after_close_signal
G16 dominant_exit_reason=raw_preplanned_intraday_target_hit
G16 dominant_delay_damage_mode=NEGATIVE_DELTA_VS_R09_CLUSTER
G16 dominant_failure_mode=G16_NEXT_OPEN_DELAY_DAMAGE_CLUSTER
```

IS bad-month-like months from C35:

```text
2023-03, 2023-09, 2024-04, 2024-05, 2024-06, 2024-09, 2024-10, 2024-12, 2025-02
```

## Boundary C36

```text
IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION=true
C35_ARTIFACT_HASH_LOCK=true
C36_CANDIDATE_FROM_C35_HYPOTHESES=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C35_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
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

C36 reads C35 and C35-linked C28 IS evidence. Runtime period must not touch `2025-05-22` or later. Return may be used only after selection as evaluation/diagnostic evidence. Return, future path, MFE/MAE, exit result, OOS bad months, and OOS returns must not be used to select a candidate or branch.

## No OOS Tuning Rule

C36 does not rerun C29/C30/C31/C32/C33/C34/C35, does not run OOS proof, does not pick a candidate from OOS, and does not unlock OOS proof. C37 IS validation / anti-overfit check is the next required session if a controlled candidate is formed.

## Candidate List

### Baseline comparator

```text
C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
```

Represents current G21/G16 behavior from C35/C28 IS evidence.

### G21 candidates

```text
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD
C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE
C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK
```

`C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD` must be `NOT_EVALUABLE` if D2 close/path fields are unavailable. `C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE` evaluates suppressing the weak G21 no-rule-profit fallback branch using branch metadata only. `C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK` must be `NOT_EVALUABLE` unless a valid pre-trade regime field exists.

### G16 candidates

```text
C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE
C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE
```

`C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE` must be `NOT_EVALUABLE` if direct pre-trade delay/gap damage fields are unavailable. `C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE` keeps G16 as a comparator because aggregate IS performance remains positive even though the delay-damage cluster exists.

### Combined controlled comparator

```text
C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
```

Combines G21 no-profit branch suppression with G16 no-change comparator. This is diagnostic only and must not be a production candidate.

## Candidate Comparison Table

C36 artifact must include `candidate_comparison_table` with these deltas versus baseline:

```text
delta_avg_ret_net_vs_baseline
delta_median_ret_net_vs_baseline
delta_p25_ret_net_vs_baseline
delta_win_rate_vs_baseline
delta_month_win_rate_min_vs_baseline
delta_month_avg_ret_net_min_vs_baseline
delta_bad_month_like_count_vs_baseline
delta_loss_concentration_vs_baseline
```

A candidate can advance only when it is `EVALUATED`, stays `production_ready=false`, uses no OOS tuning, uses no return/future-path selection input, and improves at least one C35 weakness without materially worsening downside stability.

## Candidate Safety Audit

C36 artifact must include `candidate_safety_audit` for every candidate with:

```text
candidate_code
passed
reason_code
message
```

Safety pass requires:

```text
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
production_ready=false
candidate_is_not_production=true
```

## Not Evaluable Reasons

Expected valid not-evaluable reasons include:

```text
C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE
C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE
C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE
```

These are not implementation failures. They are correct safety outcomes when C28/C35 evidence lacks pre-trade fields required by a candidate.

## Artifact Output

```text
artifact_path=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json
artifact_type=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
artifact_hash=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
file_sha1=A5D7E25594238C2743E5DB2E68657AE95BA8B927
production_ready=false
best_is_candidate_is_not_production=true
```

## Final Operator Validation Evidence

C36 final operator validation was executed in the supported project environment.

```text
PHPUNIT_C36=PASS
PHPUNIT_C36_RESULT=OK (15 tests, 203 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (544 tests, 11810 assertions)
ARTISAN_C36_RUNTIME=COMPLETED
C36_FINAL_STATUS=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
C36_ARTIFACT_PATH=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json
C36_ARTIFACT_HASH=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
C36_FILE_SHA1=A5D7E25594238C2743E5DB2E68657AE95BA8B927
```

C35 artifact lock validation:

```text
expected_c35_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
actual_c35_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
c35_hash_match=true
c35_status=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
c35_diagnostic_conclusion=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
```

IS period:

```text
from=2023-01-02
to=2025-05-21
oos_reserved_from=2025-05-22
oos_reserved_to=2026-05-29
oos_data_used_for_tuning=false
```

Source C35 summary:

```text
g21_rows=1770
g16_rows=1320
g21_weakness_confirmed=true
g16_weakness_confirmed=true
source_evidence=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
```

## Runtime Result

Runtime completed:

```text
status=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
reason_code=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
production_ready=0
diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
next_step_recommendation=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK
candidate_total=7
candidate_evaluated=4
candidate_not_evaluable=3
candidate_formed=1
best_is_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
best_is_candidate_is_not_production=true
```

## Baseline Summary

```text
candidate_code=C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
candidate_status=EVALUATED
evaluated_rows=3090
selected_rows=3090
avg_ret_net=0.002764085805812881
median_ret_net=0.007129587789325702
p25_ret_net=-0.005819495309286108
win_rate=0.5268608414239482
month_win_rate_min=0.07894736842105263
month_avg_ret_net_min=-0.012346978309652848
bad_month_like_count=9
loss_concentration=0.47313915857605177
ticker_concentration=0.08414239482200647
branch_concentration=0.5728155339805825
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
production_ready=false
```

Baseline exit reason distribution:

```text
raw_preplanned_intraday_target_hit count=1456 avg_ret_net=0.014892520371346585 win_rate=1
raw_damage_control_no_profit_d2_exit_d3_open count=1128 avg_ret_net=-0.01260028341906335 win_rate=0.031914893617021274
raw_r09_next_open_after_close_profit count=450 avg_ret_net=-0.002947269677360991 win_rate=0.17777777777777778
raw_preplanned_intraday_target_gap_open_hit count=56 avg_ret_net=0.04280190233566344 win_rate=1
```

## Candidate Summary

```text
total_candidates=7
evaluated_candidates=4
not_evaluable_candidates=3
candidate_formed=true
best_is_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
best_is_candidate_is_not_production=true
```

## Candidate Results

```text
C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
status=EVALUATED evaluated_rows=3090 selected_rows=3090 avg_ret_net=0.002764085805812881 median_ret_net=0.007129587789325702 p25_ret_net=-0.005819495309286108 win_rate=0.5268608414239482 bad_month_like_count=9

C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD
status=NOT_EVALUABLE evaluated_rows=1770 selected_rows=0 reason=C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE

C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE
status=EVALUATED evaluated_rows=3090 selected_rows=1320 avg_ret_net=0.011291069675265837 median_ret_net=0.015366845779139255 p25_ret_net=-0.0005000750112516877 win_rate=0.7196969696969697 bad_month_like_count=5

C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK
status=NOT_EVALUABLE evaluated_rows=1770 selected_rows=0 reason=C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE

C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE
status=NOT_EVALUABLE evaluated_rows=1320 selected_rows=0 reason=C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE

C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE
status=EVALUATED evaluated_rows=1320 selected_rows=1320 avg_ret_net=0.011291069675265837 median_ret_net=0.015366845779139255 p25_ret_net=-0.0005000750112516877 win_rate=0.7196969696969697 bad_month_like_count=5

C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
status=EVALUATED evaluated_rows=3090 selected_rows=1320 avg_ret_net=0.011291069675265837 median_ret_net=0.015366845779139255 p25_ret_net=-0.0005000750112516877 win_rate=0.7196969696969697 bad_month_like_count=5
```

All evaluated candidates retain:

```text
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
production_ready=false
candidate_is_not_production=true
```

## Candidate Comparison Table

Compared against `C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR`:

```text
C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR
status=EVALUATED delta_avg=0 delta_median=0 delta_p25=0 delta_win_rate=0 delta_month_win_rate_min=0 delta_month_avg_ret_net_min=0 delta_bad_month_like_count=0 delta_loss_concentration=0

C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD
status=NOT_EVALUABLE deltas=null

C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE
status=EVALUATED delta_avg=0.008526983869452956 delta_median=0.008237257989813554 delta_p25=0.00531942029803442 delta_win_rate=0.19283612827302155 delta_month_win_rate_min=-0.07894736842105263 delta_month_avg_ret_net_min=0.003182388040029914 delta_bad_month_like_count=-4 delta_loss_concentration=-0.1928361282730215

C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK
status=NOT_EVALUABLE deltas=null

C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE
status=NOT_EVALUABLE deltas=null

C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE
status=EVALUATED delta_avg=0.008526983869452956 delta_median=0.008237257989813554 delta_p25=0.00531942029803442 delta_win_rate=0.19283612827302155 delta_month_win_rate_min=-0.07894736842105263 delta_month_avg_ret_net_min=0.003182388040029914 delta_bad_month_like_count=-4 delta_loss_concentration=-0.1928361282730215

C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
status=EVALUATED delta_avg=0.008526983869452956 delta_median=0.008237257989813554 delta_p25=0.00531942029803442 delta_win_rate=0.19283612827302155 delta_month_win_rate_min=-0.07894736842105263 delta_month_avg_ret_net_min=0.003182388040029914 delta_bad_month_like_count=-4 delta_loss_concentration=-0.1928361282730215
```

Interpretation:

```text
C36 controlled candidate formation confirms that suppressing/removing the weak G21 no-profit fallback branch and keeping G16 as comparator improves IS average, median, p25, win rate, bad-month-like count, and loss concentration versus the C35 G21+G16 baseline.
The month_win_rate_min becomes lower because at least one G16 month still has 0 win rate; therefore C37 must validate whether the improved distribution is robust or overfit before any OOS proof is allowed.
```

## Candidate Safety Audit

Final artifact safety audit passed for all seven candidates:

```text
C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR passed=true reason_code=WS_BT_C36_CANDIDATE_SELECTION_INPUT_SAFE
```

Safety markers:

```text
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
production_ready=false
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C35_MUTATION=true
```

## Not Evaluable Reasons

```text
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD
reason_code=C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE
message=Candidate requires D2 close or intraday path field that is not available in the C28/C35 IS evidence.

C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK
reason_code=C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE
message=Candidate requires a valid pre-trade regime feature that is not available in the C28/C35 IS evidence.

C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE
reason_code=C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE
message=Candidate requires direct gap/open damage pre-trade field that is not available in the C28/C35 IS evidence.
```

These are valid safety outcomes, not runtime failures.

## Diagnostic Conclusion

```text
diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
final_status=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
```

C36 forms a controlled combined IS candidate from C35 hypotheses. The candidate is diagnostic only and is not production-ready.

## Next Step Recommendation

```text
next_step_recommendation=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK
```

C37 must validate the C36 controlled candidate with IS validation / anti-overfit checks before any OOS proof is allowed.

Required C37 focus:

```text
validate_C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR=true
rolling_IS_validation=true
split_IS_validation=true
yearly_IS_validation=true
month_distribution_validation=true
bad_month_like_count_validation=true
ticker_concentration_validation=true
branch_concentration_validation=true
selection_safety_validation=true
NO_OOS_PROOF_BEFORE_C37_PASS=true
```

## Final Status

```text
C36_STATUS=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
PHPUNIT_C36=PASS
FULL_WATCHLIST_PHPUNIT=PASS
ARTISAN_C36_RUNTIME=COMPLETED
DIAGNOSTIC_CONCLUSION=C36_COMBINED_CANDIDATE_FORMED
NEXT_STEP=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK
production_ready=false
OOS_PROOF_UNLOCKED=false
PRODUCTION_ROLLOUT=false
```
