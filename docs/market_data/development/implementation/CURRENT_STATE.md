# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **4038**
- Coverage denominator: **4009** (FINAL)
- SATISFIED: **3068**
- NOT_ASSESSED inside denominator: **941**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **29 / 29**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **0 / 0**
- Transitional MANDATORY_OR_CONDITIONAL: **0**
- Verified coverage: **76.53% FINAL**
- Optional capability rules: **63**

## Current executable stage

- Stage: `MD-B18`
- Latest attempt / baseline: `MD-B18-A002` / `MD-B18-A002-BL001`
- State / verdict: `IN_PROGRESS` / `BLOCKED — MD-DEP-0015 (full suite waits on the corpus oracle via MD-DEP-0016); MD-DEP-0017 approved consolidated remediation execution (F-013/F-014/F-015/F-016/F-017/F-018); per-predicate review complete, consolidated remediation package drafted; Q1-Q6 approved by D005; Q6 proven E017 and Q5 ownership aligned E018; Q1-Q4 executable proof outstanding; A001 closure remains withdrawn`
- Residue/rework: `INCONCLUSIVE_RESIDUE_EVIDENCE`
- Dependency: `MD-DEP-0009` blocks B19; `MD-DEP-0014` RESOLVED by D003; **`MD-DEP-0015`** BLOCKING — owner-rebuilt instance verified; full suite fails only on the corpus oracle (F-011(b) → MD-DEP-0016); `MD-DEP-0016` old-data recovery OPEN_NON_BLOCKING; **`MD-DEP-0017`** BLOCKING — approved consolidated remediation execution (F-013/F-014/F-015/F-016/F-017/F-018); `MD-DEP-0012/0013` RESOLVED; `MD-DEP-0010/0011` remain downstream obligations
- Open finding: `F-MD-B18-A002-003` OPEN deferred B21; `004` PARTIALLY_RESOLVED remainder B22; `005` OPEN; `006` RESOLVED complete-parent normalization; `007` RESOLVED availability; `008` RESOLVED (D003 + executed-corpus aggregate); `009` RESOLVED (re-probed); `010` RESOLVED; **`011` PARTIALLY_RESOLVED** by D004: orphan subset correction probed; corpus oracle waits for the MD-DEP-0016 recovery; `012` RESOLVED — owner-rebuilt instance verified (E010); **`013` OPEN** — publication replay binds empty temporal/calendar identities and a nominal reason-registry identity; C1 review accepted; calendar/registry capture and diagnostic completeness implemented/probed (E022; test corrections E023); C02/C03 temporal/provider proof E024 and C05 status population/selection/omission proof E025, C06 consumed source population/selection/completeness proof E027; C07 RAW lineage E028 and independent completeness E029, two full-revision domains outstanding; **`014` OPEN** — rerun-determinism guards re-export one stored record instead of rerunning; guard redesign; **`015` OPEN** — PAIRS 02–08: 5 executable defects, 9 guard gaps or rebinds, ownership question settled by D005/E018; 14 B18 obligations remain; **`016` OPEN** — PAIRS 09–19: 1 executable defect (replay backfill starts from the current pointer), 5 guard gaps, 4 rebinds, 1 F-013 carry-forward; **`017` OPEN** — PAIRS 21–40: 1 executable gap (only a missing config snapshot is BLOCKED), 7 bases that execute nothing, 4 guard gaps or rebinds, 4 F-013 carry-forwards; **`018` OPEN** — PAIRS 41–68: 3 bases pass against a cutoff wall, 2 never exercise knowledge time, 3 are mistargeted, 1 leaves the null-reason class uncompared; 2 also carry F-013
- Change Impact Declaration: `CI-MD-B18-A002-001` — ISSUED
- Denominator: **114** (FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run, every reference row decided)
- SATISFIED / NOT_ASSESSED: **0 / 114**
- Mandatory / conditional-applicable: **114 / 0**
- Conditional-not-applicable / conditional-pending / transitional: **4 / 0 / 0**

## Stage state index

| Stage | Lifecycle | Verdict | Latest attempt | Baseline | Integrity gate |
|---|---|---|---|---|---|
| `MD-B00` | `DONE` | `PASS` | `MD-B00-A004` | `MD-B00-A004-BL001` | `PASS` (classification gate hardened with `UNEXPLAINED_REFERENCE` and `BINDING_COHERENCE`, both mutation-proven; self-test 12/46; full suite 1951/18238) |
| `MD-B01` | `DONE` | `PASS` | `MD-B01-A016` | `MD-B01-A016-BL001` | `PASS` (ownership-chain proof + promoted-predicate map + classification consistency + applicability + scope map, all mutation-proven) |
| `MD-B02` | `DONE` | `PASS` | `MD-B02-A001` | `MD-B02-A001-BL001` | `PASS` (provider-bootstrap traceability/proof gate + classification + relationship + documentation; mutation-proven) |
| `MD-B03` | `DONE` | `PASS` | `MD-B03-A003` | `MD-B03-A003-BL001` | `PASS` (drift detector, nullable-placeholder gate, test-path binding integrity, all mutation-proven) |
| `MD-B04` | `DONE` | `PASS` | `MD-B04-A002` | `MD-B04-A002-BL001` | `PASS` (config-foundation proof/traceability + classification + relationship + documentation; mutation-proven) |
| `MD-B05` | `DONE` | `PASS` | `MD-B05-A001` | `MD-B05-A001-BL001` | `PASS` (temporal-identity traceability/proof gates + classification + relationship + documentation; mutation-proven) |
| `MD-B06` | `DONE` | `PASS` | `MD-B06-A001` | `MD-B06-A001-BL001` | `PASS` (calendar/status traceability + exact proof + classification + relationship + documentation; mutation-proven) |
| `MD-B07` | `DONE` | `PASS` | `MD-B07-A002` | `MD-B07-A002-BL001` | `PASS` (exact B07 116/116 + all four governance gates + full suite 1946/18210; masking guard and normalization completeness assertion both mutation-proven) |
| `MD-B08` | `DONE` | `PASS` | `MD-B08-A002` | `MD-B08-A002-BL001` | `PASS` (exact B08 139/139 + failure-taxonomy surface 75/335 + all four governance gates + full suite; the new retention guard mutation-proven against the exact collapse that previously passed all 1946 tests) |
| `MD-B09` | `DONE` | `PASS` | `MD-B09-A003` | `MD-B09-A003-BL001` | `PASS` (B09 traceability + proof gates, all four governance gates, full suite 1951/18239; the source-JSON-path guard gap closed and mutation-proven) |
| `MD-B10` | `DONE` | `PASS` | `MD-B10-A001` | `MD-B10-A001-BL001` | `PASS` (exact 9-trigger deployed immutability + cumulative lifecycle/reconciliation/full-suite proof + rollback-safe deployed `REBUILT_AND_VERIFIED` repair + exact 1072 binding + post-binding controls) |
| `MD-B11` | `DONE` | `PASS` | `MD-B11-A003` | `MD-B11-A003-BL001` | `PASS` (B11 proof/traceability gates bound, all four governance gates, B11 surface 93/480, full suite 1953/18248 exit 0 against reachable MariaDB) |
| `MD-B12` | `DONE` | `PASS` | `MD-B12-A003` | `MD-B12-A003-BL001` | `PASS` (B12 proof/traceability/static gates bound, all four governance gates, B12 surface 78/247, full suite 1953/18247 exit 0 against reachable MariaDB) |
| `MD-B13` | `DONE` | `PASS` | `MD-B13-A001` | `MD-B13-A001-BL001` | `PASS` (in-session deployed-MariaDB targeted/full-suite proof + exact 33 binding + evidenced aggregate applicability + post-binding controls) |
| `MD-B14` | `DONE` | `PASS` | `MD-B14-A001` | `MD-B14-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 10 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B15` | `DONE` | `PASS` | `MD-B15-A001` | `MD-B15-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 6 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B16` | `DONE` | `PASS` | `MD-B16-A001` | `MD-B16-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 8 fail-closed and 8 closure-condition probes all caught |
| `MD-B17` | `DONE` | `PASS` | `MD-B17-A002` | `MD-B17-A002-BL001` | `PASS` — 246-entry proof map, atomic binding, self-test 11/11, 7 snapshot fail-closed guards, 8 closure-condition probes, affected B04 gates and post-binding full suite all pass |
| `MD-B18` | `IN_PROGRESS` | `BLOCKED — MD-DEP-0015 (full suite waits on the corpus oracle via MD-DEP-0016); MD-DEP-0017 approved consolidated remediation execution (F-013/F-014/F-015/F-016/F-017/F-018); per-predicate review complete, consolidated remediation package drafted; Q1-Q6 approved by D005; Q6 proven E017 and Q5 ownership aligned E018; Q1-Q4 executable proof outstanding; A001 closure remains withdrawn` | `MD-B18-A002` | `MD-B18-A002-BL001` | `NOT_READY_FOR_CLOSURE` — 50/114 bases present (10 moved to INCOMPLETE by F-013, 4 by F-014, 14 retained by F-015; one transferred unproven to B22 by D005/E018, 11 by F-016, 16 by F-017, 9 by F-018); R0056 proven by the executed-corpus aggregate (6 probes caught); 4 stale N/A entries withdrawn from PROVEN; proof, readiness and binder FAIL on the 64 INCOMPLETE bases, and the self-test control is red for the same reason; normalization PASS on E008 (2 probes caught); all 69 pairs reviewed (PAIR 69, R0056, by the executed-corpus aggregate); closure FAIL on binding, A002 manifest and governed evidence (F005) |
| `MD-B19` | `IN_PROGRESS` | — | `MD-B19-A001` | `MD-B19-A001-BL001` | `PARTIAL` — proof map validated at 743/743 across 37 families, zero structural errors; **no family may now be called proven**: `F-MD-B19-A001-002` measured that a family-level guard assignment does not establish its members, and the three families previously recorded as proven carry one guard pair each for 52, 13 and 8 predicates. The gate now requires a reviewed per-predicate proof basis and reports **743/743 predicates without one**; 34/37 families also carry no guard at all |
| `MD-B20` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B21` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B22` | `NOT_STARTED` | — | — | — | `NOT_RUN` |

## Open dependencies and work records

- Open findings across every stage: `F-MD-B00-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A014-001` — OPEN; `F-MD-B14-A001-001` — OPEN; `F-MD-B18-A002-003` — OPEN; `F-MD-B18-A002-004` — PARTIALLY_RESOLVED; `F-MD-B18-A002-005` — OPEN; `F-MD-B18-A002-011` — PARTIALLY_RESOLVED; `F-MD-B18-A002-013` — OPEN; `F-MD-B18-A002-014` — OPEN; `F-MD-B18-A002-015` — OPEN; `F-MD-B18-A002-016` — OPEN; `F-MD-B18-A002-017` — OPEN; `F-MD-B18-A002-018` — OPEN; `F-MD-B19-A001-002` — OPEN — total **15**
- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`; `MD-DEP-0009` — BLOCKING; owner `MD-B18`; `MD-DEP-0010` — OPEN_NON_BLOCKING; owner `MD-B21`; `MD-DEP-0011` — OPEN_NON_BLOCKING; owner `MD-B22 with MD-B18 supporting`; `MD-DEP-0015` — BLOCKING; owner `database owner (explicit user recovery decision)`; `MD-DEP-0016` — OPEN_NON_BLOCKING; owner `database owner (separate recovery track)`; `MD-DEP-0017` — BLOCKING; owner `MD-B18 approved bounded remediation under D-MD-B18-A002-005`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B20` 9 — total **9**
- Registered current work records: **269** (BASELINE_LOCK=51, CHANGE_IMPACT_DECLARATION=46, DECISION=13, EVIDENCE=91, FINDING=39, STAGE_CLOSURE=5, STAGE_CLOSURE_MANIFEST=24)

## Exact resume

- Single exact next executable resume point: MD-B18-A002: E033 confirmed against real migrated MariaDB (`tradeaxis_testing`, 73/73 canonical migrations, rolled-back transaction, nothing written) that `eod_reason_codes` exactly matches the canonical `Reason_Codes_Seed.sql` (437/437, zero diff) and that `registry_versions.reason_registry.entries` does not reproduce there — the sole gap E032 found on SQLite. Combined with E031 (substrate-agnostic capture/re-derivation logic, proven correct) and E032 (SQLite all-engaged run, zero gaps except that one), whole-C1 **input-capture manifest completeness** is proven via two independently-verified, non-interacting checks composing rather than one single unified run (no MariaDB calendar/ticker/bar fixture harness exists in this codebase and none was built). This is capture-manifest completeness only — the C1 contract's own named prerequisite before binding — and is **distinct** from the 114-row per-predicate SATISFIED/denominator/INCOMPLETE count, which remains unchanged and unpromoted. Per CI-MD-B18-A002-001's own repeated proof-gate language (already authorized by D-MD-B18-A002-005's Q1-Q6 approval — independently verified, not a new approval requirement), capture → binding progression may now proceed. Next: implement Binding (C1 contract §6 step 2 — lock candidate + owning run; verify ownership/cutoff/config consistency; validate complete slot manifest; canonicalize and persist V2 input context on existing lineage; derive compatibility component hashes from those contents) as the next concrete slice in the same attempt. Preserve E021-E033 and valid P51-P86; do not repeat C02-C09 proof or this audit. 5 implementation slices, 64 INCOMPLETE, 0/114 SATISFIED unchanged. MD-DEP-0017/0015 BLOCKING; MD-DEP-0018 RESOLVED. No full repository-wide PHPUnit run. No seal/reader/admission before Binding is implemented and proven. No data_260914 action, new attempt, strategy change or relock. Return MD-B19-A001 only after valid B18 closure and MD-DEP-0009 resolution. **Runtime prerequisite chronology below:**
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
