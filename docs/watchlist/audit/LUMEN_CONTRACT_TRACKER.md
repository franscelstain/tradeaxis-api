# Watchlist Lumen Contract Tracker

## Document Purpose

Dokumen ini melacak kontrak perilaku yang harus dipenuhi selama implementasi watchlist di Lumen.

Dokumen ini bukan owner business rule. Kontrak di sini harus ditelusuri ke:

- `docs/watchlist/system/policy.md`;
- `docs/watchlist/system/policies/weekly_swing/**`;
- `docs/watchlist/system/implementation/weekly_swing/**` sebagai translation guidance;
- owner upstream market-data untuk producer-facing consumer read contract.

## ACTIVE SESSION

Session:
`WATCHLIST - C06 MODERATE-CAP CANDIDATE-SELECTION IMPLEMENTATION SESSION`

Status:
`C06_IMPLEMENTED / C06_SEEDED / C06_IS_EXECUTED / C06_IS_QUALITY_FAILED / C06_REJECTED_AS_STRATEGY_CATALOG / C06_DETERMINISTIC / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C06 contract evidence:

- R1/R2/C01/C02/C03/C04/C05 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02/C03/C04/C05 remain rejected as strategy-quality catalogs and were not patched to look successful;
- C06 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C06_2026_06`, version `C06`, count `12`, hash `6c93d67fb77319a02cecc3d96fd99bb0e139a1ac`;
- C06 uses a C06-only runtime-supported candidate-selection extension in `bt_grid_resolution.candidate_selection_extension`;
- C06 does not introduce unsupported `sector_code` or `sector_filter` catalog axes;
- C06 PHPUnit validation passed: C06 filter `OK (13 tests, 503 assertions)` and full Watchlist `OK (290 tests, 6168 assertions)`;
- C06 seed passed and inserted 12 catalog rows with `updated_count=0`;
- seed-time R1/R2/C01/C02/C03/C04/C05 immutability markers were all true;
- C06 IS calibration run 1 and run 2 both failed quality with the same deterministic artifact hash `ede8ca6f53ea49141a5e047e6094b7a282cdb232`;
- C06 quality failure is explicit: `C06_GRID_FAILED_IS_QUALITY` / `WS_BT_C06_NO_VALID_IS_CANDIDATE` / `is_valid_param_count=0` / `is_failed_param_count=12`;
- C06 did not open OOS and all reported OOS guards remained clean;
- C06 has no best IS binding and no best IS binding hash, so it cannot advance to OOS.

Contract status update:

- `WL-CONTRACT-007`: PASS for C06 catalog identity, seed, and R1/R2/C01/C02/C03/C04/C05 immutability evidence;
- `WL-CONTRACT-008`: PASS for C06 traceability as a new catalog derived from C01/C04/C05 forensic evidence, not a mutation of prior catalogs;
- `WL-CONTRACT-009`: PASS for C06 IS-only boundary in calibration output;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C06 IS calibration;
- `WL-CONTRACT-011`: FAILED_QUALITY for C06 strategy quality because no valid IS candidate exists;
- `WL-CONTRACT-014`: PASS for C06 docs/test/command/forensic tracking update with per-param C06 metrics extracted from current artifacts.

C06 OOS-proof eligibility:

```text
NOT_ELIGIBLE
```

Reason:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

Production-readiness status:

```text
NOT_PRODUCTION_READY
```

Reason: C06 has no valid IS binding and no OOS proof. C06 must remain rejected as a strategy-quality catalog.

## PRIOR SESSION - C05 SOFT SAMPLE-AWARE CANDIDATE-SELECTION IMPLEMENTATION SESSION

Session:
`WATCHLIST - C05 SOFT SAMPLE-AWARE CANDIDATE-SELECTION IMPLEMENTATION SESSION`

Status:
`C05_IMPLEMENTED / C05_SEEDED / C05_IS_EXECUTED / C05_IS_QUALITY_FAILED / C05_REJECTED_AS_STRATEGY_CATALOG / C05_DETERMINISTIC / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C05 contract evidence:

- R1/R2/C01/C02/C03/C04 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02/C03/C04 remain rejected as strategy-quality catalogs and were not patched to look successful;
- C05 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C05_2026_06`, version `C05`, count `12`, hash `476af5dde18079b1270556bc44bbc632edd46e27`;
- C05 uses a C05-only runtime-supported candidate-selection extension in `bt_grid_resolution.candidate_selection_extension`;
- C05 does not introduce unsupported `sector_code` or `sector_filter` catalog axes;
- C05 PHPUnit validation passed: C05 filter `OK (13 tests, 523 assertions)` and full Watchlist `OK (277 tests, 5665 assertions)`;
- C05 seed passed and inserted 12 catalog rows with `updated_count=0`;
- seed-time R1/R2/C01/C02/C03/C04 immutability markers were all true;
- C05 IS calibration run 1 and run 2 both failed quality with the same deterministic artifact hash `f8288cb2d395e397f433dae854c0ad80b4650a8d`;
- C05 quality failure is explicit: `C05_GRID_FAILED_IS_QUALITY` / `WS_BT_C05_NO_VALID_IS_CANDIDATE` / `is_valid_param_count=0` / `is_failed_param_count=12`;
- C05 did not open OOS and all reported OOS guards remained clean;
- C05 has no best IS binding and no best IS binding hash, so it cannot advance to OOS.

Contract status update:

- `WL-CONTRACT-007`: PASS for C05 catalog identity, seed, and R1/R2/C01/C02/C03/C04 immutability evidence;
- `WL-CONTRACT-008`: PASS for C05 traceability as a new catalog derived from C04 forensic evidence, not a mutation of prior catalogs;
- `WL-CONTRACT-009`: PASS for C05 IS-only boundary in calibration output;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C05 IS calibration;
- `WL-CONTRACT-011`: FAILED_QUALITY for C05 strategy quality because no valid IS candidate exists;
- `WL-CONTRACT-014`: PASS for C05 docs/test/command/forensic tracking update with per-param C05 metrics extracted from current artifacts.

C05 OOS-proof eligibility:

```text
NOT_ELIGIBLE
```

Reason:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

Production-readiness status:

```text
NOT_PRODUCTION_READY
```

Reason: C05 has no valid IS binding and no OOS proof. C05 must remain rejected as a strategy-quality catalog.

## PRIOR SESSION - C04 IS CANDIDATE-SELECTION REDESIGN AND IMPLEMENTATION SESSION

Session:
`WATCHLIST - C04 IS CANDIDATE-SELECTION REDESIGN AND IMPLEMENTATION SESSION`

Status:
`C04_IMPLEMENTED / C04_SEEDED / C04_IS_EXECUTED / C04_IS_QUALITY_FAILED / C04_REJECTED_AS_STRATEGY_CATALOG / C04_DETERMINISTIC / OOS_NOT_RUN / NOT_PRODUCTION_READY / C05_REQUIRED_IF_CONTINUED`.

Current C04 contract evidence:

- R1/R2/C01/C02/C03 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02 and C03 remain rejected as strategy-quality catalogs and were not patched to look successful;
- C04 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C04_2026_06`, version `C04`, count `10`, hash `0ce3a313c45432c5a4d607def12b3f774988f324`;
- C04 uses a C04-only runtime-supported candidate-selection extension in `bt_grid_resolution.candidate_selection_extension`;
- C04 does not introduce unsupported `sector_code` or `sector_filter` catalog axes;
- C04 PHPUnit validation passed: C04 filter `OK (14 tests, 499 assertions)` and full Watchlist `OK (264 tests, 5142 assertions)`;
- C04 seed passed and inserted 10 catalog rows with `updated_count=0`;
- seed-time R1/R2/C01/C02/C03 immutability markers were all true;
- C04 IS calibration run 1 and run 2 both failed quality with the same deterministic artifact hash `fe964ee879dddc8aa8a83372e8c2d05aed5e8259`;
- C04 quality failure is explicit: `C04_GRID_FAILED_IS_QUALITY` / `WS_BT_C04_NO_VALID_IS_CANDIDATE` / `is_valid_param_count=0` / `is_failed_param_count=10`;
- C04 did not open OOS and all reported OOS guards remained clean;
- C04 has no best IS binding and no best IS binding hash, so it cannot advance to OOS.

Contract status update:

- `WL-CONTRACT-007`: PASS for C04 catalog identity, seed, and R1/R2/C01/C02/C03 immutability evidence;
- `WL-CONTRACT-008`: PASS for C04 traceability as a new catalog derived from C01/C02/C03 forensic evidence, not a mutation of prior catalogs;
- `WL-CONTRACT-009`: PASS for C04 IS-only boundary in calibration output;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C04 IS calibration;
- `WL-CONTRACT-011`: FAILED_QUALITY for C04 strategy quality because no valid IS candidate exists;
- `WL-CONTRACT-014`: PASS for C04 docs/test/command/forensic tracking update with per-param C04 metrics extracted from current artifacts.

C04 OOS-proof eligibility:

```text
NOT_ELIGIBLE
```

Reason:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

Production-readiness status:

```text
NOT_PRODUCTION_READY
```

Reason: C04 has no valid IS binding and no OOS proof. C04 must remain rejected as a strategy-quality catalog.

Next contract work if continued:

```text
C05_REQUIRED
```

C05 must be a new catalog identity and must preserve R1/R2/C01/C02/C03/C04 immutability. It must not add unsupported sector filters, must not loosen canonical gates, and must not run OOS unless a valid IS candidate is first proven.

## PRIOR SESSION - C03 OPERATOR VALIDATION AND IS QUALITY FORENSIC FINALIZATION SESSION

Session:
`WATCHLIST - C03 OPERATOR VALIDATION AND IS QUALITY FORENSIC FINALIZATION SESSION`

Status:
`C03_OPERATOR_VALIDATED / C03_SEEDED / C03_IS_EXECUTED / C03_IS_QUALITY_FAILED / C03_REJECTED_AS_STRATEGY_CATALOG / C03_DETERMINISTIC / OOS_NOT_RUN / NOT_PRODUCTION_READY / C04_REQUIRED`.

Current C03 contract evidence:

- R1/R2/C01/C02 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02 remains rejected as a strategy-quality catalog and was not patched to look successful;
- C03 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C03_2026_06`, version `C03`, count `10`, hash `29e15ceab1b3f7dc31a21f339ac6ab7483e14800`;
- C03 operator PHPUnit validation passed: C03 filter `OK (12 tests, 461 assertions)` and full Watchlist `OK (250 tests, 4643 assertions)`;
- C03 seed passed and inserted 10 catalog rows with `updated_count=0`;
- seed-time R1/R2/C01/C02 immutability markers were all true;
- C03 IS calibration run 1 and run 2 both failed quality with the same deterministic artifact hash `649e8fead0c57262307f749a4776f053f5ccd0f8`;
- C03 quality failure is explicit: `C03_GRID_FAILED_IS_QUALITY` / `WS_BT_C03_NO_VALID_IS_CANDIDATE` / `is_valid_param_count=0` / `is_failed_param_count=10`;
- C03 did not open OOS and all reported OOS guards remained clean;
- C03 has no best IS binding and no best IS binding hash, so it cannot advance to OOS.

Contract status update:

- `WL-CONTRACT-007`: PASS for C03 catalog identity, seed, and R1/R2/C01/C02 immutability evidence;
- `WL-CONTRACT-008`: PASS for C03 traceability as a new catalog derived from C02/C01 evidence, not a mutation of prior catalogs;
- `WL-CONTRACT-009`: PASS for C03 IS-only boundary in operator calibration output;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C03 IS calibration;
- `WL-CONTRACT-011`: FAILED_QUALITY for C03 strategy quality because no valid IS candidate exists;
- `WL-CONTRACT-014`: PASS for C03 docs/test/command tracking update; per-param C03 forensic metrics are now extracted from available workspace JSON artifacts.

C03 OOS-proof eligibility:

```text
NOT_ELIGIBLE
```

Reason:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
oos_executed=0
production_ready=0
```

Production-readiness status:

```text
NOT_PRODUCTION_READY
```

Reason: C03 has no valid IS binding and no OOS proof. C03 must remain rejected as a strategy-quality catalog.

Next contract work:

```text
C04_REQUIRED
```

C04 must be a new catalog identity and must change the candidate-selection axis using only runtime-supported fields. It must not mutate R1/R2/C01/C02/C03, must not add unsupported sector filters, must not loosen quality gates, and must not run OOS unless a valid IS candidate is first proven.

## PRIOR SESSION — C02 DOWNSIDE STABILITY OPERATOR FORENSIC FINALIZATION SESSION

Session:
`WATCHLIST - C02 DOWNSIDE STABILITY OPERATOR FORENSIC FINALIZATION SESSION`

Status:
`C02_IMPLEMENTATION_PASS / C02_OPERATOR_VALIDATION_PASS / C02_IS_EXECUTION_PASS / C02_IS_QUALITY_FAIL / C02_REJECTED_AS_STRATEGY_CATALOG / OOS_NOT_RUN / NOT_PRODUCTION_READY / C03_REQUIRED`.

Current C02 contract evidence:

- R1/R2/C01 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06`, version `C02`, count `8`, hash `7287c438e15bd03d6beb4796e4d5159ecd8ed59a`;
- C02 design comes from current C01 runtime-derived drilldown buckets and uses only existing runtime-consumed grid axes;
- C02 does not introduce `sector_code` or `sector_filter` as a persisted/grid axis; sector evidence is diagnostic-only until a real sector axis is designed and consumed safely by runtime;
- C02 seed is operator-validated as PASS with R1/R2/C01 immutability markers intact and `oos_executed=0`;
- C02 unit/static tests are operator-validated as PASS: `WatchlistBacktestC02` 12 tests / 391 assertions;
- full Watchlist unit/static suite is operator-validated as PASS: 238 tests / 4182 assertions;
- C02 IS calibration executed twice and produced deterministic artifact hash `81da37a1c526cf71c096a4be6fc8623b013ae3a2`;
- C02 IS execution returned `C02_GRID_FAILED_IS_QUALITY`, `is_valid_param_count=0`, `is_failed_param_count=8`, empty best IS binding, and `production_ready=0`;
- every C02 param failed `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, and `WS_BT_EVAL_STABILITY_FAIL`;
- OOS service/repository/table markers remained clean: `oos_service_invoked=0`, `oos_repository_invoked=0`, `oos_table_unchanged=1`, `oos_executed=0`;
- final forensic details are recorded in `docs/watchlist/audit/WS_C02_OPERATOR_FORENSIC_FINAL_RESULT.md`.
- post-docs validation evidence confirms the final C02 documentation/forensic CSV sync did not break `WatchlistBacktestC02` or the full Watchlist unit suite.

Authoring environment validation actually performed:

```text
php lint C02/modified Watchlist PHP files = PASS
C02 pure PHP catalog/factory smoke = PASS / exit code 0
```

Operator validation evidence supplied after authoring:

```text
C02 PHPUnit = PASS / OK (12 tests, 391 assertions)
Full Watchlist PHPUnit = PASS / OK (238 tests, 4182 assertions)
C02 seed = PASS / inserted_count=8 / updated_count=0 / r1_immutable=1 / r2_immutable=1 / c01_immutable=1 / oos_executed=0 / production_ready=0
C02 IS run 1 = C02_GRID_FAILED_IS_QUALITY / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2 / is_valid_param_count=0 / is_failed_param_count=8
C02 IS run 2 = C02_GRID_FAILED_IS_QUALITY / artifact_hash=81da37a1c526cf71c096a4be6fc8623b013ae3a2 / is_valid_param_count=0 / is_failed_param_count=8
```

Post-docs validation evidence after documentation/forensic CSV sync:

```text
scope=DOCUMENTATION_AND_FORENSIC_CSV_ONLY
runtime_code_changed=false
catalog_changed=false
seed_rerun_required=false
calibration_rerun_required=false
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestC02" = PASS / OK (12 tests, 391 assertions) / Time 00:01.281 / Memory 14.00 MB / exit code 0
vendor\bin\phpunit tests\Unit\Watchlist = PASS / OK (238 tests, 4182 assertions) / Time 00:04.431 / Memory 24.00 MB / exit code 0
post_docs_validation_verdict=PASS
```

Contract impact:

- `WL-CONTRACT-007`: DONE for C02 immutable catalog identity and seed immutability evidence, not production `LOCKED`;
- `WL-CONTRACT-008`: DONE for C02 explainability/design traceability and final forensic evidence;
- `WL-CONTRACT-009`: DONE for C02 IS-only artifact boundary and no-OOS runtime markers;
- `WL-CONTRACT-010`: DONE for C02 two-run deterministic artifact hash proof;
- `WL-CONTRACT-011`: FAILED_STRATEGY_QUALITY for C02; no row passed canonical IS gates;
- `WL-CONTRACT-014`: docs synchronized for C02 operator evidence and forensic final; post-docs PHPUnit validation PASS confirms the sync did not break Watchlist static/unit guards;
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; production readiness remains blocked.

C02 OOS-proof eligibility:

```text
NOT_ELIGIBLE_FOR_OOS_PROOF — C02 has zero valid IS candidates and no frozen best-IS binding.
```

Promotion eligibility:

```text
NOT_ELIGIBLE — C02 failed strategy quality and OOS proof is missing.
```

Required next contract work:

```text
WATCHLIST — C03 IS QUALITY CATALOG DESIGN AND IMPLEMENTATION SESSION
```

The next contract work must design a new C03 catalog from C02 forensic metrics. It must preserve R1/R2/C01/C02 as immutable evidence, keep OOS unread, avoid best-of-failed selection, and avoid production-ready claims.

## PRIOR SESSION — C01 DIAGNOSTIC PAYLOAD EXPANSION

Session:
`WATCHLIST - C01 IS FAILURE DRILLDOWN PAYLOAD EXPANSION SESSION`

Status:
`DONE for C01 IS failure drilldown diagnostic runtime scope / LOCAL_C01_IS_FAILURE_DRILLDOWN_EXECUTED / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Historical baselines remain valid and are not downgraded:

- `PHASE_6_CONFIRM_OVERLAY_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`;
- `DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`;
- `FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`;
- `LOCAL_C01_IS_CALIBRATION_EXECUTED / C01_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Current C01 IS failure drilldown contract evidence:

- R1/R2/C01 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C01 two-run artifacts remain deterministic by file SHA1 equality `04f6c664a0c9006c16542a8380034a0a633041dc` and canonical artifact hash `c8505ce5a9045629234a685984d9138b3990c775`;
- C01 runtime quality remains failed with `is_valid_param_count=0`, `is_failed_param_count=8`, no best IS binding, and failure classes `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, `WS_BT_EVAL_STABILITY_FAIL`;
- expanded the IS-only diagnostic command/service to generate deeper C01 failure drilldown artifacts without OOS service/repository dependency;
- current workspace contains C01 drilldown run 1 and run 2 with identical file SHA1 `a34f6efaca2fdd16a052637a5e455013b60244cd`;
- C01 drilldown canonical artifact hash is identical across both runs: `1212405907b33c98b787f473af07472fa74b2508`;
- C01 drilldown `is_trading_date_hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753`;
- two-run diagnostic commands completed with exit code `0` and `status=DONE`;
- command blocks empty `--catalog-code`, requires explicit `--from`, `--to`, `--output`, and requires explicit `--overwrite` for replacement;
- service enforces exact frozen IS window `2023-01-02..2025-05-21`, `hard_market_data_to_date`, no latest/active fallback markers, no current-date/default max-date path, no OOS write, and no production-ready/promotion output;
- the prior payload gap is closed for the current runtime: breakout/momentum/volume/liquidity/sector/score-component buckets are derived from runtime evidence exported through market-data, candidate, scoring, PLAN, and strategy trade payloads;
- derived diagnostic review is recorded as review-only evidence; candidate focus was anti-chase / moderate-liquidity-volume / near-breakout / sector-aware stability; at that historical session boundary C02 remained `NOT_DESIGNED`, and this is superseded by the current C02 final result above;
- no file-16 gate, file-17 OOS proof rule, PLAN/RECOMMENDATION/CONFIRM behavior, execution model, OOS table, or promotion rule changed.

Local validation actually performed:

```text
php lint new/changed PHP files = PASS
watchlist:backtest-is-diagnose run 1 = PASS / exit code 0 / status=DONE
watchlist:backtest-is-diagnose run 2 = PASS / exit code 0 / status=DONE
WatchlistBacktestIsFailureDrilldown = PASS / 4 tests / 65 assertions
WatchlistBacktestC01 = PASS / 12 tests / 381 assertions
WatchlistBacktest = PASS / 134 tests / 2903 assertions
Full Watchlist = PASS / 226 tests / 3791 assertions
MarketData published/calendar/read-model filters = PASS
```

Priority contract status:

- `WL-CONTRACT-006`: PARTIAL; C01 scoring/runtime quality failed canonical IS gates, but feature-level drilldown is now runtime-derived for diagnostic review;
- `WL-CONTRACT-007`: DONE for C01 immutable traceability and failed-IS evidence scope, not `LOCKED`;
- `WL-CONTRACT-008`: DONE for C01 IS failure drilldown runtime diagnostic surface, feature-level buckets now runtime-derived, not `LOCKED`;
- `WL-CONTRACT-009`: DONE for no-OOS IS diagnostic runtime boundary proof, not `LOCKED`;
- `WL-CONTRACT-010`: DONE for C01 drilldown deterministic two-run proof, quality still fails and contract is not `LOCKED`;
- `WL-CONTRACT-011`: PARTIAL; risk/setup/scoring quality failed and root-cause focus is not proven;
- `WL-CONTRACT-013`: DONE for C01 drilldown artifact contract runtime shape;
- `WL-CONTRACT-014`: DONE for C01 drilldown docs synchronization scope;
- `WL-CONTRACT-015`: `PARTIAL / NOT_READY`.

No contract is `LOCKED`. C01 OOS-proof eligibility is `NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter`. Promotion remains `NOT_ELIGIBLE — OOS proof missing`.

## Status Rules

- `NOT_STARTED`: no implementation yet.
- `FOUNDATION_STARTED`: governance/docs baseline exists, but runtime implementation is not complete.
- `IN_PROGRESS`: implementation started but not finished.
- `PARTIAL`: some acceptance criteria met but not enough for lock.
- `DONE`: scope-specific work completed, not necessarily production readiness.
- `LOCKED`: implementation, tests, runtime proof, artifact evidence, and docs sync all valid.
- `BLOCKED`: cannot progress due to missing dependency or decision.
- `SUPERSEDED`: replaced by newer contract.

No contract may move to `LOCKED` only because documentation exists.

## Contract Summary

| Contract ID | Title | Status |
|---|---|---|
| WL-CONTRACT-001 | MARKET-DATA PUBLICATION READ CONTRACT | `PARTIAL` |
| WL-CONTRACT-002 | NO RAW MARKET-DATA BYPASS | `PARTIAL` |
| WL-CONTRACT-003 | NO MAX-DATE / LATEST SHORTCUT | `PARTIAL` |
| WL-CONTRACT-004 | INDICATOR VALIDITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-005 | ELIGIBILITY CONTRACT | `PARTIAL` |
| WL-CONTRACT-006 | SCORING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-007 | PARAMSET TRACEABILITY CONTRACT | `DONE for C02 immutable catalog identity + operator seed immutability evidence / NOT LOCKED` |
| WL-CONTRACT-008 | SIGNAL EXPLAINABILITY CONTRACT | `DONE for C02 design traceability + final forensic evidence / NOT LOCKED` |
| WL-CONTRACT-009 | BACKTEST NO-LOOKAHEAD CONTRACT | `DONE for C02 IS-only runtime no-OOS proof / NOT LOCKED` |
| WL-CONTRACT-010 | BACKTEST REPRODUCIBILITY CONTRACT | `DONE for C02 two-run deterministic artifact hash / NOT LOCKED` |
| WL-CONTRACT-011 | RISK GATE CONTRACT | `FAILED_STRATEGY_QUALITY for C02 / PARTIAL` |
| WL-CONTRACT-012 | PORTFOLIO AWARENESS BOUNDARY | `NOT_STARTED` |
| WL-CONTRACT-013 | AUDIT ARTIFACT CONTRACT | `DONE for C01 drilldown expanded artifact runtime scope / NOT LOCKED` |
| WL-CONTRACT-014 | DOCS SYNC CONTRACT | `DONE for C02 operator + forensic final docs sync scope` |
| WL-CONTRACT-015 | PRODUCTION READINESS CONTRACT | `PARTIAL / NOT_READY` |
| WL-CONTRACT-016 | PLAN GROUPING DETERMINISM CONTRACT | `PARTIAL` |
| WL-CONTRACT-017 | PLAN GROUP BOUNDARY CONTRACT | `PARTIAL` |
| WL-CONTRACT-018 | RECOMMENDATION PLAN-SOURCE CONTRACT | `PARTIAL` |
| WL-CONTRACT-019 | RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT | `PARTIAL` |

---

## WL-CONTRACT-001 — MARKET-DATA PUBLICATION READ CONTRACT

Contract ID:
`WL-CONTRACT-001`

Title:
`MARKET-DATA PUBLICATION READ CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/README.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`
- `docs/market_data/book/Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `docs/market_data/book/Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` downstream gated universe consumer
- `app/Application/MarketData/Services/MarketDataWatchlistReadService.php` upstream consumer read gateway
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream publication-scoped row source

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- upstream reference: `tests/Unit/MarketData/MarketDataWatchlistReadModelTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model exists and consumes the upstream market-data watchlist read surface.
- Candidate universe service consumes the Phase 1 read model and preserves pointer/publication metadata in gated rows.
- Contract is not `LOCKED` because there is no watchlist command/API runtime proof and no artifact/log output yet.
- Backtest foundation consumes upstream PLAN/recommendation/confirm services rather than raw market-data. Runtime command/API consumers have not been added yet.

Acceptance criteria:

- Watchlist reads market-data only from current readable publication pointer.
- Consumed publication is sealed, `SUCCESS`, `READABLE`, coverage `PASS`, and mirror-valid through upstream market-data readiness.
- Failure to resolve valid publication fails safe.
- No raw/staging/latest fallback exists in watchlist application code.
- Static guard covers the no-bypass constraint.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-002 — NO RAW MARKET-DATA BYPASS

Contract ID:
`WL-CONTRACT-002`

Title:
`NO RAW MARKET-DATA BYPASS`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/02_WS_MODULE_MAPPING.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` upstream hardened query boundary

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist application code currently has no direct DB read. Phase 2 candidate universe consumes `WatchlistMarketDataConsumerReadService` only.
- Static guard blocks `DB::table`, raw market-data table names, staging/latest/MAX(date) shortcuts in watchlist application code, including the candidate universe service.
- Contract is not `LOCKED` until future watchlist consumers are added and guarded by runtime proof.

Acceptance criteria:

- Watchlist does not directly consume raw provider response, staging tables, unsealed bars, unsealed indicators, or unsealed eligibility rows.
- Static guard rejects raw market-data bypass patterns in watchlist code.
- Any future repository/API/command must preserve this boundary or update the guard.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-003 — NO MAX-DATE / LATEST SHORTCUT

Contract ID:
`WL-CONTRACT-003`

Title:
`NO MAX-DATE / LATEST SHORTCUT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/implementation/weekly_swing/01_WS_IMPLEMENTATION_SCOPE_AND_BOUNDARY.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`
- `docs/market_data/book/Publication_Current_Pointer_Integrity_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Watchlist read model delegates date/publication resolution to market-data.
- Candidate universe service keeps the already-resolved `trade_date_effective`, `publication_id`, `publication_version`, and `run_id` from Phase 1 output.
- Static guard forbids `MAX(trade_date)`, `max('trade_date')`, `latest()`, `orderByDesc('trade_date')`, and descending date fallback in watchlist application code.
- Contract is not `LOCKED` until all future watchlist read consumers are added and covered by runtime proof.

Acceptance criteria:

- Date/effective publication resolution is owned by market-data current readable publication pointer.
- Watchlist code does not infer data freshness via `MAX(trade_date)`, `latest()`, descending date limit, or fallback to newest available raw row.
- Any future backtest/recommendation/API code must use the same resolved publication/effective date contract.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-004 — INDICATOR VALIDITY CONTRACT

Contract ID:
`WL-CONTRACT-004`

Title:
`INDICATOR VALIDITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- market-data indicator/readiness owner docs

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Required indicator fields are checked in watchlist service.
- Upstream market-data watchlist repository now filters `ind.is_valid = 1`, `invalid_reason_code IS NULL`, `indicator_set_version IS NOT NULL`, and required indicator fields non-null.
- Watchlist service still revalidates rows and excludes invalid/incomplete rows if they ever appear in the upstream payload.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- A ticker cannot become a watchlist candidate if required indicator values are null, missing, invalid, or flagged by invalid reason code.
- Required indicator list is explicit and guarded by tests.
- Invalid candidate rows are excluded with reason-coded evidence.

Last update:
`2026-05-28 — WATCHLIST — CANDIDATE UNIVERSE + LIQUIDITY/RISK FILTER EXECUTION SESSION`

---

## WL-CONTRACT-005 — ELIGIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-005`

Title:
`ELIGIBILITY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/03_WS_DATA_MODEL_MARIADB.md`
- `docs/watchlist/system/implementation/weekly_swing/05A_WS_CANONICAL_FIELD_MATRIX.md`
- `docs/market_data/book/EOD_Eligibility_Snapshot_Contract_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php`

Tests:

- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadServiceTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Upstream market-data watchlist repository returns `elig.eligible = 1` rows only and publication/run scopes eligibility to the resolved readable publication.
- Watchlist service rechecks `eligibility_state` and excludes any non-eligible row if the upstream payload is malformed.
- Contract is not `LOCKED` because no runtime command/API proof exists.

Acceptance criteria:

- Watchlist candidate universe contains only eligible tickers from the resolved market-data publication.
- Non-eligible rows are not silently accepted.
- Eligibility reason state remains traceable for downstream scoring/recommendation work.

Last update:
`2026-05-28 — WATCHLIST — MARKET-DATA CONSUMER READ MODEL EXECUTION SESSION`

---

## WL-CONTRACT-006 — SCORING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-006`

Title:
`SCORING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php` scoring input metric pass-through
- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php` ticker id pass-through
- `app/Infrastructure/Persistence/MarketData/MarketDataWatchlistReadRepository.php` ticker id read-surface pass-through

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Scoring engine foundation is baseline local PASS for Phase 3 unit/static scope.
- PLAN grouping foundation consumes scoring output and preserves deterministic sort keys for Phase 4 unit/static scope.
- `score_total` is deterministic `WEIGHTED_MEAN` over momentum, breakout, volume, and risk components.
- Component scores and total score are clamped to `0..1`.
- Ranking sort keys are deterministic: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- PLAN grouping deduplicates duplicate `ticker_id` by deterministic best item before active group assignment.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Same publication input + same paramset + same universe produces the same score and ranking.
- Tie-breaking is deterministic.
- Tests cover deterministic scoring output and deterministic PLAN grouping output.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-007 — PARAMSET TRACEABILITY CONTRACT

Contract ID:
`WL-CONTRACT-007`

Title:
`PARAMSET TRACEABILITY CONTRACT`

Status:
`DONE for published-price runtime paramset traceability scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/_shared/02_PARAMSET_CONTRACT_GLOBAL.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — final command artifacts carry resolved canonical eval thresholds, effective dynamic coverage threshold, policy/paramset snapshot, and deterministic hash; broader promotion/persistence governance remains outside this scope.`

Current gaps:

- Final closure note: final operator command proof resolved all required eval thresholds (`min_trades=120`, effective `min_days_covered=4` for the five-day window) and recorded them in the artifact; no unresolved-threshold export occurred.

- Candidate universe records canonical policy/paramset labels: `policy_code`, `policy_version`, and `paramset_code`.
- Scoring output records canonical policy/paramset labels plus `paramset_snapshot`.
- PLAN grouping output records canonical policy/paramset labels plus `paramset_snapshot.grouping`.
- Recommendation output records canonical policy/paramset labels plus `paramset_snapshot.recommendation` and `source_plan_reference`.
- Current bootstrap labels intentionally do not use `_V1` suffix because the watchlist application does not have formal app/runtime versioning yet.
- Candidate universe accepts nested `{ value: ... }` paramset shape for the gate fields it owns.
- Scoring accepts nested `{ value: ... }` weight shape for the fields it owns and rejects invalid weights.
- PLAN grouping accepts nested `{ value: ... }` grouping threshold/limit shape for the fields it owns and rejects invalid threshold/limit contracts.
- Recommendation accepts nested `{ value: ... }` recommendation threshold/limit shape for the fields it owns and rejects invalid recommendation threshold/limit contracts.
- Candidate universe rejects invalid ATR percent-point units above `1.0`.
- Scoring rejects candidate ATR unit drift above `1.0`.
- Full runtime paramset loader/validator, persistence, hash, promotion, and artifact recording are still not implemented.

Acceptance criteria:

- Every scoring/recommendation/backtest execution has traceable policy/paramset identity.
- Paramset validation rejects missing, unknown, invalid, or type-drifted fields.
- Artifact output records policy/paramset identity and hash when runtime artifacts are introduced.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-008 — SIGNAL EXPLAINABILITY CONTRACT

Contract ID:
`WL-CONTRACT-008`

Title:
`SIGNAL EXPLAINABILITY CONTRACT`

Status:
`DONE for published-price runtime explainability scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — official command diagnostics and artifacts explain publication lineage, zero-volume non-tradable entry/exit behavior, skipped evaluations, metrics, and validation state.`

Current gaps:

- Final closure note: final diagnostics proved BKDP `BT_SKIP_NO_TRADABLE_ENTRY` with `entry_volume=0` and KING `BT_SKIP_MISSING_OHLC_EXIT` with ignored zero-volume dates; no synthetic fill or zero return was created.

- PLAN scoring explainability exists via `score_components`, `score_weights`, `factor_breakdown`, and `reason_codes`.
- PLAN grouping explainability exists via `group_reason_code`, augmented `reason_codes`, `group_contract`, `paramset_snapshot.grouping`, and summary counts.
- Recommendation explainability exists via `recommendation_label`, `recommended_flag`, `recommendation_score`, `recommendation_rank`, `reason_codes`, `recommendation_contract`, `source_plan_reference`, and summary counts.
- Explainability reason codes used by scoring are traceable to Weekly Swing owner docs / reason seed.
- PLAN grouping reason codes `WS_PLAN_TOP_PICK`, `WS_PLAN_SECONDARY`, `WS_PLAN_WATCH_ONLY`, `WS_PLAN_AVOID_LOW_SCORE`, and `WS_PLAN_AVOID_EXCLUDED` are traceable to Weekly Swing reason-code docs / support seed.
- Recommendation reason codes `WS_REC_SELECTED`, `WS_REC_NOT_SELECTED`, `WS_REC_BORDERLINE`, `WS_REC_EMPTY_SET`, `WS_REC_RANK_OUTSIDE_DYNAMIC_TARGET`, `WS_REC_CAPITAL_AWARE`, `WS_REC_CAPITAL_INSUFFICIENT`, and `WS_REC_MIN_LOT_NOT_AFFORDABLE` are traceable to Weekly Swing owner docs / support seed.
- Contract is not `LOCKED` because official command/database runtime proof, current-patch PHPUnit execution, and production persisted artifact/log evidence remain incomplete.
- Confirm overlay output adds reason-coded `confirm_reason_codes` and preserves recommendation reason-code separation at unit/static scope.
- Backtest foundation output adds reason-coded diagnostics/evaluations, `source_contract`, `backtest_contract`, `paramset_snapshot`, `replay_window`, `summary`, and `artifact_manifest` at service + unit/static scope.
- Historical local PHPUnit baseline remains green: `WatchlistBacktest` 25/286, full Watchlist 116/1168, and `MarketDataWatchlistReadModelTest` 3/41. Current published-price tests are authored and lint-clean but were not executed because sandbox PHPUnit lacks required extensions.
- Published-price evidence now carries exact-date publication/run lineage, calendar/price manifests, evaluation reason codes, and deterministic artifact hash.

Acceptance criteria:

- Every signal/recommendation has explainable reason/factor output.
- Output includes enough factor breakdown to audit why a ticker is included, watched, avoided, or rejected.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-009 — BACKTEST NO-LOOKAHEAD CONTRACT

Contract ID:
`WL-CONTRACT-009`

Title:
`BACKTEST NO-LOOKAHEAD CONTRACT`

Status:
`DONE for published-price no-lookahead runtime proof scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/14_WS_BT_COVERAGE_MATRIX_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/17_WS_WALK_FORWARD_OOS_PROOF_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — strategy output is frozen before future-price reads, exact-date readable publications are used, future prices remain evaluation-only, and the final operator replay passed.`

Current gaps:

- Final closure note: final two-run proof used explicit replay dates and publication/calendar lineage; zero-volume handling remained evaluation-only and did not mutate PLAN, RECOMMENDATION, or CONFIRM.

- Backtest foundation service exists at unit/static scope and runtime artifact/metrics foundation now exists at service scope.
- Historical local PHPUnit baseline remains green. Current published-price regression tests were attempted but could not start because sandbox PHPUnit lacks `dom`, `mbstring`, `xml`, and `xmlwriter`.
- No-lookahead guard exists for future-effective source output.
- Controlled proof freezes and hashes PLAN/recommendation trade candidates before any D+1..D+5 price read; the post-read hash remains identical and future-effective strategy input fails closed.
- Service consumes existing PLAN/recommendation/confirm output layers and does not read raw market-data.
- Contract is not `LOCKED` because official command/database proof, current-patch PHPUnit regression, owner exit-model conflict resolution, production operating evidence, and OOS proof remain incomplete.

Acceptance criteria:

- Backtest never uses future publication, future indicator, future eligibility, future price, or future outcome to make historical decisions.
- Tests include lookahead guard cases.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-010 — BACKTEST REPRODUCIBILITY CONTRACT

Contract ID:
`WL-CONTRACT-010`

Title:
`BACKTEST REPRODUCIBILITY CONTRACT`

Status:
`DONE for published-price deterministic runtime reproducibility scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/12_WS_BACKTEST_SCHEMA_AND_CALIBRATION.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/20_WS_CANONICAL_PARAMSET_PROCEDURES.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — two final official command runs with identical canonical inputs produced canonical artifact hash `0eaa353d20df901c4f372c0000951408578bf302` in both runs.`

Current gaps:

- Final closure note: file SHA-1 differed only because output path/execution metadata are intentionally non-hashed; canonical hash equality passed.

- Explicit replay-window normalization and deterministic output ordering exist at service + unit/static scope.
- Source publication/run metadata is preserved in foundation output.
- Official artifact-manifest references are present.
- Runtime artifact service adds deterministic `input_manifest`, `validation.artifact_hash`, and JSON export foundation.
- Metrics service is deterministic for identical payload + explicit price/calendar input and fails safe when official inputs are missing.
- Historical local PHPUnit baseline remains green; current-patch PHPUnit is blocked before discovery by missing sandbox extensions.
- Controlled canonical hash equality is proven as `bb2268bbc053d7aa85fd5a400e834c519cfd3429` across two runs. Contract is not `LOCKED` because official command/database replay, current-patch PHPUnit, production persisted evidence, and OOS proof are not complete.

Acceptance criteria:

- Backtest can be replayed with the same dataset identity, publication scope, paramset identity, universe, date range, and artifact manifest.
- Replayed result matches expected metrics and output contract.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-011 — RISK GATE CONTRACT

Contract ID:
`WL-CONTRACT-011`

Title:
`RISK GATE CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/15_WS_UNIVERSE_EQUIVALENCE_CONTRACT_LOCKED.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistCandidateUniverseServiceTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringServiceTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Runtime risk/liquidity/volume gate exists at service + unit/static test level.
- Scoring risk/volume quality components now exist at service + unit/static scope.
- PLAN grouping consumes scored risk/volume-aware output without rewriting risk/liquidity formulas.
- Guards implemented: `dv20_idr >= min_dv20_idr`, `atr14_pct >= min_atr14_pct`, `atr14_pct <= max_atr14_pct`, `vol_ratio >= min_vol_ratio`.
- Canonical rejection reason priority implemented: `WS_DATA_MISSING`, `WS_LIQ_FAIL`, `WS_ATR_LOW`, `WS_ATR_HIGH`, `WS_VOLR_FAIL`.
- Explainable row output includes `required_ok`, `guard_ok`, `eligible_plan`, `canonical_fail_reason_code`, `reason_codes`, `missing_fields`, `gate_metrics`, and `gate_thresholds`.
- Scoring output includes risk factor breakdown and rejects ATR unit drift above `1.0`.
- PLAN grouping keeps low-score candidates in diagnostics `AVOID` and prevents scoring exclusions from entering active PLAN groups.
- Contract is not `LOCKED` because no command/API runtime proof, artifact output, backtest equivalence proof, or persisted universe snapshot exists yet.

Acceptance criteria:

- Watchlist does not rank only potential return.
- Candidate selection includes risk, liquidity, volatility, and guard failure handling.
- Risk gate output is explainable.
- Production PLAN universe and future backtest universe can compare pass/fail + reason using canonical fields.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-012 — PORTFOLIO AWARENESS BOUNDARY

Contract ID:
`WL-CONTRACT-012`

Title:
`PORTFOLIO AWARENESS BOUNDARY`

Status:
`NOT_STARTED`

Owner docs:

- `docs/watchlist/README.md`
- `docs/watchlist/audit/WATCHLIST_SCOPE_LOCK.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`

Implementation files:
`NOT_STARTED`

Tests:
`NOT_STARTED`

Runtime proof:
`NOT_STARTED`

Current gaps:

- No portfolio-aware integration exists.

Acceptance criteria:

- Portfolio integration does not alter market-data.
- Clear boundary exists between signal, position awareness, and execution decision.
- Watchlist remains suggestion-only and does not execute orders.

Last update:
`2026-05-28`

---

## WL-CONTRACT-013 — AUDIT ARTIFACT CONTRACT

Contract ID:
`WL-CONTRACT-013`

Title:
`AUDIT ARTIFACT CONTRACT`

Status:
`DONE for deterministic JSON runtime artifact evidence scope / NOT LOCKED`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/18_WS_BACKTEST_ARTIFACT_MANIFEST_LOCKED.md`
- `docs/watchlist/system/implementation/weekly_swing/03_WS_RUNTIME_ARTIFACT_FLOW.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`LOCAL_RUNTIME_PROOF_PASS — two official JSON artifacts were exported with calendar, price, publication, paramset, metrics, diagnostics, validation, and deterministic canonical hash evidence.`

Current gaps:

- Final closure note: production persisted artifact tables and production operating retention remain outside this proof scope; JSON evidence does not create a shadow official artifact.

- Backtest foundation output includes `artifact_manifest` with official Weekly Swing artifact names.
- Historical local PHPUnit baseline remains green; current-patch PHPUnit execution is blocked by missing sandbox extensions.
- Runtime artifact service now creates deterministic artifact shape, `input_manifest`, `metrics`, `validation`, `artifact_hash`, and JSON export foundation.
- Runtime production persistence remains explicitly `false`. A command surface now exists and is registered, but Artisan startup is blocked by the project PHP-version guard in this sandbox; controlled service artifacts are evidence only and do not become new official manifest artifacts.
- Contract is not `LOCKED` because official command/database runtime proof, current-patch PHPUnit, and persisted production runtime artifact/log evidence are not available.

Acceptance criteria:

- Every important watchlist run produces traceable artifact/log.
- Artifact records publication, paramset, universe, result, reason code/factor output, and validation status.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-014 — DOCS SYNC CONTRACT

Contract ID:
`WL-CONTRACT-014`

Title:
`DOCS SYNC CONTRACT`

Status:
`DONE for final published-price runtime proof docs sync scope`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/audit/README.md`
- `docs/watchlist/audit/WATCHLIST_OWNER_MATRIX.md`

Implementation files:

- `tests/Unit/Watchlist/WatchlistAuditGovernanceStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistMarketDataConsumerReadModelStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistCandidateUniverseStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistScoringStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policies/weekly_swing/07_WS_REASON_CODES_AND_HASH.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`
- `docs/watchlist/system/policies/weekly_swing/db/REASON_CODES_SEED.sql`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- `WatchlistAuditGovernanceStaticGuardTest` added for initial docs guard.
- `WatchlistMarketDataConsumerReadModelStaticGuardTest` added for Phase 1 docs/code synchronization guard.
- `WatchlistScoringStaticGuardTest` added for Phase 3 docs/code synchronization guard.
- `WatchlistPlanGroupingStaticGuardTest` added for Phase 4 docs/code synchronization guard.
- `WatchlistRecommendationStaticGuardTest` added for Phase 5 docs/code synchronization guard.
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`PASS — implementation status and contract tracker now record final PHPUnit, command, canonical hash, dynamic coverage, threshold, zero-volume diagnostic, remaining OOS gap, and `NOT_PRODUCTION_READY` status.`

Current gaps:

- Final closure note: earlier references to a required closure/coverage rerun are historical and superseded by the final closure update appended to both trackers.

- Phase 1 code/test/docs sync completed for market-data consumer read model.
- Phase 2 code/test/docs sync completed for candidate universe.
- Phase 3 code/test/docs sync completed for scoring foundation.
- Phase 4 code/test/docs sync completed for PLAN grouping foundation.
- Phase 5 code/test/docs sync completed for final recommendation foundation.
- Current docs synchronization scope is DONE. The contract remains not `LOCKED` because official command/database proof, current-patch PHPUnit, and production persistence/operating evidence remain incomplete.

- Docs sync foundation exists, but future code/config/schema/test/runtime changes still need ongoing enforcement.
- The command surface now exists and is documented; no API or production persistence surface was added. Official command execution is blocked by sandbox PHP `8.4.16`.
- Phase 6 confirm overlay service, tests, reason-code docs, and Lumen tracker/status docs are synchronized for unit/static scope.
- Phase 7 backtest strategy service, tests, static guard, and Lumen tracker/status docs are synchronized for unit/static scope.
- Runtime artifact/metrics docs, tests, and Lumen audit trackers are synchronized for unit/static scope.
- Historical local PHPUnit baseline remains green. Current patch has 17 lint-clean PHP files and zero grouped static validation failures; new PHPUnit tests remain unexecuted in this sandbox.

Acceptance criteria:

- Every watchlist code/config/schema/test/behavior change updates implementation status and contract tracker.
- Active session name is aligned between status and tracker.
- Tracker contracts reflect actual code/test/runtime status without overclaim.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-015 — PRODUCTION READINESS CONTRACT

Contract ID:
`WL-CONTRACT-015`

Title:
`PRODUCTION READINESS CONTRACT`

Status:
`PARTIAL / NOT_READY`

Owner docs:

- `docs/watchlist/audit/AUDIT_UPDATE_GOVERNANCE.md`
- `docs/watchlist/audit/LUMEN_IMPLEMENTATION_STATUS.md`
- `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
- `docs/watchlist/system/policy.md`
- `docs/watchlist/system/policies/weekly_swing/**`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistMarketDataConsumerReadService.php`
- `app/Application/Watchlist/Services/WatchlistCandidateUniverseService.php`
- `app/Application/Watchlist/Services/WatchlistScoringService.php`
- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestPublishedPriceRuntimeService.php`
- `app/Application/MarketData/Services/MarketDataTradingCalendarReadService.php`
- `app/Application/MarketData/Services/MarketDataPublishedEodSeriesReadService.php`
- `app/Infrastructure/Persistence/MarketData/MarketDataPublishedEodSeriesReadRepository.php`
- `app/Console/Commands/Watchlist/RunBacktestPublishedPriceProofCommand.php`

Tests:

- watchlist read model unit/static tests
- watchlist candidate universe unit/static tests
- watchlist scoring unit/static tests
- watchlist PLAN grouping unit/static tests
- watchlist recommendation unit/static tests
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceProofCommandTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestPublishedPriceRuntimeStaticGuardTest.php`
- `tests/Unit/MarketData/MarketDataTradingCalendarReadModelTest.php`
- `tests/Unit/MarketData/MarketDataPublishedEodSeriesReadModelTest.php`

Runtime proof:
`PARTIAL — published-price runtime proof, final PHPUnit, deterministic JSON artifacts, threshold binding, coverage, and zero-volume diagnostics pass; walk-forward/OOS and production operating proof remain unavailable.`

Current gaps:

- Final closure note: published-price runtime proof no longer blocks the next session, but no production-ready claim is allowed because OOS, production operations, and remaining contract lock evidence are incomplete.

- Read model, candidate universe, scoring foundation, PLAN grouping foundation, recommendation foundation, confirm overlay foundation, and backtest strategy foundation exist at unit/static/smoke scope.
- The proof command is implemented and registered without scheduler, but official Artisan/database execution is blocked in this sandbox; no API endpoint exists.
- Runtime-safe artifact shaping and JSON export foundation exist, but production persisted artifact/log evidence is not available.
- No API endpoint exists.
- Watchlist command surface `watchlist:backtest-published-price-proof` exists; no successful official command evidence is claimed.
- No production watchlist schema/migration exists.
- Backtest strategy, runtime artifact, and metrics foundations retain historical local PHPUnit proof. Published-price orchestration and controlled deterministic evidence now exist, but official integration-database command proof, production runtime persistence, and OOS proof do not.
- Core contracts are not `LOCKED` because official command/database runtime proof, current-patch PHPUnit, OOS proof, and production persisted artifact/log evidence are missing.
- Historical local validation remains PASS at 25/286, 116/1168, and 3/41. Current patch validation is limited to lint/static and controlled service smokes because PHPUnit/Artisan cannot start in this sandbox.
- Production readiness remains `NO`; no successful official command/database proof, API, OOS proof, production persistence, or production operating proof exists.

Acceptance criteria:

- Market-data consumer read model locked.
- No raw/latest/`MAX(date)` bypass.
- Required indicator and eligibility guards locked.
- Scoring deterministic and explainable.
- PLAN grouping deterministic and explainable.
- Paramset identity traceable.
- Recommendation output tested.
- Recommendation source is PLAN-only and empty recommendation is valid.
- Backtest no-lookahead and reproducibility have unit/static proof; runtime replay/artifact proof is still required before lock.
- Risk gates present.
- Artifact/log proof present.
- Full watchlist test suite passes.
- Runtime command/API proof passes.
- Docs sync complete.

Last update:
`2026-06-09 — WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF FINAL CLOSURE`

---

## WL-CONTRACT-016 — PLAN GROUPING DETERMINISM CONTRACT

Contract ID:
`WL-CONTRACT-016`

Title:
`PLAN GROUPING DETERMINISM CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/04_WS_PARAMSET_JSON_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/05_WS_PARAMETER_REGISTRY_COMPLETE.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- upstream source: `app/Application/Watchlist/Services/WatchlistScoringService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- PLAN grouping service exists for Phase 4 unit/static scope.
- `TOP_PICKS`, `SECONDARY`, `WATCH_ONLY`, and diagnostics `AVOID` are formed deterministically from Phase 3 scored output.
- Default bootstrap thresholds and limits are validated: top `0.70/5`, secondary `0.55/10`, watch-only `0.40/20`, avoid below `0.40`.
- Sort keys follow Phase 3 scoring contract: `score_total_desc`, `score_breakout_desc`, `score_momentum_desc`, `dv20_idr_desc`, `atr14_pct_asc`, `ticker_id_asc`.
- Duplicate `ticker_id` is resolved by deterministic best item.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- Same scored input + same grouping paramset produces identical PLAN groups.
- Active PLAN groups do not depend on input array order.
- Duplicate ticker IDs do not enter more than one active PLAN group.
- Overflow from TOP_PICKS and SECONDARY follows deterministic threshold/limit rules.

Last update:
`2026-06-05 — WATCHLIST — PLAN GROUPING + TOP_PICKS / SECONDARY SELECTION EXECUTION SESSION`

---

## WL-CONTRACT-017 — PLAN GROUP BOUNDARY CONTRACT

Contract ID:
`WL-CONTRACT-017`

Title:
`PLAN GROUP BOUNDARY CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/01_WS_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `docs/watchlist/system/policies/weekly_swing/08_WS_PLAN_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/09_WS_DYNAMIC_SELECTION_DETERMINISTIC.md`
- `docs/watchlist/system/policies/weekly_swing/10_WS_CONFIRM_OVERLAY.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistPlanGroupingServiceTest.php`
- `tests/Unit/Watchlist/WatchlistPlanGroupingStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Confirm overlay foundation now consumes active PLAN candidate binding from `WatchlistPlanGroupingService`.
- Recommended PLAN candidates and non-recommended active PLAN candidates can receive CONFIRM overlay.
- Unknown/non-active candidate evidence is rejected into diagnostics/excluded output.
- Confirm overlay does not mutate recommendation membership, rank, score, label, or hash at unit/static scope.
- Contract is not `LOCKED` because there is no command/API runtime proof and no artifact/log output yet.

Acceptance criteria:

- PLAN grouping consumes `WatchlistScoringService` only.
- PLAN grouping does not create recommendation labels, confirm state, order/execution actions, portfolio allocation, or backtest metrics.
- `AVOID` remains diagnostics and must not be interpreted as sell recommendation or execution instruction.
- Recommendation layer must consume PLAN grouping output without mutating PLAN group membership.
- Confirm overlay binds to candidate PLAN without mutating recommendation membership, rank, score, label, or hash.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`

---

## WL-CONTRACT-018 — RECOMMENDATION PLAN-SOURCE CONTRACT

Contract ID:
`WL-CONTRACT-018`

Title:
`RECOMMENDATION PLAN-SOURCE CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/01_WS_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/02_WS_CANONICAL_RUNTIME_FLOW.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- upstream source: `app/Application/Watchlist/Services/WatchlistPlanGroupingService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Recommendation service still does not read CONFIRM output.
- Confirm overlay consumes recommendation output only as immutable membership snapshot after recommendation has already been produced from PLAN.
- Confirm overlay does not add ticker into recommendation membership and does not remove ticker from recommendation membership.
- Confirm overlay preserves source PLAN trade date, publication, run, policy, and paramset identity in output.
- Contract is not `LOCKED` because there is no command/API runtime proof and no persisted artifact/log output yet.

Acceptance criteria:

- Recommendation output never adds ticker from outside PLAN source groups.
- Recommendation can be produced without CONFIRM.
- CONFIRM fields do not become source inputs for recommendation.
- Recommendation metadata preserves source PLAN trade date, publication, run, policy, and paramset identity.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`

---

## WL-CONTRACT-019 — RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT

Contract ID:
`WL-CONTRACT-019`

Title:
`RECOMMENDATION DETERMINISM AND EMPTY-SET CONTRACT`

Status:
`PARTIAL`

Owner docs:

- `docs/watchlist/system/policies/weekly_swing/13_WS_CONTRACT_TEST_CHECKLIST.md`
- `docs/watchlist/system/policies/weekly_swing/22_WS_RECOMMENDATION_OVERVIEW.md`
- `docs/watchlist/system/policies/weekly_swing/23_WS_RECOMMENDATION_INPUT_OUTPUT_CONTRACT.md`
- `docs/watchlist/system/policies/weekly_swing/24_WS_RECOMMENDATION_ALGORITHM.md`
- `docs/watchlist/system/policies/weekly_swing/25_WS_RECOMMENDATION_REASON_CODES_AND_TESTS.md`

Implementation files:

- `app/Application/Watchlist/Services/WatchlistRecommendationService.php`
- `app/Application/Watchlist/Services/WatchlistConfirmOverlayService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestStrategyService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistRecommendationServiceTest.php`
- `tests/Unit/Watchlist/WatchlistRecommendationStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayServiceTest.php`
- `tests/Unit/Watchlist/WatchlistConfirmOverlayStaticGuardTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestStrategyStaticGuardTest.php`

Runtime proof:
`NOT_STARTED` — no watchlist command/API exists yet.

Current gaps:

- Recommendation deterministic and empty-set behavior remains owned by `WatchlistRecommendationService`.
- Confirm overlay foundation proves confirm evidence does not mutate `recommended_flag`, `recommendation_rank`, `recommendation_score`, `recommendation_label`, or available hash fields.
- Empty recommendation does not block CONFIRM eligibility for active PLAN candidates when PLAN candidates exist.
- Contract is not `LOCKED` because there is no command/API runtime proof, artifact hash, or persisted replay evidence yet.

Acceptance criteria:

- Same PLAN output + same recommendation paramset + same capital input produces identical recommendation output.
- Empty recommendation is a valid output, not an error.
- Dynamic recommendation count is algorithmic and may be zero.
- Capital-aware replay is deterministic for identical explicit capital input.

Last update:
`2026-06-05 — WATCHLIST — CONFIRM OVERLAY FOUNDATION EXECUTION SESSION`


## Phase 7 Local Validation Update — 2026-06-08

Session:
`WATCHLIST — BACKTEST STRATEGY ENGINE FOUNDATION EXECUTION SESSION`

Status:
`PHASE_7_BACKTEST_STRATEGY_ENGINE_FOUNDATION_DONE / NOT_PRODUCTION_READY`.

Evidence:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktestStrategy"
OK (13 tests, 152 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (104 tests, 1034 assertions)
```

Contract impact:

- `WL-CONTRACT-008` is DONE for unit/static explainability scope.
- `WL-CONTRACT-009` is DONE for unit/static no-lookahead foundation scope.
- `WL-CONTRACT-010` is DONE for unit/static reproducibility foundation scope.
- `WL-CONTRACT-013` is DONE for unit/static artifact-manifest foundation scope.
- `WL-CONTRACT-014` is DONE for Phase 7 docs sync unit/static scope.
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.

No Phase 7 contract is moved to `LOCKED` because command/API runtime proof, persisted artifact/log evidence, completed pricing metric engine, production schema, and walk-forward/OOS proof do not exist yet.



## Runtime Artifact and Metrics Contract Update — 2026-06-08

Session:
`WATCHLIST — BACKTEST RUNTIME ARTIFACT AND METRICS EXECUTION SESSION`

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / NOT_PRODUCTION_READY`.

Priority contract impact:

- `WL-CONTRACT-008`: explainability extended through artifact diagnostics, metric diagnostics, and reason-code distribution.
- `WL-CONTRACT-009`: no-lookahead boundary preserved; metrics only uses explicit replay trade dates, explicit calendar input, and published EOD price series input.
- `WL-CONTRACT-010`: reproducibility improved with deterministic artifact hash, source payload hash, stable JSON encoding, and deterministic metrics aggregation.
- `WL-CONTRACT-013`: runtime artifact shape now exists at service level with official manifest references and JSON export foundation.
- `WL-CONTRACT-014`: audit docs synchronized for runtime artifact and metrics foundation.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no production-ready claim.

Implementation files:

- `app/Application/Watchlist/Services/WatchlistBacktestRuntimeArtifactService.php`
- `app/Application/Watchlist/Services/WatchlistBacktestMetricsService.php`

Tests:

- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestMetricsServiceTest.php`
- `tests/Unit/Watchlist/WatchlistBacktestRuntimeArtifactStaticGuardTest.php`

Internal diagnostics added for backtest artifact/metrics scope only:

- `WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE`
- `WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE`
- `WATCHLIST_BACKTEST_EVALUATION_SKIPPED_NO_PUBLISHED_PRICE`
- `WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_READY_WITH_EVALUATION_SKIPPED`

No contract is moved to `LOCKED` because command/API runtime proof, production persisted artifact/log evidence, production schema, and walk-forward/OOS proof are still missing.

## Runtime Artifact and Metrics Local Validation Update — 2026-06-09

Status:
`DONE for runtime artifact and metrics foundation unit/static scope / LOCAL_PHPUNIT_PASS / NOT_PRODUCTION_READY`.

Evidence:

```text
vendor\bin\phpunit tests\Unit\Watchlist --filter "WatchlistBacktest"
OK (25 tests, 286 assertions)

vendor\bin\phpunit tests\Unit\Watchlist
OK (116 tests, 1168 assertions)

vendor\bin\phpunit tests\Unit\MarketData --filter "MarketDataWatchlistReadModelTest"
OK (3 tests, 41 assertions)
```

Contract impact:

- `WL-CONTRACT-008`, `WL-CONTRACT-009`, `WL-CONTRACT-010`, `WL-CONTRACT-013`, and `WL-CONTRACT-014` retain DONE status for the current unit/static foundation scope with completed local PHPUnit proof.
- `WL-CONTRACT-015` remains `PARTIAL / NOT_READY`.
- The current local test requirement is satisfied; remaining blockers are command/API runtime proof, published-price production replay evidence, production persisted artifact/log evidence, production schema where required, and walk-forward/OOS proof.
- No contract is promoted to `LOCKED`.

## Next Required Contract Work

Next session must target:

`WATCHLIST — WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION`

Priority contracts:

1. `WL-CONTRACT-006`
2. `WL-CONTRACT-007`
3. `WL-CONTRACT-008`
4. `WL-CONTRACT-011`
5. `WL-CONTRACT-010`
6. `WL-CONTRACT-014`
7. `WL-CONTRACT-015`

Required proof:

- preserve R1 rows, artifacts, hashes, and failed metrics without overwrite or reinterpretation;
- preserve R2 rows, artifacts, hashes, and failed metrics without overwrite or reinterpretation;
- preserve C01 rows, artifacts, hashes, and failed metrics without overwrite or reinterpretation;
- treat C01 failure reason `WS_BT_C01_NO_VALID_IS_CANDIDATE` as failed IS quality evidence, not as OOS evidence;
- diagnose why C01 still failed `WS_BT_EVAL_DOWNSIDE_FAIL`, `WS_BT_EVAL_ROBUST_RETURN_FAIL`, and `WS_BT_EVAL_STABILITY_FAIL`;
- decide whether the next semantic catalog remains in the same focus as `WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06` or starts a new focus as `WS_BT_GRID_<NEW_FOCUS>_C01_YYYY_MM`;
- use IS evidence only; do not read or use reserved OOS to choose variables, values, ranking, or acceptance;
- keep canonical sufficiency, return, downside, and stability gates unchanged;
- retain exact official publication/calendar/OHLCV reads and corrected execution-price semantics;
- prove catalog determinism, cross-field validity, stable ordering, idempotent persistence, no best-of-failed behavior, and no mutation after first runtime;
- OOS may execute only after at least one future semantic catalog row passes every IS gate and an immutable best-IS binding is frozen;
- keep promotion, portfolio, broker, scheduler, API, and production-ready claims out of scope;
- retain `WL-CONTRACT-015` as `PARTIAL / NOT_READY`.

Naming rule:

```text
R3/R4/R5 naming is forbidden for new catalog identity.
C01 already refers to executed DOWNSIDE_STABILITY failed-IS evidence.
If the same focus continues, use WS_BT_GRID_DOWNSIDE_STABILITY_C02_2026_06.
If focus changes, use WS_BT_GRID_<NEW_FOCUS>_C01_YYYY_MM.
```

## Published Price Runtime Contract Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Evidence:

- official calendar read surface: `MarketDataTradingCalendarReadService` over `MarketCalendarRepository`;
- official exact-date price surface: `MarketDataPublishedEodSeriesReadService` over `MarketDataReadinessService` and `MarketDataPublishedEodSeriesReadRepository`;
- watchlist orchestration: `WatchlistBacktestPublishedPriceRuntimeService`;
- command: `RunBacktestPublishedPriceProofCommand`, registered in `app/Console/Kernel.php` without scheduler;
- runtime artifact adds `calendar_manifest`, `price_series_manifest`, `publication_manifest`, and `runtime_execution` while retaining official manifest names;
- canonical metric fields from file 16 are mapped and separated from derived/report metrics and diagnostic counters;
- controlled service runtime proof passed 25 assertions and produced equal canonical hashes `bb2268bbc053d7aa85fd5a400e834c519cfd3429` across two runs;
- controlled market-data read-surface proof passed 21 assertions; strategy paramset snapshot and command argument fail-safe smokes each passed 4 assertions;
- all 17 changed/new PHP files pass lint and grouped static validation has 0 failures;
- official command/database proof is blocked by sandbox PHP `8.4.16` versus project requirement PHP `< 8.4`; command attempt exits `2` and writes no artifact;
- all requested PHPUnit commands were attempted but exit `1` before discovery because `dom`, `mbstring`, `xml`, and `xmlwriter` are missing; no current-patch PHPUnit PASS is claimed.

Contract impact:

- `WL-CONTRACT-008`: published-price evaluations and diagnostics now include price/publication lineage; official command proof remains missing.
- `WL-CONTRACT-009`: strategy output is hashed/frozen before future-price reads; future price is evaluation-only; missing/future-effective inputs fail closed in controlled proof.
- `WL-CONTRACT-010`: canonical artifact hash excludes volatile execution timestamp/path and is reproducible across identical inputs.
- `WL-CONTRACT-013`: deterministic JSON evidence is exported at service level with official artifact references; official command/database evidence remains blocked.
- `WL-CONTRACT-014`: active session, implementation status, contract tracker, files, validation, blockers, and next work are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no OOS, production operating proof, production schema/persistence claim, or production-ready claim exists.

Historical pre-closure blockers (superseded by the closure update below):

- official command and PHPUnit evidence are now available for the pre-closure build;
- file 12/file 16 wording conflict is resolved by the closure patch;
- current closure-patch PHPUnit and two-run artifact proof remain required;
- walk-forward/OOS proof and production operating proof remain outstanding.

No contract is promoted to `LOCKED`.



## Published Price Runtime Proof and Closure Contract Update — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Operator evidence:

- `WatchlistBacktestPublishedPrice`: PASS, 13 tests / 87 assertions;
- `WatchlistBacktest`: PASS, 39 tests / 375 assertions;
- full Watchlist: PASS, 130 tests / 1257 assertions;
- `MarketDataPublishedEodSeries`: PASS, 6 tests / 29 assertions after correcting historical-row fixture placement;
- `MarketDataTradingCalendar`: PASS, 4 tests / 16 assertions;
- existing MarketData watchlist read model: PASS, 3 tests / 41 assertions;
- command replay `2026-05-21` through `2026-05-29`: PASS twice;
- calendar coverage 10 dates, required/resolved published-price dates 9/9, evaluated trades 13;
- canonical artifact hash both runs: `03dce5cbd7176a6065dc711e0d9907a2279f9cc3`;
- publication lineage: 10/10 current readable sealed dates through `2026-06-08`.

Observed diagnostics:

- KING: no executable exit after positive-volume entry;
- BKDP: D+1 published row had equal OHLC and zero volume and therefore must be treated as no tradable entry.

Closure-patch controlled validation:

- all 9 changed PHP source/test files pass lint;
- grouped safety/parity validation passes 20 assertions;
- zero-volume and effective-threshold metrics harness passes 12 assertions;
- controlled runtime orchestration passes 10 assertions with equal canonical hash `e2d725378e6df67ffa579017fdbb2399e8bdc322` across two runs;
- these controlled results do not replace the required operator PHPUnit/database command rerun.

Closure impact:

- `WL-CONTRACT-007`: paramset traceability improved; required eval thresholds are carried to `paramset_snapshot`, configured/effective coverage thresholds are explicit, and unresolved thresholds block export.
- `WL-CONTRACT-008`: explainability improved with `BT_SKIP_NO_TRADABLE_ENTRY`, `BT_SKIP_NO_TRADABLE_EXIT`, volumes, and ignored non-tradable dates.
- `WL-CONTRACT-009`: future price remains evaluation-only after immutable trade-candidate freeze; zero-volume handling does not feed PLAN/RECOMMENDATION/CONFIRM.
- `WL-CONTRACT-010`: prior official canonical hash equality passed; closure-patch deterministic rerun remains required because semantics and hashed paramset metadata changed.
- `WL-CONTRACT-013`: official pre-closure command artifacts exist; closure-patch artifact export must be regenerated.
- `WL-CONTRACT-014`: owner docs, reason dictionary, SQL seed, audit status, and contract tracker are synchronized; file 12/file 16 exit-model wording conflict is resolved in favor of file 12 canonical rule-based execution.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no OOS or production operating proof exists.

No contract is promoted to `LOCKED`.

Next required work inside the same session:

1. rerun closure-patch PHPUnit scopes;
2. run the command twice on `2026-05-21` through `2026-05-29` using new output files;
3. prove `metric_thresholds_resolved=1`;
4. verify BKDP becomes `BT_SKIP_NO_TRADABLE_ENTRY` and KING records zero-volume dates without synthetic exit;
5. prove the two new canonical artifact hashes are equal;
6. only then close this session and select walk-forward/OOS as the next session.

## Published Price Runtime Proof Final Contract Closure — 2026-06-09

Session:
`WATCHLIST — BACKTEST PUBLISHED PRICE SERIES RUNTIME PROOF EXECUTION SESSION`

Final session status:
`DONE for published price series runtime proof scope / LOCAL_RUNTIME_PROOF_PASS / NOT_PRODUCTION_READY`.

### Final evidence

```text
PublishedPrice PHPUnit: 17 tests / 146 assertions / PASS
MetricsService PHPUnit: 8 tests / 63 assertions / PASS
Backtest PHPUnit: 48 tests / 497 assertions / PASS
Full Watchlist PHPUnit: 139 tests / 1379 assertions / PASS
PublishedEodSeries PHPUnit: 6 tests / 29 assertions / PASS
TradingCalendar PHPUnit: 4 tests / 16 assertions / PASS
MarketDataWatchlistReadModel PHPUnit: 3 tests / 41 assertions / PASS

replay range: 2026-05-21 through 2026-05-29
command runs: 2 / PASS
calendar dates: 10
required/resolved price dates: 9/9
evaluated trades: 13
diagnostics: 2
thresholds resolved: true
min_trades: 120
effective min_days_covered: 4
days_covered / total window: 5/5
minimum coverage gate: true
metric calibration valid: false (expected: 13 < 120)
canonical artifact hash run 1: 0eaa353d20df901c4f372c0000951408578bf302
canonical artifact hash run 2: 0eaa353d20df901c4f372c0000951408578bf302
canonical hash equality: true
```

Final diagnostics:

- KING: `BT_SKIP_MISSING_OHLC_EXIT`; zero-volume dates `2026-05-25`, `2026-05-26`, and `2026-05-29` were ignored and recorded; no synthetic exit.
- BKDP: `BT_SKIP_NO_TRADABLE_ENTRY`; `entry_volume=0`; no position was opened.

### Final contract impact

- `WL-CONTRACT-007`: DONE for published-price runtime paramset traceability scope; not `LOCKED`.
- `WL-CONTRACT-008`: DONE for published-price runtime explainability scope; not `LOCKED`.
- `WL-CONTRACT-009`: DONE for published-price no-lookahead runtime proof scope; not `LOCKED`.
- `WL-CONTRACT-010`: DONE for published-price deterministic runtime reproducibility scope; not `LOCKED`.
- `WL-CONTRACT-013`: DONE for deterministic JSON runtime artifact evidence scope; not `LOCKED`.
- `WL-CONTRACT-014`: DONE for final docs synchronization scope.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`.

No contract is promoted to `LOCKED`. The completed published-price runtime proof is sufficient to begin the next backtest-proof session, but it is not sufficient for production readiness.

Earlier statements in this tracker that current closure/coverage PHPUnit or command reruns are still required are historical and superseded by this final closure section.

Next required session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`.

## Walk-Forward/OOS Unit-Static Contract Update — 2026-06-09

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Status:
`DONE for walk-forward/OOS implementation unit-static scope / LOCAL_SMOKE_PASS / OFFICIAL_RUNTIME_PROOF_BLOCKED / NOT_PRODUCTION_READY`.

### Contract decisions synchronized before implementation

- chronological split rule: `is_count=floor(0.70*N)` and OOS receives the full remainder;
- IS is the exact ordered prefix and OOS is the exact ordered suffix, with no overlap or hidden gap;
- final calibration tie-break: smallest `param_id` after all four canonical rank metrics tie;
- OOS minimum trade gate: `picks_count_oos >= ws.eval.min_trades_oos`, default `40`;
- OOS fixture acceptance keys now match file 17 only;
- official OOS row binds to the selected `watchlist_bt_eval` through `is_eval_id`.

### Implementation evidence by contract

- `WL-CONTRACT-007`: DB grid rows are snapshotted and hashed; the selected IS eval id, param id, paramset, metrics, eval model, calendar, price, and publication hashes form one immutable binding before OOS begins.
- `WL-CONTRACT-008`: reason-coded failures exist for missing proof, insufficient OOS window, return failure, stability failure, and downside failure; incomplete canonical metrics fail closed instead of persisting zeros.
- `WL-CONTRACT-009`: calibration method input is limited to IS dates/options; OOS metrics are not an accepted input; one frozen binding is evaluated after selection; controlled mutation of OOS outcomes does not alter the IS selection/hash.
- `WL-CONTRACT-010`: split/date/grid/binding/evaluation hashes are deterministic; artifact hash excludes generated timestamp and operational INSERTED/IDEMPOTENT status; controlled identical rerun hash equality passed.
- `WL-CONTRACT-013`: official repositories target `watchlist_bt_param_grid`, `watchlist_bt_eval`, and `watchlist_bt_oos_eval_ws`; duplicate payload conflict fails closed; evidence sections are `split_manifest`, `is_calibration`, `best_is_binding`, `oos_evaluation`, `oos_acceptance`, and `persistence_manifest`.
- `WL-CONTRACT-014`: owner docs, DDL, promotion guard, fixture, implementation tracker, and this contract tracker are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; OOS supported-runtime proof and production operating proof are absent.

### Validation and blocker evidence

```text
changed/new PHP lint: PASS
controlled OOS smoke: 35 assertions / PASS
controlled quantile smoke: 6 assertions / PASS
new OOS PHPUnit source: 20 methods / 118 assertion-expectation call sites
Artisan OOS run 1: exit 2 before bootstrap / unsupported PHP 8.4.16 / no artifact
Artisan OOS run 2: exit 2 before bootstrap / unsupported PHP 8.4.16 / no artifact
requested PHPUnit scopes: exit 1 before discovery / missing dom, mbstring, xml, xmlwriter
```

The controlled smoke does not satisfy official runtime proof. Therefore:

```text
LOCAL_OOS_PROOF_PASS: not claimed
OOS_ACCEPTANCE_FAIL: not claimed because OOS runtime did not execute
Promotion eligibility: NOT_ELIGIBLE — OOS proof missing
Production ready: NO
```

No contract is promoted to `LOCKED`.


## OOS Runtime Gap-Closure Contract Update — 2026-06-09

Status:
`DONE for OOS runtime gap-closure implementation unit/static scope / OPERATOR_RERUN_REQUIRED / NOT_PRODUCTION_READY`.

Contract impact:

- `WL-CONTRACT-007`: canonical grid now includes stop ATR multiplier and minimum RR, and the immutable binding hashes the exact runtime snapshot.
- `WL-CONTRACT-008`: failed IS evaluations expose aggregate gates plus deterministic worst/best trade evidence rather than a misleading zero best-binding summary.
- `WL-CONTRACT-009`: exact date/ticker price reads occur only after candidates are frozen; OOS remains excluded from grid selection.
- `WL-CONTRACT-010`: volatile DB `created_at` is excluded from canonical grid payload; one proof remains one explicit chronological window even when reads are internally bounded.
- `WL-CONTRACT-013`: schema, migrations, canonical seed, eval identity, grid/eval/OOS repositories, and JSON proof sections are synchronized. Historical unversioned IS evidence is preserved using explicit legacy identity markers.
- `WL-CONTRACT-014`: policy, implementation guidance, DDL, SQL seed, migrations, tests, and audit trackers are synchronized for the closure patch.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; corrected supported-runtime OOS acceptance proof is still required.

Operator pre-patch evidence preserved:

```text
Full Watchlist: 162 tests / 1519 assertions / PASS
Backtest: 70 tests / 631 assertions / PASS
chronological split: PASS
single baseline IS calibration: executed
single baseline valid IS candidates: 0
OOS: not executed
```

No contract is promoted to `LOCKED`. Promotion remains `NOT_ELIGIBLE — corrected OOS proof missing`.

## OOS Post-Deployment Regression Contract Correction — 2026-06-10

Operator execution proved the 24-row canonical database seed and param-grid catalog tests pass, then exposed three parity regressions: stale static-guard cardinality `18`, missing strategy bootstrap ATR/RR defaults, and runtime metadata not rebound onto returned strategy payloads before freeze.

Contract impact:

- `WL-CONTRACT-007`: parameter traceability now uses one cardinality source (`CATALOG_COUNT=24`), exact persisted-set validation, and non-null bootstrap risk defaults.
- `WL-CONTRACT-008`: trade candidates and artifacts consistently expose ATR/RR and exact published-price runtime semantics.
- `WL-CONTRACT-009`: runtime metadata binding occurs before the frozen strategy hash and before future-price access.
- `WL-CONTRACT-010`: catalog/SQL/test cardinality parity no longer depends on duplicated literals; deterministic payload hashing includes the bound runtime metadata.
- `WL-CONTRACT-013`: persisted grid extras/missing rows fail closed with `WS_BT_PARAM_GRID_PERSISTED_SET_MISMATCH`.
- `WL-CONTRACT-014`: owner contract, implementation guidance, tests, and trackers are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY` pending supported operator PHPUnit and OOS rerun.

No contract is promoted to `LOCKED`. Promotion remains `NOT_ELIGIBLE — corrected OOS proof missing`.


## OOS Grid Cross-Field Paramset Contract Correction — 2026-06-10

Operator full-window execution proved the memory-safe runtime and 24-row grid load, but aggregate IS failures included `WATCHLIST_BACKTEST_SOURCE_NOT_READY`. The cause was a row-projection defect: strict `max_atr14_pct` values were merged with a wider default ideal ATR band.

Contract impact:

- `WL-CONTRACT-007`: immutable paramset binding now includes deterministic `bt_grid_resolution` companion values and rule marker.
- `WL-CONTRACT-008`: strict canonical rows may no longer fail as source-not-ready solely due to contradictory default ATR companion values.
- `WL-CONTRACT-009`: companion-band projection is completed before replay and uses no OOS metrics or future prices.
- `WL-CONTRACT-010`: all 24 catalog rows are covered by deterministic cross-field invariants.
- `WL-CONTRACT-014`: policy, implementation guidance, checklist, tests, and trackers are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; corrected full-window rerun and actual IS/OOS result are still required.

No metric acceptance threshold was weakened. No best-of-failed selection or promotion is allowed.

## Execution-Price Corrected Full-Range R1 IS Contract Result — 2026-06-10

Session:
`WATCHLIST — WALK-FORWARD AND OUT-OF-SAMPLE PROOF EXECUTION SESSION`

Final status:
`FULL_IS_CALIBRATION_EXECUTED / R1_GRID_FAILED_IS_QUALITY / OOS_NOT_EXECUTED / NOT_PRODUCTION_READY`.

### Evidence

```text
ParamGrid: 4 tests / 636 assertions / PASS
MetricsService: 15 tests / 113 assertions / PASS
PublishedPrice: 18 tests / 177 assertions / PASS
OOS: 24 tests / 186 assertions / PASS
Backtest: 87 tests / 1430 assertions / PASS
Full Watchlist: 179 tests / 2318 assertions / PASS

IS window: 2023-01-02 through 2025-05-21 / 562 trading dates
Reserved OOS window: 2025-05-22 through 2026-05-29 / 242 trading dates
Canonical grid rows: 24
Valid IS rows: 0
Failed IS rows: 24
Maximum evaluated picks: 1445
Maximum days covered: 513
Canonical artifact hash: f4ec8464f08515b31d7d26636851acea930307d6
```

### Contract impact

- `WL-CONTRACT-006`: deterministic scoring/runtime execution is proven, but R1 entry quality is insufficient; remains `PARTIAL`.
- `WL-CONTRACT-007`: all R1 param snapshots and grid identities are traceable through full IS runtime; DONE for this scope, not `LOCKED`.
- `WL-CONTRACT-008`: trade-level trigger/executed-price evidence and aggregate gate failures are explainable; DONE for corrected IS evidence scope, not `LOCKED`.
- `WL-CONTRACT-009`: IS-only calibration and no best-of-failed behavior are proven in supported runtime; OOS no-retune runtime proof remains absent.
- `WL-CONTRACT-010`: one deterministic corrected artifact exists; contract remains `PARTIAL` because no OOS artifact/hash pair exists.
- `WL-CONTRACT-011`: execution risk rules are validated, but every R1 row fails at least one canonical quality gate; remains `PARTIAL`.
- `WL-CONTRACT-013`: official IS failure evidence exists; OOS evidence is correctly absent.
- `WL-CONTRACT-014`: final R1 result, validation, artifact hash, and next-session boundary are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`.

No contract is promoted to `LOCKED`. No acceptance gate was weakened. No OOS data was used for R1 selection. Promotion remains `NOT_ELIGIBLE — OOS proof missing`.

Next required work:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION SESSION`.

## R2 Entry-Quality Calibration Contract Update — 2026-06-10

Session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

Status:
`DONE for R2 implementation unit-static scope / OPERATOR_R2_IS_RERUN_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Contract impact:

- `WL-CONTRACT-006`: R2 adds curated entry-quality scoring/grouping axes only; canonical gates are unchanged. Runtime quality remains unproven.
- `WL-CONTRACT-007`: R1/R2 catalog identity, row identity, catalog hash, explicit paramset projection, and fixed execution snapshot are traceable and deterministic at implementation scope.
- `WL-CONTRACT-008`: new fail-closed reason codes cover missing/invalid/conflicting catalog, persisted-set mismatch, no valid IS row, exact-window/boundary violations, R1 mutation, OOS-table mutation, and eval identity conflict.
- `WL-CONTRACT-009`: the R2 orchestration accepts only the exact historical IS window, passes a hard market-data boundary, censors the final HOLD=5 entry dates, has no OOS service/repository dependency, and cannot select best-of-failed.
- `WL-CONTRACT-010`: catalog/hash/date/evaluation/binding/artifact determinism is implemented; supported two-run proof remains required.
- `WL-CONTRACT-011`: stop ATR, RR, fee, slippage, gap, price-band, and holding semantics are fixed across all R2 rows.
- `WL-CONTRACT-013`: official grid/eval tables now support explicit catalog coexistence; exact duplicates are idempotent and conflicting duplicates fail closed. No shadow table was added.
- `WL-CONTRACT-014`: owner docs, DDL, reason-code seed, migration, commands, tests, reference evidence note, and trackers are synchronized. Files 16/17 remain unchanged.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; R2 IS runtime and all OOS proof are absent.

Implementation evidence:

```text
R1 code hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog code=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06
R2 catalog version=R2
R2 count=12
R2 code hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5
IS window=2023-01-02..2025-05-21
reserved OOS=2025-05-22..2026-05-29
```

Environment evidence:

```text
PHP lint=PASS / 312 PHP files
R2 pure-PHP smoke=PASS / 180 assertions
R1 factory compatibility=PASS / 24 of 24 rows
R1 IS-calibration service compatibility=PASS / exact output equality
PHPUnit=BLOCKED before discovery; dom/mbstring/xml/xmlwriter unavailable; exit 1
artisan migrate/seed/calibration=EXPECTED FAIL-CLOSED; PHP 8.4.16 violates >=7.3,<8.4 guard; exit 2
PDO database driver=unavailable
OOS read/execution=NOT PERFORMED
```

No contract is promoted to `LOCKED`. OOS-proof eligibility cannot be determined until the supported R2 IS run establishes either a valid frozen binding or an explicit no-valid-candidate result. Promotion remains `NOT_ELIGIBLE — OOS proof missing`.


## R2 Entry-Quality Calibration Final Contract Result — 2026-06-10

Session:
`WATCHLIST — WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

Final status:
`LOCAL_R2_IS_CALIBRATION_EXECUTED / R2_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
WatchlistBacktestR2: 26 tests / 530 assertions / PASS
WatchlistBacktestOos: 24 tests / 228 assertions / PASS
WatchlistBacktest: 117 tests / 2442 assertions / PASS
Full Watchlist: 209 tests / 3330 assertions / PASS

Migration 2026_06_10_000001_add_watchlist_backtest_catalog_identity_and_r2_entry_quality: Ran / batch 10
R2 seed run 1: inserted=12 / updated=0 / existing=0 / exit=0
R2 seed run 2: inserted=0 / updated=0 / existing=12 / exit=0
R1 immutable: true

R1 catalog=WS_BT_GRID_BOOTSTRAP_2026_06 / version=R1 / count=24 / hash=9da8b0983c57bde1ce0a1fbf1c119756f8af431c
R2 catalog=WS_BT_GRID_ENTRY_QUALITY_R2_2026_06 / version=R2 / count=12 / hash=0f2eaadaa446980a3d5e48cd498df2a8157c01a5

R2 IS window=2023-01-02..2025-05-21 / 562 trading dates
R2 IS trading-date hash=581dd450ebcbd56cb3a1c066c9fc80bbccb3a753
R2 valid rows=0
R2 failed rows=12
R2 failure codes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
R2 artifact hash=8a8521fc9a3726d90f2b77506532a1e5392def8b
max requested market-data date=2025-05-21
OOS service invoked=false
OOS repository invoked=false
OOS table unchanged=true
OOS executed=false
```

### Contract impact

- `WL-CONTRACT-006`: R2 runtime execution is proven, but R2 entry/catalog quality fails all canonical IS gates; remains `PARTIAL`.
- `WL-CONTRACT-007`: R1/R2 catalog identity, count, hash, and coexistence are proven in database; DONE for R2 execution scope, not `LOCKED`.
- `WL-CONTRACT-008`: R2 no-valid-candidate result is reason-coded by `WS_BT_R2_NO_VALID_IS_CANDIDATE`; aggregate failures remain downside/robust-return/stability.
- `WL-CONTRACT-009`: strict IS-only execution and no-best-of-failed behavior are proven. OOS remains correctly unread because there is no best-IS binding.
- `WL-CONTRACT-010`: two-run R2 IS artifact determinism is proven by identical artifact hash `8a8521fc9a3726d90f2b77506532a1e5392def8b`.
- `WL-CONTRACT-011`: fixed execution snapshot remains unchanged; quality failure is not attributed to execution-price drift.
- `WL-CONTRACT-013`: official grid/eval schema supports R1/R2 coexistence and idempotent R2 seed/eval behavior.
- `WL-CONTRACT-014`: trackers and R2 reference note are synchronized with final supported-operator evidence and next-session boundary.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY` because no valid IS binding and no OOS proof exist.

No contract is promoted to `LOCKED`. No acceptance gate was weakened. No OOS data was used. No best-of-failed binding exists.

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

### Future catalog naming contract note

`R1` and `R2` are historical aliases and backward-compatible evidence labels only. Future calibration catalogs must not continue numeric R-series naming (`R3`, `R4`, `R5`, etc.).

Future catalog code format:

```text
WS_BT_GRID_<FOCUS>_C##_YYYY_MM
```

Recommended next work:

```text
WATCHLIST — WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION
```

Recommended catalog identity if the diagnostic justifies a new catalog:

```text
WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

The next session must not mutate R1/R2, must not run OOS, must not lower canonical gates, and must not create a best-of-failed binding.

## Downside/Stability C01 Diagnostic-Design Contract Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION`

Status:
`DONE for downside/stability C01 diagnostic-design scope / C01_IMPLEMENTATION_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
R2 artifacts present: r2-is-run-1.json, r2-is-run-2.json
R2 artifact hash: 8a8521fc9a3726d90f2b77506532a1e5392def8b
R2 valid IS rows: 0
R2 failed IS rows: 12
R2 failure classes: WS_BT_EVAL_DOWNSIDE_FAIL, WS_BT_EVAL_ROBUST_RETURN_FAIL, WS_BT_EVAL_STABILITY_FAIL
R2 max requested market-data date: 2025-05-21
R2 OOS service/repository invoked: false
R2 OOS table unchanged: true
C01 reference note: docs/watchlist/system/policies/weekly_swing/_refs/WS_DOWNSIDE_STABILITY_C01_CALIBRATION_NOTE.md
C01 catalog design: WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06 / C01 / 8 / b746748945df595171b45d44c7c3fbbaa199a9f4
```

### Contract impact

- `WL-CONTRACT-006`: R2 scoring/runtime execution is preserved as failed quality evidence; C01 scoring design is finite and traceable but not implemented or runtime-proven.
- `WL-CONTRACT-007`: C01 design has stable semantic identity, count, catalog hash, row hashes, and parameter hashes, but no PHP catalog class, seeder, DB row, or runtime paramset projection exists yet.
- `WL-CONTRACT-008`: R2 failure reason distribution is explicitly diagnosed; C01 row rationales are documented. Runtime explainability remains unproven for C01.
- `WL-CONTRACT-009`: C01 design keeps strict IS-only scope and fixed execution semantics. No OOS runtime proof, service call, repository call, or table write occurred.
- `WL-CONTRACT-010`: C01 has no two-run runtime proof. Future proof must show catalog hash equality, IS date hash equality, metric equality, binding equality or none equality, artifact hash equality, idempotence, OOS table unchanged, and max requested/read date `<= 2025-05-21`.
- `WL-CONTRACT-011`: C01 keeps stop ATR, RR, fee, slippage, gap, price-band, and holding semantics fixed. Risk/ATR axes are design inputs only until implementation.
- `WL-CONTRACT-013`: C01 reference note is a deterministic design artifact, not a runtime artifact.
- `WL-CONTRACT-014`: implementation status, contract tracker, and C01 reference note are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; C01 IS runtime and all OOS proof are absent.

No contract is promoted to `LOCKED`. No acceptance gate was weakened. No OOS data was used. No best-of-failed binding exists.

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_DETERMINED
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE - OOS proof missing
PRODUCTION_READY=false
```

Next required work:

```text
WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 IMPLEMENTATION UNIT-STATIC SESSION
```

## Downside/Stability C01 Implementation Unit-Static Contract Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 IMPLEMENTATION UNIT-STATIC SESSION`

Status:
`DONE for downside/stability C01 implementation unit-static scope / OPERATOR_C01_IS_RERUN_REQUIRED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
C01 catalog code: WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog version: C01
C01 catalog count: 8
C01 catalog hash: 604ac98f6f193a4c317d4f25582deada84682846
C01 seed command: watchlist:backtest-c01-param-grid-seed
C01 IS artifact version: WATCHLIST_C01_IS_CALIBRATION_V1
C01 IS artifact scope: WEEKLY_SWING_DOWNSIDE_STABILITY_C01_IS_ONLY
C01 runtime status: C01_GRID_FAILED_IS_QUALITY (supersedes initial unit-static NOT_RUN status)
OOS status: OOS_NOT_READ
PHPUnit C01: 12 tests / 381 assertions / exit 0
PHPUnit Backtest filter: 130 tests / 2829 assertions / exit 0
PHPUnit full Watchlist: 222 tests / 3717 assertions / exit 0
MarketData required filters: 7/37, 4/16, 3/41 / exit 0
```

### Contract impact

- `WL-CONTRACT-006`: C01 scoring axes are implemented and projected; later runtime result below proves quality failed.
- `WL-CONTRACT-007`: C01 has stable semantic identity, count, catalog hash, row hashes, parameter hashes, repository allowlist, and factory projection.
- `WL-CONTRACT-008`: C01 row rationale and R2 diagnostic remain documented; later runtime result below records real IS execution.
- `WL-CONTRACT-009`: C01 keeps strict IS-only command boundary and does not introduce OOS service/repository/table writes.
- `WL-CONTRACT-010`: Superseded by the later C01 two-run runtime result below.
- `WL-CONTRACT-011`: C01 keeps stop ATR, RR, fee, slippage, gap, price-band, and holding semantics fixed.
- `WL-CONTRACT-013`: Superseded by the later C01 runtime artifact result below.
- `WL-CONTRACT-014`: implementation status, contract tracker, policy docs, and C01 reference note are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; C01 IS runtime later failed quality and all OOS proof remains absent.

No contract is promoted to `LOCKED`. No acceptance gate was weakened. No OOS data was used. No best-of-failed binding exists.

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF - no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE - OOS proof missing
PRODUCTION_READY=false
```

Next required work:

```text
WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 SEED AND IS TWO-RUN VALIDATION SESSION
```

## Downside/Stability C01 Seed And IS Two-Run Contract Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING DOWNSIDE/STABILITY C01 SEED AND IS TWO-RUN VALIDATION SESSION`

Status:
`DONE for downside/stability C01 calibration execution infrastructure / LOCAL_C01_IS_CALIBRATION_EXECUTED / C01_GRID_FAILED_IS_QUALITY / OOS_NOT_READ / NOT_PRODUCTION_READY`.

### Evidence

```text
C01 seed status=PASS
C01 seed exit_code=0
C01 inserted_count=8
C01 catalog_code=WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
C01 catalog_version=C01
C01 catalog_count=8
C01 catalog_hash=604ac98f6f193a4c317d4f25582deada84682846
C01 IS artifact_hash=c8505ce5a9045629234a685984d9138b3990c775
C01 IS file SHA1 run 1=04F6C664A0C9006C16542A8380034A0A633041DC
C01 IS file SHA1 run 2=04F6C664A0C9006C16542A8380034A0A633041DC
C01 valid IS rows=0
C01 failed IS rows=8
C01 failure classes=WS_BT_EVAL_DOWNSIDE_FAIL,WS_BT_EVAL_ROBUST_RETURN_FAIL,WS_BT_EVAL_STABILITY_FAIL
max_requested_market_data_date=2025-05-21
oos_service_invoked=0
oos_repository_invoked=0
oos_table_unchanged=1
oos_executed=0
production_ready=0
```

### Checklist

| Item | Status | Notes |
|---|---|---|
| R1/R2 preservation | `PASS` | Seed and artifacts preserve R1/R2 count/hash. |
| C01 seed | `PASS` | 8 rows inserted, exit code `0`. |
| C01 two-run determinism | `PASS` | File SHA1, artifact hash, catalog hash, date hash, evaluations, eval IDs, and none-binding are equal. |
| C01 quality gates | `FAIL` | All rows fail downside, robust-return, and stability gates. |
| C01 best binding | `NOT_CREATED` | No valid IS parameter, no best-of-failed. |
| OOS proof | `NOT_RUN` | OOS was not read or invoked. |
| Promotion | `NOT_ELIGIBLE` | OOS proof missing and C01 has no valid IS parameter. |

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF - no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE - OOS proof missing
PRODUCTION_READY=false
```

No next catalog was created in this session. Any further catalog design must be a separate future session.


## C01 Failure Diagnostic Contract Result - 2026-06-11

Session:
`WATCHLIST - WEEKLY SWING C01 FAILURE DIAGNOSTIC AND NEXT SEMANTIC CATALOG DESIGN SESSION`

Status:
`DONE for C01 failure diagnostic scope / NEXT_CATALOG_NOT_DESIGNED / OOS_NOT_READ / NOT_PRODUCTION_READY`.

Reference note:
`docs/watchlist/system/policies/weekly_swing/_refs/WS_C01_FAILURE_DIAGNOSTIC_NOTE.md`

### Contract impact

- `WL-CONTRACT-006`: C01 proves deterministic execution but failed strategy quality; scoring/ranking or setup-filter suspicion is supported, not resolved.
- `WL-CONTRACT-007`: C01 catalog traceability remains stable: code/version/count/hash are preserved and no row is mutated.
- `WL-CONTRACT-008`: C01 failure diagnostic is now explicit: all rows fail robust return, downside, and monthly stability while passing coverage/trade-count.
- `WL-CONTRACT-009`: IS-only and no-OOS boundary remains intact; `max_requested_market_data_date=2025-05-21`.
- `WL-CONTRACT-010`: C01 two-run determinism is preserved by matching SHA1, artifact hash, date hash, evaluation metrics, eval IDs, and null best binding.
- `WL-CONTRACT-011`: Execution semantics remain fixed; no exit-axis, fee, slippage, holding, gap, or price-band semantics changed.
- `WL-CONTRACT-013`: New diagnostic reference note records evidence and next catalog decision without inventing runtime data.
- `WL-CONTRACT-014`: implementation status, contract tracker, and reference notes are synchronized.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`; no valid IS parameter and no OOS proof exist.

Final eligibility:

```text
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF — no valid IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE — OOS proof missing
PRODUCTION_READY=false
```

Next required work:

```text
WATCHLIST - WEEKLY SWING C01 IS FAILURE DRILLDOWN DIAGNOSTIC SESSION
```

No next catalog was designed. A future catalog requires additional IS-only trade/month/ticker/setup-bucket drilldown evidence first.

## C01 IS Failure Drilldown Unit-Static Contract Result - 2026-06-11

### Evidence

- Added `WatchlistBacktestIsFailureDrilldownService.php` as an IS-only file artifact generator.
- Added `RunBacktestIsDiagnoseCommand.php` with explicit catalog/date/output options.
- Registered `RunBacktestIsDiagnoseCommand::class` in `app/Console/Kernel.php` without scheduler wiring.
- Added unit/static tests for deterministic artifact shape, no-OOS boundary, command registration, and dependency guardrails.
- Added `WS_C01_IS_FAILURE_DRILLDOWN_NOTE.md` reference note.
- Preserved C01 catalog hash `604ac98f6f193a4c317d4f25582deada84682846` and existing C01 artifact hash `c8505ce5a9045629234a685984d9138b3990c775`.

### Contract impact

- `WL-CONTRACT-008` moves from diagnostic-note-only to source-supported IS drilldown artifact surface, but remains not locked until operator runtime artifact proof exists.
- `WL-CONTRACT-009` remains no-OOS by source boundary: no OOS service/repository dependency, no OOS table write path, explicit IS dates only.
- `WL-CONTRACT-010` remains partial: deterministic source/hash design exists, but supported runtime two-run artifact equality is still operator-required.
- `WL-CONTRACT-013` expands artifact contract coverage to C01 drilldown fields.
- `WL-CONTRACT-014` updated for status/reference-note synchronization.
- `WL-CONTRACT-015` remains not ready.

### Validation boundary

```text
php lint new/changed PHP files = PASS
isolated stubbed PHP smoke = PASS
Artisan runtime = BLOCKED locally by unsupported PHP 8.4.16
PHPUnit = BLOCKED locally by missing dom, mbstring, xml, xmlwriter extensions
```

No runtime C01 drilldown PASS, OOS PASS, promotion, or production-readiness claim is recorded.

### Required next contract work

```text
WATCHLIST — C01 IS FAILURE DRILLDOWN OPERATOR RUNTIME EXECUTION SESSION
```

Run two IS-only diagnostic command executions, compare canonical artifact hash and file SHA1, confirm no OOS leakage, and only then decide whether diagnostic payload is sufficient for C02 or whether feature-level payload enrichment is required first.


## C01 IS Failure Drilldown Workspace Artifact Review Contract Result - 2026-06-11

### Evidence

- Current ZIP/workspace contains `storage/app/watchlist/backtest/c01-is-failure-drilldown-run-1.json`.
- The available artifact preserves C01 identity: catalog code `WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06`, version `C01`, count `8`, hash `604ac98f6f193a4c317d4f25582deada84682846`.
- The available artifact reports file SHA1 `db0a8498faca15e49871ee3b33ab420075cac156` and canonical artifact hash `c2cfd4d8a438108cd53636bccf4303b12e243de7`.
- The available artifact reports no-OOS markers: `max_requested_market_data_date=2025-05-21`, `strict_is_boundary_all_evaluations=true`, `oos_service_invoked=false`, `oos_repository_invoked=false`, `oos_table_unchanged=true`, `oos_executed=false`, and `production_ready=false`.
- The available artifact reports all eight C01 params failed downside, robust-return, and stability gates.

### Contract impact

- `WL-CONTRACT-008`: upgraded from source-surface-only to source plus one workspace drilldown artifact; still not `LOCKED` because two-run deterministic proof and operator PHPUnit/runtime proof are missing.
- `WL-CONTRACT-009`: remains no-OOS by source boundary and one artifact markers; not `LOCKED` without supported runtime proof.
- `WL-CONTRACT-010`: remains `PARTIAL`; one artifact is available, but `canonical_artifact_hash_run_1 == run_2` is not proven for drilldown.
- `WL-CONTRACT-013`: artifact contract shape is present in source and in one workspace artifact.
- `WL-CONTRACT-014`: docs synchronized for the one-run artifact review.
- `WL-CONTRACT-015`: remains `PARTIAL / NOT_READY`.

### Validation boundary

```text
php lint diagnostic service/command/tests = PASS
php artisan list = BLOCKED / ENV_UNSUPPORTED_PHP_VERSION / PHP 8.4.16
php vendor/bin/phpunit --version = BLOCKED / missing extensions: dom,mbstring,xml,xmlwriter
```

No OOS proof, promotion, production readiness, or next catalog design is unlocked by this result.

### Required next contract work

```text
WATCHLIST — C01 IS FAILURE DRILLDOWN OPERATOR TWO-RUN PROOF SESSION
```

Run the IS-only diagnostic command twice in the supported operator environment, compare canonical artifact hash and file SHA1, confirm no OOS leakage, and keep `NEXT_CATALOG_NOT_DESIGNED` unless the runtime payload is enriched enough to support a specific next semantic catalog decision.
