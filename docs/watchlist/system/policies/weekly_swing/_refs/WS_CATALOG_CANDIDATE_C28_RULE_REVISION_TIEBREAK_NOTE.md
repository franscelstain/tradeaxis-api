# WS Catalog Candidate C28 Rule Revision Tiebreak Note

## C170 Superseding Correction

The bucket tiebreak is not execution-eligible. `bucket_code` is derived by comparing C22/R09 results over the evaluated D1-D5 path, so the R09/G21/G16 router cannot be fixed before entry. The original readiness markers below are superseded by the corrected result in this file.

C28 follows C27's raw OHLC validation result and stays IS-only.

## Boundary

```text
scope=IS_ONLY_RULE_REVISION_TIEBREAK_DIAGNOSTIC
catalog_created=false
oos_run=false
production_ready=0
mutates_C01_to_C27=false
```

C28 is not a production catalog and not an OOS proof. It is an IS-only rule revision diagnostic.

## Revised Rule Candidate

C27 showed that original G21 is not stable enough globally because it hurts the `candidate_matches_or_beats_c22` bucket average. C28 defines an explicit bucket tiebreak:

```text
C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY

candidate_matches_or_beats_c22        => raw R09
no_rule_profit_signal_before_fallback => raw G21
next_open_delay_after_close_signal    => raw G16
```

The rule uses C27 raw OHLC exits only. It does not use derived MFE/MAE for execution.

## Result

```text
raw_ohlc_validation_pass=true
candidate_distribution_beats_r09=true
candidate_param_stability_pass=true
candidate_month_stability_pass=true
candidate_bucket_stability_pass=true
lookahead_safety_pass=false
execution_time_route_availability_pass=false
future_derived_route_count=1575
lookahead_violation_count=1575
c28_revised_candidate_ready=false
c29_oos_proof_recommended=false
```

The primary candidate all-param metrics:

```text
avg_ret_net=0.0061941599395967
median_ret_net=0.0058664259927798
p25_ret_net=-0.0065973510332174
win_rate=0.58603174603175
avg_delta_vs_r09=0.0064115930122448
p25_delta_vs_r09=0.014647308567441
param_pass_fail=12/0
month_pass_fail=27/0
bucket_pass_fail=3/0
```

## Next Step

```text
NEXT_STEP=C171_EXECUTABLE_IS_STRATEGY_REMEDIATION
```

C29 must not run for G05. A later candidate may enter OOS only when all routing inputs are available at their documented execution timestamp and every canonical IS/evidence gate passes.
