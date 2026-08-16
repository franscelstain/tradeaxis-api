# WS C47 - OOS Proof with Locked C44 Refinement

## Purpose

C47 performs the one-shot reserved-window OOS proof authorized by C46 for the locked C44 market-extension refinement. The candidate, monthly quota, ranking rule, OOS window, execution model, source rows, and acceptance gate are frozen before evaluation.

C47 does not tune from OOS, lower a gate, select best-of-OOS, reselect a candidate or profile, create a production catalog, promote a candidate, or mutate PLAN/CONFIRM behavior.

## Source locks

```text
input_c46_artifact=storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json
expected_c46_hash=d531dd5b911f55d8824ac514ccc7600470a076bd
expected_c46_file_sha1=59A80EA0BAE12034F42395EA0605536D9F9B2E5D

input_c44_artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json
expected_c44_hash=606cd3109371b0d99419082daee18ff65f1cd99b
expected_c44_file_sha1=4A9A7A915DD37278D9F44634C5D08006B310ED71

input_oos_source_artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
expected_oos_source_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
expected_oos_source_file_sha1=62744E652235799A38CBCA57F81D2F1C3BE25FF4
```

All three runtime stable-hash checks passed.

## Frozen candidate and proof contract

```text
candidate_code=C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA
selection_rule=prefer non-extended IHSG ROC20 dates, then signal metadata, inside fixed quota
monthly_g21_quota=13
oos_from=2025-05-22
oos_to=2026-05-29
entry=NEXT_OPEN
exit=STOP_TP_OR_TIME
hold=5
fee=IDR_FIXED
slip=0
gap=OPEN
px=IDX_BANDS
```

C47 reuses the pre-existing C29 OOS acceptance thresholds without adjusting them after seeing C47 returns:

```text
minimum_evaluated_picks=40
average_ret_net>0
median_ret_net>=0
p25_ret_net>=-0.03
minimum_month_win_rate>=0.45
maximum_missing_path_count=0
maximum_lookahead_violation_count=0
threshold_source=LOCKED_C29_OOS_ACCEPTANCE_GATE_REUSED_WITHOUT_RETUNING
```

## Source and selection result

```text
source_all_pick_rows=132
source_g16_g21_rows=98
source_g16_rows=18
source_g21_rows=80
source_months=11
market_source_mode=DATABASE_EXACT_SIGNAL_DATE_JOIN
market_index_roc20_missing_count=0
target_selected_rows=85
target_selected_g16_rows=18
target_selected_g21_rows=67
expected_target_selected_g21_rows=67
fixed_quota_pass=true
selection_rule_reconstruction_pass=true
target_missing_path_count=0
target_lookahead_violation_count=0
target_future_or_return_selection_violation_count=0
```

## OOS result achieved

The refinement improves the locked metadata baseline, especially in December 2025, but its absolute OOS performance remains below the frozen gate.

```text
target_evaluated_picks_count=85
target_avg_ret_net=-0.006863279994262265
target_median_ret_net=-0.0005005957088935833
target_p25_ret_net=-0.017446232516167844
target_p10_ret_net=-0.04048987753061734
target_win_rate=0.3411764705882353
target_month_win_rate_min=0
target_month_avg_ret_net_min=-0.04048987753061734
target_bad_month_like_count=7
target_months_covered=11
```

Versus the C44 metadata-quota comparator:

```text
baseline_avg_ret_net=-0.00769232413206381
baseline_win_rate=0.29411764705882354
delta_avg_ret_net=+0.0008290441378015446
delta_win_rate=+0.047058823529411764
delta_median_ret_net=0
delta_p25_ret_net=0
delta_p10_ret_net=0
delta_bad_month_like_count=0
```

The only month altered materially by the 13-row quota/ranking is December 2025:

```text
baseline_2025_12_avg_ret_net=0.002221691514802885
baseline_2025_12_win_rate=0.15384615384615385
target_2025_12_avg_ret_net=0.007642364723505276
target_2025_12_win_rate=0.46153846153846156
```

Seven target months remain bad-like:

```text
2025-06,2025-07,2025-08,2025-09,2025-10,2026-03,2026-05
```

## Gate result

Fourteen of seventeen checks pass. Three frozen performance checks fail:

```text
avg_pass=false
median_pass=false
month_win_rate_pass=false
p25_pass=true
min_picks_pass=true
missing_path_pass=true
lookahead_pass=true
overall_pass=false
```

This is a performance/robustness failure, not a source-lock, data-completeness, selection-leakage, or lookahead failure.

## Final decision

```text
PHPUNIT_C47=PASS - OK (12 tests, 75 assertions)
FULL_WATCHLIST_PHPUNIT=PASS - OK (698 tests, 13336 assertions)
ARTISAN_C47_RUNTIME=COMPLETED
status=C47_OOS_PROOF_FAILED
artifact_path=storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json
artifact_hash=1c742e257847752def1f582dc24d6061a4c4e735
file_sha1=351B0805F43D2B610B6826C4CDE1513B93FF2FE0
diagnostic_conclusion=C47_LOCKED_C44_REFINEMENT_OOS_PROOF_FAILED
next_step_recommendation=C48_OOS_FAILURE_ATTRIBUTION_FOR_C44_REFINEMENT
oos_proof_executed=true
oos_result_used_for_retuning=false
oos_result_used_for_candidate_reselection=false
production_ready=false
```

C47 does not authorize production. C48 must attribute the failure without retuning on the OOS window.

