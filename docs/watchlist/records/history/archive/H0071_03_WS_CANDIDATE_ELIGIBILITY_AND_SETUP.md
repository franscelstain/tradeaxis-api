# 03 — WS Candidate Eligibility & Setup Strategy

## Purpose

Dokumen ini menetapkan kualitas minimum sebuah saham agar boleh masuk **Weekly Swing recommendation-candidate pool**. Tujuannya bukan memperbanyak kandidat, tetapi memastikan hanya saham dengan data, likuiditas, setup, dan risk profile yang layak yang dapat diteruskan ke final Top Picks qualification.

## Lifecycle Position

- **Stages:** `WS-S01` trusted data readiness and `WS-S02` absolute eligibility.
- **Consumes:** authoritative same-date point-in-time Market Data.
- **Produces:** eligibility facts required for deterministic candidate-state assignment.
- **Next:** complete `WS-S02` deterministic candidate-state assignment.

## A. Authoritative Market Data Requirement

Weekly Swing hanya boleh memakai Market Data product yang authoritative untuk `asof_eod_date` yang sama.

Candidate harus fail-closed dari recommendation-candidate pool bila:
- Market Data publication/read product tidak sah atau tidak point-in-time;
- data-usability untuk field yang diwajibkan active strategy tidak lulus;
- temporal listing/trading-status facts membuat ticker tidak eligible untuk Weekly Swing pada tanggal tersebut;
- field yang dipakai oleh active hard gate atau active score component hilang/invalid.

Watchlist tidak boleh menebak, mengisi synthetic value, atau menghitung ulang fakta upstream untuk menyelamatkan candidate.

## B. Weekly Swing Eligibility Dimensions

Recommendation-candidate harus melewati seluruh active hard gate pada dimensi berikut.

### 1. Liquidity / Executability

Candidate harus mempunyai likuiditas yang memadai agar next-open entry dan exit Weekly Swing realistis untuk dieksekusi.

Canonical liquidity measure menggunakan Market Data liquidity metric yang dibekukan pada strategy identity. Absolute floor harus diterapkan sebelum relative ranking.

### 2. Participation / Volume Quality

Candidate harus mempunyai participation/volume behavior yang cukup untuk mendukung setup Weekly Swing.

Volume confirmation adalah kualitas signal, bukan alasan untuk mengejar anomaly ekstrem. Bila upper-bound digunakan untuk menghindari abnormal participation, bound harus preregistered dan dibuktikan pada evaluation identity yang sama.

### 3. Volatility / Risk Quality

Candidate harus berada pada volatility range yang masih kompatibel dengan holding horizon maksimum 5 trading day dan active exit/risk policy.

Volatility yang terlalu rendah atau terlalu tinggi dapat ditolak melalui versioned absolute guard. Risk quality harus semakin baik ketika karakteristik candidate semakin sesuai dengan desired Weekly Swing risk band; raw volatility yang lebih tinggi tidak otomatis berarti score lebih tinggi.

### 4. Momentum Persistence

Candidate harus menunjukkan momentum yang cukup kuat untuk mendukung continuation dalam horizon Weekly Swing, tetapi strategy boleh membatasi kondisi yang terlalu lemah maupun terlalu extended.

Exact momentum measure dan bounds adalah bagian dari active strategy/paramset identity dan harus dibekukan sebelum outcome evaluation dibaca.

### 5. Breakout / Setup Integrity

Candidate harus mempunyai setup yang cukup dekat atau cukup kuat terhadap canonical breakout/reference structure sehingga recommendation tidak berasal dari saham yang sudah kehilangan setup atau terlalu jauh dari intended entry context.

Exact setup measure, trigger mode, proximity/integrity floor, dan maximum extension adalah versioned strategy parameters.

### 6. Market-Regime Compatibility

Market regime boleh menjadi hard gate atau explicit score context bila preregistered. Regime hanya boleh menggunakan informasi yang sudah tersedia pada signal date dan tidak boleh ditentukan dari future path.

Baseline real-use strategy tidak boleh mengubah regime rule setelah melihat OOS/shadow outcome tanpa proof identity baru.

## C. Required Active Features

Semua feature yang benar-benar dipakai oleh active hard gate atau active score formula adalah **required for recommendation candidacy**.

Jika salah satu required active feature missing/invalid:
- ticker tidak boleh memperoleh synthetic neutral/zero score untuk tetap recommendation-eligible;
- ticker dapat tetap dicatat sebagai diagnostic/watch-only item;
- ticker tidak dapat menjadi final Top Pick pada run tersebut.

Aturan ini menjaga agar ranking membandingkan candidate dengan informasi kualitas yang setara.

## D. Absolute Quality Before Relative Ranking

Absolute eligibility/setup floor selalu diterapkan sebelum quantile, percentile, atau relative ordering.

Relative ranking tidak boleh mengubah saham yang gagal absolute quality menjadi recommendation candidate hanya karena seluruh market sedang lebih buruk.

## E. Anti-Overfit Boundary

Canonical candidate selection tidak boleh memakai:
- realized future return;
- OOS/shadow outcome untuk mengubah rule yang sedang diuji;
- post-hoc ticker, sector, atau month blacklist yang dipilih karena outcome buruk;
- future corporate-action/status/sector knowledge;
- target jumlah candidate sebagai alasan melonggarkan guard.

Pre-trade exclusion yang mempunyai alasan operasional/market-fact yang sah hanya boleh dipakai bila rule tersebut explicit, causal, versioned, dan dievaluasi dengan identity yang sama.

## F. Eligibility Outcome Handoff

Setelah seluruh hard gate dan required-feature checks diterapkan, eligibility menghasilkan fakta yang cukup untuk deterministic `WS-S02` candidate-state classification.

Handoff invariants:
- ticker yang memenuhi seluruh recommendation-candidacy requirement dapat diteruskan sebagai `RECOMMENDATION_CANDIDATES`;
- ticker yang gagal recommendation candidacy tidak boleh menerima relative-rank rescue;
- non-candidate outcome hanya dapat berakhir sebagai `WATCH_ONLY` atau `AVOID`;
- tidak ada PRIMARY/SECONDARY fallback dan tidak ada forced minimum candidate count.

Exact state assignment semantics tetap harus konsisten pada seluruh run dan replay.

## G. Empty Opportunity Set

Jika tidak ada ticker yang memenuhi recommendation-candidate eligibility, hasil yang benar adalah candidate pool kosong dan final **NO QUALIFIED TOP PICKS**.

Breadth/count market boleh dilaporkan sebagai context, tetapi jumlah candidate yang sedikit tidak boleh otomatis membatalkan candidate berkualitas atau memaksa candidate tambahan kecuali market-breadth gate itu sendiri telah menjadi explicit, evidence-backed canonical strategy rule.

## Final Rules

1. Recommendation candidacy membutuhkan complete active feature set dan seluruh hard gate lulus.
2. Absolute quality selalu mendahului relative ranking.
3. Threshold numeric adalah versioned strategy identity dan tidak dituning dari OOS/shadow.
4. Candidate count tidak menjadi optimization objective.
5. PLAN candidate state hanya `RECOMMENDATION_CANDIDATES`, `WATCH_ONLY`, atau `AVOID`.
6. Missing active scoring/qualification feature fail-closed untuk final recommendation path.
