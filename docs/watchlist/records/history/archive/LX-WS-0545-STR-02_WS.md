# Legacy Role Extract — WS — STRATEGY

> **Document Type:** STRATEGY
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0545-STR-02`
> **Legacy Source ID:** `LS-WS-0545`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
> **Original SHA1:** `BD70FD8283A295D8C51C47A316B3A5DB404C39EB`
> **Source Sections:** L3-L6 Purpose; L7-L14 Scope; L15-L97 A. Active Reason-Code Contract; L98-L103 Recommendation Reason-Code Usage Rules; L104-L173 Recommendation Hash Boundary; L174-L203 B. Hash Contract; L204-L207 C. Verification Vectors; L208-L211 D. Relationship to Seed SQL; L212-L215 Final Rule
> **Extract Body SHA1:** `6763BD59D806C7656AF69A9E02BD6B492B178F33`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Purpose

Dokumen ini adalah owner normatif untuk reason-code contract dan hash contract Weekly Swing.

## Scope

Dokumen ini mengunci:

- daftar reason code aktif yang disediakan seed resmi Weekly Swing,
- semantics hash contract yang dipakai untuk reproducibility PLAN,
- dan relationship ke seed SQL serta verification vectors.

## A. Active Reason-Code Contract

Daftar reason code aktif saat ini harus tetap parity dengan `db/REASON_CODES_SEED.sql`.

### PLAN / data readiness / guards / grouping

- `WS_DATA_MISSING`
- `WS_HIST_SHORT`
- `WS_MISSING_BARS_60D`
- `WS_OUTLIER_RET1D`
- `WS_OUTLIER_RANGE1D`
- `WS_MOM_SOFT_MIN`
- `WS_DATA_OUTLIER`
- `WS_EOD_ABORT`
- `WS_LIQ_FAIL`
- `WS_LIQ_HIGH`
- `WS_ATR_LOW`
- `WS_ATR_HIGH`
- `WS_VOLR_FAIL`
- `WS_VOLR_HIGH`
- `WS_TICK_RISK_HIGH`
- `WS_MOM_STRONG`
- `WS_MOM_WEAK`
- `WS_BO_NEAR`
- `WS_BO_BREAK`
- `WS_BO_FAR`
- `WS_BO_EXT`
- `WS_LIQ_STRONG`
- `WS_LIQ_BORDER`
- `WS_RISK_IDEAL`
- `WS_RISK_HIGH`
- `WS_RISK_LOW`
- `WS_FW_EXT`
- `WS_FW_RR_LOW`
- `WS_FW_RR_INV`
- `WS_GRP_TOP`
- `WS_GRP_SEC`
- `WS_GRP_WATCH`
- `WS_GRP_AVOID`
- `WS_SEL_PCT`
- `WS_HID_PCT`
- `WS_HID_CAP`
- `WS_SHOW`
- `WS_HIDE`
- `WS_RUN_CAPPED`
- `WS_NO_TRADE_MIN_ELIGIBLE`
- `WS_NO_TRADE_ALL_FILTERED`
- `WS_PLAN_HASH_MISMATCH`
- `WS_PLAN_WRITEBACK_DETECTED`


### C171 decision-time upper-bound reason codes

- `WS_LIQ_HIGH` means `dv20_idr` exceeds the explicit immutable `liquidity.max_dv20_idr` bound.
- `WS_VOLR_HIGH` means `vol_ratio` exceeds the explicit immutable `volume.max_vol_ratio` bound.
- Both codes are PLAN universe guard diagnostics evaluated from data available on `asof_eod_date`.
- Neither code may be inferred from return outcomes, OOS evidence, ticker identity, sector identity, or a later trade path.
- Legacy paramsets that omit the optional upper-bound fields do not emit these codes.
- `WS_TICK_RISK_HIGH` means the signal-date close/ATR stop, after conservative IDX price-fraction normalization, expands risk above `risk.max_signal_tick_risk_expansion_pct`.
- The tick-risk guard uses only signal-date `close`, `atr14_pct`, and immutable `stop_atr_mult`; next-open entry, realized return, and future path are forbidden.

### PLAN grouping foundation reason codes

Reason code berikut adalah reason code PLAN grouping foundation. Kode ini hanya menjelaskan membership group PLAN dan diagnostics AVOID. Kode ini bukan final recommendation, bukan confirm, bukan sell instruction, dan bukan execution instruction.

- `WS_PLAN_TOP_PICK`
- `WS_PLAN_SECONDARY`
- `WS_PLAN_WATCH_ONLY`
- `WS_PLAN_AVOID_LOW_SCORE`
- `WS_PLAN_AVOID_EXCLUDED`


### RECOMMENDATION

- `WS_REC_SELECTED`
- `WS_REC_NOT_SELECTED`
- `WS_REC_BORDERLINE`
- `WS_REC_EMPTY_SET`
- `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`
- `WS_REC_CAPITAL_AWARE`
- `WS_REC_CAPITAL_INSUFFICIENT`
- `WS_REC_MIN_LOT_NOT_AFFORDABLE`

## Recommendation Reason-Code Usage Rules

Reason code recommendation hanya boleh dibentuk dari PLAN immutable dan optional capital input yang sah.

Reason code confirm tidak boleh dipakai untuk membentuk recommendation.

## Recommendation Hash Boundary

Jika recommendation hash diaktifkan, canonical payload recommendation tidak boleh memasukkan field hasil confirm.

Recommendation hash harus stabil untuk PLAN, policy, dan capital input yang sama.

### CONFIRM

- `WS_SNAPSHOT_MISSING`
- `WS_STALE`
- `WS_INPUT_INCOMPLETE`
- `WS_NO_PRICE`
- `WS_CONFIRM_OK`
- `WS_CONFIRM_NEUTRAL`
- `WS_CONFIRM_VOL_WEAK`
- `WS_DRIFT_FAR`
- `WS_OUT_BAND`



### CONFIRM overlay foundation reason codes

Reason code berikut adalah reason code foundation untuk overlay CONFIRM. Kode ini hanya menjelaskan eligibility dan hasil overlay CONFIRM terhadap candidate PLAN immutable. Kode ini bukan reason code recommendation final, bukan portfolio allocation, bukan execution instruction, dan bukan backtest metric.

- `WS_CONFIRM_ELIGIBLE_RECOMMENDED`
- `WS_CONFIRM_ELIGIBLE_NON_RECOMMENDED`
- `WS_CONFIRM_APPLIED`
- `WS_CONFIRM_NOT_APPLIED`
- `WS_CONFIRM_REJECTED_UNKNOWN_CANDIDATE`
- `WS_CONFIRM_REJECTED_NOT_PLAN_CANDIDATE`
- `WS_CONFIRM_NO_DATA`

### Backtest / coverage / eval / OOS / artifacts

- `WS_BT_COV_CUTOFFS_MISSING`
- `WS_BT_COV_GRID_MISSING`
- `WS_BT_COV_MATRIX_MISSING`
- `WS_BT_COV_PICK_VIOLATION`
- `WS_BT_EVAL_DOWNSIDE_FAIL`
- `WS_BT_EVAL_METRICS_MISSING`
- `WS_BT_EVAL_MIN_DAYS_FAIL`
- `WS_BT_EVAL_MIN_TRADES_FAIL`
- `WS_BT_EVAL_ROBUST_RETURN_FAIL`
- `WS_BT_EVAL_STABILITY_FAIL`
- `WS_BT_OOS_DRAWDOWN_FAIL`
- `WS_BT_OOS_METRICS_FAIL`
- `WS_BT_OOS_PROOF_MISSING`
- `WS_BT_OOS_STABILITY_FAIL`
- `WS_BT_OOS_WINDOW_INSUFFICIENT`
- `WS_BT_ARTIFACT_MISSING`
- `WS_BT_ARTIFACT_NOT_ALLOWED`
- `WS_BT_ARTIFACT_PARAM_ID_MISMATCH`
- `BT_SKIP_NO_TRADABLE_ENTRY`
- `BT_SKIP_NO_TRADABLE_EXIT`
- `BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY`
- `BT_SKIP_NON_EXECUTABLE_PRICE_EXIT`
- `BT_GAP_THROUGH_STOP_AT_OPEN`
- `BT_GAP_THROUGH_TARGET_AT_OPEN`

Semantics tambahan untuk tradable-bar backtest:

- `BT_SKIP_NO_TRADABLE_ENTRY` berarti exact-date published bar tersedia, tetapi volume tidak positif sehingga tidak ada fill entry executable.
- `BT_SKIP_NO_TRADABLE_EXIT` berarti exact-date published bar tersedia untuk exit/time-exit, tetapi volume tidak positif sehingga tidak ada fill exit executable.
- Kedua code adalah backtest evaluation diagnostics. Kode ini bukan reason code PLAN, RECOMMENDATION, CONFIRM, portfolio, atau broker execution.
- Kedua kondisi wajib menghasilkan `ret_net = NULL`; zero return sintetis dilarang.
- `BT_SKIP_NON_EXECUTABLE_PRICE_ENTRY` dan `BT_SKIP_NON_EXECUTABLE_PRICE_EXIT` berarti raw OHLC tidak berada pada fraksi harga executable; keduanya wajib menghasilkan `ret_net = NULL`.
- `BT_GAP_THROUGH_STOP_AT_OPEN` dan `BT_GAP_THROUGH_TARGET_AT_OPEN` adalah informational evaluation codes yang membuktikan opening-gap fill memakai harga open, bukan theoretical trigger level.

Tidak boleh ada reason code aktif di seed atau runtime output yang tidak dapat ditelusuri ke daftar aktif di dokumen ini.

## B. Hash Contract

Hash contract Weekly Swing yang aktif saat ini mengunci nilai berikut:

- `version = 1`
- `order_by = ticker_id_asc`
- `null_handling = EXCLUDE_FROM_HASH_PAYLOAD`

### Required scale map

`hash_contract.scales.value` wajib memiliki:

- `close_price_dp = 4`
- `hh20_dp = 4`
- `roc20_dp = 6`
- `atr14_pct_dp = 4`
- `dv20_idr_dp = 0`

### Canonical Payload Rule

Canonical payload rule yang diverifikasi fixtures saat ini adalah:

- compact JSON array UTF-8,
- rows ordered by `ticker_id ASC`,
- object keys fixed sebagai `ticker_id, close, hh20, roc20, atr14_pct, dv20_idr`,
- tidak ada extra whitespace,
- numeric strings fixed ke declared decimal places,
- negative zero dinormalisasi menjadi zero,
- null handling mengikuti `EXCLUDE_FROM_HASH_PAYLOAD`.

## C. Verification Vectors

Fixture `fixtures/hash_contract_vectors.json` adalah verification artifact untuk kontrak hash ini. Fixture tersebut tidak menjadi owner rule, tetapi wajib tetap parity dengan kontrak yang ditetapkan di dokumen ini.

## D. Relationship to Seed SQL

`db/REASON_CODES_SEED.sql` adalah implementation seed untuk reason code yang aktif di Weekly Swing. Jika seed dan dokumen ini berbeda, dokumen ini tetap authoritative dan seed harus diperbarui.

## Final Rule

Perubahan pada reason code aktif atau hash semantics tidak boleh hidup hanya di seed SQL, vectors, atau runtime output. Perubahan tersebut harus terlebih dahulu atau secara bersamaan ditetapkan secara normatif di dokumen ini.
