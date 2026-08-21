# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **2082**
- Coverage denominator: **2081** (PROVISIONAL)
- SATISFIED: **144**
- NOT_ASSESSED inside denominator: **1937**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **0 / 0**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **1 / 1**
- Transitional MANDATORY_OR_CONDITIONAL: **1855**
- Verified coverage: **6.92% PROVISIONAL**
- Optional capability rules: **54**

## Current executable stage

- Stage: `MD-B01`
- Latest attempt / baseline: `MD-B01-A014` / `MD-B01-A014-BL001`
- State / verdict: `IN_PROGRESS` / `PARTIAL`
- Residue/rework: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND; 1 GOVERNED_REQUIRED_REWORK`
- Dependency: `MD-DEP-0004` (`OPEN_NON_BLOCKING`)
- Open finding: `F-MD-B01-A003-001` (P2; blocks `MD-S020-R0067`), `F-MD-B01-A014-001` (P2; owner `MD-B19`)
- Change Impact Declaration: `CI-MD-B01-A014-001` — ISSUED
- Denominator: **207** (FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run)
- SATISFIED / NOT_ASSESSED: **144 / 63**
- Mandatory / conditional-applicable: **201 / 6**
- Conditional-not-applicable / conditional-pending / transitional: **0 / 0 / 0**

## Stage state index

| Stage | Lifecycle | Verdict | Latest attempt | Baseline | Integrity gate |
|---|---|---|---|---|---|
| `MD-B00` | `DONE` | `PASS` | `MD-B00-A002` | `MD-B00-A002-BL001` | `PASS` (validity + completeness, mutation-proven) |
| `MD-B01` | `IN_PROGRESS` | `PARTIAL` | `MD-B01-A014` | `MD-B01-A014-BL001` | `PASS` (classification consistency + applicability + scope proof map, mutation-proven) |
| `MD-B02` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B03` | `IN_PROGRESS` | — | `MD-B03-A001` | `MD-B03-A001-BL001` | `PASS` (post-restoration; drift detector corrected and mutation-proven) |
| `MD-B04` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B05` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B06` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B07` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
| `MD-B08` | `NOT_STARTED` | — | — | — | `NOT_RUN` |
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

- Open dependencies: `MD-DEP-0003` — OPEN_NON_BLOCKING; owner `owning stages MD-B03/B06/B15/B17/B19/B21/B22`; `MD-DEP-0004` — OPEN_NON_BLOCKING; owner `each stage at entry`
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B02` 1, `MD-B04` 50, `MD-B05` 27, `MD-B06` 25, `MD-B07` 25, `MD-B08` 18, `MD-B09` 45, `MD-B10` 127, `MD-B11` 38, `MD-B12` 6, `MD-B13` 10, `MD-B14` 65, `MD-B15` 37, `MD-B16` 17, `MD-B17` 41, `MD-B18` 43, `MD-B19` 46, `MD-B20` 9 — total **630**
- Registered current work records: **67** (BASELINE_LOCK=17, CHANGE_IMPACT_DECLARATION=12, DECISION=3, EVIDENCE=22, FINDING=11, STAGE_CLOSURE_MANIFEST=2)

## Exact resume

- Single exact next executable resume point: open `MD-B01-A015` to rebaseline and prove the 62 promoted rows that now carry a normalized predicate and no proof, then evaluate `MD-B01` closure. `MD-S020-R0067` stays excluded until an authorised strategy-change process resolves `F-MD-B01-A003-001`. Do not enter `MD-B02` first.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
