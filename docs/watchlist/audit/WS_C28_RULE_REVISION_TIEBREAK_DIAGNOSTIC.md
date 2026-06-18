# WS C28 Rule Revision Tiebreak Diagnostic

C28 is closed as an IS-only rule revision/tiebreak diagnostic. It reads the C27 raw OHLC artifact, tests explicit bucket-level rule revisions, and does not create a production catalog or run OOS.

## Why C28 Exists

C27 resolved the raw OHLC blocker but found that original raw G21 was not ready because `candidate_matches_or_beats_c22` lost average return versus raw R09. C28 tests explicit tiebreak variants that preserve the good C27 components while avoiding the weak bucket.

Primary revised candidate:

```text
C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY

candidate_matches_or_beats_c22        => raw R09
no_rule_profit_signal_before_fallback => raw G21
next_open_delay_after_close_signal    => raw G16
```

This is an explicit rule candidate. C28 does not bind the best-performing diagnostic profile as production behavior.

## Source Components

```text
Service:
app/Application/Watchlist/Services/WatchlistBacktestC28RuleRevisionTiebreakDiagnosticService.php

Command:
app/Console/Commands/Watchlist/RunBacktestC28RuleRevisionTiebreakDiagnoseCommand.php

Command signature:
watchlist:backtest-c28-rule-revision-tiebreak-diagnose

Tests:
tests/Unit/Watchlist/WatchlistBacktestC28RuleRevisionTiebreakDiagnosticServiceTest.php
tests/Unit/Watchlist/WatchlistBacktestC28StaticGuardTest.php
```

The command is registered in `app/Console/Kernel.php` and is not scheduled.

## Diagnostic Profiles

```text
C28_G00_RAW_R09_BASELINE
C28_G01_RAW_G21_ORIGINAL
C28_G02_RAW_G13_GLOBAL
C28_G03_RAW_G16_GLOBAL
C28_G04_G21_PROBLEM_BUCKETS_ELSE_R09
C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
C28_G06_BUCKET_TIEBREAK_R09_STABLE_G13_NO_SIGNAL_G16_DELAY
C28_G07_G13_GLOBAL_WITH_G21_NO_SIGNAL
C28_G08_G16_GLOBAL_WITH_G21_NO_SIGNAL
C28_G09_PRIMARY_READINESS_SCORE
```

All C28 profiles are IS-only diagnostics.

## Runtime Evidence

PHPUnit:

```text
PHPUNIT_C28=PASS
OK (5 tests, 90 assertions)

FULL_WATCHLIST_PHPUNIT=PASS
OK (435 tests, 10768 assertions)
```

Focused runtime:

```text
C28_FOCUSED_RUNTIME_PASS=true
C28_FOCUSED_ARTIFACT_HASH=94805cfba218fab4baae0a0e25f427f688acb924
C28_FOCUSED_EVALUATED_PICKS=395
C28_FOCUSED_PARAM_PASS_FAIL=3/0
C28_FOCUSED_MONTH_PASS_FAIL=26/1
C28_FOCUSED_BUCKET_PASS_FAIL=3/0
C28_FOCUSED_REVISED_CANDIDATE_READY=false
```

All-param runtime:

```text
C28_ALL_PARAM_RUNTIME_PASS=true
C28_ARTIFACT_PATH=storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
C28_ARTIFACT_HASH=64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd
C28_INPUT_C27_ARTIFACT_HASH=9bae5ed7227615d64765738b1ff83fa8b9232769
C28_EVALUATED_PICKS=1575
C28_RAW_OHLC_VALIDATION_PASS=true
```

All-param profile metrics:

```text
RAW_R09 avg=-0.0217%, median=-0.0500%, p25=-2.1245%, win_rate=47.17%
RAW_G21_ORIGINAL avg=+0.1036%, median=+1.0023%, p25=-0.3889%, win_rate=61.21%
RAW_G13_GLOBAL avg=+0.1458%, median=+0.5825%, p25=-0.0500%, win_rate=73.08%
RAW_G16_GLOBAL avg=+0.1723%, median=+0.9581%, p25=-1.7163%, win_rate=57.59%
C28_G04 avg=+0.5878%, median=+0.6515%, p25=-0.6507%, win_rate=59.43%
C28_G05 avg=+0.6194%, median=+0.5866%, p25=-0.6597%, win_rate=58.60%
C28_G06 avg=+0.5161%, median=+0.5837%, p25=-0.7879%, win_rate=62.29%
C28_G07 avg=+0.2491%, median=+0.5825%, p25=-0.0500%, win_rate=69.40%
C28_G08 avg=+0.4236%, median=+1.0733%, p25=-0.5819%, win_rate=60.32%
```

## Candidate Readiness

Primary C28 all-param candidate:

```text
candidate_profile_code=C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY
candidate_avg_ret_net=0.0061941599395967
candidate_median_ret_net=0.0058664259927798
candidate_p25_ret_net=-0.0065973510332174
candidate_win_rate=0.58603174603175
candidate_avg_delta_vs_r09=0.0064115930122448
candidate_median_delta_vs_r09=0.006366301024022
candidate_p25_delta_vs_r09=0.014647308567441
candidate_param_pass_count=12
candidate_param_fail_count=0
candidate_month_pass_count=27
candidate_month_fail_count=0
candidate_bucket_pass_count=3
candidate_bucket_fail_count=0
lookahead_violation_count=0
```

Readiness:

```text
raw_ohlc_validation_pass=true
derived_mfe_mae_dependency_removed=true
candidate_distribution_beats_r09=true
candidate_param_stability_pass=true
candidate_month_stability_pass=true
candidate_bucket_stability_pass=true
lookahead_safety_pass=true
explicit_bucket_tiebreak_rule=true
c28_revised_candidate_ready=true
c29_oos_proof_recommended=true
```

## Boundary Preserved

```text
IS_ONLY=true
OOS_NOT_RUN=true
production_ready=0
C28_CATALOG_CODE=NOT_CREATED
C28_CATALOG_IMPLEMENTATION_DEFERRED=true
NO_PROMOTION=true
NO_OOS=true
NO_C01_TO_C27_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
NO_C23_REOPEN=true
NO_C24_REOPEN=true
NO_C25_REOPEN=true
NO_C26_REOPEN=true
NO_C27_REOPEN=true
```

Canonical model remains unchanged:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

## Final Decision

```text
C28_DECISION_STATUS=C28_REVISED_RAW_CANDIDATE_READY_FOR_C29_OOS_PROOF
C28_REVISED_CANDIDATE_READY=true
C28_C29_OOS_PROOF_RECOMMENDED=true
C28_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
NEXT_STEP=C29_OOS_PROOF_WITH_C28_ARTIFACT_HASH_LOCK
```
