# C171 Targeted Executable IS Strategy Remediation and Trade Evidence Diagnostic

## Session identity

```text
C171_TOPIC=C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION_AND_TRADE_EVIDENCE_DIAGNOSTIC
C171_PHASE=IS_ONLY_FAILED_BASELINE_DIAGNOSTIC
C171_EXECUTION_MODE=READ_ONLY_CANONICAL_IS_REPRODUCTION
C171_OFFICIAL_BASELINE_EVAL_ID=188
C171_OFFICIAL_BASELINE_PARAM_SET_ID=1
C171_PRODUCTION_READY=0
```

## Owner-document decision

The owner documents require one immutable IS candidate to pass before OOS. The
current official C171 evaluation is valid evidence but failed five performance
and stability gates. Therefore the next action remains inside C171. It is not a
C172 OOS run and it is not permission to lower canonical gates.

This session follows the planned order:

1. preserve `param_set_id=1` and `eval_id=188` as immutable failed-IS evidence;
2. reproduce the exact execution-time candidate population without future-derived routing;
3. prove parity with the persisted official picks;
4. segment all evaluated TOP picks using decision-time fields;
5. flag extreme price discontinuities for Market Data/corporate-action review;
6. distinguish data-quality impact from strategy-quality failure;
7. permit design of new immutable DRAFT candidates only after diagnostic evidence exists.

## Locked official baseline

```text
SOURCE_ZIP_SHA1=6A3BE45D1EDCD93030A78C15BDC8C8ECB9C23046
C171_ARTIFACT_FILE_SHA1=B9A3E74466F05FB7A1504CAFF4C7B06F86DD3F62
C171_ARTIFACT_HASH=fef05d2849746e233290224ca3c18018a44bbd81
C171_OFFICIAL_EVAL_ID=188
C171_PARAM_SET_ID=1
C171_BT_PARAM_ID=1
C171_PARAMS_HASH=b7f3c207b989c55c93f8f61b1fcceea2c343a151
C171_EVAL_MODEL_HASH=8e87dae9608e32b22be47c813dfdc975e0026a8a
C171_IMPLEMENTATION_VERSION=WS_CANONICAL_IS_C171_V1
C171_IMPLEMENTATION_HASH=9f9ac615f2c9506bbebee5fb60a2038aa3a42c25
C171_PICKS_COUNT=1425
C171_UNIVERSE_COUNT=401982
C171_CUTOFF_COUNT=508
C171_CANONICAL_IS_GATES_PASS=0
```

The persisted official evidence remains authoritative and must not be edited,
deleted, re-owned, or reused under a different parameter hash.

## Baseline gate result

```text
MINIMUM_TRADE_COUNT=PASS (1425 >= 120)
MINIMUM_COVERAGE=PASS (506 >= 390)
AVERAGE_RETURN_POSITIVE=FAIL (-0.002539000647187985)
MEDIAN_RETURN_NON_NEGATIVE=FAIL (-0.04926957899275094)
P25_DOWNSIDE_BOUND=FAIL (-0.08211231684752712 < -0.03)
MONTHLY_WIN_RATE_FLOOR=FAIL (0.2413793103448276 < 0.45)
MONTHLY_AVERAGE_FLOOR=FAIL (-0.0345288792730266 < -0.01)
```

The sample and date coverage are sufficient. The diagnostic must therefore
focus on return quality, downside, monthly stability, concentration, and any
price discontinuity that may distort the result.

## Implemented diagnostic command

```text
COMMAND=watchlist:backtest-c171-trade-evidence-diagnostic
SERVICE=WeeklySwingC171TradeEvidenceDiagnosticService
DATABASE_TARGET=tradeaxis
OFFICIAL_EVIDENCE_WRITE=0
OOS_RUNTIME=0
PARAMSET_PROMOTION=0
NEW_DRAFT_CREATION=0
PLAN_WRITE=0
RECOMMENDATION_WRITE=0
CONFIRM_MUTATION=0
PRODUCTION_MUTATION=0
```

The command reruns the same canonical IS window with the exact immutable DRAFT,
strict boundary, compact replay, and JSONL universe/cutoff spool. It reads but
does not replace the official support evidence.

Before any interpretation, the reproduction must match official picks on:

```text
trade_date + ticker_id
ticker_code
ret_net at six-decimal evidence scale
score_total at six-decimal evidence scale
entry Market Data publication_id/version/run_id
```

A parity mismatch blocks the diagnostic and forbids candidate redesign.

## Diagnostic outputs

```text
c171-trade-evidence-diagnostic.json
c171-trade-evidence-diagnostic-trades.csv
c171-trade-evidence-diagnostic-segments.csv
c171-trade-evidence-diagnostic-anomalies.csv
```

The detailed trade CSV contains entry/exit dates and prices, exit reason, fill
rule, gap flag, returns, P&L, score components, liquidity/risk/momentum fields,
sector, and Market Data lineage.

Segmentation axes are:

```text
month
ticker
exit_reason
gap_detected
entry_price_band
score_decile
dv20_band
atr14_band
vol_ratio_band
roc20_band
close_to_hh20_band
sector
```

These fields are decision-time fields or execution-result fields used only for
post-evaluation attribution. Future returns never select the strategy route.

## Price-discontinuity handling

Rows are flagged for Market Data review when one or more apply:

```text
ret_net <= -50%
ret_net >= +50%
exit_price / entry_price < 0.50
exit_price / entry_price > 1.50
```

A flag is not a corporate-action conclusion. It means the exact ticker/date and
published Market Data lineage must be reviewed against corporate-action and raw
price history. The diagnostic does not clamp prices, remove official evidence,
or silently exclude a trade.

## Remediation classification

The command produces exactly one classification:

```text
DATA_QUALITY_REVIEW_REQUIRED
MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED
STRATEGY_QUALITY_FAILURE_CONFIRMED
```

Interpretation:

- `DATA_QUALITY_REVIEW_REQUIRED`: canonical performance gates pass only after
  isolating flagged discontinuities. Market Data remediation and a new official
  evaluation of the unchanged paramset come first.
- `MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED`: discontinuities materially
  affect the result, but cleaned trades still fail canonical gates.
- `STRATEGY_QUALITY_FAILURE_CONFIRMED`: robust-return and stability failure
  remains after anomaly isolation. New immutable DRAFT candidates may then be
  designed from the segment evidence.

No classification itself creates a DRAFT or permits OOS.

## Forbidden actions

```text
EDIT_PARAM_SET_1_IN_PLACE=0
DELETE_EVAL_188=0
LOWER_CANONICAL_GATES=0
USE_OOS_FOR_DIAGNOSTIC_SELECTION=0
CREATE_LARGE_RANDOM_PARAM_GRID=0
AUTOMATIC_DRAFT_CREATION=0
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

## Next-stage rule

Only after the operator diagnostic completes:

```text
IF DATA_QUALITY_REVIEW_REQUIRED:
  C171_MARKET_DATA_CORPORATE_ACTION_AND_PRICE_DISCONTINUITY_REMEDIATION

IF MIXED_DATA_AND_STRATEGY_REMEDIATION_REQUIRED:
  C171_MARKET_DATA_REVIEW_THEN_IMMUTABLE_DRAFT_STRATEGY_REDESIGN

IF STRATEGY_QUALITY_FAILURE_CONFIRMED:
  C171_DESIGN_NEW_IMMUTABLE_DRAFT_PARAMSET_CANDIDATES_FROM_DIAGNOSTIC
```

C172 remains forbidden until one unchanged DRAFT passes every canonical IS gate
with versioned official evidence.
