# 03 — WS Runtime Artifact Flow

## Purpose

Dokumen ini menjelaskan aliran artifact runtime yang harus dihasilkan aplikasi watchlist.

## Canonical Flow

### Step 1 — Build PLAN
Input upstream producer-facing yang sah dari `market-data` dibaca dan divalidasi. Intake ini harus publication-aware dan tunduk pada kontrak consumer-readable/downstream-readable milik producer.
Anchor minimum untuk Step 1 adalah:
- `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
Output `PLAN` dimaterialize sebagai artifact immutable untuk `trade_date`.

### Step 2 — Build RECOMMENDATION
Artifact `PLAN` dibaca ulang.
Output `RECOMMENDATION` dibentuk tanpa membaca `CONFIRM`.

### Step 3 — Build CONFIRM
Permintaan confirm mengikat ticker ke candidate `PLAN` yang sah.
Output `CONFIRM` dibentuk tanpa memutasi `PLAN` atau `RECOMMENDATION`.

### Step 4 — Build Composite View
Consumer view dapat menggabungkan:
- `PLAN`
- `RECOMMENDATION`
- `CONFIRM`

Composite view tidak boleh mengubah semantics artifact asal.

## Runtime Keys

Kunci minimum artifact-level yang harus konsisten pada artifact runtime yang sah:
- `strategy_code`
- `trade_date`
- `policy_code`
- `param_set_id`
- `policy_version`
- `schema_version`

Kunci minimum item-level yang harus konsisten bila artifact membawa scope ticker tunggal atau item candidate yang spesifik:
- `ticker`

Aturan interpretasi:
- `PLAN` dan `RECOMMENDATION` wajib membawa runtime keys artifact-level pada header artifact/read model.
- `CONFIRM` wajib membawa runtime keys artifact-level **dan** `ticker` karena artifact ini memang ticker-scoped.
- Untuk `PLAN`, `ticker` boleh hidup pada item candidate, bukan wajib sebagai header artifact.
- Untuk `RECOMMENDATION`, `ticker` boleh hidup pada `selected_items`, bukan wajib sebagai header artifact.

## Allowed States

- `PLAN only`
- `PLAN + RECOMMENDATION`
- `PLAN + CONFIRM (candidate still PLAN-rooted)`
- `PLAN + RECOMMENDATION + CONFIRM`

## Invalid States

- `RECOMMENDATION without PLAN`
- `CONFIRM without PLAN candidate`


## Terminology Guard

- `param_set_id` = identifier instance paramset aktif yang benar-benar dipakai artifact runtime.
- `policy_version` = versi policy Weekly Swing yang mengatur behavior artifact.
- `schema_version` = versi kontrak schema paramset.
- Istilah `paramset_version` tidak boleh dipakai lagi sebagai shorthand karena ambigu; ia dulu bisa dibaca sebagai versi instance paramset, versi policy, atau versi schema.


## Minimum Implementation Outputs

Implementasi yang sah minimal menghasilkan:
- `PLAN` artifact
- `RECOMMENDATION` artifact
- `CONFIRM` artifact
- source references antar artifact yang relevan
- reason-code / hash integrity yang relevan
- bukti test untuk core rules

## Traceability Pointer

Traceability detail implementasi dibaca bersama `02_WS_MODULE_MAPPING.md`, terutama untuk pemetaan:
- owner policy doc -> module area
- runtime artifact -> serializer / publisher / repository
- contract acceptance -> implementation test suite


## Invalid intake shortcuts
Step 1 tidak boleh diimplementasikan sebagai salah satu shortcut berikut:
- membaca raw bars atau raw indicators lalu menganggapnya otomatis setara dengan intake consumer-facing
- membaca session snapshot internals sebagai source utama PLAN
- membaca technical switching artifacts sebagai pengganti current publication semantics
- membuat istilah baru `input EOD sah` tanpa anchor ke kontrak producer-facing `market-data`

## Layer Mapping by Runtime Step

| Runtime step | Canonical layer | Primary object boundary | Read/Persist mode | Forbidden drift |
|---|---|---|---|---|
| Step 1 — Upstream Intake Read | producer-facing read adapter / intake repository | upstream intake DTO / normalized result object | read-only upstream | application service/domain compute membaca raw internals producer langsung |
| Step 2 — PLAN Build | domain compute + artifact assembler | PLAN compute input DTO -> PLAN result object -> PLAN artifact payload | compute, lalu persistence via repository | service menghitung scoring sendiri, presenter membentuk PLAN |
| Step 3 — RECOMMENDATION Build | domain compute + artifact assembler | recommendation input DTO/result object | compute, lalu persistence via repository | recommendation membaca CONFIRM atau source di luar PLAN |
| Step 4 — CONFIRM Build | orchestration binder + domain compute + artifact assembler | confirm input DTO/result object | compute, lalu persistence via repository | confirm memutasi recommendation membership/rank |
| Step 5 — Composite View / Response Exposure | application orchestration + presenter/transport DTO shaping | composite read result -> response DTO | read-only consumer exposure | transport mengambil policy decision atau persistence write |

## DTO / Result Object Expectations

- Step 1 wajib menghasilkan object intake yang cukup bersih untuk dipakai layer berikutnya, bukan raw rows yang dibiarkan hidup sebagai kontrak aktif.
- Step 2–4 wajib memisahkan compute input, compute result, dan artifact payload.
- Step 5 wajib memakai response DTO/presenter output yang berbeda dari persistence payload.

## Forbidden Layer Drift

1. request mentah langsung dipakai sebagai compute input PLAN / RECOMMENDATION / CONFIRM;
2. result query upstream langsung dipakai sekaligus sebagai artifact payload dan response DTO;
3. domain compute menulis persistence atau membentuk response transport final;
4. repository/persistence adapter menjalankan scoring, ranking, atau confirm decision.


## Chronological OOS bounded-read flow

For IS/OOS execution, freeze candidates first, derive the exact date/ticker pairs needed for D+1 through the maximum exit horizon, and resolve those pairs through `MarketDataPublishedEodSeriesReadService::readPublishedSeriesForDateTickerMap`. Do not allocate a full universe/date matrix. IS grid rows are evaluated in memory; only the final OOS proof export is written.

## Published-Price Runtime Metadata Freeze Rule

The published-price runtime owns the exact price-consumption markers. After strategy replay returns and before candidate freezing/hash calculation, it must bind:

```text
pricing_model = PUBLISHED_EOD_OHLCV_CURRENT_READABLE_EXACT_DATE
price_read_mode = TARGETED_DATE_TICKER_MAP
```

The binding must be reflected in `paramset_snapshot`, `meta.paramset_snapshot`, and runtime trade evidence. It must happen before future-price access so the frozen hash represents the exact runtime semantics. The binding may not backfill missing evaluation thresholds; unresolved thresholds must still fail closed.

The default Weekly Swing replay risk values are `stop_atr_mult=1.5` and `min_rr=1.5`. Grid values remain authoritative when explicitly supplied.


## BT-grid paramset compatibility projection

`WatchlistBacktestIsCalibrationService` must not merge a low `max_atr14_pct` grid value with the wider active default ideal ATR band directly. It delegates row-to-paramset construction to `WatchlistBacktestParamGridParamsetFactory` before any daily replay begins.

The factory records:

```text
bt_grid_resolution.risk_band_rule = CLAMP_DEFAULT_IDEAL_ATR_BAND_TO_GRID_MAX_ATR
```

and guarantees:

```text
min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct
```

This prevents valid strict grid rows from being misclassified as `WATCHLIST_BACKTEST_SOURCE_NOT_READY` because of an internally contradictory paramset. The projection is deterministic from the grid row plus canonical defaults and must remain independent of OOS data.


## Executable price and gap-fill boundary

Published OHLC availability is not sufficient evidence of an executable fill. The pricing evaluator must:

- require raw tradable integer-rupiah OHLC and reject adjusted-looking fractional bars;
- preserve theoretical `stop_price` / `target_price` separately from normalized trigger levels;
- fill an opening gap through stop or target at the bar open;
- use conservative stop-floor / target-ceil normalization for intraday triggers;
- emit `trigger_price`, `executed_price`, `fill_rule`, `gap_detected`, and price-rule markers;
- fail closed on adjusted-looking/non-executable OHLC rather than fabricate a raw fill.
- force these semantics at runtime; caller/grid overrides are ignored so artifact labels cannot diverge from actual fill behavior.

Changing these semantics changes `eval_model` and requires fresh IS/OOS evaluation rows; historical rows remain immutable.

## R2 IS-Only Runtime Artifact Flow

R2 entry-quality calibration uses a separate explicit flow:

```text
explicit R2 catalog
→ official IS calendar 2023-01-02..2025-05-21
→ catalog row to canonical paramset projection
→ strategy replay with final HOLD=5 dates censored from entry generation
→ exact published-price date/ticker reads bounded by 2025-05-21
→ canonical metrics and unchanged gates
→ catalog-aware watchlist_bt_eval persistence
→ deterministic IS-only JSON evidence
```

The R2 path does not construct an OOS split, call an OOS service/repository, or write `watchlist_bt_oos_eval_ws`. The R1 factory path remains compatibility-locked: catalog-enrichment columns are excluded from its runtime `bt_grid` snapshot so historical R1 paramset and binding hashes do not drift.
