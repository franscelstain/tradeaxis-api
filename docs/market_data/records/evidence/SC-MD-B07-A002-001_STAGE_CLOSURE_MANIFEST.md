# MD Stage Closure Manifest — SC-MD-B07-A002-001

- ID: `SC-MD-B07-A002-001`
- Stage / Attempt / Baseline / Epoch: `MD-B07` / `MD-B07-A002` / `MD-B07-A002-BL001` / `MD-REBASELINE-20260820-001`
- Strategy freeze: `MD-STRATEGY-FREEZE-20260823-001`
- Change Impact Declaration: `CI-MD-B07-A002-001`
- Governed evidence: `E-MD-B07-A002-001`
- Stage precondition: `SC-MD-B07-A001-001`
- Dependency: `MD-DEP-0004` B07 entry obligation re-completed at full stage scope; dependency remains `OPEN_NON_BLOCKING` for later stages
- Role: `EVIDENCE`, scope `STAGE_CLOSURE_MANIFEST`, mutability `IMMUTABLE_AFTER_ISSUE`
- Supersedes: nothing. `SC-MD-B07-A001-001` remains immutable and is not edited; this manifest records the corrected denominator that supersedes its coverage claim only.

## Closure verdict

`MD-B07` is `DONE` with verdict `PASS` under `MD-B07-A002`. The corrected B07 semantic denominator is
**116 mandatory / 116 SATISFIED**, zero transitional applicability, zero conditional pending, zero
unbound proof, zero B07 mixed-classification debt.

`SC-MD-B07-A001-001` closed at 115/115. That figure was not wrong about the rows it measured; it was
measured over 166 of the 167 rows the matrix assigns to this stage. The 116th predicate,
`MD-S066-R0002` (`Mask tokens/cookies/keys.`), was never examined by the A001 pass.

## What was corrected and why

`MD-S066` contains exactly two imperative sentences. `MD-S066-R0001` was bound as a mandatory
predicate with a full proof chain; `MD-S066-R0002` was left `REFERENCE_ONLY` with empty notes. The
two are indistinguishable in form, subject, and enforceability.

The cause was structural. `MarketDataSourceAcquisitionNormalization.php` selected its own scope from
`SOURCE_DOCUMENT_COUNTS` plus a hand-curated `EXTERNAL_RULES` list, and any B07-owned row in neither
list hit an unguarded `continue`. Nothing in the pass could report the omission, and the
classification consistency gate could not see it either: `MIXED_RUN` fires only inside an enumerated
run holding both classes, and both `MD-S066` rows are paragraph lines with no list marker.

## Executed proof

- B07 proof surface: **19 files, 194 tests, 1095 assertions, 0 failures, exit 0** (A001: 18 files,
  165 tests, 824 assertions). The added file is `ApiBackfillLifecycleStaticGuardTest`, joining the
  `observation` family because the checkpoint-cache masking path is a surface the envelope tests do
  not cover.
- Full suite: **1946 tests, 18210 assertions, 0 failures, 0 errors, exit 0**.
- B07 traceability gate: `PASS` — 116 mandatory, 88 moved, 51 reference, 162 contextual, 255 reviewed.
- B07 proof gate: `PASS` — 116 denominator, 116 satisfied, 0 unbound.
- Documentation integrity gate: `PASS`. Relationship integrity gate: `PASS` — 147 records, 259
  relationships, 0 validity errors, 0 completeness gaps. Classification consistency gate: `PASS`.
  Traceability applicability gate: `PASS`.

## Falsifiability proof

Neither guard was accepted on the strength of a green run.

- **Masking guard**: `BackfillLifecycleOrchestrator::redactDiagnosticString()` was neutered to return
  its input unchanged. `ApiBackfillLifecycleStaticGuardTest::test_source_acquisition_cache_is_slim_valid_json_and_sanitized`
  failed at the `assertStringNotContainsString('SECRET', $raw)` assertion. The file was restored from
  git and verified byte-identical to `HEAD`.
- **Foreign-binding assertion**: the two restore lines were removed and the normalization aborted
  with `this pass unbound closed proof owned by another stage`, naming all 30 rules and their owning
  stages.
- **Scope completeness assertion**: `MD-S066-R0002` was removed from `EXTERNAL_RULES` and the
  normalization aborted with `B07-owned rows never examined by this normalization: MD-S066-R0002`.
  The assertion fires before the denominator check, so it reports the omission itself rather than its
  arithmetic symptom.

## Defect this attempt introduced, found, and corrected

Re-running the B07 normalization stripped `coverage_status` and `current_evidence_ids` from **30
rows whose proof owner is another stage** — 17 in `MD-B09`, 7 in `MD-B08`, 6 in `MD-B10`. The pass
cleared proof state for every row it examined, including the 88 it assigns to downstream owners.
Under A001 that was inert because those stages were unproven; once they closed, the same code
silently unbound their closed proof.

**All six gates and the full suite passed while those 30 predicates were unbound.** No governance
gate verifies that a closed stage still holds its bindings, so a stage pass can unbind another
stage and every green signal stays green.

Corrected by preserving prior proof state for any row this stage does not own, plus an assertion
that no row arriving `SATISFIED` under another owner leaves the pass unbound. Verified by restoring
the matrix from `HEAD` and re-applying both scripts deterministically: the diff against `HEAD` is
exactly one row, `MD-S066-R0002`, and per-stage `SATISFIED` counts are unchanged everywhere except
`MD-B07` (115 to 116).

## Residue and boundary verdict

- Verdict: `CONFORMANT_NO_HARMFUL_RESIDUE_FOUND_IN_THE_CORRECTED_B07_SURFACE`.
- Application source changed: **NO**. Schema/migration changed: **NO**. Configuration changed: **NO**.
- Storage inspected: **NO**. Database mutated: **NO**.
- The executable changes are the normalization scope-completeness assertion, foreign-binding
  preservation with its assertion, and attempt-awareness in the two B07 gates. All three guards are
  demonstrated falsifiable above.
- `E-MD-B07-A001-001` was not edited; its 115 bindings stand. Only the 116th predicate carries the
  A002 pair.

## Reference population re-check

All 51 remaining B07 reference rows were re-checked against `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`
sections 2 and 3: 26 list introducers, 1 bare label, 6 capability-boundary disclaimers, 8
purpose/rationale/pointer statements, 9 `MD-S054` normalized-field fragments whose obligations are
owned by the `REQUIRED` Mapping-rules siblings, and 1 permission granted to the promote phase. None
states an unowned executable obligation.

## Findings and dependencies

- No new finding is raised against B07: the corrected predicate is proven, and the mechanism that hid
  it is now guarded within this stage.
- **Reported, not fixed here**: the scope-selection defect is structural and not specific to B07.
  Every stage normalization that selects its scope from hand-curated lists can omit a row and still
  report success. The remaining stages are unverified against this class.
- **Reported, not fixed here**: the classification consistency gate still cannot see an
  obligation-bearing paragraph row filed as reference outside a mixed enumerated run.
- **Reported, not fixed here**: no governance gate verifies that a closed stage still holds its
  proof bindings, and every other stage normalization carries the same unguarded clearing code that
  unbound 30 predicates here.

## Correlation and closure chain

`SC-MD-B07-A001-001` → `MD-B07-A002-BL001` → `CI-MD-B07-A002-001` → `E-MD-B07-A002-001` →
`SC-MD-B07-A002-001`, registered as `MD-REL-0254` through `MD-REL-0259`.

## Exact successor state

`MD-B07` is closed at 116/116. `MD-B13` remains closed and is the latest stage. No downstream stage is
opened by this attempt. The single next executable resume point is the `MD-B08` reference-population
re-check under the same method, or the classification-gate hardening that would detect this defect
class across all remaining stages.
