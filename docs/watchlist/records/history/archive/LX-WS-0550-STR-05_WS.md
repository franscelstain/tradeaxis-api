# Legacy Role Extract — WS — STRATEGY

> **Document Type:** STRATEGY
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0550-STR-05`
> **Legacy Source ID:** `LS-WS-0550`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
> **Original SHA1:** `F36B415CA47D448CF0C5EA5AEDE987D497FFEF42`
> **Source Sections:** L5-L11 Purpose; L12-L27 Scope lock (yang dikerjakan); L28-L36 Proof of BT coverage (LOCKED); L37-L46 Universe equivalence (LOCKED); L47-L50 Prerequisites; L51-L54 Inputs; L55-L232 Backtest execution assumptions (LOCKED); L233-L301 Process; L302-L307 Evaluation metrics sufficiency (LOCKED); L308-L314 Walk-forward / OOS proof (LOCKED); L315-L320 Outputs; L321-L323 Failure modes; L327-L371 Universe rule (DET) (LOCKED); L460-L463 Next; L464-L540 OOS Runtime Gap Closure — Deterministic Grid, Evaluation Identity, and Bounded Reads (LOCKED); L541-L566 Canonical grid cross-field projection rule (LOCKED)
> **Extract Body SHA1:** `36DDBD8B07E794673DBEF9DB89D2CD8529492DE2`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

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
policy_code + catalog_code + catalog_version + param_id + eval_model
+ eval_model_hash + implementation_version + implementation_hash
+ evidence_pipeline_version + evidence_pipeline_hash
+ paramset_hash + from_date + to_date
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
