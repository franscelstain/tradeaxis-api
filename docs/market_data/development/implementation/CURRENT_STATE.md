# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **4038**
- Coverage denominator: **4009** (FINAL)
- SATISFIED: **3012**
- NOT_ASSESSED inside denominator: **997**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **29 / 29**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **0 / 0**
- Transitional MANDATORY_OR_CONDITIONAL: **0**
- Verified coverage: **75.13% FINAL**
- Optional capability rules: **63**

## Current executable stage

- Stage: `MD-B10`
- Latest attempt / baseline: `MD-B10-A002` / `MD-B10-A002-BL001`
- State / verdict: `IN_PROGRESS` / `BLOCKED — MD-DEP-0021; V2 artifacts, publication/correction/seal and nested semantic identities implemented/proven, controlled deployment and governed reproof pending; 56 NOT_ASSESSED`
- Residue/rework: `CONFORMANT_IN_BOUNDED_SUCCESSOR_PROFILE_TEST_SCOPE` (E011/E012: V2 publication manifest, correction/republication and seal consume V2 artifact roots and stable semantic lineage; E012 proves convergence across allocations and refusal of tampered sealed material on the production repository path, fixes the env-reader regression and preserves legacy V1; isolated databases cleaned; normal database not deployed; E013: V2 nested observation/factor/revision identities and the publication-scope reason set converge across allocations, key order and producer clock on the production producer path)
- Dependency: `MD-DEP-0021` BLOCKING: E011-E013 prove successor profile identity, including the nested semantic producers, on the production path; controlled normal-database deployment and complete reproof remain. `MD-DEP-0020` keeps B18/R0025 blocked until all 56 are revalidated and B10 closes.
- Open finding: `F-MD-B18-A002-023` OPEN (artifact plus publication/correction/seal primitives complete; nested inputs/deployment/full reproof remain); `F-MD-B10-A002-001` OPEN (separate moved-count tooling debt); `F-MD-B10-A002-002` RESOLVED (four static-guard regressions resolved by their recorded owners under D-MD-B10-A002-004, approved by D-MD-B10-A002-005, E-MD-B10-A002-014 and E-MD-B18-A002-087/088; full-suite confirmation is the successor-closure control); `F-MD-B10-A002-004` PARTIALLY_RESOLVED (replay identities no longer carry run-scope capture allocation; V1 entity ids in captured content remain; owner MD-B18-A002 on the MD-DEP-0020 return path); `F-MD-B10-A002-003` OPEN (later runs can bind platform-created factor records recorded after their cutoff; owner MD-B18-A002)
- Change Impact Declaration: `CI-MD-B10-A002-001` — ISSUED
- Denominator: **1072** (FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run, every reference row decided)
- SATISFIED / NOT_ASSESSED: **1016 / 56**
- Mandatory / conditional-applicable: **1072 / 0**
- Conditional-not-applicable / conditional-pending / transitional: **0 / 0 / 0**

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
| `MD-B10` | `IN_PROGRESS` | `BLOCKED — MD-DEP-0021; V2 artifacts, publication/correction/seal and nested semantic identities implemented/proven, controlled deployment and governed reproof pending; 56 NOT_ASSESSED` | `MD-B10-A002` | `MD-B10-A002-BL001` | `CURRENT_VERIFICATION_REVIEW_REQUIRED` — artifact, publication/correction/seal and nested semantic producer implementation/proof is complete (E010-E013), but the complete current-verification review of all 56 remains; B10 DONE/PASS and 1072/1072 current sufficiency remain withdrawn |
| `MD-B11` | `DONE` | `PASS` | `MD-B11-A003` | `MD-B11-A003-BL001` | `PASS` (B11 proof/traceability gates bound, all four governance gates, B11 surface 93/480, full suite 1953/18248 exit 0 against reachable MariaDB) |
| `MD-B12` | `DONE` | `PASS` | `MD-B12-A003` | `MD-B12-A003-BL001` | `PASS` (B12 proof/traceability/static gates bound, all four governance gates, B12 surface 78/247, full suite 1953/18247 exit 0 against reachable MariaDB) |
| `MD-B13` | `DONE` | `PASS` | `MD-B13-A001` | `MD-B13-A001-BL001` | `PASS` (in-session deployed-MariaDB targeted/full-suite proof + exact 33 binding + evidenced aggregate applicability + post-binding controls) |
| `MD-B14` | `DONE` | `PASS` | `MD-B14-A001` | `MD-B14-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 10 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B15` | `DONE` | `PASS` | `MD-B15-A001` | `MD-B15-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 6 fail-closed probes and 8 closure-condition probes all caught |
| `MD-B16` | `DONE` | `PASS` | `MD-B16-A001` | `MD-B16-A001-BL001` | `PASS` — proof gate bound, self-test 11/11, 8 fail-closed and 8 closure-condition probes all caught |
| `MD-B17` | `DONE` | `PASS` | `MD-B17-A002` | `MD-B17-A002-BL001` | `PASS` — 246-entry proof map, atomic binding, self-test 11/11, 7 snapshot fail-closed guards, 8 closure-condition probes, affected B04 gates and post-binding full suite all pass |
| `MD-B18` | `IN_PROGRESS` | `BLOCKED — MD-DEP-0020 (B10 semantic hash identity remediation) and MD-DEP-0015 (full suite waits on the corpus oracle via MD-DEP-0016); MD-DEP-0017 approved consolidated remediation execution (F-013/F-014/F-015/F-016/F-017/F-018); per-predicate review complete, consolidated remediation package drafted; Q1-Q6 approved by D005; Q6 proven E017 and Q5 ownership aligned E018; Q1-Q4 executable proof outstanding; A001 closure remains withdrawn` | `MD-B18-A002` | `MD-B18-A002-BL001` | `NOT_READY_FOR_CLOSURE` — 100/113 bases present per `MarketDataReplayVerificationProofReadinessGate` (re-derived after the G05 residual E084 proved `MD-S002-R0007` (existing split-ratio oracle bound into its member set) and `MD-S002-R0008` (behavioural atomic-switch guard) again: 13 remain without a reviewed basis -- 2 owned by F-017 (`MD-S003-R0025` blocked on MariaDB via `MD-DEP-0019`, `MD-S050-R0005`), 9 by F-018 and 2 by the reopened F-013; earlier 98/113 after the closure integrity review E083 returned both to `INCOMPLETE` (15 remained; F-017 4); earlier 100/113 after E082 (G05) on 13; earlier 92/113 after E080 proved `MD-S002-R0003` (G07): 21 remain -- 10 owned by F-017, 9 by F-018 and 2 by the reopened F-013; earlier 91/113 after `D-MD-B18-A002-010`/E079 transferred `MD-S065-R0003` to `MD-B21`; earlier 91/114 re-verified after E075 promoted `MD-S003-R0023`/`MD-S004-R0004` PROVEN by rebinding them to the real writer -> export path; superseded stale "89/114"; one transferred unproven to B22 by D005/E018, 23 remain without a reviewed basis -- 12 owned by F-017, 9 by F-018 and 2 by the reopened F-013); R0056 proven by the executed-corpus aggregate (6 probes caught); 4 stale N/A entries withdrawn from PROVEN; proof, readiness and binder FAIL on the 13 INCOMPLETE bases (15 after E083, 13 after E082 before E083, 21 before G05, 22 before G07, 23 before the D-010 transfer), and the self-test control is red for the same reason (plus one unrelated MISSING_NEGATIVE_PROOF:determinism_and_operations); normalization PASS on E008 (2 probes caught); all 69 pairs reviewed (PAIR 69, R0056, by the executed-corpus aggregate); closure FAIL on binding, A002 manifest and governed evidence (F005) |
| `MD-B19` | `IN_PROGRESS` | — | `MD-B19-A001` | `MD-B19-A001-BL001` | `PARTIAL` — proof map validated at 743/743 across 37 families, zero structural errors; **no family may now be called proven**: `F-MD-B19-A001-002` measured that a family-level guard assignment does not establish its members, and the three families previously recorded as proven carry one guard pair each for 52, 13 and 8 predicates. The gate now requires a reviewed per-predicate proof basis and reports **743/743 predicates without one**; 34/37 families also carry no guard at all |
| `MD-B20` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B21` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B22` | `NOT_STARTED` | — | — | — | `NOT_RUN` |

## Open dependencies and work records

- Open findings across every stage: `F-MD-B00-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A014-001` — OPEN; `F-MD-B10-A002-001` — OPEN; `F-MD-B10-A002-003` — OPEN; `F-MD-B10-A002-004` — PARTIALLY_RESOLVED; `F-MD-B14-A001-001` — OPEN; `F-MD-B18-A002-003` — OPEN; `F-MD-B18-A002-004` — PARTIALLY_RESOLVED; `F-MD-B18-A002-005` — OPEN; `F-MD-B18-A002-011` — PARTIALLY_RESOLVED; `F-MD-B18-A002-013` — OPEN; `F-MD-B18-A002-017` — OPEN; `F-MD-B18-A002-018` — OPEN; `F-MD-B18-A002-023` — OPEN; `F-MD-B19-A001-002` — OPEN — total **16**
- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`; `MD-DEP-0009` — BLOCKING; owner `MD-B18`; `MD-DEP-0010` — OPEN_NON_BLOCKING; owner `MD-B21`; `MD-DEP-0011` — OPEN_NON_BLOCKING; owner `MD-B22 with MD-B18 supporting`; `MD-DEP-0015` — BLOCKING; owner `database owner (explicit user recovery decision)`; `MD-DEP-0016` — OPEN_NON_BLOCKING; owner `database owner (separate recovery track)`; `MD-DEP-0017` — BLOCKING; owner `MD-B18 approved bounded remediation under D-MD-B18-A002-005`; `MD-DEP-0019` — BLOCKING; owner `database owner (explicit user decision to start and verify the MariaDB instance)`; `MD-DEP-0020` — BLOCKING; owner `MD-B10 successor audit-hash remediation, using the shared security-identity foundation owner`; `MD-DEP-0021` — BLOCKING; owner `TradeAxis Shared Security Identity Foundation (same-repository shared logical domain); TradeAxis project-owner role accountable for implementation/data stewardship; MD-B10 consumer coordination`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B20` 9 — total **9**
- Registered current work records: **359** (BASELINE_LOCK=52, CHANGE_IMPACT_DECLARATION=47, DECISION=24, EVIDENCE=160, FINDING=47, STAGE_CLOSURE=5, STAGE_CLOSURE_MANIFEST=24)

## Exact resume

- Single exact next executable resume point: MD-B10-A002: complete governed reproof of the 56 affected predicates against E-MD-B10-A002-010..014, predicate by predicate and without promotion until the closure gates hold: controlled deployment under MD-DEP-0021, then a full MarketData suite failing only governed expected states (which also confirms F-MD-B10-A002-002 closure). Same A002/BL001/CI. MD-DEP-0021/0020/0019 remain BLOCKING; R0025 and F-018 stay paused; F-MD-B10-A002-003 waits for MD-B18-A002.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
