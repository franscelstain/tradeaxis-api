# Change Impact Declaration — `MD-B18-A002`

- ID: `CI-MD-B18-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B18` / `MD-B18-A002` / `MD-B18-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260903-001`
- Predecessor attempt: `MD-B18-A001`, closure `SC-MD-B18-A001-001` — **withdrawn, not edited**
- Remediates: `F-MD-B19-A001-002` (P1)
- Blocking dependency: `MD-DEP-0009` — `MD-B19` is the blocked logical stage; return-to `MD-B19-A001`
- Status: `IN_PROGRESS — REMEDIATION`
- Strategy meaning change: `NO`
- Governance authority change: `NO`

Issued after `MD-B18-A002-BL001` and before any material `MD-B18` mutation, so that it directs the
attempt rather than describing it afterwards.

## Objective

Re-enter `MD-B18` to repair a closure that is not supportable, and re-close it only on proof that
establishes each predicate.

`MD-B18-A001` closed at `121/121`. `F-MD-B19-A001-002` measured two defects behind that figure:

1. **85 of 121 denominator rows carry no `predicate_context=` / `normalized_predicate=`**, which
   `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §3 requires and §8 makes a closure
   precondition. `MarketDataReplayVerificationClosureGate` lacked the condition its peer gates
   enforce, so the stage closed with eight green conditions and this unmeasured.
2. **121 predicates were bound to 11 guard pairs — one per family.** Checked against guard bodies,
   38 predicates are established by a different obligation and 20 by a proper subset. **58 of 85 are
   not established by the guard bound to them.**

## What this attempt does NOT do

- It does not edit `SC-MD-B18-A001-001`, `E-MD-B18-A001-001`, or any issued immutable record.
- It does not change strategy bytes. Every predicate here is composed from parent and child text
  already frozen in the owner document; composition is recorded in `notes`, not in `rule_text`.
- It does not weaken a predicate to make a guard fit. Where the guard is insufficient, the guard is
  written or the row returns to `NOT_ASSESSED` — the predicate is not narrowed.
- It does not re-open `MD-B09`, `MD-B10`, `MD-B11` or `MD-B12`, which carry the same §8 records gap
  (38, 7, 129, 1). Those are reported in `F-MD-B19-A001-002` and are not owned here.
- It makes no claim about `MD-B14`–`MD-B17`, which share the one-pair-per-family shape but were not
  measured predicate by predicate.

## 1. Measured entry state

Measured against the matrix this attempt's baseline locks, before any mutation:

| | |
|---|---|
| `MD-B18` rows, all classes | 155 |
| Denominator (`MANDATORY` + `CONDITIONAL_APPLICABLE`) | **121** |
| — carrying a normalized predicate | 36 |
| — **carrying none** | **85** |
| Distinct positive guards across the denominator | **11** |
| Distinct proof families | 11 |
| Predicates per guard | **11.0** — the highest in the package |
| `coverage_status = SATISFIED` at entry | 121 |

The `121/121` is the figure this attempt must stop asserting until re-proven. It is carried here as
the entry measurement, not as inherited coverage.

## 2. Scope of intended mutation

**Traceability matrix (`MUTABLE_TRACEABLE`)** — `MD-B18` rows only:

- add `predicate_context=` and `normalized_predicate=` to all 121 denominator rows;
- return rows whose bound guard does not establish their predicate to `NOT_ASSESSED`, clearing
  `current_evidence_ids` for those rows;
- where the composed predicate belongs to another stage's executable responsibility, correct
  `primary_stage` under §7 rather than manufacturing a guard here.

No row outside `primary_stage = MD-B18` is written. Foreign-row isolation is asserted by the binder
and by a closure condition, after `MD-B18-A001`'s binder silently re-quoted 6226 rows it did not own.

**Proof surface** — `MarketDataReplayVerificationProofSpec`, `ProofGate`, `ProofSelfTest`,
`ProofBinder`, `ClosureGate`:

- the gate gains the reviewed per-predicate proof basis requirement already added to
  `MarketDataOperationsProofGate`;
- the closure gate's `context_binding_and_normalized_predicate` condition, added under
  `MD-B19-A001` when the defect was found, stays and is re-probed here.

**Tests** — new guards under `tests/Unit/MarketData/` for predicates that currently have none.

**Application code** — none intended. If a guard written here fails against real behaviour, that is
a finding and a fix in its own right, not an occasion to adjust the guard.

## 3. Re-verification required before closure

- every one of the 121 rows carries a parent/context binding and a normalized predicate;
- every row still `SATISFIED` names a guard that establishes **that** predicate, with a reviewed
  one-line basis;
- the closure gate's nine conditions each independently mutation-proven;
- proof gate green in `--bound` mode with zero rows lacking a basis;
- full suite green before and after binding;
- new evidence and a new closure manifest issued; `MD-DEP-0009` discharged.

## 4. Risk this declaration accepts

The honest outcome of §3 may be that `MD-B18` closes at **less than 121/121**, with predicates
returned to `NOT_ASSESSED` or reassigned to their owning stage. That is an acceptable and expected
result. `MARKET_DATA_DOCUMENT_AUTHORITY.md` §9 forbids manufacturing a pass; a smaller honest
denominator with real proof is the goal, not the restoration of the previous number.
