# WS C49 - Operator Validation Commands

## PHPUnit C49 only

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC49"
```

Expected completed marker:

```text
C49 PHPUnit: PASS
```

Do not claim PASS if this command is not run in the supported operator environment.

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected completed marker:

```text
Full Watchlist PHPUnit: PASS
```

Do not claim PASS if this command is not run in the supported operator environment.

## Runtime C49

```powershell
php artisan watchlist:backtest-c49-broader-strategy-redesign `
  --c48-artifact=storage/app/watchlist/backtest/c48-oos-failure-attribution.json `
  --expected-c48-hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json `
  --progress
```

Expected completed marker:

```text
status=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
production_ready=0
c48_hash_match=1
c48_status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
c48_diagnostic_conclusion=C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED
```

If the artifact already exists during validation, remove it first or use `--overwrite` only to rerun the same locked input. Do not use `--overwrite` to tune against OOS.

## Read artifact summary

```powershell
$run = Get-Content storage/app/watchlist/backtest/c49-broader-strategy-redesign.json | ConvertFrom-Json

$run | Select-Object `
  run_code,
  status,
  production_ready,
  diagnostic_conclusion,
  next_step_recommendation,
  expected_c48_hash,
  actual_c48_hash,
  c48_hash_match,
  c48_status,
  c48_diagnostic_conclusion |
  Format-List
```

## C48 hash validation

```powershell
$run | Select-Object `
  input_c48_artifact,
  expected_c48_hash,
  actual_c48_hash,
  c48_hash_match,
  c48_status,
  c48_diagnostic_conclusion,
  c48_next_step_recommendation |
  Format-List
```

## C48 carry-forward

```powershell
$run.c48_carry_forward_summary | Format-List
```

## IS period

```powershell
$run.is_redesign_period | Format-List
```

Expected:

```text
from=2023-01-02
to=2025-05-21
purpose=broader_strategy_redesign_is_only
oos_data_used_for_tuning=false
oos_return_used_for_selection=false
```

## OOS reserved period

```powershell
$run.oos_reserved_period | Format-List
```

Expected:

```text
from=2025-05-22
to=2026-05-29
used_for_selection=false
used_for_tuning=false
used_for_proof=false
```

## Source universe

```powershell
$run.source_universe_summary | Format-List
```

## Baseline / C44 comparator

```powershell
$run.baseline_c44_comparator_summary | Format-List
```

## Redesign profile results

```powershell
$run.redesign_profile_results |
  Select-Object `
    profile_code,
    family_code,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    p25_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    overlap_with_c44,
    overlap_with_baseline,
    material_selection_difference_pass,
    coverage_pass,
    quality_pass,
    stability_pass |
  Format-Table -AutoSize
```

## Shared-core escape attribution

```powershell
$run.shared_core_escape_attribution | Format-List
```

## Branch / G21 quota fragility

```powershell
$run.branch_quota_fragility_is_diagnostic |
  Select-Object `
    quota_variant,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    g21_share,
    g16_share,
    branch_balance_score,
    coverage_loss,
    quality_delta_vs_c44_is,
    stability_delta_vs_c44_is |
  Format-Table -AutoSize
```

## Regime-aware diagnostic

```powershell
$run.regime_aware_is_diagnostic |
  Select-Object `
    regime_profile_code,
    regime_field,
    regime_bucket,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    regime_robustness_pass |
  Format-Table -AutoSize
```

## Concentration guard diagnostic

```powershell
$run.concentration_guard_is_diagnostic |
  Select-Object `
    concentration_profile_code,
    row_count,
    max_ticker_share,
    max_sector_share,
    max_branch_share,
    unique_ticker_count,
    unique_sector_count,
    loss_cluster_share,
    concentration_pass |
  Format-Table -AutoSize
```

## Post-entry path diagnostic

```powershell
$run.post_entry_path_is_diagnostic |
  Select-Object `
    path_field,
    path_bucket,
    row_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    loss_count,
    loss_share,
    safe_for_selection,
    diagnostic_only,
    possible_pre_trade_proxy_fields |
  Format-Table -AutoSize
```

## Candidate scorecard

```powershell
$run.candidate_scorecard |
  Select-Object `
    candidate_code,
    profile_code,
    family_code,
    candidate_role,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    bad_month_like_count,
    overlap_with_c44,
    overlap_with_baseline,
    material_selection_difference_pass,
    concentration_pass,
    regime_robustness_pass,
    candidate_selected_for_c50_validation |
  Format-Table -AutoSize
```

## Selected C49 candidates for C50

```powershell
$run.selected_c49_candidates_for_c50 | Format-List
```

## C50 readiness decision

```powershell
$run.c50_readiness_decision | Format-List
```

Expected safety flags:

```text
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

## Candidate safety audit

```powershell
$run.candidate_safety_audit |
  Select-Object candidate_code,review_layer,passed,reason_code,message |
  Format-Table -AutoSize
```

## Not evaluable reasons

```powershell
$run.not_evaluable_reasons |
  Select-Object validation_layer,validation_slice,reason_code,message |
  Format-Table -AutoSize
```

## Diagnostic conclusion

```powershell
$run.diagnostics |
  Select-Object reason_code,message |
  Format-Table -AutoSize
```

Allowed C49 conclusions include:

```text
C49_BROADER_STRATEGY_REDESIGN_COMPLETED
C49_SHARED_CORE_ESCAPE_CANDIDATE_IDENTIFIED
C49_MATERIAL_SELECTION_DIFFERENCE_IDENTIFIED
C49_G21_QUOTA_FRAGILITY_CONFIRMED_IN_IS
C49_REGIME_AWARE_REDESIGN_PROMISING
C49_CONCENTRATION_GUARD_PROMISING
C49_POST_ENTRY_PROXY_REDESIGN_PROMISING
C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
C49_EVIDENCE_EXPANSION_REQUIRED
C49_NO_OOS_TUNING_CONFIRMED
C49_NOT_PRODUCTION_READY
```

## Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c49-broader-strategy-redesign.json -Algorithm SHA1
```

## Final operator validation result

Operator validation has been completed in the supported project environment.

```text
PHPUnit C49: PASS — OK (12 tests, 196 assertions)
Full Watchlist PHPUnit: PASS — OK (723 tests, 13647 assertions)
Runtime C49: COMPLETED
status=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
reason_code=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
artifact_path=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
artifact_hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8
production_ready=0
```

C48 hash validation result:

```text
expected_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
actual_c48_hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
c48_hash_match=1
c48_status=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
c48_diagnostic_conclusion=C48_SHARED_CORE_SELECTION_FAILURE_IDENTIFIED
```

Source universe runtime markers:

```text
source_source_evidence_artifact=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
source_source_rows_available=1
source_source_mode=C28_PICK_DIAGNOSTIC_ROWS
source_is_rows=15750
source_g21_rows=1770
source_g16_rows=1320
source_g13_rows=590
source_months=27
source_pre_trade_source_mode=DATABASE_AS_OF_SIGNAL_DATE_JOIN
source_pre_trade_source_row_count=482
source_oos_data_used_for_tuning=0
source_oos_return_used_for_selection=0
source_return_used_for_selection=0
source_future_path_used_for_selection=0
```

Selected candidate markers:

```text
selected_primary_candidate=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
selected_primary_profile_code=C49_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
selected_defensive_comparator=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
selected_coverage_comparator=
selected_regime_comparator=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
selected_concentration_guard_comparator=
selected_selected_candidate_count=3
selected_candidate_is_not_production=1
selected_production_ready=0
```

C50 readiness markers:

```text
c50_redesign_completed=1
c50_shared_core_escape_achieved=1
c50_material_selection_difference_achieved=1
c50_g21_quota_fragility_confirmed_in_is=0
c50_regime_aware_redesign_promising=1
c50_concentration_guard_promising=0
c50_path_proxy_redesign_promising=0
c50_primary_candidate_code=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
c50_defensive_comparator_code=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
c50_coverage_comparator_code=
c50_c50_recommendation=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
c50_diagnostic_conclusion=C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
c50_direct_oos_proof_recommended=0
c50_oos_proof_unlocked=0
c50_production_ready=0
```

Final expected marker:

```text
C49 IS broader strategy redesign completed
```

C49 remains non-production and does not authorize OOS proof. The next command sequence belongs to C50 IS validation / anti-overfit check.
