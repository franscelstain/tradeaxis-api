# Weekly Swing Price Quality P01 Execution Identity Repair Lock

## Finding

P01 remediation eval `218` executed the intended fixed `-1%` close-loss
next-open route, but its canonical `eval_model` was stored as
`EXIT=STOP_TP_OR_TIME`. The model-label mapper recognized the identical S01
execution object but did not recognize the P01-namespaced execution object.

```text
INVALIDATED_EVAL_ID=218
INVALIDATED_PARAM_SET_ID=27
INVALIDATED_PARAMS_HASH=b9ebd3a64c92aa7e09c786f1fce6c1a13ada469a
INVALIDATED_ARTIFACT_HASH=d4a834938202c0c39b53fd094088273a561854a2
INVALIDATED_ARTIFACT_FILE_SHA1=a7084384ba41767e594d739a5d464f85f21c7f4d
INVALIDATED_EVIDENCE_MANIFEST_HASH=2110f4fec4984446b599f9e3b1fd6c7b5fb40ac1
INVALIDATED_EVAL_MODEL=ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
EXPECTED_EVAL_MODEL=ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
```

## Repair boundary

The repair changes only deterministic identity mapping. It must create a new
DRAFT identity and a new Official IS eval because persisted DRAFT/eval rows
are immutable.

```text
STRATEGY_SELECTION_CHANGED=0
STRATEGY_EXECUTION_CHANGED=0
PRICE_THRESHOLD_CHANGED=0
LOSS_THRESHOLD_CHANGED=0
CANONICAL_GATES_CHANGED=0
REMEDIATION_ROUND_INCREMENTED=0
SECOND_REMEDIATION_CREATED=0
OOS_READ_OR_EXECUTED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
PRODUCTION_READY=0
```

The repaired rerun remains the same single P01 remediation. Eval `218` is
retained as immutable historical evidence but cannot authorize OOS,
promotion, PLAN, or closure. Only the corrected identity rerun may determine
the final P01 decision.
