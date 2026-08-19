# 22 — WS Recommendation Overview

## Purpose

RECOMMENDATION adalah final decision-support selection layer Weekly Swing. Layer ini menentukan saham mana yang cukup layak untuk disebut **Top Picks** dan mengurutkannya dari kualitas tertinggi ke terendah.

## Lifecycle Position

- **Stage:** `WS-S04` — Final Recommendation and Ranked Top Picks.
- **Consumes:** immutable PLAN.
- **Produces:** semantic definition of final qualified `TOP_PICKS`, including valid empty set.
- **Next:** complete `WS-S04` qualification/ranking, then enter `WS-S05` CONFIRM.

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
- current CONFIRM actionability bila CONFIRM tersedia.

## Capital Independence

Modal pengguna, affordability, atau jumlah lot tidak mengukur kualitas saham dan **tidak boleh**:
- menambah atau menghapus Top Pick;
- mengubah recommendation score;
- mengubah recommendation rank.

Optional capital/lot information boleh ditampilkan setelah Top Picks selesai sebagai informational enrichment saja.

## Relationship to CONFIRM

Recommendation dibentuk tanpa CONFIRM.

CONFIRM tidak mengubah historical recommendation membership atau rank. CONFIRM hanya menentukan apakah Top Pick masih actionable pada saat pemeriksaan dilakukan.

## Final Rules

1. Final Top Picks adalah seluruh dan hanya candidate yang lulus recommendation qualification.
2. Top Picks count tidak mempunyai fixed quota dan boleh nol.
3. Ranking Top Picks harus merepresentasikan canonical PLAN quality ordering setelah final qualification.
4. Capital tidak memengaruhi kualitas atau ranking recommendation.
5. Recommendation harus dapat dievaluasi langsung pada backtest/OOS; PLAN candidate state tidak boleh menjadi proxy untuk final recommendation proof.
