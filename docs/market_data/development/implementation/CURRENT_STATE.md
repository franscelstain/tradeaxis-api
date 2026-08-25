# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **3072**
- Coverage denominator: **3063** (PROVISIONAL)
- SATISFIED: **2066**
- NOT_ASSESSED inside denominator: **997**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **8 / 8**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **1 / 1**
- Transitional MANDATORY_OR_CONDITIONAL: **701**
- Verified coverage: **67.45% PROVISIONAL**
- Optional capability rules: **64**

## Current executable stage

- Stage: `MD-B11`
- Latest attempt / baseline: — / —
- State / verdict: `NOT_STARTED` / —
- Residue/rework: `NOT_ASSESSED`
- Dependency: —
- Open finding: —
- Change Impact Declaration: **missing**
- Denominator: **111** (PROVISIONAL — transitional applicability unresolved)
- SATISFIED / NOT_ASSESSED: **0 / 111**
- Mandatory / conditional-applicable: **3 / 0**
- Conditional-not-applicable / conditional-pending / transitional: **0 / 0 / 108**

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
| `MD-B08` | `DONE` | `PASS` | `MD-B08-A001` | `MD-B08-A001-BL001` | `PASS` (final R3 targeted runtime + exact 138 binding + B08 invariants + classification + documentation/relationship + full suite) |
| `MD-B09` | `DONE` | `PASS` | `MD-B09-A002` | `MD-B09-A002-BL001` | `PASS` (deployed reason dictionary + canonical RAW/import runtime proof + affected B03/B04 revalidation + exact 139 binding + classification + documentation/relationship + full suite) |
| `MD-B10` | `DONE` | `PASS` | `MD-B10-A001` | `MD-B10-A001-BL001` | `PASS` (exact 9-trigger deployed immutability + cumulative lifecycle/reconciliation/full-suite proof + rollback-safe deployed `REBUILT_AND_VERIFIED` repair + exact 1072 binding + post-binding controls) |
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
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B11` 38, `MD-B12` 6, `MD-B13` 10, `MD-B14` 65, `MD-B15` 37, `MD-B16` 17, `MD-B17` 41, `MD-B18` 43, `MD-B19` 46, `MD-B20` 9 — total **312**
- Registered current work records: **132** (BASELINE_LOCK=32, CHANGE_IMPACT_DECLARATION=27, DECISION=7, EVIDENCE=39, FINDING=14, STAGE_CLOSURE_MANIFEST=13)

## Exact resume

- Single exact next executable resume point: begin `MD-B11` stage-entry preflight; rederive current B11 applicability/ownership/classification from current authority and issue the first valid B11 Baseline Lock + Change Impact Declaration before any material B11 mutation.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
