# C171 Dynamic Selection Score-Cap Extension

> **Doc Role:** HISTORICAL / RESEARCH ADDENDA
> **Authority:** NON-CANONICAL. Preserved verbatim from the previous mixed document during architecture separation.

## C171 Optional TOP Score-Cap Semantics

An immutable C171 remediation paramset may define `grouping.top_max_score_total`. The field is an optional decision-time upper bound for `TOP_PICKS` membership only.

When present:
- the TOP quantile pool contains only scored candidates with `score_total <= top_max_score_total`;
- `top_cutoff_score` is recalculated from that bounded TOP pool on the same `asof_eod_date`;
- candidates above the cap are not eligible for `TOP_PICKS`, but may still be considered by downstream PLAN grouping rules such as `SECONDARY` when they meet those rules;
- replacement candidates are selected by the existing deterministic ranking from the same-day scored universe;
- the cutoff manifest must preserve the active cap, bounded TOP-pool count, and deterministic bounded-pool identity/hash.

The cap must not be applied after trade returns are known, must not depend on OOS results, and must not blacklist specific tickers, sectors, or months. A legacy paramset that omits the field uses the historical effective maximum `1.0`.

