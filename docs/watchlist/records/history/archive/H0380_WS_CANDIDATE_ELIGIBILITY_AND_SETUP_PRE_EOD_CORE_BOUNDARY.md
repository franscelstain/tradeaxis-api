# Watchlist Weekly Swing — Candidate Eligibility and Setup Strategy

## Purpose

Dokumen ini menetapkan kualitas minimum sebuah saham agar boleh masuk **Weekly Swing recommendation-candidate pool**. Tujuannya bukan memperbanyak kandidat, tetapi memastikan hanya saham dengan data, likuiditas, setup, dan risk profile yang layak yang dapat diteruskan ke final Top Picks qualification.

## Lifecycle Position

- **Stages:** `WS-S01` trusted data readiness and `WS-S02` absolute eligibility.
- **Consumes:** authoritative same-date point-in-time Market Data.
- **Produces:** eligibility facts required for deterministic candidate-state assignment.
- **Next:** complete `WS-S02` deterministic candidate-state assignment.

## A. Authoritative Market Data Requirement

Seluruh intake mengikuti `WS_MARKET_DATA_INPUT_REQUIREMENTS.md`. Untuk new current PLAN, Market Data read product harus `READABLE`, `FRESH`, publication-coherent, dan `effective_trade_date` harus sama dengan requested `asof_eod_date`.

Per ticker, `data_usable=true` adalah prerequisite upstream, bukan strategy approval. Candidate harus fail-closed dari recommendation-candidate pool bila:
- producer-facing read product/run-level binding tidak sah untuk current PLAN;
- `data_usable=false`;
- field yang dipakai active hard gate, active score component, atau trade-plan derivation hilang/invalid;
- temporal market facts yang diketahui menunjukkan normal Regular-Market entry tidak dapat dilakukan; atau
- active Weekly Swing rule secara eksplisit memetakan factual status/event context menjadi disqualifying state.

Known `is_suspended=true` atau status lain yang secara faktual mencegah normal Regular-Market entry harus berakhir `AVOID`. UMA/event-risk/corporate-action flags yang tidak dengan sendirinya memblokir Market Data usability tetap merupakan fakta; efeknya pada WATCH_ONLY/AVOID hanya boleh datang dari active Weekly Swing rule.

Watchlist tidak boleh menebak, zero-fill, mengisi synthetic value, menghitung ulang indikator/adjustment/status/sector, atau membaca producer internals untuk menyelamatkan candidate.

## B. Weekly Swing Eligibility Dimensions

Recommendation-candidate harus melewati seluruh active hard gate pada dimensi berikut.

### 1. Liquidity / Executability

Candidate harus mempunyai likuiditas yang memadai agar next-open entry dan exit Weekly Swing realistis untuk dieksekusi.

Canonical bootstrap liquidity measure adalah Market Data `adv20_close_volume_proxy_idr`. Legacy `dv20_idr` hanya boleh dibaca sebagai compatibility alias untuk proxy yang sama dan tidak boleh disebut actual turnover. Beralih ke `adv20_traded_value_idr_actual` sebagai selection metric adalah strategy/proof identity baru. Absolute floor diterapkan sebelum relative ranking.

### 1A. Product Capacity / Reference Notional

Selain absolute liquidity floor, active strategy identity harus membekukan **product-level execution capacity parameters** sebelum outcome dibaca:

- `reference_order_notional_idr` — standardized proof notional per Top Pick;
- `max_adv20_participation_rate` — maximum allowed fraction of canonical ADV20 liquidity proxy for that reference order;
- lot/tick feasibility rule yang menggunakan effective-dated market-structure facts.

Reference notional adalah product proof parameter, bukan modal pengguna dan tidak boleh mengubah ranking berdasarkan affordability individual. Candidate yang tidak mampu mendukung reference notional di bawah frozen participation cap tidak boleh menjadi recommendation candidate untuk identity tersebut.

Production evidence juga harus melaporkan `supported_notional_per_pick` / capacity headroom agar consumer memahami skala eksekusi yang masih konsisten dengan tested liquidity assumption.

### 2. Participation / Volume Quality

Candidate harus mempunyai participation/volume behavior yang cukup untuk mendukung setup Weekly Swing. Baseline producer semantic adalah `vol_ratio_20`; serialization alias lama hanya boleh dipetakan bila producer contract/version membuktikan formula yang sama.

Volume confirmation adalah kualitas signal, bukan alasan untuk mengejar anomaly ekstrem. Bila upper-bound digunakan untuk menghindari abnormal participation, bound harus preregistered dan dibuktikan pada evaluation identity yang sama.

### 3. Volatility / Risk Quality

Candidate harus berada pada volatility range yang masih kompatibel dengan holding horizon maksimum 5 trading day dan active exit/risk policy. Baseline producer risk metric adalah `atr14_pct`.

Volatility yang terlalu rendah atau terlalu tinggi dapat ditolak melalui versioned absolute guard. Risk quality harus semakin baik ketika karakteristik candidate semakin sesuai dengan desired Weekly Swing risk band; raw volatility yang lebih tinggi tidak otomatis berarti score lebih tinggi.

### 4. Momentum Persistence

Candidate harus menunjukkan momentum yang cukup kuat untuk mendukung continuation dalam horizon Weekly Swing, tetapi strategy boleh membatasi kondisi yang terlalu lemah maupun terlalu extended. Baseline momentum metric adalah producer `roc20`; `roc5`/`roc10` menjadi required hanya bila frozen strategy identity memakainya.

Exact momentum measure dan bounds adalah bagian dari active strategy/paramset identity dan harus dibekukan sebelum outcome evaluation dibaca.

### 5. Breakout / Setup Integrity

Candidate harus mempunyai setup yang cukup dekat atau cukup kuat terhadap canonical breakout/reference structure sehingga recommendation tidak berasal dari saham yang sudah kehilangan setup atau terlalu jauh dari intended entry context. Baseline breakout proximity adalah producer `close_to_hh20_pct`; `hh20`, `range_position_20_pct`, atau field range lain menjadi required hanya bila active identity memakainya.

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
