# Legacy Role Extract — WS — EVIDENCE

> **Document Type:** EVIDENCE
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0550-EVD-02`
> **Legacy Source ID:** `LS-WS-0550`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
> **Original SHA1:** `F36B415CA47D448CF0C5EA5AEDE987D497FFEF42`
> **Source Sections:** L1569-L1596 C171 versioned official IS evidence addendum; L1597-L1605 C171 strict canonical IS boundary and evidence population; L1606-L1619 C171 C01 tick-risk evidence-pipeline repair addendum
> **Extract Body SHA1:** `4C07B208EF5B7F8F54F17C46AF00A239E88866ED`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## C171 versioned official IS evidence addendum

C171 implements the previously planned exact evaluation identity. Official IS evidence is no longer accepted as aggregate metrics alone.

```text
DRAFT_CANONICAL_PARAMS_HASH=watchlist_param_sets.params_hash
IS_PARAMSET_HASH=watchlist_bt_eval.paramset_hash
IS_EVAL_MODEL_HASH=watchlist_bt_eval.eval_model_hash
IS_IMPLEMENTATION_HASH=watchlist_bt_eval.implementation_hash
IS_EVIDENCE_PIPELINE_VERSION=watchlist_bt_eval.evidence_pipeline_version
IS_EVIDENCE_PIPELINE_HASH=watchlist_bt_eval.evidence_pipeline_hash
IS_EVIDENCE_MANIFEST_HASH=watchlist_bt_eval.evidence_manifest_hash
SUPPORT_ROWS_IDENTITY=eval_id
```

The official support tables are now evaluation-versioned:

```text
watchlist_bt_picks_ws.eval_id
watchlist_bt_universe_ws.eval_id
watchlist_bt_cutoffs_ws.eval_id
```

Each row carries a deterministic `row_hash`. `watchlist_bt_eval` stores aggregate hashes for picks, universe, cutoffs, and Market Data lineage. Promotion must recompute the persisted manifest and match all hashes; matching row counts alone are insufficient.

C171 runs canonical IS only. OOS, DRAFT-to-ACTIVE promotion, PLAN persistence, recommendation persistence, CONFIRM, activation, and rollout remain forbidden.

## C171 strict canonical IS boundary and evidence population

The canonical C171 IS window is locked to `2023-01-02` through `2025-05-21`. Entry generation must censor the final holding-horizon trading days so no required price read exceeds the IS boundary.

Official IS picks are exactly the metrics-ready `TOP` / `TOP_PICKS` population used by canonical `picks_count`. `SECONDARY` and non-metrics-ready rows are not official picks. Every official pick, universe row, and cutoff row must carry positive Market Data publication/version/run lineage. Universe dates and cutoff dates must match exactly.

`watchlist_bt_eval` and its support evidence are one transactional identity bundle. Hash fields are strings and must never be coerced to numeric values. A failed support-evidence write must not leave a newly inserted orphan evaluation row.

## C171 C01 tick-risk evidence-pipeline repair addendum

Evidence construction is versioned independently from immutable strategy semantics. The corrected pipeline is:

```text
EVIDENCE_PIPELINE_VERSION=WS_C171_C01_TICK_RISK_EVIDENCE_PIPELINE_V2
EVIDENCE_PIPELINE_HASH=53857a635f6662542f0dc80f08051bed25a7afb8
STRATEGY_IMPLEMENTATION_VERSION=WS_CANONICAL_IS_C171_V1
```

Legacy evaluations are backfilled with an explicit V1 marker and are never rewritten. A corrected rerun with the same DRAFT, param-grid row, model, implementation, and IS window receives a new `eval_id` because evidence-pipeline identity is part of the evaluation key.

For a paramset with `max_signal_tick_risk_expansion_pct`, official IS must fail closed unless all scored candidates and TOP-pick candidates carry decision-time `signal_close_price` and `signal_tick_risk_expansion_pct`, every above-threshold row includes `WS_TICK_RISK_HIGH` in the full reason-code set, and no eligible row remains above threshold. OOS and promotion remain forbidden until corrected official IS comparison is complete.
