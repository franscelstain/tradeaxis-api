# Market Data Documentation Architecture

## Canonical layers

`authority → development → records`

- `authority/strategy`: current Market Data behavior/product/operational strategy.
- `authority/governance`: current process/verification authority.
- `development/implementation`: technical realization and active revalidation.
- `development/research`: current research.
- `development/findings`: current finding lifecycle.
- `records/evidence`: governed evidence records; raw runtime/test/replay artifacts normally remain outside docs under configured `storage/**` paths.
- `records/decisions`: issued decisions.
- `records/history`: historical/superseded/provenance.

A document's status never changes its role. A historical PASS does not make a history record current authority.

## Authority is not the same as immutability

`authority` identifies which documents control current meaning/process. Mutability is controlled separately by `DOCUMENT_ROLE_REGISTRY.csv` and `DOCUMENT_CHANGE_POLICY.md`.

Therefore:

- current strategy authority is frozen during normal implementation/revalidation and changes only through explicit strategy change control;
- governance standards with `CONTROLLED_REVISION` may change only through controlled governance revision;
- governance registries/matrices classified `MUTABLE_TRACEABLE` are expected to move during governed execution when their own standards require it;
- records classified `IMMUTABLE_AFTER_ISSUE` are corrected by successor/correction records, not in-place edits.

No development or record document may override either strategy or governance authority silently.


## Governed records versus runtime storage

The documentation architecture does not absorb application storage into the authority tree.

For executed proof:

`authority → current work/records → referenced raw artifact → observed execution`

`records/evidence/**` owns the governed proof record. `storage/**` owns raw/generated execution artifacts only.

Normal start/resume navigation MUST be driven by `START_HERE.md` and canonical records. Do not use a broad scan of `storage/**` to infer current stage, current verdict, or current authority.

The mandatory artifact-read/admission rules are defined by `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md`.
