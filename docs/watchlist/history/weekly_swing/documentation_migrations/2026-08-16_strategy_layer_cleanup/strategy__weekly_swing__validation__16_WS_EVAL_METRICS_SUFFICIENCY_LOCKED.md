# 16 - WS Evaluation Metrics Sufficiency (LOCKED)

> **Doc Role:** CANONICAL WEEKLY SWING STRATEGY
> **Change rule:** Stable by default; revision requires material finding + evidence + decision per `../../../governance/DOCUMENT_CHANGE_POLICY.md`.


## Purpose (LOCKED)

Dokumen ini mengunci metrik minimum dan gating evaluasi WS. Dokumen ini tidak menjadi owner schema backtest maupun prosedur promote.

Menetapkan bahwa metrik di `watchlist_bt_eval` cukup untuk:
- memilih param_id terbaik secara robust (bukan kebetulan/overfit),
- menghindari hasil bagus palsu karena trade_count kecil,
- memastikan hasil bisa dipakai untuk aktivasi paramset (candidate ACTIVE).

Kalibrasi WS dianggap TIDAK VALID jika metrik minimum pada dokumen ini tidak tersedia / tidak memenuhi gating rules.

## Prerequisites
### Weekly Swing
15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md

---

## Return definition (LOCKED)

Model entry/exit yang dipilih harus terdokumentasi konsisten pada metadata evaluasi dan tidak boleh berubah antar run tanpa perubahan evaluasi model yang eksplisit.


### Trade timing model
- PLAN dibuat dari data EOD (asof_eod_date).
- Entry untuk evaluasi backtest WS harus mengikuti satu model yang dipilih dan konsisten:
  - Model A: entry = next day open (lebih realistis untuk eksekusi besok)
  - Model B: entry = next day VWAP (opsional jika data ada)
  - Model C: entry = next day close (tidak disarankan, rawan look-ahead)

Dokumen ini mengunci default:
**entry = next day open.**

### Exit / holding horizon

Owner canonical execution assumptions tetap `12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`.
Runtime Weekly Swing aktif menggunakan **rule-based exit** yang deterministic:

- target-profit atau stop-loss dapat mengakhiri trade lebih awal;
- jika target dan stop tersentuh pada hari yang sama, stop diprioritaskan;
- bila tidak ada target/stop yang tersentuh, trade ditutup melalui time-exit pada akhir horizon;
- `holding_days = 5` adalah **batas maksimum lima trading day**, bukan kewajiban fixed holding sampai hari kelima.

Model fixed-holding bukan default runtime aktif dan hanya boleh digunakan oleh evaluation model lain yang diberi identifier/version eksplisit agar tidak tercampur dengan artifact rule-based Weekly Swing.

### Net return
Return yang dipakai untuk evaluasi kalibrasi adalah net:
- `ret_gross` = (exit_price - entry_price) / entry_price
- `ret_net` = ret_gross - fee_bps/10000 - slippage_bps/10000

Fee & slippage wajib eksplisit dan konsisten.

### Executable-bar eligibility (LOCKED)

- Trade hanya masuk `picks_count` dan agregasi return bila entry serta exit memenuhi tradable-bar rule file 12.
- Bar published dengan `volume <= 0` atau volume tidak tersedia tidak boleh digunakan sebagai fill entry/exit, tidak boleh memicu TP/SL, dan tidak boleh menghasilkan return nol sintetis.
- Trade yang di-skip karena `BT_SKIP_NO_TRADABLE_ENTRY`, `BT_SKIP_NO_TRADABLE_EXIT`, `BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY`, atau `BT_SKIP_NON_EXECUTABLE_PRICE_EXIT` tetap masuk diagnostic counters, tetapi tidak masuk canonical return distribution.

---

## Minimum metrics required in watchlist_bt_eval (LOCKED)

Kolom wajib di sini adalah sufficiency contract; detail storage fisik tabel backtest tetap mengikuti file 12.


Untuk tiap `(policy_code, param_id)` pada rentang backtest, `watchlist_bt_eval` WAJIB punya:

### A) Coverage metrics (wajib)
- `picks_count` : total trade/picks yang dievaluasi
- `days_covered` : jumlah distinct replay trade date yang benar-benar tercakup oleh evaluasi runtime. Satu tanggal dihitung covered bila minimal satu trade pada tanggal tersebut menghasilkan `metrics_ready = true`, atau tanggal tersebut secara eksplisit menghasilkan `WATCHLIST_BACKTEST_EMPTY_RECOMMENDATION_VALID`. Trade date yang seluruh kandidatnya di-skip karena calendar/price/non-tradable bar tidak dihitung covered.

Definisi pembanding coverage (LOCKED):
- `total_trading_days_in_window` adalah seluruh ordered trading date pada explicit replay window;
- `days_covered` bukan alias untuk jumlah tanggal yang diminta;
- `days_covered` tidak boleh dihitung langsung dari `count(replay_window.trade_dates)`, karena hal itu membuat coverage gate selalu lulus;
- satu tanggal dengan beberapa trade tetap dihitung satu kali.
- `avg_ret_net_top` : rata-rata ret_net pada picks (top bucket)
- `win_rate_top` : % trade dengan ret_net > 0

### B) Risk & distribution metrics (wajib)
- `median_ret_net_top`
- `p25_ret_net_top`
- `p75_ret_net_top`
- `min_ret_net_top`
- `max_ret_net_top`

Definisi (LOCKED):
- `min_ret_net_top` adalah worst single-trade `ret_net` pada TOP bucket (proxy downside bound sederhana; bukan max drawdown).
- Istilah `max_drawdown_proxy_top` tidak digunakan sebagai kolom wajib dan tidak disimpan.

- `loss_rate_top` dihitung saat query/report sebagai `1 - win_rate_top` dan tidak disimpan sebagai kolom.

Rule (LOCKED):
Jika sebuah metrik adalah turunan langsung dari kolom yang sudah ada (derived),
maka metrik tersebut tidak boleh dicantumkan sebagai “kolom wajib”.
Metrik turunan harus ditulis sebagai “computed at query/report time”.

### C) Stability metrics (wajib)
- `month_win_rate_min` : win_rate minimum per bulan (atau per periode)
- `month_avg_ret_net_min` : avg return minimum per bulan

Tujuan: param tidak boleh menang hanya karena 1 bulan “meledak”.

Jika belum ada agregasi bulanan:
- minimal simpan `periods_count` dan `period_fail_count` untuk periode bulanan.

---

## Gating rules for calibration validity (LOCKED)

Threshold minimum ini adalah acceptance floor dan bukan ranking policy penuh; pemilihan kandidat terbaik tetap dapat memakai urutan ranking yang lebih kaya selama tidak melanggar floor ini.


Kalibrasi param_id dianggap valid hanya jika:

1) Minimum trade count
- picks_count >= `ws.eval.min_trades` (default 120 untuk 2 tahun; boleh disesuaikan tapi harus terdokumentasi)
- canonical bootstrap runtime memakai `min_trades = 120`; nilai nol tidak boleh dipakai untuk mengklaim calibration validity.

2) Minimum coverage
- days_covered >= `ws.eval.min_days_covered`

Definisi (LOCKED):
`ws.eval.min_days_covered = ceil(0.70 * total_trading_days_in_window)`

Paramset bootstrap boleh menyimpan `eval.min_days_covered.value = 0` hanya sebagai sentinel dinamis. Nilai efektif yang ditulis pada metric sufficiency artifact wajib sudah di-resolve menjadi formula 70% di atas; nilai `0` tidak boleh dipakai langsung sebagai coverage gate.

Tujuan: mencegah param yang cuma aktif di sebagian kecil data.

3) Robust return
- avg_ret_net_top > 0
- median_ret_net_top > 0 (atau minimal tidak negatif jika strategi defensif)

4) Downside bound
- p25_ret_net_top >= -X (X adalah toleransi downside; default 0.03)
  atau jika belum ada percentile: min_ret_net_top >= -Y (default 0.07)

5) Stability across periods
- month_win_rate_min >= 0.45
- month_avg_ret_net_min >= -0.01

Jika salah satu gagal → param_id tidak boleh dipilih menjadi BEST/ACTIVE.

---

## Ranking policy (LOCKED)
Ranking policy pada section ini hanya berlaku setelah semua floor sufficiency di atas lulus. Dokumen ini tidak mengubah prosedur promote ACTIVE ataupun artifact allowlist.


Pemilihan param_id terbaik tidak boleh hanya berdasarkan `avg_ret_net_top`.

Urutan ranking default:
1) Lolos semua gating rules
2) Maksimalkan `avg_ret_net_top`
3) Tie-breaker: lebih tinggi `median_ret_net_top`
4) Tie-breaker: lebih tinggi `month_win_rate_min`
5) Tie-breaker: lebih kecil downside (`p25_ret_net_top` lebih tinggi)
6) Final deterministic tie-break: `param_id` paling kecil.

Urutan database yang tidak dijamin tidak boleh dipakai sebagai tie-break implisit.

---

## Required artifacts (LOCKED)

Agar metrik cukup dan repeatable, wajib ada:
- definisi entry/exit/fee/slippage/horizon (di dokumen ini)
- `watchlist_bt_eval` menyimpan metrik minimum
- query/skrip yang menjelaskan cara menghitung metrik (boleh di lampiran dokumen calibration)

## Next
### Weekly Swing
- 17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md