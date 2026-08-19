# Weekly Swing Strategy — End-to-End Coherence Findings

## Objective

Menilai apakah revised qualified-Top-Picks strategy sudah mempunyai satu urutan end-to-end yang tidak ambigu dari trusted Market Data sampai production-use proof.

## Material Findings

### F1 — Filename numbering did not define a safe build sequence

Canonical files memakai stable legacy identifiers (`03`, `08`, `09`, `22`, `24`, `10`, dst.) sehingga pembaca yang mengikuti angka file dapat menjalankan urutan yang salah.

**Impact:** HIGH — implementer dapat menebak dependency sendiri walaupun seluruh rule individual benar.

### F2 — Candidate classification order was internally inconsistent

PLAN membutuhkan `RECOMMENDATION_CANDIDATES` sebagai source scoring, tetapi classification document sebelumnya meletakkan candidate-state classification setelah scoring/ordering.

**Impact:** HIGH — circular dependency antara classification dan scoring.

### F3 — Historical evaluation document overlapped downstream proof ownership

Backtest/calibration strategy sebelumnya ikut mendeskripsikan winner selection, OOS, stress, dan shadow progression yang juga dimiliki validation strategy.

**Impact:** MEDIUM/HIGH — stage authority dapat berbeda bila salah satu dokumen berubah.

### F4 — OOS proof document repeated IS calibration ownership

OOS document sebelumnya memulai protocol dengan calibration pada IS walaupun IS sufficiency/winner freeze sudah mempunyai owner tersendiri.

**Impact:** MEDIUM — membuka kemungkinan OOS flow memilih ulang winner atau menafsirkan IS gate secara berbeda.

### F5 — Historical Top-Picks proof and D+1 CONFIRM proof needed explicit time separation

Backtest/OOS memakai historical EOD/executable price data, sedangkan actual user decision membutuhkan D+1 current actionability. Tanpa separation eksplisit, implementasi dapat membuat synthetic historical CONFIRM atau memakai future/current information secara retroaktif.

**Impact:** CRITICAL — berpotensi menghasilkan false proof/future leakage.

## Required Resolution

- satu canonical lifecycle sequence dengan explicit stage inputs/outputs/exit conditions;
- classification setelah absolute eligibility/required-feature completeness tetapi sebelum scoring;
- S06 hanya historical evaluation model;
- S07 hanya IS sufficiency + one-winner freeze;
- S08–S11 hanya untouched OOS → stress → forward shadow → production-use boundary;
- EOD ranking proof dipisahkan dari D+1 CONFIRM full-flow proof.
