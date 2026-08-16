# WS C29 OOS Proof

## C170 Validity Correction (2026-07-24)

The historical C29 JSON is not an admissible OOS proof. Before calculating its OOS metrics, C29 recomputed the future-derived C22/R09 gap bucket from the OOS D1-D5 path and then selected R09, G21, or G16 from that bucket. This makes the rule route unavailable at execution time and leaks OOS path information into rule selection.

The corrected service now blocks before any OOS runtime read:

```text
status=C29_BLOCKED_INVALID_C28_SOURCE
reason_code=WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN
execution_route_pass=false
overall_pass=false
oos_runtime_invoked=false
production_ready=0
```

Correction artifact:

```text
storage/app/watchlist/backtest/c170-c29-future-route-blocked.json
artifact_hash=55cda589a69a204078a631ffe74a8f60b15e080d
```

The previously reported OOS metrics remain historical diagnostic numbers only. They must not be inserted into `watchlist_bt_oos_eval_ws` and cannot authorize promotion.

C29 is an OOS proof for the locked C28 G05 candidate. It is not a tuning session, not catalog promotion, and not production rollout.

## Purpose

C29 proves whether the C28 all-param IS-ready candidate survives the reserved OOS window without retuning or choosing a new profile from OOS results.

## Input C28 Artifact

```text
input_c28_artifact=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
expected_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
candidate_profile_code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
```

C29 locks the C28 artifact by stable artifact hash. The source service also records the raw file SHA1 as diagnostic metadata, but the acceptance lock is the C28 stable artifact hash used by prior C22-C28 artifacts.

If the C28 artifact is missing, unreadable, hash-mismatched, missing the candidate, or contains an unexpected rule mapping, C29 must return `C29_BLOCKED_INVALID_C28_SOURCE` and must not silently fallback to another profile.

## Reserved OOS Window

```text
from=2025-05-22
to=2026-05-29
```

This window is fixed. C29 must reject a shifted, expanded, or narrowed OOS window.

## Candidate Rule Mapping

```text
candidate_matches_or_beats_c22        => RAW_R09
no_rule_profit_signal_before_fallback => RAW_G21
next_open_delay_after_close_signal    => RAW_G16
```

The mapping is fixed before OOS execution. OOS return is evaluation-only and is never used to reselect the profile or create a best-of-OOS binding.

## Canonical Execution Model

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Acceptance Gate

```text
c28_hash_match=true
candidate_found_pass=true
rule_match_pass=true
evaluated_picks_count>=40
avg_ret_net>0
median_ret_net>=0
month_win_rate_min>=0.45
p25_ret_net>=-0.03
lookahead_violation_count=0
production_ready=false
```

Final statuses:

```text
C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY
C29_OOS_PROOF_FAILED
C29_BLOCKED_INVALID_C28_SOURCE
C29_OPERATOR_VALIDATION_REQUIRED
```

Even when OOS gate passes, `production_ready` remains `false/0`.

## Source Components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC29OosProofService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC29OosProofCommand.php

Command signature:
watchlist:backtest-c29-oos-proof

Tests:
tests/Unit/Watchlist/WatchlistBacktestC29OosProofServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC29StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## Runtime Result

C29 was executed by the operator in the supported project environment.

PHPUnit validation:

```text
PHPUNIT_C29=PASS: OK (13 tests, 132 assertions)
FULL_WATCHLIST_PHPUNIT=PASS: OK (448 tests, 10900 assertions)
```

Runtime command result:

```text
status=C29_OOS_PROOF_FAILED
reason_code=C29_OOS_PROOF_FAILED
artifact_path=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
artifact_hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
expected_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
actual_c28_hash=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
c28_hash_match=1
production_ready=0
```

C29 is not blocked. The locked C28 source was valid and hash-matched. C29 failed because OOS proof gates did not pass.

## Artifact Output

Runtime output path:

```text
storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json
```

Artifact status:

```text
C29_ARTIFACT_CREATED=true
C29_ARTIFACT_HASH=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9
```

## OOS Metrics

```text
evaluated_picks_count=128
avg_ret_net=0.004431048028767
median_ret_net=0.0052763819095477
p25_ret_net=-0.0075615188321481
win_rate=0.53125
month_win_rate_min=0
month_avg_ret_net_min=-0.040489877530617
lookahead_violation_count=4
```

## Gate Result

Passed gates:

```text
c28_hash_match=true
candidate_found_pass=true
rule_match_pass=true
evaluated_picks_count>=40
avg_ret_net>0
median_ret_net>=0
p25_ret_net>=-0.03
production_ready=false
```

Failed gates:

```text
month_win_rate_pass=false
lookahead_pass=false
```

Runtime diagnostics:

```text
WS_BT_C29_GATE_FAIL_MONTH_WIN_RATE_PASS: C29 OOS proof gate failed: month_win_rate_pass.
WS_BT_C29_GATE_FAIL_LOOKAHEAD_PASS: C29 OOS proof gate failed: lookahead_pass.
```

## Bad Month Attribution

Bad months with `win_rate=0`:

```text
2025-06: evaluated_picks_count=10, avg_ret_net=-0.04048987753061734, win_rate=0
2025-08: evaluated_picks_count=7, avg_ret_net=-0.0064012506567370005, win_rate=0
2026-03: evaluated_picks_count=4, avg_ret_net=-0.006991928435556013, win_rate=0
```

Bad-month source branch breakdown:

```text
2025-06, G21, no_rule_profit_signal_before_fallback: 10 rows
2025-06, R09: 2 rows
2025-08, G16, next_open_delay_after_close_signal: 3 rows
2025-08, G21, no_rule_profit_signal_before_fallback: 4 rows
2025-08, R09: 2 rows
2026-03, G16, next_open_delay_after_close_signal: 4 rows
```

Representative bad-month rows:

```text
2025-06-24 GWSA param_id=145..150,153..156 selected_source_code=G21 profile_ret_net=-0.04048987753061734 profile_exit_reason=raw_damage_control_no_profit_d2_exit_d3_open
2025-08-13 MDKI param_id=145,149,150 selected_source_code=G21 profile_ret_net=-0.012842696966362937
2025-08-29 EPMT param_id=150 selected_source_code=G21 profile_ret_net=-0.004781038604343624
2026-03-02 BINA param_id=145,149,150,154 selected_source_code=G16 profile_ret_net=-0.006991928435556013
```

## Lookahead / Missing Path Attribution

Rows contributing to the C29 lookahead gate failure:

```text
2025-06-04 MICE param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING lookahead_safe=false
2025-06-04 MICE param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING lookahead_safe=false
2025-08-15 BBSI param_id=151 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING lookahead_safe=false
2025-08-15 BBSI param_id=152 selected_source_code=R09 missing_path_reason_code=WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING lookahead_safe=false
```

Leak flags recorded on invalid path rows:

```text
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

Interpretation: the four rows counted by the C29 lookahead gate are currently evidenced as missing raw OHLC D1-D5 path rows, not as proven future-return/profile-return/MFE-MAE selection leakage. This still fails C29 because the configured `lookahead_pass` gate requires zero unsafe rows, but C30 must split actual lookahead leak from missing-path/non-evaluable rows before making strategy decisions.

## Boundary Preserved

```text
OOS_PROOF_ONLY=true
NO_RETUNE=true
NO_PROFILE_RESELECTION=true
NO_BEST_OF_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C28_MUTATION=true
production_ready=0
```

## Final C29 Status

```text
C29_FINAL_VERDICT=C29_OOS_PROOF_FAILED
production_ready=0
```

C29 does not unlock production readiness and must not be promoted.

## Next Step

```text
NEXT_STEP=C30_OOS_FAILURE_ATTRIBUTION_AND_DATA_COMPLETENESS_DIAGNOSTIC
```

C30 must:

```text
- not tune directly from OOS;
- not reselect a profile from OOS;
- not create best-of-OOS;
- not create or promote a production catalog;
- split actual lookahead leak count from missing-path/non-evaluable row count;
- split clean evaluable metrics from non-evaluable rows;
- explain the bad months 2025-06, 2025-08, and 2026-03;
- attribute branch failures across R09, G21, and G16;
- determine whether C28 G05 failed from data completeness, strategy robustness, or both.
```
