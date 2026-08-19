# Legacy Role Extract — WS — RESEARCH

> **Document Type:** RESEARCH
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0551-RES-02`
> **Legacy Source ID:** `LS-WS-0551`
> **Legacy Work Key:** `WS`
> **Original Path:** `docs/watchlist/system/policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md`
> **Original SHA1:** `99AA10494EB908109701F152E21402943FEF0B63`
> **Source Sections:** L104-L122 R2 Entry-Quality IS-Only Calibration Additions
> **Extract Body SHA1:** `1059C16B11F04A3D80B230A6A3BAD25E389DA165`
> **Current Authority:** NO

The body below is an exact copy of the identified original sections. No semantic rewriting was applied.

---

## R2 Entry-Quality IS-Only Calibration Additions

- [ ] R1 rows remain count `24` and hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c` before and after R2 seed/calibration.
- [ ] R2 has a distinct explicit catalog code/version/hash and coexists with R1.
- [ ] R2 catalog is finite, curated, deterministic, duplicate-free, and contains one R1 control row.
- [ ] every R2 axis is `bt_target=true`, persisted, mapped, and consumed by runtime.
- [ ] changing each R2 field changes the paramset hash or relevant deterministic output.
- [ ] explicit R2 values are never replaced by defaults.
- [ ] liquidity, volume, ATR, ROC, weight, and quantile invariants fail closed.
- [ ] `risk.stop_atr_mult`, `risk.min_rr`, `grouping.top_picks_target=5`, `grouping.secondary_target=10`, fees, slippage, gap rule, price bands, and HOLD=5 remain fixed.
- [ ] command requires explicit catalog/from/to/output and exposes no OOS option.
- [ ] only `2023-01-02..2025-05-21` is accepted for the immutable R2 run.
- [ ] final five IS dates are censored from entry generation, not read beyond the IS boundary.
- [ ] mutation of data after `2025-05-21` cannot change R2 metrics, binding, or artifact hash.
- [ ] no OOS service/repository call and no OOS table mutation occur.
- [ ] exact eval rerun is idempotent; conflicting duplicate fails closed.
- [ ] no best-of-failed binding is created.
- [ ] two identical runs produce equal catalog/date/evaluation/binding/artifact hashes.
