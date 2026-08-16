# WS C42 — IS Rolling / Normal-Month Evidence Expansion

## 1. Tujuan

C42 adalah sesi **IS-only rolling / normal-month evidence expansion** untuk membedah warning yang dibawa dari C40/C41 sebelum OOS boleh dipertimbangkan.

C42 tidak melakukan OOS proof, tidak melakukan OOS tuning, tidak membuat best-of-OOS, tidak membuat production catalog, tidak promote candidate, dan tidak mengubah PLAN/CONFIRM production behavior.

## 2. Input C41 artifact

```text
input_c41_artifact=storage/app/watchlist/backtest/c41-is-review-or-evidence-expansion-before-oos.json
expected_c41_hash=fa3afd197cfe07d67d90edf87d69aec81310d791
expected_c41_file_sha1=9B44AD084DBD7637E0794A8AF5085E3A846D9486
```

C42 wajib block jika C41 artifact missing, hash mismatch, status/conclusion unexpected, `production_ready` bukan false, `oos_data_used_for_tuning` bukan false, target candidate missing, atau flag direct OOS/OOS unlocked invalid.

## 3. C41 evidence summary

```text
c41_status=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED
c41_diagnostic_conclusion=C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
c41_next_step_recommendation=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_OR_GUARD_REFINEMENT
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
overall_anti_overfit_result=WARNING
warning_layers_count=2
failed_layers_count=0
rolling_warning_windows=3
non_bad_month_warning=true
guard_blockers_resolved=true
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## 4. Boundary C42

```text
IS_ONLY_EVIDENCE_EXPANSION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C41_MUTATION=true
NO_C01_TO_C41_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
oos_return_used_for_candidate_selection=false
```

Return/realized result hanya dipakai setelah selection pre-trade selesai untuk evaluasi diagnostik.

## 5. IS period

```text
from=2023-01-02
to=2025-05-21
oos_reserved_from=2025-05-22
oos_reserved_to=2026-05-29
oos_data_used_for_tuning=false
```

## 6. Target candidate

```text
target_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
baseline_comparator=C39_BASELINE_C36_CURRENT_BRANCH_BEHAVIOR
selection_model=G16 next_open_delay_after_close_signal + metadata-sorted G21 monthly quota
selection_fields=selected_source_code,bucket_code,trade_month,trade_date,ticker,param_id,row_code
```

## 7. Rolling warning window expansion

C42 membedah 3 rolling warning window dari C41:

```text
2023-10_to_2024-03 — 6_month_window
2023-07_to_2024-03 — 9_month_window
2023-04_to_2024-03 — 12_month_window
```

Runtime final operator validation menunjukkan semua rolling warning mengarah ke suspected warning month yang sama:

```text
suspected_warning_month=2024-03
candidate_warning_explanation_code=C42_WARNING_CLUSTER_G21_METADATA_QUOTA_LOSS_MONTH
target_g16_rows=20
target_g21_rows=13
target_g16_avg_ret_net=0.010582180659756094
target_g21_avg_ret_net=-0.03640117418032827
target_g21_win_rate=0.07692307692307693
```

Interpretasi: warning rolling bukan karena C39 guard coverage/branch gagal, tetapi karena quota G21 metadata-sorted membawa cluster G21 buruk di 2024-03. Return dipakai hanya untuk menjelaskan hasil setelah selection, bukan untuk selection.

## 8. Non-bad-month warning expansion

C42 membedah slice:

```text
validation_slice=NON_BAD_MONTH_IS_MONTHS
new_bad_like_months_created_by_candidate=2024-03
target_bad_like_month_source=2024-03
normal_month_explanation_code=C42_NON_BAD_MONTH_WARNING_CLUSTER_G21_METADATA_QUOTA_LOSS_MONTH
normal_month_warning_explained=true
```

Interpretasi: non-bad-month warning berasal dari month 2024-03 yang baseline-nya bukan bad-like, tetapi target C39 berubah menjadi bad-like karena subset G21 quota yang buruk.

## 9. Guard preservation audit

C42 tetap mempertahankan guard C39:

```text
candidate_months_covered=27
candidate_zero_pick_months=0
candidate_min_selected_rows_per_month=13
candidate_median_selected_rows_per_month=58
candidate_top_branch_share=0.79374624173181
candidate_g16_share=0.79374624173181
candidate_g21_share=0.20625375826819
removed_or_suppressed_g21_rows=1427
coverage_guard_preserved=true
branch_guard_preserved=true
c39_guard_preservation_result=PASS
```

## 10. Pre-trade field availability matrix

C42 mengklasifikasikan field sebagai berikut:

```text
SAFE_PRE_TRADE_SELECTION_FIELD=trade_date,trade_month,ticker/symbol,selected_source_code,bucket_code,param_id,row_code
DIAGNOSTIC_ONLY_EVALUATION_FIELD=profile_code,profile_exit_reason
UNSAFE_FUTURE_OR_RETURN_FIELD=avg_ret_net,profile_ret_net,ret_net,delta_vs_raw_r09
UNAVAILABLE_FIELD=gap_open_pct,market_regime,sector_code,sector_roc20,dv20_idr,vol_ratio,liquidity_bucket
```

## 11. Guard refinement feasibility

```text
feasibility_result=C42_NO_ADDITIONAL_SAFE_REFINEMENT_FIELD_AVAILABLE
safe_refinement_field_available=false
safe_refinement_candidate_formed=false
refinement_candidate_code=null
return_used_for_selection=false
future_path_used_for_selection=false
oos_data_used_for_tuning=false
production_ready=false
```

C42 tidak membentuk refinement candidate karena current C28 rows tidak menyediakan additional safe pre-trade quality field di luar metadata yang sudah dipakai C39. Membentuk rule berdasarkan ticker/return month yang rugi berisiko menjadi retrospective IS overfit.

## 12. Refinement candidate summary

```text
refinement_candidate_results=[]
safe_refinement_candidate_formed=false
candidate_is_not_production=true
production_ready=false
```

Tidak ada candidate baru yang dibentuk di C42.

## 13. Candidate comparison table

C42 menyertakan candidate comparison table untuk baseline C39 dan target C39. Comparison bersifat IS diagnostic only dan tidak memilih ulang candidate dari OOS.

## 14. Warning explanation summary

```text
rolling_warning_explanation_result=C42_ROLLING_WARNING_EXPLAINED
normal_month_warning_explanation_result=C42_NORMAL_MONTH_WARNING_EXPLAINED
warning_interpretation=STRUCTURAL_METADATA_QUOTA_WEAKNESS
candidate_warning_explained=true
candidate_warning_acceptable_for_direct_oos=false
safe_refinement_field_available=false
safe_refinement_candidate_formed=false
```

## 15. C42 decision summary

```text
c39_candidate_lock_decision=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
c42_candidate_decision=C42_C39_CANDIDATE_REQUIRES_GUARD_REFINEMENT_BEFORE_OOS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
requires_c43_is_validation=false
requires_c43_oos_proof=false
requires_c43_evidence_expansion=true
requires_c43_pre_trade_field_expansion_diagnostic=true
production_ready=false
```

## 16. Candidate safety audit

```text
return_used_for_selection=false
future_path_used_for_selection=false
profile_ret_net_used_for_selection=false
future_path_price_used_for_selection=false
derived_mfe_mae_used_for_execution=false
oos_data_used_for_tuning=false
production_ready=false
candidate_is_not_production=true
no_oos_proof=true
no_best_of_oos=true
no_oos_winner=true
no_production_catalog=true
no_candidate_promoted=true
no_plan_confirm_mutation=true
```

## 17. Not evaluable reasons

C42 carry-forward evidence gaps dari C41:

```text
C39_BLOCKED_G21_PRE_TRADE_QUALITY_FIELD_UNAVAILABLE
C39_BLOCKED_ROLLING_STABILITY_PRE_TRADE_SPLIT_FIELD_UNAVAILABLE
C42_NO_SAFE_REFINEMENT_FIELD_BEYOND_C39_METADATA
```

## 18. Diagnostic conclusion

Final operator validation menghasilkan:

```text
diagnostic_conclusion=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
next_step_recommendation=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC
```

## 19. Runtime result

Operator validation final:

```text
PHPUNIT_C42=PASS
PHPUNIT_C42_RESULT=OK (12 tests, 97 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (639 tests, 12860 assertions)
ARTISAN_C42_RUNTIME=COMPLETED
```

Runtime final:

```text
status=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
reason_code=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
artifact_path=storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json
artifact_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
file_sha1=CBB44B864DD9B2071DE5B10C426F01ED2776525D
production_ready=0
expected_c41_hash=fa3afd197cfe07d67d90edf87d69aec81310d791
actual_c41_hash=fa3afd197cfe07d67d90edf87d69aec81310d791
c41_hash_match=1
c41_status=C41_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED
c41_diagnostic_conclusion=C41_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
diagnostic_conclusion=C42_NO_SAFE_REFINEMENT_FIELD_AVAILABLE
next_step_recommendation=C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC
```


## 20. Artifact output

```text
artifact_path=storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json
artifact_hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11
file_sha1=CBB44B864DD9B2071DE5B10C426F01ED2776525D
status=C42_IS_ROLLING_NORMAL_MONTH_EVIDENCE_EXPANSION_COMPLETED
production_ready=false
```

## 21. Status akhir C42

C42 implementation, PHPUnit, full Watchlist PHPUnit, dan runtime Artisan sudah operator-validated. Final decision C42: warning C39 berhasil dijelaskan sebagai structural metadata quota weakness, tetapi tidak ada additional safe pre-trade refinement field. C42 tidak membuka OOS proof, tidak production-ready, dan next step adalah C43 pre-trade field expansion diagnostic.
