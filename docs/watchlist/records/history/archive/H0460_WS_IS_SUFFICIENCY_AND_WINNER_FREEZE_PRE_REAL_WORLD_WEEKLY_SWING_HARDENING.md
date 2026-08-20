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

## Statistical Reliability and Multiple-Testing Inputs

IS evaluation harus memperlakukan parameter search sebagai experiment family, bukan kumpulan hasil terbaik yang boleh dipilih tanpa penalty.

Wajib direkam sebelum winner freeze:

- complete preregistered candidate grid/search space;
- total candidate/trial count yang benar-benar dievaluasi, termasuk failed/abandoned trials dalam research lineage yang relevan;
- exact metric selection policy;
- date-clustered 95% confidence interval untuk average net return;
- Deflated Sharpe Ratio (`DSR`) atau equivalent selection-bias-adjusted performance statistic yang memperhitungkan number of trials dan non-normality;
- Probability of Backtest Overfitting (`PBO`) menggunakan purged/combinatorial time-respecting partitions ketika sample/grid mencukupi untuk perhitungan yang sah.

Canonical acceptance:

- `lower_95ci_avg_ret_net_top > 0`;
- `DSR_probability > 0.95`;
- bila PBO computable, `PBO <= 0.20`;
- bila PBO tidak computable karena grid/sample terlalu kecil, state harus explicit `PBO_NOT_COMPUTABLE`; hal tersebut tidak menghapus kewajiban DSR/trial ledger dan tidak boleh dipresentasikan sebagai PBO PASS.

## Robust Parameter Plateau Rule

Winner tidak boleh dipilih hanya karena menjadi isolated numerical spike. Untuk parameter dimensions yang mempunyai meaningful ordered neighbors, immediate preregistered neighbors harus diperiksa.

Winner gagal robustness review bila nearest reasonable parameter neighbors secara konsisten kehilangan positive net edge, mengalami severe ranking inversion, atau tail-risk breakdown sehingga winner tampak bergantung pada satu titik tuning yang rapuh. Jika parameter bersifat categorical dan neighborhood tidak bermakna, evidence harus menulis `NOT_APPLICABLE` dengan rationale.

## Baseline-vs-Challenger Incremental Proof

Feature-family challenger hanya dapat menggantikan canonical baseline bila pada same IS dates/friction:

- challenger melewati seluruh acceptance floor sendiri;
- incremental net edge/ranking utility terhadap baseline positif dan bukan hanya hasil satu subperiod;
- additional complexity tercatat sebagai additional selection trial;
- winner freeze memilih satu identity; unsuccessful challengers tidak boleh disembunyikan dari trial count.

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

## Additional IS Acceptance Floors — Economic, Benchmark, Tail, and Top-K

Selain floor yang sudah ada:

### Economic significance and uncertainty

- `avg_ret_net_top >= 0.0025`;
- `lower_95ci_avg_ret_net_top > 0`.

### Benchmark-relative edge

Bila primary benchmark proof input tersedia dan production qualification dituju:

- `avg_excess_ret_vs_ihsg > 0`;
- `lower_95ci_avg_excess_ret_vs_ihsg > 0`;
- `avg_selection_uplift_vs_eligible_universe > 0`.

Tidak tersedianya required benchmark input menghasilkan **INSUFFICIENT EVIDENCE** untuk benchmark-relative production proof, bukan synthetic benchmark PASS.

### Tail-risk floor

- `p05_ret_net_top >= -0.08`;
- `expected_shortfall_05_ret_net_top >= -0.10`;
- date-level equal-reference-notional `max_drawdown <= 0.20`;
- post-entry unresolved exposure count harus `0` untuk IS PASS kecuali exposure telah diberi conservative terminal-loss treatment dan acceptance tetap lulus dengan treatment tersebut.

MAE dan losing-streak metrics wajib dilaporkan dan dibandingkan dengan declared trade-plan risk; material unexplained breach menghasilkan finding sebelum winner freeze.

### Top-K ranking utility

Untuk Top-1, Top-3, Top-5 bila sample tersedia:

- average net return masing-masing harus `> 0`;
- Top-1 dan Top-3 tidak boleh menunjukkan persistent underperformance terhadap `ALL_QUALIFIED`;
- insufficient Top-K sample harus dilaporkan dan tidak boleh diisi synthetic observations.

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

## Challenger Market-Fact Dependency Rule

Research/IS tidak boleh menjadi jalur pintas untuk membuat market feature di Watchlist.

- Challenger yang membutuhkan factual feature baru **MUST** preregister kebutuhan tersebut sebagai Market Data dependency sebelum official candidate evaluation; locally derived substitute feature tidak boleh dipakai sebagai canonical comparable evidence.
- Hypothesis boleh tetap tercatat sebagai research candidate ketika required producer fact belum tersedia, tetapi candidate tersebut tidak boleh menerima official IS/OOS winner status dari dataset buatan Watchlist.
- Tersedianya producer fact baru hanya menyelesaikan data dependency; penggunaannya sebagai gate/score/ranking masih memerlukan preregistered challenger/strategy identity dan seluruh selection-bias/OOS proof yang berlaku.
- Trial inventory harus membedakan candidate yang benar-benar dievaluasi dengan authoritative inputs dari hypothesis yang `BLOCKED_BY_MARKET_DATA_DEPENDENCY`, sehingga trial count/proof tidak menyamarkan unavailable data sebagai failed strategy.

## Weekday / Calendar-Anomaly Challenger Rule

Weekday effect bukan bagian dari canonical baseline dan tidak boleh disisipkan sebagai tuning shortcut.

- Candidate yang menggunakan signal weekday, preferred entry weekday, preferred exit weekday, atau calendar anomaly harus preregistered sebagai explicit challenger sebelum outcome dibaca.
- Weekday value sendiri harus berasal dari authoritative trading-calendar/session identity; Watchlist tidak membangun calendar fact paralel.
- Setiap weekday challenger dihitung dalam experiment/trial family untuk DSR/PBO/multiple-testing controls dan harus menunjukkan incremental edge/ranking/downside benefit terhadap weekday-neutral baseline.
- Weekday challenger yang lolos IS belum boleh mengubah production strategy sampai melewati untouched OOS, adverse friction, dan forward shadow dengan identity yang sama.
- Temuan post-hoc seperti “Selasa lebih bagus” atau “Jumat lebih baik untuk exit” tidak boleh menjadi rule tanpa controlled new identity dan fresh proof.
