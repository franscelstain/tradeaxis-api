# Document Change Policy

## 1. Core rule

Strategy/governance MUST NOT be weakened, rewritten, or reinterpreted merely to make implementation, tests, traceability, or a verification stage pass.

Authority role and mutability are separate concepts. A document may be authoritative while still being intentionally mutable under its registered change class.

The authoritative change class is the value registered in `DOCUMENT_ROLE_REGISTRY.csv`.

## 2. Strategy authority

Documents under `authority/strategy/` that own current Market Data semantics are treated as frozen during normal implementation/revalidation.

Strategy-byte changes require all of the following:

1. an explicit strategy finding/rationale;
2. supporting evidence;
3. a reviewed decision;
4. explicit user authorization for the strategy revision;
5. a `DOCUMENT_CHANGE_LOG.md` entry;
6. a new strategy freeze identity/manifest;
7. invalidation and governed revalidation of all affected current verification.

Normal implementation work MUST adapt implementation to strategy authority, not strategy authority to implementation.

## 3. Governance authority

Governance authority controls how work is verified, recorded, correlated, closed, and revalidated. It is not globally immutable.

Governance documents follow their registered change class:

- `CONTROLLED_REVISION`: may change only through deliberate change control with finding/rationale, evidence, reviewed decision, change-log entry, and affected-current-verification invalidation when the revision changes an acceptance, proof, correlation, closure, or orchestration invariant.
- `MUTABLE_TRACEABLE`: may be updated as part of normal governed execution when the document's own standard requires it, provided the update is traceable, does not silently alter strategy semantics, and does not create a parallel authority.
- `IMMUTABLE_AFTER_ISSUE`: MUST NOT be edited after issue; corrections require a new correlated record according to the applicable standard.

Updating a `MUTABLE_TRACEABLE` governance registry or matrix is an authority-area modification, but it is not by itself a governance-semantic revision.

## 4. No pass-by-authority-edit

If implementation, tooling, a generator, a gate, registry state, traceability assignment, baseline state, or evidence is non-conformant, the default remediation target is that implementation/execution layer.

Changing authority is permitted only when the authority itself is demonstrably ambiguous, incomplete, contradictory, or defective and the applicable change-control requirements are satisfied.

## 5. Verification impact

Any authority revision that changes the meaning of a current proof obligation, relationship requirement, traceability assignment rule, baseline invariant, stage transition, closure criterion, or gate requirement MUST identify affected current verification and invalidate/rebaseline/revalidate it as required.

A prior `PASS`, `DONE`, `SATISFIED`, or equivalent MUST NOT survive solely because it predates the revised governance rule.
