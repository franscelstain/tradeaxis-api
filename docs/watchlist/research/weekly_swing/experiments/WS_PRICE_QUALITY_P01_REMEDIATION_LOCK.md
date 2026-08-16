# Weekly Swing Price Quality P01 Single Remediation Lock

## Lock timing and source identity

This contract is locked after both preregistered P01 candidates completed
Official IS and before any P01 remediation DRAFT or runtime is created.

```text
SCOPE=WS_PRICE_QUALITY_P01
REMEDIATION_CODE=P01_M1_C1_MIN_PRICE_50_LOSS_CLOSE_NEG1_NEXT_OPEN
REMEDIATION_ROUND=1
MAX_REMEDIATION_ROUNDS=1
SOURCE_CANDIDATE=P01_C1_MIN_SIGNAL_PRICE_50
SOURCE_PARAM_SET_ID=25
SOURCE_BT_PARAM_ID=178
SOURCE_EVAL_ID=216
SOURCE_PARAMS_HASH=2fb258a0e5c77ff9ee0347a9656e8ff77f3ae53c
SOURCE_OFFICIAL_IS_ARTIFACT_HASH=68e23dbcb942aab5e53fb00c58e371d76e4fa6a0
SOURCE_OFFICIAL_IS_FILE_SHA1=0a6c3611fed404887ff1be66ef20201d4fbf266b
SOURCE_EVIDENCE_MANIFEST_HASH=01b398612ee5add8b757c468f495dd37427775be
CANONICAL_IS_FROM=2023-01-02
CANONICAL_IS_TO=2025-05-21
```

## Evidence and bounded change

C1 retained 189 TOP trades, 498 covered days, positive average and median,
91.53% win rate, and failed only the unchanged monthly-average floor:
`-0.0114390534 < -0.01`. The failing month was October 2024. Its seven TOP
trades included one D5 hold-expired LPCK loss of `-0.1247305359`; the other
six trades were positive.

C2 is not the remediation source because it failed two gates and had a lower
average return. No intermediate signal-price threshold is introduced.

The one and only remediation retains the exact C1 selection:

```text
min_roc20=0.10
max_roc20=0.15
benchmark_code=IHSG
allowed_regimes=STRONG,MIXED
min_signal_close_price=50
```

It changes only fixed exit behavior:

```text
preplanned_profit_target_pct=0.005
profit_signal_days=D1,D2,D3
profit_signal_exit=NEXT_TRADING_DAY_OPEN
loss_close_threshold_pct=-0.01
loss_signal_days=D1,D2,D3
loss_signal_exit=NEXT_TRADING_DAY_OPEN
fallback_exit=D5_CLOSE
```

The `-1%` threshold is the unchanged canonical monthly-average floor, not an
optimized threshold selected from a sweep. The loss signal uses only a
published close and executes at the next tradable open.

## Boundaries

```text
NEW_SIGNAL_PRICE_THRESHOLD=0
TICKER_BLACKLIST_USED=0
MONTH_BLACKLIST_USED=0
SECTOR_WHITELIST_USED=0
CANONICAL_GATES_CHANGED=0
OOS_READ_OR_EXECUTED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
PRODUCTION_READY=0
REMEDIATION_RUNTIME_INVOKED_AT_LOCK_TIME=0
```

If this remediation fails any canonical IS gate, P01 closes failed/not-ready
with no second remediation and OOS remains forbidden.
