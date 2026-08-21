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
