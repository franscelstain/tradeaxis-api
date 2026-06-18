# WS C22 Shadow Gap Bridge C24 Design Note

C24 is the diagnostic bridge after C23. C23 produced a realizable non-lookahead first-profit-capture candidate, but it still did not fully match the C22 first-profitable-close shadow benchmark. C24 exists to explain that remaining gap, not to tune production behavior.

## Context carried forward

C19 found quality signal but no sample-qualified catalog candidate. C20 showed regime/date gates are not enough. C21 kept exit behavior as a suspect. C22 found a shadow exit-capture direction. C23 converted that direction into non-lookahead rule candidates and found `C23_R09` as the best average/win-rate candidate, while still failing the C22 shadow gap.

Critical evidence carried into C24:

```text
C22_ALL_PARAM_ARTIFACT_HASH=4e939d091a03ed49bbf460c0424ff1a018f98e72
C23_ALL_PARAM_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND=true
C23_C22_SHADOW_GAP_ACCEPTABLE=false
C23_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## C24 objective

C24 compares:

```text
canonical baseline
C23_R09_EXIT_NEXT_OPEN_AFTER_D1_OR_D2_OR_D3_CLOSE_PROFIT_GT_0
C22_S06_FIRST_PROFITABLE_CLOSE_EXIT benchmark
```

The diagnostic answers:

```text
How much of the C22 S06 shadow gap does C23 R09 close?
Which row-level components explain the remaining gap?
Does the remaining gap justify any catalog, OOS, or production step?
```

## Input and output contract

C24 reads:

```text
storage/app/watchlist/backtest/c23-first-profit-capture-rule-diagnostic-all-param.json
```

C24 writes:

```text
storage/app/watchlist/backtest/c24-c22-shadow-gap-bridge-diagnostic-all-param.json
```

C24 must not:

```text
recompute C19 selection
read new future price paths
create or seed a catalog
run OOS
mutate C01-C23
change canonical entry/exit rules
turn C22 S06 into a production rule
```

## Gap component taxonomy

C24 uses three main buckets:

```text
candidate_matches_or_beats_c22
next_open_delay_after_close_signal
no_rule_profit_signal_before_fallback
```

`candidate_matches_or_beats_c22` is a non-gap bucket and must not be chosen as the dominant gap component. The dominant actual gap is selected from rows where C22 beats the C23 candidate.

## Current implementation status

```text
C24_SOURCE_IMPLEMENTED=true
PHPUNIT_C24_FILTER=PASS
C24_COMMAND_REGISTERED=PASS
C24_RUNTIME_VALIDATED=true
C24_ARTIFACT_HASH=feabfbe720d39155a3d741e509cc69cade3ef31c
C24_DECISION_STATUS=C24_C22_SHADOW_GAP_STILL_MATERIAL
C24_DOMINANT_GAP_COMPONENT=no_rule_profit_signal_before_fallback
C24_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

C24 explains the remaining gap and keeps production readiness locked. If continued, the next diagnostic should focus on next-open delay and no-signal fallback cases only, without creating a catalog or running OOS.
