# Executed Run Admission Criteria (LOCKED)

## Purpose
Define when an artifact may be honestly described as a real executed evidence artifact rather than a shape example or proof-spec example.

## Core rule (LOCKED)
An artifact may be labeled as executed evidence only if it is sourced from an actual run, replay, correction, or test execution context.

## Admission criteria
A candidate executed artifact must satisfy all of the following:

1. it references a real execution identity, such as:
   - run_id
   - replay_id
   - correction_id
   - test_id plus actual execution metadata

2. it contains actual produced values, such as:
   - real hashes
   - real counts
   - real timestamps
   - real terminal states
   - real publication IDs

3. it is not composed solely of placeholders such as:
   - `H1B`
   - `A1`
   - `example_run_id`
   - fabricated timestamps

4. it can be traced back to a real execution context or archived execution bundle

## Recommended labeling
Executed evidence artifacts should explicitly state one of:
- `Source: actual executed run`
- `Source: actual executed replay`
- `Source: actual executed correction`
- `Source: actual executed test`

## Non-admissible examples
The following do not count as executed evidence:
- illustrative JSON shapes with placeholder hashes
- manually fabricated example outputs with no execution trace
- examples whose values are obviously symbolic only

## Cross-contract alignment
This contract must remain aligned with:
- `Run_Execution_Evidence_Pack_Contract_LOCKED.md`
- examples folder artifacts
- testing proof contracts

## Anti-ambiguity rule (LOCKED)
If a reader cannot tell whether an artifact is illustrative or actually executed, the evidence labeling is too weak.
## Capability boundary (LOCKED)

**What run admission proves.** That an executed run carried the identity, evidence, and completeness required before its results may be cited, and that a run failing those criteria is excluded rather than partially counted.

**What it cannot prove.**

- **That an admitted run produced correct data.** Admission checks the run's evidence, not its output. A well-evidenced run over wrong inputs is fully admissible.
- **That the criteria cover what matters.** A run is admitted against declared requirements. A missing binding nobody required — as configuration snapshots were until recently — does not block admission.
- **That admission history is comparable across versions.** Runs admitted under earlier criteria were held to a different bar, and the record does not distinguish them unless the criteria version is bound.

Consequently an admitted run may be cited as evidence that **its evidence met the declared bar**, never as evidence that **its results are sound**.
