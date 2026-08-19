# Legacy Role Extract — WS — IMPLEMENTATION

> **Document Type:** IMPLEMENTATION
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0553-IMP-01`
> **Legacy Source ID:** `LS-WS-0553`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`
> **Original SHA1:** `040F62E02B9B13136AF9445261B20C4C0EF12B01`
> **Source Sections:** L75-L94 Reason code equivalence (LOCKED)
> **Extract Body SHA1:** `1927D6A4A6D35D17976A9B4A3FEE0FB479075101`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## Reason code equivalence (LOCKED)

Owner vocabulary reason code tetap pada file 07; dokumen ini hanya mengunci equivalence outcome dan prioritasnya.


### Rule: canonical fail reason
Jika satu ticker gagal lebih dari satu guardrail, reason utama dipilih berdasarkan prioritas berikut dan **harus memakai kode WS_* yang sama** di backtest maupun production.

Priority order (highest first):
1) `WS_DATA_MISSING` — data-quality / required fields tidak lengkap atau invalid
2) `WS_LIQ_FAIL` — liquidity lower guard gagal (`dv20_idr < min_dv20_idr`)
3) `WS_LIQ_HIGH` — optional C171 liquidity upper guard gagal (`dv20_idr > max_dv20_idr`)
4) `WS_ATR_LOW` — volatility lower guard gagal (`atr14_pct < min_atr14_pct`)
5) `WS_ATR_HIGH` — volatility upper guard gagal (`atr14_pct > max_atr14_pct`)
6) `WS_VOLR_FAIL` — volume participation lower guard gagal (`vol_ratio < min_vol_ratio`)
7) `WS_VOLR_HIGH` — optional C171 volume upper guard gagal (`vol_ratio > max_vol_ratio`)
8) `WS_TICK_RISK_HIGH` — optional C171 C01 decision-time tick-risk expansion melebihi batas paramset

Backtest dan production wajib memakai prioritas yang sama agar audit konsisten.
