# 16 — WS Evaluation Metrics Sufficiency (LOCKED)

> **Doc Role:** CANONICAL WEEKLY SWING STRATEGY
> **Change rule:** Stable by default; revision requires material finding + evidence + decision per `../../../governance/DOCUMENT_CHANGE_POLICY.md`.

## Purpose (LOCKED)

Dokumen ini mengunci **minimum evaluation metrics, acceptance floors, dan ranking policy** untuk menentukan apakah hasil kalibrasi Weekly Swing cukup robust untuk diteruskan ke OOS evaluation.

Dokumen ini bukan owner physical schema, table/column storage, query, reason code, artifact serialization, atau promotion procedure.

Kalibrasi dianggap **tidak valid** jika minimum evidence/metrics pada dokumen ini tidak tersedia atau acceptance floor gagal.

## Evaluation Model Binding (LOCKED)

Semantik entry, exit, holding horizon, executable bar, fee, slippage, dan return mengikuti `../12_WS_BACKTEST_AND_CALIBRATION_STRATEGY.md`.

Canonical baseline menggunakan:
- PLAN dari EOD;
- entry next trading day open;
- rule-based stop/target/time exit;
- maximum holding horizon 5 trading day;
- explicit fee dan slippage model;
- hanya executable trades yang masuk return distribution.

Evaluation model lain tidak boleh dicampur dengan baseline tanpa identity/version yang berbeda.

## Minimum Metric Set (LOCKED)

Untuk setiap candidate paramset pada evaluation window, evidence harus cukup untuk menghasilkan minimum metric berikut.

### A. Coverage and activity
- `picks_count`: jumlah trade yang benar-benar dapat dievaluasi dengan entry dan exit executable;
- `days_covered`: jumlah distinct replay trading date yang benar-benar tercakup oleh valid evaluation;
- `avg_ret_net_top`;
- `win_rate_top`.

Coverage semantics:
- denominator adalah seluruh ordered trading date pada explicit evaluation window;
- `days_covered` bukan alias jumlah tanggal yang diminta;
- satu tanggal dengan beberapa trade tetap dihitung satu kali;
- tanggal yang seluruh candidate-nya gagal memperoleh valid evaluation tidak boleh otomatis dihitung covered;
- valid no-recommendation/no-trade date dapat dihitung covered bila strategy evaluation memang selesai secara sah untuk tanggal tersebut.

### B. Risk and return distribution
- `median_ret_net_top`;
- `p25_ret_net_top`;
- `p75_ret_net_top`;
- `min_ret_net_top`;
- `max_ret_net_top`.

`min_ret_net_top` adalah worst single-trade net return dan bukan pengganti max drawdown. Derived metric seperti loss rate tidak perlu menjadi minimum metric tersendiri bila dapat dihitung deterministically dari metric canonical lain.

### C. Period stability
- `month_win_rate_min`;
- `month_avg_ret_net_min`.

Evidence juga harus cukup untuk mengetahui jumlah periode yang dievaluasi dan jumlah periode yang gagal, sehingga candidate tidak dapat menang hanya karena satu periode ekstrem.

## Gating Rules for Calibration Validity (LOCKED)

Acceptance floor berikut harus lulus **semuanya** sebelum candidate dapat masuk ranking best-IS.

### 1. Minimum trade count
- `picks_count >= 120` untuk default 2-year baseline; threshold boleh disesuaikan tetapi harus terdokumentasi secara eksplisit pada evaluation/paramset yang sah.

### 2. Minimum coverage
- `days_covered >= ceil(0.70 * total_trading_days_in_window)`.

Nilai placeholder/sentinel implementation tidak boleh dipakai sebagai effective gate. Evidence harus menggunakan nilai threshold yang sudah di-resolve.

### 3. Robust return
- `avg_ret_net_top > 0`;
- `median_ret_net_top > 0`, atau minimal tidak negatif bila strategy/evaluation mode defensif secara eksplisit menetapkannya.

### 4. Downside bound
- `p25_ret_net_top >= -0.03`;
- bila percentile belum tersedia pada evaluation model yang sah, fallback yang terdokumentasi adalah `min_ret_net_top >= -0.07`.

### 5. Stability across periods
- `month_win_rate_min >= 0.45`;
- `month_avg_ret_net_min >= -0.01`.

Jika satu gate gagal, candidate tidak boleh dipilih sebagai best calibration candidate untuk activation path.

## Ranking Policy after Gates Pass (LOCKED)

Best candidate tidak boleh dipilih hanya dari average return. Setelah seluruh acceptance floor lulus, ranking canonical adalah:

1. lebih tinggi `avg_ret_net_top`;
2. lebih tinggi `median_ret_net_top`;
3. lebih tinggi `month_win_rate_min`;
4. lebih tinggi `p25_ret_net_top` (downside lebih baik);
5. final deterministic tie-break menggunakan stable candidate identity yang paling kecil/awal sesuai canonical grid ordering.

Urutan database atau runtime yang tidak dijamin deterministic tidak boleh menjadi implicit tie-breaker.

## Evidence Sufficiency Rule (LOCKED)

Evidence wajib cukup untuk:
- merecompute seluruh minimum metrics;
- memverifikasi coverage;
- membedakan evaluated trade dari skipped/non-executable trade;
- mengikat hasil ke evaluation model dan strategy version;
- menjalankan ranking yang sama pada replay.

Detail physical persistence dan serialization berada di implementation layer.

## Next

- `17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`
