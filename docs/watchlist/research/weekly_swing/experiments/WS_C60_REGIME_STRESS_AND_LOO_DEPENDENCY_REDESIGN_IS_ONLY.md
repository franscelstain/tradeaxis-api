# WS C60 Regime Stress and LOO Dependency Redesign IS-Only

## Session Code

`C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY`

## Scope

C60 is strictly IS-only and uses the locked C59 evidence as source input.

IS window:

- `2023-01-02` to `2025-05-21`

Reserved OOS window:

- `2025-05-22` to `2026-05-29`

C60 does not run OOS proof, does not read OOS rows, does not create a production catalog, does not mutate PLAN/CONFIRM, and does not claim production readiness.

## Locked Input

C60 starts from:

- `storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json`
- expected C59 lock from final prompt/docs: `7ebd6f74bc90ffac358b410244d90b3c7c3c5456`

The uploaded ZIP contains a C59 JSON stable/payload hash of `55c78da17a6e551f30493ce8d1531640ffba4f67`, while the final C59 audit docs record `C59_ARTIFACT_HASH=7ebd6f74bc90ffac358b410244d90b3c7c3c5456`. C60 records both values in `source_artifact_locks` and treats the documented final C59 hash as the operator lock, so the documented C60 command does not false-block on the stale payload hash discrepancy.

## Database Dictionary Rule

Before DB-connected code is changed or audited, C60 requires these dictionary files to be present/readable:

- `docs/market_data/db/MARKET_DATA_DICTIONARY.md`
- `docs/db/DATABASE_DICTIONARY_USAGE_RULE.md`
- `docs/market_data/db/Database_Schema_MariaDB.sql`
- `docs/market_data/db/Database_Schema_Contracts_MariaDB.md`
- `docs/market_data/db/DB_FIELDS_AND_METADATA.md`
- `docs/watchlist/implementation/persistence/WATCHLIST_DB_DICTIONARY.md`

C60 records:

- `dictionary_read_required=true`
- `asof_safe=true`
- `future_lookup_detected=false`
- `oos_rows_requested=0`

Market-index mapping remains locked:

- `market_benchmark_indicators.roc_20` maps to `market_index_roc20`
- `market_benchmark_indicators.ma20_slope_pct` maps to `market_index_ma20_slope_pct`
- `benchmark_code='IHSG'`
- `market_calendar.cal_date` is the calendar date key

## C57/C58/C59 Carry Forward

C57 market-index/regime reconstruction remains solved and is not repeated in C60.

C58/C59 improvements are prerequisites:

- C59 improved concentration validation.
- C59 improved loss-cluster validation.
- C59 started to improve LOO validation.
- C59 still failed regime robustness on every candidate.
- The weakest regime remains `market_down_or_sideways_high_vol`.

C60 therefore focuses on:

- weak-regime return survival
- weak-regime sample floor and month coverage
- regime-aware branch/bucket concentration
- LOO/single-month dependency reduction
- retaining C59 loss-cluster and concentration improvements

## Implemented Files

- `app/Application/Watchlist/Services/WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService.php`
- `app/Console/Commands/Watchlist/RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand.php`
- `tests/Unit/Watchlist/WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestC60StaticGuardTest.php`
- `docs/watchlist/evidence/weekly_swing/operator_commands/WS_C60_OPERATOR_VALIDATION_COMMANDS.md`

`app/Console/Kernel.php` registers the C60 command.

## Candidate Tracks

C60 creates controlled candidates from C59 parents:

- replay comparators from C59 H02 and A01, non-promotable
- Track A: weak-regime survival redesign
- Track B: regime-aware branch/bucket diversity
- Track C: LOO dependency breaker with regime awareness
- Track D: weak-regime sample recovery
- Track E: hybrid C59-improvement retention

Parent pools are read from C59 scorecard and include:

- C59 loss-cluster/concentration improved candidates
- C59 LOO-improved candidates
- C59 branch/bucket strongest candidates
- C59 weak-regime targeted candidates

Replay comparators are never promotable.

## Runtime Artifact

C60 artifact path:

`storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json`

Latest generated artifact evidence from the local service execution:

- `status=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED`
- `reason_code=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS`
- `artifact_hash=4d3ae77bd79b73392cea17b8ca7b0720d950f55b`
- `c59_hash_match=true`
- `production_ready=false`
- `direct_oos_proof_recommended=false`
- `oos_proof_unlocked=false`
- `candidate_ready_for_c61_count=0`
- `concentration_validation_pass_candidate_count=10`
- `regime_aware_concentration_pass_candidate_count=10`
- `loss_cluster_pass_candidate_count=10`
- `loo_validation_pass_candidate_count=7`
- `rolling_validation_pass_candidate_count=4`
- `weak_regime_sample_recovery_pass_candidate_count=9`
- `weak_regime_survival_pass_candidate_count=0`
- `regime_robustness_pass_candidate_count=0`

## Diagnostic Conclusion

C60 improved the structure around weak-regime sample coverage, concentration, and LOO dependency, but it still does not prove weak-regime return survival.

Dominant remaining blocker:

`C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS`

The weak regime is not skipped, bad months are not removed, and no ticker/sector hard exclusion is used. The result is valid but not ready for OOS.

## C61 Recommendation

Recommended next step:

`C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY`

Reason:

The C60 candidate family can retain C59 loss-cluster/concentration improvements and reduce LOO dependency for several candidates, but `market_down_or_sideways_high_vol` return survival remains below the strict gate.

C61 should stay IS-only and rebuild signal quality specifically for the weak regime without using OOS, future return path, bad-month deletion, weak-regime skip, ticker hard exclusion, or sector hard exclusion.

---

## Final Operator Validation Closeout

Status: `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

Final operator validation evidence:

```text
PHPUNIT_C60=PASS OK (13 tests, 165 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (863 tests, 17663 assertions)
C60_RUNTIME=COMPLETED
C60_STATUS=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED
C60_REASON_CODE=C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS
C60_ARTIFACT_HASH=25a32ee9c4cb77ecc29103c86a1abf0826aea705
C60_FILE_SHA1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F
C59_HASH_MATCH=true
EXPECTED_C59_HASH=7ebd6f74bc90ffac358b410244d90b3c7c3c5456
ACTUAL_C59_HASH=7ebd6f74bc90ffac358b410244d90b3c7c3c5456
ACTUAL_C59_STABLE_HASH=55c78da17a6e551f30493ce8d1531640ffba4f67
```

Final C60 gate evidence:

```text
CANDIDATE_READY_FOR_C61_COUNT=0
ROLLING_VALIDATION_PASS_CANDIDATE_COUNT=4
CONCENTRATION_VALIDATION_PASS_CANDIDATE_COUNT=10
REGIME_AWARE_CONCENTRATION_PASS_CANDIDATE_COUNT=10
LOSS_CLUSTER_PASS_CANDIDATE_COUNT=10
LOO_VALIDATION_PASS_CANDIDATE_COUNT=7
REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
WEAK_REGIME_SAMPLE_RECOVERY_PASS_CANDIDATE_COUNT=9
WEAK_REGIME_SURVIVAL_PASS_CANDIDATE_COUNT=0
```

Final C60 safety evidence:

```text
DATABASE_DICTIONARY_READ_REQUIRED=true
DICTIONARY_MISSING_COVERAGE_DETECTED=false
ASOF_SAFE=true
FUTURE_LOOKUP_DETECTED=false
OOS_ROWS_REQUESTED=0
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
TOP_LEVEL_PRODUCTION_READY=false
TOP_LEVEL_DIRECT_OOS_PROOF_RECOMMENDED=false
TOP_LEVEL_OOS_PROOF_UNLOCKED=false
C61_DIRECT_OOS_PROOF_RECOMMENDED=false
C61_OOS_PROOF_UNLOCKED=false
C61_PRODUCTION_READY=false
```

Final interpretation:

- C60 is accepted as an IS-only diagnostic/redesign continuation.
- C60 retained C59 concentration and loss-cluster improvements.
- C60 improved LOO stability from C59 but did not remove all single-month dependency.
- C60 improved weak-regime sample recovery and kept `market_down_or_sideways_high_vol` evaluated; it did not skip the weak regime.
- Regime robustness remains zero-pass because weak-regime return survival remains below gate.
- No candidate is ready for C61/pre-lock review, OOS, pre-OOS, or production.
- C60 does not unlock direct OOS proof and does not create or imply production readiness.

Governed next step:

```text
C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY
```

C61 must remain IS-only and must rebuild signal quality for `market_down_or_sideways_high_vol` without OOS usage, future-return selection, bad-month deletion, weak-regime skipping, or hard ticker/sector exclusion from failure attribution.
