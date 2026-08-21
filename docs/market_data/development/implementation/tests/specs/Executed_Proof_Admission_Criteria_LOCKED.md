# Executed Proof Admission Criteria (LOCKED)

## Purpose

Define the minimum conditions under which a test, replay, or correction evidence artifact may be claimed as executed proof rather than proof-spec or example-only material.

This technical criterion is subordinate to the current governance boundary in `../../../../authority/governance/RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.

## Core rule (LOCKED)

Executed proof requires:

- real execution identity;
- real produced outputs;
- real pass/fail or match/mismatch result;
- traceable origin;
- exact owner-contract/fixture version;
- executable build/commit identity;
- database engine/schema/migration state;
- immutable input/observation/config/temporal/factor/product/formula/read-model identities and hashes applicable to the case;
- evidence artifact paths and hashes when material output is retained externally;
- current Stage/Attempt/Baseline/Epoch correlation through a governed evidence record when the result is used for current verification.

A raw `storage/**` artifact alone is not a current evidence record.

## Required conditions

### For executed test proof

Must include:

- actual `test_id` or deterministic test/command identity;
- actual fixture family;
- actual execution result;
- actual observed values or summaries;
- production-path layer and database/runtime identity where applicable;
- expected-versus-actual semantic bindings and reasons;
- material raw output path/manifest and integrity hash where the external artifact is part of the proof.

### For executed replay proof

Must include:

- actual `replay_id`;
- replay mode and fixture manifest hash;
- actual trade_date;
- knowledge cutoff for `AS_KNOWN`;
- actual comparison result;
- actual produced hashes or mismatch evidence;
- frozen observation/config/temporal/factor/product/formula/read-model identities;
- retained raw replay/evidence artifact linkage when required by the governing proof contract.

### For executed correction proof

Must include:

- actual correction context;
- actual old/new publication references;
- actual old/new hashes;
- actual switch result;
- immutable before/after observation/config/factor/product lineage and complete recursive impact scope;
- retained evidence/export artifact linkage when required by the governing proof contract.

## Prohibited weak claims

The following must not be described as executed proof:

- shape-only examples;
- symbolic placeholder outputs;
- manually constructed pseudo-pass outputs;
- evidence without traceable execution identity;
- an old raw artifact discovered in storage with no current governed correlation;
- a path that exists but cannot be tied to the claimed execution.

## Read-order rule

Do not scan all of `storage/**` to search for a convenient PASS.

Current docs/records select the proof obligation first. Inspect only the raw artifacts that are referenced by current evidence or required to prove that obligation.

## Relationship to examples

Examples may still be kept in docs, but they must be distinguishable from executed proof artifacts.

## Cross-contract alignment

This criterion must remain aligned with:

- current strategy test/proof contracts;
- `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`;
- current ops/run evidence contracts;
- applicable example/evidence documentation.

## Anti-ambiguity rule (LOCKED)

If an artifact can be mistaken for real executed proof when it is only illustrative, historical, uncorrelated, or unverifiable, the proof labeling/admission layer is incomplete.
