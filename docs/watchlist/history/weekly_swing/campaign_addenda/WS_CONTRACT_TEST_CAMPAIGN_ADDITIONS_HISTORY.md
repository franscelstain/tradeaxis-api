# Weekly Swing Contract Test Campaign Additions History

> **Doc Role:** HISTORICAL / RESEARCH ADDENDA
> **Authority:** NON-CANONICAL. Preserved verbatim from the previous mixed document during architecture separation.

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

## C01 IS Failure Drilldown Payload Additions

- [ ] `watchlist:backtest-is-diagnose` requires explicit catalog/from/to/output and has no OOS option.
- [ ] drilldown reads only `2023-01-02..2025-05-21` for the immutable C01 diagnostic window.
- [ ] artifact includes `is_trading_date_hash`, `artifact_hash`, and `canonical_artifact_hash`.
- [ ] canonical artifact hash excludes timestamp-like metadata such as `generated_at`.
- [ ] two identical drilldown runs produce equal canonical artifact hashes.
- [ ] file SHA1 is identical when generated files are byte-identical.
- [ ] artifact contains ticker, month, trade-date, setup, ATR, score, breakout, momentum, volume, liquidity, sector, and score-component diagnostic sections.
- [ ] diagnostic feature sections are runtime-derived only when source fields exist in evaluated trade evidence.
- [ ] missing runtime feature fields are marked `FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE`, `NOT_DERIVED`, and `NOT_USED_FOR_NEXT_CATALOG_DECISION`.
- [ ] `runtime_consumed_parameter_summary` covers only registry/runtime-owned C01 axes.
- [ ] `dead_parameter_or_silent_default_summary` reports catalog mapping gaps or explicitly records no detected dead/silent default axis.
- [ ] no next catalog is designed from unavailable diagnostic fields.
- [ ] no OOS service/repository call, OOS table write, best-of-failed binding, promotion, order, broker, allocation, or production-ready output occurs.

## C171 Immutable Real-IS Remediation DRAFT Catalog Additions

- [ ] catalog identity is exactly `WS_BT_GRID_REAL_IS_REMEDIATION_C171_R1_2026_07`, version `C171-R1`, count `5`, and hash `82b0fcbf17823fda5ab59bd2dba3d947b4f9e233`;
- [ ] source `eval_id=188`, source `param_set_id=1`, diagnostic identity, and candidate-design identity are verified before any DRAFT persistence;
- [ ] `max_dv20_idr`, `max_vol_ratio`, and `top_max_score_total` are persisted, mapped, hashed, validated, and consumed at decision time;
- [ ] legacy paramsets without the optional upper bounds remain valid and behaviorally unchanged;
- [ ] `WS_LIQ_HIGH` and `WS_VOLR_HIGH` follow the locked reason priority and remain parity with the official seed;
- [ ] TOP score cap recalculates the same-day TOP quantile pool and cannot use realized return, OOS evidence, ticker blacklist, sector whitelist, or month blacklist;
- [ ] seed and DRAFT persistence reruns are idempotent; conflicting duplicate catalog/DRAFT payloads fail closed;
- [ ] exactly five new immutable DRAFT paramsets are persisted with distinct hashes and explicit catalog binding provenance;
- [ ] catalog persistence invokes no official IS runtime, OOS service/repository, promotion, PLAN, recommendation, CONFIRM, activation, or rollout;
- [ ] canonical gates remain unchanged and C172 stays blocked until a later official IS run passes every gate.

## C171 Final Closure and Separate New-Strategy Research

- [ ] `C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION` remains immutable;
- [ ] no additional C171 catalog, DRAFT, official IS, or C172 OOS is created;
- [ ] separate strategy research uses a new scope identity and does not present itself as C171 remediation;
- [ ] failed C171 evidence is read-only diagnostic input, not a best-of-failed binding;
- [ ] equity research features are bound to exact signal-date publication lineage;
- [ ] realized return, actual entry gap, fill rule, and exit result remain post-selection diagnostic fields;
- [ ] no OOS table is read until a candidate in the separate scope passes every unchanged canonical IS gate;
- [ ] tracker `ACTIVE SESSION` values remain synchronized after the scope transition.

## WS New Strategy R02 Closure

- [ ] exactly three initial one-idea candidates are bound before Official IS;
- [ ] initial Official IS uses only the canonical `2023-01-02..2025-05-21` window;
- [ ] at most one remediation is persisted and evaluated;
- [ ] remediation selection remains identical to its declared source candidate;
- [ ] remediation exit routes are fixed before entry and evaluated chronologically;
- [ ] a Dn close signal cannot exit before D(n+1) open;
- [ ] raw tradable OHLCV and IDX tick normalization remain mandatory;
- [ ] future target outcomes and future-derived buckets cannot select an exit route;
- [ ] every canonical IS threshold remains unchanged;
- [ ] failure of any gate after the one remediation closes R02 with OOS row count zero;
- [ ] closed R02 cannot be rescued by another remediation, blacklist, promotion, PLAN, or rollout.

## WS Tail Risk S01 Closure

- [ ] S01 uses a separate scope identity and does not reopen R02;
- [ ] immutable source eval `211`, paramset `19`, artifact hash, and evidence
  manifest are verified before diagnostic interpretation;
- [ ] the diagnostic is read-only and never reads OOS;
- [ ] exactly three one-idea candidates are locked before DRAFT persistence and
  Official IS;
- [ ] H1 benchmark and H2 tick-risk features use exact signal-date lineage and
  fail closed when unavailable;
- [ ] H3 close-loss signal and remediation close-loss signal execute only at
  the next trading-day open;
- [ ] all target, close-signal, and time-exit routes are evaluated
  chronologically from raw tradable OHLCV;
- [ ] no realized return, future path, ticker blacklist, month blacklist, or
  OOS result routes candidate selection or execution;
- [ ] initial evals `212-214` use unchanged canonical IS gates;
- [ ] at most one S01 remediation is persisted and evaluated;
- [ ] remediation eval `215` failure closes S01 with no further remediation;
- [ ] OOS row count remains zero and no promotion, ACTIVE paramset, PLAN,
  CONFIRM, activation, or rollout occurs.

## WS Price Quality P01 Closure

- [x] P01 uses a separate scope and does not reopen C171, R02, or S01.
- [x] thresholds 50, 100, and 200 were locked before diagnostic runtime.
- [x] only diagnostic-authorized thresholds 50 and 100 were persisted.
- [x] exact signal-date close, ROC20, and IHSG regime fail closed when absent.
- [x] initial evals 216-217 used unchanged canonical IS gates and both failed.
- [x] exactly one remediation retained C1 selection and introduced no new
  signal-price threshold.
- [x] the close-loss signal executes only at the next trading-day open.
- [x] eval 218 generic model-label drift was detected and not used as final
  identity evidence.
- [x] identity repair changed no strategy semantics and did not increment the
  remediation round.
- [x] authoritative eval 219 stores `SEQ_TP05_PCL1NO_TIME` identity and
  reproduced eval 218 metrics.
- [x] eval 219 failed four unchanged canonical gates and closed P01.
- [x] no OOS service, repository, or table read; no promotion, ACTIVE
  paramset, PLAN, CONFIRM, activation, or rollout occurred.
- [x] focused P01 and full Watchlist PHPUnit suites pass.
