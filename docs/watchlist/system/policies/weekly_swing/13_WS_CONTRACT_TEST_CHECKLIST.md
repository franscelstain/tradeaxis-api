# 13 — WS Contract Test Checklist

## Scope

Checklist ini menetapkan acceptance minimum untuk artefak dan boundary Weekly Swing berikut:
1. PLAN
2. RECOMMENDATION
3. CONFIRM
4. relationship antar ketiga lapisan tersebut

## A. PLAN Runtime Shape Acceptance
- [ ] PLAN runtime output memiliki `meta`, `items`, `summary`
- [ ] PLAN output dapat direplay dengan stabil

## B. RECOMMENDATION Runtime Shape Acceptance
- [ ] recommendation output memiliki `meta`, `items`, dan `summary`
- [ ] recommendation `meta.trade_date` cocok dengan PLAN `trade_date`
- [ ] recommendation hanya memuat ticker yang berasal dari PLAN item yang sah
- [ ] recommendation output dapat tersedia walaupun CONFIRM belum ada
- [ ] recommendation output tidak memuat field hasil CONFIRM sebagai input pembentukannya
- [ ] recommendation set boleh kosong
- [ ] `empty_recommendation_flag = true` jika dan hanya jika `recommended_count = 0`
- [ ] recommendation ranking deterministik untuk input yang sama
- [ ] mode `CAPITAL_FREE` tetap valid tanpa capital input
- [ ] mode `CAPITAL_AWARE` tetap deterministic untuk capital input yang sama

## C. Empty Recommendation Acceptance
- [ ] recommendation set kosong dianggap valid jika proses recommendation selesai dengan policy yang sah
- [ ] recommendation set kosong tidak dianggap error hanya karena `TOP_PICKS` dan/atau `SECONDARY` pada PLAN tidak kosong
- [ ] consumer tetap dapat membaca PLAN dan CONFIRM walaupun recommendation set kosong

## D. CONFIRM Runtime Shape Acceptance
- [ ] setiap hasil CONFIRM harus terikat ke candidate PLAN yang sah pada `trade_date` yang sama
- [ ] ticker di luar candidate PLAN tidak boleh menghasilkan CONFIRM yang valid
- [ ] ticker recommended dapat di-confirm
- [ ] ticker non-recommended tetap dapat di-confirm selama masih valid sebagai candidate PLAN
- [ ] recommendation yang kosong tidak menghalangi CONFIRM terhadap candidate PLAN
- [ ] CONFIRM tidak menambah ticker baru di luar PLAN
- [ ] CONFIRM tidak mengubah recommendation membership/rank/score/label/hash

## E. Cross-Layer Boundary Acceptance
- [ ] PLAN dapat berdiri sendiri tanpa RECOMMENDATION dan tanpa CONFIRM
- [ ] RECOMMENDATION hanya membaca PLAN immutable
- [ ] CONFIRM hanya membaca PLAN candidate binding dan overlay input yang sah
- [ ] RECOMMENDATION tidak membaca hasil CONFIRM
- [ ] CONFIRM tidak membentuk recommendation baru
- [ ] ticker recommended lalu confirmed tetap mempertahankan recommendation score/rank/label yang sama
- [ ] ticker non-recommended lalu confirmed tetap tidak otomatis menjadi recommended

## F. Determinism Acceptance
- [ ] PLAN replay dengan input yang sama menghasilkan PLAN output yang identik
- [ ] recommendation replay dengan PLAN dan policy yang sama menghasilkan output recommendation yang identik
- [ ] recommendation capital-aware replay dengan capital input yang sama menghasilkan output yang identik
- [ ] menjalankan CONFIRM setelah recommendation tidak mengubah payload recommendation normatif
- [ ] menjalankan CONFIRM pada ticker non-recommended tidak mengubah membership recommendation

## G. Published-Price Backtest Runtime Acceptance
- [ ] runtime replay menggunakan explicit `from/to` dan official trading calendar
- [ ] exact-date readable publication dan published EOD OHLCV digunakan tanpa latest fallback
- [ ] trade candidate dibekukan sebelum future price series dibaca
- [ ] bar `volume <= 0` atau volume tidak tersedia tidak digunakan sebagai entry, TP, SL, atau time-exit fill
- [ ] zero-volume entry menghasilkan `BT_SKIP_NO_TRADABLE_ENTRY` dan `ret_net = NULL`
- [ ] zero-volume final exit menghasilkan `BT_SKIP_NO_TRADABLE_EXIT` dan `ret_net = NULL`
- [ ] gap di bawah stop memakai executable open, bukan theoretical stop
- [ ] gap di atas target memakai executable open, bukan theoretical target
- [ ] intraday stop/target memakai normalized IDX price fraction
- [ ] artifact membedakan `trigger_price` dan `executed_price`
- [ ] adjusted-looking/fractional OHLC entry/exit fail closed dengan `ret_net = NULL`
- [ ] canonical `eval` thresholds tersedia pada `paramset_snapshot`
- [ ] threshold unresolved memblokir artifact export
- [ ] input identik menghasilkan canonical `validation.artifact_hash` identik
- [ ] file-byte hash boleh berbeda hanya karena metadata non-hashed yang terdokumentasi

## Final Rules

1. Acceptance Weekly Swing **MUST** mencakup PLAN, RECOMMENDATION, CONFIRM, dan boundary di antaranya.
2. Recommendation availability **MUST NOT** bergantung pada CONFIRM.
3. Recommendation set **MAY** kosong dan kondisi tersebut **MUST** tetap dianggap valid.
4. CONFIRM eligibility **MUST** berasal dari candidate PLAN membership.
5. Ticker non-recommended **MAY** tetap memiliki CONFIRM yang valid jika masih merupakan candidate PLAN.
6. CONFIRM **MUST NOT** mengubah recommendation payload normatif.

## OOS runtime gap-closure test additions

- canonical grid catalog is non-empty, deterministic, duplicate-free, and ordered by persisted `param_id ASC`;
- every grid row has valid units, positive targets, `stop_atr_mult > 0`, `min_rr > 0`, and scoring weights summing to `1.0`;
- grid seed rerun is idempotent and duplicate database payloads fail closed;
- existing grid schema without stop/RR is migrated without deleting rows;
- `watchlist_bt_eval` identity includes `eval_model` and `paramset_hash`, preserving legacy evidence across semantic reruns;
- published-price runtime reads exact candidate date/ticker pairs instead of a full date/ticker cartesian product;
- IS grid evaluation stays in memory and writes no temporary JSON per parameter;
- ATR/RR fallback levels are applied when PLAN has no explicit stop/target and carry trade evidence;
- OOS proof remains one explicit window; internal bounded reads do not change split or selection;
- worst/best trade evidence contains prices, dates, level source, returns, volumes, and publication lineage where available.

## OOS grid paramset compatibility

- [ ] Every canonical `watchlist_bt_param_grid` row resolves into a cross-field valid runtime paramset.
- [ ] `min_atr14_pct <= atr_ideal_low <= atr_ideal_high <= max_atr14_pct` for all rows.
- [ ] Strict max-ATR rows are not rejected only because active default ideal-band values are wider.
- [ ] `bt_grid_resolution.risk_band_rule` is present and deterministic.
- [ ] The projection uses no OOS metrics or price outcomes.

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

## C01 Downside/Stability IS-Only Implementation Additions

- [ ] R1 rows remain count `24` and hash `9da8b0983c57bde1ce0a1fbf1c119756f8af431c` before and after C01 seed/calibration.
- [ ] R2 rows remain count `12` and hash `0f2eaadaa446980a3d5e48cd498df2a8157c01a5` before and after C01 seed/calibration.
- [ ] C01 has semantic catalog identity `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06`, version `C01`, count `8`, and hash `604ac98f6f193a4c317d4f25582deada84682846`.
- [ ] C01 catalog is finite, curated, deterministic, duplicate-free, and has no `_R3_`, `_R4_`, or `_R5_` catalog identity.
- [ ] every C01 axis is `bt_target=true`, persisted, mapped, and consumed by runtime.
- [ ] explicit C01 values are never replaced by defaults.
- [ ] C01 seed is explicit and idempotent; conflicting duplicate payloads fail closed.
- [ ] C01 calibration uses only `2023-01-02..2025-05-21` and does not call OOS service/repository or mutate `watchlist_bt_oos_eval_ws`.
- [ ] C01 runtime returns `C01_GRID_FAILED_IS_QUALITY` when all rows reach canonical gates but none pass.
- [ ] C01 success may freeze a best-IS binding only when every canonical IS gate passes; no best-of-failed binding is allowed.
