# Watchlist Weekly Swing — Canonical Runtime Flow

## Purpose

Dokumen ini menetapkan urutan canonical Weekly Swing dari EOD candidate generation sampai decision-support sebelum pembelian manual.

## Lifecycle Position

- **Role:** runtime orchestration for `WS-S01..WS-S05`.
- **Consumes:** frozen `WS-S00` scope/objective.
- **Produces:** canonical dependency `Market Data → PLAN → RECOMMENDATION/TOP PICKS → CONFIRM`.
- **Completion:** setiap valid trade date mempunyai deterministic outcome termasuk no-pick/no-actionable state.

## A. PLAN — EOD Candidate Formation

Pada akhir EOD untuk `trade_date`, Weekly Swing membentuk PLAN dari authoritative Market Data snapshot untuk tanggal tersebut.

PLAN harus final dan immutable sebelum RECOMMENDATION dibentuk.

PLAN berisi `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID`, serta plan-derived levels. PLAN tidak memiliki final Top Picks.

## B. RECOMMENDATION — Final Qualified Top Picks

Setelah PLAN immutable tersedia, RECOMMENDATION harus:

1. membaca candidate PLAN untuk `trade_date` yang sama;
2. menerapkan final recommendation qualification gates;
3. mempertahankan semua candidate yang lulus dan menolak semua candidate yang gagal;
4. mengurutkan seluruh candidate yang lulus menjadi `TOP_PICKS` rank `1..N`;
5. mengizinkan `N = 0`.

Recommendation tidak membaca CONFIRM dan tidak bergantung pada capital input untuk menentukan membership atau rank.

## C. CONFIRM — Current Actionability Overlay

Canonical initial-entry session adalah next trading day setelah EOD recommendation. CONFIRM hanya berlaku pada ticker yang berada pada final Top Picks untuk entry window yang masih sah.

CONFIRM:
- membaca binding recommendation dan PLAN yang sama;
- tidak menambah ticker baru;
- tidak mengubah recommendation score atau rank;
- mengevaluasi apakah entry/setup Top Pick masih valid pada kondisi terbaru yang diizinkan strategy.

Hasil CONFIRM mempunyai dua makna product-level:
- **actionable** — Top Pick masih memenuhi current-entry conditions;
- **not actionable** — Top Pick historis tetap ada, tetapi tidak layak ditindaklanjuti sekarang.

Jika CONFIRM belum tersedia, recommendation tetap sah sebagai EOD ranked watchlist tetapi belum memiliki current-actionability proof.

## D. Consumer Decision Semantics

Untuk keputusan beli manual:

- `TOP_PICK + actionable CONFIRM` adalah kondisi terkuat yang dapat diberikan Watchlist;
- `TOP_PICK tanpa CONFIRM` tetap merupakan recommendation EOD tetapi **belum boleh dipresentasikan sebagai actionable buy**;
- ticker non-recommended tidak boleh dipromosikan oleh CONFIRM menjadi alternatif buy recommendation.

## E. Canonical Output Relationship

Valid relationship:

`PLAN → RECOMMENDATION/TOP_PICKS → optional CONFIRM actionability`

Invalid relationship:
- RECOMMENDATION dari ticker di luar PLAN;
- TOP PICKS dibentuk langsung dari PLAN candidate state tanpa final qualification;
- CONFIRM menambah recommendation baru;
- CONFIRM mengubah historical recommendation membership/rank;
- capital mengubah recommendation quality ordering.

## Final Invariants

1. PLAN harus immutable sebelum recommendation.
2. Final Top Picks hanya dimiliki RECOMMENDATION.
3. Recommendation count sama dengan jumlah candidate yang benar-benar lulus qualification gate dan boleh nol.
4. Recommendation ranking harus deterministic dan capital-independent.
5. CONFIRM hanya mengevaluasi final Top Picks.
6. CONFIRM dapat mengubah actionability state tetapi tidak historical recommendation state.
7. Expired entry window tidak boleh dihidupkan kembali tanpa explicit carry-forward strategy identity dan proof baru.
