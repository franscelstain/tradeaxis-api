# C171 Final Bounded Remediation Catalog and Closure Rule Lock

## Decision

The exact V3 official-IS evidence set is `eval_id=199..204`, pipeline
`WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3` / `9e9933b363026623b7ab5629f3281fa680a53a2e`.
All six candidates failed canonical IS gates. Paramset 11 (`eval_id=204`) is the final anchor because it has the best V3 average return, win rate, monthly-win floor, and period-fail count among the valid V3 comparison set.

The low-price tick-risk direction is closed as a primary strategy direction. The valid V3 sequence 1.50% -> 1.00% -> 0.50% improved only P25 while degrading average return, median return, win rate, coverage, and period stability.

Exactly one final bounded remediation catalog is allowed:

```text
catalog_code=WS_BT_GRID_FINAL_BOUNDED_REMEDIATION_C01_2026_07
catalog_version=FINAL-C01
catalog_count=3
anchor_eval_id=204
anchor_param_set_id=11
```

Candidates:

1. `C171_FINAL_A_RISK_FORWARD_INTERPOLATED`: interpolate risk-forward scoring at 35% risk weight.
2. `C171_FINAL_B_RISK_FORWARD_ATR_055`: retain paramset-11 scoring and apply only a mild 5.5% ATR ceiling.
3. `C171_FINAL_C_RISK_FORWARD_STOP_125`: retain paramset-11 selection and tighten only the stop ATR multiplier to 1.25.

No ticker blacklist, month blacklist, OOS field, future return, or weakened canonical gate is allowed.

## Closure rule

```text
IF_ANY_FINAL_CANDIDATE_PASSES_ALL_CANONICAL_IS_GATES=
  C171_FINAL_REVIEW_REQUIRED_BEFORE_C172

IF_NO_FINAL_CANDIDATE_PASSES_ALL_CANONICAL_IS_GATES=
  C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION

ADDITIONAL_C171_CANDIDATE_CATALOG_ALLOWED=0
```

The persistence command creates only three immutable DRAFT paramsets. It does not run official IS, read OOS, promote a paramset, create PLAN, persist recommendations, mutate CONFIRM, or activate production.

## Operator outcome

The catalog was persisted as paramsets `12,13,14` and executed as official-IS evals `205,206,207`. All three candidates failed every canonical quality gate while preserving the no-OOS/no-promotion/no-PLAN boundary. The locked no-pass branch now applies: `C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION`.
