# Watchlist Weekly Swing — OOS, Friction Stress, Forward Shadow, and Production Proof

## Purpose

Dokumen ini mengunci minimum proof bahwa frozen Weekly Swing strategy yang dipilih pada IS tetap menghasilkan qualified Top Picks dengan positive net edge, acceptable downside, dan useful ranking pada data OOS yang tidak dipakai untuk tuning.

Core production proof membuktikan **final EOD Top Picks**. Optional D+1 CONFIRM mempunyai capability-specific proof terpisah dan tidak boleh menjadi dependency yang membuat core Top Picks gagal hanya karena decision-time data CONFIRM belum tersedia.

## Lifecycle Position

- **Stages:** `WS-S08` untouched OOS → `WS-S09` adverse-friction stress → `WS-S10` core forward shadow (+ optional CONFIRM observation) → `WS-S11` production-use boundary.
- **Consumes:** exactly one frozen best-IS strategy identity.
- **Produces:** proof verdicts for the same material core strategy identity, plus optional CONFIRM capability proof status bila evidence tersedia.
- **Terminal rule:** core production-use review membutuhkan seluruh required core proof stages PASS; CONFIRM availability bukan required core gate.

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

## OOS / Stress / Shadow Protocol

### Prerequisite — Frozen IS Winner

Stage ini hanya boleh dimulai setelah `WS-S07` menghasilkan exactly one frozen best-IS strategy/paramset binding yang telah lulus seluruh IS acceptance floor.

IS winner tidak boleh dipilih ulang setelah OOS suffix dibaca.

### Step 1 — Evaluate Frozen Binding on Untouched OOS (`WS-S08`)

- replay exact frozen candidate eligibility, PLAN, final recommendation, entry/exit, dan baseline friction logic pada complete OOS suffix;
- tidak boleh retuning recommendation threshold, component transform, score weights, ranking rule, cost assumption, exit rule, atau parameter lain setelah OOS outcome dibaca;
- hanya final Top Picks menjadi canonical evaluated trades.

### Step 2 — Adverse-Friction Stress (`WS-S09`)

Exact frozen OOS recommendation set harus dievaluasi kembali dengan adverse transaction-cost/slippage profile yang lebih konservatif daripada baseline production profile.

Stress evaluation tidak boleh mengubah recommendation membership atau rank; hanya execution-return outcome yang berubah.

### Step 3 — Core Forward Shadow (`WS-S10`)

Strategy yang lulus OOS + stress harus menjalani forward shadow pada exact frozen strategy identity sebelum core production-use review.

Required core shadow flow:

`Market Data → PLAN → TOP PICKS → causal executable outcome`

Core shadow:
- tidak melakukan transaksi otomatis;
- hanya memakai data yang benar-benar tersedia pada waktunya;
- tidak boleh retuning selama validation window;
- merekam seluruh EOD Top Picks dan causal realized executable outcomes;
- membuktikan bahwa live-available EOD pipeline menghasilkan output yang sama secara deterministic tanpa future leakage;
- tidak bergantung pada ketersediaan CONFIRM.

Minimum core shadow duration adalah **40 trading days** dan tidak boleh dipersingkat setelah outcome terlihat. Minimum core Top-Pick observation untuk acceptance adalah `top_picks_count_shadow >= 20`. Jika sample belum tercapai setelah 40 trading days, core shadow harus diperpanjang; kekurangan required core sample tidak boleh dianggap PASS.

### Optional CONFIRM Observation During Shadow

Bila valid D+1 decision-time data tersedia, shadow **boleh dan sebaiknya** merekam optional CONFIRM branch:

`TOP PICK → CONFIRM → ACTIONABLE / NOT_ACTIONABLE`

Canonical handling:
- valid CONFIRM data tersedia → evaluate active gates;
- data belum tersedia/stale/incomplete → `UNAVAILABLE_RETRYABLE` dan core shadow tetap berjalan;
- valid data datang kemudian saat entry window masih terbuka → CONFIRM boleh dievaluasi ulang;
- entry window berakhir tanpa valid evaluation → `EXPIRED_UNCONFIRMED`;
- tidak boleh membuat synthetic/default PASS atau synthetic `NOT_ACTIONABLE` dari missing data.

Jika CONFIRM capability ingin dinyatakan **proven**, minimum evaluated CONFIRM sample adalah `confirm_evaluated_picks_count_shadow >= 20` dan hanya observation dengan valid decision-time data yang masuk denominator capability proof. Jika sample tidak cukup, status adalah `CONFIRM_EVIDENCE_INSUFFICIENT`, bukan core shadow FAIL.

Untuk `ACTIONABLE` outcome, causal executable entry price harus tersedia **pada atau setelah CONFIRM timestamp**. Earlier D+1 open tidak boleh dipakai sebagai synthetic fill bila CONFIRM terjadi kemudian.

## OOS Acceptance Criteria

Seluruh kriteria berikut wajib lulus untuk core strategy.

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

## Core Shadow Acceptance

Core forward shadow harus membuktikan:
- strategy menghasilkan recommendation secara reproducible dari live-available EOD information;
- no future leakage;
- recommendation count tetap quality-driven dan dapat nol;
- `top_picks_count_shadow >= 20`;
- realized Top-Pick outcomes dapat dihitung secara causal menggunakan exact frozen entry/exit/friction identity;
- aggregate/ranking behavior tidak menunjukkan material breakdown terhadap OOS expectation;
- ranking tidak menunjukkan systematic inversion baru;
- no-trade outcome diterima tanpa fallback candidate.

Material breakdown menghasilkan finding dan review. Strategy tidak boleh otomatis dituning menggunakan shadow outcome lalu dianggap masih memiliki proof lama.

## Optional CONFIRM Capability Acceptance

CONFIRM capability dapat diberi status `CONFIRM_PROVEN` hanya bila:
- hanya final Top Picks dievaluasi;
- valid decision-time data tersedia secara causal;
- missing/stale/incomplete data tidak dihitung sebagai `NOT_ACTIONABLE`;
- `confirm_evaluated_picks_count_shadow >= 20`;
- `avg_ret_net_actionable_shadow > 0`;
- `median_ret_net_actionable_shadow >= 0`;
- actionable-confirm subset tidak menunjukkan material breakdown terhadap core/OOS expectation;
- no-actionable outcome diterima tanpa fallback candidate.

Jika valid CONFIRM sample tidak cukup atau source data belum tersedia, capability status adalah `CONFIRM_UNPROVEN` / `CONFIRM_EVIDENCE_INSUFFICIENT`. Status tersebut **tidak mengubah** core OOS/stress/shadow verdict dan tidak memblokir core production-use review.

## Production-Use Boundary

Core production-use approval hanya dapat dipertimbangkan bila exact core strategy identity telah mempunyai:

`IS PASS → OOS PASS → adverse-friction PASS → core forward-shadow PASS`

CONFIRM bukan prerequisite core approval. Jika product ingin menampilkan explicit current-entry label `ACTIONABLE`, CONFIRM capability harus mempunyai valid capability-specific proof atau tetap ditampilkan sebagai unproven/availability-only sesuai statusnya.

Historical proof yang memakai PLAN proxy, different recommendation semantics, atau zero-slippage-only identity tidak boleh dipromosikan secara administratif menjadi proof untuk strategy baru.

## Evaluation Identity Requirement

Core OOS/shadow proof harus mengikat minimal:
- strategy/paramset identity;
- exact candidate eligibility and score formula identity;
- exact recommendation algorithm;
- entry/exit/horizon model;
- transaction-cost profile;
- slippage profile;
- executable-price rule;
- Market Data publication/knowledge identity;
- deterministic split identity.

CONFIRM semantics/threshold identity hanya menjadi tambahan proof identity bila `CONFIRM_PROVEN` sedang diklaim. Perubahan CONFIRM tidak otomatis membatalkan core Top-Picks proof selama core selection/ranking/trade identity tidak berubah.

Perubahan material pada core component menghasilkan core proof identity baru.

## Single-Window Integrity Rule

Satu chronological 70/30 proof tidak boleh dibentuk dari beberapa independently calibrated windows yang digabung setelah outcome diketahui.

Canonical core order:

`freeze dates → split IS/OOS → calibrate IS → freeze one winner → evaluate OOS → stress exact OOS trades → core forward shadow`

Optional CONFIRM capability proof dapat berjalan pada forward shadow yang sama bila valid decision-time data tersedia, tetapi tidak menjadi prerequisite urutan core tersebut.
