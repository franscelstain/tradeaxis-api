# 12 — Backtest Schema & Calibration — Weekly Swing

Default backtest window: 2 tahun (configurable).

## Purpose

Dokumen ini adalah owner schema backtest WS dan flow kalibrasi parameter; evaluasi metrik minimum, OOS proof, dan artifact manifest dirujuk ke file khusus masing-masing.

Dokumen ini mengunci schema backtest dan aturan perhitungan evaluasi untuk Weekly Swing agar kalibrasi reproducible, audit-able, dan konsisten dengan kontrak runtime canonical Weekly Swing (PLAN / RECOMMENDATION / CONFIRM).
Dokumen ini juga menetapkan syarat validasi: BT coverage, universe equivalence, metric sufficiency, dan OOS proof.

## Scope lock (yang dikerjakan)
Backtest Weekly Swing mengikuti artefak resmi yang didefinisikan pada:
[`18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`](18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md).

**Schema backtest (LOCKED)**
Schema tabel backtest mengikuti: [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql).

Dokumen ini tidak menduplikasi daftar tabel; semua daftar artefak wajib merujuk ke Manifest.

Catatan audit: universe harian dan picks harian wajib tersedia untuk replay dan audit; lihat Manifest (18) untuk daftar artefak resminya.

Artefak yang tidak tercantum pada Manifest dianggap tidak digunakan dan tidak boleh diasumsikan ada.

Menetapkan mekanisme kalibrasi parameter WS dari backtest 2 tahun:
- menghasilkan param_set baru (origin=BT) yang dapat dipromosikan menjadi ACTIVE.

## Proof of BT coverage (LOCKED)
Semua parameter dengan origin=BT wajib tercakup di:
[`14_WS_BT_COVERAGE_MATRIX_LOCKED.md`](14_WS_BT_COVERAGE_MATRIX_LOCKED.md).

Kalibrasi backtest dianggap tidak valid jika ada parameter origin=BT yang:
- tidak punya mapping ke kolom `watchlist_bt_param_grid`, atau
- tidak punya bukti audit (universe/picks/eval), atau
- tidak lolos test `BT_COVERAGE_GUARD`.

## Universe equivalence (LOCKED)
Backtest universe (`watchlist_bt_universe_ws`) wajib setara dengan production PLAN universe
untuk tanggal EOD yang sama sesuai:
[`15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md).

Bukti equivalence WAJIB menggunakan snapshot dari production PLAN yang mengikuti schema resmi:
[`db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md`](db/PLAN_UNIVERSE_SNAPSHOT_SCHEMA.md).

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
[`16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md`](16_WS_EVAL_METRICS_SUFFICIENCY_LOCKED.md).

Kalibrasi param_id dianggap tidak valid jika metrik minimum tidak tersedia atau gagal gating rules.

## Walk-forward / OOS proof (LOCKED)
Kalibrasi WS wajib memiliki bukti out-of-sample sesuai:
[`17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`](17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md).

Ringkasan OOS wajib tersimpan di:
`watchlist_bt_oos_eval_ws`.

## Outputs

Artefak resmi yang boleh dipakai sebagai proof atau promote harus tetap mengikuti manifest pada file 18.

- Paramset BT validated + audit trail.

## Failure modes
- Backtest dataset tidak konsisten => tidak boleh promote.

## DDL
Schema backtest (DDL) disimpan sebagai artefak di: [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql).

## Universe rule (DET) (LOCKED)

Universe backtest untuk Weekly Swing harus deterministik dan bisa diulang.

### 1) Sumber ticker
Per `asof_eod_date`:
1) Ambil semua ticker aktif dari tabel master (mis. `tickers`) yang memiliki data OHLC EOD pada `asof_eod_date`.
2) Exclude ticker yang ada di `liquidity.exclude_tickers` (paramset MAN).

### 2) Data-quality vs eligibility (LOCKED)
Dokumen ini membedakan dua hal:
- **required_ok** = data-quality untuk kebutuhan guardrails & snapshot metrics.
- **eligible_ok** = hasil akhir eligibility untuk backtest universe, yaitu:
  `eligible_ok = required_ok AND guard_ok`.

### 3) Required fields untuk guardrails (LOCKED)
Field required minimal agar `required_ok=TRUE`:
- OHLC: `close` (dan `high/low/open` untuk evaluasi trade setelah pick)
- Volume: `volume`
- Guard/metrics snapshot: `dv20_idr`, `atr14_pct`, `vol_ratio`

Jika salah satu field di atas NULL/invalid:
- ticker tetap dicatat di `watchlist_bt_universe_ws` dengan `required_ok=FALSE`,
- `missing_fields` wajib diisi,
- `eligible_ok=FALSE`,
- `reason_code` mengikuti prioritas canonical reason pada [`15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md) (contoh: `WS_DATA_MISSING`).

### 4) Field untuk scoring (LOCKED)
Indikator yang dipakai untuk menghitung `score_total` (contoh: `roc20`, `hh20`) **tidak termasuk** daftar required fields guardrails kecuali policy WS secara eksplisit menetapkannya sebagai requirement eligibility.

Jika indikator scoring missing (LOCKED):
- ticker tetap boleh eligible selama guardrails terpenuhi,
- skor komponen yang membutuhkan indikator tersebut = 0,
- `missing_fields` tetap mencatat indikator yang missing (untuk audit), namun tidak menjatuhkan `required_ok`.
  Aturan ini harus konsisten dengan production PLAN.

Rule (LOCKED):
`missing_fields` boleh berisi gabungan field guardrails dan field scoring; namun `required_ok` hanya dipengaruhi oleh required fields guardrails (lihat Section 3).

### 5) Audit storage (LOCKED)
Untuk audit/re-run **wajib** menyimpan universe harian di `watchlist_bt_universe_ws` (lihat [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql)) minimal berisi:
- `required_ok, missing_fields, guard_ok, eligible_ok, dv20_idr, atr14_pct, vol_ratio, reason_code`

Reason_code memakai dictionary WS_* yang **resmi di-seed** (contoh: `WS_DATA_MISSING`, `WS_LIQ_FAIL`) sesuai prioritas canonical reason di [`15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md). Alias nama lain tidak boleh dipakai.

## Schema: watchlist_bt_universe_ws (AUDIT) (LOCKED)

Tabel ini **wajib** ada untuk membuat backtest reproducible dan audit-friendly.

Lokasi DDL: [`db/BACKTEST_SCHEMA_DDL.sql`](db/BACKTEST_SCHEMA_DDL.sql)

Kolom (harus match DDL):
- `asof_eod_date` (DATE, NOT NULL) — tanggal EOD universe
- `ticker_id` (INT, NOT NULL) — id ticker
- `required_ok` (TINYINT(1), NOT NULL) — data-quality only (bukan eligibility final)
- `reason_code` (VARCHAR(32), NULL) — reason WS_* (contoh: `WS_DATA_MISSING`) saat `required_ok=0`
- `missing_fields` — daftar field required yang missing/invalid (CSV string)
- `guard_ok` — 1 jika lolos semua guardrail, 0 jika tidak
- `eligible_ok` — 1 jika required_ok=1 dan guard_ok=1
- `dv20_idr`, `atr14_pct`, `vol_ratio` — metric snapshot untuk debug equivalence

Primary key:
- `(asof_eod_date, ticker_id)`

Indexes:
- `idx_bt_univ_ws_req (asof_eod_date, required_ok)`
- `idx_bt_univ_ws_reason (asof_eod_date, reason_code)`
- `idx_bt_univ_ws_elig (asof_eod_date, eligible_ok)`

## Next
### Weekly Swing
- 13_WS_CONTRACT_TEST_CHECKLIST.md

## OOS Runtime Gap Closure — Deterministic Grid, Evaluation Identity, and Bounded Reads (LOCKED)

The minimum chronological OOS runtime uses one explicit historical window. Operators must not split one requested proof into multiple commands and merge the results, because every command would create a different 70/30 split and a different calibration boundary.

### Canonical grid source

Runtime calibration reads only `watchlist_bt_param_grid`, ordered by `param_id ASC`. The canonical bootstrap catalog is implemented by `WatchlistBacktestParamGridCatalog` and persisted idempotently through:

- `php artisan watchlist:backtest-param-grid-seed`;
- `database/seeders/Watchlist/WatchlistBacktestParamGridSeeder.php`;
- [`db/BACKTEST_PARAM_GRID_SEED.sql`](db/BACKTEST_PARAM_GRID_SEED.sql) for operator SQL deployment.

The bootstrap catalog is deterministic and curated before OOS execution. The current canonical bootstrap cardinality is `24`, exposed by `WatchlistBacktestParamGridCatalog::CATALOG_COUNT`; catalog code, SQL seed cardinality, repository persistence, and static guards must derive from that single source instead of duplicating a literal count. Any cardinality change requires synchronized owner-doc, catalog, SQL seed, and test updates.

It is not generated from OOS metrics, random search, Bayesian search, or current runtime outcomes. Every row must satisfy:

- scoring weights sum to `1.0`;
- quantiles are inside `0..1` and `top_min_score_q >= secondary_min_score_q`;
- targets are positive integers;
- ATR values use fractional units;
- `stop_atr_mult > 0` and `min_rr > 0`.

The official grid columns include:

```text
min_dv20_idr
max_atr14_pct
min_vol_ratio
w_momentum
w_volume
w_breakout
w_risk
stop_atr_mult
min_rr
top_picks_target
secondary_target
top_min_score_q
secondary_min_score_q
```

### Versioned evaluation identity

`watchlist_bt_eval` identity is:

```text
policy_code + param_id + eval_model + paramset_hash + from_date + to_date
```

This identity preserves historical evidence when evaluation semantics or a paramset snapshot changes. An old row must not be overwritten or deleted merely to permit a rerun. Existing unversioned rows are migrated to explicit legacy markers by the gap-closure migration/SQL.

Exact duplicate payloads are idempotent. The same identity with different metrics fails closed. The OOS identity also includes `is_eval_id`, ensuring that an OOS result is bound to one exact frozen IS evaluation and corrected IS semantics can coexist with historical proof rows.

### Memory-bounded published-price read

After PLAN/recommendation candidates are frozen, the runtime builds an exact `trade_date -> ticker_code[]` map for entry/exit evaluation and reads only those date/ticker pairs through the official published EOD read surface. The runtime must not materialize `all candidate tickers × all required dates` when most pairs are not consumed.

Canonical markers:

```text
pricing_model = PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE
price_read_mode = TARGETED_DATE_TICKER_MAP
targeted_date_ticker_read = true
```

These runtime-owned markers are bound to the returned strategy payload **before** the frozen strategy hash is computed and before any future-price read begins. This binding must update the top-level and meta paramset snapshots consistently, while leaving missing canonical evaluation thresholds missing so threshold validation can still fail closed rather than fabricating evidence.

The bootstrap strategy risk defaults remain `risk.stop_atr_mult = 1.5` and `risk.min_rr = 1.5` when those inputs are absent. Explicit grid/runtime values override the defaults; a missing nested `risk` section must not silently produce null trade-candidate risk fields.

IS calibration is in-memory and does not write a temporary JSON file for every grid row. One final proof artifact is exported by the OOS orchestrator. Per-grid iteration state must be released before evaluating the next row.

### Trade evidence in the proof artifact

For every IS evaluation reference, the proof artifact includes compact deterministic extreme-trade evidence (worst and best evaluated trades) with entry/exit dates, prices, volume, stop/target source, ATR/RR inputs, return, and publication lineage. This evidence supports diagnosis without creating an unofficial shadow table. It does not replace the official table allowlist or the separate full coverage requirements for promotion.

## Canonical grid cross-field projection rule (LOCKED)

`watchlist_bt_param_grid.max_atr14_pct` is an explicit grid axis, while the minimum OOS bootstrap schema does not persist separate `atr_ideal_low` and `atr_ideal_high` columns. A grid row must never be combined with incompatible active defaults.

Before strategy/scoring execution, the runtime paramset factory must resolve the companion ATR band deterministically:

```text
min_atr14_pct = canonical active minimum
atr_ideal_high = max(min_atr14_pct, min(canonical default atr_ideal_high, grid.max_atr14_pct))
atr_ideal_low  = max(min_atr14_pct, min(canonical default atr_ideal_low, atr_ideal_high))
```

Canonical marker:

```text
risk_band_rule = CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR
```

Required invariants:

```text
min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct
```

The resolved values and rule must be present in the immutable paramset snapshot under `bt_grid_resolution`. This is a deterministic compatibility projection, not OOS tuning. It may not inspect IS/OOS metrics, prices, or ranking results. A row with `max_atr14_pct < min_atr14_pct` must fail closed as invalid.

## R2 Catalog Identity and IS-Only Calibration Contract

R1, R2, and semantic C-campaign catalogs must coexist in the official tables without implicit “latest” or “active catalog” selection.

### Catalog identity

`watchlist_bt_param_grid` persists:

```text
policy_code
catalog_code
catalog_version
catalog_hash
row_code
row_hash
rationale
```

The canonical row identity is `(policy_code, catalog_code, row_code)`. A seed rerun with an identical payload is idempotent. The same identity with a different payload fails closed. R1 remains `WS_BT_GRID_BOOTSTRAP_2026_06` with count `24` and hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c`. R2 remains `WS_BT_GRID_ENTRY_QUALITY_R2_2026_06` with count `12` and hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5`. C01 remains `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06` with version `C01`, count `8`, and code-owned hash `604ac98f6f193a4c317d4f25582deada84682846`. C02 is `WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06` with version `C02`, count `8`, and code-owned hash `7287c438e15bd03d6beb4796e4d5159ecd8ed59a`; it remains rejected as a strategy-quality catalog. C03 is `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06` with version `C03`, count `10`, and code-owned hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`; it remains rejected as a strategy-quality catalog. C04 is `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06` with version `C04`, count `10`, and code-owned hash `0ce3a313c45432c5a4d607def12b3f774988f324`; it remains rejected as a strategy-quality catalog. C05 is `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06` with version `C05`, count `12`, and code-owned hash `476af5dde18079b1270556bc44bbc632edd46e27`; it remains rejected as a strategy-quality catalog. C06 is `WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06` with version `C06`, count `12`, and code-owned hash `6c93d67fb77319a02cecc3d96fd99bb0e139a1ac`; it remains rejected as a strategy-quality catalog. C07 is `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06` with version `C07`, count `12`, and code-owned hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`; it is derived from C01/C04/C05/C06 forensic evidence plus runtime feature audit and requires R1/R2/C01/C02/C03/C04/C05/C06 immutability before seed/calibration.

### Curated entry-quality columns

The official grid stores the runtime-consumed entry-quality fields for liquidity, volume, ATR band, ROC/breakout setup, score weights, grouping quantiles, and catalog-scoped optional feature confirmations. R2, C01, C02, C03, C04, C05, C06, and C07 catalogs are finite, curated, deterministic, and explicit. Random, Bayesian, latest-catalog fallback, active-catalog fallback, unsupported sector-filter injection, and post-result catalog mutation are forbidden.

### Evaluation identity

`watchlist_bt_eval` identity includes:

```text
policy_code
catalog_code
catalog_version
param_id
paramset_hash
eval_model
from_date
to_date
```

Exact reruns are idempotent; conflicting payloads fail with `WS_BT_EVAL_IDENTITY_CONFLICT`.

### Strict IS boundary

The IS calibration command accepts only the exact IS window for immutable R2/C01/C02/C03/C04/C05/C06/C07 execution:

```text
2023-01-02 through 2025-05-21
```

The published-price runtime receives `hard_market_data_to_date = 2025-05-21`. Because the canonical holding horizon is five trading days, the final five IS trading dates are retained in the calendar/lineage manifest but excluded from entry generation. This prevents any exit evaluation from requesting reserved OOS prices. The rule is:

```text
EXCLUDE_LAST_HOLDING_DAYS_FROM_ENTRY_GENERATION;KEEP_ALL_PRICE_READS_WITHIN_IS
```

The runtime may not call an OOS service/repository, write `watchlist_bt_oos_eval_ws`, infer a current/latest date, or read market data after the explicit IS boundary. Canonical metric gates remain owned by file 16 and are unchanged.

### Fixed execution snapshot

Every R2/C01/C02/C03/C04/C05/C06/C07 row uses exactly:

```text
ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS
risk.stop_atr_mult=1.50
risk.min_rr=1.50
grouping.top_picks_target=5
grouping.secondary_target=10
```

R2/C01/C02/C03/C04/C05/C06/C07 success freezes a best-IS binding only when every canonical gate passes. R2/C01/C02/C03/C04/C05/C06/C07 failure creates no binding and never selects best-of-failed. C01/C02/C03/C04/C05/C06/C07 are not eligible for OOS proof until a separate IS runtime creates a valid frozen binding for that exact catalog.

### C02 final operator result

C02 has been operator-validated for implementation/test/seed/execution, but rejected as a strategy-quality catalog. This result does not change the canonical gates.

```text
C02 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06
C02 catalog_version=C02
C02 catalog_count=8
C02 catalog_hash=7287c438e15bd03d6beb4796e4d5159ecd8ed59a
C02 PHPUnit=PASS / OK (12 tests, 391 assertions)
Full Watchlist PHPUnit=PASS / OK (238 tests, 4182 assertions)
C02 seed=PASS / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / oos_executed=0 / production_ready=0
C02 IS run 1=C02_GRID_FAILED_IS_QUALITY / is_valid_param_count=0 / is_failed_param_count=8 / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
C02 IS run 2=C02_GRID_FAILED_IS_QUALITY / is_valid_param_count=0 / is_failed_param_count=8 / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2
```

Post-docs validation after the C02 final documentation/forensic CSV sync:

```text
scope=DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
C02 PHPUnit post-docs=PASS / OK (12 tests, 391 assertions) / Time 00:01.281 / Memory 14.00 MB / exit code 0
Full Watchlist PHPUnit post-docs=PASS / OK (238 tests, 4182 assertions) / Time 00:04.431 / Memory 24.00 MB / exit code 0
post_docs_validation_verdict=PASS
```

This confirms the C02 final docs/static-guard sync remained test-safe. It does not change C02 strategy quality, OOS, or production-readiness status.

C02 failure reason distribution:

```text
WS_BT_EVAL_DOWNSIDE_FAIL=8
WS_BT_EVAL_ROBUST_RETURN_FAIL=8
WS_BT_EVAL_STABILITY_FAIL=8
```

C02 final forensic summary: all rows had sufficient sample (`minimum_coverage=true`, `minimum_trade_count=true`) but failed return/downside/stability quality. Metric ranges were `picks_count=1360..1435`, `win_rate_top=39.44%..41.82%`, `median_ret_net_top=-2.10%..-1.72%`, `p25_ret_net_top=-5.59%..-4.97%`, `month_win_rate_min=14.03%..23.21%`, and `period_fail_count=18..22` of `27`.

C02 has no valid IS param, no best IS binding, and no best IS binding hash. Therefore OOS remains forbidden for C02 and production readiness remains false. The next same-focus catalog must be a new `C03` identity, not a mutation of C02.

### C03 final operator result

C03 has been implemented and operator-validated as a new IS-quality catalog candidate. It is not a C02 mutation, not a C02 pass-forcing patch, and not an OOS unlock.

```text
C03 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06
C03 catalog_version=C03
C03 catalog_count=10
C03 catalog_hash=29e15ceab1b3f7dc31a21f339ac6ab7483e14800
C03 design_source=C02 forensic metrics + C01 diagnostic evidence
C03 sector_filter_used=false
C03 OOS=NOT_RUN
C03 production_ready=0
```

C03 operator validation result:

```text
C03 PHPUnit filter=PASS / OK (12 tests, 461 assertions)
Full Watchlist PHPUnit=PASS / OK (250 tests, 4643 assertions)
C03 seed=PASS / inserted_count=10 / updated_count=0 / existing_count=0
R1/R2/C01/C02 immutable=1
C03 IS run 1=C03_GRID_FAILED_IS_QUALITY / valid=0 / failed=10 / artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
C03 IS run 2=C03_GRID_FAILED_IS_QUALITY / valid=0 / failed=10 / artifact_hash=649e8fead0c57262307f749a4776f053f5ccd0f8
C03 deterministic=true
C03 OOS=NOT_RUN
C03 production_ready=0
```

C03 failed quality with:

```text
reason_code=WS_BT_C03_NO_VALID_IS_CANDIDATE
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
```

C03 final decision:

```text
C03_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C03 is not eligible for OOS because it has no valid IS candidate, no best IS param, and no best IS binding hash.

The next same-focus catalog must be a new `C04` identity, not a mutation of C03. C04 must change candidate-selection axis/logic using only runtime-supported data and must not loosen canonical quality gates.

C03 forensic result is recorded in:

```text
docs/watchlist/audit/WS_C03_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c03-forensic-summary.csv
```

C04 design input is recorded in:

```text
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C04_DESIGN_INPUT_NOTE.md
```

### C04 final operator result

C04 has been implemented, seeded, and calibrated as a new IS-quality catalog candidate. It is not a C03 mutation, not a C03 pass-forcing patch, and not an OOS unlock.

```text
C04 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06
C04 catalog_version=C04
C04 catalog_count=10
C04 catalog_hash=0ce3a313c45432c5a4d607def12b3f774988f324
C04 design_source=C01/C02/C03 forensic metrics + runtime-supported candidate-selection axes
C04 sector_filter_used=false
C04 OOS=NOT_RUN
C04 production_ready=0
```

C04 validation result:

```text
C04 PHPUnit filter=PASS / OK (14 tests, 499 assertions)
Full Watchlist PHPUnit=PASS / OK (264 tests, 5142 assertions)
C04 seed=PASS / inserted_count=10 / updated_count=0 / existing_count=0
R1/R2/C01/C02/C03 immutable=1
C04 IS run 1=C04_GRID_FAILED_IS_QUALITY / valid=0 / failed=10 / artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
C04 IS run 2=C04_GRID_FAILED_IS_QUALITY / valid=0 / failed=10 / artifact_hash=fe964ee879dddc8aa8a83372e8c2d05aed5e8259
C04 deterministic=true
C04 OOS=NOT_RUN
C04 production_ready=0
```

C04 failed quality with:

```text
reason_code=WS_BT_C04_NO_VALID_IS_CANDIDATE
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
```

C04 final forensic summary:

```text
picks_count=82..176
median_ret_net_top=-1.2712%..-0.0501%
p25_ret_net_top=-3.8881%..-3.0868%
month_win_rate_min=0.00%..0.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=10,WS_BT_EVAL_MIN_TRADES_FAIL=7,WS_BT_EVAL_ROBUST_RETURN_FAIL=10,WS_BT_EVAL_STABILITY_FAIL=10
```

C04 final decision:

```text
C04_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C04 is not eligible for OOS because it has no valid IS candidate, no best IS param, and no best IS binding hash. OOS has not been run and must not be claimed PASS.

C04 forensic result is recorded in:

```text
docs/watchlist/audit/WS_C04_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c04-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C04_DESIGN_NOTE.md
```

### C05 final operator result

C05 has been implemented, seeded, and calibrated as a new IS-quality catalog candidate. It is not a C04 mutation and not an OOS unlock.

```text
C05 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06
C05 catalog_version=C05
C05 catalog_count=12
C05 catalog_hash=476af5dde18079b1270556bc44bbc632edd46e27
C05 design_source=C04 forensic metrics + runtime-supported soft sample-aware candidate-selection axes
C05 sector_filter_used=false
C05 OOS=NOT_RUN
C05 production_ready=0
```

C05 validation result:

```text
C05 PHPUnit filter=PASS / OK (13 tests, 523 assertions)
Full Watchlist PHPUnit=PASS / OK (277 tests, 5665 assertions)
C05 seed=PASS / inserted_count=12 / updated_count=0 / existing_count=0
R1/R2/C01/C02/C03/C04 immutable=1
C05 IS run 1=C05_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=f8288cb2d395e397f433dae854c0ad80b4650a8d
C05 IS run 2=C05_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=f8288cb2d395e397f433dae854c0ad80b4650a8d
C05 deterministic=true
C05 OOS=NOT_RUN
C05 production_ready=0
```

C05 failed quality with:

```text
reason_code=WS_BT_C05_NO_VALID_IS_CANDIDATE
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
```

C05 final forensic summary:

```text
picks_count=370..886
median_ret_net_top=-1.6122%..-0.7301%
p25_ret_net_top=-4.0209%..-3.2708%
month_win_rate_min=0.00%..18.75%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=12,WS_BT_EVAL_ROBUST_RETURN_FAIL=12,WS_BT_EVAL_STABILITY_FAIL=12
```

C05 final decision:

```text
C05_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C05 is not eligible for OOS because it has no valid IS candidate, no best IS param, and no best IS binding hash. OOS has not been run and must not be claimed PASS.

C05 forensic result is recorded in:

```text
docs/watchlist/audit/WS_C05_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c05-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C05_DESIGN_NOTE.md
```

### C06 final operator result

C06 has been implemented, seeded, and calibrated as a new IS-quality catalog candidate. It is not a C05 mutation and not an OOS unlock.

```text
C06 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06
C06 catalog_version=C06
C06 catalog_count=12
C06 catalog_hash=6c93d67fb77319a02cecc3d96fd99bb0e139a1ac
C06 design_source=C01/C04/C05 forensic metrics + runtime-supported moderate-liquidity/volume/ROC cap axes
C06 sector_filter_used=false
C06 OOS=NOT_RUN
C06 production_ready=0
```

C06 validation result:

```text
C06 PHPUnit filter=PASS / OK (13 tests, 503 assertions)
Full Watchlist PHPUnit=PASS / OK (290 tests, 6168 assertions)
C06 seed=PASS / inserted_count=12 / updated_count=0 / existing_count=0
R1/R2/C01/C02/C03/C04/C05 immutable=1
C06 IS run 1=C06_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=ede8ca6f53ea49141a5e047e6094b7a282cdb232
C06 IS run 2=C06_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=ede8ca6f53ea49141a5e047e6094b7a282cdb232
C06 deterministic=true
C06 OOS=NOT_RUN
C06 production_ready=0
```

C06 failed quality with:

```text
reason_code=WS_BT_C06_NO_VALID_IS_CANDIDATE
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_MIN_TRADES_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
```

C06 final forensic summary:

```text
picks_count=9..214
median_ret_net_top=-1.6757%..1.6637%
p25_ret_net_top=-3.4390%..-0.6101%
month_win_rate_min=0.00%..0.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=5,WS_BT_EVAL_MIN_TRADES_FAIL=9,WS_BT_EVAL_ROBUST_RETURN_FAIL=10,WS_BT_EVAL_STABILITY_FAIL=12
```

C06 final decision:

```text
C06_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C06 is not eligible for OOS because it has no valid IS candidate, no best IS param, and no best IS binding hash. OOS has not been run and must not be claimed PASS.

C06 forensic result is recorded in:

```text
docs/watchlist/audit/WS_C06_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c06-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C06_DESIGN_NOTE.md
```

### C07 final operator result

C07 has been implemented, seeded, and calibrated as a new IS-quality catalog candidate after a runtime feature audit. It is not a C06 mutation and not an OOS unlock.

```text
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C07 design_source=C01/C04/C05/C06 forensic metrics + runtime-supported short-term/range/sector-relative/event-risk feature audit
C07 sector_filter_used=false
C07 OOS=NOT_RUN
C07 production_ready=0
```

C07 validation result:

```text
C07 PHPUnit filter=PASS / OK (10 tests, 376 assertions)
Full Watchlist PHPUnit=PASS / OK (300 tests, 6544 assertions)
C07 seed=PASS / inserted_count=12 / updated_count=0 / existing_count=0
R1/R2/C01/C02/C03/C04/C05/C06 immutable=1
C07 IS run 1=C07_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=c562d0a37ec7911c17c50072413fbbae25bb6114
C07 IS run 2=C07_GRID_FAILED_IS_QUALITY / valid=0 / failed=12 / artifact_hash=c562d0a37ec7911c17c50072413fbbae25bb6114
C07 deterministic=true
C07 OOS=NOT_RUN
C07 production_ready=0
```

C07 failed quality with:

```text
reason_code=WS_BT_C07_NO_VALID_IS_CANDIDATE
is_failure_reason_codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
param_id_best_is=
best_is_binding_hash=
```

C07 final forensic summary:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
failure_distribution=WS_BT_EVAL_DOWNSIDE_FAIL=12,WS_BT_EVAL_ROBUST_RETURN_FAIL=12,WS_BT_EVAL_STABILITY_FAIL=12
```

C07 final decision:

```text
C07_REJECTED_AS_STRATEGY_QUALITY_CATALOG
```

C07 is not eligible for OOS because it has no valid IS candidate, no best IS param, and no best IS binding hash. OOS has not been run and must not be claimed PASS.

C07 forensic result is recorded in:

```text
docs/watchlist/audit/WS_C07_OPERATOR_FORENSIC_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c07-forensic-summary.csv
docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C07_DESIGN_NOTE.md
```

### C07 scoped failure drilldown addendum

After C07 failed IS quality, a scoped IS-only drilldown surface was added so heavy catalogs can be diagnosed by explicit `param_id` or `row_code` without full-catalog timeout. This is diagnostic-only and does not change any catalog identity, quality gate, or OOS boundary.

```text
C07 scoped drilldown params=102,106
C07 scoped drilldown artifact_hash_param_102=c362ff6682a69b8db145887214b137e786ea731a
C07 scoped drilldown artifact_hash_param_106=f7a91a3e9dc1c3ab13aedd04a7daabf51f90201e
C07 scoped drilldown next_focus=RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG
C07 scoped drilldown next_decision=NEXT_CATALOG_NOT_DESIGNED
C08 created=false
OOS=NOT_RUN
production_ready=0
```

Scoped findings:

```text
param_102 median=-0.6993% / p25=-3.4831% / month_win_min=25.00%
param_106 median=-0.7569% / p25=-3.4276% / month_win_min=20.59%
missing_runtime_evidence_field=corporate_action_flag
```

The scoped drilldown confirms that C07 remains ineligible for OOS and does not justify a same-shape C08 threshold retune. Any future catalog must be based on additional runtime payload enrichment or a distinct approved strategy family/exit model.

Scoped drilldown result is recorded in:

```text
docs/watchlist/audit/WS_C07_SCOPED_FAILURE_DRILLDOWN_FINAL_RESULT.md
docs/watchlist/audit/_artifacts/c07-scoped-drilldown-summary.csv
```

### C08 runtime payload and batched C07 drilldown addendum

After the C07 scoped drilldown found a runtime evidence gap, C08 was handled as a diagnostic/runtime enrichment session, not as a new strategy catalog.

```text
C08 strategy_catalog_created=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
OOS=NOT_RUN
production_ready=0
```

Runtime payload enrichment carries the following source-backed nullable context into diagnostic evidence when present:

```text
corporate_action_types
trading_status_code
event_risk_reasons
```

The enrichment does not convert missing source context to `0`. `corporate_action_flag` may be derived only when non-empty `corporate_action_types` exists and the explicit flag is absent.

The C08 batch diagnostic command is IS-only:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c08-batched-c07-drilldown --summary=storage/app/watchlist/backtest/c08-batched-c07-drilldown-summary.csv --overwrite
```

Executed batch result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_sha1=49101D6AA702A898A3F691A7553823A8DFB2F125
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

C08 batched findings:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
available_event_context=trading_status_code,event_risk_flag,is_suspended,is_uma
missing_runtime_evidence_fields=corporate_action_flag,corporate_action_types,event_risk_reasons
next_decision=NEXT_CATALOG_NOT_DESIGNED
```

C08 does not change C07 strategy quality. C07 remains ineligible for OOS because no valid IS candidate, `param_id_best_is`, or `best_is_binding_hash` exists.

C08 result is recorded in:

```text
docs/watchlist/audit/WS_C08_RUNTIME_PAYLOAD_AND_BATCHED_C07_DRILLDOWN_FINAL_RESULT.md
docs/watchlist/audit/WS_C08_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c08-batched-c07-drilldown-summary.csv
```

### C09 nullable event-context runtime coverage addendum

C09 did not create a strategy catalog. It clarified runtime diagnostic semantics for source-backed nullable event context after C08 showed corporate-action fields as missing in evaluated C07 trade evidence.

```text
C09 strategy_catalog_created=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
OOS=NOT_RUN
production_ready=0
```

Read-only IS source coverage:

```text
market_data_corporate_actions rows=262
market_data_trading_status_events rows=1469
eod_indicators rows=501386
eod_indicators corporate_action_types_present=243
eod_indicators event_risk_reasons_present=28746
eod_indicators trading_status_code_present=69560
```

The IS-only drilldown artifact now distinguishes:

```text
AVAILABLE_IN_RUNTIME_EVIDENCE
AVAILABLE_NULLABLE_NO_POSITIVE_RUNTIME_EVIDENCE
FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE
```

Executed C09 batch:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c09-batched-c07-nullable-context-drilldown --summary=storage/app/watchlist/backtest/c09-batched-c07-nullable-context-summary.csv --overwrite
```

Executed result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_sha1=4A317C890F416619FA2F24396D1EC9DDDE8CC3AB
missing_runtime_evidence_fields=
nullable_runtime_no_positive_evidence_fields=corporate_action_flag|corporate_action_types|event_risk_reasons
next_focus=STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG
next_decision=NEXT_CATALOG_NOT_DESIGNED
oos_executed=0
production_ready=0
```

C09 batched strategy-quality metrics:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
```

C09 closes the runtime diagnostic semantics gap, but it does not change strategy quality. C07 remains rejected and remains ineligible for OOS.

C09 result is recorded in:

```text
docs/watchlist/audit/WS_C09_NULLABLE_EVENT_CONTEXT_RUNTIME_COVERAGE_FINAL_RESULT.md
docs/watchlist/audit/WS_C09_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c09-batched-c07-nullable-context-summary.csv
```

### C10 exit-model diagnostic addendum

C10 did not create a strategy catalog. It added diagnostic-only exit outcome evidence after C09 confirmed that runtime diagnostic fields are represented well enough for C07 strategy-quality review.

```text
C10 strategy_catalog_created=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C10 OOS=NOT_RUN
C10 production_ready=0
```

The C10 batch diagnostic command is IS-only:

```text
php artisan watchlist:backtest-is-diagnose-batch --catalog-code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06 --from=2023-01-02 --to=2025-05-21 --output-dir=storage/app/watchlist/backtest/c10-batched-c07-exit-model-drilldown --summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --overwrite
```

Executed C10 result:

```text
status=PASS
reason_code=WS_BT_IS_FAILURE_DRILLDOWN_BATCH_READY
diagnostic_param_count=12
ready_count=12
blocked_count=0
summary_sha1=04EE547EE3F982901CABE23E55078868F14104C9
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

C10 batched strategy-quality metrics:

```text
picks_count=728..1355
median_ret_net_top=-1.0279%..-0.6993%
p25_ret_net_top=-4.0156%..-3.4276%
month_win_rate_min=17.86%..25.00%
hit_target_count=168..249
hit_stop_count=315..504
timeout_hold_expired_count=443..667
```

C10 confirms that C07 remains rejected and remains ineligible for OOS. The exit outcome evidence supports a future explicitly approved exit-model or strategy-family redesign review, but it does not justify mutating C07, selecting best-of-failed, or launching OOS.

C10 result is recorded in:

```text
docs/watchlist/audit/WS_C10_EXIT_MODEL_DIAGNOSTIC_FINAL_RESULT.md
docs/watchlist/audit/WS_C10_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c10-batched-c07-exit-model-summary.csv
```

### C11 exit-model contract audit addendum

C11 did not create a strategy catalog. It formalized the post-C10 decision gate: exit-model catalog work is not authorized under the current C01-C07 fixed-execution contract.

```text
C11 strategy_catalog_created=false
C11 exit_model_catalog_authorized=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C11 OOS=NOT_RUN
C11 production_ready=0
```

The C11 command consumes C10 IS-only evidence and writes a JSON contract artifact:

```text
php artisan watchlist:backtest-exit-model-contract-audit --c10-summary=storage/app/watchlist/backtest/c10-batched-c07-exit-model-summary.csv --output=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --overwrite
```

Executed C11 result:

```text
status=PASS
reason_code=WS_BT_C11_EXIT_MODEL_CONTRACT_AUDIT_READY
summary_row_count=12
source_summary_sha1=04ee547ee3f982901cabe23e55078868f14104c9
hit_target_total=2585
hit_stop_total=4927
timeout_hold_expired_total=6858
exit_model_catalog_authorized=0
next_decision=NEXT_CATALOG_NOT_DESIGNED
strategy_catalog_created=0
oos_executed=0
artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
production_ready=0
```

C11 blocking reasons:

```text
C01_C07_FACTORY_REJECTS_EXIT_AXIS_DRIFT
PUBLISHED_RUNTIME_FORCES_HOLD_5
PARAM_GRID_SCHEMA_LACKS_TARGET_STOP_PERCENT_FIELDS
C10_SUMMARY_REMAINS_BELOW_LOCKED_IS_GATES
C10_EXIT_OUTCOMES_STOP_OR_TIMEOUT_DOMINATE_TARGET
```

Supported-but-not-authorized exit axes are classified as follows:

```text
risk.stop_atr_mult: runtime/schema supported, fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07
risk.min_rr: runtime/schema supported, fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07
backtest.holding_days: metrics consumed, published-price runtime currently forces HOLD=5
backtest.target_pct|backtest.stop_pct: metrics consumed when present, not present in param-grid schema or curated rows
```

C11 confirms that the next step is an explicit exit-model or strategy-family redesign contract, not a new catalog, not OOS, and not mutation of C07.

C11 result is recorded in:

```text
docs/watchlist/audit/WS_C11_EXIT_MODEL_CONTRACT_AUDIT_FINAL_RESULT.md
docs/watchlist/audit/WS_C11_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c11-exit-model-contract-audit.json
```

### C12 exit-model redesign contract addendum

C12 did not create a strategy catalog. It converts the C11 blocker list into an explicit redesign contract and keeps catalog creation unauthorized for this session.

```text
C12 strategy_catalog_created=false
C12 design_contract_ready=true
C12 catalog_creation_authorized=false
C12 exit_model_catalog_authorized=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C12 OOS=NOT_RUN
C12 production_ready=0
```

The C12 command consumes C11 contract evidence and writes a redesign-contract artifact:

```text
php artisan watchlist:backtest-exit-model-redesign-contract --c11-artifact=storage/app/watchlist/backtest/c11-exit-model-contract-audit.json --output=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --overwrite
```

Executed C12 result:

```text
status=PASS
reason_code=WS_BT_C12_EXIT_MODEL_REDESIGN_CONTRACT_READY
source_c11_artifact_hash=4b8a6a383c1ad9f5cab78394b3851b4b3a3325ea
design_contract_ready=1
catalog_creation_authorized=0
exit_model_catalog_authorized=0
next_required_step=IMPLEMENT_CONTRACTED_EXIT_AXIS_SUPPORT_BEFORE_CATALOG
strategy_catalog_created=0
oos_executed=0
artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
production_ready=0
```

C12 allowed first-phase future implementation axes:

```text
risk.min_rr
risk.stop_atr_mult
```

These axes are allowed only for future implementation work because they are already represented in official schema/factory/runtime metrics. They remain fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07.

C12 blocked first-phase axes:

```text
backtest.holding_days
backtest.target_pct|backtest.stop_pct
```

C12 required implementation sequence before any future catalog:

```text
create_new_catalog_identity_only_after_contract_support_exists
keep_c01_c07_fixed_execution_snapshot_guards
add_factory_and_calibration_definitions_for_the_new_family_only
add static/unit guards for no_oos_no_best_of_failed_no_gate_relaxation
seed_new_catalog_idempotently
run_is_calibration_twice_only
allow_oos_only_if_is_valid_param_count_ge_1_and_best_binding_hash_is_non_empty
```

C12 confirms that the next step is implementation of contracted exit-axis support for a future new-family catalog path, not a catalog, not OOS, and not mutation of C07.

C12 result is recorded in:

```text
docs/watchlist/audit/WS_C12_EXIT_MODEL_REDESIGN_CONTRACT_FINAL_RESULT.md
docs/watchlist/audit/WS_C12_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c12-exit-model-redesign-contract.json
```

### C13 exit-axis support addendum

C13 did not create a strategy catalog. It implements the C12 support boundary for a future new-family catalog path while keeping the historical fixed-execution catalogs immutable.

```text
C13 strategy_catalog_created=false
C13 support_ready=true
C13 catalog_creation_authorized=false
C13 future_catalog_definition_work_authorized=true
C13 exit_model_catalog_authorized=false
C07 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06
C07 catalog_version=C07
C07 catalog_count=12
C07 catalog_hash=233b45b06cbf34da221d5d7de2d9725fdf4d3441
C13 OOS=NOT_RUN
C13 production_ready=0
```

The C13 command consumes C12 redesign-contract evidence and writes a support-audit artifact:

```text
php artisan watchlist:backtest-exit-axis-support-audit --c12-artifact=storage/app/watchlist/backtest/c12-exit-model-redesign-contract.json --output=storage/app/watchlist/backtest/c13-exit-axis-support-audit.json --overwrite
```

Executed C13 result:

```text
status=PASS
reason_code=WS_BT_C13_EXIT_AXIS_SUPPORT_READY
source_c12_artifact_hash=04d4e2f230685962fadd1bc26c294cbaed10f38b
support_ready=1
fixed_guard_rejects_drift=1
variable_policy_accepts_risk_axes=1
variable_policy_blocks_holding_days=1
variable_policy_blocks_target_stop_pct=1
catalog_creation_authorized=0
future_catalog_definition_work_authorized=1
exit_model_catalog_authorized=0
next_required_step=CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
strategy_catalog_created=0
oos_executed=0
artifact_hash=73ba035edfa22f19b4b3525ee3f522241fbae291
production_ready=0
```

C13 implements support for these future first-phase variable risk-exit axes:

```text
risk.stop_atr_mult
risk.min_rr
```

These axes remain fixed for R1/R2/C01/C02/C03/C04/C05/C06/C07. C13 preserves the fixed execution/grouping drift guard for existing catalogs.

C13 blocks these first-phase axes:

```text
backtest.holding_days
backtest.target_pct
backtest.stop_pct
```

C13 next step:

```text
CREATE_NEW_EXIT_AXIS_CATALOG_DEFINITION_AND_SEED_IS_ONLY
```

The next catalog session must still create a new catalog identity, seed idempotently, run IS calibration twice only, and keep OOS blocked unless a valid IS binding exists.

C13 result is recorded in:

```text
docs/watchlist/audit/WS_C13_EXIT_AXIS_SUPPORT_FINAL_RESULT.md
docs/watchlist/audit/WS_C13_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c13-exit-axis-support-audit.json
```
