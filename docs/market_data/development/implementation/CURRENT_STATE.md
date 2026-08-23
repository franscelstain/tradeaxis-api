# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **2536**
- Coverage denominator: **2527** (PROVISIONAL)
- SATISFIED: **717**
- NOT_ASSESSED inside denominator: **1810**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **8 / 8**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **1 / 1**
- Transitional MANDATORY_OR_CONDITIONAL: **1226**
- Verified coverage: **28.37% PROVISIONAL**
- Optional capability rules: **53**

## Stage state index

| Stage | Lifecycle | Verdict | Latest attempt | Baseline | Integrity gate |
|---|---|---|---|---|---|
| `MD-B00` | `DONE` | `PASS` | `MD-B00-A003` | `MD-B00-A003-BL001` | `PASS` (validity + completeness + extract structure over 428 extracts, mutation-proven) |
| `MD-B01` | `DONE` | `PASS` | `MD-B01-A016` | `MD-B01-A016-BL001` | `PASS` (ownership-chain proof + promoted-predicate map + classification consistency + applicability + scope map, all mutation-proven) |
| `MD-B02` | `DONE` | `PASS` | `MD-B02-A001` | `MD-B02-A001-BL001` | `PASS` (provider-bootstrap traceability/proof gate + classification + relationship + documentation; mutation-proven) |
| `MD-B03` | `DONE` | `PASS` | `MD-B03-A003` | `MD-B03-A003-BL001` | `PASS` (drift detector, nullable-placeholder gate, test-path binding integrity, all mutation-proven) |
| `MD-B04` | `DONE` | `PASS` | `MD-B04-A002` | `MD-B04-A002-BL001` | `PASS` (config-foundation proof/traceability + classification + relationship + documentation; mutation-proven) |
| `MD-B05` | `DONE` | `PASS` | `MD-B05-A001` | `MD-B05-A001-BL001` | `PASS` (temporal-identity traceability/proof gates + classification + relationship + documentation; mutation-proven) |
| `MD-B06` | `DONE` | `PASS` | `MD-B06-A001` | `MD-B06-A001-BL001` | `PASS` (calendar/status traceability + exact proof + classification + relationship + documentation; mutation-proven) |
| `MD-B07` | `DONE` | `PASS` | `MD-B07-A001` | `MD-B07-A001-BL001` | `PASS` (deployed MariaDB + exact B07 + hardened documentation/relationship mutation proof + full suite) |
| `MD-B08` | `IN_PROGRESS` | `PARTIAL` | `MD-B08-A001` | `MD-B08-A001-BL001` | `STATIC_PROOF_PASS / LOCAL_RUNTIME_PROOF_REQUIRED` |
| `MD-B09` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B10` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B11` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B12` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B13` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B14` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B15` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B16` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B17` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B18` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B19` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B20` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B21` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B22` | `NOT_STARTED` | — | — | — | `NOT_RUN` |

## Open dependencies and work records

- Open findings across every stage: `F-MD-B00-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A001-001` — PARTIALLY_RESOLVED; `F-MD-B01-A008-001` — OPEN; `F-MD-B01-A014-001` — OPEN — total **4**
- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B09` 45, `MD-B10` 127, `MD-B11` 38, `MD-B12` 6, `MD-B13` 10, `MD-B14` 65, `MD-B15` 37, `MD-B16` 17, `MD-B17` 41, `MD-B18` 43, `MD-B19` 46, `MD-B20` 9 — total **484**
- Registered current work records: **116** (BASELINE_LOCK=29, CHANGE_IMPACT_DECLARATION=24, DECISION=6, EVIDENCE=34, FINDING=13, STAGE_CLOSURE_MANIFEST=10)

## Exact resume

- Single exact next executable resume point: continue `MD-B08-A001` by applying the complete non-local A001 patch and executing the consolidated `LOCAL-B08-*` proof package. All 138 B08 runtime-dependent predicates remain `NOT_ASSESSED`; `MD-B09` must not open before returned proof is admitted and B08 closure is valid.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
