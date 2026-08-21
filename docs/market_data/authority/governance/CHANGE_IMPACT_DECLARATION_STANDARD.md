# Change Impact Declaration Standard

## 1. Requirement

Every material current attempt MUST issue a correlated Change Impact Declaration record using the attempt Work ID, for example `CI-MD-B05-A001-001`, and register it in the current work-record registry.

A material attempt includes work that changes or materially revalidates any of the following:

- schema/migration;
- configuration;
- runtime behavior;
- provider/source behavior;
- backfill/replay;
- tests/gates/generators;
- operator/ops behavior;
- evidence/proof mechanics;
- compatibility or residue risk;
- traceability/baseline/closure behavior.

## 2. Required declaration content

The declaration MUST identify:

- affected strategy IDs/rules;
- affected schema/config/runtime/backfill/tests/ops/evidence areas;
- affected raw-artifact storage/path/manifest/hash/retention mechanics when executed proof is involved;
- compatibility risk;
- residue/rework risk;
- affected dependencies/relationships where applicable;
- whether strategy meaning changes.

Strategy meaning change is prohibited during normal implementation revalidation unless separately approved through `DOCUMENT_CHANGE_POLICY.md`.

## 3. Timing and closure

The declaration MUST exist early enough to guide the attempt's validation scope and MUST be current before stage/attempt closure.

For a material attempt, a missing/unregistered Change Impact Declaration blocks `DONE`, even if tests and other gates pass.

If the impact scope changes materially during the attempt, update the mutable declaration if its record class permits it, or issue the required correlated successor/correction record according to the recording standard.

## 4. No generic substitution

Impact statements embedded only in evidence, commit messages, prose findings, or final summaries do not replace the formal correlated `CI-*` record when this standard requires one.

When external runtime artifacts are material to the attempt, the declaration MUST apply `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` and state whether proof linkage/retention/integrity changes can affect existing current evidence or closure.
