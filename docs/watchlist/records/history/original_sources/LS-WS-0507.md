# WS Price Quality P01 Preregistration

P01 follows the uploaded post-C171 target by starting a new strategy scope,
limiting hypothesis count, using decision-time evidence, and keeping OOS
locked until all canonical IS gates pass.

The scope focuses on the explicit unresolved research area of low-price and
tick-size execution quality. It does not repeat the historically rejected
entry-gap hypothesis and does not add another S01 remediation.

```text
SCOPE=WS_PRICE_QUALITY_P01
SOURCE_EVAL_ID=212
SOURCE_PARAM_SET_ID=20
HYPOTHESIS_COUNT=1
CANDIDATE_THRESHOLD_COUNT=3
THRESHOLDS=50,100,200
THRESHOLDS_LOCKED_BEFORE_DIAGNOSTIC=1
OFFICIAL_IS_RUNTIME_INVOKED=0
OOS_RUNTIME_INVOKED=0
PRODUCTION_READY=0
```

The exact research and advancement contract is recorded in
`docs/watchlist/system/policies/weekly_swing/_refs/WS_PRICE_QUALITY_P01_PREREGISTRATION_LOCK.md`.
