# Executed Proof Admission Criteria (LOCKED)

## Purpose
Define the minimum conditions under which a test, replay, or correction evidence artifact may be claimed as executed proof rather than proof-spec or example-only material.

## Core rule (LOCKED)
Executed proof requires:
- real execution identity
- real produced outputs
- real pass/fail or match/mismatch result
- traceable origin
- exact owner-contract/fixture version
- executable build/commit identity
- database engine/schema/migration state
- immutable input/observation/config/temporal/factor/product/formula/read-model identities and hashes applicable to the case
- evidence artifact paths and hashes

## Required conditions

### For executed test proof
Must include:
- actual `test_id`
- actual fixture family
- actual execution result
- actual observed values or summaries
- production-path layer and database/runtime identity
- expected-versus-actual semantic bindings and reasons

### For executed replay proof
Must include:
- actual `replay_id`
- replay mode and fixture manifest hash
- actual trade_date
- knowledge cutoff for `AS_KNOWN`
- actual comparison result
- actual produced hashes or mismatch evidence
- frozen observation/config/temporal/factor/product/formula/read-model identities

### For executed correction proof
Must include:
- actual correction context
- actual old/new publication references
- actual old/new hashes
- actual switch result
- immutable before/after observation/config/factor/product lineage and complete recursive impact scope

## Prohibited weak claims
The following must not be described as executed proof:
- shape-only examples
- symbolic placeholder outputs
- manually constructed pseudo-pass outputs
- evidence without traceable execution identity

## Relationship to examples
Examples may still be kept in docs, but they must be distinguishable from executed proof artifacts.

## Cross-contract alignment
This contract must remain aligned with:
- `Test_Implementation_Guidance_LOCKED.md`
- `../ops/Run_Execution_Evidence_Pack_Contract_LOCKED.md`
- examples folder evidence docs

## Anti-ambiguity rule (LOCKED)
If an artifact can be mistaken for real executed proof when it is only illustrative, the proof labeling layer is incomplete.
