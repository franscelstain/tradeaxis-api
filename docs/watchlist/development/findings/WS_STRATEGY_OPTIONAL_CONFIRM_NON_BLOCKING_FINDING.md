# Weekly Swing Strategy — Optional CONFIRM Non-Blocking Finding

## Objective

Menilai apakah canonical Weekly Swing benar-benar memperlakukan D+1 CONFIRM sebagai capability opsional sehingga core Top Picks tetap dapat dibangun, diuji, dan digunakan sebagai EOD decision-support ketika current-entry data CONFIRM belum tersedia.

## Material Findings

### F1 — Runtime wording still made CONFIRM appear mandatory for actionable use

Sebagian dokumen menyatakan CONFIRM optional, tetapi bagian lain masih menyatakan Top Pick tanpa CONFIRM belum boleh dipresentasikan sebagai actionable buy consideration.

**Impact:** HIGH — implementer dapat membuat core Weekly Swing bergantung pada ketersediaan data CONFIRM.

### F2 — Lifecycle treated `WS-S05` as mandatory runtime completion

Runtime-complete sebelumnya didefinisikan sebagai `WS-S00..WS-S05`, sehingga ketiadaan current-entry snapshot berpotensi dibaca sebagai runtime yang belum selesai.

**Impact:** CRITICAL — missing D+1 data dapat memblokir penyelesaian core Watchlist walaupun EOD Top Picks sudah valid.

### F3 — Production proof required CONFIRM-dependent forward shadow

`WS-S10` dan `WS-S11` sebelumnya mewajibkan actionable-CONFIRM sample untuk production-use review.

**Impact:** CRITICAL — core Top Picks dapat dinyatakan NOT READY hanya karena optional CONFIRM data belum tersedia.

### F4 — Missing/stale CONFIRM input was not separated cleanly from a negative decision

Tanpa explicit non-terminal availability state, implementation dapat memetakan missing snapshot menjadi failure atau `NOT_ACTIONABLE` walaupun rule belum dapat dievaluasi secara sah.

**Impact:** HIGH — menghasilkan false negative dan membuat capability sulit dipakai saat data datang terlambat.

## Required Resolution

- core runtime selesai pada final ranked Top Picks (`WS-S04`);
- `WS-S05` menjadi optional non-blocking branch;
- missing/stale/incomplete decision-time data menghasilkan `UNAVAILABLE_RETRYABLE`, bukan strategy failure dan bukan `NOT_ACTIONABLE`;
- CONFIRM dapat dievaluasi ulang selama canonical entry window ketika valid data kemudian tersedia;
- production proof core tidak bergantung pada availability CONFIRM;
- CONFIRM mempunyai proof/status sendiri bila capability tersebut ingin dinyatakan proven untuk user-facing current-actionability.
