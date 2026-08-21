# MD Change Impact Declaration — CI-MD-B01-A005-001

- ID: `CI-MD-B01-A005-001`
- Stage / Attempt / Baseline / Epoch: `MD-B01` / `MD-B01-A005` / `MD-B01-A005-BL001` / `MD-REBASELINE-20260820-001`
- Record class: `MUTABLE_TRACEABLE`
- Issued: 2026-08-21. **See the issuance-timing note below — this record was written after the matrix mutation, not before it.**
- Remediates: `MD-DEP-0004` (proof-ownership half), `F-MD-B01-A001-001` (secondary concern)

## Why this attempt is material

It mutates `primary_stage` in `STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv`, which decides which stage carries which proof obligation.

## The defect, measured

`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` section 4 requires each predicate to be assigned to the stage that can own and close its proof, and states that assignment must follow implementation/proof responsibility rather than the physical document the text came from.

Measured across the whole matrix: **all 91 strategy documents map to exactly one stage, and no document maps to more than one.** `primary_stage` is therefore a pure function of the extraction document with zero per-rule differentiation — exactly what section 4 forbids.

For single-purpose owner contracts this happens to be correct: `Coverage_Gate_Enforcement_Contract_LOCKED.md` belongs to `MD-B15` because that contract *is* `MD-B15`'s subject. The defect concentrates in cross-cutting documents, and `MD-B01`'s three — the platform baseline summary, the boundary invariants, and the terminology contract — are the principal case, because they state obligations whose runtime enforcement lives downstream.

## Method, and a rejected method

A keyword classifier was written first, mapping rule text to build-sequence contract areas. It proposed 41 moves. On inspection roughly a third were wrong: `MD-S020-R0003` (boundary ownership) and `MD-S056-R0139` (naming rule) were pulled to `MD-B17` on the words "read model" and "read-product"; `MD-S056-R0016` (Weekly Swing horizon, core `MD-B01` scope) on "consumer"; `MD-S020-R0107` (replay must not be described as proof a strategy works — a boundary rule) on "replay". It would also have moved two rules that already hold valid current proof.

**That classifier was rejected and not applied.** Substituting a pattern for the judgement section 4 asks for is the same defect this dependency exists to correct.

What was applied instead is an explicit 15-entry table, each rule read in full and justified individually, with the applier refusing to move any row that is `SATISFIED`, not `MD-B01`, or not `REQUIRED`. Ambiguous rules were left in place: leaving a rule with its semantic-lock stage is recoverable, moving it wrongly is not.

## Strategy IDs / rules affected

**No strategy byte changes.** `rule_text`, `rule_fingerprint_sha1`, `strategy_owner`, and `source_line` untouched on every row.

15 rules change `primary_stage`; each retains `MD-B01` as a supporting stage, because `MD-B01` keeps a semantic-lock interest in a boundary it defined even when another stage proves it.

| To | Count | Subject |
|---|---|---|
| `MD-B17` | 9 | consumer read-path enforcement: sealed/current/readable only, no raw-table read, no `MAX(date)`, no indicator recomputation, no coverage-gate bypass |
| `MD-B19` | 3 | retention and maintenance: long-term records, audit-evidence retention, partitioning by trade date |
| `MD-B10` | 2 | publication immutability and correction/reseal/supersession trail |
| `MD-B15` | 1 | 98% minimum delivery-coverage prerequisite |

## Affected areas

| Area | Impact |
|---|---|
| Traceability | **Material.** `MD-B01` 170 → 155. `MD-B17` 93 → 102, `MD-B19` 219 → 222, `MD-B10` 452 → 454, `MD-B15` 88 → 89. Global required unchanged at 2010: rules moved, none created or destroyed. |
| Schema / config / runtime / provider / backfill / replay / tests / ops | **None.** No file outside the matrix and its derived records is touched. |
| Evidence | Coverage denominators cited by evidence issued before this attempt were taken against the prior per-stage split and remain true as issued. |
| Runtime artifacts | **None.** This is document-only work under `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` section 5 — traceability ownership analysis claiming no runtime proof — so no `storage/**` inspection is required or performed, and no raw-artifact linkage is claimed. |

## Compatibility risk

**Low.** No rule changed state, no `SATISFIED` moved, and the applier structurally refuses to move a proven row. Stages receiving rules gain obligations they can actually evidence; `MD-B01` sheds obligations it never could.

## Residue / rework risk

**Low.** 15 of 170 moved; 155 stayed. The residual risk is under-correction, not over-correction: rules that arguably belong downstream were left with `MD-B01` where the case was not clear-cut. That is recoverable at the receiving stage.

## Affected dependencies and relationships

- `MD-DEP-0004` — `MD-B01`'s ownership is re-derived. The dependency does not close: the other 17 stages' assignments were not individually re-derived.
- `F-MD-B01-A001-001` — its secondary concern is addressed for `MD-B01`.

## Strategy semantic change

`NO`.

## Issuance timing — recorded, not smoothed over

`CHANGE_IMPACT_DECLARATION_STANDARD.md` section 3 requires the declaration to exist early enough to guide the attempt validation scope, and to be current before stage/attempt closure.

The second requirement is met: this record is current and precedes any closure. The first is met only in substance, not in file order. The scope decisions it states — which 15 rules move, which stay, why the keyword classifier was rejected — were all made during the dry-run analysis that preceded the mutation, and this record documents that analysis. But the file itself was written after `ownership_apply.php --apply` had already run.

That is a process deviation. It is recorded here rather than corrected by backdating, for the same reason `MD-B01-A003` recorded its late baseline lock instead of restating it: a record that quietly claims better timing than it had is worth less than one that states its own limits. The authority binding that matters — strategy freeze, epoch, and the pre-mutation matrix fingerprint `2BD318991C98575B3C87C8EA58AF71F1AADCBE2E` — was captured in `MD-B01-A005-BL001` before any change, so the change remains fully diffable against a fixed reference.

Do-not-repeat: write the Change Impact Declaration at the point the dry run produces its numbers, before the applier runs, not after.
