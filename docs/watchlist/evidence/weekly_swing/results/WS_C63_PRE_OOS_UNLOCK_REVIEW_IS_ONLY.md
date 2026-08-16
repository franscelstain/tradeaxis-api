# WS C63 — Pre-OOS Unlock Review IS-Only

Status: `FINAL_OPERATOR_VALIDATED`

C63 is an IS-only pre-OOS unlock review. It starts from locked C62 evidence and retains C61/C60 lineage locks. It does not run OOS, does not read OOS rows, does not use OOS return for selection/ranking/tie-break, does not unlock OOS proof inside C63 runtime, does not create a production catalog, and does not mutate PLAN/CONFIRM.

## Scope

```text
RUN_CODE=C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY
IS_PERIOD=2023-01-02..2025-05-21
OOS_RESERVED=2025-05-22..2026-05-29
SOURCE_C62_ARTIFACT=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json
SOURCE_C61_ARTIFACT=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json
SOURCE_C60_ARTIFACT=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

## Locked Evidence

C63 validates these locks before continuing:

```text
EXPECTED_C62_HASH=d3a089b9b986838764d517682035d76e0bb4112d
EXPECTED_C62_FILE_SHA1=8DF1649BC72233D119581A802F9E41BA9BEBF12E
EXPECTED_C61_HASH=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8
EXPECTED_C61_FILE_SHA1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6
EXPECTED_C60_HASH=25a32ee9c4cb77ecc29103c86a1abf0826aea705
EXPECTED_C60_FILE_SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
```

If C62 hash or file SHA1 mismatches, C63 blocks with `C63_BLOCKED_C62_ARTIFACT_LOCK_MISMATCH` or `C63_BLOCKED_C62_FILE_SHA1_LOCK_MISMATCH`. If C61/C60 lineage mismatches, C63 blocks with `C63_BLOCKED_LINEAGE_LOCK_MISMATCH`.

C63 also validates C62 readiness from the nested field `c63_readiness_decision`, specifically `candidate_ready_for_c63_count=2` and `c63_recommendation=C63_PRE_OOS_UNLOCK_REVIEW_IS_ONLY`. These fields are not expected as top-level C62 fields.

## Reviewed Candidates

C63 reviews only the C62 hierarchy candidates:

```text
PRIMARY_UNLOCK_CANDIDATE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_UNLOCK_CANDIDATE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
SIBLING_COMPARATOR_ONLY=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
```

E02 remains primary. B01 remains backup parent-diversifier. A01 remains sibling comparator only because shared-parent/shared-core risk is still documented. No new candidate is created. No replay comparator is promoted.

## Mandatory Database Dictionary Rule

C63 records the mandatory database dictionary read rule and checks these dictionary paths:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/market_data/db/Database_Schema_MariaDB.sql
docs/market_data/db/Database_Schema_Contracts_MariaDB.md
docs/market_data/db/DB_FIELDS_AND_METADATA.md
docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
```

C63 records table/field mappings for `market_calendar`, `market_benchmark_indicators`, `eod_bars`, `eod_indicators`, `eod_eligibility`, and the watchlist backtest artifact read model. C63 keeps lookups as-of safe and records zero OOS rows requested.

## Required Audits

C63 audits:

- C62 artifact hash and file SHA1 lock.
- C61 and C60 lineage locks.
- C62 decision hierarchy.
- E02 as primary unlock candidate.
- B01 as backup parent-diversifier.
- A01 as sibling comparator only.
- `month_win_rate_min=0`.
- E02 worst month `2024-08`.
- B01 worst month `2024-11`.
- documented bad-month unlock risk.
- weak-regime unlock readiness in `market_down_or_sideways_high_vol`.
- regime robustness and weak-regime sample integrity.
- concentration and loss-cluster unlock readiness.
- rolling and LOO unlock readiness.
- material selection difference, shared-core, and source bias.
- as-of safety and OOS leakage boundaries.

Bad months and weak regimes are not removed. Ticker/sector hard exclusions are not used.

## Runtime Artifact

C63 writes:

```text
storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json
```

The artifact includes source locks, dictionary summary, C62/C61/C60 validation summaries, C62 decision replay, unlock candidate scorecard, unlock hierarchy, bad-month review, weak-regime review, concentration review, loss-cluster review, rolling and LOO review, shared-core and source-bias review, safety/leakage audit, pre-OOS unlock decision, C64 readiness decision, and diagnostics.

## Decision Semantics

C63 may return:

```text
C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_ONLY
C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_MONTH_DEPENDENCY
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_BAD_MONTH_EXPOSURE
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_WEAK_REGIME_RISK
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SAMPLE_COLLAPSE
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SOURCE_BIAS
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_SHARED_CORE
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_CONCENTRATION_REGRESSION
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_LOSS_CLUSTER_REGRESSION
C63_PRE_OOS_UNLOCK_REVIEW_REJECTED_ASOF_OR_OOS_SAFETY
C63_PRE_OOS_UNLOCK_REVIEW_BLOCKED_LOCK_MISMATCH
```

If C63 approves unlock, the result is only:

```text
ready for C64 pre-OOS/OOS proof execution review
```

It is not production-ready. It is not OOS-proven. It does not mutate production catalog. Runtime flags remain:

```text
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

If C63 rejects unlock, C63 reports the dominant blocker and recommends IS-only continuation such as bad-month risk repair, weak-regime unlock repair, source-bias reduction, shared-core reduction, or concentration/loss-cluster repair.

## Implementation Files

```text
app/Application/Watchlist/Services/WatchlistBacktestC63PreOosUnlockReviewIsOnlyService.php
app/Console/Commands/Watchlist/RunBacktestC63PreOosUnlockReviewIsOnlyCommand.php
tests/Unit/Watchlist/WatchlistBacktestC63PreOosUnlockReviewIsOnlyServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC63StaticGuardTest.php
docs/watchlist/evidence/weekly_swing/operator_commands/WS_C63_OPERATOR_VALIDATION_COMMANDS.md
```

## Safety Statement

C63 does not authorize:

```text
OOS proof
pre-OOS execution inside C63
production catalog
PLAN/CONFIRM mutation
return/future-path/OOS-return selection
OOS tie-break
bad-month removal
weak-regime removal
ticker/sector hard exclusion from failure attribution
A01 promotion equal to E02 while shared-parent/shared-core risk remains
```


---

## Final Operator Validation Evidence

Status: `FINAL_OPERATOR_VALIDATED`

Final C63 operator validation completed with focused PHPUnit, full Watchlist PHPUnit, runtime artifact generation, source lock validation, hierarchy validation, and safety/leakage validation.

```text
PHPUNIT_C63=PASS OK (29 tests, 183 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (929 tests, 18281 assertions)
C63_RUNTIME=COMPLETED
C63_STATUS=C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP
C63_REASON_CODE=C63_PRE_OOS_UNLOCK_REVIEW_APPROVED_PRIMARY_AND_BACKUP
C63_ARTIFACT_PATH=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json
C63_ARTIFACT_HASH=e98f1386928b36ee367728ceeec4de4344e1f3be
C63_FILE_SHA1=24C7EE585A165DA41E8FC22538A68145247C68B4
NEXT_STEP_RECOMMENDATION=C64_PRE_OOS_OR_OOS_PROOF_EXECUTION
UNLOCK_SCOPE=PRIMARY_AND_BACKUP_RECOMMENDED_FOR_C64_REVIEW
```

Source and lineage locks matched:

```text
C62_ARTIFACT_HASH_MATCH=true
C62_FILE_SHA1_MATCH=true
C61_ARTIFACT_HASH_MATCH=true
C61_FILE_SHA1_MATCH=true
C60_ARTIFACT_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
```

Runtime safety flags remained locked false:

```text
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

Safety/leakage audit remained clean:

```text
RETURN_FIELDS_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_RETURN_USED_FOR_SELECTION=false
OOS_ROWS_REQUESTED=0
FUTURE_LOOKUP_DETECTED=false
ASOF_SAFE=true
LATEST_SHORTCUT_USED=false
MAX_DATE_SHORTCUT_USED=false
ORDER_DESC_TRADE_DATE_SHORTCUT_USED=false
OOS_DATE_QUERY_DETECTED=false
PRODUCTION_CATALOG_CREATED=false
PLAN_CONFIRM_MUTATED=false
BAD_MONTH_REMOVED=false
WEAK_REGIME_REMOVED=false
HARD_TICKER_EXCLUSION_USED=false
HARD_SECTOR_EXCLUSION_USED=false
SAFETY_AND_LEAKAGE_UNLOCK_PASS=true
```

Final unlock hierarchy:

```text
PRIMARY_UNLOCK_CANDIDATE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_UNLOCK_CANDIDATE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
COMPARATOR_ONLY=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
REJECTED_CANDIDATES=[]
A01_PROMOTED_EQUAL_TO_E02=false
NEW_CANDIDATE_CREATED=false
SELECTION_RULE_CHANGED=false
```

C64 readiness decision:

```text
CANDIDATE_READY_FOR_C64_COUNT=2
C64_RECOMMENDATION=C64_PRE_OOS_OR_OOS_PROOF_EXECUTION
C64_CANDIDATES=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE,C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
```

Final candidate outcomes:

```text
E02_ROLE=primary_unlock_candidate
E02_CANDIDATE_READY_FOR_C64=true
E02_PRE_OOS_UNLOCK_REVIEW_PASS=true
E02_BAD_MONTH_RISK_LEVEL=MODERATE
E02_BAD_MONTH_UNLOCK_DECISION=APPROVE_WITH_DOCUMENTED_RISK
E02_WORST_MONTH=2024-08
E02_WORST_MONTH_WIN_RATE=0
E02_WORST_MONTH_AVG_RET_NET=-0.0041
E02_WORST_MONTH_REGIME=market_down_or_sideways_high_vol
E02_WEAK_REGIME_UNLOCK_READY=true
E02_ROLLING_UNLOCK_READY=true
E02_LOO_UNLOCK_READY=true
E02_CONCENTRATION_UNLOCK_READY=true
E02_LOSS_CLUSTER_UNLOCK_READY=true
E02_SHARED_CORE_UNLOCK_READY=true
E02_SOURCE_BIAS_UNLOCK_READY=true

B01_ROLE=backup_unlock_candidate
B01_CANDIDATE_READY_FOR_C64=true
B01_PRE_OOS_UNLOCK_REVIEW_PASS=true
B01_BAD_MONTH_RISK_LEVEL=MODERATE
B01_BAD_MONTH_UNLOCK_DECISION=APPROVE_WITH_DOCUMENTED_RISK
B01_WORST_MONTH=2024-11
B01_WORST_MONTH_WIN_RATE=0
B01_WORST_MONTH_AVG_RET_NET=-0.0052
B01_WORST_MONTH_REGIME=market_down_or_sideways_high_vol
B01_WEAK_REGIME_UNLOCK_READY=true
B01_ROLLING_UNLOCK_READY=true
B01_LOO_UNLOCK_READY=true
B01_CONCENTRATION_UNLOCK_READY=true
B01_LOSS_CLUSTER_UNLOCK_READY=true
B01_SHARED_CORE_UNLOCK_READY=true
B01_SOURCE_BIAS_UNLOCK_READY=true

A01_ROLE=comparator_only
A01_CANDIDATE_READY_FOR_C64=false
A01_PRE_OOS_UNLOCK_REVIEW_PASS=false
A01_SHARED_CORE_UNLOCK_READY=false
A01_PARENT_DIVERSITY_SUFFICIENT=false
A01_FAILURE_REASON_CODES=C63_A01_REMAINS_SIBLING_COMPARATOR_ONLY
```

Final weak-regime review:

```text
WEAKEST_REGIME=market_down_or_sideways_high_vol
E02_WEAK_REGIME_PICK_COUNT=28
E02_WEAK_REGIME_MONTH_COVERAGE=14
E02_WEAK_REGIME_AVG_RET_NET=0.0017212795439995802
E02_WEAK_REGIME_MEDIAN_RET_NET=0.002413136314079545
E02_WEAK_REGIME_WIN_RATE=0.5692650952205373
E02_SAMPLE_COLLAPSE_DETECTED=false
E02_WEAK_REGIME_IMPROVED_VS_C60=true
E02_WEAK_REGIME_IMPROVED_VS_C59=true
E02_WEAK_REGIME_IMPROVED_VS_C58=true

B01_WEAK_REGIME_PICK_COUNT=27
B01_WEAK_REGIME_MONTH_COVERAGE=14
B01_WEAK_REGIME_AVG_RET_NET=0.001216638572845102
B01_WEAK_REGIME_MEDIAN_RET_NET=0.002117325316364164
B01_WEAK_REGIME_WIN_RATE=0.5544874418604652
B01_SAMPLE_COLLAPSE_DETECTED=false
B01_WEAK_REGIME_IMPROVED_VS_C60=true
B01_WEAK_REGIME_IMPROVED_VS_C59=true
B01_WEAK_REGIME_IMPROVED_VS_C58=true
```

Concentration and loss-cluster review passed for the unlock candidates:

```text
E02_MAX_TICKER_SHARE=0.075
E02_MAX_SECTOR_SHARE=0.145
E02_MAX_BUCKET_SHARE=0.44
E02_MAX_BRANCH_SHARE=0.43
E02_LOSS_CLUSTER_SHARE=0.079
E02_LOSS_CLUSTER_UNLOCK_READY=true

B01_MAX_TICKER_SHARE=0.075
B01_MAX_SECTOR_SHARE=0.145
B01_MAX_BUCKET_SHARE=0.44
B01_MAX_BRANCH_SHARE=0.44
B01_LOSS_CLUSTER_SHARE=0.079
B01_LOSS_CLUSTER_UNLOCK_READY=true
```

Rolling and LOO review passed:

```text
ROLLING_UNLOCK_READY_CANDIDATE_COUNT=3
ROLLING_WORST_WINDOW=IS_ROLLING_WINDOW_WITH_ZERO_WIN_MONTH_INCLUDED
LOO_UNLOCK_READY_CANDIDATE_COUNT=3
SINGLE_MONTH_DEPENDENCY_DETECTED=false
E02_LOO_STABILITY_RATE=1
B01_LOO_STABILITY_RATE=0.9629629629629629
```

Shared-core and source-bias interpretation:

```text
E02_A01_SAME_PARENT_DETECTED=true
E02_A01_NOT_PROMOTED_EQUALLY=true
A01_REMAINS_COMPARATOR_ONLY=true
E02_VS_B01_PARENT_DIVERSITY_DETECTED=true
SHARED_CORE_UNLOCK_READY_CANDIDATE_COUNT=2
PARENT_DIVERSITY_SUFFICIENT=true
SOURCE_BIAS_DETECTED=true
SOURCE_BIAS_RISK_LEVEL=DOCUMENTED_NOT_HIGH
SOURCE_BIAS_UNLOCK_READY=true
```

Final conclusion:

C63 is accepted as an operator-validated IS-only pre-OOS unlock review. It approves only a recommendation into C64 for primary+backup OOS/pre-OOS proof execution review. It does not prove OOS performance, does not unlock production, does not unlock OOS proof inside C63, and does not authorize PLAN/CONFIRM mutation. C64 must keep selection locked from C63: E02 primary, B01 backup, A01 comparator-only. Bad-month risk remains documented and must be explicitly inspected in C64.
