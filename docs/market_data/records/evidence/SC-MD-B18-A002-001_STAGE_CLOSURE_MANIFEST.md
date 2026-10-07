# MD Stage Closure Manifest — SC-MD-B18-A002-001

- ID: `SC-MD-B18-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20261005-001`
- Change Impact Declaration: `CI-MD-B18-A002-001` (successor amendment of section 3 by `D-MD-B18-A002-022`)
- Governed evidence: `E-MD-B18-A002-001` (atomic binder evidence and raw manifest); `E-MD-B18-A002-117` (post-binding); `E-MD-B18-A002-122` (closure controls and final completion)
- Reviewed decisions: `D-MD-B18-A002-022` (F-011 Option 2), `D-MD-B18-A002-019` (candidate-v5 approval), `D-MD-B18-A002-004`, `D-MD-B18-A002-003`, `D-MD-B18-A002-020`, `D-MD-B18-A002-021`
- Predecessor closure: `SC-MD-B18-A001-001` (withdrawn by `F-MD-B19-A001-002`; not inherited)
- Dependencies: `MD-DEP-0009`, `MD-DEP-0015`, `MD-DEP-0017` RESOLVED by this closure; `MD-DEP-0016` and `MD-DEP-0024` OPEN_NON_BLOCKING
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, immutable after issue
- Issued at: 2026-10-08T04:48:29+07:00

## Terminal coverage

- Mandatory denominator: **113**; mandatory `SATISFIED`: **113/113**, atomically bound to `E-MD-B18-A002-001` (113 rows changed, 0 foreign, independently recomputed in `E-MD-B18-A002-117`)
- Reviewed proof bases: **113/113 PROVEN**, 0 INCOMPLETE; conditional not applicable: **4** (each with its false-condition basis); optional capability: **2**; reference/context: **33**; `NOT_ASSESSED`: **0**
- Matrix sha256 `8b5bd9b1cb2d47022e6f003fef251d9160ef660b35e2813b92c297e7708e0695`; candidate-v5 fingerprint `daf96a296875df875539c196e997823e99ee4171d6d9d77af8d213f13a05fbe3` (reviewed `E-MD-B18-A002-114`, owner-approved `D-MD-B18-A002-019`, admitted `E-MD-B18-A002-115`); frozen build `sha256:7ea1956a36fe3ad773479bb249f1409b6fa04df8725576080284294ed8016c4a`

## Regression — stated as it is

- Fresh MarketData suite for this closure: **2960 tests, 48530 assertions, 0 errors, 0 skipped, 7 failures**. This is a FAILED full suite, not a full-suite PASS. The seven failures are all `ProductionCorpusInvariantOracleTest` deployed-corpus controls (no deployed corpus exists in the database); every other test passes.
- B18 closure acceptance is judged under `D-MD-B18-A002-022` item 6 (0 errors, 0 skips, every test outside that class passes, the seven failures recorded as real failures), not by calling the suite green. The earlier runs `E-MD-B18-A002-001` and `E-MD-B18-A002-117` record the same seven failures and stay exactly as issued.
- The oracle test is unchanged; its obligation is carried by `MD-DEP-0024` (owner `MD-B22`, OPEN_NON_BLOCKING). Option 1 recovery of the old corpus ended at `E-MD-B18-A002-121` (normal startup failed); `innodb_force_recovery` was not used and is not required.

## Closure conditions

| Condition | Result |
|---|---|
| Zero transitional required rows | **MET — 0** |
| Zero pending applicability rows | **MET — 0** |
| Complete applicable denominator | **MET — 113/113** |
| Conditional-not-applicable basis | **MET — 4/4** |
| Context binding and normalized predicate | **MET — every required row** |
| Every row names its own guard pair with a reviewed basis | **MET — 113/113** |
| No foreign row carries this stage's evidence | **MET — 0** |
| Raw artifact integrity and governed evidence reachable | **MET** — `MANIFEST.json` (8 artifacts) bound by `E-MD-B18-A002-001`; closure-controls manifest `e5239c9005a8a2a4b2c2a177881956ce576125e9e72301c87b79b274f0b6de43` bound by `E-MD-B18-A002-122` |
| Closure gate conditions mutation-proven | **MET** — all ten conditions: 21 data scenarios, 17 protection removals, 9 matrix/basis scenarios (`F-MD-B18-A002-005` resolved) |
| Proof gate `--bound`; reviewed bases | **MET** — PASS; 113/113 reviewed (the binder validate-only was PASS 113/113 immediately before the apply; afterwards pre-binding-mode checks report PREMATURE_BINDING by design) |
| Suite acceptance under `D-MD-B18-A002-022` | **MET** — see above |
| Residue verdict | `CONFORMANT_WITH_CONTROLLED_COMPATIBILITY` |
| Findings and dependencies reconciled | **MET** — see below |

## Residue

`CONFORMANT_WITH_CONTROLLED_COMPATIBILITY`

- The six B18 surfaces (`ReplayVerificationService`, `ReplayBackfillService`, `ReplaySmokeSuiteService`, `FullRangeCurrentEvidenceReplayService`, `ReplayResultRepository`, `VerifyReplayCommand`) have no reachable current/latest fallback, live-configuration read or adjusted-close fallback in the replay and backfill paths; every test that references them passes in the fresh run.
- Controlled compatibility: `FullRangeCurrentEvidenceReplayService` deliberately replays the CURRENT publications and generates self-generated fixtures, which the verifier refuses as independent proof; V1-profile historical publications keep their interpretation; the activated-world freshness evaluator does not exist (`F-MD-B18-A002-033`, pre-activation candidate).
- Limits: static and test-based review; the probes live in the 113 predicate bases.

## Findings and dependencies at closure

- Resolved by this closure: `MD-DEP-0015`, `MD-DEP-0017`, `MD-DEP-0009`, `F-MD-B18-A002-005`, `F-MD-B18-A002-017`.
- Open and closure-compatible: `MD-DEP-0016` (old-data recovery, retained), `MD-DEP-0024` (deployed-corpus validation, MD-B22), `MD-DEP-0010` (MD-B21), `MD-DEP-0011` (MD-B22), `F-MD-B18-A002-003` (MD-B21), `F-MD-B18-A002-004` (MD-B22), `F-MD-B18-A002-011` (deferral to MD-B22), `F-MD-B18-A002-033` (MD-B17 and MD-B10 successors).

## Boundaries carried forward, not closed here

- `MD-B17` (`244/257`, 13 rows not assessed) is a separate bounded work unit (`MD-B17-A003`) and not a prerequisite of this closure.
- `MD-B22` owns the deployed-corpus controls through `MD-DEP-0024`; it is not opened.
- The old data directory `D:\xampp\mysql\data_260914` and the byte-copy `D:\tradeaxis_recovery\data_260914_copy\` are retained untouched.
- Historical evidence (candidate-v1 to v5 chains, E102/E103, the B04-A003 history, earlier attempts and baseline locks) is preserved as issued.

## Resume

`MD-B18` is closed under `MD-B18-A002`. Return to `MD-B19-A001` (`MD-DEP-0009` resolved).
