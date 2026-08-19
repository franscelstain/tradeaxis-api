# Watchlist Scope Lock

## Current Active Scope

- domain: `watchlist`
- active policy: `weekly_swing`
- active layers: `PLAN`, `RECOMMENDATION`, `CONFIRM`

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
- CONFIRM sebagai pemeriksaan current actionability terhadap Top Picks sebelum keputusan beli manual;
- IS/OOS/shadow proof untuk membuktikan bahwa strategy mempunyai positive expected net return setelah realistic trading friction dan downside yang terkendali.

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
6. Top Pick EOD hanya boleh ditampilkan sebagai **actionable buy consideration** setelah CONFIRM pada canonical entry window lulus.
7. Target strategy adalah positive expected net return setelah biaya dan slippage yang realistis dengan downside terkontrol; target ini bukan jaminan bahwa setiap trade akan untung.
