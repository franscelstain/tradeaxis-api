# WS_C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION

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

## Locked C71 evidence

```text
C71_STATUS=C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C71_REASON_CODE=C71_SHADOW_READ_OR_DRY_RUN_RUNTIME_VALIDATION_PASSED_PRIMARY_AND_BACKUP
C71_ARTIFACT_HASH=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f
C71_FILE_SHA1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB
C71_READY_FOR_C72_COUNT=2
C71_C72_RECOMMENDATION=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION
```

## Runtime safety boundary

C72 creates only an isolated controlled opt-in runtime bridge validation path. The command requires `--controlled-opt-in`, the feature flags remain default off, and the kill switch proof must show force-disable behavior even when explicit opt-in is requested.

C72 does not make PLAN/CONFIRM read the activated catalog by default. C72 does not create a live fallback path from PLAN/CONFIRM to the activated production catalog. C72 does not promote A01 and does not use A01 as runtime fallback.

## Candidate hierarchy

```text
PRIMARY=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
PRIMARY_PARENT=C60_B01_H01_PER_REGIME_BRANCH_BUCKET_QUOTA
BACKUP=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
BACKUP_PARENT=C60_A02_C02_DEFENSIVE_GATE_WEAK_REGIME_SURVIVAL
COMPARATOR_ONLY=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
A01_PROMOTED=false
A01_USED_AS_RUNTIME_FALLBACK=false
```

## Risk retention

E02 bad month remains documented: `worst_month=2026-03`, `worst_month_regime=market_down_or_sideways_high_vol`, `bad_month_risk_level=MODERATE`, `bad_month_governance_decision=PASS_WITH_DOCUMENTED_RISK`.

B01 bad month remains documented: `worst_month=2025-10`, `worst_month_regime=market_down_or_sideways_high_vol`, `bad_month_risk_level=MODERATE`, `bad_month_governance_decision=PASS_WITH_DOCUMENTED_RISK`.

Weak regime remains `market_down_or_sideways_high_vol`. Source-bias and shared-core risk remain documented risk, not erased.

## Artifact

```text
storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json
```

Pass means only readiness for `C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION`. It is not production deployment and not PLAN/CONFIRM live rollout.

## Final C72 Operator Evidence — 2026-06-24

Status: `OPERATOR_VALIDATED_ACCEPTED`

Focused C72 PHPUnit passed:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC72"
OK (23 tests, 246 assertions)
```

Full Watchlist PHPUnit passed:

```text
vendor\bin\phpunit tests\Unit\Watchlist
OK (1186 tests, 20424 assertions)
```

Runtime command completed with controlled opt-in enabled:

```text
COMMAND=php artisan watchlist:backtest-c72-controlled-opt-in-runtime-bridge-validation --controlled-opt-in --overwrite --progress
STATUS=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP
REASON_CODE=C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED_PRIMARY_AND_BACKUP
ARTIFACT_PATH=storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json
ARTIFACT_HASH=df3ee58a47572900d42b91d8348f0d6ea9ad1965
ARTIFACT_FILE_SHA1=1ADF2C81797140A7A756B7A4EB02815AF1CBE75E
```

Locked C71 source evidence remained valid:

```text
EXPECTED_C71_HASH=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f
ACTUAL_C71_HASH=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f
C71_HASH_MATCH=true
EXPECTED_C71_FILE_SHA1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB
ACTUAL_C71_FILE_SHA1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB
C71_FILE_SHA1_MATCH=true
C71_SOURCE_LINEAGE_CHECKED=true
C71_SOURCE_LINEAGE_MATCH=true
```

Final C72 gate results:

```text
DATABASE_DICTIONARY_RULE_COMPLIED=true
DICTIONARY_MISSING_COVERAGE_DETECTED=false
C71_LOCK_VALIDATION_COMPLETED=true
C72_READINESS_NESTED_PATH_VALIDATED=true
TOP_LEVEL_ALIAS_USED_FOR_C71_SOURCE_VALIDATION=false
LINEAGE_VALIDATION_COMPLETED=true
LINEAGE_SEQUENCE=C71 -> C70 -> C69 -> C68 -> C67 -> C66 -> C65 -> C64 -> C63 -> C62 -> C61 -> C60
CANDIDATE_SCOPE_FREEZE_COMPLETED=true
PRIMARY_CANDIDATE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_CANDIDATE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
COMPARATOR_ONLY_CANDIDATE=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
A01_PROMOTED=false
A01_USED_AS_RUNTIME_FALLBACK=false
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_EXECUTED=true
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_ALLOWED=true
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASS=true
CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASS_SCOPE=PRIMARY_AND_BACKUP
PRIMARY_BRIDGE_READINESS_PASS=true
BACKUP_BRIDGE_READINESS_PASS=true
DEFAULT_OFF_FEATURE_FLAG_PASS=true
EXPLICIT_OPT_IN_REQUIRED_PASS=true
KILL_SWITCH_RUNTIME_BRIDGE_VALIDATION_PASS=true
CONTROLLED_BRIDGE_READ_EXECUTION_PROOF_PASS=true
PLAN_CONFIRM_OUTPUT_NON_MUTATION_PASS=true
BASELINE_PLAN_CONFIRM_HASH_UNCHANGED=true
FALLBACK_BEHAVIOR_RUNTIME_BRIDGE_VALIDATION_PASS=true
DOCUMENTATION_GOVERNANCE_PASS=true
PRODUCTION_MUTATION_SAFETY_PASS=true
```

PLAN/CONFIRM baseline remained unchanged:

```text
BASELINE_PLAN_CONFIRM_HASH_BEFORE=3bd10d6bd89e7d4353eb02306022bfc17f2a9493
BASELINE_PLAN_CONFIRM_HASH_AFTER=3bd10d6bd89e7d4353eb02306022bfc17f2a9493
BASELINE_PLAN_CONFIRM_HASH_UNCHANGED=true
PLAN_CONFIRM_CURRENT_BEHAVIOR_PRESERVED=true
PLAN_CONFIRM_LIVE_OUTPUT_CHANGED=false
PLAN_CONFIRM_PARALLEL_RUN_DEFERRED_TO_C73_OR_LATER=true
```

Safety boundary remained locked:

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
SELECTION_CHANGED_AFTER_C72=false
PARAMETER_CHANGED_AFTER_C72=false
NEW_CANDIDATE_CREATED=false
OOS_REUSED_FOR_RANKING=false
LATEST_SHORTCUT_USED=false
MAX_DATE_SHORTCUT_USED=false
FUTURE_LOOKUP_DETECTED=false
RETURN_FIELDS_USED_FOR_SELECTION=false
DESTRUCTIVE_MIGRATION_DETECTED=false
IRREVERSIBLE_MUTATION_DETECTED=false
```

C73 readiness decision:

```text
C73_VALIDATION_COMPLETED=true
C73_CANDIDATE_READY_FOR_C73_COUNT=2
C73_CANDIDATE_CODES=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE,C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
C73_RECOMMENDATION=C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION
C73_DIAGNOSTIC_CONCLUSION=READY_FOR_C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION
```

Operator inspection note: the local `Select-Object` scorecard inspection emitted a duplicate-property warning because `controlled_opt_in_runtime_bridge_validation_pass` was selected twice in that inspection command. This is not a C72 runtime blocker and does not invalidate the artifact. The operator command documentation keeps the field only once.

Final C72 conclusion: C72 is accepted as controlled opt-in runtime bridge validation for E02 primary and B01 backup. A01 remains comparator-only. C72 did not deploy live production, did not mutate PLAN/CONFIRM, did not make PLAN/CONFIRM read the activated catalog by default, and did not change PLAN/CONFIRM output. The only valid next step is `C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION`.

