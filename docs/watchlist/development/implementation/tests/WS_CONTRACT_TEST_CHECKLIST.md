# 13 — WS Contract Test Checklist

> **Current Conformance:** `NOT_ASSESSED_REVALIDATION_REQUIRED`  
> **Verification Epoch:** `WS-REBASELINE-20260819-001`  
> Existing content is a revalidation input. Historical PASS/READY/DONE wording does not grant current conformance.


## Scope

CONFIRM-specific acceptance wajib mengikuti `../CONFIRM_OPTIONAL_NON_BLOCKING_IMPLEMENTATION_GUARD.md`.

Checklist ini menetapkan acceptance minimum untuk core Weekly Swing dan optional CONFIRM boundary:
1. PLAN
2. RECOMMENDATION / final Top Picks
3. optional CONFIRM
4. relationship required-vs-optional antar lapisan tersebut

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
- [ ] consumer tetap dapat membaca PLAN/RECOMMENDATION walaupun CONFIRM tidak ada; recommendation set kosong tidak memerlukan CONFIRM

## D. Optional CONFIRM Runtime Shape Acceptance
- [ ] core PLAN + RECOMMENDATION dapat sukses tanpa CONFIRM request/artifact
- [ ] setiap evaluated CONFIRM harus terikat ke final Top Pick + immutable PLAN/recommendation binding pada entry window yang sama
- [ ] ticker di luar final Top Picks tidak boleh menghasilkan valid business CONFIRM
- [ ] no CONFIRM request menghasilkan valid `NOT_REQUESTED` semantics
- [ ] missing/delayed/stale/incomplete current data menghasilkan `UNAVAILABLE_RETRYABLE`, bukan `NOT_ACTIONABLE` dan bukan core failure
- [ ] `UNAVAILABLE_RETRYABLE` dapat dievaluasi ulang menjadi `ACTIONABLE` atau `NOT_ACTIONABLE` bila valid data kemudian tersedia sebelum entry-window expiry
- [ ] `NOT_ACTIONABLE` hanya boleh berasal dari valid current data + actually evaluated failed gate
- [ ] entry window yang habis tanpa valid evaluation menghasilkan `EXPIRED_UNCONFIRMED`, bukan core failure
- [ ] CONFIRM tidak menambah ticker baru dan tidak mengubah recommendation membership/rank/score/label/hash
- [ ] technical CONFIRM error setelah Top Picks valid tidak boleh back-propagate menjadi PLAN/RECOMMENDATION failure

## E. Cross-Layer Boundary Acceptance
- [ ] PLAN dapat berdiri sendiri tanpa RECOMMENDATION dan tanpa CONFIRM
- [ ] RECOMMENDATION hanya membaca PLAN immutable
- [ ] CONFIRM hanya membaca final Top Pick + immutable PLAN/recommendation binding; current input boleh belum tersedia
- [ ] RECOMMENDATION tidak membaca hasil CONFIRM
- [ ] CONFIRM tidak membentuk recommendation baru
- [ ] ticker Top Pick lalu confirmed tetap mempertahankan recommendation score/rank/label yang sama
- [ ] ticker non-Top-Pick tidak dapat menjadi valid CONFIRM target

## F. Determinism Acceptance
- [ ] PLAN replay dengan input yang sama menghasilkan PLAN output yang identik
- [ ] recommendation replay dengan PLAN dan policy yang sama menghasilkan output recommendation yang identik
- [ ] recommendation capital-aware replay dengan capital input yang sama menghasilkan output yang identik
- [ ] menjalankan CONFIRM setelah recommendation tidak mengubah payload recommendation normatif
- [ ] attempting CONFIRM on non-Top-Pick is rejected/invalid without mutating recommendation

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

1. Core acceptance Weekly Swing **MUST** mencakup PLAN + RECOMMENDATION/Top Picks dan membuktikan keduanya dapat selesai tanpa CONFIRM.
2. Recommendation availability **MUST NOT** bergantung pada CONFIRM.
3. Recommendation set **MAY** kosong dan kondisi tersebut **MUST** tetap dianggap valid.
4. CONFIRM eligibility **MUST** berasal dari final Top Picks, bukan sekadar PLAN candidate membership.
5. Missing/stale/incomplete CONFIRM input **MUST NOT** menjadi core failure atau synthetic `NOT_ACTIONABLE`.
6. CONFIRM **MUST** support retry from `UNAVAILABLE_RETRYABLE` while entry window is open.
7. CONFIRM **MUST NOT** mengubah recommendation payload normatif.
8. Core proof/readiness **MUST NOT** require CONFIRM evidence; CONFIRM proof is capability-specific.

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


## Campaign-specific test history
Campaign-scoped R2/C01/C171/R02/S01/P01 test additions were moved to `../../../records/history/campaign_addenda/WS_CONTRACT_TEST_CAMPAIGN_ADDITIONS_HISTORY.md`.

## Recurring Residue / Conformance Test Gate

Sebelum stage test evidence dipakai untuk `DONE`, cek juga `../../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`:

- current behavior punya positive + negative conformance tests;
- legacy branch/default/fallback yang harmful tidak reachable;
- controlled compatibility alias diuji exact mapping dan failure guard;
- fixture lama tidak menjadi satu-satunya alasan test PASS;
- evaluator/proof tests mengikat exact current strategy identity.

Test PASS tanpa residue/reachability check belum cukup untuk implementation conformance.
