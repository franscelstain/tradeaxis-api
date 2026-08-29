# Change Impact Declaration — `MD-B11-A002`

- ID: `CI-MD-B11-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B11` / `MD-B11-A002` / `MD-B11-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B11-A001-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED — SCOPED`
- Strategy meaning change: `NO`

## Issuance sequence, stated plainly

`CHANGE_IMPACT_DECLARATION_STANDARD.md` §3 requires this record to exist early enough to guide the
attempt's validation scope and to be current before closure. The scope was derived and stated before
any matrix change — the 34-row set, its family mapping, and the guard-coverage check were all
completed first, and `MD-B11-A002-BL001` was issued before the promotions were applied. This formal
record was written after the promotions and before closure. The declared scope is exactly what was
executed; nothing here is retrofitted to match an outcome.

## 1. Affected strategy IDs and rules

| Document | Section | Rules |
|---|---|---|
| `MD-S011` | Verification hierarchy (LOCKED) | `R0023` |
| `MD-S011` | Effective-date hierarchy (LOCKED) | `R0044`–`R0048` |
| `MD-S011` | Forbidden behavior (LOCKED) | `R0061`–`R0072` |
| `MD-S079` | Forbidden behavior (LOCKED) | `R0129`, `R0130`, `R0131`, `R0135` |
| `MD-S080` | Prohibited use (LOCKED) | `R0046`–`R0050` |
| `MD-S084` | Forbidden behavior | `R0046`–`R0052` |

34 rows promoted `REFERENCE_ONLY` → `REQUIRED`/`MANDATORY`. Denominator 138 → **172**; reference
273 → **239**.

`MD-S079-R0132`, `R0133` and `R0134` are continuation lines of the wrapped sentence headed by
`R0131` and stay reference; the composed predicate is recorded on `R0131`.

## 2. Affected areas

- **Schema / migration**: none.
- **Configuration**: none.
- **Runtime behaviour**: none. No application source is changed by this attempt.
- **Provider / source behaviour**: none.
- **Backfill / replay**: none.
- **Tests / gates / generators**: one new structural guard
  (`CorporateActionLifecycleB11RegressionTest::test_the_continuity_diagnostic_never_reaches_an_authority_decision`);
  a new proof family `continuity_diagnostic_boundary` with a per-rule override; the B11 proof gate,
  traceability gate and binder made attempt-aware and constant-driven instead of hardcoding `A001`
  and the literals `138` and `273`; a new scoped review pass with three fail-closed guards.
- **Operator / ops behaviour**: none.
- **Evidence / proof mechanics**: the binder previously accepted only `A001` evidence and required
  the entire denominator pristine. That would have forced this attempt to unbind the 138 closed A001
  predicates in order to rebind them — the shape that lost 30 predicates in `MD-B07-A002`.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

No external runtime artifact is material to this attempt. Every promoted predicate is proven by
repository tests over application source. `storage/**` was not inspected and not mutated; no database
was mutated. `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` linkage requirements are therefore
not engaged, and no proof-linkage, retention or integrity change can affect existing current evidence
or closure.

## 4. Compatibility risk

`E-MD-B11-A001-001` is not edited and its 138 bindings stand unchanged. Only the 34 newly promoted
predicates carry the A002 pair. No stage other than `MD-B11` has a row altered by this attempt, and
the review pass aborts if it touches one.

## 5. Residue and rework risk

The correction is confined to the four prohibition sections, the two date hierarchies, and the B11
review/binder/gate tooling. Residue search scope is that surface. No application behaviour changes,
so no behavioural residue is possible; the risk that remains is tooling correctness, addressed by
making each new guard demonstrably fail.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is partially discharged for `MD-B11`; 167 non-structural reference rows remain for
`MD-B11-A003`, so `MD-B11` is **not** admitted to `DECISION_RECORDED_STAGES` and the classification
gate continues to count it. Relationships to be registered: baseline→A001 closure precondition,
CI→baseline, evidence→baseline and CI, evidence→`E-MD-B11-A001-001` additive lineage,
evidence→`E-MD-B12-A002-001` (the twin invariant, guarded there first), evidence→`SC-MD-B00-A004-001`
(the gate that surfaced these sections).

## 7. Strategy meaning change

**NO.** No strategy byte is changed. This is a classification correction under
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §9, where strategy meaning is unchanged and the
matrix is `MUTABLE_TRACEABLE`.

## Closure boundary

Closure requires the six conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`: zero transitional
applicability, zero `CONDITIONAL_PENDING`, every denominator row `SATISFIED`, evidenced conditions for
any `CONDITIONAL_NOT_APPLICABLE`, deterministic parent/context binding for every context-dependent
required fragment, and re-proof of anything a semantic correction invalidated. Plus the new guard
demonstrated to fail when removed, no harmful residue, current evidence, complete relationships, and
all governance gates passing.

## Actual impact and result

- **Traceability**: 34 promoted; denominator 138 → **172**; B11 unexplained reference 201 → **167**.
- **The audit's headline invariant is now fully claimed.** `MD-S011-R0023` was the rule used to
  demonstrate the whole problem: enforced by one line at `AdjustmentFactorSetService`, and widening
  that filter passed all 1946 tests while no traceability row claimed it. Its `MD-B12` twin was bound
  in `MD-B12-A002`; this binds the remaining copy to the same guard.
- **The expected gap was not a gap.** `MD-S011-R0070` and `R0071` were reported as unguarded.
  `continuity_check_status`, `observed_gap_pct`, `GAP_BEYOND_EXCHANGE_BAND` and `GAP_AMBIGUOUS`
  appear in **zero** files under `app`, `config` and `routes` — only in the migration that declares
  the column diagnostic-only, the SQLite mirror, and three test files. Both are prohibitions
  satisfied by construction, and the correct proof is a structural invariant pinning that, not a
  behavioural branch that does not exist.
- **Guard coverage verified before binding, not assumed**: seven existing family guards were checked
  to exist by name before any promotion was applied.
- **Zero whole prohibition sections remain at `required=0` anywhere in the package.**
- **Application source changed**: **NO**. **Strategy changed**: **NO**.
