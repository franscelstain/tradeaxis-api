# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **4049**
- Coverage denominator: **4020** (FINAL)
- SATISFIED: **3066**
- NOT_ASSESSED inside denominator: **954**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **29 / 29**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **0 / 0**
- Transitional MANDATORY_OR_CONDITIONAL: **0**
- Verified coverage: **76.27% FINAL**
- Optional capability rules: **63**

## Current executable stage

- Stage: `MD-B17`
- Latest attempt / baseline: `MD-B17-A002` / `MD-B17-A002-BL001`
- State / verdict: `IN_PROGRESS` / `REVALIDATION_REQUIRED` — closure sufficiency under `MD-STRATEGY-FREEZE-20260925-001` withdrawn for 13 rows by `E-MD-B18-A002-098` (`DOC-CHG-20261005-001`); no successor attempt opened
- Residue/rework: `CONFORMANT_WITH_FAIL_CLOSED_LEGACY_SNAPSHOT_BOUNDARY`
- Dependency: `MD-DEP-0004` discharged for B17
- Open finding: none — `F-MD-B17-A001-001` remains RESOLVED; `F-MD-B18-A002-033` OPEN (read surface emits no `freshness_state`; 13 rows to prove)
- Change Impact Declaration: `CI-MD-B17-A002-001` — ISSUED
- Denominator: **257** (PROVISIONAL — 33 reference-only rows carry no recorded stage-entry decision, so an obligation may still be filed as reference and the denominator can only grow)
- SATISFIED / NOT_ASSESSED: **244 / 13**
- Mandatory / conditional-applicable: **257 / 0**
- Conditional-not-applicable / conditional-pending / transitional: **2 / 0 / 0**

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
| `MD-B10` | `DONE` | `PASS` | `MD-B10-A003` | `MD-B10-A003-BL001` | `PASS` (B10 proof and traceability gates in bound closure mode; successor binder self-test 54/54 with the pristine A002 matrix, A003 binder cases 20/20, 3 tooling probes and 12 producer probes red at the intended case; the 43 governance gates identical to their state before A003; successor full suite E002 2847/40132 with 8 governed failures and 9 expected candidate-v3 invalidations) |
| `MD-B11` | `DONE` | `PASS` | `MD-B11-A003` | `MD-B11-A003-BL001` | `PASS` (B11 proof/traceability gates bound, all four governance gates, B11 surface 93/480, full suite 1953/18248 exit 0 against reachable MariaDB) |
| `MD-B12` | `DONE` | `PASS` | `MD-B12-A003` | `MD-B12-A003-BL001` | `PASS` (B12 proof/traceability/static gates bound, all four governance gates, B12 surface 78/247, full suite 1953/18247 exit 0 against reachable MariaDB) |
| `MD-B13` | `DONE` | `PASS` | `MD-B13-A001` | `MD-B13-A001-BL001` | `PASS` (in-session deployed-MariaDB targeted/full-suite proof + exact 33 binding + evidenced aggregate applicability + post-binding controls) |
| `MD-B14` | `DONE` | `PASS` | `MD-B14-A001` | `MD-B14-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 10 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B15` | `DONE` | `PASS` | `MD-B15-A001` | `MD-B15-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 6 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B16` | `DONE` | `PASS` | `MD-B16-A001` | `MD-B16-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 8 fail-closed and 8 closure-condition probes all caught |
| `MD-B17` | `IN_PROGRESS` | `REVALIDATION_REQUIRED` — closure sufficiency under `MD-STRATEGY-FREEZE-20260925-001` withdrawn for 13 rows by `E-MD-B18-A002-098` (`DOC-CHG-20261005-001`); no successor attempt opened | `MD-B17-A002` | `MD-B17-A002-BL001` | `FAIL` by design — closure gate `denominator_fully_satisfied` unmet for the 13 rows and the proof gate has no proof family for the 11 new rows; the predecessor-freeze closure stays as immutable history only |
| `MD-B18` | `IN_PROGRESS` | `BLOCKED — MD-DEP-0015 (full suite waits on the corpus oracle via MD-DEP-0016; gates closure, not the R0025 proof work); `MD-DEP-0019` RESOLVED by E-MD-B18-A002-090 and D-MD-B18-A002-012 (zero-skip MariaDB execution of the R0025 aggregate; `F-MD-B18-A002-024` resolved by scope correction) and `MD-DEP-0020` RESOLVED by the B10 closure `SC-MD-B10-A002-001`; MD-DEP-0017 approved consolidated remediation execution (F-013/F-014/F-015/F-016/F-017/F-018); per-predicate review complete, consolidated remediation package drafted; Q1-Q6 approved by D005; Q6 proven E017 and Q5 ownership aligned E018; Q1-Q4 executable proof outstanding; A001 closure remains withdrawn` | `MD-B18-A002` | `MD-B18-A002-BL001` | `NOT_READY_FOR_CLOSURE` — 100/113 bases present per `MarketDataReplayVerificationProofReadinessGate` (re-derived after the G05 residual E084 proved `MD-S002-R0007` (existing split-ratio oracle bound into its member set) and `MD-S002-R0008` (behavioural atomic-switch guard) again: 13 remain without a reviewed basis -- 2 owned by F-017 (`MD-S003-R0025` blocked on MariaDB via `MD-DEP-0019`, `MD-S050-R0005`), 9 by F-018 and 2 by the reopened F-013; earlier 98/113 after the closure integrity review E083 returned both to `INCOMPLETE` (15 remained; F-017 4); earlier 100/113 after E082 (G05) on 13; earlier 92/113 after E080 proved `MD-S002-R0003` (G07): 21 remain -- 10 owned by F-017, 9 by F-018 and 2 by the reopened F-013; earlier 91/113 after `D-MD-B18-A002-010`/E079 transferred `MD-S065-R0003` to `MD-B21`; earlier 91/114 re-verified after E075 promoted `MD-S003-R0023`/`MD-S004-R0004` PROVEN by rebinding them to the real writer -> export path; superseded stale "89/114"; one transferred unproven to B22 by D005/E018, 23 remain without a reviewed basis -- 12 owned by F-017, 9 by F-018 and 2 by the reopened F-013); R0056 proven by the executed-corpus aggregate (6 probes caught); 4 stale N/A entries withdrawn from PROVEN; proof, readiness and binder FAIL on the 13 INCOMPLETE bases (15 after E083, 13 after E082 before E083, 21 before G05, 22 before G07, 23 before the D-010 transfer), and the self-test control is red for the same reason (plus one unrelated MISSING_NEGATIVE_PROOF:determinism_and_operations); normalization PASS on E008 (2 probes caught); all 69 pairs reviewed (PAIR 69, R0056, by the executed-corpus aggregate); closure FAIL on binding, A002 manifest and governed evidence (F005) |
| `MD-B19` | `IN_PROGRESS` | — | `MD-B19-A001` | `MD-B19-A001-BL001` | `PARTIAL` — proof map validated at 743/743 across 37 families, zero structural errors; **no family may now be called proven**: `F-MD-B19-A001-002` measured that a family-level guard assignment does not establish its members, and the three families previously recorded as proven carry one guard pair each for 52, 13 and 8 predicates. The gate now requires a reviewed per-predicate proof basis and reports **743/743 predicates without one**; 34/37 families also carry no guard at all |
| `MD-B20` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B21` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B22` | `NOT_STARTED` | — | — | — | `NOT_RUN` |

## Open dependencies and work records

- Open findings across every stage: `F-MD-B00-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A014-001` — OPEN; `F-MD-B10-A002-003` — OPEN; `F-MD-B10-A002-004` — PARTIALLY_RESOLVED; `F-MD-B10-A003-002` — OPEN; `F-MD-B14-A001-001` — OPEN; `F-MD-B18-A002-003` — OPEN; `F-MD-B18-A002-004` — PARTIALLY_RESOLVED; `F-MD-B18-A002-005` — OPEN; `F-MD-B18-A002-011` — PARTIALLY_RESOLVED; `F-MD-B18-A002-013` — OPEN; `F-MD-B18-A002-017` — OPEN; `F-MD-B18-A002-018` — OPEN; `F-MD-B18-A002-025` — OPEN; `F-MD-B18-A002-028` — OPEN; `F-MD-B18-A002-030` — OPEN; `F-MD-B18-A002-031` — OPEN; `F-MD-B18-A002-032` — OPEN; `F-MD-B18-A002-033` — OPEN; `F-MD-B19-A001-002` — OPEN — total **21**
- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`; `MD-DEP-0009` — BLOCKING; owner `MD-B18`; `MD-DEP-0010` — OPEN_NON_BLOCKING; owner `MD-B21`; `MD-DEP-0011` — OPEN_NON_BLOCKING; owner `MD-B22 with MD-B18 supporting`; `MD-DEP-0015` — BLOCKING; owner `database owner (explicit user recovery decision)`; `MD-DEP-0016` — OPEN_NON_BLOCKING; owner `database owner (separate recovery track)`; `MD-DEP-0017` — BLOCKING; owner `MD-B18 approved bounded remediation under D-MD-B18-A002-005`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B20` 9 — total **9**
- Registered current work records: **400** (BASELINE_LOCK=53, CHANGE_IMPACT_DECLARATION=48, DECISION=28, EVIDENCE=179, FINDING=61, STAGE_CLOSURE=5, STAGE_CLOSURE_MANIFEST=26)

## Exact resume

- Single exact next executable resume point: OWNER APPROVAL OF EXACT CANDIDATE-V4 FINGERPRINT: the project owner decides whether to approve candidate-v4 (tests/fixtures/replay/r0025-synthetic-v2-candidate-v4, fingerprint d0a61b36d99682c9e165560a9e89ad83ee5a363d08f4f3f58701b06cc7ac9f00, manifest sha256 6ce59c64e458638691504ea1de2d7726e4dae15ae53cfb77d65b1e2c9c2d8c32, valid only for frozen build sha256:7a1ed5c78cc37a978df0441b565815f3e435fdceafd2045eb202d716ce16d8cf) as a governed owner-approval record bound to that fingerprint (Runtime Evidence Standard 6A); approval is a separate record, is not implied by the independent review, and does not promote MD-S003-R0025. Candidate-v1, candidate-v2 and candidate-v3 must not be approved. MD-B17-A003 remains a separate bounded work unit and is not a prerequisite.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
