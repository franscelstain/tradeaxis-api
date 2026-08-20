# Watchlist Weekly Swing — IS Sufficiency and Winner Freeze

## Purpose

Dokumen ini mengunci minimum evidence dan acceptance floor untuk menentukan apakah satu Weekly Swing strategy/paramset cukup robust untuk diteruskan dari IS calibration ke OOS proof.

Seluruh return metric mengacu pada **executable final Top Pick trades**, bukan PLAN candidate state.

## Lifecycle Position

- **Stage:** `WS-S07` — IS Sufficiency and Winner Freeze.
- **Consumes:** IS outcomes generated under `WS-S06`.
- **Produces:** `IS PASS + one frozen best-IS binding`, `IS FAIL`, or `INSUFFICIENT EVIDENCE`.
- **Next:** only one frozen PASS binding may enter `WS-S08` untouched OOS.

## Evaluation Binding

Selection, entry, exit, holding horizon, cost, slippage, dan executable-bar semantics mengikuti `../WS_HISTORICAL_EVALUATION_STRATEGY.md`.

Satu evaluation identity harus mengikat:
- strategy/paramset identity;
- historical Market Data identity;
- evaluation window;
- transaction-cost profile;
- slippage profile;
- entry/exit model;
- final recommendation algorithm identity.

Identity berbeda tidak boleh dibandingkan seolah evaluation yang sama.

## Minimum Metric Set

### A. Coverage and recommendation activity

Minimum:
- `picks_count` — executable final Top Pick trades;
- `days_covered` — trading dates yang strategy evaluation-nya selesai secara sah;
- `recommendation_days` — tanggal dengan minimal satu final Top Pick;
- `no_recommendation_days` — valid evaluation dates dengan zero Top Picks;
- `avg_ret_net_top`;
- `win_rate_top`.

No-recommendation date adalah outcome valid dan tidak boleh diubah menjadi synthetic trade hanya untuk menaikkan sample count.

### B. Return distribution and downside

Minimum:
- `median_ret_net_top`;
- `p25_ret_net_top`;
- `p75_ret_net_top`;
- `min_ret_net_top`;
- `max_ret_net_top`.

### C. Period stability

Minimum:
- `month_win_rate_min`;
- `month_avg_ret_net_min`;
- jumlah periode yang dievaluasi;
- jumlah periode yang gagal floor.

### D. Ranking quality

Karena produk menampilkan ordered Top Picks, evidence minimal juga harus mencakup:
- `rank1_picks_count`;
- `avg_ret_net_rank1`;
- `median_ret_net_rank1`;
- canonical score-vs-return rank correlation, menggunakan deterministic metric yang sama pada seluruh candidate;
- comparison return higher-ranked vs lower-ranked recommendation bucket.

Ranking metric definition dan bucket split harus dibekukan sebelum outcome dibaca.

## IS Acceptance Floors

Seluruh floor berikut harus lulus sebelum candidate dapat masuk best-IS ranking.

### 1. Sample sufficiency

Untuk default 2-year baseline:
- `picks_count >= 120`;
- `recommendation_days >= 40`.

`recommendation_days` mencegah sample terlihat besar hanya karena banyak picks terkonsentrasi pada sedikit tanggal.

Jika strategy yang sangat selektif tidak mencapai minimum sample, hasilnya adalah **INSUFFICIENT EVIDENCE**, bukan alasan menurunkan recommendation quality floor.

### 2. Coverage

- `days_covered >= ceil(0.70 * total_trading_days_in_window)`.

Valid no-recommendation day boleh dihitung covered bila seluruh strategy evaluation selesai secara sah.

### 3. Robust net return

- `avg_ret_net_top > 0`;
- `median_ret_net_top > 0`.

Return selalu net of baseline production cost/slippage profile yang dibekukan untuk evaluation identity.

### 4. Downside bound

- `p25_ret_net_top >= -0.03`.

`min_ret_net_top` wajib dilaporkan tetapi tidak menggantikan distribution/downside analysis.

### 5. Period stability

- `month_win_rate_min >= 0.45`;
- `month_avg_ret_net_min >= -0.01`.

### 6. Ranking usefulness

- `avg_ret_net_rank1 > 0`;
- `median_ret_net_rank1 >= 0`;
- canonical score-vs-return rank correlation **MUST NOT** negatif;
- higher-ranked recommendation bucket **MUST NOT** menunjukkan persistent return inversion terhadap lower-ranked bucket.

Jika ranking gate gagal, strategy boleh tetap menunjukkan aggregate edge tetapi belum terbukti mampu mengurutkan **Top Picks terbaik**.

## Best-IS Ranking Policy

Setelah seluruh acceptance floor lulus, candidate strategy/paramset diurutkan berdasarkan:

1. lebih tinggi `avg_ret_net_top`;
2. lebih tinggi `median_ret_net_top`;
3. lebih tinggi `avg_ret_net_rank1`;
4. lebih tinggi `month_win_rate_min`;
5. lebih tinggi `p25_ret_net_top`;
6. lebih baik ranking-quality metric;
7. stable candidate identity sebagai deterministic final tie-break.

Average return tidak boleh menjadi satu-satunya optimization objective.

## Evidence Sufficiency Rule

Evidence harus cukup untuk:
- merecompute seluruh metric;
- membuktikan exact final recommendation membership per date;
- membedakan recommendation dari PLAN candidate yang tidak recommended;
- membedakan executable trade dari skipped trade;
- membuktikan no-recommendation dates;
- merecompute ranking-quality metrics;
- mengikat hasil ke exact strategy/evaluation identity;
- menghasilkan outcome sama pada replay.
