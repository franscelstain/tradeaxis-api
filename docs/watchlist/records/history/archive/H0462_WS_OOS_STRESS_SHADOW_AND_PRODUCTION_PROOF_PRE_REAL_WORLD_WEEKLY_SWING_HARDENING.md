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
- tidak boleh overlap, randomization, **undeclared/hidden gap**, atau post-result date removal; explicit predeclared purge interval untuk mencegah outcome-overlap leakage justru wajib diterapkan.

## Purged Boundary and OOS Contamination Control

Chronological split harus melindungi OOS dari IS outcome dependency.

Setelah nominal 70/30 boundary ditentukan, setiap IS recommendation yang maximum possible entry/exit/outcome dependency interval menyentuh atau melewati first OOS signal date harus **dipurge dari IS selection metrics**. Purge rule dihitung dari frozen maximum strategy horizon/execution dependency sebelum returns dibaca dan dicatat sebagai explicit split metadata; purge tidak menggeser atau mengecilkan protected OOS suffix setelah outcome terlihat.

Protected OOS tidak boleh dipakai untuk:

- parameter selection;
- threshold/weight/feature choice;
- deciding which IS rule to keep/remove;
- debugging strategy quality dengan outcome-specific changes lalu dianggap masih untouched.

## OOS Consumption / Burn Rule

Saat first OOS performance result, trade-level outcome, summary, atau diagnostic yang dapat mengungkap strategy quality telah dibaca oleh research/implementation process, OOS identity tersebut menjadi **CONSUMED_OOS**.

Jika setelah itu terjadi material strategy change, consumed OOS tetap historical validation evidence tetapi **tidak boleh disebut untouched OOS untuk identity baru**. Identity baru membutuhkan fresh untouched later holdout bila tersedia; jika tidak ada historical holdout yang benar-benar belum dilihat, fresh proof harus datang dari forward shadow/forward validation. Tidak boleh mereset label OOS hanya dengan mengganti campaign/paramset name.

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

## Operational and Capacity Proof Requirements

OOS/shadow production proof harus mengikat:

- `recommendation_available_at` dan minimum 30-minute decision lead-time compliance untuk canonical D+1 open claim;
- frozen `reference_order_notional_idr` dan `max_adv20_participation_rate`;
- supported notional/capacity headroom per Top Pick;
- count of late publications, pre-entry non-executable trades, post-entry delayed exits, dan unresolved exposures.

Recommendation selection/rank tetap capital-independent, tetapi production-use proof gagal bila reference notional yang dideklarasikan tidak dapat dieksekusi secara konsisten dengan frozen liquidity/participation assumptions.

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

### E1. Statistical confidence, economic significance, benchmark, and Top-K

- `avg_ret_net_top_oos >= 0.0025`;
- `lower_95ci_avg_ret_net_top_oos > 0` using date-clustered/block bootstrap;
- `avg_excess_ret_vs_ihsg_oos > 0` and `lower_95ci_avg_excess_ret_vs_ihsg_oos > 0` when required benchmark input is available;
- `avg_selection_uplift_vs_eligible_universe_oos > 0`;
- Top-1, Top-3, and Top-5 average net return must be positive when their minimum sample is available;
- Top-1/Top-3 must not show persistent underperformance versus `ALL_QUALIFIED`;
- OOS must not show material contradiction to IS multiple-testing/plateau robustness conclusion.

### E2. Tail-risk and execution-risk acceptance

- `p05_ret_net_top_oos >= -0.08`;
- `expected_shortfall_05_ret_net_top_oos >= -0.10`;
- date-level equal-reference-notional `max_drawdown_oos <= 0.20`;
- all post-entry non-executable exposures remain counted; none may be skipped from return distribution;
- unresolved post-entry exposure must use conservative terminal-loss treatment and any material concentration triggers FAIL/review rather than sample deletion;
- MAE, loss-streak, exit-delay, and capacity metrics must not show material breakdown versus frozen IS risk profile.

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

## Post-Production Strategy Health and Automatic Safety Boundary

Production-use approval is not permanent proof. Exact active strategy identity must continue a **live-equivalent causal monitoring stream** using the same recommendation, execution-model, friction, benchmark, ranking, capacity, and tail-risk semantics.

Canonical health states:

- `HEALTHY` — no material degradation signal;
- `WATCH` — short-window degradation exists but confirmation threshold not yet met;
- `SUSPEND_NEW_RECOMMENDATIONS_REVIEW_REQUIRED` — material degradation is confirmed; new Top Picks publication for real-use must stop while diagnosis/revalidation runs;
- `REVALIDATION_REQUIRED` — strategy/material assumption changed or proof identity can no longer be relied upon.

Minimum monitoring windows:

- **20 trading days** = early-warning window;
- **60 trading days** = confirmation window, extended when sample is insufficient.

Material degradation indicators include at minimum:

- rolling net edge at/below zero or below the +0.25% economic-edge objective for a sustained confirmation window;
- rolling benchmark-relative edge at/below zero;
- persistent Top-K/rank inversion;
- realized/model execution friction materially exceeding tested stress assumptions;
- tail-risk or drawdown breach of production acceptance floor;
- repeated late recommendation publication / insufficient decision lead time;
- liquidity/capacity deterioration that violates frozen reference-notional participation assumptions;
- systematic Market Data/readiness degradation that prevents trustworthy production behavior.

Deterministic health transitions untuk baseline monitoring adalah:

- `WATCH` bila 20-trading-day window mempunyai `avg_ret_net < 0.0025`, benchmark-relative mean `<= 0`, persistent Top-3-vs-All inversion, atau operational/capacity warning yang berulang;
- `SUSPEND_NEW_RECOMMENDATIONS_REVIEW_REQUIRED` bila 60-trading-day confirmation window mempunyai `avg_ret_net <= 0`, `avg_excess_ret_vs_ihsg <= 0`, `p05 < -0.08`, `expected_shortfall_05 < -0.10`, date-level max drawdown `> 0.20`, reference-notional capacity violation pada `>= 10%` recommendation days, atau late-publication lead-time failure pada `>= 5%` recommendation days;
- immediate `REVALIDATION_REQUIRED` bila material strategy/data/execution identity berubah, OOS/proof contamination ditemukan, atau monitoring calculation tidak lagi comparable dengan production identity.

Jika required 60-day sample belum cukup, state tidak boleh dinaikkan menjadi `HEALTHY` hanya karena calendar window selesai; monitoring diperpanjang sampai evidence cukup. A single noisy short-window observation dapat memindahkan state ke `WATCH`, tetapi tidak mengizinkan retuning.

**No automatic retuning:** production/shadow outcome may open finding/research, but threshold/feature/weight/ranking/exit changes create a new strategy identity and require the applicable IS/OOS/stress/shadow proof again. Old production proof cannot be administratively inherited.

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


## EOD-Only Production Proof Boundary

Core production-use proof tetap proof untuk **EOD Weekly Swing recommendation**, bukan certification atas exact broker execution.

- OOS, adverse-friction, dan core forward-shadow **MUST** dapat diselesaikan tanpa realtime/orderbook dependency menggunakan frozen EOD strategy identity dan conservative modeled-execution rules.
- Forward shadow wajib membuktikan recommendation availability/timestamp sebelum intended next-session opportunity, reproducibility, data freshness, dan causal outcome accounting; ia tidak wajib membuktikan queue position atau exact opening fill pengguna.
- Production evidence harus menyebut modeled execution sebagai modeled/reference execution dan tidak boleh menggunakan istilah `actual_fill` untuk price yang hanya berasal dari EOD OHLC plus model assumptions.
- Missing realtime/orderbook capability **MUST NOT** menurunkan core strategy menjadi unproven bila seluruh EOD proof gates telah lulus; hanya optional capability yang bergantung pada source tersebut yang dapat berstatus unproven/unavailable.
- Jika future intraday/orderbook enhancement diaktifkan, enhancement harus mempunyai separately versioned capability/proof identity; result-nya tidak boleh dicampur ke core EOD OOS/shadow denominator tanpa controlled strategy revision.
- Core approval tetap menilai apakah EOD Top Picks mempunyai robust positive net edge setelah conservative friction/execution uncertainty, bukan apakah setiap investor memperoleh harga yang identik dengan backtest reference price.

## Proof Input Ownership Across OOS, Stress, Shadow, and Production

Seluruh post-IS proof memakai boundary fakta yang sama dengan core runtime.

- Untouched OOS dan adverse-friction stress **MUST** memakai producer-facing point-in-time facts/replay identity; local reconstruction atau substitute market feature membuat affected proof tidak valid untuk production qualification.
- Forward shadow **MUST** mengonsumsi live-available authoritative Market Data publication dan tidak boleh menjalankan hidden local feature pipeline untuk membuat Top Picks tetap tersedia ketika producer fact missing.
- Production-health monitoring yang membutuhkan market inputs **MUST** memakai authoritative producer facts/identities yang comparable dengan approved strategy; monitoring tidak boleh repair/recompute market facts untuk mempertahankan `HEALTHY` state.
- Material/persistent upstream fact gap yang membuat approved runtime/proof identity tidak lagi dapat dievaluasi harus menghasilkan unavailable/insufficient-evidence/revalidation behavior sesuai affected stage, bukan local substitution atau automatic rule relaxation.

## EOD Availability and Action-Window Production Proof

Production-use proof harus membuktikan bukan hanya kualitas Top Picks, tetapi bahwa EOD recommendation secara operasional tersedia ketika opportunity yang diklaim masih dapat ditindaklanjuti.

Minimum metrics pada forward shadow/live-equivalent observation:

- `requested_eod_runs_count`;
- `market_data_unavailable_retryable_count`;
- `same_date_ready_count`;
- `timely_recommendation_count` — qualified/no-pick current runs yang selesai sebelum canonical entry cutoff;
- `late_action_window_expired_count`;
- `timely_recommendation_rate`;
- `previous_context_only_count` bila stale/prior-date context ditampilkan.

Canonical shadow acceptance untuk operational availability:

- `timely_recommendation_rate >= 0.95` pada seluruh governed requested runs yang memang dijadwalkan untuk active strategy; upstream/provider unavailability tetap dihitung sebagai operational unavailability dan tidak boleh dikeluarkan dari denominator hanya agar availability terlihat lebih baik;
- `late_action_window_expired_count / evaluable_requested_runs <= 0.05`;
- stale/prior-date context **MUST NOT** pernah dihitung sebagai timely current recommendation;
- expired recommendation **MUST NOT** pernah menggunakan already-passed intended-session open sebagai modeled/user-actionable fill;
- no automatic carry-forward dari expired result ke session berikutnya.

Jika historical publication timestamps tidak tersedia, OOS return-quality proof tidak boleh mengarang timeliness PASS; operational availability/cutoff acceptance harus dibuktikan oleh forward shadow/live-equivalent evidence.

Production health monitoring harus terus menghitung ready/timely/expired rates dengan semantic yang sama. Persistent breach terhadap frozen operational availability limits dapat memicu `WATCH` atau `SUSPEND_NEW_RECOMMENDATIONS_REVIEW_REQUIRED` sesuai health rules, tanpa local Market Data workaround.
