# WS C58 Operator Validation Commands

Run from project root:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC58"
```

Then full Watchlist PHPUnit:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Then C58 runtime:

```powershell
php artisan watchlist:backtest-c58-loss-cluster-concentration-redesign-continuation-is-only `
  --c57-artifact=storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json `
  --expected-c57-hash=71230896c2121fcfedddf36dd54c9c03ad462b4d `
  --expected-c57-file-sha1=50272917A107E304F8EEEB874DBC02A881DB0C31 `
  --from=2023-01-02 `
  --to=2025-05-21 `
  --output=storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json `
  --progress
```

If the output already exists and rerun is intentional, add:

```powershell
  --overwrite
```

Inspect artifact:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json | ConvertFrom-Json

$run.database_dictionary_read_summary | Format-List
$run.candidate_generation_summary | Format-List
$run.candidate_scorecard |
  Select-Object `
    candidate_code,
    parent_candidate_code,
    candidate_role,
    evaluated_picks_count,
    avg_ret_net,
    median_ret_net,
    win_rate,
    month_win_rate_min,
    max_branch_share,
    max_bucket_share,
    max_sector_share,
    max_ticker_share,
    max_month_share,
    loss_cluster_share,
    rolling_validation_pass,
    loo_validation_pass,
    regime_robustness_validation_pass,
    concentration_validation_pass,
    material_selection_difference_pass,
    anti_shared_core_pass,
    overall_is_redesign_pass,
    candidate_ready_for_c59,
    failure_reason_codes |
  Format-Table -AutoSize

$run.concentration_dependency_validation_results |
  Select-Object `
    candidate_code,
    max_ticker_share,
    max_sector_share,
    max_bucket_share,
    max_branch_share,
    max_month_share,
    unique_ticker_count,
    unique_sector_count,
    unique_bucket_count,
    unique_branch_count,
    loss_cluster_share,
    concentration_validation_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.loss_cluster_validation_results | Format-Table -AutoSize
$run.rolling_validation_summary | Format-List
$run.leave_one_month_out_summary | Format-List
$run.regime_robustness_validation_summary | Format-List
$run.material_selection_difference_summary | Format-List
$run.anti_shared_core_summary | Format-List
$run.source_bias_validation_summary | Format-List
$run.c59_readiness_decision | Format-List
```

Hash artifact:

```powershell
Get-FileHash storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json -Algorithm SHA1
```

Expected C58 acceptance checks:

```text
PHPUNIT_C58=PASS
FULL_WATCHLIST_PHPUNIT=PASS
ARTISAN_C58_RUNTIME=COMPLETED
C57_HASH_MATCH=true
C57_FILE_SHA1_MATCH=true
DATABASE_DICTIONARY_READ_REQUIRED=true
DICTIONARY_MISSING_COVERAGE_DETECTED=false
ASOF_SAFE=true
FUTURE_LOOKUP_DETECTED=false
OOS_ROWS_REQUESTED=0
REGIME_FULLY_EVALUABLE_RETAINED=true
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
NEXT_STEP_IS_EVIDENCE_BASED=true
```

If no candidate passes every IS gate, this is valid C58 evidence. Do not lower gates. Record the dominant blocker and continue IS-only.


## Final operator validation evidence

The following operator validation has been completed in the project environment:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC58"
OK (12 tests, 430 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (817 tests, 16397 assertions)

php artisan watchlist:backtest-c58-loss-cluster-concentration-redesign-continuation-is-only ... --progress
status=C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
reason_code=C58_LOSS_CLUSTER_GAP_REMAINS
artifact_hash=80d09de8053659bf01ce5b8b72d9e2d82cdf69dc
production_ready=0
c57_hash_match=1
c57_file_sha1_match=1
next_step_recommendation=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
c59_candidate_ready_for_c59_count=0
c59_rolling_validation_pass_candidate_count=4
c59_concentration_validation_pass_candidate_count=0
c59_loss_cluster_pass_candidate_count=0
c59_loo_validation_pass_candidate_count=0
c59_regime_robustness_pass_candidate_count=0
c59_direct_oos_proof_recommended=0
c59_oos_proof_unlocked=0
c59_production_ready=0
```

Final artifact file hash:

```powershell
Get-FileHash storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json -Algorithm SHA1
```

Operator result:

```text
FA6FE27604F6CDA664DCF90A251AF41672670700
```

Final expected interpretation:

```text
C58_IMPLEMENTATION_STATUS=DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY
C58_DIAGNOSTIC_CONCLUSION=C58_LOSS_CLUSTER_GAP_REMAINS
C58_OOS_UNLOCKED=false
C58_PRODUCTION_READY=false
NEXT_STEP=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
```
