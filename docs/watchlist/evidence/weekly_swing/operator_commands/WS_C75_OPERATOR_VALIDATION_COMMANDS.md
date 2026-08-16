# WS_C75_OPERATOR_VALIDATION_COMMANDS

C75 is controlled operator-approved rollout execution review / controlled wiring execution review.

C75 starts from locked C74 final evidence. C74 controlled operator-reviewed rollout gate passed primary + backup.

E02 is primary controlled execution review candidate. B01 is backup controlled execution review candidate. A01 is comparator-only and cannot be promoted.

C75 validates C74 artifact hash and file SHA1. C75 validates C74 readiness through nested `c75_readiness_decision.*` path. C75 validates C74 → C60 lineage.

C75 requires --operator-approved. C75 requires non-empty --approval-reference.

C75 does not redesign. C75 does not retune. C75 does not run parameter search. C75 does not use OOS to rerank. C75 does not use parallel-run delta to rerank. C75 does not use controlled wiring result to rerank. C75 does not change candidate scope.

C75 may create controlled operator-approved execution review proof. C75 may create explicit controlled wiring context proof. C75 may create rollback/emergency disable proof. C75 may create next-session readiness decision.

C75 does not wire activated catalog to PLAN/CONFIRM live default runtime. C75 does not deploy live production. C75 does not mutate PLAN/CONFIRM. C75 does not change PLAN/CONFIRM output.

C75 keeps `production_catalog_runtime_wired=false`. C75 keeps `controlled_opt_in_runtime_bridge_active=false`. C75 keeps `controlled_parallel_run_active=false`. C75 keeps `controlled_rollout_active=false`. C75 keeps `controlled_wiring_context_persisted_to_live_runtime=false`. C75 keeps `production_deployment_allowed=false`. C75 keeps `production_deployment_executed=false`. C75 keeps `plan_confirm_mutation_allowed=false`. C75 keeps `plan_confirm_mutated=false`. C75 keeps `plan_confirm_runtime_reads_activated_catalog=false`. C75 keeps `live_plan_confirm_rollout_allowed=false`. C75 keeps `live_plan_confirm_rollout_executed=false`.

C75 carries bad-month risk as documented risk. C75 carries weak-regime risk as documented risk. C75 carries source-bias/shared-core risk as documented risk. C65 cleanup note remains non-blocking.

C75 may only recommend C76 controlled runtime opt-in pilot / shadow rollout preparation review if all execution/wiring gates pass.

C75 pass is not full production deployment. C75 pass is not PLAN/CONFIRM live rollout.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC75"
```

Expected marker:

```text
OK (... tests, ... assertions)
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Expected marker:

```text
OK (... tests, ... assertions)
```

## Runtime C75

```powershell
php artisan watchlist:backtest-c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review `
  --c74-artifact=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json `
  --expected-c74-hash=8958e1fcec798fbd364642864b0a9d0c21bd8f93 `
  --expected-c74-file-sha1=D4C2EF90B533BED11F6902E75141BE5774E947BE `
  --approval-reference=C75_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected status if C75 passes:

```text
C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
```

## Inspect artifact

```powershell
$run = Get-Content storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.controlled_operator_approved_rollout_execution_review_executed
$run.controlled_operator_approved_rollout_execution_review_allowed
$run.controlled_operator_approved_rollout_execution_review_pass
$run.controlled_wiring_execution_review_executed
$run.controlled_wiring_execution_review_allowed
$run.controlled_wiring_execution_review_pass
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.controlled_parallel_run_active
$run.controlled_rollout_active
$run.controlled_wiring_context_persisted_to_live_runtime
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c74_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.operator_approval_validation_summary | Format-List
$run.controlled_wiring_execution_review_decision | Format-List
$run.controlled_wiring_execution_context_summary | Format-List
$run.runtime_readiness_inspection_summary | Format-List
$run.feature_flag_operator_approval_kill_switch_validation_summary | Format-List
$run.rollback_and_emergency_disable_review_summary | Format-List
$run.c74_proof_carry_forward_validation_summary | Format-List
$run.controlled_execution_governance_summary | Format-List
$run.fallback_behavior_controlled_wiring_validation_summary | Format-List
$run.baseline_plan_confirm_non_mutation_summary | Format-List
$run.next_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List

$run.controlled_operator_approved_execution_candidate_scorecard |
  Select-Object `
    candidate_code,
    c75_role,
    parent_candidate_code,
    controlled_operator_approved_rollout_execution_review_pass,
    controlled_wiring_execution_review_pass,
    candidate_ready_for_next_controlled_pilot_or_shadow_rollout_review,
    candidate_active_in_controlled_catalog,
    production_catalog_runtime_wired,
    controlled_opt_in_runtime_bridge_active,
    controlled_parallel_run_active,
    controlled_rollout_active,
    controlled_wiring_context_persisted_to_live_runtime,
    production_deployment_allowed,
    production_deployment_executed,
    plan_confirm_mutation_allowed,
    plan_confirm_mutated,
    plan_confirm_runtime_reads_activated_catalog,
    live_plan_confirm_rollout_allowed,
    live_plan_confirm_rollout_executed,
    c74_lock_validation_pass,
    lineage_lock_validation_pass,
    candidate_scope_freeze_pass,
    operator_approval_validation_pass,
    default_off_feature_flag_pass,
    kill_switch_validation_pass,
    controlled_wiring_context_validation_pass,
    baseline_plan_confirm_hash_unchanged_pass,
    plan_confirm_output_non_mutation_pass,
    controlled_execution_advisory_only_pass,
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

$run.bad_month_controlled_wiring_review_results | Format-List
$run.weak_regime_controlled_wiring_review_results | Format-List
$run.source_bias_shared_core_controlled_wiring_validation_summary | Format-List
$run.documentation_governance_summary | Format-List
```

## Hash artifact

```powershell
Get-FileHash storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json -Algorithm SHA1
```

## Negative manual test: without operator approval

```powershell
php artisan watchlist:backtest-c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review `
  --c74-artifact=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json `
  --expected-c74-hash=8958e1fcec798fbd364642864b0a9d0c21bd8f93 `
  --expected-c74-file-sha1=D4C2EF90B533BED11F6902E75141BE5774E947BE `
  --approval-reference=C75_OPERATOR_APPROVED_EXECUTION_REVIEW_ONLY `
  --output=storage/app/watchlist/backtest/c75-no-operator-approval-test.json `
  --overwrite `
  --progress
```

Expected negative status:

```text
C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Negative manual test: without approval reference

```powershell
php artisan watchlist:backtest-c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review `
  --c74-artifact=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json `
  --expected-c74-hash=8958e1fcec798fbd364642864b0a9d0c21bd8f93 `
  --expected-c74-file-sha1=D4C2EF90B533BED11F6902E75141BE5774E947BE `
  --output=storage/app/watchlist/backtest/c75-no-approval-reference-test.json `
  --operator-approved `
  --overwrite `
  --progress
```

Expected negative status:

```text
C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

## Cleanup negative artifacts

```powershell
Remove-Item storage/app/watchlist/backtest/c75-no-operator-approval-test.json
Remove-Item storage/app/watchlist/backtest/c75-no-approval-reference-test.json
```


---

## Final operator results — 2026-06-24

```text
FOCUSED_PHPUNIT_C75=OK (18 tests, 203 assertions)
FULL_WATCHLIST_PHPUNIT=OK (1263 tests, 21123 assertions)
C75_RUNTIME_STATUS=C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C75_RUNTIME_REASON_CODE=C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED_PRIMARY_AND_BACKUP
C75_ARTIFACT_HASH=cd1346cd05ab5471a947fcb5304e0f347a4881eb
C75_FILE_SHA1=668043836BA1DB8FF50EC69DF0560988E633CF75
C74_LOCK_USED_BY_C75_ARTIFACT_HASH=8958e1fcec798fbd364642864b0a9d0c21bd8f93
C74_LOCK_USED_BY_C75_FILE_SHA1=D4C2EF90B533BED11F6902E75141BE5774E947BE
C75_C74_HASH_MATCH=true
C75_C74_FILE_SHA1_MATCH=true
C75_SOURCE_LINEAGE_MATCH=true
C75_FINAL_LOCK_SAFE_FOR_C76=true
NEXT_RECOMMENDATION=C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW
```

Final negative manual test result:

```text
C75_NEGATIVE_WITHOUT_OPERATOR_APPROVED=PASS
C75_NEGATIVE_WITHOUT_OPERATOR_APPROVAL_STATUS=C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
C75_NEGATIVE_WITHOUT_APPROVAL_REFERENCE=PASS
C75_NEGATIVE_WITHOUT_APPROVAL_REFERENCE_STATUS=C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_REJECTED_OPERATOR_APPROVAL_MISSING
```

Final cleanup result:

```text
storage/app/watchlist/backtest/c75-no-operator-approval-test.json removed
storage/app/watchlist/backtest/c75-no-approval-reference-test.json removed
```

C76 source lock:

```text
C76_EXPECTED_C75_ARTIFACT=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json
C76_EXPECTED_C75_ARTIFACT_HASH=cd1346cd05ab5471a947fcb5304e0f347a4881eb
C76_EXPECTED_C75_FILE_SHA1=668043836BA1DB8FF50EC69DF0560988E633CF75
```

The historical C74 hash `2e02737a212cf9043d5937f5354a3c31541dc22f` and file SHA1 `C7FCA9797AFF0B2B3CD4B37E587DC646F01C2187` are superseded/pre-alignment only and must not be used as active expected locks.
