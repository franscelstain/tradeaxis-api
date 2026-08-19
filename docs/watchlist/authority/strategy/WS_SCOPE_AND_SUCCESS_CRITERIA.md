# Watchlist Weekly Swing — Scope and Success Criteria

## Lifecycle Position

- **Stage:** `WS-S00` — Scope and Success Lock.
- **Produces:** frozen product boundary, success meaning, Top Picks semantics, dan out-of-scope guard.
- **Next:** `WS-S01` trusted Market Data binding.

## Current Active Scope

- domain: `watchlist`
- active policy: `weekly_swing`
- core layers: `PLAN`, `RECOMMENDATION`
- optional enhancement: `CONFIRM`

## Product Scope

Weekly Swing hanya berfungsi sebagai decision-support watchlist untuk memilih saham IDX yang paling layak dipertimbangkan untuk pembelian swing dengan maximum holding horizon 5 trading day.

Output utama produk adalah **qualified recommendations yang diurutkan sebagai TOP PICKS**. Jumlah Top Picks mengikuti kualitas kandidat yang tersedia dan boleh bernilai nol.

## In Scope

- konsumsi Market Data EOD yang sah, konsisten, point-in-time, dan dapat direplay;
- pembentukan candidate PLAN Weekly Swing;
- eligibility, setup, risk, scoring, dan ranking kandidat;
- final recommendation qualification;
- ranked Top Picks;
- PLAN-derived entry dan predeclared exit/risk plan information;
- optional CONFIRM sebagai pemeriksaan current actionability terhadap Top Picks ketika decision-time data tersedia;
- IS/OOS/core-shadow proof untuk membuktikan bahwa strategy mempunyai positive expected net return setelah realistic trading friction dan downside yang terkendali;
- optional CONFIRM proof bila capability current-actionability ingin dinyatakan proven.

## Out of Scope

- portfolio construction atau portfolio optimization;
- broker execution atau automatic order placement;
- order lifecycle;
- position management setelah pembelian;
- holdings / realized-unrealized PnL / trade journal;
- Market Data acquisition/internal processing;
- provider-specific acquisition logic;
- policy trading selain `weekly_swing`.

## Hard Boundary Rules

1. Watchlist hanya memberi decision-support dan tidak melakukan transaksi.
2. `weekly_swing` adalah satu-satunya active watchlist policy.
3. Watchlist hanya mengonsumsi authoritative Market Data product dan tidak mendefinisikan ulang fakta pasar upstream.
4. Recommendation quality tidak boleh dikorbankan untuk memenuhi jumlah picks tertentu.
5. Bila tidak ada kandidat yang melewati seluruh qualification gate, output yang benar adalah **NO QUALIFIED TOP PICKS**.
6. Final Top Picks adalah output core Weekly Swing yang sah walaupun CONFIRM belum diminta atau current-entry data belum tersedia.
7. CONFIRM hanya menambah current-actionability evidence ketika data yang sah tersedia; ketidaktersediaan CONFIRM tidak boleh menggagalkan, menghapus, atau mererank Top Picks.
8. `NOT_ACTIONABLE` hanya boleh dihasilkan bila valid CONFIRM data tersedia dan active gate benar-benar dapat dievaluasi; missing/stale/incomplete data bukan negative decision.
9. Target strategy adalah positive expected net return setelah biaya dan slippage yang realistis dengan downside terkontrol; target ini bukan jaminan bahwa setiap trade akan untung.
