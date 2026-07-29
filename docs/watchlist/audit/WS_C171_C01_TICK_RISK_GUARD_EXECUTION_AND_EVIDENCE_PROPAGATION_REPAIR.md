# C171 C01 Tick-Risk Guard Execution and Evidence Propagation Repair

## Scope

This repair closes the evidence gap observed after official IS eval 194-196. It does not change the immutable C01 DRAFT payloads, canonical IS gates, execution model, OOS boundary, PLAN/CONFIRM, or production runtime.

## Root cause

`WatchlistCandidateUniverseService` calculated decision-time tick-risk metrics inside `gate_metrics`, but `WatchlistScoringService::extractMetrics()` did not propagate those fields into `score_metrics`. Official evidence built from scored plan rows therefore persisted NULL tick-risk values for selected picks, making the C01 threshold experiment inconclusive.

## Repair

1. Propagate `signal_close_price`, `theoretical_stop_risk_pct`, `normalized_stop_risk_pct`, and `signal_tick_risk_expansion_pct` into scored evidence.
2. Audit all official-universe rows using the complete `reason_codes` set, not only the canonical primary reason.
3. Fail closed when a scored candidate or official pick is missing the metric, when an above-threshold row lacks `WS_TICK_RISK_HIGH`, or when an eligible row remains above threshold.
4. Version evidence construction as `WS_C171_C01_TICK_RISK_EVIDENCE_PIPELINE_V2` while leaving `WS_CANONICAL_IS_C171_V1` unchanged.
5. Preserve eval 194-196 and create new eval rows for corrected reruns of paramsets 7-9.

## Required audit fields

```text
scored_candidate_count
metric_propagated_to_scored_candidates_count
metric_missing_on_scored_candidates_count
official_pick_count
metric_propagated_to_official_picks_count
metric_missing_on_official_picks_count
above_threshold_before_guard_count
above_threshold_without_tick_reason_count
tick_only_rejected_count
tick_multi_reason_rejected_count
eligible_above_threshold_after_guard_count
```

## Boundary

```text
OOS_ALLOWED=0
PROMOTION_ALLOWED=0
PLAN_ALLOWED=0
C172_ALLOWED=0
PRODUCTION_READY=0
```

## Operator migration immutability-trigger repair

The first production migration attempt added the nullable pipeline columns and dropped the old evaluation identity index, but its legacy metadata backfill was correctly blocked by the existing `trg_wbe_eval_no_update` immutability trigger. The migration is now recovery-safe and idempotent:

1. it tolerates columns already created and the old index already removed;
2. it fingerprints every pre-existing evaluation field except the two new pipeline columns;
3. it temporarily releases only the MySQL UPDATE guard;
4. it backfills only missing pipeline metadata;
5. it restores the UPDATE guard in `finally`;
6. it fails if the immutable evidence fingerprint changes;
7. it recreates the expanded unique identity index if missing.

The DELETE guard is never released. No eval, pick, universe, cutoff, DRAFT, OOS, promotion, PLAN, or production payload is changed.

```text
C171_C01_PIPELINE_MIGRATION_FIRST_MAIN_ATTEMPT=BLOCKED_BY_EXPECTED_IMMUTABILITY_TRIGGER
C171_C01_PIPELINE_MIGRATION_PARTIAL_STATE_RECOVERY_SUPPORTED=1
C171_C01_PIPELINE_BACKFILL_FIELDS=evidence_pipeline_version,evidence_pipeline_hash
C171_C01_PIPELINE_IMMUTABLE_PAYLOAD_FINGERPRINT_REQUIRED=1
C171_C01_PIPELINE_MYSQL_UPDATE_GUARD_TEMPORARILY_RELEASED=1
C171_C01_PIPELINE_MYSQL_UPDATE_GUARD_FINALLY_RESTORED=1
C171_C01_PIPELINE_MYSQL_DELETE_GUARD_RELEASED=0
C171_C01_PIPELINE_DRAFT_MUTATION=0
C171_C01_PIPELINE_OOS_RUNTIME_INVOKED=0
C171_C01_PIPELINE_PROMOTION_EXECUTED=0
C171_C01_PIPELINE_PLAN_RUN_CREATED=0
C171_C01_PIPELINE_PRODUCTION_READY=0
```
