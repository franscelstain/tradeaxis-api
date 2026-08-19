# Legacy Role Extract — WS — EVIDENCE

> **Document Type:** EVIDENCE
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0526-EVD-01`
> **Legacy Source ID:** `LS-WS-0526`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/implementation/weekly_swing/05_WS_PERSISTENCE_GUIDANCE.md`
> **Original SHA1:** `9ED7FE8517BBC9E3E02704222C282674E5A60994`
> **Source Sections:** L137-L166 Canonical Logical Keys per Artifact
> **Extract Body SHA1:** `E67C13E7608A0AE909C96C31E5B87B4B8B5D653D`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Canonical Logical Keys per Artifact

### PLAN
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `param_set_id`

### RECOMMENDATION
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `param_set_id`
- `source_plan_reference`
- `capital_mode`

### CONFIRM
Logical uniqueness minimum harus bisa dibuktikan dari kombinasi:
- `strategy_code`
- `trade_date`
- `policy_code`
- `policy_version`
- `source_plan_reference`
- `ticker`
- `snapshot_reference`
