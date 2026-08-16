# WS C170 Weekly Swing Canonical IS Strategy and Real OOS Proof Remediation

## Outcome

C170 did not lock or promote a strategy. It corrected two implementation gaps that made the planned C170 OOS step unsafe:

1. C28 G05 routes each trade to R09, G21, or G16 using a bucket derived from the evaluated D1-D5 path. The route is not available at execution time.
2. The official `picks`, `universe`, and `cutoffs` tables are empty and lack `eval_id`, so they cannot prove the exact evaluation represented by an aggregate IS/OOS row.

The runtime now rejects C28 G05 before an OOS read, and paramset promotion now requires all three support-evidence tables to be version-bound to the exact IS `eval_id`.

```text
C170_STATUS=IMPLEMENTED_FAIL_CLOSED
C170_CANONICAL_IS_CANDIDATE_LOCKED=0
C170_OOS_RUNTIME_INVOKED=0
C170_OFFICIAL_OOS_ROW_INSERTED=0
C170_PARAMSET_PROMOTED=0
C170_ACTIVE_PARAMSET_COUNT=0
C170_PLAN_RUN_COUNT=0
C170_PRODUCTION_READY=0
```

## Documents Read Before Change

C170 read the C169 owner result, Weekly Swing calibration/schema/coverage/universe/metrics/OOS/manifest/procedure contracts, and the C15-C29 diagnostic chain. The implementation follows these locked rules:

```text
IS before OOS
no best-of-failed
route inputs available at execution time
no future-path selection
official six-table evidence manifest
exact immutable candidate binding
no promotion without persisted passing IS and OOS proof
```

## Reproduced Real State

Live official table state before C170:

```text
watchlist_bt_param_grid=156
watchlist_bt_eval=186
watchlist_bt_picks_ws=0
watchlist_bt_universe_ws=0
watchlist_bt_cutoffs_ws=0
watchlist_bt_oos_eval_ws=0
```

Identity-column state:

```text
watchlist_bt_eval.eval_id=present
watchlist_bt_picks_ws.eval_id=absent
watchlist_bt_universe_ws.eval_id=absent
watchlist_bt_cutoffs_ws.eval_id=absent
```

All persisted canonical catalogs through C17 have zero IS rows passing every canonical gate. The official OOS table is still empty.

## C28 G05 Canonical-Gate Recheck

The historical C28 aggregate was not sufficient to claim a canonical IS pass. Its `param_consistency_pass` and `month_stability_pass` compare candidate performance with R09; they are not the owner-required absolute gates.

Recalculation over the 12 C17 param IDs produced:

```text
per_param_picks_count_range=128..133
per_param_avg_ret_net=positive_for_12_of_12
per_param_median_ret_net=positive_for_12_of_12
per_param_p25_ret_net_above_minus_0_03=12_of_12
per_param_month_win_rate_min_range=0.00..0.20
per_param_month_avg_ret_net_min_range=-0.0245121..-0.0144932
absolute_month_win_floor_pass=0_of_12
absolute_month_avg_floor_pass=0_of_12
canonical_is_pass=0_of_12
```

The more fundamental failure is execution-time route availability:

```text
candidate_profile=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
candidate_matches_or_beats_c22=>R09
no_rule_profit_signal_before_fallback=>G21
next_open_delay_after_close_signal=>G16
bucket_source=C22/R09 D1-D5 evaluated path comparison
route_available_before_entry=false
future_path_price_used_for_rule_routing=true
```

An exit can be individually lookahead-safe while the router choosing that exit is not. C28 previously checked only the selected exit timing and missed the router leak.

## Implemented Runtime Corrections

### C28

`WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService` now:

- marks every future-derived bucket route explicitly;
- sets `route_decision_available_before_entry=false`;
- sets `future_path_price_used_for_rule_routing=true`;
- records `WS_BT_C28_FUTURE_DERIVED_BUCKET_ROUTE`;
- excludes the candidate from OOS readiness.

Real revalidation:

```text
artifact_path=storage/app/watchlist/backtest/c170-c28-g05-execution-route-revalidation.json
artifact_hash=1ef90eea6d196db0584ca8ff8da77064a8405e89
evaluated_picks_count=1575
lookahead_violation_count=1575
future_derived_route_count=1575
execution_time_route_availability_pass=0
c28_revised_candidate_ready=0
c29_oos_proof_recommended=0
```

### C29

`WatchlistBacktestC29OosProofService` now validates route availability before building OOS rows or reading OOS prices. Missing route-availability metadata also fails closed.

Real guard execution:

```text
artifact_path=storage/app/watchlist/backtest/c170-c29-future-route-blocked.json
artifact_hash=55cda589a69a204078a631ffe74a8f60b15e080d
status=C29_BLOCKED_INVALID_C28_SOURCE
reason_code=WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN
c28_hash_match=1
execution_route_pass=0
oos_runtime_invoked=0
production_ready=0
```

### Promotion

`WeeklySwingParamsetPromotionService` now requires:

```text
watchlist_bt_picks_ws.eval_id
watchlist_bt_universe_ws.eval_id
watchlist_bt_cutoffs_ws.eval_id
```

It also requires the exact `eval_id` to have:

```text
picks_count == watchlist_bt_eval.picks_count
universe evidence present
cutoff evidence present
```

Current production schema therefore remains fail-closed with:

```text
WS_PARAMSET_PROMOTION_OFFICIAL_EVIDENCE_SCHEMA_UNVERSIONED
```

This is additional to the already-empty official OOS table. No database evidence was invented or backfilled from diagnostic JSON.

## Validation

```text
PHP_LINT_C170_FILES=OK (12 files)
PHPUNIT_C170_FOCUSED=OK (42 tests, 309 assertions)
PHPUNIT_WATCHLIST_FULL=OK (7066 tests, 47680 assertions)
LOCKED_CONTRACT_DOCUMENTS_UNCHANGED=1
GIT_DIFF_CHECK=PASS
```

## Historical Record Corrections

The following prior markers are superseded:

```text
C28_REVISED_CANDIDATE_READY=true
C28_C29_OOS_PROOF_RECOMMENDED=true
C29_OOS_PROOF_VALID=true
```

The historical C29 metrics may remain as diagnostic observations, but they cannot be persisted as official OOS proof because rule routing used future OOS path information.

C30-C64 are downstream of that invalid C29 proof and are therefore diagnostic history only; they cannot repair or replace the missing clean IS-to-OOS chain. C65-C167 runtime/rollout claims remain declaration-only as already corrected by C168.

## Final Status

```text
AUDIT_CHAIN=PASS
REAL_MARKET_DATA_TO_TICKER_RUNTIME=C168_PASS
REAL_PARAMSET_DRAFT=C169_PASS
C28_G05_EXECUTION_ROUTE=INVALID_FUTURE_DERIVED
CANONICAL_IS_PASSING_CANDIDATE=NONE
OFFICIAL_PICKS_COUNT=0
OFFICIAL_UNIVERSE_COUNT=0
OFFICIAL_CUTOFF_COUNT=0
OFFICIAL_OOS_COUNT=0
PARAMSET_ACTIVE_COUNT=0
PLAN_RUN_COUNT=0
RECOMMENDATION_PERSISTENCE_EXECUTED=0
CONFIRM_MUTATED=0
CONTROLLED_ROLLOUT_EXECUTED=0
C167=INCOMPLETE
C30_TO_C64_PRODUCTION_EVIDENCE=INVALID_UPSTREAM_C29
C65_TO_C167_RUNTIME_CLAIMS=DECLARATION_ONLY
WATCHLIST_PRODUCTION_READY=NO
```

## Canonical Next Session

```text
C171_WEEKLY_SWING_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRATEGY_REMEDIATION
```

C171 must:

1. add immutable `eval_id` identity to official picks/universe/cutoffs;
2. persist those rows directly from the canonical runtime, not from a diagnostic declaration;
3. verify counts, coverage, cutoff integrity, and universe equivalence for the exact eval;
4. redesign/recalibrate only on IS using routing inputs available at execution time;
5. stop before OOS unless one immutable candidate passes every canonical IS gate.

OOS, promotion, PLAN, recommendation, CONFIRM, activation, rollout, and observation remain locked.
