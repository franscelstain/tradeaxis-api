# Legacy Role Extract — R2 — STRATEGY

> **Document Type:** STRATEGY
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0604-STR-04`
> **Legacy Source ID:** `LS-WS-0604`
> **Legacy Work Key:** `R2`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/_refs/WS_R2_ENTRY_QUALITY_CALIBRATION_NOTE.md`
> **Original SHA1:** `74783FDEA5FEA8C3F39255D52386E24E31018678`
> **Source Sections:** L61-L73 Runtime-Consumption Trace
> **Extract Body SHA1:** `5EA60CD5D885575B46182510C10FBDB570D001B8`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Runtime-Consumption Trace

| Axis | Owner/registry | Persisted/factory | Runtime consumer |
|---|---|---|---|
| liquidity minimum/strong DV20 | file 05, `bt_target=true` | grid columns → `paramset.liquidity` | Candidate Universe / Scoring |
| volume minimum/strong ratio | file 05, `bt_target=true` | grid columns → `paramset.volume` | Scoring and volume guard logic |
| ATR min/max/ideal band | file 05, `bt_target=true` | grid columns → `paramset.risk` | Candidate Universe / Scoring |
| ROC and breakout thresholds | file 05, `bt_target=true` | grid columns → `paramset.setup` | Scoring |
| four score weights | file 05, `bt_target=true` | grid columns → `paramset.scoring.weights` | Scoring |
| top/secondary quantiles | file 05, `bt_target=true` | grid columns → `paramset.grouping` | PLAN Grouping |

`volume.strong_vol_ratio` was a real runtime input but was missing from the exhaustive registry summary. The owner registry and validator were corrected before it was admitted as an R2 axis.
