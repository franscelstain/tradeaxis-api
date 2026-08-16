# C171 PLAN Remediation Guard Extension

> **Doc Role:** HISTORICAL / RESEARCH ADDENDA
> **Authority:** NON-CANONICAL. Preserved verbatim from the previous mixed document during architecture separation.

## C171 Remediation Guard Extension

The candidate-guard stage may consume these optional immutable decision-time bounds:
- `liquidity.max_dv20_idr`;
- `volume.max_vol_ratio`.

When present, rows above the bounds fail before scoring with `WS_LIQ_HIGH` or `WS_VOLR_HIGH`, following the canonical priority in file 15. When absent, legacy lower-bound behavior is unchanged. `grouping.top_max_score_total` is not a universe guard; it is applied later during deterministic TOP-pool construction under file 09. None of these fields may be evaluated from realized return or future path data.

