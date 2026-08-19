# WS Breakout Integrity B01 — Execution and Target Alignment

## Decision

The post-C171 target is aligned with the Watchlist owner contracts and has now
been implemented through the first controlled-runtime boundary:

```text
RESEARCH_HYPOTHESIS=COMPLETE
DIAGNOSTIC_EVIDENCE=COMPLETE
MINIMAL_STRATEGY_IMPLEMENTATION=COMPLETE
CANONICAL_IS=PASS
OFFICIAL_OOS=PASS
CANONICAL_PROMOTION=PASS
ACTIVE_SHADOW_DRY_RUN=PASS
CONTROLLED_PLAN_ROLLOUT=NOT_EXECUTED
PRODUCTION_APPROVAL=NOT_GRANTED
PRODUCTION_READY=0
```

The original C171 failed/not-ready assessment remains historically correct.
B01 is a separate strategy scope; it does not reopen or overwrite C171.

## Alignment with the Uploaded Target

| Uploaded target | B01 result |
|---|---|
| Maximum three hypotheses | Three bounded directions were considered: Q01, preliminary M01, and B01. B01 locked one primary hypothesis. |
| Maximum 3–5 candidates | B01 diagnosed exactly three variants and implemented only one authorized candidate. |
| Maximum one remediation | Zero remediation rounds were used. |
| One idea per candidate | B01 added only a signal-date close-to-HH20 floor. |
| Canonical gates unchanged | All seven IS and five locked OOS gates remained unchanged. |
| OOS unread before IS PASS | IS identity review completed before the single Official OOS execution. |
| No ticker/month blacklist | No blacklist was introduced. |
| No future return input | Selection uses only signal-date data; future-derived routing remains false. |
| Immutable/reproducible evidence | Paramset, eval model, implementation, support manifest, and artifacts carry exact hashes. |
| No PLAN/CONFIRM mutation from research | PLAN runs/items and CONFIRM-related tables remain zero. |
| Full stable test suite | 7,171 Watchlist tests and 48,832 assertions pass. |
| Controlled runtime before production | ACTIVE shadow passed; rollout and production remain closed. |

## Locked Strategy Identity

```text
primary_hypothesis=B01_H1_BREAKOUT_DISTANCE_INTEGRITY
candidate=B01_C1_CLOSE_TO_HH20_FLOOR_NEG5
rule=SIGNAL_ROC20_10_TO_15_IHSG_NON_WEAK_MIN_PRICE_BREAKOUT_FLOOR
min_close_to_hh20_pct=-0.05
min_signal_close_price=50
allowed_regimes=STRONG,MIXED
param_set_id=29
bt_param_id=181
params_hash=ff14df49c1a5b3da997dafbea163a51e008314fd
eval_model_hash=d0e6f180b85edda2c3785460cd958581684102f1
implementation_hash=9f9ac615f2c9506bbebee5fb60a2038aa3a42c25
evidence_manifest_hash=e413a21f8951722e113a99cb6c60691d8b289750
```

## Canonical IS Result

Window: `2023-01-02..2025-05-21`

| Metric | Result | Gate |
|---|---:|---|
| Trades | 146 | PASS, minimum 120 |
| Days covered | 500 | PASS, minimum 390 |
| Average return | 0.0036870822158956863 | PASS, greater than 0 |
| Median return | 0.006905178884163123 | PASS, at least 0 |
| P25 return | 0.005220092973476967 | PASS, at least -0.03 |
| Worst monthly win rate | 0.625 | PASS, at least 0.45 |
| Worst monthly average | -0.008998265585691277 | PASS, at least -0.01 |

All seven canonical IS gates passed. The support manifest contains 146 picks,
401,705 universe rows, and 508 cutoffs.

## Official OOS Result

Window: `2025-05-22..2026-05-29`

| Metric | Result | Locked gate |
|---|---:|---|
| Trades | 84 | PASS, minimum 40 |
| Average return | 0.002326377918853518 | PASS, greater than 0 |
| Median return | 0.0070446965286297515 | PASS, at least 0 |
| P25 return | 0.005048780318109579 | PASS, at least -0.03 |
| Worst monthly win rate | 0.7142857142857143 | PASS, at least 0.45 |

Average-return retention from IS to OOS is approximately `63.10%`; median
retention is `102.02%`, P25 retention `96.72%`, and the worst-month win-rate
ratio `114.29%`. This is acceptable under the preregistered five-gate OOS
contract and no new threshold was invented after OOS was read.

One operational caution remains visible: the OOS worst monthly average was
`-0.019712322274332197` and one of 11 periods failed the IS-style monthly
average floor. Monthly average was not a locked OOS gate, so it cannot be
retroactively converted into a strategy rejection or tuning signal. It must,
however, be considered by the next operator go/no-go review.

## Promotion and ACTIVE Shadow

The canonical promotion path completed:

```text
param_set_id=29
status=ACTIVE
deprecated_active_count=0
active_paramset_count=1
production_ready=0
```

The ACTIVE shadow used explicit date `2026-07-28` and exact Market Data
lineage:

```text
publication_id=68547
publication_version=3
run_id=67865
pointer_resolve_status=RESOLVED_READABLE_CURRENT
market_data_candidates=809
eligible_candidates=31
scored_candidates=31
non_official_tickers=BFIN,GGRM
```

Ticker audit:

| Ticker | Close | ROC20 | Close to HH20 | B01 decision |
|---|---:|---:|---:|---|
| BFIN | 845 | 0.1496598639 | -1.7441860465% | PASS |
| GGRM | 18,350 | 0.1292307692 | -2.1333333333% | PASS |

IHSG was STRONG on the shadow date. Both rows match the exact publication
lineage and the B01 price, momentum, regime, and breakout-floor contract.
These rows are controlled local evidence only, not investment instructions or
officially published recommendations.

## Evidence Ledger

| Evidence | Artifact/output hash | File SHA1 |
|---|---|---|
| Q01 rejected diagnostic | `a60779a2b4fbdfc89561b345ec039efd9573c001` | `57bf6d14092fdffe205dcefd4d6f53cb9e4dae4f` |
| B01 diagnostic | `1a328ea84d4468fe2124263f25be272286dfbf01` | `fe703103a768a68a8286d13dd8f7ac41f3f2446a` |
| B01 Official IS | `adf7ec1ba705a4823f4c8590967ffba08fcbd5d8` | `9d36e816b5b2ed31c7c3d087954d7cf47b476ef3` |
| IS identity review | `ca65ca2e25db2929f047f7baec6fc0891d90e7c0` | `462c8dd9e1fe21ae624b78fafb0aaea14f8437d0` |
| Official OOS | `0be1ef09abfb4ba332dc3f0605af90a5d3a565df` | `e6caa3390104b36598e97a5dd4ceaf740edc14fa` |
| Promotion readiness | `d71e7287f86bd3fcccf8db0ae01486fbaae0f4d7` | `250eea5203154adcf55f06e1adfda587bc74d358` |
| ACTIVE shadow review | `e1e6325321acd90e2e7ff95bded4d7bc885b0dd0` | `ca53036f9ef50310f20205e6604f90b502ac8994` |
| ACTIVE shadow runtime | `08ee7631ee6924efec29341156d204bc0ee5978f` | `303349cf5cbb15a3c8d3f560ced5e3a95081d504` |

The first shadow wrapper attempt stopped only on strict PHP-vs-JSON numeric
type equality after the runtime itself passed. The identity-preserving retry
changed only file validation to compare canonical hashes. It retained the same
ACTIVE identity, date, thresholds, and output path; strategy retuning remained
false.

## Current Boundary and Next Governed Step

```text
ACTIVE_PARAMSET_COUNT=1
ACTIVE_PARAM_SET_ID=29
OFFICIAL_OOS_ROW_COUNT=1
PLAN_RUN_COUNT=0
PLAN_ITEM_COUNT=0
RECOMMENDATION_ROW_COUNT=0
CONFIRM_MUTATED=0
PRODUCTION_FEATURE_FLAGS_ENABLED=0
OFFICIAL_OUTPUT_PUBLISHED=0
CONTROLLED_RUNTIME_PASS=1
PRODUCTION_ROLLOUT_APPROVED=0
PRODUCTION_READY=0
```

The next safe step is a separate operator go/no-go review of the shadow
evidence and the OOS monthly-average caution. No controlled PLAN persistence,
CONFIRM mutation, rollout, or official publication is authorized by this
document.
