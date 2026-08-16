# WS_C72_OPERATOR_VALIDATION_COMMANDS

C72 is controlled opt-in runtime bridge validation.
C72 starts from locked C71 final evidence.
C71 shadow-read/dry-run runtime validation passed primary + backup.
E02 is primary controlled opt-in runtime bridge candidate.
B01 is backup controlled opt-in runtime bridge candidate.
A01 is comparator-only and cannot be promoted.
C72 validates C71 artifact hash and file SHA1.
C72 validates C71 readiness through nested `c72_readiness_decision.*` path.
C72 validates C71 → C60 lineage.
C72 does not redesign.
C72 does not retune.
C72 does not run parameter search.
C72 does not use OOS to rerank.
C72 does not change candidate scope.
C72 may create isolated controlled opt-in runtime bridge proof.
C72 may create controlled bridge read proof.
C72 may create baseline PLAN/CONFIRM non-mutation proof.
C72 may create fallback behavior proof.
C72 does not wire activated catalog to PLAN/CONFIRM live.
C72 does not deploy live production.
C72 does not mutate PLAN/CONFIRM.
C72 does not change PLAN/CONFIRM output.
C72 keeps `production_catalog_runtime_wired=false`.
C72 keeps `controlled_opt_in_runtime_bridge_active=false`.
C72 keeps `production_deployment_allowed=false`.
C72 keeps `production_deployment_executed=false`.
C72 keeps `plan_confirm_mutation_allowed=false`.
C72 keeps `plan_confirm_mutated=false`.
C72 keeps `plan_confirm_runtime_reads_activated_catalog=false`.
C72 keeps `live_plan_confirm_rollout_allowed=false`.
C72 keeps `live_plan_confirm_rollout_executed=false`.
C72 carries bad-month risk as documented risk.
C72 carries weak-regime risk as documented risk.
C72 carries source-bias/shared-core risk as documented risk.
C65 cleanup note remains non-blocking.
C72 may only recommend C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation if all controlled opt-in gates pass.
C72 pass is not full production deployment.
C72 pass is not PLAN/CONFIRM rollout.

## Focused PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC72"
```

## Full Watchlist PHPUnit

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

## Runtime command

```powershell
php artisan watchlist:backtest-c72-controlled-opt-in-runtime-bridge-validation `
  --c71-artifact=storage/app/watchlist/backtest/c71-shadow-read-or-dry-run-runtime-validation.json `
  --expected-c71-hash=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f `
  --expected-c71-file-sha1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB `
  --output=storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json `
  --controlled-opt-in `
  --overwrite `
  --progress
```

## Runtime inspect

```powershell
$run = Get-Content storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.controlled_opt_in_runtime_bridge_validation_executed
$run.controlled_opt_in_runtime_bridge_validation_pass
$run.production_ready
$run.production_catalog_runtime_wired
$run.controlled_opt_in_runtime_bridge_active
$run.production_deployment_allowed
$run.production_deployment_executed
$run.plan_confirm_mutation_allowed
$run.plan_confirm_mutated
$run.plan_confirm_runtime_reads_activated_catalog
$run.live_plan_confirm_rollout_allowed
$run.live_plan_confirm_rollout_executed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c71_lock_validation_summary | Format-List
$run.lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.controlled_opt_in_runtime_bridge_validation_decision | Format-List
$run.runtime_path_inspection_summary | Format-List
$run.feature_flag_opt_in_kill_switch_runtime_bridge_validation_summary | Format-List
$run.controlled_bridge_read_execution_summary | Format-List
$run.plan_confirm_baseline_non_mutation_summary | Format-List
$run.fallback_behavior_runtime_bridge_validation_summary | Format-List
$run.c73_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.c65_cleanup_note_summary | Format-List

$run.controlled_opt_in_runtime_bridge_validation_candidate_scorecard |
  Select-Object `
    candidate_code,
    c72_role,
    parent_candidate_code,
    controlled_opt_in_runtime_bridge_validation_pass,
    candidate_ready_for_c73_controlled_parallel_run_non_mutating_plan_confirm_bridge_validation,
    candidate_active_in_controlled_catalog,
    production_catalog_runtime_wired,
    controlled_opt_in_runtime_bridge_active,
    controlled_opt_in_runtime_bridge_validation_allowed,
    production_deployment_allowed,
    production_deployment_executed,
    plan_confirm_mutation_allowed,
    plan_confirm_mutated,
    plan_confirm_runtime_reads_activated_catalog,
    live_plan_confirm_rollout_allowed,
    live_plan_confirm_rollout_executed,
    default_off_feature_flag_pass,
    kill_switch_runtime_bridge_validation_pass,
    explicit_opt_in_required_pass,
    controlled_bridge_read_execution_proof_pass,
    baseline_plan_confirm_hash_unchanged_pass,
    plan_confirm_output_non_mutation_pass,
    audit_logging_runtime_bridge_validation_pass,
    fallback_behavior_runtime_bridge_validation_pass,
    bad_month_governance_pass,
    weak_regime_governance_pass,
    source_bias_governance_pass,
    shared_core_governance_pass,
    safety_and_leakage_governance_pass,
    production_mutation_safety_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.bad_month_runtime_bridge_validation_review_results | Format-List
$run.weak_regime_runtime_bridge_validation_review_results | Format-List
$run.source_bias_shared_core_runtime_bridge_validation_summary | Format-List
$run.documentation_governance_summary | Format-List
```

## Artifact hash

```powershell
Get-FileHash storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json -Algorithm SHA1
```

Expected status when C72 passes:

```text
C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP
```

Expected safety fields remain false even when C72 passes:

```text
production_catalog_runtime_wired=false
controlled_opt_in_runtime_bridge_active=false
production_deployment_allowed=false
production_deployment_executed=false
plan_confirm_mutation_allowed=false
plan_confirm_mutated=false
plan_confirm_runtime_reads_activated_catalog=false
live_plan_confirm_rollout_allowed=false
live_plan_confirm_rollout_executed=false
```

## Final C72 Operator Validation Result — 2026-06-24

Operator validation completed and accepted.

```text
FOCUSED_PHPUNIT_C72=PASS
FOCUSED_PHPUNIT_C72_RESULT=OK (23 tests, 246 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1186 tests, 20424 assertions)
C72_RUNTIME=PASS
C72_STATUS=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C72_REASON_CODE=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C72_ARTIFACT_HASH=df3ee58a47572900d42b91d8348f0d6ea9ad1965
C72_ARTIFACT_FILE_SHA1=1ADF2C81797140A7A756B7A4EB02815AF1CBE75E
```

Final safety markers:

```text
PRODUCTION_READY=false
PRODUCTION_CATALOG_RUNTIME_WIRED=false
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_ACTIVE=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATION_ALLOWED=false
PLAN_CONFIRM_MUTATED=false
PLAN_CONFIRM_RUNTIME_READS_ACTIVATED_CATALOG=false
LIVE_PLAN_CONFIRM_ROLLOUT_ALLOWED=false
LIVE_PLAN_CONFIRM_ROLLOUT_EXECUTED=false
```

Final readiness marker:

```text
C73_CANDIDATE_READY_FOR_C73_COUNT=2
C73_RECOMMENDATION=C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION
```

PowerShell inspection note: avoid selecting `controlled_opt_in_runtime_bridge_validation_pass` twice in the same `Select-Object` call. Duplicate selection causes a non-runtime `Select-Object` warning and is not a C72 artifact failure.

