# Change Impact Declaration — `MD-B12-A003`

- ID: `CI-MD-B12-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B12` / `MD-B12-A003` / `MD-B12-A003-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Stage precondition: `SC-MD-B12-A002-001`
- Dependencies: `MD-DEP-0004` stage-entry semantic normalization
- Status: `EXECUTED`
- Strategy meaning change: `NO`

## Objective

Complete the `MD-B12` reference population that `MD-B12-A002` deliberately left partial: resolve all
39 outstanding rows and admit the stage to `DECISION_RECORDED_STAGES`.

## 1. Affected strategy IDs and rules

**Promoted `REFERENCE_ONLY` → `REQUIRED`/`MANDATORY` (15):**

| Document | Section | Rules |
|---|---|---|
| `MD-S012` | Version/change rule | `R0030`–`R0034` |
| `MD-S083` | Factor provenance vocabulary (LOCKED) | `R0006`, `R0017`, `R0019` |
| `MD-S083` | Factor revision model | `R0030` |
| `MD-S083` | Structural-adjustment formula (LOCKED) | `R0038` |
| `MD-S083` | Contamination behavior | `R0048`–`R0052` |

**Recorded as reference with basis (24):** `MD-S012-R0007`, `R0009`, `R0023`–`R0027`, `R0037`,
`R0038`, `R0039`; `MD-S083-R0001`, `R0014`, `R0016`, `R0018`, `R0020`, `R0021`, `R0043`–`R0047`,
`R0065`–`R0067`.

Denominator 60 → **75**; reference 63 → **48**; non-structural reference without a recorded decision
39 → **0**.

## 2. Affected areas

- **Schema / migration / configuration / provider / backfill**: none.
- **Runtime behaviour**: none. No application source is changed.
- **Tests / gates / generators**: five new proof families (`correction_recompute_lineage`,
  `deterministic_oracle`, `factor_provenance_vocabulary`, `structural_adjustment_formula`,
  `candidate_quarantine_boundary`); a new completion pass with an absolute completeness assertion;
  the B12 binder and both B12 gates extended to accept the A003 attempt pair.
- **Operator / ops behaviour**: none.
- **Evidence / proof mechanics**: `MD-B12` is admitted to `DECISION_RECORDED_STAGES`, which makes
  `UNEXPLAINED_REFERENCE` enforce on this stage from now on rather than merely count it.

## 3. Raw-artifact storage, path, manifest, hash and retention mechanics

No external runtime artifact is material to this attempt. Every promoted predicate is proven by
repository tests over application source. MariaDB is reachable and the suite exercises it, but this
attempt writes no business row, mutates no stored data and inspects no `storage/**` artifact, so
`RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` linkage is not engaged and no proof-linkage or
retention change can affect existing current evidence or closure.

## 4. Compatibility risk

`E-MD-B12-A001-001` and `E-MD-B12-A002-001` are not edited; their 45 and 15 bindings stand. Only the
final 15 predicates carry the A003 pair. No stage other than `MD-B12` has a row altered, and the
completion pass aborts if it touches one.

## 5. Residue and rework risk

Confined to the `MD-S012` version/change rule, the `MD-S083` provenance, revision, formula and
contamination sections, and the B12 review, binder and gate tooling. No application behaviour
changes, so no behavioural residue is possible. **No new guard was written**: every promoted
predicate is proven by a guard that already existed, and each was verified to exist by name before
the promotion was applied.

## 6. Affected dependencies and relationships

`MD-DEP-0004` is fully discharged for `MD-B12`. `MD-B11-A003` at 167 rows becomes the last
closed-stage backlog. Relationships to register: baseline→A002 closure precondition, CI→baseline,
evidence→baseline and CI, evidence→`E-MD-B12-A002-001` additive lineage,
evidence→`SC-MD-B00-A004-001` (the gate whose count this drives to zero for B12).

## 7. Strategy meaning change

**NO.** No strategy byte is changed. Classification correction under
`STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` §9.

## What is deliberately not promoted

§7 forbids duplicating a closure obligation a `REQUIRED` row already owns, and several rows read like
strong candidates:

- `MD-S012` coherence bullets `R0023`–`R0027` — owned by the `REQUIRED` `MD-S083-R0039` and
  `MD-S012-R0028`; `R0027` raw immutability is owned by `MD-S083-R0054`, promoted in A002.
- `MD-S012-R0007` and `R0009` — the binding components and stored-state requirement are owned by
  `MD-S012-R0011`–`R0017`.
- `MD-S012-R0038` — restates obligations owned by `MD-S083-R0069` and `MD-S083-R0053`.
- `MD-S083-R0014`, `R0016`, `R0018`, `R0020`, `R0021` — continuation lines of wrapped sentences whose
  heads are `REQUIRED` or promoted here.
- `MD-S083-R0043`–`R0047`, `R0065`–`R0067` — detector emission enumeration, its limit, and what the
  product cannot prove. `R0067` states outright that it restates a prohibition already owned.

## Closure boundary

The six conditions in `STAGE_CLOSURE_MANIFEST_STANDARD.md`, plus a completeness assertion proven to
fail, no harmful residue, current evidence, complete relationships, and all governance gates passing
with the database reachable.

## Actual impact and result

- **Traceability**: 15 promoted, 24 decided; denominator 60 → **75**; B12 unexplained reference
  39 → **0**; `MD-B12` admitted to `DECISION_RECORDED_STAGES`.
- **A wrong guess the check caught.** The `deterministic_oracle` family first named
  `IndicatorIndependentOracleTest::test_indicator_vector_matches_the_independent_oracle`, which does
  not exist. Verifying all 34 family guard methods by name before promoting caught it; the family now
  names `test_correction_oracle_propagates_by_exactly_the_expected_amount`. Without that check this
  attempt would have bound a predicate to a guard nobody wrote.
- **Completeness proven falsifiable**: removing `MD-S083-R0067` from the decision list made the pass
  abort reporting that row unaccounted for. Injecting a mutation on `MD-S011-R0023`, a `MD-B11` row,
  made it abort naming the foreign stage.
- **Application source changed**: **NO**. **Strategy changed**: **NO**.
