# Weekly Swing Backtest Persistence and Universe Schema Contract

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Current Market Data Semantic Override

This technical document may retain legacy physical/parameter tokens for backward compatibility. Current Watchlist interpretation of producer fields is governed by `docs/watchlist/development/implementation/MARKET_DATA_INTAKE_IMPLEMENTATION_CONTRACT.md`, which delegates semantic ownership to the Market Data producer contracts:

- legacy `dv20_idr` / `*_dv20_idr` tokens apply only to Market Data canonical `adv20_close_volume_proxy_idr` (`RAW close × RAW volume` 20-session proxy); they MUST NOT be interpreted as `adv20_traded_value_idr_actual`;
- legacy serialized `vol_ratio` / `*_vol_ratio` tokens apply to canonical `vol_ratio_20` only when the selected Market Data read-model version declares exact semantic equivalence;
- direct Market Data table names, if preserved below as implementation history/debug context, are not current runtime intake authority;
- a future change from proxy liquidity to actual traded value, or to a different participation formula, is a strategy/proof identity change rather than a transparent field substitution.

Where wording below conflicts with this override or the canonical Weekly Swing strategy, this override + the canonical strategy wins until the implementation document is physically migrated.

> **Doc Role:** IMPLEMENTATION CONTRACT
> Extracted unchanged from the former mixed backtest strategy document.

## DDL
Schema backtest (DDL) disimpan sebagai artefak di: [`db/BACKTEST_SCHEMA_DDL.sql`](../db/BACKTEST_SCHEMA_DDL.sql).

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
- `reason_code` mengikuti prioritas canonical reason pada [`WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](../tests/WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md) (contoh: `WS_DATA_MISSING`).

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
Untuk audit/re-run **wajib** menyimpan universe harian di `watchlist_bt_universe_ws` (lihat [`db/BACKTEST_SCHEMA_DDL.sql`](../db/BACKTEST_SCHEMA_DDL.sql)) minimal berisi:
- `required_ok, missing_fields, guard_ok, eligible_ok, dv20_idr, atr14_pct, vol_ratio, reason_code`

Reason_code memakai dictionary WS_* yang **resmi di-seed** (contoh: `WS_DATA_MISSING`, `WS_LIQ_FAIL`) sesuai prioritas canonical reason di [`WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`](../tests/WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md). Alias nama lain tidak boleh dipakai.

## Schema: watchlist_bt_universe_ws (AUDIT) (LOCKED)

Tabel ini **wajib** ada untuk membuat backtest reproducible dan audit-friendly.

Lokasi DDL: [`db/BACKTEST_SCHEMA_DDL.sql`](../db/BACKTEST_SCHEMA_DDL.sql)

Kolom (harus match DDL):
- `asof_eod_date` (DATE, NOT NULL) — tanggal EOD universe
- `ticker_id` (INT, NOT NULL) — id ticker
- `required_ok` (TINYINT(1), NOT NULL) — data-quality only (bukan eligibility final)
- `reason_code` (VARCHAR(32), NULL) — reason WS_* (contoh: `WS_DATA_MISSING`) saat `required_ok=0`
- `missing_fields` — daftar field required yang missing/invalid (CSV string)
- `guard_ok` — 1 jika lolos semua guardrail, 0 jika tidak
- `eligible_ok` — 1 jika required_ok=1 dan guard_ok=1
- `dv20_idr`, `atr14_pct`, `vol_ratio` — metric snapshot untuk debug equivalence
- `vol_ratio` disimpan sebagai `DECIMAL(20,6)` agar snapshot audit tidak overflow pada rasio historis ekstrem ketika denominator volume sangat kecil; nilai tidak boleh di-clamp hanya agar muat ke schema.

Primary key:
- `(asof_eod_date, ticker_id)`

Indexes:
- `idx_bt_univ_ws_req (asof_eod_date, required_ok)`
- `idx_bt_univ_ws_reason (asof_eod_date, reason_code)`
- `idx_bt_univ_ws_elig (asof_eod_date, eligible_ok)`
