# 17 — WS Walk-Forward / Out-of-Sample Proof

## Purpose

Dokumen ini mengunci minimum proof bahwa frozen Weekly Swing strategy yang dipilih pada IS tetap menghasilkan qualified Top Picks dengan positive net edge, acceptable downside, dan useful ranking pada data OOS yang tidak dipakai untuk tuning.

OOS-qualified tidak otomatis berarti production-ready karena real-use flow juga membutuhkan CONFIRM actionability validation.

## Deterministic Split

Evaluation menggunakan explicit chronological `(from_date, to_date)` range.

Canonical minimum split:
- IS = 70% awal ordered trading dates;
- OOS = 30% sisanya;
- split berbasis waktu, bukan random.

Untuk `N` ordered trading dates:
- `is_count = floor(0.70 * N)`;
- `oos_count = N - is_count`;
- IS adalah prefix;
- OOS adalah untouched suffix;
- tidak boleh overlap, randomization, hidden gap, atau post-result date removal.

## OOS Protocol

### Step 1 — Calibrate on IS

- evaluate seluruh preregistered candidate grid menggunakan exact candidate eligibility, PLAN scoring, dan final recommendation algorithm;
- apply IS acceptance floor;
- freeze satu best-IS strategy/paramset binding.

### Step 2 — Evaluate Frozen Binding on OOS

- replay exact frozen PLAN + RECOMMENDATION logic pada complete OOS suffix;
- tidak boleh retuning recommendation threshold, component transform, score weights, ranking rule, cost assumption, atau parameter lain setelah OOS outcome dibaca;
- hanya final Top Picks menjadi canonical evaluated trades.

### Step 3 — Adverse-Friction Stress

Exact frozen OOS recommendation set harus dievaluasi kembali dengan adverse transaction-cost/slippage profile yang lebih konservatif daripada baseline production profile.

Stress evaluation tidak boleh mengubah recommendation membership atau rank; hanya execution-return outcome yang berubah.

### Step 4 — Forward Shadow Full-Flow Validation

Strategy yang lulus OOS + stress harus menjalani forward shadow pada exact frozen strategy identity sebelum production-use approval.

Shadow harus menjalankan intended real-use flow:

`Market Data → PLAN → TOP PICKS → D+1 CONFIRM → ACTIONABLE / NOT ACTIONABLE`

Shadow:
- tidak melakukan transaksi otomatis;
- hanya memakai data yang benar-benar tersedia pada waktunya;
- tidak boleh retuning selama validation window;
- merekam seluruh EOD Top Picks, CONFIRM outcomes, dan realized executable outcomes;
- membedakan EOD recommendation edge dari actionable-confirm subset edge;
- untuk actionable-confirm outcome, menggunakan causal executable entry price yang tersedia **pada atau setelah CONFIRM timestamp**, bukan mengganti entry dengan earlier D+1 open bila CONFIRM terjadi kemudian.

Minimum shadow duration adalah **40 trading days** dan tidak boleh dipersingkat setelah outcome terlihat. Minimum actionable sample untuk acceptance adalah `actionable_picks_count_shadow >= 20`. Jika sample belum tercapai setelah 40 trading days, shadow harus diperpanjang; kekurangan sample tidak boleh dianggap PASS.

## OOS Acceptance Criteria

Seluruh kriteria berikut wajib lulus.

### A. Sample sufficiency

Untuk default 2-year 70/30 baseline:
- `picks_count_oos >= 40`;
- `recommendation_days_oos >= 20`.

Jika sample tidak cukup, status adalah **INSUFFICIENT OOS EVIDENCE**, bukan PASS.

### B. Robust net return

- `avg_ret_net_top_oos > 0`;
- `median_ret_net_top_oos >= 0`.

### C. Period stability

- `month_win_rate_min_oos >= 0.45`;
- `month_avg_ret_net_min_oos >= -0.01`.

### D. Downside bound

- `p25_ret_net_top_oos >= -0.03`.

### E. Ranking usefulness

- `avg_ret_net_rank1_oos > 0`;
- `median_ret_net_rank1_oos >= 0`;
- score-vs-return rank correlation OOS **MUST NOT** negatif;
- higher-ranked bucket tidak boleh menunjukkan persistent inversion terhadap lower-ranked bucket.

### F. Adverse-friction robustness

Pada stress profile:
- `avg_ret_net_top_oos_stress > 0`;
- `median_ret_net_top_oos_stress >= 0`.

Jika edge hilang pada realistic adverse friction, strategy belum layak digunakan sebagai production Top Picks walaupun zero/low-friction backtest terlihat positif.

## Shadow Acceptance

Forward shadow harus membuktikan:
- strategy menghasilkan recommendation secara reproducible dari live-available EOD information;
- no future leakage;
- recommendation count tetap quality-driven dan dapat nol;
- CONFIRM hanya mengevaluasi final Top Picks pada canonical entry window;
- stale/over-drift/invalid entry tidak lolos sebagai actionable;
- `actionable_picks_count_shadow >= 20`;
- `avg_ret_net_actionable_shadow > 0`;
- `median_ret_net_actionable_shadow >= 0`;
- actionable-confirm subset tidak menunjukkan material breakdown terhadap OOS expectation;
- ranking tidak menunjukkan systematic inversion baru;
- no-trade/no-actionable outcome diterima tanpa fallback candidate.

Material breakdown menghasilkan finding dan review. Strategy tidak boleh otomatis dituning menggunakan shadow outcome lalu dianggap masih memiliki proof lama.

## Production-Use Boundary

Production-use approval hanya dapat dipertimbangkan bila exact strategy identity telah mempunyai:

`IS PASS → OOS PASS → adverse-friction PASS → full-flow shadow PASS`

Historical proof yang memakai PLAN proxy, different recommendation semantics, zero-slippage-only identity, atau CONFIRM behavior berbeda tidak boleh dipromosikan secara administratif menjadi proof untuk strategy baru.

## Evaluation Identity Requirement

OOS dan shadow proof harus mengikat minimal:
- strategy/paramset identity;
- exact candidate eligibility and score formula identity;
- exact recommendation algorithm;
- entry/exit/horizon model;
- transaction-cost profile;
- slippage profile;
- executable-price rule;
- CONFIRM identity untuk shadow;
- Market Data publication/knowledge identity;
- deterministic split identity.

Perubahan material pada salah satu komponen menghasilkan proof identity baru.

## Single-Window Integrity Rule

Satu chronological 70/30 proof tidak boleh dibentuk dari beberapa independently calibrated windows yang digabung setelah outcome diketahui.

Canonical order:

`freeze dates → split IS/OOS → calibrate IS → freeze one winner → evaluate OOS → stress exact OOS trades → full-flow forward shadow`
