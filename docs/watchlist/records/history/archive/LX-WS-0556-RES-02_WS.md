# Legacy Role Extract — WS — RESEARCH

> **Document Type:** RESEARCH
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0556-RES-02`
> **Legacy Source ID:** `LS-WS-0556`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
> **Original SHA1:** `0EF878820A9B7B8CADAEE05DBFC248A9313344AD`
> **Source Sections:** L129-L155 9) Official R2/C01 IS Calibration Evidence Transport
> **Extract Body SHA1:** `3A0DADC4FE9D01DE5C4F346A1CF649453776DD14`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## 9) Official R2/C01 IS Calibration Evidence Transport

The deterministic JSON produced by `watchlist:backtest-is-calibrate` is an official IS-calibration evidence transport. It is not a new database table and does not replace `watchlist_bt_param_grid` or `watchlist_bt_eval`.

Required sections are:

```text
meta
catalog_manifest
parameter_axes
r1_control_reference
is_window_manifest
market_data_lineage
all_evaluations
best_is_binding
gate_summary
diagnostic_summary
persistence_manifest
r1_immutability_proof
r2_immutability_proof
no_oos_read_proof
validation
```


The artifact must record `production_ready=false`, `oos_executed=false`, and `paramset_promoted=false`. A filename is operator-selected evidence transport, not catalog identity. The normative reference notes are `_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md` for R2 and `_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md` for C01.
