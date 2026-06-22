# WS C65 — Operator Validation Commands

Run focused C65 tests:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC65"
```

Run full Watchlist unit suite:

```powershell
vendor\bin\phpunit tests\Unit\Watchlist
```

Run C65 runtime review:

```powershell
php artisan watchlist:backtest-c65-production-pre-lock-review `
  --c64-artifact=storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json `
  --expected-c64-hash=767d860956e0f27eeedccdc30f73aa1d0e5a415b `
  --expected-c64-file-sha1=032C7BA7435799D83CC06EEDBC463A9AF2B123B3 `
  --c63-artifact=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json `
  --expected-c63-hash=e98f1386928b36ee367728ceeec4de4344e1f3be `
  --expected-c63-file-sha1=24C7EE585A165DA41E8FC22538A68145247C68B4 `
  --c62-artifact=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json `
  --expected-c62-hash=d3a089b9b986838764d517682035d76e0bb4112d `
  --expected-c62-file-sha1=8DF1649BC72233D119581A802F9E41BA9BEBF12E `
  --c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json `
  --expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8 `
  --expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6 `
  --c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json `
  --expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705 `
  --expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F `
  --output=storage/app/watchlist/backtest/c65-production-pre-lock-review.json `
  --overwrite `
  --progress
```

Inspect C65 artifact:

```powershell
$run = Get-Content storage/app/watchlist/backtest/c65-production-pre-lock-review.json | ConvertFrom-Json

$run.run_code
$run.status
$run.reason_code
$run.artifact_hash
$run.production_ready
$run.production_prelock_review_executed
$run.production_prelock_review_pass
$run.production_catalog_allowed
$run.production_deployment_allowed

$run.source_artifact_locks | Format-List
$run.database_dictionary_read_summary | Format-List
$run.c64_lock_validation_summary | Format-List
$run.c63_lineage_validation_summary | Format-List
$run.c62_lineage_validation_summary | Format-List
$run.c61_lineage_validation_summary | Format-List
$run.c60_lineage_validation_summary | Format-List
$run.candidate_scope_freeze_summary | Format-List
$run.c64_oos_proof_replay_summary | Format-List
$run.production_prelock_decision | Format-List
$run.c66_readiness_decision | Format-List
$run.failure_attribution_summary | Format-List
$run.production_mutation_safety_summary | Format-List
$run.c64_cleanup_note_summary | Format-List

$run.production_prelock_candidate_scorecard |
  Select-Object `
    candidate_code,
    c65_role,
    parent_candidate_code,
    production_prelock_review_pass,
    candidate_ready_for_c66,
    production_catalog_allowed,
    production_deployment_allowed,
    production_ready,
    bad_month_governance_pass,
    weak_regime_governance_pass,
    concentration_governance_pass,
    loss_cluster_governance_pass,
    rolling_governance_pass,
    source_bias_governance_pass,
    shared_core_governance_pass,
    safety_and_leakage_governance_pass,
    plan_confirm_non_mutation_pass,
    production_catalog_non_creation_pass,
    failure_reason_codes |
  Format-Table -AutoSize

$run.bad_month_governance_review_results | Format-List
$run.weak_regime_governance_review_results | Format-List
$run.concentration_loss_cluster_governance_summary | Format-List
$run.rolling_month_dependency_governance_summary | Format-List
$run.source_bias_shared_core_governance_summary | Format-List
$run.documentation_governance_summary | Format-List
```

Hash artifact:

```powershell
Get-FileHash storage/app/watchlist/backtest/c65-production-pre-lock-review.json -Algorithm SHA1
```

Expected governance result if all C65 gates pass:

```text
C65_STATUS=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C66_RECOMMENDATION=C66_PRODUCTION_LOCK_REVIEW
PRODUCTION_READY=false
PRODUCTION_CATALOG_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
```

C65 is not production-ready by itself. It does not create, activate, or deploy a production catalog and does not mutate PLAN/CONFIRM.


---

## Final Operator Validation Evidence — C65

Status: `IMPLEMENTED_OPERATOR_VALIDATED / C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP / READY_FOR_C66_PRODUCTION_LOCK_REVIEW / NOT_PRODUCTION_READY`

Operator validation was executed on the local repository after the C65 status-logic hotfix. Focused C65 PHPUnit and full Watchlist PHPUnit both passed, then the official C65 runtime command generated the final C65 artifact.

```text
FOCUSED_C65_PHPUNIT=PASS
FOCUSED_C65_PHPUNIT_RESULT=OK (28 tests, 193 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (1024 tests, 18664 assertions)
C65_RUNTIME=COMPLETED
C65_RUN_CODE=C65_PRODUCTION_PRE_LOCK_REVIEW
C65_FINAL_STATUS=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C65_REASON_CODE=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
C65_ARTIFACT_PATH=storage/app/watchlist/backtest/c65-production-pre-lock-review.json
C65_ARTIFACT_HASH=f08da5acc87ccbe0d88c39423c4321496230b01b
C65_FILE_SHA1=115201C1F44C7C420ABA3251435F21B870EF9AE6
PRODUCTION_READY=false
PRODUCTION_PRELOCK_REVIEW_EXECUTED=true
PRODUCTION_PRELOCK_REVIEW_PASS=true
PRODUCTION_CATALOG_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
```

Source lock and lineage validation completed successfully:

```text
C64_HASH_MATCH=true
C64_FILE_SHA1_MATCH=true
C63_HASH_MATCH=true
C63_FILE_SHA1_MATCH=true
C62_HASH_MATCH=true
C62_FILE_SHA1_MATCH=true
C61_HASH_MATCH=true
C61_FILE_SHA1_MATCH=true
C60_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
```

Production pre-lock decision:

```text
PRODUCTION_PRELOCK_VALIDATION_COMPLETED=true
PRODUCTION_PRELOCK_STATUS=C65_PRODUCTION_PRE_LOCK_REVIEW_PASSED_PRIMARY_AND_BACKUP
PRODUCTION_PRELOCK_REVIEW_PASS=true
PRIMARY_PRODUCTION_PRELOCK_PASS=true
BACKUP_PRODUCTION_PRELOCK_PASS=true
PRIMARY_CANDIDATE_CODE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_CANDIDATE_CODE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
COMPARATOR_ONLY_CANDIDATE_CODE=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
PRODUCTION_PRELOCK_PASS_SCOPE=PRIMARY_AND_BACKUP
PRODUCTION_READY=false
PRODUCTION_CATALOG_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
```

C66 readiness decision:

```text
C66_VALIDATION_COMPLETED=true
CANDIDATE_READY_FOR_C66_COUNT=2
CANDIDATE_READY_FOR_C66_CODES=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE,C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
C66_RECOMMENDATION=C66_PRODUCTION_LOCK_REVIEW
C66_DECISION_REASON=C65 production pre-lock review passed. Next step is C66 production lock review only.
PRODUCTION_READY=false
PRODUCTION_CATALOG_ALLOWED=false
PRODUCTION_DEPLOYMENT_ALLOWED=false
```

Failure attribution and cleanup note:

```text
DOMINANT_BLOCKER=NONE
FAILURE_REASON_CODES={}
A01_COMPARATOR_ONLY_NOT_FAILURE_FOR_PRELOCK_SCOPE=true
REPAIR_RECOMMENDATION=C66_PRODUCTION_LOCK_REVIEW
C64_LEGACY_REPAIR_RECOMMENDATION=C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY
C64_LEGACY_REPAIR_RECOMMENDATION_NON_BLOCKING=true
NORMALIZED_REPAIR_RECOMMENDATION=NOT_REQUIRED
C65_FAILURE_REPAIR_REQUIRED=false
```

Production mutation safety remained clean:

```text
PRODUCTION_CATALOG_CREATED=false
PRODUCTION_CATALOG_ACTIVATED=false
PRODUCTION_DEPLOYMENT_EXECUTED=false
PLAN_CONFIRM_MUTATED=false
SELECTION_CHANGED_AFTER_C64=false
PARAMETER_CHANGED_AFTER_C64=false
NEW_CANDIDATE_CREATED=false
OOS_REUSED_FOR_RANKING=false
LATEST_SHORTCUT_USED=false
DATE_DESC_SHORTCUT_USED=false
FUTURE_LOOKUP_DETECTED=false
RETURN_FIELDS_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
PRODUCTION_MUTATION_SAFETY_PASS=true
```

Final C65 conclusion: C65 is accepted as production pre-lock review for primary E02 and backup B01. A01 remains comparator-only and is not promoted. C65 does not declare production-ready and does not authorize production catalog creation, activation, deployment, or PLAN/CONFIRM mutation. The only allowed next step is `C66_PRODUCTION_LOCK_REVIEW`.
