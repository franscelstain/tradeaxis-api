# Weekly Swing Price Quality P01 Preregistration Lock

## Scope identity

```text
SCOPE=WS_PRICE_QUALITY_P01
RUN_CODE=WS_PRICE_QUALITY_P01_PREREGISTERED_IS_ONLY_DIAGNOSTIC
SEPARATE_FROM_C171_R02_S01=1
SOURCE_EVAL_ID=212
SOURCE_PARAM_SET_ID=20
SOURCE_BT_PARAM_ID=174
SOURCE_PARAMS_HASH=dde1467e62dd6c586ad7234479d5ae23794a759a
SOURCE_ARTIFACT_HASH=0e1823c01c48f64072f9d07347cad0e7f304c4d1
SOURCE_EVIDENCE_MANIFEST_HASH=f9cd54843f3f34f946e7df6ff03246c10529fb3a
CANONICAL_IS_FROM=2023-01-02
CANONICAL_IS_TO=2025-05-21
```

P01 is a separately named strategy scope. Eval `212` is a diagnostic anchor
only because it retained sufficient sample and failed only the monthly-average
gate. It is not a best-of-failed binding and S01 remains closed.

## Research hypothesis

```text
HYPOTHESIS_CODE=P01_H1_LOW_PRICE_MICROSTRUCTURE_RISK
PRIMARY_IDEA=EXACT_SIGNAL_DATE_MINIMUM_PRICE_QUALITY_FLOOR
MAX_HYPOTHESES=1
MAX_CANDIDATES=3
MAX_REMEDIATION_ROUNDS=1
```

Hypothesis: sub-band signal prices create materially asymmetric tick and
execution outcomes. A minimum exact signal-date close may preserve the
high-quality non-weak-IHSG core while removing unstable low-price exposure.

## Diagnostic buckets and candidate thresholds

The following buckets and candidate thresholds are locked before the P01
diagnostic runtime:

```text
DIAGNOSTIC_BUCKET_1=SIGNAL_CLOSE_LT_50
DIAGNOSTIC_BUCKET_2=SIGNAL_CLOSE_50_TO_99
DIAGNOSTIC_BUCKET_3=SIGNAL_CLOSE_100_TO_199
DIAGNOSTIC_BUCKET_4=SIGNAL_CLOSE_GE_200

CANDIDATE_1=P01_C1_MIN_SIGNAL_PRICE_50
CANDIDATE_1_THRESHOLD=50
CANDIDATE_2=P01_C2_MIN_SIGNAL_PRICE_100
CANDIDATE_2_THRESHOLD=100
CANDIDATE_3=P01_C3_MIN_SIGNAL_PRICE_200
CANDIDATE_3_THRESHOLD=200
```

These are finite, interpretable price-quality floors. No additional threshold
may be introduced after diagnostic output is observed.

## Diagnostic authorization gate

A candidate may proceed to DRAFT design only when its retrospective immutable
IS subset:

```text
picks_count >= 120
avg_ret_net > 0
median_ret_net >= 0
p25_ret_net >= -0.03
month_win_rate_min >= 0.45
month_avg_ret_net_min >= -0.01
source_worst_month_average_improved=1
```

This is only a candidate-design authorization check. The later independent
Official IS rerun remains authoritative and must pass every unchanged
canonical gate.

## Anti-overfit and boundary rules

```text
EXACT_SIGNAL_DATE_FIELD_ONLY=signal_close_price
REALIZED_RETURN_AS_RUNTIME_INPUT=0
ENTRY_GAP_AS_RUNTIME_INPUT=0
TICKER_BLACKLIST_USED=0
MONTH_BLACKLIST_USED=0
SECTOR_WHITELIST_USED=0
CANONICAL_GATES_CHANGED=0
OOS_READ_OR_EXECUTED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
PRODUCTION_READY=0
```

The P01 diagnostic must verify immutable source artifact/database identity,
official evidence manifest parity, exact signal publication lineage, and
unchanged database boundary counts. If no locked threshold satisfies the
authorization gate, P01 closes without a catalog.
