# Watchlist Weekly Swing — Top Picks Recommendation

## Purpose

RECOMMENDATION adalah final decision-support selection layer Weekly Swing. Layer ini menentukan saham mana yang cukup layak untuk disebut **Top Picks** dan mengurutkannya dari kualitas tertinggi ke terendah.

## Lifecycle Position

- **Stage:** `WS-S04` — Final Recommendation and Ranked Top Picks.
- **Consumes:** immutable PLAN.
- **Produces:** semantic definition of final qualified `TOP_PICKS`, including valid empty set.
- **Next:** core proof may continue to `WS-S06`; optional `WS-S05` CONFIRM may run independently when valid decision-time data is available.

## Source

Recommendation hanya boleh membaca immutable PLAN output untuk `trade_date` yang sama.

Source candidate hanya `RECOMMENDATION_CANDIDATES`.

`WATCH_ONLY` dan `AVOID` tidak boleh menjadi final recommendation pada run yang sama.

## Qualified Recommendation Principle

Recommendation memakai **qualification**, bukan quota.

Satu ticker masuk final Top Picks hanya jika seluruh mandatory recommendation gate lulus. Bila 0 ticker lulus, output Top Picks harus kosong. Bila 12 ticker lulus, seluruh 12 ticker tetap qualified recommendations dan diurutkan `1..12`.

UI boleh menonjolkan sebagian rank untuk kenyamanan, tetapi presentation limit tidak boleh mengubah strategy membership.

## Top Picks Meaning

`TOP_PICKS` adalah nama final recommendation set dan tidak digunakan untuk PLAN candidate state.

Setiap Top Pick harus mempunyai:
- recommendation rank;
- canonical quality score;
- reason/explanation yang dapat diturunkan dari rule strategy;
- PLAN entry dan predeclared exit/risk-plan binding;
- optional current-actionability state bila CONFIRM tersedia; absence of CONFIRM does not reduce recommendation validity.

## Capital Independence

Modal pengguna, affordability, atau jumlah lot tidak mengukur kualitas saham dan **tidak boleh**:
- menambah atau menghapus Top Pick;
- mengubah recommendation score;
- mengubah recommendation rank.

Optional capital/lot information boleh ditampilkan setelah Top Picks selesai sebagai informational enrichment saja.

## Relationship to CONFIRM

Recommendation dibentuk tanpa CONFIRM.

CONFIRM tidak mengubah historical recommendation membership atau rank. CONFIRM adalah optional current-actionability overlay. Missing/stale/incomplete CONFIRM data tidak boleh membuat Top Pick gagal atau menjadi `NOT_ACTIONABLE`; Top Pick tetap sah sebagai EOD recommendation dan CONFIRM dapat dicoba lagi bila valid data tersedia dalam entry window.

## Final Rules

1. Final Top Picks adalah seluruh dan hanya candidate yang lulus recommendation qualification.
2. Top Picks count tidak mempunyai fixed quota dan boleh nol.
3. Ranking Top Picks harus merepresentasikan canonical PLAN quality ordering setelah final qualification.
4. Capital tidak memengaruhi kualitas atau ranking recommendation.
5. Recommendation harus dapat dievaluasi langsung pada backtest/OOS; PLAN candidate state tidak boleh menjadi proxy untuk final recommendation proof.
