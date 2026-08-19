# WS Breakout Integrity B01 — ACTIVE Shadow Lock

The canonical promotion completed with this immutable identity:

```text
param_set_id=29
paramset_status=ACTIVE
bt_param_id=181
is_eval_id=220
oos_id=1
params_hash=ff14df49c1a5b3da997dafbea163a51e008314fd
promotion_readiness_artifact_hash=d71e7287f86bd3fcccf8db0ae01486fbaae0f4d7
promotion_readiness_file_sha1=250eea5203154adcf55f06e1adfda587bc74d358
promotion_log_file_sha1=d118170b27ce49c95c39d0f28de15351c7e8236a
```

The next stage is one artifact-only ACTIVE shadow execution. Before any
strategy output was read, the exact date was locked from read-only Market Data
readiness:

```text
shadow_trade_date=2026-07-28
publication_id=68547
publication_version=3
run_id=67865
pointer_resolve_status=RESOLVED_READABLE_CURRENT
terminal_status=SUCCESS
publishability_state=READABLE
coverage_gate_state=PASS
seal_state=SEALED
coverage_ratio=0.987486
strategy_return_used_for_date_selection=0
```

The ACTIVE canonical payload must be validated, unwrapped only through
`WeeklySwingParamsetRuntimeAdapter`, and executed by
`WeeklySwingWatchlistRuntimeService`. The B01 rule and threshold remain exact:

```text
rule_code=SIGNAL_ROC20_10_TO_15_IHSG_NON_WEAK_MIN_PRICE_BREAKOUT_FLOOR
min_close_to_hh20_pct=-0.05
retuning_allowed=0
default_paramset_substitution_allowed=0
```

This stage may write only local review/runtime JSON artifacts. It must not
create or mutate PLAN, PLAN items, recommendations, CONFIRM state, Official
OOS evidence, production feature flags, or official published output.
Production readiness remains false until a separate operator-reviewed stage.
An engineering retry is allowed only for resource or artifact-validation
mechanics while the ACTIVE identity, trade date, thresholds, and output path
remain unchanged. It is not a strategy retry and cannot authorize retuning.
