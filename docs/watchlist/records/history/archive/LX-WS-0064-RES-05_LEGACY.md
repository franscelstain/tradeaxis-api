# Legacy Role Extract — LEGACY — RESEARCH

> **Document Type:** RESEARCH
> **Authoritative Role:** `RESEARCH`
> **Status:** HISTORICAL_EXTRACT / IMMUTABLE
> **Legacy Extract ID:** `LX-WS-0064-RES-05`
> **Legacy Source ID:** `LS-WS-0064`
> **Legacy Work Key:** `LEGACY`
> **Original Path:** `docs/watchlist/audit/LUMEN_CONTRACT_TRACKER.md`
> **Original SHA1:** `EA74B18E611681C8BFDFEA7F436AE16E2222F596`
> **Source Sections:** L293-L351 PRIOR SESSION - WS NEW STRATEGY R01 RESEARCH HYPOTHESIS AND DIAGNOSTIC EVIDENCE; L352-L398 PRIOR SESSION - C171 COMPARATIVE OFFICIAL IS FAILURE DIAGNOSTIC AND R2 HYPOTHESIS LOCK; L1853-L1856 PRIOR SESSION - C19 FINAL STRATEGY MODEL REDESIGN AND PRICE DIAGNOSTIC; L2325-L2389 PRIOR SESSION - C12 EXIT MODEL REDESIGN CONTRACT SESSION; L2699-L2755 PRIOR SESSION - C07 STRATEGY-QUALITY REDESIGN / RUNTIME FEATURE AUDIT SESSION; L2868-L2931 PRIOR SESSION - C04 IS CANDIDATE-SELECTION REDESIGN AND IMPLEMENTATION SESSION; L4802-L4849 R2 Entry-Quality Calibration Contract Update â€” 2026-06-10; L4850-L4932 R2 Entry-Quality Calibration Final Contract Result â€” 2026-06-10; L5341-L5444 C35 Contract â€” IS-Only Robustness Redesign Diagnostic; L5445-L5543 C36 Contract â€” IS-Controlled Redesign Candidate Formation; L5656-L5764 C38 Contract - IS Redesign Or Evidence Expansion Diagnostic; L5765-L5878 C39 Contract - IS Controlled Redesign With Coverage And Branch Diversification Guards; L6266-L6298 C44 Contract â€” IS Guard Refinement Candidate Formation; L6475-L6547 C49 Contract - IS Broader Strategy Redesign From C48 Failure Attribution; L6548-L6671 C50 Contract - IS Validation and Anti-Overfit Check for C49 Redesign; L6672-L6729 C52 Contract â€” Sector Reconstruction and Second-Pass Concentration Redesign; L6730-L6770 C53 Contract â€” IS Evidence Expansion for C52 Redesign; L6771-L6907 C51 Contract â€” IS-only Concentration/Dependency Redesign Review; L6908-L6933 C54 Contract â€” Rolling Stability Redesign or Recalibration (IS Only); L6934-L6990 C55 Contract â€” Rolling Stability Redesign Continuation (IS Only); L6991-L7090 C56 Contract â€” Rolling Stability Redesign Continuation (IS Only); L7242-L7315 C58 contract â€” loss-cluster/concentration redesign continuation IS-only; L7316-L7374 C59 contract â€” loss-cluster or branch/bucket redesign continuation IS-only; L7415-L7467 C60 Contract Tracker â€” Regime Stress and LOO Dependency Redesign IS-Only
> **Extract Body SHA1:** `DC506E3BA6AD0E542F548B65C70EFE66EF06B887`
> **Current Authority:** NO

The body below is an exact copy of the registered source sections for this single semantic role. Cross-role authority is intentionally excluded.

---

## PRIOR SESSION - WS NEW STRATEGY R01 RESEARCH HYPOTHESIS AND DIAGNOSTIC EVIDENCE

Session:
`WATCHLIST - WS NEW STRATEGY R01 RESEARCH HYPOTHESIS AND DIAGNOSTIC EVIDENCE`

Current status:

`SEPARATE_NEW_STRATEGY_RESEARCH / C171_FINAL_CLOSURE_VERIFIED / THREE_PRE_REGISTERED_HYPOTHESES_SUPPORTED_WITH_H1_P25_WARNING / IMMUTABLE_SIGNAL_FEATURE_LINEAGE_PASS / READ_ONLY_DIAGNOSTIC_REPLAY_PARITY_PASS / DATABASE_BOUNDARY_UNCHANGED / NO_DRAFT / NO_IS / NO_OOS / NOT_PRODUCTION_READY`.

R01 contract status:

- `WL-CONTRACT-WSR01-001`: PASS_SOURCE. R01 is a separate strategy-research
  scope; it does not reopen C171 and does not claim C172/OOS permission.
- `WL-CONTRACT-WSR01-002`: PASS_SOURCE. Source identity is fixed to
  `eval_id=204`, `param_set_id=11`, `param_id=166`, paramset hash
  `c93bae2b761028d6b236f368d5b19bb4f498715a`, and manifest hash
  `604bfbe9698fbb8ec3c74e3fa6e10f9335f66d1d`.
- `WL-CONTRACT-WSR01-003`: PASS_SOURCE. R01 verifies the official database
  manifest before interpretation.
- `WL-CONTRACT-WSR01-004`: PASS_SOURCE. Equity decision-time features are
  joined through exact signal `publication_id + run_id + trade_date +
  ticker_id` against immutable indicator history.
- `WL-CONTRACT-WSR01-005`: PASS_SOURCE. Official picks are replayed with current
  readable published prices and must match persisted return to six decimals
  plus entry publication lineage.
- `WL-CONTRACT-WSR01-006`: PASS_SOURCE. Exactly three hypotheses are
  pre-registered: breakout quality, momentum persistence, and market regime.
- `WL-CONTRACT-WSR01-007`: PASS_SOURCE. Return, actual gap, fill, and exit
  outcome are diagnostic-only and cannot route a future candidate.
- `WL-CONTRACT-WSR01-008`: PASS_SOURCE. R01 cannot create a DRAFT, invoke
  official IS/OOS, read OOS, promote, create PLAN, mutate CONFIRM, or activate
  production.
- `WL-CONTRACT-WSR01-009`: PASS_OPERATOR. Runtime completed with 1,308/1,308
  official-pick return and entry-lineage parity, zero mismatches, full
  signal/benchmark feature coverage, and unchanged database boundary counts.
- `WL-CONTRACT-WSR01-010`: PASS_OPERATOR. Three hypotheses are supported for
  minimal candidate design. H1 carries a P25 regression warning; H2 support is
  from ROC20 contrast; H3 has the strongest robust contrast.
- `WL-CONTRACT-WSR01-011`: PASS_OPERATOR. Focused R01 PHPUnit passed 3 tests /
  35 assertions and C171 regression passed 63 tests / 695 assertions.
- `WL-CONTRACT-WSR01-012`: PASS_OPERATOR. Full Watchlist regression passed
  7,137 tests / 48,447 assertions.

```text
WSR01_MAX_HYPOTHESES=3
WSR01_MAX_FUTURE_CANDIDATES=3
WSR01_MAX_REMEDIATION_ROUNDS=1
WSR01_CANONICAL_GATES_CHANGED=0
WSR01_OOS_BEFORE_IS_PASS_ALLOWED=0
WSR01_RUNTIME_ARTIFACT_HASH=a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7
WSR01_RUNTIME_FILE_SHA1=BF76FB76388D6E0C81230B12B1DD4E934BBBE59A
WSR01_SUPPORTED_HYPOTHESIS_COUNT=3
WSR01_OFFICIAL_PICK_REPLAY_MISMATCH_COUNT=0
WSR01_PRODUCTION_READY=0
```

Watchlist Production Ready: `NO`.

## PRIOR SESSION - C171 COMPARATIVE OFFICIAL IS FAILURE DIAGNOSTIC AND R2 HYPOTHESIS LOCK

Session:
`WATCHLIST - C171 COMPARATIVE OFFICIAL IS FAILURE DIAGNOSTIC AND R2 HYPOTHESIS LOCK`

Current status:

`EVAL_188_TO_193_IMMUTABLE_FAILED_IS / COMPARATIVE_DIAGNOSTIC_IMPLEMENTED_PENDING_OPERATOR_RUNTIME / EXACT_DATABASE_MANIFEST_AND_RET_NET_REPLAY_PARITY_REQUIRED / PARAMSET_5_ANCHOR / SEMANTIC_HYPOTHESIS_LOCK_ONLY / NO_DRAFT / NO_OOS / NOT_PRODUCTION_READY`.

C171 comparative contract status:

- `WL-CONTRACT-C171-008`: PASS_EVIDENCE. Baseline and five remediation candidates are locked to `eval_id=188-193`, `param_set_id=1-6`, the same canonical IS window, strict boundary, and failed canonical gates.
- `WL-CONTRACT-C171-009`: PASS_EVIDENCE. Paramset 5 / `eval_id=192` is the deterministic aggregate-metric anchor: highest positive average return among coverage-valid failed candidates, with robust-return/stability tiebreaks. Anchor does not imply IS pass.
- `WL-CONTRACT-C171-010`: PASS_SOURCE. Comparative service verifies locked physical file SHA1, recomputed JSON artifact hash, strict route/boundary identity, database evaluation identity, and full official evidence manifest parity for every eval before analysis.
- `WL-CONTRACT-C171-011`: PASS_SOURCE. Diagnostic execution reconstructs only frozen official picks through exact-date current readable publications and blocks unless every persisted official `ret_net` matches to six decimals and entry publication ID/version/run lineage matches exactly.
- `WL-CONTRACT-C171-012`: PASS_SOURCE. Overlap, added/removed contribution, monthly stability, score deciles, price/tick risk, exit distribution, IHSG decision-time regime, and population reconciliation outputs are implemented.
- `WL-CONTRACT-C171-013`: PASS_SOURCE. `metrics.picks_count` is required to equal DB official picks. The broader `trade_evidence.evaluated_trade_count` is documented as all evaluated buckets and is not mixed into official TOP metrics.
- `WL-CONTRACT-C171-014`: PASS_SOURCE. At most three decision-time hypotheses may be locked. Ticker blacklist, month blacklist, outcome routing, OOS reads, and canonical gate weakening are forbidden.
- `WL-CONTRACT-C171-015`: PASS_SOURCE. Future catalog identity is semantic (`WS_BT_GRID_<FOCUS>_C01_2026_07`); numeric `R3`, `R4`, or later revision naming is forbidden.
- `WL-CONTRACT-C171-016`: PASS_SOURCE. Current stage does not query the OOS table and cannot create/mutate DRAFTs, invoke official IS/OOS, promote a paramset, create PLAN, persist recommendation, mutate CONFIRM, activate production, or roll out.
- `WL-CONTRACT-C171-017`: OPERATOR_VALIDATION_REQUIRED. Focused PHPUnit, full Watchlist regression, comparative runtime, output hashes, and zero-mutation row-count proof are not yet available for this patch.
- `WL-CONTRACT-C171-018`: NOT_READY. C172 remains forbidden because no eval among `188-193` passes all canonical IS gates.

```text
C171_COMPARATIVE_SOURCE_EVAL_IDS=188,189,190,191,192,193
C171_COMPARATIVE_SOURCE_PARAM_SET_IDS=1,2,3,4,5,6
C171_COMPARATIVE_ANCHOR_EVAL_ID=192
C171_COMPARATIVE_ANCHOR_PARAM_SET_ID=5
C171_COMPARATIVE_IMPLEMENTATION=PASS_SOURCE_PENDING_OPERATOR_RUNTIME
C171_COMPARATIVE_COMMAND=watchlist:backtest-c171-comparative-official-is-failure-diagnostic
C171_R2_HYPOTHESIS_MAX_COUNT=3
C171_NEXT_CATALOG_IDENTITY=WS_BT_GRID_<SEMANTIC_FOCUS>_C01_2026_07
C171_CURRENT_PATCH_DRAFT_CREATED=0
C171_CURRENT_PATCH_OFFICIAL_IS_INVOKED=0
C171_OOS_RUNTIME_INVOKED=0
C171_OOS_TABLE_READ=0
C171_PARAMSET_PROMOTED=0
C171_PLAN_RUN_CREATED=0
C171_PRODUCTION_READY=0
C172_ALLOWED=0
```

Behavioral ownership remains in `docs/watchlist/system/**`. Return/path fields remain evaluation-only. The next evidence must come from operator execution of the read-only comparative command and its complete artifact set; no new catalog may be persisted from source inspection alone.

Watchlist Production Ready: `NO`.

## PRIOR SESSION - C19 FINAL STRATEGY MODEL REDESIGN AND PRICE DIAGNOSTIC

C19 closed as diagnostic success but catalog-candidate failure. Its final frontier evidence is carried into C20 only as baseline context, not as permission to reopen C19 tuning.

## PRIOR SESSION - C12 EXIT MODEL REDESIGN CONTRACT SESSION

Session:
`WATCHLIST - C12 EXIT MODEL REDESIGN CONTRACT SESSION`

Status:
`C12_EXIT_MODEL_REDESIGN_CONTRACT_READY / CATALOG_CREATION_NOT_AUTHORIZED / C07_REJECTED_AS_STRATEGY_CATALOG / C12_STRATEGY_CATALOG_NOT_CREATED / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C12 contract evidence:

- R1/R2/C01/C02/C03/C04/C05/C06/C07 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C07 remains rejected as a strategy-quality catalog and was not patched to look successful;
- C12 did not create a strategy catalog, did not select best-of-failed, and did not invoke OOS;
- C12 adds a redesign-contract command that reads C11 evidence and emits a contract artifact with `design_contract_ready=1`;
- C12 keeps `catalog_creation_authorized=0`, `exit_model_catalog_authorized=0`, `oos_executed=0`, and `production_ready=0`;
- C12 artifact hash is deterministic across two runs: `04d4e2f230685962fadd1bc26c294cbaed10f38b`;
- C12 docs artifact file SHA1 is `B3575122DB69A0CA8EAD4D3C78B328687C2CC894`;
- allowed first-phase future axes are `risk.min_rr` and `risk.stop_atr_mult`;
- blocked first-phase axes are `backtest.holding_days` and `backtest.target_pct|backtest.stop_pct`;
- validation passed after C12 changes: `WatchlistBacktestExitModelRedesignContract` = `OK (3 tests, 33 assertions)`, `WatchlistBacktestExitModelContractAudit` = `OK (3 tests, 34 assertions)`, `WatchlistBacktestC07` = `OK (10 tests, 376 assertions)`, full Watchlist = `OK (308 tests, 6669 assertions)`.

Contract status update:

- `WL-CONTRACT-008`: PASS for exit-model redesign contract traceability and explicit no-catalog decision;
- `WL-CONTRACT-009`: PASS for strict artifact-only contract generation and no OOS boundary crossing;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C12 redesign contract generation;
- `WL-CONTRACT-011`: FAILED_QUALITY remains for C07 strategy quality;
- `WL-CONTRACT-013`: PASS for C12 contract artifact surface;
- `WL-CONTRACT-014`: PASS for C12 docs and JSON artifact tracking.

C12 audit references:

```text
docs/watchlist/audit/WS_C12_EXIT_MODEL_REDESIGN_CONTRACT_FINAL_RESULT.md
docs/watchlist/audit/WS_C12_OPERATOR_VALIDATION_COMMANDS.md
docs/watchlist/audit/_artifacts/c12-exit-model-redesign-contract.json
```

C12 OOS-proof eligibility:

```text
NOT_ELIGIBLE
```

Reason:

```text
is_valid_param_count=0
param_id_best_is=
best_is_binding_hash=
catalog_creation_authorized=0
exit_model_catalog_authorized=0
strategy_catalog_created=0
oos_executed=0
production_ready=0
```

Production-readiness status:

```text
NOT_PRODUCTION_READY
```

Reason: C12 is a redesign contract artifact only. It does not create a catalog, run IS calibration for a new catalog, or run OOS. OOS has not been run and must not be claimed PASS.

## PRIOR SESSION - C07 STRATEGY-QUALITY REDESIGN / RUNTIME FEATURE AUDIT SESSION

Session:
`WATCHLIST - C07 STRATEGY-QUALITY REDESIGN / RUNTIME FEATURE AUDIT SESSION`

Status:
`C07_IMPLEMENTED / C07_SEEDED / C07_IS_EXECUTED / C07_IS_QUALITY_FAILED / C07_REJECTED_AS_STRATEGY_CATALOG / C07_DETERMINISTIC / OOS_NOT_RUN / NOT_PRODUCTION_READY`.

Current C07 contract evidence:

- R1/R2/C01/C02/C03/C04/C05/C06 identities remain immutable historical evidence and are not renamed, mutated, reinterpreted, or promoted;
- C02/C03/C04/C05/C06 remain rejected as strategy-quality catalogs and were not patched to look successful;
- C07 is a new semantic C-campaign catalog: `WS_BT_GRID_DOWNSIDE_STABILITY_C07_2026_06`, version `C07`, count `12`, hash `233b45b06cbf34da221d5d7de2d9725fdf4d3441`;
- C07 uses a C07-only runtime-supported candidate-selection extension in `bt_grid_resolution.candidate_selection_extension`;
- C07 introduces runtime pass-through for audited optional metrics and uses sector-relative values only as continuous confirmation metrics;
- C07 does not introduce unsupported `sector_code`, sector whitelist, or `sector_filter` catalog axes;
- C07 PHPUnit validation passed: C07 filter `OK (10 tests, 376 assertions)` and full Watchlist `OK (300 tests, 6544 assertions)`;
- C07 seed passed and inserted 12 catalog rows with `updated_count=0`;
- seed-time R1/R2/C01/C02/C03/C04/C05/C06 immutability markers were all true;
- C07 IS calibration run 1 and run 2 both failed quality with the same deterministic artifact hash `c562d0a37ec7911c17c50072413fbbae25bb6114`;
- C07 quality failure is explicit: `C07_GRID_FAILED_IS_QUALITY` / `WS_BT_C07_NO_VALID_IS_CANDIDATE` / `is_valid_param_count=0` / `is_failed_param_count=12`;
- C07 did not open OOS and all reported OOS guards remained clean;
- C07 has no best IS binding and no best IS binding hash, so it cannot advance to OOS.

Contract status update:

- `WL-CONTRACT-007`: PASS for C07 catalog identity, seed, and R1/R2/C01/C02/C03/C04/C05/C06 immutability evidence;
- `WL-CONTRACT-008`: PASS for C07 traceability as a new catalog derived from C01/C04/C05/C06 forensic evidence and runtime feature audit, not a mutation of prior catalogs;
- `WL-CONTRACT-009`: PASS for C07 IS-only boundary in calibration output;
- `WL-CONTRACT-010`: PASS for OOS non-invocation during C07 IS calibration;
- `WL-CONTRACT-011`: FAILED_QUALITY for C07 strategy quality because no valid IS candidate exists;
- `WL-CONTRACT-014`: PASS for C07 docs/test/command/forensic tracking update with per-param C07 metrics extracted from current artifacts.

C07 OOS-proof eligibility:

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

Reason: C07 has no valid IS binding and no OOS proof. C07 must remain rejected as a strategy-quality catalog.

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

## R2 Entry-Quality Calibration Contract Update â€” 2026-06-10

Session:
`WATCHLIST â€” WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

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

No contract is promoted to `LOCKED`. OOS-proof eligibility cannot be determined until the supported R2 IS run establishes either a valid frozen binding or an explicit no-valid-candidate result. Promotion remains `NOT_ELIGIBLE â€” OOS proof missing`.

## R2 Entry-Quality Calibration Final Contract Result â€” 2026-06-10

Session:
`WATCHLIST â€” WEEKLY SWING R2 ENTRY-QUALITY CALIBRATION EXECUTION SESSION`

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
OOS_PROOF_ELIGIBILITY=NOT_ELIGIBLE_FOR_OOS_PROOF â€” no valid R2 IS parameter
PROMOTION_ELIGIBILITY=NOT_ELIGIBLE â€” OOS proof missing
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
WATCHLIST â€” WEEKLY SWING IS FAILURE DIAGNOSTIC AND DOWNSIDE/STABILITY C01 CATALOG DESIGN SESSION
```

Recommended catalog identity if the diagnostic justifies a new catalog:

```text
WS_BT_GRID_DOWNSIDE_STABILITY_C01_2026_06
```

The next session must not mutate R1/R2, must not run OOS, must not lower canonical gates, and must not create a best-of-failed binding.

## C35 Contract â€” IS-Only Robustness Redesign Diagnostic

C35 is an IS-only robustness redesign diagnostic after C34. It locks the C34 source artifact before reading IS evidence.

Contract locks:

```text
input_c34_artifact=storage/app/watchlist/backtest/c34-bad-month-robustness-diagnostic.json
expected_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
actual_c34_hash=1dcf355095334796c2f4558823a1882e71e3ed30
c34_hash_match=true
expected_c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
actual_c34_status=C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED
expected_c34_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
actual_c34_conclusion=C34_BAD_MONTH_ROBUSTNESS_FAILURE_CONFIRMED_AFTER_C33_DATA_PATH_PASS
production_ready=false
```

Required boundaries:

```text
IS_ONLY_ROBUSTNESS_REDESIGN_DIAGNOSTIC=true
C34_ARTIFACT_HASH_LOCK=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_PROFILE_RESELECTION=true
NO_CANDIDATE_RESELECTION=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C34_MUTATION=true
production_ready=false
oos_data_used_for_tuning=false
```

C34 bad months are context-only:

```text
bad_months_oos_for_context_only=2025-06,2025-08,2026-03
```

They must not be used for threshold tuning, candidate selection, profile selection, or production gating.

Redesign hypotheses must come from IS evidence only. Valid support levels:

```text
STRONG_IS_SUPPORT
MODERATE_IS_SUPPORT
WEAK_IS_SUPPORT
INSUFFICIENT_IS_SUPPORT
```

Current source-of-truth IS evidence source:

```text
storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json
```

C35 output artifact:

```text
storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
artifact_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
file_sha1=733BE61DF96DBA0ECA450ECCF30A8C0CE8329A4B
```

C35 validation status:

```text
PHPUNIT_C35=PASS
PHPUNIT_C35_RESULT=OK (11 tests, 106 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (529 tests, 11607 assertions)
ARTISAN_C35_RUNTIME=COMPLETED
C35_FINAL_STATUS=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
```

C35 diagnostic result:

```text
IS_EVIDENCE_TOTAL_ROWS=15750
IS_EVIDENCE_G21_ROWS=1770
IS_EVIDENCE_G16_ROWS=1320
IS_MONTHS_COVERED=27
G21_IS_WEAKNESS_CONFIRMED=true
G16_IS_WEAKNESS_CONFIRMED=true
DIAGNOSTIC_CONCLUSION=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
NEXT_STEP=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
```

C35 hypotheses:

```text
C35_HYP_G21_NO_PROFIT_SIGNAL_BRANCH_WEAK=STRONG_IS_SUPPORT
C35_HYP_G21_FALLBACK_EXIT_TOO_LATE=STRONG_IS_SUPPORT
C35_HYP_G16_NEXT_OPEN_DELAY_GAP_DAMAGE=MODERATE_IS_SUPPORT
C35_HYP_BRANCH_CONCENTRATION_REQUIRES_IS_REGIME_FILTER=MODERATE_IS_SUPPORT
```

C35 contract decision: PASS. C35 completed the IS-only robustness redesign diagnostic, kept OOS context-only, kept production readiness false, and recommends C36 IS-controlled redesign candidate formation.

---

## C36 Contract â€” IS-Controlled Redesign Candidate Formation

C36 contract scope:

```text
CONTRACT_CODE=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION
SOURCE_ARTIFACT=storage/app/watchlist/backtest/c35-is-robustness-redesign-diagnostic.json
EXPECTED_C35_HASH=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
EXPECTED_C35_STATUS=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
EXPECTED_C35_CONCLUSION=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c36-is-controlled-redesign-candidate-formation.json
```

Required C36 boundaries:

```text
IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION=true
C35_ARTIFACT_HASH_LOCK=true
C36_CANDIDATE_FROM_C35_HYPOTHESES=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C35_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
```

Candidate contract result:

```text
C36_BASELINE_C35_CURRENT_BRANCH_BEHAVIOR=EVALUATED
C36_G21_EARLIER_NO_PROFIT_EXIT_D2_CLOSE_OR_D2_GUARD=NOT_EVALUABLE:C36_BLOCKED_G21_EARLIER_EXIT_PRICE_PATH_UNAVAILABLE
C36_G21_NO_PROFIT_BRANCH_SUPPRESSION_GATE=EVALUATED:CANDIDATE_FORMED
C36_G21_BAD_MONTH_LIKE_REGIME_GATED_FALLBACK=NOT_EVALUABLE:C36_BLOCKED_REGIME_PRE_TRADE_FEATURE_UNAVAILABLE
C36_G16_NEXT_OPEN_DELAY_DAMAGE_GATE=NOT_EVALUABLE:C36_BLOCKED_G16_DELAY_DAMAGE_PRE_TRADE_FIELD_UNAVAILABLE
C36_G16_KEEP_AS_COMPARATOR_NO_CHANGE=EVALUATED:CANDIDATE_FORMED
C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR=EVALUATED:CANDIDATE_FORMED:BEST_IS_CANDIDATE_NOT_PRODUCTION
```

C36 output contract result:

```text
baseline_summary=present
candidate_results=present
candidate_comparison_table=present
candidate_safety_audit=present
not_evaluable_reasons=present
is_bad_month_like_candidate_effect=present
ticker_failure_cluster_after_candidate=present
redesign_decision_notes=present
```

C36 validation status:

```text
C36_IMPLEMENTATION_STATUS=IMPLEMENTED
PHPUNIT_C36=PASS:OK (15 tests, 203 assertions)
FULL_WATCHLIST_PHPUNIT=PASS:OK (544 tests, 11810 assertions)
ARTISAN_C36_RUNTIME=COMPLETED
C36_FINAL_STATUS=C36_IS_CONTROLLED_REDESIGN_CANDIDATE_FORMATION_COMPLETED
C36_ARTIFACT_HASH=8bc5198cf3b79fc9b58c39fc19f319826406b4b1
C36_FILE_SHA1=A5D7E25594238C2743E5DB2E68657AE95BA8B927
```

C35 lock result:

```text
expected_c35_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
actual_c35_hash=1ab43b0dcee6d41d11b2ab0ed904721836dee3b1
c35_hash_match=true
c35_status=C35_IS_ROBUSTNESS_REDESIGN_DIAGNOSTIC_COMPLETED
c35_diagnostic_conclusion=C35_IS_G21_AND_G16_WEAKNESS_CONFIRMED
```

C36 candidate decision:

```text
total_candidates=7
evaluated_candidates=4
not_evaluable_candidates=3
candidate_formed=true
best_is_candidate_code=C36_COMBINED_G21_REDESIGN_PLUS_G16_COMPARATOR
best_is_candidate_is_not_production=true
diagnostic_conclusion=C36_COMBINED_CANDIDATE_FORMED
next_step_recommendation=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK
production_ready=false
```

C36 contract decision: PASS. C36 completed IS-controlled redesign candidate formation from C35 hypotheses and C28 IS evidence only. C36 forms a diagnostic combined IS candidate, but the candidate is not production-ready and does not unlock OOS proof. C37 IS validation / anti-overfit check is required before any OOS proof.

---

## C38 Contract - IS Redesign Or Evidence Expansion Diagnostic

C38 contract scope:

```text
CONTRACT_CODE=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
SOURCE_ARTIFACT=storage/app/watchlist/backtest/c37-is-validation-anti-overfit-check.json
EXPECTED_C37_HASH=5938e353296cb2188b6668093522d0b40d6cb9d2
EXPECTED_C37_STATUS=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
EXPECTED_C37_CONCLUSION=C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
EXPECTED_C37_NEXT_STEP=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json
```

Required C38 boundaries:

```text
IS_ONLY_DIAGNOSTIC=true
C37_ARTIFACT_HASH_LOCK=true
C37_FAILED_ANTI_OVERFIT_MUST_BE_CONFIRMED=true
NO_NEW_CANDIDATE_SELECTION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C37_ARTIFACT_MUTATION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

C38 diagnostic target contract:

```text
diagnose_c37_month_coverage_failure=true
diagnose_c37_branch_concentration_warning=true
diagnose_c37_rolling_warning=true
diagnose_c36_not_evaluable_pre_trade_blockers=true
derive_evidence_expansion_requirements=true
derive_is_controlled_redesign_hypotheses=true
candidate_must_not_advance_to_oos_from_c38=true
```

C38 output contract result:

```text
source_c37_summary=present
month_coverage_failure_diagnostic=present
branch_concentration_diagnostic=present
rolling_warning_diagnostic=present
not_evaluable_evidence_gap_diagnostic=present
evidence_expansion_requirements=present
redesign_hypotheses=present
candidate_safety_audit=present
```

C38 validation status:

```text
C38_IMPLEMENTATION_STATUS=IMPLEMENTED
PHPUNIT_C38=PASS:OK (15 tests, 137 assertions)
FULL_WATCHLIST_PHPUNIT=PASS:OK (576 tests, 12290 assertions)
ARTISAN_C38_RUNTIME=COMPLETED
C38_FINAL_STATUS=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
C38_ARTIFACT_HASH=7fe69c9ee9797615df676b0fe0c7378b452da429
C38_FILE_SHA1=74AF66E0170D4C6FF8AE3B7E45F8EC72D9774A7B
```

C37 lock result:

```text
expected_c37_hash=5938e353296cb2188b6668093522d0b40d6cb9d2
actual_c37_hash=5938e353296cb2188b6668093522d0b40d6cb9d2
c37_hash_match=true
c37_status=C37_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_COMPLETED
c37_diagnostic_conclusion=C37_CANDIDATE_FAILED_ANTI_OVERFIT_CHECK
c37_next_step=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC
```

C38 diagnostic result:

```text
c37_overall_anti_overfit_result=FAIL
month_coverage_failure_diagnostic=CONFIRMED_REDESIGN_REQUIRED
zero_pick_months=2023-03
branch_concentration_diagnostic=CONFIRMED_BRANCH_DIVERSIFICATION_REQUIRED
candidate_top_branch_share=1.0
candidate_g16_share=1.0
suppressed_g21_rows=1770
rolling_warning_diagnostic=CONFIRMED_ROLLING_STABILITY_REVIEW_REQUIRED
rolling_warning_window=2024-06_to_2024-11
requirements_count=4
diagnostic_conclusion=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
next_step_recommendation=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
production_ready=false
```

C38 contract decision: PASS as an IS-only diagnostic. C38 confirms the failed C37 candidate should not go directly to OOS proof. The next step must be an IS-controlled C39 redesign with month coverage and branch diversification guards, plus rolling-window and pre-trade evidence expansion. C38 does not select a new candidate, does not run OOS proof, does not promote a catalog, and keeps `production_ready=false`.

---

## C39 Contract - IS Controlled Redesign With Coverage And Branch Diversification Guards

C39 contract scope:

```text
CONTRACT_CODE=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
SOURCE_ARTIFACT=storage/app/watchlist/backtest/c38-is-redesign-evidence-expansion-diagnostic.json
EXPECTED_C38_HASH=7fe69c9ee9797615df676b0fe0c7378b452da429
EXPECTED_C38_STATUS=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
EXPECTED_C38_CONCLUSION=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
EXPECTED_C38_NEXT_STEP=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c39-is-controlled-redesign-with-coverage-and-branch-diversification-guards.json
```

Required C39 boundaries:

```text
IS_ONLY_CANDIDATE_FORMATION=true
C38_ARTIFACT_HASH_LOCK=true
C39_FROM_C38_EVIDENCE_EXPANSION_REQUIRED=true
COVERAGE_GUARD_REQUIRED=true
BRANCH_DIVERSIFICATION_GUARD_REQUIRED=true
CANDIDATE_REQUIRES_C40_VALIDATION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C38_ARTIFACT_MUTATION=true
production_ready=false
oos_data_used_for_tuning=false
return_used_for_selection=false
future_path_used_for_selection=false
future_path_price_used_for_selection=false
profile_ret_net_used_for_selection=false
derived_mfe_mae_used_for_execution=false
```

C39 guard target contract:

```text
month_coverage_guard_must_remove_zero_pick_month=true
branch_diversification_guard_must_reduce_top_branch_share=true
max_top_branch_share=0.80
candidate_selection_uses_metadata_ordering_only=true
candidate_may_advance_to_C40_validation_only=true
candidate_may_not_advance_to_oos_from_C39=true
```

C39 output contract result:

```text
source_c38_summary=present
guard_requirements_from_c38=present
guard_configuration=present
baseline_summary=present
candidate_results=present
candidate_comparison_table=present
formed_candidate_codes=present
candidate_summary=present
candidate_safety_audit=present
not_evaluable_reasons=present
guard_validation_summary=present
redesign_decision_notes=present
```

C39 validation status:

```text
C39_IMPLEMENTATION_STATUS=IMPLEMENTED
PHPUNIT_C39=PASS:OK (17 tests, 174 assertions)
FULL_WATCHLIST_PHPUNIT=PASS:OK (593 tests, 12464 assertions)
ARTISAN_C39_RUNTIME=COMPLETED
C39_FINAL_STATUS=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS_COMPLETED
C39_ARTIFACT_HASH=504aaa061054ed2771ed08294d8a0570f08e18db
C39_FILE_SHA1=B08233211E335C982E327D6A0C638428B906BFC9
```

C38 lock result:

```text
expected_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
actual_c38_hash=7fe69c9ee9797615df676b0fe0c7378b452da429
c38_hash_match=true
c38_status=C38_IS_REDESIGN_OR_EVIDENCE_EXPANSION_DIAGNOSTIC_COMPLETED
c38_diagnostic_conclusion=C38_EVIDENCE_EXPANSION_REQUIRED_BEFORE_OOS
c38_next_step=C39_IS_CONTROLLED_REDESIGN_WITH_COVERAGE_AND_BRANCH_DIVERSIFICATION_GUARDS
```

C39 guarded candidate result:

```text
candidate_formed=true
best_is_candidate_code=C39_COVERAGE_AND_BRANCH_GUARD_G16_PLUS_METADATA_G21_MONTHLY_QUOTA
best_is_candidate_is_not_production=true
best_candidate_requires_C40_validation=true
baseline_months_required=27
c38_zero_pick_months=2023-03
metadata_monthly_g21_quota_per_month=13
metadata_monthly_g21_quota_selected_rows=343
best_candidate_zero_pick_month_count=0
best_candidate_top_branch_share=0.79374624173181
diagnostic_conclusion=C39_GUARDED_IS_CANDIDATE_FORMED
next_step_recommendation=C40_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C39_GUARDED_CANDIDATE
production_ready=false
```

C39 contract decision: PASS as IS-controlled guarded candidate formation. C39 forms a non-production guarded candidate that resolves the C37 zero-pick month and branch concentration blocker under C38-derived guards. C39 does not run OOS proof and does not promote the candidate. The candidate may only proceed to C40 IS validation and anti-overfit check.

---

## C44 Contract â€” IS Guard Refinement Candidate Formation

```text
INPUT_C43_HASH_LOCK=41a91ba0447dcf6c0493e1bb27bce6df08fd3490
IS_ONLY_CANDIDATE_FORMATION=true
SAFE_SIGNAL_DATE_FIELDS_ONLY=true
FIXED_MONTHLY_G21_QUOTA=true
C39_MONTH_COVERAGE_GUARD_REQUIRED=true
C39_BRANCH_DIVERSIFICATION_GUARD_REQUIRED=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_TUNING=false
NO_OOS_PROOF=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C43_ARTIFACT_MUTATION=true
CANDIDATE_REQUIRES_C45_VALIDATION=true
production_ready=false
```

Validated result:

```text
C44_STATUS=C44_IS_GUARD_REFINEMENT_CANDIDATE_FORMATION_COMPLETED
BEST_IS_CANDIDATE=C44_G21_MARKET_EXTENSION_CONTROL_FIXED_MONTHLY_QUOTA
ALL_C39_GUARDS_PRESERVED=true
DIAGNOSTIC_CONCLUSION=C44_GUARD_REFINEMENT_CANDIDATE_FORMED
NEXT_STEP=C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT
ARTIFACT_HASH=606cd3109371b0d99419082daee18ff65f1cd99b
FILE_SHA1=4A9A7A915DD37278D9F44634C5D08006B310ED71
```

## C49 Contract - IS Broader Strategy Redesign From C48 Failure Attribution

```text
SOURCE_ARTIFACT_LOCK=C48
EXPECTED_C48_HASH=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7
EXPECTED_C48_FILE_SHA1=EEA350AF2D8A42C881B78701C48A1E301230362C
SOURCE_C48_STATUS_REQUIRED=C48_OOS_FAILURE_ATTRIBUTION_COMPLETED
SOURCE_C48_NEXT_STEP_REQUIRED=C49_BROADER_STRATEGY_REDESIGN
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
IS_BROADER_STRATEGY_REDESIGN_ONLY=true
C48_USED_FOR_HYPOTHESIS_ONLY=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C48_MUTATION=true
NO_C01_TO_C48_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
FUTURE_PATH_PRICE_USED_FOR_SELECTION=false
PROFILE_RET_NET_USED_FOR_SELECTION=false
DERIVED_MFE_MAE_USED_FOR_EXECUTION=false
OOS_DATA_USED_FOR_SELECTION_OR_TUNING=false
C49_MUST_NOT_RECOMMEND_OOS_PROOF=true
C49_MUST_RECOMMEND_C50_IS_VALIDATION_OR_EVIDENCE_EXPANSION_ONLY=true
production_ready=false
```

Required C49 layers:

```text
C48_CARRY_FORWARD_SUMMARY=true
IS_SOURCE_UNIVERSE_SUMMARY=true
BASELINE_C44_COMPARATOR_SUMMARY=true
REDESIGN_PROFILE_RESULTS=true
SHARED_CORE_ESCAPE_ATTRIBUTION=true
BRANCH_G21_QUOTA_FRAGILITY_IS_DIAGNOSTIC=true
REGIME_AWARE_IS_DIAGNOSTIC=true
CONCENTRATION_GUARD_IS_DIAGNOSTIC=true
POST_ENTRY_PATH_IS_DIAGNOSTIC_OR_NOT_EVALUABLE_REASON=true
CANDIDATE_SCORECARD=true
SELECTED_C49_CANDIDATES_FOR_C50=true
C50_READINESS_DECISION=true
CANDIDATE_SAFETY_AUDIT=true
NOT_EVALUABLE_REASONS=true
```

Final operator validation status:

```text
C49_IMPLEMENTATION_STATUS=IMPLEMENTED
C49_PHPUNIT=PASS â€” OK (12 tests, 196 assertions)
FULL_WATCHLIST_PHPUNIT=PASS â€” OK (723 tests, 13647 assertions)
C49_RUNTIME_STATUS=COMPLETED
C49_ARTIFACT_PATH=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json
C49_ARTIFACT_HASH=9266ec2b59a6ea11c21b830cd9b769635afc91a8
C48_HASH_MATCH=true
C49_DIAGNOSTIC_CONCLUSION=C49_IS_REDESIGN_CANDIDATE_READY_FOR_C50_VALIDATION
C49_NEXT_STEP_RECOMMENDATION=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
PRIMARY_CANDIDATE_FOR_C50=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
DEFENSIVE_COMPARATOR_FOR_C50=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
production_ready=false
```

## C50 Contract - IS Validation and Anti-Overfit Check for C49 Redesign

```text
SOURCE_ARTIFACT_LOCK=C49
EXPECTED_C49_HASH=9266ec2b59a6ea11c21b830cd9b769635afc91a8
ACTUAL_C49_HASH=9266ec2b59a6ea11c21b830cd9b769635afc91a8
C49_HASH_MATCH=true
SOURCE_C49_STATUS_REQUIRED=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
SOURCE_C49_STATUS_ACTUAL=C49_BROADER_STRATEGY_REDESIGN_COMPLETED
SOURCE_C49_NEXT_STEP_REQUIRED=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
SOURCE_C49_NEXT_STEP_ACTUAL=C50_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C49_REDESIGN
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json
OUTPUT_ARTIFACT_HASH=1f2b919662a395444f43403e8f7f4d0b91e146aa
IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_ONLY=true
C49_USED_AS_LOCKED_CANDIDATE_SOURCE=true
LOCKED_C49_CANDIDATE_REPLAY_ONLY=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C49_MUTATION=true
NO_C01_TO_C49_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
FUTURE_PATH_PRICE_USED_FOR_SELECTION=false
PROFILE_RET_NET_USED_FOR_SELECTION=false
DERIVED_MFE_MAE_USED_FOR_EXECUTION=false
OOS_DATA_USED_FOR_SELECTION_OR_TUNING_OR_PROOF=false
C50_MUST_NOT_RECOMMEND_OOS_PROOF=true
C50_MUST_RECOMMEND_C51_PRE_OOS_LOCK_REVIEW_OR_IS_EVIDENCE_EXPANSION_ONLY=true
production_ready=false
```

Artifact JSON compatibility contract:

```text
SAFETY_BOUNDARIES_KEY_STYLE=lowercase_snake_case_only
NO_CASE_INSENSITIVE_DUPLICATE_KEYS=true
POWERSHELL_CONVERTFROM_JSON_COMPATIBLE=true
```

Required C50 layers:

```text
C49_CARRY_FORWARD_SUMMARY=true
SOURCE_RECONSTRUCTION_SUMMARY=true
LOCKED_CANDIDATE_REPLAY_RESULTS=true
ROLLING_VALIDATION_RESULTS=true
ROLLING_VALIDATION_SUMMARY=true
LEAVE_ONE_MONTH_OUT_RESULTS=true
LEAVE_ONE_MONTH_OUT_SUMMARY=true
REGIME_ROBUSTNESS_VALIDATION_RESULTS=true
REGIME_ROBUSTNESS_VALIDATION_SUMMARY=true
CONCENTRATION_DEPENDENCY_VALIDATION_RESULTS=true
BRANCH_DEPENDENCY_VALIDATION_RESULTS=true
MATERIAL_DIFFERENCE_VALIDATION=true
SOURCE_RECONSTRUCTION_BIAS_CHECK=true
CANDIDATE_VALIDATION_SCORECARD=true
SELECTED_C50_CANDIDATES_FOR_C51=true
C51_READINESS_DECISION=true
CANDIDATE_SAFETY_AUDIT=true
NOT_EVALUABLE_REASONS=true
```

Current validation status:

```text
C50_IMPLEMENTATION_STATUS=PASS
C50_PHPUNIT=PASS
C50_PHPUNIT_RESULT=OK (12 tests, 218 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (735 tests, 13865 assertions)
C50_RUNTIME_STATUS=COMPLETED
POWERSHELL_CONVERTFROM_JSON=PASS
OPERATOR_VALIDATION_REQUIRED=false
production_ready=false
```

C50 contract outcome:

```text
PRIMARY_CANDIDATE=C49_CANDIDATE_F03_REGIME_AWARE_MARKET_EXTENSION_CONTROL
PRIMARY_CANDIDATE_VALIDATION_PASS=false
PRIMARY_CANDIDATE_FAILURE_REASON=C50_CONCENTRATION_DEPENDENCY_FAIL
PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED=true
DEFENSIVE_COMPARATOR=C49_CANDIDATE_F08_AGGRESSIVE_SHARED_CORE_ESCAPE_REDESIGN
DEFENSIVE_COMPARATOR_VALIDATION_PASS=false
DEFENSIVE_COMPARATOR_FAILURE_REASON=C50_STABILITY_FAIL
C44_SHARED_CORE_COMPARATOR=C49_CANDIDATE_F00_C44_SHARED_CORE_COMPARATOR
C44_SHARED_CORE_COMPARATOR_ROLE=comparator_only_not_redesign_candidate
ROLLING_VALIDATION_PASS=true
LOO_VALIDATION_PASS=true
REGIME_ROBUSTNESS_VALIDATION_PASS=true
MATERIAL_DIFFERENCE_VALIDATION_PASS=true
SOURCE_BIAS_VALIDATION_PASS=true
CONCENTRATION_VALIDATION_PASS=false
ANTI_OVERFIT_PASS=false
DIAGNOSTIC_CONCLUSION=C50_C49_PRIMARY_CANDIDATE_OVERFIT_RISK_IDENTIFIED
NEXT_STEP_RECOMMENDATION=C51_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
production_ready=false
```

C51 contract carry-forward:

```text
C51_MUST_REMAIN_IS_ONLY=true
C51_MUST_REVIEW_CONCENTRATION_DEPENDENCY=true
C51_MUST_TREAT_F03_AS_PROMISING_BUT_OVER_CONCENTRATED=true
C51_MUST_USE_F08_AS_DIVERSIFICATION_TEMPLATE_ONLY=true
C51_MUST_KEEP_F00_C44_AS_COMPARATOR_ONLY=true
C51_MUST_REDUCE_G16_DOMINANCE=true
C51_MUST_NOT_USE_OOS_RETURN_FOR_SELECTION=true
C51_MUST_NOT_OPEN_OOS_PROOF=true
```

## C52 Contract â€” Sector Reconstruction and Second-Pass Concentration Redesign

```text
CONTRACT=C52_IS_ONLY_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
C51_ARTIFACT_HASH_LOCK=true
C50_ARTIFACT_HASH_LOCK=true
C49_ARTIFACT_HASH_LOCK=true
C51_C50_C49_LOCKED_LINEAGE=true
SECTOR_METADATA_ASOF_SAFE_REQUIRED=true
SECTOR_METADATA_EXACT_DATE_INDICATOR_SOURCE=true
SECTOR_METADATA_EFFECTIVE_DATED_MEMBERSHIP_FALLBACK=true
NO_FUTURE_MAX_DATE_LOOKUP=true
NO_DUMMY_SECTOR=true
SECTOR_NOT_EVALUABLE_DISTINCT_FROM_TRUE_FAIL=true
REDESIGN_CANDIDATES_DETERMINISTIC=true
G21_PRIMARY_BACKFILL=true
G13_LIMITED_FILLER=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_TUNING=false
OOS_RETURN_USED_FOR_SELECTION=false
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C51_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
```

Allowed C53 routes:

```text
C53_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C52_REDESIGN
C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN
C53_SECTOR_METADATA_EVIDENCE_EXPANSION_REQUIRED
C53_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
C53_SHARED_CORE_REVERSION_REDESIGN_REQUIRED
C53_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY
```

C52 cannot recommend OOS proof. The final runtime repaired sector reconstruction and identified 14 concentration-pass candidates, but selected none because the complete rolling/LOO/regime/material-difference/anti-overfit stack did not pass.

```text
C52_CONTRACT_RESULT=PASS_TECHNICAL_GUARDS
C52_SECTOR_METADATA_RECONSTRUCTION_PASS=true
C52_STRATEGY_RESULT=NO_C53_READY_CANDIDATE
C52_NEXT_STEP=C53_IS_EVIDENCE_EXPANSION_FOR_C52_REDESIGN
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
production_ready=false
```

## C53 Contract â€” IS Evidence Expansion for C52 Redesign

```text
C52_ARTIFACT_HASH_LOCK=true
C52_FILE_SHA1_LOCK=true
C52_USED_AS_LOCKED_EVIDENCE_SOURCE=true
C51_C50_C49_LINEAGE_CARRIED_FORWARD=true
IS_ONLY_VALIDATION=true
STRUCTURAL_COHORT_NO_RETURN_SELECTION=true
NO_NEW_CANDIDATE_FORMATION=true
NO_CANDIDATE_WINNER=true
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_TUNING=false
OOS_RETURN_USED_FOR_SELECTION=false
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C52_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
```

Final contract outcome:

```text
C53_EVIDENCE_EXPANSION_COMPLETED=true
C53_PRIMARY_GAP=ROLLING_STABILITY
C53_READY_CANDIDATE_COUNT=0
C53_NEXT_STEP=C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY
C53_MUST_NOT_RECOMMEND_OOS_PROOF=true
```

---

## C51 Contract â€” IS-only Concentration/Dependency Redesign Review

```text
C51_CONTRACT_STATUS=IMPLEMENTED_OPERATOR_VALIDATED
C51_SCOPE=IS_ONLY_CONCENTRATION_DEPENDENCY_REDESIGN_REVIEW
C50_ARTIFACT_HASH_LOCK=true
C49_ARTIFACT_HASH_LOCK=true
C50_USED_AS_LOCKED_VALIDATION_SOURCE=true
C49_USED_AS_LOCKED_CANDIDATE_SOURCE=true
REDESIGN_CANDIDATES_MUST_BE_DETERMINISTIC=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PROMOTION=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C50_MUTATION=true
NO_C01_TO_C50_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
production_ready=false
RETURN_USED_FOR_SELECTION=false
FUTURE_PATH_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_SELECTION=false
OOS_DATA_USED_FOR_TUNING=false
OOS_DATA_USED_FOR_PROOF=false
C51_MUST_NOT_RECOMMEND_OOS_PROOF=true
```

Allowed C51 next steps:

```text
C52_IS_VALIDATION_AND_PRE_OOS_LOCK_REVIEW_FOR_C51_REDESIGN
C52_IS_EVIDENCE_EXPANSION_FOR_C51_REDESIGN
C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
C52_SHARED_CORE_REVERSION_REDESIGN_REQUIRED
C52_REDESIGN_OR_RECALIBRATION_REQUIRED_IS_ONLY
```

Disallowed C51 next steps:

```text
DIRECT_OOS_PROOF
OOS_PROOF_RERUN
BEST_OF_OOS
OOS_WINNER
PRODUCTION_ROLLOUT
CATALOG_PROMOTION
```

C51 candidate contract:

```text
C51_R00_C50_F03_LOCKED_PRIMARY_REPLAY=comparator_replay_only
C51_R01_F03_BRANCH_CAP_70=deterministic_branch_cap_redesign
C51_R02_F03_BRANCH_CAP_65=deterministic_branch_cap_redesign
C51_R03_F03_BUCKET_CAP_70=deterministic_bucket_cap_redesign
C51_R04_F03_BUCKET_CAP_65=deterministic_bucket_cap_redesign
C51_R05_F03_G16_DOWNSAMPLED_G21_BACKFILL=deterministic_downsample_backfill
C51_R06_F03_G16_DOWNSAMPLED_G21_G13_BACKFILL=deterministic_downsample_backfill
C51_R07_F03_F08_HYBRID_DIVERSIFIED_BRANCH_MIX=deterministic_hybrid_mix
C51_R08_F03_BRANCH_QUOTA_CONTROL=predeclared_branch_quota
C51_R09_F03_BUCKET_CONCENTRATION_CONTROL=predeclared_bucket_quota
C51_R10_F03_LOSS_CLUSTER_CONTROL=predeclared_ticker_sector_exposure_cap_no_return_rank
C51_R11_F03_F08_QUALITY_WEIGHTED_DIVERSIFIED_MIX=safe_pre_trade_ordering
C51_R12_F08_STABILITY_REPAIR_VARIANT=deterministic_f08_repair_variant
C51_R13_C44_F00_ANCHOR_COMPARATOR_ONLY=comparator_only
```

C51 safety artifact contract:

```text
SAFETY_BOUNDARIES_KEY_STYLE=lowercase_snake_case_only
NO_CASE_INSENSITIVE_DUPLICATE_KEYS=true
POWERSHELL_CONVERTFROM_JSON_COMPATIBLE=true
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
production_ready=false
```


C51 final operator validation:

```text
C51_IMPLEMENTATION_STATUS=IMPLEMENTED_FINAL
C51_PHPUNIT_STATUS=PASS
C51_PHPUNIT_RESULT=OK (14 tests, 378 assertions)
FULL_WATCHLIST_PHPUNIT_STATUS=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (749 tests, 14243 assertions)
C51_ARTISAN_RUNTIME_STATUS=COMPLETED
ARTIFACT_PATH=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json
C51_ARTISAN_REPORTED_ARTIFACT_HASH=a786034b8e344207592e58efe262287102b0ef36
C51_FILE_SHA1=0BFAD3BC9985602E1FE6318557754ECBE9A63F91
status=C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED
diagnostic_conclusion=C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS
next_step_recommendation=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
production_ready=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
```

C51 final contract outcome:

```text
best_redesigned_candidate_code=null
best_redesigned_profile_code=null
best_redesigned_candidate_pass=false
selected_candidate_count=0
primary_dependency_reduced=false
concentration_validation_pass=false
rolling_validation_pass=false
loo_validation_pass=false
regime_robustness_validation_pass=false
material_difference_validation_pass=false
source_bias_validation_pass=true
anti_overfit_pass=false
c52_recommendation=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
decision_reason=concentration_dependency_issue_remains
diagnostic_conclusion=C51_REDESIGNED_CANDIDATE_OVERFIT_RISK_REMAINS
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

Contract interpretation:

```text
C51_CONTRACT_RESULT=PASS_TECHNICAL_GUARDS
C51_STRATEGY_RESULT=NO_C52_READY_CANDIDATE
C51_MUST_CONTINUE_IS_ONLY=true
C51_NEXT_STEP=C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION
C51_MUST_NOT_OPEN_OOS_PROOF=true
```

## C54 Contract â€” Rolling Stability Redesign or Recalibration (IS Only)

```text
SOURCE_ARTIFACT_LOCK=C53_AND_C52_STABLE_HASH_AND_FILE_SHA1
VALIDATION_COMMAND=watchlist:backtest-c54-rolling-stability-redesign-or-recalibration-is-only
OUTPUT_ARTIFACT=storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json
IS_ONLY_REDESIGN=true
C53_ADVERSE_MONTHS_DIAGNOSTIC_ONLY=true
ADVERSE_MONTH_EXCLUSION_RULE_FORBIDDEN=true
TICKER_SECTOR_EXCLUSION_RULE_FORBIDDEN=true
SAFE_PRE_TRADE_QUOTA_AND_CAP_FORMATION_REQUIRED=true
RETURN_RANKED_FORMATION_FORBIDDEN=true
NO_GATE_RELAXATION=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_PRODUCTION_READINESS=true
C54_REDESIGNED_CANDIDATE_COUNT=11
C54_FULL_ROLLING_PASS_COUNT=0
C54_BEST_ROLLING_PASS_RATE=0.9833333333333333
C54_CANDIDATE_READY_FOR_C55_COUNT=0
C54_DIAGNOSTIC_CONCLUSION=C54_ROLLING_STABILITY_GAP_REMAINS
C54_NEXT_STEP=C55_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY
production_ready=false
```

## C55 Contract â€” Rolling Stability Redesign Continuation (IS Only)

C55 adds the following contract surface:

```text
IS_ONLY_ROLLING_STABILITY_REDESIGN_CONTINUATION=true
C54_ARTIFACT_HASH_LOCK=true
C54_FILE_SHA1_LOCK=true
C53_ARTIFACT_HASH_LOCK=true
C53_FILE_SHA1_LOCK=true
C52_ARTIFACT_HASH_LOCK=true
C52_FILE_SHA1_LOCK=true
C54_C53_C52_USED_AS_LOCKED_LINEAGE=true
NEAR_PASS_ROLLING_ATTRIBUTION_DIAGNOSTIC_ONLY=true
FAILED_WINDOWS_MUST_NOT_BECOME_EXCLUSION_RULES=true
ADVERSE_MONTHS_MUST_NOT_BECOME_EXCLUSION_RULES=true
REDESIGN_CANDIDATES_MUST_BE_DETERMINISTIC=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C54_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
PRODUCTION_READY_REMAINS_FALSE=true
RETURN_NOT_USED_FOR_SELECTION=true
FUTURE_PATH_NOT_USED_FOR_SELECTION=true
OOS_DATA_NOT_USED_FOR_SELECTION_TUNING_PROOF=true
C55_MUST_NOT_RECOMMEND_OOS_PROOF=true
```

Allowed C55 next steps are limited to C56 IS validation / pre-OOS lock review, C56 IS evidence expansion, C56 rolling-stability continuation, C56 concentration continuation, C56 shared-core reversion redesign, or C56 IS-only recalibration. C55 must never jump directly to OOS proof.

C55 final operator evidence:

```text
PHPUNIT_C55=PASS
PHPUNIT_C55_RESULT=OK (9 tests, 293 assertions)
FULL_WATCHLIST_PHPUNIT=PASS
FULL_WATCHLIST_PHPUNIT_RESULT=OK (786 tests, 15445 assertions)
C55_RUNTIME=COMPLETED
C55_ARTIFACT_PATH=storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json
C55_ARTIFACT_HASH=a4145d6f356e678d0dadf95be5d356198ebfed79
C55_FILE_SHA1=18875FCAD7FD7CDA6607BB09A60917E853E68D2B
CANDIDATE_READY_FOR_C56_COUNT=0
ROLLING_VALIDATION_PASS_CANDIDATE_COUNT=0
CONCENTRATION_VALIDATION_PASS_CANDIDATE_COUNT=0
DIAGNOSTIC_CONCLUSION=C55_ROLLING_STABILITY_GAP_REMAINS
NEXT_STEP=C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY
PRODUCTION_READY=false
```

## C56 Contract â€” Rolling Stability Redesign Continuation (IS Only)

C56 adds the following contract surface:

```text
IS_ONLY_ROLLING_STABILITY_REDESIGN_CONTINUATION=true
C55_ARTIFACT_HASH_LOCK=true
C55_FILE_SHA1_LOCK=true
C54_ARTIFACT_HASH_LOCK=true
C54_FILE_SHA1_LOCK=true
C53_ARTIFACT_HASH_LOCK=true
C53_FILE_SHA1_LOCK=true
C52_ARTIFACT_HASH_LOCK=true
C52_FILE_SHA1_LOCK=true
C55_C54_C53_C52_USED_AS_LOCKED_LINEAGE=true
NEAR_PASS_ROLLING_ATTRIBUTION_DIAGNOSTIC_ONLY=true
FAILED_WINDOWS_MUST_NOT_BECOME_EXCLUSION_RULES=true
ADVERSE_MONTHS_MUST_NOT_BECOME_EXCLUSION_RULES=true
REGIME_FIELD_RECONSTRUCTION_ASOF_SAFE=true
SOURCE_RECONSTRUCTION_MUST_NOT_USE_MAX_TRADE_DATE=true
REDESIGN_CANDIDATES_MUST_BE_DETERMINISTIC=true
NO_OOS_TUNING=true
NO_OOS_PROOF=true
NO_OOS_PROOF_RERUN=true
NO_BEST_OF_OOS=true
NO_OOS_WINNER=true
NO_CANDIDATE_RESELECTION_FROM_OOS=true
NO_PROFILE_RESELECTION_FROM_OOS=true
NO_PRODUCTION_CATALOG=true
NO_PLAN_CONFIRM_MUTATION=true
NO_C01_TO_C55_ARTIFACT_MUTATION=true
CANDIDATE_IS_NOT_PRODUCTION=true
PRODUCTION_READY_REMAINS_FALSE=true
RETURN_NOT_USED_FOR_SELECTION=true
FUTURE_PATH_NOT_USED_FOR_SELECTION=true
OOS_DATA_NOT_USED_FOR_SELECTION_TUNING_PROOF=true
C56_MUST_NOT_RECOMMEND_OOS_PROOF=true
```

Allowed C56 next steps are limited to C57 IS validation / pre-OOS lock review, C57 IS evidence expansion, C57 rolling-stability continuation, C57 concentration/loss-cluster continuation, C57 regime reconstruction continuation, C57 shared-core reversion redesign, or C57 IS-only recalibration. C56 must never jump directly to OOS proof.


### C56 Final Contract Validation Result

```text
C56_CONTRACT_STATUS=PASS_FOR_TECHNICAL_AND_BOUNDARY_VALIDATION
C56_STRATEGY_UNLOCK_STATUS=NOT_UNLOCKED
C56_PHPUNIT_STATUS=PASS
FULL_WATCHLIST_PHPUNIT_STATUS=PASS
C56_RUNTIME_STATUS=COMPLETED
C56_ARTIFACT_HASH=f7edab247dc824dcd33a15f00575dd04f76f4786
C55_ARTIFACT_HASH_LOCK=PASS
C55_FILE_SHA1_LOCK=PASS
C54_ARTIFACT_HASH_LOCK=PASS
C54_FILE_SHA1_LOCK=PASS
C53_ARTIFACT_HASH_LOCK=PASS
C53_FILE_SHA1_LOCK=PASS
C52_ARTIFACT_HASH_LOCK=PASS
C52_FILE_SHA1_LOCK=PASS
C55_C54_C53_C52_USED_AS_LOCKED_LINEAGE=PASS
NEAR_PASS_ROLLING_ATTRIBUTION_DIAGNOSTIC_ONLY=PASS
FAILED_WINDOWS_MUST_NOT_BECOME_EXCLUSION_RULES=PASS
ADVERSE_MONTHS_MUST_NOT_BECOME_EXCLUSION_RULES=PASS
REGIME_FIELD_RECONSTRUCTION_ASOF_SAFE=PASS_FOR_ASOF_SAFETY_BUT_NOT_FULLY_EVALUABLE
SOURCE_RECONSTRUCTION_MUST_NOT_USE_MAX_TRADE_DATE=PASS
NO_OOS_TUNING=PASS
NO_OOS_PROOF=PASS
NO_OOS_PROOF_RERUN=PASS
NO_BEST_OF_OOS=PASS
NO_OOS_WINNER=PASS
NO_CANDIDATE_RESELECTION_FROM_OOS=PASS
NO_PROFILE_RESELECTION_FROM_OOS=PASS
NO_PRODUCTION_CATALOG=PASS
NO_PLAN_CONFIRM_MUTATION=PASS
NO_C01_TO_C55_ARTIFACT_MUTATION=PASS
CANDIDATE_IS_NOT_PRODUCTION=PASS
PRODUCTION_READY_REMAINS_FALSE=PASS
RETURN_NOT_USED_FOR_SELECTION=PASS
FUTURE_PATH_NOT_USED_FOR_SELECTION=PASS
OOS_DATA_NOT_USED_FOR_SELECTION_TUNING_PROOF=PASS
C56_MUST_NOT_RECOMMEND_OOS_PROOF=PASS
```

C56 complies with boundary contracts but does not unlock strategy readiness. The final next step remains IS-only:

```text
diagnostic_conclusion=C56_REGIME_FIELD_RECONSTRUCTION_GAP_REMAINS
next_step_recommendation=C57_REGIME_FIELD_RECONSTRUCTION_CONTINUATION_IS_ONLY
candidate_ready_for_c57_count=0
rolling_validation_pass_candidate_count=4
concentration_validation_pass_candidate_count=0
loss_cluster_pass_candidate_count=0
regime_fully_evaluable=false
direct_oos_proof_recommended=false
oos_proof_unlocked=false
production_ready=false
```

---

## C58 contract â€” loss-cluster/concentration redesign continuation IS-only

Status: `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

- `WL-CONTRACT-C58-001`: C58 must run only on IS `2023-01-02..2025-05-21`; reserved OOS `2025-05-22..2026-05-29` must not be requested.
- `WL-CONTRACT-C58-002`: C58 must lock C57 artifact hash `71230896c2121fcfedddf36dd54c9c03ad462b4d` and file SHA1 `50272917A107E304F8EEEB874DBC02A881DB0C31`.
- `WL-CONTRACT-C58-003`: C58 must enforce the database dictionary read rule before DB-connected implementation assumptions are accepted.
- `WL-CONTRACT-C58-004`: C58 must retain C57 regime completeness: `required_field_count=9`, `evaluable_field_count=9`, `missing_field_count=0`, `regime_fully_evaluable=true`.
- `WL-CONTRACT-C58-005`: C58 must not repeat market-index reconstruction; mapping remains dictionary-locked to `market_benchmark_indicators.roc_20`, `market_benchmark_indicators.ma20_slope_pct`, `benchmark_code='IHSG'`, and `market_calendar.cal_date`.
- `WL-CONTRACT-C58-006`: C58 must create controlled Track A, Track B, replay comparator, and hybrid candidates from C56/C57 lineage.
- `WL-CONTRACT-C58-007`: C58 must compute concentration/loss-cluster metrics for every candidate.
- `WL-CONTRACT-C58-008`: C58 must re-evaluate rolling, leave-one-month-out, regime robustness, material-difference, and anti-shared-core gates.
- `WL-CONTRACT-C58-009`: C58 must keep `production_ready=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.
- `WL-CONTRACT-C58-010`: If no candidate passes all IS gates, C58 must recommend an IS-only C59 continuation and identify the dominant blocker.

Allowed C59 recommendations from C58:

```text
C59_PRE_LOCK_IS_REVIEW_FOR_C58_CANDIDATE_IS_ONLY
C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
C59_SAMPLE_RECOVERY_WITH_CONCENTRATION_GUARD_IS_ONLY
C59_LOO_DEPENDENCY_REDESIGN_CONTINUATION_IS_ONLY
C59_REGIME_ROBUSTNESS_REDESIGN_CONTINUATION_IS_ONLY
C59_ROLLING_STABILITY_RECOVERY_IS_ONLY
```

Forbidden C58 outcomes:

```text
OOS proof unlocked
Direct OOS proof recommended
Production-ready claim
Production catalog creation
PLAN/CONFIRM mutation
C01-C57 artifact mutation
Gate relaxation
Return/future-path/OOS-return selection
Adverse-month, failed-window, ticker, or sector hard exclusion from failure attribution
```


### C58 final contract validation

Status: `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

- `WL-CONTRACT-C58-001`: PASS. Runtime stayed within IS `2023-01-02..2025-05-21`; OOS rows requested = `0`.
- `WL-CONTRACT-C58-002`: PASS. C57 artifact hash and file SHA1 matched the locked values.
- `WL-CONTRACT-C58-003`: PASS. Database dictionary read rule was recorded; missing dictionary coverage was not detected.
- `WL-CONTRACT-C58-004`: PASS. C57 regime completeness was retained: required `9`, evaluable `9`, missing `0`, fully evaluable `true`.
- `WL-CONTRACT-C58-005`: PASS. C58 did not repeat market-index reconstruction and retained C57 market-index reconstruction evidence.
- `WL-CONTRACT-C58-006`: PASS. C58 generated 10 controlled candidates from replay comparator, Track A, Track B, and hybrid lineage.
- `WL-CONTRACT-C58-007`: PASS. Concentration/loss-cluster metrics were computed for every candidate.
- `WL-CONTRACT-C58-008`: PASS. Rolling, LOO, regime robustness, material-difference, and anti-shared-core gates were re-evaluated.
- `WL-CONTRACT-C58-009`: PASS. `production_ready=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.
- `WL-CONTRACT-C58-010`: PASS. No candidate passed all IS gates; C58 recommends `C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY`.

Final C58 validation markers:

```text
PHPUNIT_C58=PASS OK (12 tests, 430 assertions)
FULL_WATCHLIST_PHPUNIT=PASS OK (817 tests, 16397 assertions)
C58_RUNTIME=COMPLETED
C58_ARTIFACT_HASH=80d09de8053659bf01ce5b8b72d9e2d82cdf69dc
C58_FILE_SHA1=FA6FE27604F6CDA664DCF90A251AF41672670700
CANDIDATE_READY_FOR_C59_COUNT=0
ROLLING_VALIDATION_PASS_CANDIDATE_COUNT=4
CONCENTRATION_VALIDATION_PASS_CANDIDATE_COUNT=0
LOSS_CLUSTER_PASS_CANDIDATE_COUNT=0
LOO_VALIDATION_PASS_CANDIDATE_COUNT=0
REGIME_ROBUSTNESS_PASS_CANDIDATE_COUNT=0
DIAGNOSTIC_CONCLUSION=C58_LOSS_CLUSTER_GAP_REMAINS
NEXT_STEP=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY
```

## C59 contract â€” loss-cluster or branch/bucket redesign continuation IS-only

Status: `DONE_OPERATOR_VALIDATED_NOT_PRODUCTION_READY`

- `WL-CONTRACT-C59-001`: C59 must run only on IS `2023-01-02..2025-05-21`; reserved OOS `2025-05-22..2026-05-29` must not be requested.
- `WL-CONTRACT-C59-002`: C59 must lock C58 artifact hash `80d09de8053659bf01ce5b8b72d9e2d82cdf69dc` and file SHA1 `FA6FE27604F6CDA664DCF90A251AF41672670700`.
- `WL-CONTRACT-C59-003`: C59 must enforce the database dictionary read rule before DB-connected assumptions are accepted.
- `WL-CONTRACT-C59-004`: C59 must retain C57 regime completeness through C58 lock: `required_field_count=9`, `evaluable_field_count=9`, `missing_field_count=0`, `regime_fully_evaluable=true`.
- `WL-CONTRACT-C59-005`: C59 must not repeat market-index reconstruction; mappings remain dictionary-locked to `market_benchmark_indicators.roc_20`, `market_benchmark_indicators.ma20_slope_pct`, `benchmark_code='IHSG'`, and `market_calendar.cal_date`.
- `WL-CONTRACT-C59-006`: C59 must include the C58 blocker summary and start from C58 candidate lineage.
- `WL-CONTRACT-C59-007`: C59 must create controlled replay, Track A, Track B, Track C, Track D, and hybrid candidates.
- `WL-CONTRACT-C59-008`: C59 must compute loss-cluster metrics for every candidate.
- `WL-CONTRACT-C59-009`: C59 must compute concentration metrics for every candidate.
- `WL-CONTRACT-C59-010`: C59 must re-evaluate rolling, leave-one-month-out, regime robustness, sample recovery, material-difference, and anti-shared-core gates.
- `WL-CONTRACT-C59-011`: C59 must not use return fields, future path, OOS rows, or OOS returns for selection.
- `WL-CONTRACT-C59-012`: C59 must not use adverse-month exclusion, failed-window exclusion, ticker hard exclusion, or sector hard exclusion from failure attribution.
- `WL-CONTRACT-C59-013`: Replay comparators must not be promoted.
- `WL-CONTRACT-C59-014`: C59 must keep `production_ready=false`, `direct_oos_proof_recommended=false`, and `oos_proof_unlocked=false`.
- `WL-CONTRACT-C59-015`: If no candidate passes all IS gates, C59 must recommend an IS-only C60 continuation and identify the dominant blocker.

Allowed C60 recommendations from C59:

```text
C60_PRE_LOCK_IS_REVIEW_FOR_C59_CANDIDATE_IS_ONLY
C60_SAMPLE_RECOVERY_WITH_LOSS_CLUSTER_GUARD_IS_ONLY
C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY
C60_CANDIDATE_FAMILY_RESET_WITH_STRICT_GUARDS_IS_ONLY
C60_SAMPLE_RECOVERY_WITH_BRANCH_BUCKET_GUARD_IS_ONLY
C60_ROLLING_STABILITY_RECOVERY_IS_ONLY
```

Forbidden C59 outcomes:

```text
OOS proof unlocked
Direct OOS proof recommended
Production-ready claim
Production catalog creation
PLAN/CONFIRM mutation
C01-C58 artifact mutation
Gate relaxation
Return/future-path/OOS-return selection
Adverse-month, failed-window, ticker, or sector hard exclusion from failure attribution
Replay comparator promotion
```

Sandbox C59 contract smoke evidence:

```text
C59_STATUS=C59_LOSS_CLUSTER_OR_BRANCH_BUCKET_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED
C59_DIAGNOSTIC_CONCLUSION=C59_REGIME_ROBUSTNESS_GAP_REMAINS
C59_NEXT_STEP=C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY
CANDIDATE_READY_FOR_C60_COUNT=0
PRODUCTION_READY=false
DIRECT_OOS_PROOF_RECOMMENDED=false
OOS_PROOF_UNLOCKED=false
```

## C60 Contract Tracker â€” Regime Stress and LOO Dependency Redesign IS-Only

Contract code:

`C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY`

Contract status: implemented, IS-only, operator validation required.

Required locked input:

- C59 artifact: `storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json`
- expected C59 lock: `7ebd6f74bc90ffac358b410244d90b3c7c3c5456`

Safety contract:

- no OOS proof
- no OOS rows
- no future lookup
- no return/future path used for selection
- no production catalog
- no PLAN/CONFIRM mutation
- no gate relaxation
- no bad-month deletion
- no weak-regime removal
- no hard ticker/sector exclusion from failure attribution
- no replay comparator promotion
- database dictionary read rule mandatory

Artifact contract:

- path: `storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json`
- artifact hash: `4d3ae77bd79b73392cea17b8ca7b0720d950f55b`
- status: `C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED`
- reason: `C60_WEAK_REGIME_RETURN_SURVIVAL_GAP_REMAINS`

Gate result summary:

- candidate ready for C61: 0
- concentration validation pass: 10
- regime-aware concentration pass: 10
- loss-cluster validation pass: 10
- LOO validation pass: 7
- rolling validation pass: 4
- weak-regime sample recovery pass: 9
- weak-regime survival pass: 0
- regime robustness pass: 0

Contract conclusion:

C60 does not unlock OOS. C61 remains IS-only and should rebuild signal quality for `market_down_or_sideways_high_vol`.

---
