# Weekly Swing Strategy Decision — Qualified Top Picks

> **Current-status annotation — partially superseded**
>
> The clauses that made successful D+1 CONFIRM or CONFIRM-dependent full-flow shadow mandatory for core Top Picks usability/production proof are superseded by `WS_STRATEGY_DECISION_OPTIONAL_CONFIRM_NON_BLOCKING.md`. Final Top Picks remain valid without CONFIRM; CONFIRM is an optional capability proof and may be unavailable without failing the core Weekly Swing product. All other qualified-Top-Picks decisions remain in force unless separately superseded.

## Decision

Weekly Swing diarahkan menjadi satu product flow yang jelas:

`trusted EOD Market Data → PLAN recommendation candidates → QUALIFIED RECOMMENDATION → ranked TOP PICKS → D+1 CONFIRM actionability → manual buy decision`

Keputusan canonical:

1. istilah **TOP PICKS** hanya dimiliki final RECOMMENDATION;
2. PLAN tidak memakai PRIMARY/SECONDARY fallback; canonical candidate states adalah `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, dan `AVOID`;
3. final recommendation tidak memakai quota tetap atau target count; seluruh dan hanya candidate yang lulus qualification gate menjadi Top Picks;
4. recommendation boleh kosong;
5. candidate yang kehilangan/missing feature yang dipakai active hard gate atau active score formula fail-closed dari recommendation path;
6. canonical `score_total` memakai normalized weighted-sum atas momentum, breakout/setup, participation/volume, dan risk quality dengan frozen transforms/weights;
7. canonical `recommendation_score = PLAN score_total`; rescore kedua yang opaque dilarang;
8. recommendation ranking tidak dipengaruhi capital/affordability;
9. optional affordability/lot information hanya enrichment setelah ranking dan tidak boleh mengubah membership/rank;
10. canonical initial-entry session adalah trading day setelah EOD recommendation; Top Pick hanya boleh disajikan sebagai actionable buy bila CONFIRM pada entry window lulus;
11. CONFIRM hanya berlaku pada final Top Picks dan menentukan current actionability tanpa menulis ulang historical EOD recommendation;
12. IS/OOS proof harus mengevaluasi **final recommendation output**, bukan PLAN proxy;
13. production qualification memakai baseline realistic transaction-cost profile, non-zero adverse slippage, dan adverse-friction stress; zero-slippage hanya diagnostic/legacy comparison;
14. ranking usefulness ikut dibuktikan, bukan hanya aggregate recommendation return;
15. OOS-qualified strategy belum production-ready sampai full-flow forward shadow pada exact frozen strategy/evaluation identity lulus;
16. canonical exit evaluation boleh memakai causal `STOP_TARGET_TIME` atau `SEQUENTIAL_SIGNAL_NEXT_OPEN`, tetapi hanya satu family boleh aktif pada satu production identity dan perubahan family membutuhkan proof baru.

## Rationale

Keputusan ini mengikat strategy dengan output yang benar-benar digunakan pengguna: saham yang cukup layak untuk dibeli, urutan kualitasnya, dan apakah entry masih valid ketika pengguna hendak bertindak.

Quality threshold lebih penting daripada jumlah output. Candidate classification disederhanakan karena PRIMARY/SECONDARY tidak lagi mempunyai fungsi keputusan setelah quota/fallback dihapus.

Complete active feature requirement menjaga score comparability. Explicit D+1 CONFIRM menjaga agar real-use decision tidak menyimpang dari next-open/entry-band semantics yang dibuktikan.

## Evidence-Sufficiency Rationale

Acceptance menilai distinct recommendation days selain raw trade count agar sample tidak terlihat besar hanya karena banyak picks terkonsentrasi pada sedikit tanggal. Forward shadow mempunyai minimum time window dan harus diperpanjang bila actionable observation belum cukup; kekurangan sample bukan PASS.

## Affected Canonical Strategy Owners

This decision revises or introduces the following canonical owners:

- `../../authority/strategy/WS_SCOPE_AND_SUCCESS_CRITERIA.md`;
- `../../authority/strategy/WS_PRODUCT_OBJECTIVE_AND_LAYERS.md`;
- `../../authority/strategy/WS_RUNTIME_FLOW.md`;
- `../../authority/strategy/WS_CANDIDATE_ELIGIBILITY_AND_SETUP.md` (new owner);
- `../../authority/strategy/WS_PLAN_SCORING_AND_TRADE_PLAN.md`;
- `../../authority/strategy/WS_CANDIDATE_CLASSIFICATION.md` (supersedes prior dynamic-selection semantics);
- `../../authority/strategy/WS_D1_CONFIRM_ACTIONABILITY.md`;
- `../../authority/strategy/WS_HISTORICAL_EVALUATION_STRATEGY.md`;
- `../../authority/strategy/WS_TOP_PICKS_RECOMMENDATION.md`;
- `../../authority/strategy/WS_TOP_PICKS_QUALIFICATION_AND_RANKING.md`;
- `../../authority/strategy/WS_IS_SUFFICIENCY_AND_WINNER_FREEZE.md`;
- `../../authority/strategy/WS_OOS_STRESS_SHADOW_AND_PRODUCTION_PROOF.md`.

The prior authoritative versions are preserved under `../history/superseded/2026-08-16_pre-qualified-top-picks-strategy/`.

## Compatibility Impact

Keputusan ini adalah breaking strategy revision terhadap technical contracts yang masih menggunakan PLAN `TOP_PICKS`, PRIMARY/SECONDARY tiers, capital-aware membership, missing-score zero-fill, generic dynamic recommendation count, non-recommended CONFIRM, atau backtest PLAN-priority proxy.

Implementation harus di-align pada fase berikutnya; evidence lama tetap historical dan tidak ditulis ulang.

## Current Evidence Interpretation

B01 remains the strongest existing candidate/evidence chain and must be preserved. It is **not rejected retroactively**.

However, because this decision changes the product object under test, candidate completeness, recommendation semantics, ranking proof, realistic-friction requirements, and CONFIRM actionability, B01's prior IS/OOS/shadow evidence cannot by itself authorize production use of the revised strategy.

Existing B01 identity should be used as the first implementation/revalidation anchor, not silently relabeled as a pass for the new strategy.
