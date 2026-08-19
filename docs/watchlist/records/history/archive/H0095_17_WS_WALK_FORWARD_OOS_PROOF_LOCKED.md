# 17 — WS Walk-Forward / Out-of-Sample Proof (LOCKED)

## Purpose (LOCKED)

Dokumen ini mengunci proof minimum bahwa candidate hasil kalibrasi tidak hanya menang di in-sample (IS), tetapi tetap memenuhi acceptance rule pada out-of-sample (OOS) yang belum dipakai untuk memilih parameter.

Candidate hasil kalibrasi tidak boleh diteruskan ke activation path tanpa OOS proof yang lulus.

## Data Window and Deterministic Split (LOCKED)

Evaluation menggunakan satu explicit chronological `(from_date, to_date)` range.

Canonical split:
- IS = 70% awal ordered trading dates;
- OOS = 30% sisanya;
- split berbasis waktu, bukan random.

Untuk `N` ordered trading dates:
- `is_count = floor(0.70 * N)`;
- `oos_count = N - is_count`;
- IS adalah prefix pertama sebanyak `is_count`;
- OOS adalah seluruh suffix sisanya;
- tidak boleh ada overlap, randomization, hidden gap, atau date yang dibuang;
- proof gagal tertutup bila salah satu sisi kosong.

Evidence harus dapat membuktikan ordered date set dan split boundary secara deterministik sehingga replay menghasilkan split yang sama.

## OOS Protocol (LOCKED)

### Step 1 — Calibrate on IS
- evaluate seluruh official candidate grid pada complete IS prefix;
- apply acceptance floors dan ranking policy dari `16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`;
- freeze tepat satu best-IS binding secara deterministic.

### Step 2 — Evaluate Frozen Best-IS on OOS
- evaluate complete OOS suffix menggunakan exact best-IS binding;
- **tidak boleh retuning** menggunakan OOS metrics;
- OOS result harus dapat ditelusuri ke exact IS selection dan exact evaluation model.

### Step 3 — Optional Rolling Walk-Forward
Rolling walk-forward dapat digunakan sebagai penguat evidence, tetapi bukan minimum canonical proof. Contoh penguat: train 6 bulan → test 3 bulan secara rolling maju.

## OOS Acceptance Criteria (LOCKED)

Canonical minimum OOS proof harus memenuhi semuanya:

### A. Minimum OOS trade count
- `picks_count_oos >= 40` sebagai default untuk 2-year 70/30 baseline; threshold lain harus terdokumentasi eksplisit.

### B. Robust return
- `avg_ret_net_top_oos > 0`;
- `median_ret_net_top_oos >= 0`.

### C. Stability
- `month_win_rate_min_oos >= 0.45`.

### D. Downside bound
- `p25_ret_net_top_oos >= -0.03`, atau threshold OOS lain yang terdokumentasi secara eksplisit pada strategy/evaluation identity yang sah.

Jika satu kriteria gagal, candidate tidak boleh dianggap OOS-qualified untuk activation path walaupun IS menang.

## Evaluation Identity Requirement (LOCKED)

OOS proof harus merekam evaluation assumptions secara eksplisit dan tidak ambigu, minimal mencakup:
- entry rule;
- exit rule;
- maximum holding horizon;
- fee model;
- slippage assumption;
- gap/fill rule;
- executable price rule;
- strategy/evaluation version identity.

Perubahan salah satu komponen tersebut menghasilkan evaluation identity berbeda dan tidak boleh disamakan dengan proof lama.

## Minimum Evidence Requirements (LOCKED)

Evidence OOS harus cukup untuk membuktikan:
- exact IS window dan OOS window;
- exact frozen best-IS candidate;
- link/binding antara IS selection dan OOS evaluation;
- OOS metric set yang diperlukan untuk seluruh acceptance criteria;
- evaluation model/version;
- deterministic date/split identity;
- bahwa tidak terjadi OOS retuning.

## Single-Window Evaluation Rule (LOCKED)

Satu chronological 70/30 proof adalah satu logical evaluation atas satu explicit range dan tidak boleh dipecah menjadi beberapa independently calibrated ranges lalu digabungkan setelahnya.

Required logical order tetap:

```text
freeze ordered trading dates
→ deterministic 70/30 split
→ evaluate every official candidate on complete IS prefix
→ freeze one best-IS binding
→ evaluate complete OOS suffix with that exact binding
```

Split boundary, candidate grid ordering, best-IS selection, dan proof identity harus tetap invariant pada replay.
