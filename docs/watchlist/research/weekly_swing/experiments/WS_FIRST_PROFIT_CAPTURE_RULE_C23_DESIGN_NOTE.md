# WS First Profit Capture Rule C23 Design Note

C23 is the diagnostic bridge from C22 shadow evidence to realizable rule candidates. It must not copy `C22_S06_FIRST_PROFITABLE_CLOSE_EXIT` as a production rule because C22 S06 exits on the first profitable close inside the future path. C23 instead tests whether close-confirmed profit can be captured at the next open without lookahead.

## Context carried forward

C19 found quality signal but no sample-qualified catalog candidate. C20 showed regime/date gates are not enough. C21 rejected entry gap as the primary problem and kept exit behavior as a suspect. C22 found an exit-capture direction, strongest around first-profitable-close behavior.

Critical C22 all-param benchmark carried into C23:

```text
C22_ALL_PARAM_ARTIFACT_HASH=4e939d091a03ed49bbf460c0424ff1a018f98e72
C22_EXIT_CAPTURE_SIGNAL_FOUND=true
C22_BEST_SHADOW_DIRECTION=C22_S06_FIRST_PROFITABLE_CLOSE_EXIT
C22_CATALOG_CODE=NOT_CREATED
OOS_NOT_RUN=true
production_ready=0
```

## C23 objective

C23 tests rule candidate profiles only:

```text
exit next open after D1 close profit > threshold
exit next open after D1 or D2 close profit > threshold
exit next open after D1, D2, or D3 close profit > threshold
compress hold to D3 if no profit by D2
compress hold to D4 if no profit by D3
combine first-profit capture with damage control
```

The diagnostic compares every rule candidate against:

```text
canonical baseline
C22_S06 first-profitable-close shadow benchmark
```

## Non-lookahead contract

The only allowed C23 rule signal is an observed close. The rule exit must be the next open after that signal:

```text
D1 close signal -> D2 open exit
D2 close signal -> D3 open exit
D3 close signal -> D4 open exit
rule_signal_day_offset < rule_exit_day_offset
lookahead_safe=true
```

Same-day close exits are forbidden for C23 rule candidates. C22 S06 may appear only as a benchmark measurement field.

## Canonical model boundary

C23 must preserve:

```text
ENTRY=NEXT_OPEN
EXIT=STOP_TP_OR_TIME
HOLD=5
FEE=IDR_FIXED
SLIP=0
GAP=OPEN
PX=IDX_BANDS
```

Rule returns are diagnostic output only:

```text
future_path_price_used_for_selection=false
rule_exit_used_for_selection=false
rule_ret_net_used_for_selection=false
c22_shadow_s06_used_for_selection=false
mfe_mae_used_for_selection=false
```

## Candidate interpretation gates

C23 may mark an IS diagnostic signal when a rule profile improves enough against canonical by median, p25, win-rate, improved-pick rate, or gave-back-profit reduction. A stronger non-lookahead rule candidate additionally requires no lookahead violations and parameter consistency.

Even when those diagnostic gates are true, C23 still cannot create a catalog or run OOS:

```text
C23_CATALOG_CODE=NOT_CREATED
C23_CATALOG_IMPLEMENTATION_DEFERRED=true
catalog_allowed=false
oos_allowed=false
OOS_NOT_RUN=true
production_ready=0
```

## Required lock flags

```text
NO_C01_TO_C22_MUTATION=true
NO_C19_REOPEN=true
NO_C20_REOPEN=true
NO_C21_REOPEN=true
NO_C22_REOPEN=true
PLAN_RECOMMENDATION_CONFIRM_BOUNDARY_UNCHANGED=true
NO_TICKER_BLACKLIST=true
NO_MONTH_BLACKLIST=true
NO_SECTOR_WHITELIST=true
NO_BEST_OF_FAILED_BINDING=true
```

## Current implementation status

```text
C23_SOURCE_IMPLEMENTED=true
PHPUNIT_C23_SERVICE=PASS
PHPUNIT_C23_STATIC_GUARD=PASS
C23_COMMAND_REGISTERED=PASS
C23_RUNTIME_VALIDATED=true
C23_ALL_PARAM_ARTIFACT_HASH=5b79103c74faa01e4ce01cabbad1a3b36cdf31aa
C23_FIRST_PROFIT_CAPTURE_RULE_SIGNAL_FOUND=true
C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND=true
C23_C22_SHADOW_GAP_ACCEPTABLE=false
FULL_WATCHLIST_PHPUNIT=PASS
```

C23 remains a diagnostic evidence layer, not a strategy promotion layer. The all-param rerun found average/win-rate improvement for a non-lookahead first-profit-capture profile and closed the param/month consistency gap, but it did not pass the C22 shadow gap required to justify any catalog or OOS path.
