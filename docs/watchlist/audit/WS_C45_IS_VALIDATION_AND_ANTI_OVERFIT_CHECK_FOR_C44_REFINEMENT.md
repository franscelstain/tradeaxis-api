# WS C45 - IS Validation and Anti-Overfit Check for C44 Refinement

## Purpose

C45 locks the completed C44 artifact and independently reconstructs the selected market-extension refinement before validating it across full IS, yearly, rolling-window, bad-month, normal-month, ticker-concentration, branch-concentration, coverage, and downside layers. C45 does not run OOS proof, tune on OOS, unlock OOS, promote a candidate, or write a production catalog.

```text
input_c44_artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json
expected_c44_hash=606cd3109371b0d99419082daee18ff65f1cd99b
expected_c44_file_sha1=4A9A7A915DD37278D9F44634C5D08006B310ED71
expected_c44_status=C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED
expected_c44_conclusion=C44_GUARD_REFINEMENT_CANDIDATE_FORMED
target_candidate=C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA
```

## Validation design

The C44 selection is reconstructed from the same C28 IS rows, fixed monthly G21 quota, signal-date `market_index_roc20`, and deterministic metadata tie-break. The reconstructed row counts must exactly match C44 before validation can proceed.

```text
IS=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
baseline_selected_rows=1663
target_selected_rows=1663
target_selected_g21_rows=343
c44_selected_rows_match=true
c44_selected_g21_rows_match=true
rolling_windows=6M,9M,12M
rolling_slice_count=57
oos_data_used_for_tuning=false
oos_proof_executed=false
production_ready=false
```

Slice classification is conservative: small negative drift is a `WARNING`; material average, quartile, worst-month, or bad-month degradation is a `FAIL`. Any failed layer makes the overall result fail. A warning cannot unlock OOS.

## Result achieved

Nine independent validation layers completed: six passed, three warned, none failed.

```text
full_is=PASS
yearly=WARNING
rolling=WARNING
bad_month_like_stress=PASS
non_bad_month=WARNING
ticker_concentration=PASS
branch_concentration=PASS
month_coverage=PASS
downside_stability=PASS
overall_anti_overfit_result=WARNING
```

The full-IS result improves the baseline on the central outcome and every recorded downside measure:

```text
delta_avg_ret_net=+0.0004453772039743186
delta_median_ret_net=+0.00006237982334463335
delta_p25_ret_net=+0.000000015011106177086758
delta_p10_ret_net=+0.0014328532206546469
delta_win_rate=+0.0078171978352376
delta_month_avg_ret_net_min=+0.005767206176365093
delta_bad_month_like_count=-3
delta_loss_concentration=-0.007817197835237488
```

The six baseline bad months improve materially as a group:

```text
baseline_bad_months=2023-03,2023-09,2024-03,2024-05,2024-06,2024-10
baseline_bad_month_avg_ret_net=-0.0032944065976044717
target_bad_month_avg_ret_net=+0.0007560532255371511
delta_bad_month_avg_ret_net=+0.004050459823141623
baseline_bad_month_count=6
target_full_is_bad_month_count=3
```

The warning is real but not material-failure grade:

- 2023 average trails the baseline by `-0.00035134109455126246`, while its downside and bad-month count improve.
- 2025 average improves by `+0.0005299964973908815`, but worst-month average trails by `-0.0002759686816451593`.
- 12 of 57 rolling slices warn and none fail; the worst rolling average delta is `-0.0011491263561919643`.
- Non-bad months trail in average by `-0.0002410594293102246`, with unchanged median, p25, p10, worst-month average, and bad-month count.

Structural guards remain intact:

```text
months_covered=27/27
zero_pick_months=0
min_selected_rows_per_month=13
median_selected_rows_per_month=58
top_branch_share=0.79374624173181
branch_count=2
top_ticker_share=0.0661455201443175
unique_tickers=83
```

## Final decision

```text
PHPUNIT_C45=PASS - OK (11 tests, 76 assertions)
FULL_WATCHLIST_PHPUNIT=PASS - OK (675 tests, 13179 assertions)
ARTISAN_C45_RUNTIME=COMPLETED
status=C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED
artifact_path=storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json
artifact_hash=47970ba6e772bcf7fec68f306883f9f3d6cdd976
file_sha1=CF7D7D78103B543814C1B84F29B33AEA3E4FAF78
diagnostic_conclusion=C45_C44_REFINEMENT_WARNING_REQUIRES_REVIEW_BEFORE_OOS
next_step_recommendation=C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
requires_human_review_before_any_oos_step=true
production_ready=false
```

C45 therefore keeps the C44 candidate alive as a review candidate, but does not validate it for direct OOS advancement. C46 must review the warning slices or expand IS evidence before any explicit OOS decision.

