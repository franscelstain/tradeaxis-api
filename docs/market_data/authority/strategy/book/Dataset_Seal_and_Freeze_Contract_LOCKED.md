# Dataset Seal and Freeze Contract (LOCKED)

## Purpose
Freeze the published dataset for effective date D so downstream consumers read a stable, non-drifting upstream input.

## Seal preconditions (LOCKED)
Seal may be written only when all conditions hold:
- run is success-eligible for requested date T and candidate effective date D
- eligibility exists for D
- hashes exist for bars, indicators, and eligibility
- seal is being written by the same run context that produced the candidate artifacts
- immutable source observation references and provenance exist
- identity/universe, calendar/status, price-basis/factor, indicator-set, and output-affecting config versions/hashes are bound
- every publication-bound row snapshot is complete and hash-covered

Seal is a precondition to final `SUCCESS`; it is not something written after an already-finalized `SUCCESS`.

## Freeze rule (LOCKED)
After seal:
- dataset for D must not change in-place, silently or explicitly
- any content correction requires controlled correction flow with new observation/revision selection, new `run_id`, new publication version, new hashes, and reseal
- prior sealed state must remain auditable
- operational notes/events may append, but protected content, lineage, source/config/factor bindings, and seal metadata may not be rewritten

## Consumer dependency (LOCKED)
Any downstream consumer may read only SEALED datasets.
Unsealed datasets are `not ready` even if the run status is otherwise marked as technically complete.

## Anti-drift rule (LOCKED)
For one sealed effective date D, the content hashes, eligibility snapshot, and row set of bars/indicators used for publication must remain frozen.
Operational metadata may grow later through extra run events or operator notes, but the sealed content itself must not drift.

Seal is not evidence that semantic meaning is correct by itself. A sealed publication produced under obsolete or invalid contracts remains subject to audit findings and may only be corrected through a new revision/publication.

## Capability boundary (LOCKED)

**What a seal proves.** That the content bound to a publication was fixed at seal time, that its hashes were computed over the declared payload, and that later mutation through guarded paths is rejected.

**What it cannot prove.**

- **That the sealed content is correct.** A seal preserves faithfully whatever was presented to it. Wrong values seal exactly as well as right ones, and the resulting hash is equally valid.
- **That everything relevant was inside the seal.** Only declared payload participates in the hash. A field, annotation, or reason set excluded from the hash definition can differ between two publications that hash identically.
- **That the inputs were complete.** A seal over a dataset missing an entire listing, or a session the calendar never recorded, is a valid seal over an incomplete truth.

Consequently `SEALED` may be cited as evidence of **fixity and reproducibility**, never as evidence of **correctness or completeness**.

## Protected content scope (LOCKED)

The frozen identity includes bars, analytical price products, indicators, eligibility facts, observation references, temporal universe/calendar/status versions, corporate-action/factor revisions, config snapshot/hash, formula/version identity, content hashes, and lineage links.

Database administration, repair utilities, or migrations must not bypass this scope. Mutation guard failure must leave the current pointer unchanged and emit audit evidence.
