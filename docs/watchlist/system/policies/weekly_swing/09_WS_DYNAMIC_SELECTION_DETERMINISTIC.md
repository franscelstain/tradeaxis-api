# 09 — WS Dynamic Selection (Deterministic)

## Scope

Dokumen ini menetapkan aturan deterministic selection untuk **PLAN group semantics** pada Weekly Swing.

Dokumen ini mengunci:
- candidate ordering yang relevan untuk PLAN;
- dynamic target count untuk group semantics PLAN;
- final group mapping seperti `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, dan `AVOID`;
- tie-breaker deterministic pada selection PLAN.

Dokumen ini **tidak** mengunci final recommendation membership.

## Boundary Note — Dynamic Selection vs Recommendation

Dynamic selection pada dokumen ini hanya berlaku untuk penetapan group semantics PLAN.

Target dinamis pada dokumen ini tidak boleh dibaca sebagai target recommendation count.

Akibatnya:
- jumlah `TOP_PICKS` bukan jumlah recommendation final;
- jumlah `SECONDARY` bukan jumlah recommendation final;
- recommendation dapat kosong walaupun dynamic selection menghasilkan item pada group prioritas PLAN.

## Relationship to Recommendation

`TOP_PICKS` dan `SECONDARY` adalah group hasil PLAN.

Kedua group tersebut merepresentasikan prioritas PLAN, tetapi **tidak identik** dengan final recommendation set.

## Tie-Breaker Rule

Tie-breaker pada dokumen ini hanya berlaku untuk selection dan ordering PLAN.

Jika recommendation membutuhkan tie-breaker tambahan, tie-breaker tersebut **MUST** didefinisikan pada dokumen recommendation.

## C171 Optional TOP Score-Cap Semantics

An immutable C171 remediation paramset may define `grouping.top_max_score_total`. The field is an optional decision-time upper bound for `TOP_PICKS` membership only.

When present:
- the TOP quantile pool contains only scored candidates with `score_total <= top_max_score_total`;
- `top_cutoff_score` is recalculated from that bounded TOP pool on the same `asof_eod_date`;
- candidates above the cap are not eligible for `TOP_PICKS`, but may still be considered by downstream PLAN grouping rules such as `SECONDARY` when they meet those rules;
- replacement candidates are selected by the existing deterministic ranking from the same-day scored universe;
- the cutoff manifest must preserve the active cap, bounded TOP-pool count, and deterministic bounded-pool identity/hash.

The cap must not be applied after trade returns are known, must not depend on OOS results, and must not blacklist specific tickers, sectors, or months. A legacy paramset that omits the field uses the historical effective maximum `1.0`.

## Final Rules

1. Dokumen ini hanya mengunci deterministic selection untuk PLAN group semantics.
2. `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, dan `AVOID` adalah output PLAN, bukan output RECOMMENDATION.
3. Dynamic selection pada dokumen ini **MUST NOT** dipakai untuk menyimpulkan final recommendation count.
4. Recommendation set **MAY** kosong walaupun `TOP_PICKS` dan/atau `SECONDARY` tidak kosong.
5. Recommendation membership **MUST** ditentukan oleh dokumen recommendation, bukan oleh dokumen ini.
