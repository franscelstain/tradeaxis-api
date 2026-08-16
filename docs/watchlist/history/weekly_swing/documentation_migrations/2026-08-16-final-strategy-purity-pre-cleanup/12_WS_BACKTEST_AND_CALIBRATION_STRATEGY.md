# 12 — WS Backtest & Calibration Strategy

> **Doc Role:** CANONICAL WEEKLY SWING STRATEGY
> **Change rule:** Stable by default; revision requires material finding + evidence + decision per `../../governance/DOCUMENT_CHANGE_POLICY.md`.

## Purpose

Dokumen ini mengunci **semantik evaluasi dan kalibrasi** Weekly Swing agar hasil backtest dapat dibandingkan secara konsisten, reproducible, dan tidak berubah hanya karena detail implementation berbeda.

Dokumen ini **bukan owner schema fisik, nama tabel, SQL, reason code, artifact serialization, command, atau test**. Technical translation berada di `../../implementation/weekly_swing/`.

Default evaluation window adalah **2 tahun** dan dapat diubah hanya sebagai input evaluation yang terdokumentasi; run dengan window atau evaluation model berbeda tidak boleh dibandingkan seolah identik.

## Inputs

Backtest Weekly Swing menggunakan:
- historical EOD OHLCV dan indikator yang sah dari Market Data;
- trading calendar yang sah;
- deterministic parameter grid / paramset yang dibekukan sebelum hasil evaluation dibaca.

## A. Data and Calendar Rules (LOCKED)

- Universe evaluation harus mengikuti eligibility Weekly Swing pada `asof_eod_date`.
- Trading day mengikuti kalender bursa; weekend dan holiday tidak menjadi trading day.
- Harga simulasi menggunakan EOD OHLC yang sah. Intraday tidak menjadi dependency wajib canonical backtest.
- Backtest universe harus equivalent dengan production PLAN universe untuk tanggal yang sama. Detail cara membuktikan equivalence adalah implementation verification contract.

## B. Entry Model (LOCKED)

- PLAN dibentuk pada trading day `D` dari informasi EOD yang sah.
- Earliest canonical entry adalah trading day berikutnya `D+1`; entry pada harga yang sudah diketahui di `D` dilarang.
- Harga entry default adalah **open(D+1)**.
- Bila canonical open D+1 tidak tersedia tetapi bar D+1 masih memiliki harga executable yang sah, fallback canonical adalah **close(D+1)** dan fallback tersebut harus dapat ditelusuri pada evidence.

## C. Tradable-Bar Rule (LOCKED)

Published/readable EOD row dan executable backtest bar adalah dua konsep berbeda.

- Bar market-data tetap dapat sah walaupun `volume = 0`; Watchlist tidak mengubah fakta upstream tersebut.
- Simulasi entry, stop, target, dan time-exit hanya boleh memakai bar dengan volume numerik dan `volume > 0`.
- Bar dengan volume nol/tidak tersedia tidak boleh digunakan sebagai synthetic fill dan tidak boleh memicu stop/target.
- Jika canonical entry tidak mempunyai executable bar, trade di-skip dan tidak menghasilkan return nol sintetis.
- Jika canonical time-exit tidak mempunyai executable bar dan tidak ada exit sah sebelumnya, trade di-skip dan tidak menghasilkan return nol sintetis.
- Trade yang di-skip tidak masuk canonical return distribution.

## D. Exit Model and Weekly Swing Horizon (LOCKED)

- Horizon maksimum adalah **5 trading day sejak entry**.
- Stop dan target adalah theoretical policy levels dan harus diterjemahkan ke harga yang executable sesuai fraksi harga IDX.
- Normalisasi theoretical stop bersifat konservatif ke bawah; theoretical target bersifat konservatif ke atas.
- Canonical IDX equity price fraction:
  - `< 200` → `1`;
  - `200 .. < 500` → `2`;
  - `500 .. < 2_000` → `5`;
  - `2_000 .. < 5_000` → `10`;
  - `>= 5_000` → `25`.
- Urutan observable exit canonical:
  1. gap-through-stop pada open → fill open;
  2. gap-through-target pada open → fill open;
  3. bila tidak gap dan low menyentuh stop → fill normalized stop;
  4. bila tidak gap dan high menyentuh target → fill normalized target;
  5. bila tidak ada stop/target sampai akhir horizon → exit pada executable close hari kelima.
- Jika dalam satu bar tanpa opening gap stop dan target sama-sama tersentuh, **STOP diprioritaskan**.
- Opening gap selalu dievaluasi sebelum high/low pada bar yang sama.

## E. Executable Source Price (LOCKED)

- Fill entry/exit hanya menggunakan raw tradable OHLC dalam satuan rupiah yang sesuai fraksi harga.
- `adj_close` atau transformed/fractional adjusted OHLC tidak boleh menggantikan raw tradable OHLC untuk fill.
- Jika harga tidak dapat dibuktikan executable, trade harus fail closed / di-skip; runtime tidak boleh menebak atau merekonstruksi synthetic fill.

## F. Stop / Target Levels (LOCKED)

Jika PLAN sudah menyimpan stop dan target, evaluation menggunakan level PLAN tersebut.

Jika policy belum menyimpan level:
- `stop = entry_price * (1 - stop_atr_mult * atr14_pct)`
- `target = entry_price + rr * (entry_price - stop)`

`stop_atr_mult` dan `rr` harus berasal dari paramset/evaluation grid yang dibekukan dan dapat ditelusuri.

## G. Notional, Lot, Fee, and Slippage Model (LOCKED)

### Notional and quantity
- Canonical evaluation notional: `10_000_000` IDR per trade.
- Canonical lot size: `100` shares.
- Quantity diturunkan deterministically dari notional, entry price, dan lot size.
- Jika satu lot tidak dapat dibeli oleh canonical notional, trade tidak masuk return distribution.
- Eksperimen dengan notional berbeda harus menggunakan evaluation identity berbeda dan tidak dibandingkan apple-to-apple dengan canonical evaluation.

### Fee
Satu evaluation series hanya boleh menggunakan satu fee model yang eksplisit.

Canonical default:
- model: `IDR_FIXED`;
- buy fee: `2_500` IDR;
- sell fee: `2_500` IDR.

Model fee lain boleh dievaluasi hanya bila versioned dan diberi evaluation identity berbeda.

### Slippage
Canonical default saat ini:
- entry slippage = `0`;
- exit slippage = `0`.

Jika evaluation menggunakan slippage non-zero, nilai tersebut harus eksplisit dan evaluation identity harus membedakannya. Effective entry menjadi lebih tinggi dan effective exit menjadi lebih rendah sesuai slippage yang dipakai.

## H. Return and Metric Semantics (LOCKED)

- Gross buy = effective entry × quantity.
- Gross sell = effective exit × quantity.
- Net PnL mengurangi buy fee dan sell fee dari gross trading PnL.
- `ret_net` dihitung terhadap total buy cash outlay.
- Win berarti `ret_net > 0`.
- `picks_count` hanya menghitung trade yang benar-benar memperoleh entry dan exit executable.
- TOP evaluation bucket merujuk pada PLAN priority bucket yang canonical untuk evaluation tersebut.

Risk/activity metrics seperti stopout rate, drawdown, turnover, trade count, distribution return, dan period stability harus dapat dihitung secara deterministik dari evidence evaluation.

## I. Missing Data Handling (LOCKED)

- Missing entry price atau non-executable entry → skip trade.
- Missing/non-executable bar di dalam holding horizon tidak boleh dipakai untuk stop/target.
- Bila tidak ada exit executable yang sah sampai akhir horizon → skip trade.
- Semua skip harus dapat dihitung dan ditelusuri.
- Synthetic zero return untuk trade yang tidak dapat dieksekusi dilarang.

## J. PLAN Relationship (LOCKED)

- Backtest membentuk candidate/pick dari PLAN logic pada `D`.
- PLAN group semantics tetap mengikuti canonical PLAN strategy.
- CONFIRM tidak digunakan untuk menentukan historical backtest selection karena canonical backtest ini EOD-based.

## K. Determinism and Calibration Process (LOCKED)

Semua input yang memengaruhi hasil evaluation harus berasal dari paramset/grid yang dibekukan atau rule canonical pada strategy ini. Perubahan evaluation semantics adalah breaking evaluation change dan mewajibkan evaluation identity baru serta re-evaluation sebelum hasil dibandingkan dengan baseline lama.

Canonical calibration flow:

1. freeze evaluation window, Market Data inputs, strategy version, dan deterministic parameter grid;
2. evaluate semua candidate parameter pada window yang ditentukan;
3. hitung minimum metric set dan coverage sesuai `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`;
4. hanya candidate yang melewati seluruh acceptance floor yang dapat masuk ranking;
5. pilih best IS candidate menggunakan ranking policy canonical;
6. evaluate exact best-IS binding pada OOS tanpa retuning sesuai `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`;
7. candidate hanya dapat dipertimbangkan untuk activation setelah OOS proof lulus dan decision/governance yang diperlukan tersedia.

## Calibration Objective (LOCKED)

Calibration tidak boleh memaksimalkan satu metrik tunggal. Strategy mencari kombinasi yang:
- mempunyai positive net-return expectation;
- mempunyai trade count dan coverage memadai;
- robust pada median/distribution return;
- menjaga downside;
- stabil lintas periode;
- deterministic dan reproducible.

Threshold acceptance dan canonical ranking metrics dimiliki oleh `validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`; OOS acceptance dimiliki oleh `validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`.

## Failure Rules (LOCKED)

Calibration tidak valid jika salah satu kondisi berikut terjadi:
- data window tidak konsisten atau tidak dapat direplay;
- production PLAN universe equivalence tidak dapat dibuktikan;
- required evaluation metrics tidak tersedia;
- acceptance floor gagal;
- OOS proof tidak tersedia atau gagal;
- evaluation semantics berubah tanpa identity/version baru.

## Technical Translation (NON-OWNER REFERENCES)

Technical implementation yang menerjemahkan strategy ini berada di:
- `../../implementation/weekly_swing/contracts/WS_BACKTEST_EVALUATION_TECHNICAL_CONTRACT.md`;
- `../../implementation/weekly_swing/contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md`;
- `../../implementation/weekly_swing/contracts/WS_BACKTEST_OOS_RUNTIME_IMPLEMENTATION_CONTRACT.md`;
- `../../implementation/weekly_swing/evidence_contracts/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`;
- `../../implementation/weekly_swing/verification/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`;
- `../../implementation/weekly_swing/verification/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`.

Dokumen implementation tersebut tidak boleh mengubah semantik strategy ini secara diam-diam.
