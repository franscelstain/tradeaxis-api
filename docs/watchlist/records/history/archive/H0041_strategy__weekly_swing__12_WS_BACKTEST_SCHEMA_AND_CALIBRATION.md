# 12 — Backtest Schema & Calibration — Weekly Swing

> **Doc Role:** CANONICAL WEEKLY SWING STRATEGY
> **Change rule:** Stable by default; revision requires material finding + evidence + decision per `../../governance/DOCUMENT_CHANGE_POLICY.md`.


Default backtest window: 2 tahun (configurable).

## Purpose

Dokumen ini adalah owner schema backtest WS dan flow kalibrasi parameter; evaluasi metrik minimum, OOS proof, dan artifact manifest dirujuk ke file khusus masing-masing.

Dokumen ini mengunci schema backtest dan aturan perhitungan evaluasi untuk Weekly Swing agar kalibrasi reproducible, audit-able, dan konsisten dengan kontrak runtime canonical Weekly Swing (PLAN / RECOMMENDATION / CONFIRM).
Dokumen ini juga menetapkan syarat validasi: BT coverage, universe equivalence, metric sufficiency, dan OOS proof.

## Scope lock (yang dikerjakan)
Backtest Weekly Swing mengikuti artefak resmi yang didefinisikan pada:
[`18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`](../../implementation/weekly_swing/evidence_contracts/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md).

**Schema backtest (LOCKED)**
Schema tabel backtest mengikuti: [`db/BACKTEST_SCHEMA_DDL.sql`](../../implementation/weekly_swing/db/BACKTEST_SCHEMA_DDL.sql).

Dokumen ini tidak menduplikasi daftar tabel; semua daftar artefak wajib merujuk ke Manifest.

Catatan audit: universe harian dan picks harian wajib tersedia untuk replay dan audit; lihat Manifest (18) untuk daftar artefak resminya.

Artefak yang tidak tercantum pada Manifest dianggap tidak digunakan dan tidak boleh diasumsikan ada.

Menetapkan mekanisme kalibrasi parameter WS dari backtest 2 tahun:
- menghasilkan param_set baru (origin=BT) yang dapat dipromosikan menjadi ACTIVE.

## Proof of BT coverage (LOCKED)
Semua parameter dengan origin=BT wajib tercakup di:
[`14_WS_BT_COVERAGE_MATRIX_LOCKED.md`](../../implementation/weekly_swing/verification/14_WS_BT_COVERAGE_MATRIX_LOCKED.md).

Kalibrasi backtest dianggap tidak valid jika ada parameter origin=BT yang:
- tidak punya mapping ke kolom `watchlist_bt_param_grid`, atau
- tidak punya bukti audit (universe/picks/eval), atau
- tidak lolos test `BT_COVERAGE_GUARD`.

## Universe equivalence (LOCKED)
Backtest universe (`watchlist_bt_universe_ws`) wajib setara dengan production PLAN universe
untuk tanggal EOD yang sama sesuai:
[`15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](../../implementation/weekly_swing/verification/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md).

Bukti equivalence WAJIB menggunakan snapshot dari production PLAN yang mengikuti schema resmi:
[`db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md`](../../implementation/weekly_swing/db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md).

Kalibrasi dianggap tidak valid jika tidak ada bukti equivalence (pass/fail + canonical reason).

## Prerequisites
### Weekly Swing
11_WS_INTRADAY_SNAPSHOT_TABLES

## Inputs
- Dataset historis 2 tahun (EOD OHLCV + indicators)
- Param grid (seed MAN) untuk eksplorasi

## Backtest execution assumptions (LOCKED)

Bagian ini mengunci cara backtest dihitung agar hasil kalibrasi 2 tahun reproducible dan tidak berubah karena implementasi berbeda.

### A. Data & Kalender
- Universe: hanya ticker yang lolos gate WS pada `asof_eod_date`.
- Trading day: gunakan kalender bursa (skip weekend/holiday).
- Sumber harga: OHLC harian resmi (EOD). Tidak memakai intraday.

### B. Entry Model (PLAN → eksekusi)
- PLAN dibuat pada `asof_eod_date = D` (harga penilaian = close(D)).
- Entry dieksekusi pada trading day berikutnya `D+1`.
- Harga entry default: **open(D+1)**.
- Jika open(D+1) tidak tersedia: fallback **close(D+1)** dan catat `BT_FALLBACK_ENTRY_PRICE`.

### B1. Tradable-Bar Rule (LOCKED)

Published/readable EOD row dan executable backtest bar adalah dua konsep berbeda.

- Bar tetap sah sebagai data market-data published walaupun `volume = 0`; watchlist tidak boleh mengubah atau menghapus row upstream tersebut.
- Untuk simulasi entry, stop, target, dan time-exit, bar dianggap executable hanya jika `volume` numerik dan `volume > 0`.
- `volume = 0` atau `volume IS NULL` berarti tidak ada bukti transaksi executable pada tanggal tersebut.
- Entry D+1 dengan bar non-executable harus di-skip dengan `BT_SKIP_NO_TRADABLE_ENTRY`; harga OHLC statis tidak boleh dipakai sebagai fill sintetis.
- Bar non-executable di dalam horizon exit diabaikan untuk hit stop/target.
- Jika time-exit jatuh pada bar non-executable dan tidak ada exit sah sebelumnya, trade di-skip dengan `BT_SKIP_NO_TRADABLE_EXIT`.
- Skip karena bar non-executable harus menghasilkan `ret_net = NULL`, bukan return nol.
- Runtime artifact wajib mencatat volume entry/exit dan tanggal bar non-executable yang diabaikan bila tersedia.

Canonical runtime marker:

```text
tradable_bar_rule = POSITIVE_VOLUME_REQUIRED
min_tradable_volume = 1
```

Rule ini adalah execution/evaluation semantics milik backtest watchlist. Rule ini bukan perubahan definisi publication/readability market-data.

### C. Exit Model (horizon Weekly Swing)
- Horizon maksimum: **5 trading day** sejak entry (D+1 s/d D+5).
- `stop_price` dan `target_price` adalah theoretical policy levels; keduanya bukan otomatis harga fill.
- Sebelum evaluasi, theoretical levels dinormalisasi ke fraksi harga executable:
  - stop trigger dibulatkan turun ke fraksi valid;
  - target trigger dibulatkan naik ke fraksi valid;
  - marker: `price_fraction_rule = IDX_EQUITY_PRICE_BANDS`;
  - normalized stop/target memakai band fraksi dari theoretical level masing-masing;
  - marker: `price_fraction_reference = THEORETICAL_LEVEL`;
  - marker: `price_normalization_rule = CONSERVATIVE_STOP_FLOOR_TARGET_CEIL`.
  - canonical price-band table used for theoretical trigger normalization:
    - `< 200` → `1`;
    - `200 .. < 500` → `2`;
    - `500 .. < 2_000` → `5`;
    - `2_000 .. < 5_000` → `10`;
    - `>= 5_000` → `25`.
- Exit utama mengambil event pertama menurut urutan observable berikut:
  1) Jika **open(t) <= stop_trigger_price**, gap melewati stop dan fill wajib di **open(t)** dengan `GAP_THROUGH_STOP_AT_OPEN`.
  2) Jika **open(t) >= target_trigger_price**, gap melewati target dan fill wajib di **open(t)** dengan `GAP_THROUGH_TARGET_AT_OPEN`.
  3) Jika tidak ada opening gap dan **low(t) <= stop_trigger_price**, exit di normalized stop trigger.
  4) Jika tidak ada opening gap dan **high(t) >= target_trigger_price**, exit di normalized target trigger.
  5) Jika sampai akhir horizon belum kena stop/target, exit di executable **close(D+5)**.
- Jika open berada di antara stop dan target lalu dalam bar yang sama low menyentuh stop dan high menyentuh target, prioritas **STOP dulu** dan catat `BT_AMBIGUOUS_HIT_STOP_PRIOR`.
- Opening gap selalu dievaluasi sebelum high/low intraday; karena itu target-at-open dapat mendahului low intraday dan stop-at-open dapat mendahului high intraday.
- Artifact wajib membedakan `trigger_price` dari `executed_price`, serta mencatat `fill_rule` dan `gap_detected`.
- Seluruh execution-price markers pada section ini adalah canonical runtime rules, bukan grid/calibration knobs; caller atau paramset tidak boleh menggantinya.

### C1. Executable source-price rule (LOCKED)

- Entry, stop/target evaluation, dan time-exit hanya boleh memakai raw OHLC dalam satuan rupiah bulat; transformed/adjusted fractional OHLC bukan harga fill executable.
- `adj_close` tidak boleh menggantikan raw `open/high/low/close` untuk simulasi fill.
- Bar dengan OHLC fractional/transformed yang tidak sesuai fraksi harga harus fail closed:
  - entry: `BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY`;
  - exit: `BT_SKIP_NON_EXECUTABLE_PRICE_EXIT`.
- Kedua kondisi menghasilkan `ret_net = NULL`; runtime tidak boleh menebak raw price atau mengubah adjusted-looking OHLC menjadi fill.
- Marker: `source_price_mode = RAW_TRADABLE_OHLC_REQUIRED`.

### D. Level Stop/Target (deterministik)
- Jika policy menyimpan level di PLAN:
  - gunakan langsung `stop_price` dan `target_price` dari PLAN.
- Jika policy tidak menyimpan level:
  - stop berbasis ATR: `stop = entry_price * (1 - stop_atr_mult * atr14_pct)`
  - target berbasis RR: `target = entry_price + rr * (entry_price - stop)`
  - `stop_atr_mult` dan `rr` harus berasal dari paramset/backtest grid dan tercatat.

### E. Notional, Qty, Fee, Slippage (tanpa persen)
Untuk menghindari ketergantungan pada fee persen broker, backtest memakai model fee **IDR** dan notional deterministik.

**E1. Notional & qty**
- Notional per trade (LOCKED): `notional_idr = 10_000_000`.
  - Jika notional ingin diubah untuk eksperimen, itu **bukan** bagian dari kontrak LOCKED: wajib dicatat sebagai metadata run (input kalibrasi) dan tidak boleh dibandingkan apple-to-apple dengan run yang notional-nya berbeda.
- Lot size (LOCKED): `lot_size = 100` saham per lot.
- Qty saham:
  - `lots = floor(notional_idr / (entry_price * lot_size))`
  - Jika `lots < 1` → trade di-skip, catat `BT_SKIP_NOT_ENOUGH_NOTIONAL`.
  - `qty = lots * lot_size`

**E2. Fee model (LOCKED)**
Pilih **satu** model dan tulis eksplisit di metadata evaluasi; model tidak boleh campur dalam satu seri evaluasi.

- Model 1 (fixed fee per side, canonical default):
  - `fee_model = IDR_FIXED`
  - `fee_buy_idr  = 2500`
  - `fee_sell_idr = 2500`

- Model 2 (tiered berdasarkan nilai transaksi):
  - `fee_model = IDR_TIERED`
  - `fee_buy_idr  = f_buy(gross_buy_idr)`
  - `fee_sell_idr = f_sell(gross_sell_idr)`
  - fungsi `f_buy/f_sell` wajib dikunci di code/table terpisah dan versioned.

Rule (LOCKED):
- Jika tidak ada model fee real yang sudah dibakukan, default kontrak adalah `IDR_FIXED` dengan `2500/2500` per sisi agar semua evaluasi tetap reproduksibel.
- Bila nanti berpindah ke `IDR_TIERED`, hasil run lama tidak boleh dibandingkan apple-to-apple tanpa label `eval_model` yang berbeda.

Catatan: jika fee real di Ajaib tersedia sebagai biaya transaksi per order, fungsi/tabel tiered boleh dikalibrasi dari sample statement, tetapi begitu dipakai harus LOCKED.

**E3. Slippage (LOCKED)**
- Default: `slippage_entry_pct = 0` dan `slippage_exit_pct = 0` (ditulis eksplisit).
- Jika dipakai:
  - `entry_eff = entry_price * (1 + slippage_entry_pct)`
  - `exit_eff  = exit_price  * (1 - slippage_exit_pct)`

### F. Return & Metric Definitions (LOCKED)
- Gross amounts:
  - `gross_buy_idr  = entry_eff * qty`
  - `gross_sell_idr = exit_eff  * qty`
- Net PnL IDR:
  - `net_pnl_idr = gross_sell_idr - gross_buy_idr - fee_buy_idr - fee_sell_idr`
- Return net:
  - `ret_net = net_pnl_idr / (gross_buy_idr + fee_buy_idr)`
- Win flag:
  - `is_win = (ret_net > 0)`

**Aggregasi metrik (LOCKED):**
- Definisi group: `group=TOP` merujuk pada picks dengan `bucket_code='TOP_PICKS'`.
- `avg_ret_net_top`: rata-rata `ret_net` untuk trades dari picks `group=TOP` di seluruh periode backtest.
- `win_rate_top`: persentase `is_win` untuk trades dari picks `group=TOP` di seluruh periode backtest.
- `picks_count`: jumlah trade yang benar-benar dieksekusi (setelah skip rules).

### G. Risk & Activity Metrics (LOCKED)

Rule (LOCKED):
Metrik di section G bersifat **computed at query/report time** untuk analisis risiko/aktivitas,
dan **bukan** kolom wajib pada `watchlist_bt_eval`, kecuali schema `watchlist_bt_eval` secara eksplisit ditambah.

- `stopout_rate_top`:
  - Definisi: `(# trade group=TOP yang exit karena STOP) / (# trade group=TOP yang dieksekusi)`
  - Exit karena STOP mengikuti aturan Assumptions → C.
- `max_drawdown_top`:
  - Definisi: maximum peak-to-trough drawdown dari equity curve `group=TOP`.
  - Equity curve dihitung dari akumulasi `net_pnl_idr` per trade (urut kronologis by exit date).
- `turnover_top_per_week`: 
  - Definisi: rata-rata jumlah trade group=TOP yang dieksekusi per minggu selama window backtest.
  - Rumus: `turnover_top_per_week` = total_executed_trades_top / total_weeks_in_window.
  - Catatan: yang dihitung hanya trade yang lolos (tidak termasuk trade yang di-skip oleh Assumptions → H).

### H. Missing Data and Non-Tradable Bar Handling (LOCKED)
- Jika OHLC untuk hari entry tidak lengkap → skip trade, catat `BT_SKIP_MISSING_OHLC_ENTRY`.
- Jika bar entry published tetapi `volume <= 0` atau volume tidak tersedia → skip trade, catat `BT_SKIP_NO_TRADABLE_ENTRY`.
- Jika OHLC untuk salah satu hari evaluasi (D+1..D+5) tidak lengkap:
  - Hari itu tidak dipakai untuk hit stop/target.
  - Jika sampai D+5 tidak ada close yang valid untuk time-exit → skip trade, catat `BT_SKIP_MISSING_OHLC_EXIT`.
- Jika bar evaluasi published tetapi non-executable karena volume tidak positif:
  - Hari itu tidak dipakai untuk hit stop/target.
  - Jika time-exit tidak mempunyai bar executable dan tidak ada exit sah sebelumnya → skip trade, catat `BT_SKIP_NO_TRADABLE_EXIT`.
- Semua skip harus tercatat (count) agar evaluasi tidak menipu.
- Semua trade yang di-skip wajib memiliki `ret_net = NULL`; zero return sintetis dilarang.

### I. Ranking & Picks (LOCKED)
- Picks dibuat dari PLAN score pada D:
  - Top picks = N tertinggi yang lolos guard.
  - Secondary/Watch/Avoid mengikuti group semantics policy.
- CONFIRM tidak digunakan dalam backtest (backtest EOD-only).

### J. Determinism & Audit (LOCKED)
- Semua parameter yang mempengaruhi hasil backtest wajib berasal dari:
  - paramset/backtest grid (wajib tercatat), atau
  - konstanta LOCKED di file ini (mis. notional_idr, lot_size, slippage default).
- Jika ada perubahan angka/aturan di section ini → dianggap breaking change dan wajib re-run kalibrasi.

## Process
### 1) Backtest goals
- Maximize `avg_ret_net_top`
- Maximize `win_rate_top`
- Minimize `max_drawdown_top` dan `stopout_rate_top`
- Monitor `turnover_top_per_week` dan `picks_count`

### 2) Output backtest wajib
- `watchlist_bt_param_grid`
- `watchlist_bt_eval`
- `watchlist_bt_picks_ws`
- `watchlist_bt_universe_ws`
- `watchlist_bt_cutoffs_ws`

Tanpa `watchlist_bt_oos_eval_ws` (OOS proof), kalibrasi tidak boleh dipromote menjadi ACTIVE.

### 3) Calibration procedure (ringkas)

Dokumen ini tidak mengambil alih acceptance threshold OOS dan evaluation sufficiency; ownership threshold tetap pada file 16 dan 17.

1) Generate param grid dari seed MAN (TEMP/bt_target=true).
2) Run backtest 2 tahun untuk semua param_id.
3) Pilih best param_id dengan query canonical:

   Rule (LOCKED):
   Nilai threshold `:min_*` WAJIB berasal dari Parameter Registry (05) / paramset evaluasi.
   Tidak boleh hardcode angka di implementasi.

   Coverage semantics (LOCKED):
   - `total_trading_days_in_window` = seluruh trading date pada explicit replay window;
   - `ev.days_covered` = distinct replay date dengan minimal satu completed metrics-ready evaluation, ditambah explicit valid empty-recommendation date;
   - replay date yang seluruh trade candidate-nya di-skip karena calendar, missing price, atau non-tradable bar tidak dihitung covered;
   - implementasi dilarang mengisi `ev.days_covered` langsung dari jumlah requested replay dates.

   Mapping parameter (LOCKED):
   - `:min_trades` -> `ws.eval.min_trades`
   - `:min_days_covered` -> `ws.eval.min_days_covered`
   - `:min_p25_ret_net_top` -> `ws.eval.min_p25_ret_net_top`
   - `:min_month_win_rate_min` -> `ws.eval.min_month_win_rate_min`
   - `:min_month_avg_ret_net_min` -> `ws.eval.min_month_avg_ret_net_min`

   SQL (canonical):
   ```sql
   SELECT pg.*
   FROM watchlist_bt_param_grid pg
   JOIN watchlist_bt_eval ev
     ON ev.policy_code = pg.policy_code
    AND ev.param_id    = pg.param_id
   WHERE pg.policy_code = 'WS'
     AND ev.picks_count >= :min_trades
     AND ev.days_covered >= :min_days_covered
     AND ev.avg_ret_net_top > 0
     AND ev.median_ret_net_top >= 0
     AND ev.p25_ret_net_top >= :min_p25_ret_net_top
     AND ev.month_win_rate_min >= :min_month_win_rate_min
     AND ev.month_avg_ret_net_min >= :min_month_avg_ret_net_min
   ORDER BY
     ev.avg_ret_net_top DESC,
     ev.median_ret_net_top DESC,
     ev.month_win_rate_min DESC,
     ev.p25_ret_net_top DESC,
     ev.param_id ASC
   LIMIT 1;
   ```
4) Buat param_set baru (DRAFT):
   - parameter terkalibrasi => origin=BT, status=ACTIVE
   - parameter deterministik => origin=DET, status=ACTIVE
5) Promote param_set BT menjadi ACTIVE (lihat [`02_WS_CANONICAL_RUNTIME_FLOW.md`](02_WS_CANONICAL_RUNTIME_FLOW.md)).

## Evaluation metrics sufficiency (LOCKED)
Metrik pada `watchlist_bt_eval` wajib memenuhi spesifikasi:
[`16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`](validation/16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md).

Kalibrasi param_id dianggap tidak valid jika metrik minimum tidak tersedia atau gagal gating rules.

## Walk-forward / OOS proof (LOCKED)
Kalibrasi WS wajib memiliki bukti out-of-sample sesuai:
[`17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`](validation/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md).

Ringkasan OOS wajib tersimpan di:
`watchlist_bt_oos_eval_ws`.

## Outputs

Artefak resmi yang boleh dipakai sebagai proof atau promote harus tetap mengikuti manifest pada file 18.

- Paramset BT validated + audit trail.

## Failure modes
- Backtest dataset tidak konsisten => tidak boleh promote.

## Next
### Weekly Swing
- 13_WS_CONTRACT_TEST_CHECKLIST.md


## Implementation and historical separation pointers
- Persistence/universe schema details: `../../implementation/weekly_swing/contracts/WS_BACKTEST_PERSISTENCE_AND_UNIVERSE_SCHEMA_CONTRACT.md`.
- OOS runtime/grid implementation details: `../../implementation/weekly_swing/contracts/WS_BACKTEST_OOS_RUNTIME_IMPLEMENTATION_CONTRACT.md`.
- Campaign-specific C/R/S/P addenda and operator outcomes: `../../history/weekly_swing/campaign_addenda/WS_BACKTEST_CAMPAIGN_ADDENDA_HISTORY.md`.

These moves are documentation-role separation only; no strategy threshold, metric, gate, or execution rule was changed.
