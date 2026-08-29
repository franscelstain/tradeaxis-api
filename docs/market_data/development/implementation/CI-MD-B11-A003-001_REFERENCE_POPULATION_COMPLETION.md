# Change Impact Declaration — `MD-B11-A003`

- ID: `CI-MD-B11-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B11` / `MD-B11-A003` / `MD-B11-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B11-A002-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Complete the `MD-B11` reference population that `MD-B11-A002` left at 167 rows, and empty the last
closed-stage backlog in the package.

## 1. Affected strategy IDs and rules

**Promoted `REFERENCE_ONLY` → `REQUIRED`/`MANDATORY` (30):**

| Document | Rules |
|---|---|
| `MD-S010` | `R0017`–`R0020`, `R0025` — flag semantics and the explainability acceptance criterion |
| `MD-S011` | `R0003` core safety rule; `R0037`, `R0038`, `R0040`–`R0042` `GAP_AMBIGUOUS` resolution; `R0055` candidate-break linkage |
| `MD-S080` | `R0010`–`R0012`, `R0026`, `R0028` storage requirements; `R0032` `FAIL_CLOSED` board identity; `R0045` consumer boundary; `R0061` acceptance criterion |
| `MD-S084` | `R0002`, `R0004`–`R0007` detector-only boundary; `R0030`, `R0031` classification limits; `R0036` quarantine; `R0042` no-repair; `R0045` idempotency |

**Recorded as reference with basis (137):** `MD-S079` 90, `MD-S080` 28, `MD-S011` 10, `MD-S010` 5,
`MD-S084` 4.

Denominator 172 → **202**; reference 239 → **209**; non-structural reference without a recorded
decision 167 → **0**.

## 2. Affected areas

- **Schema / migration / configuration / provider / backfill / runtime**: none. No application source
  is changed.
- **Tests / gates / generators**: five new proof families (`event_risk_flag_semantics`,
  `detector_authority_boundary`, `exchange_market_structure_authority`, `dual_use_fact_boundary`,
  `detector_idempotency`) and 27 new rule-family overrides; a completion pass whose completeness is
  re-scanned after writing; the B11 binder and both B11 gates extended to accept the A003 pair.
- **Operator / ops behaviour**: none.
- **Evidence / proof mechanics**: `MD-B11` is admitted to `DECISION_RECORDED_STAGES`, which switches
  `UNEXPLAINED_REFERENCE` and `MIXED_RUN` from counting this stage to enforcing on it.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

No external runtime artifact is material. Every promoted predicate is proven by repository tests over
application source. MariaDB is reachable and the suite exercises it, but this attempt writes no
business row, mutates no stored data and inspects no `storage/**` artifact, so
`RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` linkage is not engaged.

## 4. Compatibility risk

`E-MD-B11-A001-001`, `E-MD-B11-A002-001` and `E-MD-B11-A002-002` are not edited; the 138 and 34
bindings stand. Only the final 30 carry the A003 pair. No stage other than `MD-B11` has a row
altered, and the pass aborts if it touches one.

## 5. Residue and rework risk

Confined to the four contracts above and the B11 review, binder and gate tooling. **No new guard was
written**: every promoted predicate is proven by a guard that already existed, and all 28 family
guard methods were verified to exist by name before any promotion was applied.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is fully discharged for `MD-B11`. **No closed stage in the package retains a
reference-population backlog.** 1384 rows remain across eleven unopened stages for resolution at
entry. Relationships to register: baseline→A002 closure precondition, CI→baseline, evidence→baseline
and CI, evidence→`E-MD-B11-A002-001` additive lineage, evidence→`SC-MD-B00-A004-001`.

## 7. Strategy meaning change

**NO.** No strategy byte is changed. Classification correction under
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §9.

## Why MD-S079 contributes 90 reference decisions and zero promotions

`MD-S079` already carries **36 `REQUIRED` rows** covering its operative obligations: the
unknown-action-type policy, the forbidden-behaviour list promoted in A002, the dictionary table
contract, the capability boundary, and the current event-lifecycle corrections. The document wraps at
roughly 100 characters, so what remains is continuation lines and enum vocabulary whose predicates
complete in those `REQUIRED` siblings. Promoting the fragments would duplicate a closure obligation
contrary to §7 and bind predicates to text that does not state them.

## The eight sanctioned mixed-run exceptions

Admitting `MD-B11` to the allowlist turned `MIXED_RUN` from counting to enforcing, and it immediately
reported eight rows: `MD-S011-R0057`–`R0059` and `MD-S011-R0076`–`R0078`, `MD-S079-R0138`,
`MD-S079-R0140`.

These are the case the gate's own exception mechanism exists for — semantic context owned by another
stage, or a capability boundary — and both permitted bases apply literally. The first three are
product semantics owned by the `REQUIRED` `MD-S083-R0002`–`R0005` under `MD-B12`
(`downstream_price_product`); the other five are *what this contract cannot prove*
(`capability_limitation`). The exception map lives in the spec so the call is reproducible rather
than a hand edit to the matrix.

## Closure boundary

The six conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`, a completeness assertion re-scanned after
the pass and proven to fail, no harmful residue, current evidence, complete relationships, and all
governance gates passing with the database reachable.

## Actual impact and result

- **Traceability**: 30 promoted, 137 decided; denominator 172 → **202**; B11 unexplained reference
  167 → **0**; `MD-B11` admitted to `DECISION_RECORDED_STAGES`.
- **Five new families were necessary, not cosmetic.** `MD-B11` assigns families per document. Four of
  the five promoted groups would have inherited guards that do not prove them — `MD-S080` rows would
  have inherited detector-only guards, `MD-S010` rows window-contamination guards. Binding them to
  the document default would have claimed proof those families do not carry.
- **The start-state guard was kept, not loosened.** Re-running the pass to apply the exception markers
  hit the guard that forbids promoting a rule which was not `REFERENCE_ONLY`. Rather than weaken it,
  the pass now recognises a rule it promoted itself by its own note; a rule `REQUIRED` for any other
  reason still errors, proven by control.
- **Application source changed**: **NO**. **Strategy changed**: **NO**.
