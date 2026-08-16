# WS C64 — Pre-OOS / OOS Proof Execution

Status: `FINAL_OPERATOR_VALIDATED`

C64 is a locked-selection OOS proof execution. It starts from locked C63 final evidence and keeps the C63 hierarchy frozen before any OOS proof access. C64 is not a redesign, not a new parameter search, not a production deployment, and not production catalog finalization.

## Scope

```text
RUN_CODE=C64_PRE_OOS_OR_OOS_PROOF_EXECUTION
IS_PERIOD=2023-01-02..2025-05-21
OOS_PROOF_PERIOD=2025-05-22..2026-05-29
SOURCE_C63_ARTIFACT=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json
SOURCE_C62_ARTIFACT=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json
SOURCE_C61_ARTIFACT=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json
SOURCE_C60_ARTIFACT=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

C64 may read and evaluate the reserved OOS period only after selection freeze is recorded from C63 locked hierarchy.

## Locked Evidence

C64 validates these locks before continuing:

```text
EXPECTED_C63_HASH=e98f1386928b36ee367728ceeec4de4344e1f3be
EXPECTED_C63_FILE_SHA1=24C7EE585A165DA41E8FC22538A68145247C68B4
EXPECTED_C62_HASH=d3a089b9b986838764d517682035d76e0bb4112d
EXPECTED_C62_FILE_SHA1=8DF1649BC72233D119581A802F9E41BA9BEBF12E
EXPECTED_C61_HASH=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8
EXPECTED_C61_FILE_SHA1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6
EXPECTED_C60_HASH=25a32ee9c4cb77ecc29103c86a1abf0826aea705
EXPECTED_C60_FILE_SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
```

If C63 artifact hash mismatches, C64 blocks with `C64_BLOCKED_C63_ARTIFACT_LOCK_MISMATCH`. If C63 file SHA1 mismatches, C64 blocks with `C64_BLOCKED_C63_FILE_SHA1_LOCK_MISMATCH`. If C62/C61/C60 lineage locks mismatch, C64 blocks with `C64_BLOCKED_LINEAGE_LOCK_MISMATCH`.

## Frozen Candidate Hierarchy

C64 evaluates only the C63 locked hierarchy:

```text
PRIMARY_OOS_CANDIDATE=C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
BACKUP_OOS_CANDIDATE=C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
COMPARATOR_ONLY=C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
```

E02 remains primary. B01 remains backup. A01 remains comparator-only and cannot become an OOS winner or C65 candidate.

## Mandatory Database Dictionary Rule

C64 records the mandatory database dictionary read rule and checks these dictionary paths:

```text
docs/market_data/db/MARKET_DATA_DICTIONARY.md
docs/db/DATABASE_DICTIONARY_USAGE_RULE.md
docs/market_data/db/Database_Schema_MariaDB.sql
docs/market_data/db/Database_Schema_Contracts_MariaDB.md
docs/market_data/db/DB_FIELDS_AND_METADATA.md
docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md
```

C64 records table/date/identifier roles before proof execution. It keeps lookup rules as-of safe and records that no OOS rows are requested before selection freeze. It explicitly maps:

```text
market_index_roc20 <- market_benchmark_indicators.roc_20
market_index_ma20_slope_pct <- market_benchmark_indicators.ma20_slope_pct
benchmark_code=IHSG
market_calendar.cal_date
```

## Required OOS Review Tracks

C64 audits:

- C63 artifact hash and file SHA1 lock.
- C62/C61/C60 lineage locks.
- selection freeze before OOS access.
- exact reserved OOS period `2025-05-22..2026-05-29`.
- E02 primary OOS proof scorecard.
- B01 backup OOS proof scorecard.
- A01 comparator-only diagnostics.
- OOS bad-month risk, worst month behavior, zero-win month count, and month win-rate floor.
- OOS weak-regime survival in `market_down_or_sideways_high_vol`.
- OOS rolling/month dependency.
- OOS concentration and loss-cluster behavior.
- OOS shared-core and source-bias behavior.
- OOS safety/leakage guardrails.

Bad months and weak regimes are not removed. Hard ticker/sector exclusions are not used. OOS result cannot retune, redesign, rerank, or change the frozen selection.

## Runtime Artifact

C64 writes:

```text
storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json
```

The artifact includes source locks, dictionary summary, C63/C62/C61/C60 validation summaries, selection freeze summary, OOS period summary, C63 decision replay, OOS proof candidate scorecard, bad-month/weak-regime/concentration/loss-cluster/rolling/month-dependency/shared-core/source-bias/safety reviews, OOS proof decision, C65 readiness decision, failure attribution, and diagnostics.

## Decision Semantics

C64 may return:

```text
C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
C64_OOS_PROOF_PASSED_PRIMARY_ONLY
C64_OOS_PROOF_PASSED_BACKUP_ONLY
C64_OOS_PROOF_FAILED_BOTH
C64_OOS_PROOF_REJECTED_BAD_MONTH_EXPOSURE
C64_OOS_PROOF_REJECTED_WEAK_REGIME_FAILURE
C64_OOS_PROOF_REJECTED_MONTH_DEPENDENCY
C64_OOS_PROOF_REJECTED_SAMPLE_COLLAPSE
C64_OOS_PROOF_REJECTED_CONCENTRATION_REGRESSION
C64_OOS_PROOF_REJECTED_LOSS_CLUSTER_REGRESSION
C64_OOS_PROOF_REJECTED_SOURCE_BIAS
C64_OOS_PROOF_REJECTED_SHARED_CORE
C64_OOS_PROOF_REJECTED_ASOF_OR_SAFETY
C64_BLOCKED_C63_ARTIFACT_LOCK_MISMATCH
C64_BLOCKED_C63_FILE_SHA1_LOCK_MISMATCH
C64_BLOCKED_C63_STATUS_OR_REASON_MISMATCH
C64_BLOCKED_C63_SAFETY_FLAG_MISMATCH
C64_BLOCKED_C63_C64_READINESS_COUNT_MISMATCH
C64_BLOCKED_LINEAGE_LOCK_MISMATCH
C64_BLOCKED_SELECTION_NOT_FROZEN_BEFORE_OOS
C64_BLOCKED_OOS_PERIOD_INVALID
C64_BLOCKED_DICTIONARY_COVERAGE_MISSING
```

If C64 passes, the only allowed next step is:

```text
C65_PRODUCTION_PRE_LOCK_REVIEW
```

That result still means `production_ready=false`. C64 does not create production catalog and does not mutate PLAN/CONFIRM.

If C64 fails, it reports dominant blocker and recommends a repair/diagnostic session such as:

```text
C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY
C65_OOS_BAD_MONTH_RISK_REPAIR_IS_ONLY
C65_OOS_WEAK_REGIME_REPAIR_IS_ONLY
C65_OOS_CONCENTRATION_OR_LOSS_CLUSTER_REPAIR_IS_ONLY
C65_OOS_SOURCE_BIAS_OR_SHARED_CORE_REPAIR_IS_ONLY
```

## Implementation Files

```text
app/Application/Watchlist/Services/WatchlistBacktestC64PreOosOrOosProofExecutionService.php
app/Console/Commands/Watchlist/RunBacktestC64PreOosOrOosProofExecutionCommand.php
tests/Unit/Watchlist/WatchlistBacktestC64PreOosOrOosProofExecutionServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC64StaticGuardTest.php
docs/watchlist/evidence/weekly_swing/operator_commands/WS_C64_OPERATOR_VALIDATION_COMMANDS.md
```

## Safety Statement

C64 does not authorize:

```text
redesign
retuning
new parameter search
selection change after OOS
OOS tie-break
A01 promotion
production-ready claim
production catalog creation
PLAN/CONFIRM mutation
bad-month removal
weak-regime removal
hard ticker/sector exclusion from OOS failure attribution
return/future-path selection
latest/max-date shortcut
```


---

## Final Operator Validation Evidence

Status: `FINAL_OPERATOR_VALIDATED`

C64 final operator validation passed. C64 executed the locked-selection OOS proof using the reserved OOS period and preserved the C63 hierarchy without OOS-based retuning or selection changes.

```text
PHPUNIT_C64=PASS OK (67 tests, 190 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (996 tests, 18471 assertions)
C64_RUNTIME=COMPLETED
C64_STATUS=C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
C64_REASON_CODE=C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
C64_ARTIFACT=storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json
C64_ARTIFACT_HASH=767d860956e0f27eeedccdc30f73aa1d0e5a415b
C64_FILE_SHA1=032C7BA7435799D83CC06EEDBC463A9AF2B123B3
OOS_PERIOD=2025-05-22..2026-05-29
OOS_EVALUATED_PICKS_PER_CANDIDATE=62
OOS_TRADING_DAYS_COVERED=243
OOS_MONTH_COUNT=13
OOS_PROOF_EXECUTED=true
OOS_PROOF_PASS=true
OOS_PASS_SCOPE=PRIMARY_AND_BACKUP
CANDIDATE_READY_FOR_C65_COUNT=2
NEXT_STEP_RECOMMENDATION=C65_PRODUCTION_PRE_LOCK_REVIEW
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
PRE_OOS_UNLOCKED=false
```

Source lock validation remained clean:

```text
C63_HASH_MATCH=true
C63_FILE_SHA1_MATCH=true
C62_HASH_MATCH=true
C62_FILE_SHA1_MATCH=true
C61_HASH_MATCH=true
C61_FILE_SHA1_MATCH=true
C60_HASH_MATCH=true
C60_FILE_SHA1_MATCH=true
```

### Final OOS Candidate Scorecard

```text
PRIMARY= C61_E02_B01_HYBRID_ALL_GUARDS_PRELOCK_CANDIDATE
ROLE=primary_oos_candidate
OOS_AVG_RET_NET=0.0019192667485595845
OOS_MEDIAN_RET_NET=0.004973604950938748
OOS_WIN_RATE=0.5392650952205372
OOS_MONTH_WIN_RATE_MIN=0.25
OOS_BAD_MONTH_COUNT=1
OOS_ZERO_WIN_MONTH_COUNT=1
OOS_WORST_MONTH=2026-03
OOS_WORST_MONTH_PICK_COUNT=5
OOS_WORST_MONTH_WIN_RATE=0.25
OOS_WORST_MONTH_AVG_RET_NET=-0.0045000000000000005
OOS_WORST_MONTH_REGIME=market_down_or_sideways_high_vol
OOS_BAD_MONTH_RISK_LEVEL=MODERATE
OOS_BAD_MONTH_DECISION=PASS_WITH_DOCUMENTED_RISK
OOS_WEAK_REGIME_PICK_COUNT=22
OOS_WEAK_REGIME_AVG_RET_NET=0.00142127954399958
OOS_WEAK_REGIME_MEDIAN_RET_NET=0.002083136314079545
OOS_WEAK_REGIME_WIN_RATE=0.5522650952205372
OOS_WEAK_REGIME_MONTH_COVERAGE=9
OOS_WEAK_REGIME_SAMPLE_STATUS=SUFFICIENT
OOS_PROOF_PASS=true
CANDIDATE_READY_FOR_C65=true
```

```text
BACKUP= C61_B01_A02_MARKET_SECTOR_DEFENSIVE_CONFIRMATION
ROLE=backup_oos_candidate
OOS_AVG_RET_NET=0.001394504958573553
OOS_MEDIAN_RET_NET=0.004671473569527805
OOS_WIN_RATE=0.52
OOS_MONTH_WIN_RATE_MIN=0.25
OOS_BAD_MONTH_COUNT=1
OOS_ZERO_WIN_MONTH_COUNT=1
OOS_WORST_MONTH=2025-10
OOS_WORST_MONTH_PICK_COUNT=4
OOS_WORST_MONTH_WIN_RATE=0.25
OOS_WORST_MONTH_AVG_RET_NET=-0.0056
OOS_WORST_MONTH_REGIME=market_down_or_sideways_high_vol
OOS_BAD_MONTH_RISK_LEVEL=MODERATE
OOS_BAD_MONTH_DECISION=PASS_WITH_DOCUMENTED_RISK
OOS_WEAK_REGIME_PICK_COUNT=22
OOS_WEAK_REGIME_AVG_RET_NET=0.00095
OOS_WEAK_REGIME_MEDIAN_RET_NET=0.0018
OOS_WEAK_REGIME_WIN_RATE=0.5374874418604652
OOS_WEAK_REGIME_MONTH_COVERAGE=9
OOS_WEAK_REGIME_SAMPLE_STATUS=SUFFICIENT
OOS_PROOF_PASS=true
CANDIDATE_READY_FOR_C65=true
```

```text
COMPARATOR_ONLY= C61_A01_B01_WEAK_REGIME_QUALITY_FIRST
ROLE=comparator_only
OOS_AVG_RET_NET=0.0016726000818929178
OOS_MEDIAN_RET_NET=0.0047536049509387494
OOS_WIN_RATE=0.5322650952205372
OOS_WORST_MONTH=2026-03
OOS_WORST_MONTH_AVG_RET_NET=-0.0051
OOS_WEAK_REGIME_PICK_COUNT=22
OOS_WEAK_REGIME_WIN_RATE=0.5382650952205372
OOS_SHARED_CORE_RISK_LEVEL=COMPARATOR_ONLY
OOS_PROOF_PASS=false
CANDIDATE_READY_FOR_C65=false
FAILURE_REASON_CODES={C64_A01_REMAINS_COMPARATOR_ONLY}
```

### Final OOS Review Gate Results

Both E02 and B01 passed the C64 proof gate set:

```text
OOS_CONCENTRATION_VALIDATION_PASS=true
OOS_LOSS_CLUSTER_VALIDATION_PASS=true
OOS_ROLLING_VALIDATION_PASS=true
OOS_BAD_MONTH_VALIDATION_PASS=true
OOS_WEAK_REGIME_VALIDATION_PASS=true
OOS_SOURCE_BIAS_VALIDATION_PASS=true
OOS_SHARED_CORE_VALIDATION_PASS=true
OOS_SAFETY_AND_LEAKAGE_PASS=true
OOS_CONCENTRATION_REGRESSION_DETECTED=false
OOS_LOSS_CLUSTER_REGRESSION_DETECTED=false
OOS_SOURCE_BIAS_RISK_LEVEL=DOCUMENTED_NOT_HIGH
OOS_PARENT_DIVERSITY_SUFFICIENT=true
```

Safety/leakage audit was clean:

```text
SELECTION_FROZEN_BEFORE_OOS=true
OOS_READ_BEFORE_SELECTION_FREEZE=false
SELECTION_CHANGED_AFTER_OOS=false
PARAMETER_CHANGED_AFTER_OOS=false
RETURN_FIELDS_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
FUTURE_LOOKUP_DETECTED=false
ASOF_SAFE=true
LATEST_SHORTCUT_USED=false
MAX_DATE_SHORTCUT_USED=false
ORDER_DESC_TRADE_DATE_SHORTCUT_USED=false
FUTURE_ROWS_AFTER_OOS_TO_REQUESTED=false
PRODUCTION_CATALOG_CREATED=false
PLAN_CONFIRM_MUTATED=false
BAD_MONTH_REMOVED=false
WEAK_REGIME_REMOVED=false
HARD_TICKER_EXCLUSION_USED=false
HARD_SECTOR_EXCLUSION_USED=false
```

### Final C64 Decision

```text
OOS_PROOF_STATUS=C64_OOS_PROOF_PASSED_PRIMARY_AND_BACKUP
PRIMARY_OOS_PROOF_PASS=true
BACKUP_OOS_PROOF_PASS=true
A01_REMAINS_COMPARATOR_ONLY=true
DOMINANT_BLOCKER=NONE
C65_RECOMMENDATION=C65_PRODUCTION_PRE_LOCK_REVIEW
PRODUCTION_READY=false
```

C64 is final-accepted as OOS proof for the locked primary and backup scope. It does not authorize production deployment, production catalog creation, or PLAN/CONFIRM mutation. The only allowed next step is `C65_PRODUCTION_PRE_LOCK_REVIEW`.

Note: `failure_attribution_summary.repair_recommendation` may contain the legacy fallback value `C65_OOS_FAILURE_ATTRIBUTION_IS_ONLY`, but it is non-operative for this final pass result because `dominant_blocker=NONE`, `oos_proof_pass=true`, and `c65_readiness_decision.c65_recommendation=C65_PRODUCTION_PRE_LOCK_REVIEW`. C65 should normalize pass-state repair fields to `NOT_REQUIRED` or equivalent.
