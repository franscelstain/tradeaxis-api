# Watchlist Weekly Swing — Canonical Runtime Flow

## Purpose

Dokumen ini menetapkan urutan canonical Weekly Swing dari EOD candidate generation sampai final ranked Top Picks, dengan optional CONFIRM sebagai current-actionability overlay yang tidak memblokir core product.

## Lifecycle Position

- **Core runtime:** `WS-S01..WS-S04`.
- **Optional branch:** `WS-S05` CONFIRM.
- **Consumes:** frozen `WS-S00` scope/objective.
- **Produces:** canonical dependency `Market Data → PLAN → RECOMMENDATION/TOP PICKS`, lalu optional `→ CONFIRM` bila valid decision-time data tersedia.
- **Core completion:** setiap valid trade date mempunyai deterministic EOD outcome termasuk valid no-pick state, tanpa membutuhkan CONFIRM.

## A. PLAN — EOD Candidate Formation

Pada akhir EOD untuk `trade_date`, Weekly Swing membentuk PLAN hanya dari intake yang lulus `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`. Untuk new current PLAN, producer response harus readable, fresh, dan mempunyai `effective_trade_date` yang sama dengan requested `trade_date`. Explicit stale/prior-date fallback tidak boleh disamarkan sebagai PLAN tanggal baru.

PLAN harus mengikat publication/read-model identity yang diterima dan final/immutable sebelum RECOMMENDATION dibentuk.

PLAN berisi `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, `AVOID`, serta plan-derived levels. PLAN tidak memiliki final Top Picks.

## B. RECOMMENDATION — Final Qualified Top Picks

Setelah PLAN immutable tersedia, RECOMMENDATION harus:

1. membaca candidate PLAN untuk `trade_date` yang sama;
2. menerapkan final recommendation qualification gates;
3. mempertahankan semua candidate yang lulus dan menolak semua candidate yang gagal;
4. mengurutkan seluruh candidate yang lulus menjadi `TOP_PICKS` rank `1..N`;
5. mengizinkan `N = 0`.

Recommendation tidak membaca CONFIRM dan tidak bergantung pada capital input untuk menentukan membership atau rank.

Setelah final Top Picks terbentuk, **core Weekly Swing runtime untuk trade date tersebut selesai**. CONFIRM bukan prerequisite untuk menyimpan, mempublikasikan, membaca, atau membuktikan recommendation EOD.

## B1. Recommendation Availability and Entry Cutoff

Final Top Picks runtime record wajib menyimpan `recommendation_available_at` dan effective-dated intended entry session.

Canonical `D+1 open` opportunity hanya sah bila:

`recommendation_available_at <= earliest_entry_time(D+1) - 30 minutes`

`earliest_entry_time(D+1)` harus berasal dari governed effective-dated exchange session/calendar fact, bukan hardcoded current clock schedule.

Jika SLA tersebut gagal:

- Top Picks tetap disimpan sebagai EOD recommendation history;
- system tidak boleh mengklaim bahwa user masih dapat memperoleh canonical D+1 open;
- production monitoring mencatat `LATE_RECOMMENDATION_PUBLICATION`;
- historical/shadow evaluation hanya boleh memakai later causal fill rule bila data dan strategy identity memang mendukungnya; otherwise canonical entry dinyatakan non-executable.

Operational lateness tidak boleh disembunyikan dengan backdating `recommendation_available_at`.

## C. CONFIRM — Optional Current Actionability Overlay

Canonical initial-entry session adalah next trading day setelah EOD recommendation. CONFIRM hanya berlaku pada ticker yang berada pada final Top Picks untuk entry window yang masih sah.

CONFIRM:
- membaca binding recommendation dan PLAN yang sama;
- tidak menambah ticker baru;
- tidak mengubah recommendation score atau rank;
- mengevaluasi current-entry condition hanya bila valid decision-time data tersedia.

Canonical product-level states:
- **NOT_REQUESTED** — CONFIRM belum diminta/dijalankan;
- **UNAVAILABLE_RETRYABLE** — valid current data belum tersedia; bukan failure dan boleh dicoba lagi selama entry window;
- **ACTIONABLE** — valid current data tersedia dan seluruh active gate lulus;
- **NOT_ACTIONABLE** — valid current data tersedia dan sedikitnya satu active actionability gate gagal;
- **EXPIRED_UNCONFIRMED** — entry window berakhir sebelum valid CONFIRM dapat dievaluasi.

Missing, stale, incomplete, delayed, atau temporarily unavailable current data **tidak boleh** dipetakan menjadi `NOT_ACTIONABLE` dan tidak boleh menggagalkan core Weekly Swing.

Jika state masih `UNAVAILABLE_RETRYABLE` dan valid data kemudian tersedia sebelum entry window berakhir, CONFIRM dapat dievaluasi ulang untuk menghasilkan `ACTIONABLE` atau `NOT_ACTIONABLE`.

## D. Consumer Decision Semantics

Untuk keputusan beli manual:

- `TOP_PICK` adalah qualified EOD recommendation yang sah;
- `TOP_PICK + ACTIONABLE` adalah Top Pick dengan tambahan current-actionability evidence;
- `TOP_PICK + NOT_ACTIONABLE` berarti valid EOD recommendation yang current-entry conditions-nya telah terbukti tidak layak pada saat CONFIRM;
- `TOP_PICK + NOT_REQUESTED/UNAVAILABLE_RETRYABLE/EXPIRED_UNCONFIRMED` tetap merupakan recommendation EOD, tetapi current actionability **unknown / not proven**, bukan negative decision;
- ticker non-recommended tidak boleh dipromosikan oleh CONFIRM menjadi alternatif buy recommendation.

Sistem tidak boleh mengklaim `ACTIONABLE` tanpa valid CONFIRM, tetapi ketiadaan label `ACTIONABLE` tidak membuat Top Pick menjadi gagal atau tidak valid sebagai EOD decision-support.

## E. Canonical Output Relationship

Core relationship:

`PLAN → RECOMMENDATION/TOP_PICKS`

Optional relationship:

`TOP_PICKS → optional CONFIRM actionability`

Invalid relationship:
- RECOMMENDATION dari ticker di luar PLAN;
- TOP PICKS dibentuk langsung dari PLAN candidate state tanpa final qualification;
- CONFIRM menjadi prerequisite untuk membentuk atau mempublikasikan Top Picks;
- missing CONFIRM data menggagalkan PLAN/RECOMMENDATION;
- CONFIRM menambah recommendation baru;
- CONFIRM mengubah historical recommendation membership/rank;
- capital mengubah recommendation quality ordering.

## Final Invariants

1. PLAN harus immutable sebelum recommendation.
2. Final Top Picks hanya dimiliki RECOMMENDATION.
3. Recommendation count sama dengan jumlah candidate yang benar-benar lulus qualification gate dan boleh nol.
4. Recommendation ranking harus deterministic dan capital-independent.
5. Core runtime selesai pada final Top Picks dan tidak bergantung pada CONFIRM.
6. CONFIRM hanya mengevaluasi final Top Picks sebagai optional overlay.
7. Data CONFIRM yang belum tersedia menghasilkan non-terminal availability state, bukan business failure.
8. `NOT_ACTIONABLE` memerlukan valid evaluated current data.
9. CONFIRM dapat mengubah current-actionability interpretation tetapi tidak historical recommendation state.
10. Expired entry window tidak boleh dihidupkan kembali tanpa explicit carry-forward strategy identity dan proof baru.
