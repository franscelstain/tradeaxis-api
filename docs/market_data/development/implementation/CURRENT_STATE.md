# Market Data Current State

> GENERATED — DO NOT EDIT MANUALLY

## Verification identity and coverage

- Verification epoch: `MD-REBASELINE-20260820-001`
- Required active traceability rows: **3185**
- Coverage denominator: **3161** (PROVISIONAL)
- SATISFIED: **2334**
- NOT_ASSESSED inside denominator: **827**
- CONDITIONAL_NOT_APPLICABLE / NOT_APPLICABLE: **22 / 22**
- CONDITIONAL_PENDING / APPLICABILITY_PENDING: **2 / 2**
- Transitional MANDATORY_OR_CONDITIONAL: **541**
- Verified coverage: **73.84% PROVISIONAL**
- Optional capability rules: **63**

## Current executable stage

- Stage: `MD-B11`
- Latest attempt / baseline: `MD-B11-A002` / `MD-B11-A002-BL001`
- State / verdict: `DONE` / `PASS`
- Residue/rework: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND`
- Dependency: `MD-DEP-0004` partially discharged for B11
- Open finding: none
- Change Impact Declaration: `CI-MD-B11-A002-001` — ISSUED
- Denominator: **172** (FINAL for every machine-checked criterion — no transitional applicability, no mixed-classification run)
- SATISFIED / NOT_ASSESSED: **172 / 0**
- Mandatory / conditional-applicable: **172 / 0**
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
| `MD-B10` | `DONE` | `PASS` | `MD-B10-A001` | `MD-B10-A001-BL001` | `PASS` (exact 9-trigger deployed immutability + cumulative lifecycle/reconciliation/full-suite proof + rollback-safe deployed `REBUILT_AND_VERIFIED` repair + exact 1072 binding + post-binding controls) |
| `MD-B11` | `DONE` | `PASS` | `MD-B11-A002` | `MD-B11-A002-BL001` | `PASS` (B11 proof/traceability gates bound, all four governance gates, B11 surface 51/161; full suite 1953/18244 exit 0 with zero skips against restored MariaDB 10.4.27, discharged by `E-MD-B11-A002-002`; B11 deployed-schema probe PASS) |
| `MD-B12` | `DONE` | `PASS` | `MD-B12-A002` | `MD-B12-A002-BL001` | `PASS` (B12 proof/traceability/static gates bound, all four governance gates, full suite 1952/18241; the adjustment-authority invariant from the audit now mutation-proven) |
| `MD-B13` | `DONE` | `PASS` | `MD-B13-A001` | `MD-B13-A001-BL001` | `PASS` (in-session deployed-MariaDB targeted/full-suite proof + exact 33 binding + evidenced aggregate applicability + post-binding controls) |
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
- Classification entry obligation (`MD-DEP-0004`), reference-only rows in mixed-classification runs by stage: `MD-B14` 65, `MD-B15` 37, `MD-B16` 17, `MD-B17` 41, `MD-B18` 43, `MD-B19` 46, `MD-B20` 9 — total **258**
- Registered current work records: **169** (BASELINE_LOCK=41, CHANGE_IMPACT_DECLARATION=36, DECISION=7, EVIDENCE=49, FINDING=14, STAGE_CLOSURE_MANIFEST=22)

## Exact resume

- Single exact next executable resume point: begin `MD-B11-A003` — the remaining 167 non-structural reference rows of this stage — and issue its Baseline Lock + Change Impact Declaration before any material change. `MD-B12-A003` at 39 follows. The `MD-B11-A002` full-suite control is no longer deferred: it was re-executed clean against restored MariaDB and discharged by `E-MD-B11-A002-002`.
- Current stage source: `MD_IMPLEMENTATION_STAGE_REGISTER.md`
- Pre-epoch W00..W22 verdicts: **historical-only**
