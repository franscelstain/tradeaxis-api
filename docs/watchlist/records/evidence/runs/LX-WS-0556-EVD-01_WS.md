# Legacy Role Extract — WS — EVIDENCE

> **Document Type:** EVIDENCE
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0556-EVD-01`
> **Legacy Source ID:** `LS-WS-0556`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
> **Original SHA1:** `0EF878820A9B7B8CADAEE05DBFC248A9313344AD`
> **Source Sections:** L156-L211 10) Official C01 IS Failure Drilldown Evidence Transport; L212-L253 C171 official IS evidence manifest
> **Extract Body SHA1:** `31A7B1DA20411673EDC2C715EC325F67D8BFEF51`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## 10) Official C01 IS Failure Drilldown Evidence Transport

The deterministic JSON produced by `watchlist:backtest-is-diagnose` is an official IS-only diagnostic evidence transport for failed C01 analysis. It is not a new database table, not an OOS proof artifact, not a promotion artifact, and not production readiness evidence.

Required sections are:

```text
catalog_code
catalog_version
catalog_hash
catalog_count
is_from
is_to
is_trading_date_hash
artifact_hash
canonical_artifact_hash
per_param_status
per_param_failure_codes
per_param_key_metrics
nearest_gate_gap
worst_gate_gap
candidate_count_summary
days_covered_summary
month_win_rate_min_summary
month_avg_ret_min_summary
downside_metric_summary
robust_return_metric_summary
stability_metric_summary
ticker_loss_cluster_summary
ticker_profit_cluster_summary
month_failure_cluster_summary
month_profit_cluster_summary
trade_date_failure_cluster_summary
setup_bucket_summary
breakout_extension_bucket_summary
momentum_roc_bucket_summary
volume_ratio_bucket_summary
liquidity_dv20_bucket_summary
atr_bucket_summary
score_bucket_summary
sector_bucket_summary
score_component_effectiveness_summary
param_axis_effectiveness_summary
runtime_consumed_parameter_summary
dead_parameter_or_silent_default_summary
runtime_field_availability_summary
data_quality_diagnostic_summary
no_oos_leakage_summary
diagnostic_reason_summary
next_focus_recommendation
```

If a runtime feature field needed by a diagnostic bucket is not exported by the evaluated trade evidence, the artifact must record `FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE`, `NOT_DERIVED`, and `NOT_USED_FOR_NEXT_CATALOG_DECISION` instead of synthesizing the missing field.

Canonical artifact hash must exclude timestamp-like metadata such as `generated_at`. The artifact must record `production_ready=false`, `oos_executed=false`, and `best_is_binding=null` when C01 has no valid IS parameter. The normative reference note is `_refs/WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md`.

## C171 official IS evidence manifest

The canonical IS manifest is owned by one immutable `watchlist_bt_eval.eval_id` and contains:

```text
eval_id
policy_code
param_id
paramset_hash
eval_model
eval_model_hash
implementation_version
implementation_hash
from_date
to_date
picks_count
picks_hash
universe_count
universe_hash
cutoff_count
cutoffs_hash
market_data_lineage_hash
evidence_manifest_hash
```

`evidence_manifest_hash` is the canonical hash of the manifest fields above excluding database-generated identifiers and timestamps. A support row from another `eval_id` is never valid evidence, even when counts and ticker values happen to match.

The C171 execution artifact is not an OOS, promotion, PLAN, recommendation, CONFIRM, activation, publication, or rollout artifact.


### Execution-time route proof for C171

A C171 artifact may state `future_derived_route_used=false` only when all of the following are recorded true by the runtime:

```text
trade_candidates_frozen_before_price_read
future_price_used_for_evaluation_only
strategy_payload_immutable
fixed_eval_model_identity_match
```

The statement may not be hard-coded without those runtime proofs.
