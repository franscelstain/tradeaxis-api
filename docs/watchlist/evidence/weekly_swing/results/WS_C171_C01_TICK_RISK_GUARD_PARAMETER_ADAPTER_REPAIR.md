# C171 C01 Tick-Risk Guard Parameter Adapter Repair

## Scope

This repair follows the fail-closed V2 corrected-run attempt for immutable C01 paramsets 7, 8, and 9. Database migration, legacy evidence-pipeline backfill, missing-identity validation, and append-only triggers were valid. Runtime audit then proved that tick-risk metrics were propagated, but the threshold was not enforced:

```text
above_threshold_without_tick_reason_count>0
eligible_above_threshold_after_guard_count>0
tick_only_rejected_count=0
tick_multi_reason_rejected_count=0
```

## Root cause

`WatchlistScoringService::scoreForTradeDate()` resolves a local scoring paramset and passes that resolved payload to `WatchlistCandidateUniverseService`. The local resolver previously omitted candidate-universe-only guards:

```text
liquidity.max_dv20_idr
volume.max_vol_ratio
risk.stop_atr_mult
risk.min_rr
risk.max_signal_tick_risk_expansion_pct
```

Consequently the downstream candidate-universe resolver received `max_signal_tick_risk_expansion_pct=null`; tick-risk metrics existed, but no candidate could receive `WS_TICK_RISK_HIGH` and above-threshold candidates remained eligible.

## Repair

The scoring adapter now preserves all upstream candidate-universe guard fields before invoking candidate-universe construction. Validation also covers the optional maximum bounds and the fractional tick-risk threshold. A regression test captures the exact paramset received by the candidate-universe dependency.

Evidence-pipeline identity is advanced without changing immutable strategy semantics or DRAFT payloads:

```text
PREVIOUS_PIPELINE_VERSION=WS_C171_C01_TICK_RISK_EVIDENCE_PIPELINE_V2
PREVIOUS_PIPELINE_HASH=53857a635f6662542f0dc80f08051bed25a7afb8
CURRENT_PIPELINE_VERSION=WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3
CURRENT_PIPELINE_HASH=9e9933b363026623b7ab5629f3281fa680a53a2e
STRATEGY_IMPLEMENTATION_VERSION=WS_CANONICAL_IS_C171_V1
```

The V2 attempts failed before evaluation persistence; existing V1 evals and historical evals 194-198 remain immutable.

## Boundary

```text
DRAFT_PARAMSET_MUTATED=0
NEW_DRAFT_CREATED=0
OLD_EVAL_MUTATED=0
OOS_RUNTIME_INVOKED=0
PARAMSET_PROMOTED=0
PLAN_RUN_CREATED=0
PRODUCTION_READY=0
```

## Next operator action

Run focused and full PHPUnit, then rerun official IS for control paramset 5 and C01 paramsets 7-11. The control is required because the same adapter gap also dropped max-liquidity and max-volume guards from the historical runs. Each run must use evidence pipeline V3; tick-threshold candidates 7-9 must pass the tick-risk audit before canonical IS comparison.
