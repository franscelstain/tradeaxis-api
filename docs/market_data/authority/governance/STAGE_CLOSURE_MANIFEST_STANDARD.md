# Stage Closure Manifest Standard

A terminal stage publishes immutable `SC-MD-Bxx-Ayyy-NNN` containing Stage/Attempt/Baseline/Epoch, required coverage result, tests, residue verdict, dependencies, findings/decisions, integrity results, evidence IDs, and successor/resume state. A stage table alone is not closure evidence.

## Runtime/raw-artifact effect

When closure relies on executed proof whose material output is external to docs, the closure proof chain MUST satisfy `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.

The closure manifest does not need to duplicate raw logs. It MUST identify the governed evidence records from which required raw artifact path/manifest and integrity linkage are deterministically reachable.

For required external raw proof, closure MUST NOT claim execution sufficiency when:

- the governed evidence has no required artifact linkage;
- the artifact required by the applicable proof contract was not actually inspected or produced;
- required hash/manifest integrity fails;
- the available environment could not access the artifact and no valid replacement execution was performed.

Document-only gates do not substitute for runtime-artifact verification when runtime proof is part of the stage acceptance criteria.

## Traceability semantic/applicability effect

A terminal stage closure MUST use the semantic-predicate and applicability lifecycle from `STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md`.

Closure MUST report and satisfy all of the following for the stage:

- zero required rows with transitional `MANDATORY_OR_CONDITIONAL`;
- zero `CONDITIONAL_PENDING` / `APPLICABILITY_PENDING` rows;
- all `MANDATORY` and `CONDITIONAL_APPLICABLE` denominator rows are `SATISFIED`;
- every `CONDITIONAL_NOT_APPLICABLE` row has current evidence/rationale proving its condition false;
- every context-dependent required fragment has deterministic parent/context binding and a normalized predicate;
- any previously `SATISFIED` proof invalidated by semantic-context/applicability correction has been re-proven or is no longer counted.

A stage MUST NOT claim `DONE` from a percentage computed over a provisional denominator.
