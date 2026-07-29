# WS Price Quality P01 Diagnostic, Candidates, Official IS, and Closure

## Final decision

```text
SCOPE=WS_PRICE_QUALITY_P01
STATUS=FAILED_NOT_READY_CLOSED
CANONICAL_IS_PASSING_CANDIDATE_COUNT=0
REMEDIATION_ROUNDS_USED=1
REMEDIATION_ROUNDS_REMAINING=0
OOS_RUNTIME_INVOKED=0
OOS_REPOSITORY_INVOKED=0
OOS_TABLE_READ=0
PARAMSET_PROMOTED=0
ACTIVE_PARAMSET_COUNT=0
PLAN_RUN_COUNT=0
PRODUCTION_READY=0
NEXT=NEW_SEPARATE_PREREGISTERED_STRATEGY_SCOPE_ONLY
```

P01 followed the uploaded post-closure target: it used a separate strategy
scope, one primary hypothesis, three thresholds locked before diagnostic,
only two evidence-authorized candidates, unchanged canonical IS gates, at
most one remediation, and no OOS access before IS pass.

## Diagnostic

```text
RUN_CODE=WS_PRICE_QUALITY_P01_PREREGISTERED_IS_ONLY_DIAGNOSTIC
SOURCE_EVAL_ID=212
SOURCE_PARAM_SET_ID=20
SOURCE_PARAMS_HASH=dde1467e62dd6c586ad7234479d5ae23794a759a
DIAGNOSTIC_ARTIFACT_HASH=3e12d95c1a39673859aa95831c84017ca4b298c7
DIAGNOSTIC_FILE_SHA1=931440303da57995c0db169b5387f66c3a40c265
PREDECLARED_THRESHOLDS=50,100,200
AUTHORIZED_THRESHOLDS=50,100
REJECTED_THRESHOLD=200
DRAFT_CREATED_DURING_DIAGNOSTIC=0
OOS_TABLE_READ=0
```

Threshold 200 was not persisted because it had only 115 trades and a
worst-month average of `-0.015352`.

## Immutable initial catalog and Official IS

```text
CATALOG_CODE=WS_BT_GRID_PRICE_QUALITY_P01_2026_07
CATALOG_HASH=e91085d64706ef9a0f296a42ea30e750f831217d
CANDIDATE_COUNT=2
C1_PARAM_SET_ID=25
C1_BT_PARAM_ID=178
C1_EVAL_ID=216
C1_PARAMS_HASH=2fb258a0e5c77ff9ee0347a9656e8ff77f3ae53c
C1_ARTIFACT_HASH=68e23dbcb942aab5e53fb00c58e371d76e4fa6a0
C1_FILE_SHA1=0a6c3611fed404887ff1be66ef20201d4fbf266b
C2_PARAM_SET_ID=26
C2_BT_PARAM_ID=179
C2_EVAL_ID=217
C2_PARAMS_HASH=abbd9197d6e12c8e26bd9a13181efa29a2d6b592
C2_ARTIFACT_HASH=1db740ad8f13b14b4a3f51a6420041d77b250821
C2_FILE_SHA1=4f187e4d27134205a96cb2389d072ac18b85cacd
```

| Candidate | Trades | Average | Median | P25 | Win rate | Min monthly WR | Min monthly average | Failed periods |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| C1 floor 50 | 189 | 0.00372060 | 0.00690518 | 0.00527814 | 0.915344 | 0.666667 | -0.01143905 | 1 |
| C2 floor 100 | 172 | 0.00180405 | 0.00620928 | 0.00511509 | 0.901163 | 0.500000 | -0.03022579 | 2 |

C1 failed only the unchanged monthly-average floor. C2 failed the monthly
win-rate and monthly-average floors. Neither candidate authorized OOS.

## Single bounded remediation

The single remediation retained exact C1 selection and added only a fixed
D1-D3 close-loss signal at `-1%`, executed at the next trading-day open.
The threshold was sourced from the unchanged canonical monthly-average floor,
not from a threshold sweep. No new signal-price threshold was introduced.

```text
REMEDIATION_CATALOG_CODE=WS_BT_GRID_PRICE_QUALITY_P01_REMEDIATION_2026_07
REMEDIATION_PARAM_SET_ID=27
REMEDIATION_BT_PARAM_ID=180
REMEDIATION_PARAMS_HASH=b9ebd3a64c92aa7e09c786f1fce6c1a13ada469a
INITIAL_REMEDIATION_EVAL_ID=218
INITIAL_REMEDIATION_ARTIFACT_HASH=d4a834938202c0c39b53fd094088273a561854a2
INITIAL_REMEDIATION_EVIDENCE_DECISION=INVALID_FOR_FINAL_CLOSURE
INVALID_REASON=GENERIC_EVAL_MODEL_LABEL_DID_NOT_ENCODE_P01_LOSS_EXIT
```

Eval 218 is retained immutably but cannot authorize closure or downstream
use. The runtime semantics were correct; the deterministic execution-identity
mapper lacked the P01-namespaced equality branch.

## Identity-only repair and authoritative rerun

The repair changed only identity mapping and created a new immutable DRAFT.
It did not change selection, execution, gates, price threshold, loss
threshold, or remediation round.

```text
IDENTITY_REPAIR_PARAM_SET_ID=28
IDENTITY_REPAIR_BT_PARAM_ID=180
IDENTITY_REPAIR_PARAMS_HASH=b3a61e825751fa007f9fcfed8d30ecbbfa78c171
IDENTITY_REPAIR_EVAL_MODEL=ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
IDENTITY_REPAIR_EVAL_MODEL_HASH=6c0b0e23228f644ed26109bcd09b6a818978fe75
AUTHORITATIVE_REMEDIATION_EVAL_ID=219
AUTHORITATIVE_REMEDIATION_ARTIFACT_HASH=521b74201b95f91e2e811e0b8e1bd9b2b9fe1758
AUTHORITATIVE_REMEDIATION_FILE_SHA1=ab191c46ff64116ecb4663e36aab1c2025bdf6a4
AUTHORITATIVE_EVIDENCE_MANIFEST_HASH=2110f4fec4984446b599f9e3b1fd6c7b5fb40ac1
```

Authoritative eval 219:

```text
picks_count=187
days_covered=497
avg_ret_net_top=0.008006009018199029
median_ret_net_top=-0.0005000750112516877
p25_ret_net_top=-0.04232922821700027
win_rate_top=0.48663101604278075
month_win_rate_min=0
month_avg_ret_net_min=-0.08743718592964825
period_fail_count=11
failed_gates=MEDIAN,P25,MONTHLY_WIN_RATE,MONTHLY_AVERAGE
```

The corrected rerun reproduced eval 218 metrics exactly, proving that the
repair was identity-only. Four unchanged canonical gates failed, so P01 is
closed without OOS, promotion, ACTIVE paramset, PLAN, or production use.

## Validation

```text
P01_FOCUSED_PHPUNIT=PASS_10_TESTS_154_ASSERTIONS
S01_REGRESSION=PASS_5_TESTS_46_ASSERTIONS
R02_REGRESSION=PASS_7_TESTS_56_ASSERTIONS
FULL_WATCHLIST_PHPUNIT=PASS_7161_TESTS_48725_ASSERTIONS
FINAL_DATABASE_EVAL_ROW_COUNT=218
FINAL_DATABASE_PARAMSET_ROW_COUNT=26
FINAL_ACTIVE_PARAMSET_COUNT=0
FINAL_PLAN_RUN_COUNT=0
```
