# WS_C76_OPERATOR_VALIDATION_COMMANDS

C76 is controlled runtime opt-in pilot / shadow rollout preparation review.

C76 starts from locked C75 final evidence. C75 controlled operator-approved execution/wiring review passed primary + backup.

C76 validates C75 artifact hash and file SHA1. C76 validates C75 readiness through nested `next_readiness_decision.*` path. C76 validates C75 readiness through nested next_readiness_decision.* path. C76 validates C75 -> C60 lineage.

E02 is primary controlled pilot/shadow preparation candidate. B01 is backup controlled pilot/shadow preparation candidate. A01 is comparator-only and cannot be promoted.

C76 requires --operator-approved. C76 requires non-empty --approval-reference.

C76 does not redesign. C76 does not retune. C76 does not run parameter search. C76 does not use OOS to rerank. C76 does not use parallel-run delta to rerank. C76 does not use controlled wiring result to rerank. C76 does not use pilot/shadow preparation result to rerank. C76 does not change candidate scope.

C76 may create controlled runtime opt-in pilot preparation proof. C76 may create controlled shadow rollout preparation proof. C76 may create explicit controlled pilot/shadow context proof. C76 may create rollback/emergency disable proof. C76 may create next-session readiness decision.

C76 does not wire activated catalog to PLAN/CONFIRM live default runtime. C76 does not deploy live production. C76 does not mutate PLAN/CONFIRM. C76 does not change PLAN/CONFIRM output.

C76 keeps `production_catalog_runtime_wired=false`, `controlled_opt_in_runtime_bridge_active=false`, `controlled_parallel_run_active=false`, `controlled_rollout_active=false`, `controlled_pilot_context_persisted_to_live_runtime=false`, `controlled_shadow_context_persisted_to_live_runtime=false`, `production_deployment_allowed=false`, `production_deployment_executed=false`, `plan_confirm_mutation_allowed=false`, `plan_confirm_mutated=false`, `plan_confirm_runtime_reads_activated_catalog=false`, `live_plan_confirm_rollout_allowed=false`, and `live_plan_confirm_rollout_executed=false`.

C76 carries bad-month risk as documented risk. C76 carries weak-regime risk as documented risk. C76 carries source-bias/shared-core risk as documented risk. C65 cleanup note remains non-blocking.

C76 may only recommend C77 controlled runtime opt-in pilot / shadow rollout execution review if all preparation gates pass.

C76 pass is not full production deployment. C76 pass is not PLAN/CONFIRM live rollout. C76 pass is not runtime bridge activation.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC76"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime Command

```powershell
php artisan watchlist:backtest-c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review `
  --c75-artifact=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json `
  --expected-c75-hash=cd1346cd05ab5471a947fcb5304e0f347a4881eb `
  --expected-c75-file-sha1=668043836BA1DB8FF50EC69DF0560988E633CF75 `
  --approval-reference=C76_OPERATOR_APPROVED_PREPARATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected runtime pass status:

```text
C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

Expected safety fields, always false even when C76 passes:

```text
production_catalog_runtime_wired=false
controlled_opt_in_runtime_bridge_active=false
controlled_parallel_run_active=false
controlled_rollout_active=false
controlled_pilot_context_persisted_to_live_runtime=false
controlled_shadow_context_persisted_to_live_runtime=false
production_deployment_allowed=false
production_deployment_executed=false
plan_confirm_mutation_allowed=false
plan_confirm_mutated=false
plan_confirm_runtime_reads_activated_catalog=false
live_plan_confirm_rollout_allowed=false
live_plan_confirm_rollout_executed=false
```

## Runtime Inspection

```powershell
$run = Get-Content storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.controlled_runtime_opt_in_pilot_preparation_review_executed
$run.controlled_runtime_opt_in_pilot_preparation_review_allowed
$run.controlled_runtime_opt_in_pilot_preparation_review_pass
$run.controlled_shadow_rollout_preparation_review_executed
$run.controlled_shadow_rollout_preparation_review_allowed
$run.controlled_shadow_rollout_preparation_review_pass
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.controlled_pilot_context_persisted_to_live_runtime
$run.controlled_shadow_context_persisted_to_live_runtime
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c75_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.controlled_pilot_shadow_preparation_decision | Format-List
$run.controlled_pilot_preparation_context_summary | Format-List
$run.controlled_shadow_preparation_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.feature_flag_operator_approval_kill_switch_validation_summary | Format-List
$run.rollback_and_emergency_disable_review_summary | Format-List
$run.c75_proof_carry_forward_validation_summary | Format-List
$run.controlled_pilot_shadow_preparation_governance_summary | Format-List
$run.fallback_behavior_controlled_pilot_shadow_validation_summary | Format-List
$run.baseline_plan_confirm_non_mutation_summary | Format-List
$run.next_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List
```

## Candidate Scorecard Inspection

```powershell
$run.controlled_pilot_shadow_preparation_candidate_scorecard |
  Select-Object `
    candidate_code,
    c76_role,
    parent_candidate_code,
    controlled_runtime_opt_in_pilot_preparation_review_pass,
    controlled_shadow_rollout_preparation_review_pass,
    candidate_ready_for_controlled_runtime_opt_in_pilot_or_shadow_rollout_execution_review,
    candidate_active_in_controlled_catalog,
    production_catalog_runtime_wired,
    controlled_opt_in_runtime_bridge_active,
    controlled_parallel_run_active,
    controlled_rollout_active,
    controlled_pilot_context_persisted_to_live_runtime,
    controlled_shadow_context_persisted_to_live_runtime,
    production_deployment_allowed,
    production_deployment_executed,
    plan_confirm_mutation_allowed,
    plan_confirm_mutated,
    plan_confirm_runtime_reads_activated_catalog,
    live_plan_confirm_rollout_allowed,
    live_plan_confirm_rollout_executed,
    c75_lock_validation_pass,
    lineage_lock_validation_pass,
    candidate_scope_freeze_pass,
    operator_approval_validation_pass,
    default_off_feature_flag_pass,
    kill_switch_validation_pass,
    controlled_pilot_context_validation_pass,
    controlled_shadow_context_validation_pass,
    baseline_plan_confirm_hash_unchanged_pass,
    plan_confirm_output_non_mutation_pass,
    controlled_preparation_advisory_only_pass,
    fallback_behavior_validation_pass,
    rollback_plan_validation_pass,
    emergency_disable_validation_pass,
    audit_logging_validation_pass,
    observability_validation_pass,
    bad_month_governance_pass,
    weak_regime_governance_pass,
    source_bias_governance_pass,
    shared_core_governance_pass,
    safety_and_leakage_governance_pass,
    production_mutation_safety_pass,
    documentation_governance_pass,
    failure_reason_codes |
  Format-Table -AutoSize
```

## Artifact SHA1

```powershell
Get-FileHash storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json -Algorithm SHA1
```

## Negative Test: without operator approval

```powershell
php artisan watchlist:backtest-c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review `
  --c75-artifact=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json `
  --expected-c75-hash=cd1346cd05ab5471a947fcb5304e0f347a4881eb `
  --expected-c75-file-sha1=668043836BA1DB8FF50EC69DF0560988E633CF75 `
  --approval-reference=C76_OPERATOR_APPROVED_PREPARATION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c76-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected negative status:

```text
C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative Test: without approval reference

```powershell
php artisan watchlist:backtest-c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review `
  --c75-artifact=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json `
  --expected-c75-hash=cd1346cd05ab5471a947fcb5304e0f347a4881eb `
  --expected-c75-file-sha1=668043836BA1DB8FF50EC69DF0560988E633CF75 `
  --output=storage/app/watchlist/backtest/c76-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Delete negative artifacts:

```powershell
Remove-Item storage/app/watchlist/backtest/c76-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c76-no-approval-reference-test.json
```
