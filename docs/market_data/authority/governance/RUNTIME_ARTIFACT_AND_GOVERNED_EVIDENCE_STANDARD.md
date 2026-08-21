# Runtime Artifact and Governed Evidence Standard

## 1. Purpose

This standard governs the boundary between:

- canonical documentation/verification records under `docs/market_data/records/**`; and
- raw execution artifacts produced outside the documentation tree, normally under configured Market Data `storage/**` paths.

It controls how runtime/test/replay/backfill/evidence-export output may support current Market Data verification. It does not redefine Market Data business semantics.

## 2. Source-of-truth roles

`docs/market_data/records/**` is the governed record layer. A current evidence record states what was executed, what result was observed, what requirement it proves, and which Stage/Attempt/Baseline/Epoch owns that proof.

`storage/**` is the raw execution-artifact layer. It may contain logs, command output, replay output, exported evidence packs, manifests, fixtures, coverage output, dumps, or other machine-produced artifacts.

The roles are not interchangeable:

- a raw artifact in `storage/**` is **not** an independent source of current verification truth;
- a prose/evidence record in `docs/market_data/records/**` MUST NOT fabricate or substitute raw execution that did not occur;
- current runtime proof is admitted through a governed evidence record correlated to the active verification identity, with raw-artifact linkage when this standard requires it.

Large/generated runtime outputs SHOULD remain outside `docs/`; the documentation tree stores the governed evidence record, not an unbounded copy of runtime logs.

## 3. Mandatory start/resume read order

When starting or resuming implementation/revalidation, use this order:

1. `docs/market_data/START_HERE.md`;
2. current strategy/governance authority referenced from it;
3. current canonical orchestration and verification records needed to determine Stage/Attempt/Baseline/Epoch, findings, dependencies, traceability, relationships, and the single exact resume point;
4. current evidence records relevant to the selected proof obligation;
5. only then, raw artifacts in `storage/**` that are referenced by those current records or are required to obtain/verify the selected runtime proof;
6. actual implementation code/tests/commands as required to continue or reproduce the proof.

Do **not** start a normal resume by recursively scanning all of `storage/**`.

Canonical docs determine which raw artifacts are relevant. `storage/**` does not determine the current stage or current verdict by itself.

## 4. When raw storage artifacts MUST be inspected

The agent/operator MUST inspect the relevant raw artifact, or rerun the execution to produce a new valid artifact, when any of the following applies:

- a current evidence/closure record explicitly references that artifact;
- the current requirement is being proven by runtime, database, command, test, replay, backfill, correction, evidence-export, or other executed behavior whose material output is stored externally;
- stage/attempt closure depends on an execution result whose raw output is required by the applicable proof/admission contract;
- artifact existence, hash, manifest, completeness, or integrity is part of the claimed proof;
- a current evidence record and observed runtime/storage state disagree;
- an environment-blocked execution becomes available again and the deferred proof must now actually run;
- prior current proof is being carried forward and its validity depends on an external raw artifact that must remain verifiable.

Inspection means verifying the artifact that actually supports the claim, not merely verifying that some similarly named directory exists.

## 5. When storage inspection is NOT required

A recursive or broad `storage/**` inspection is not required merely because Market Data work is being resumed.

Storage normally does not need to be inspected for work that is purely:

- authority reading or governance interpretation;
- traceability predicate classification/ownership analysis that does not claim runtime proof;
- Stage Register/CURRENT_STATE synchronization;
- relationship/dependency/finding registry reconciliation that does not depend on runtime output;
- documentation architecture/link/role integrity;
- other document-only work whose acceptance criteria contain no executed proof obligation.

If the work later crosses into an executed proof obligation, section 4 becomes controlling.

## 6. Raw-artifact admission and linkage

When an external raw artifact is required for current proof, the governed evidence record MUST identify enough information to bind the claim to the actual execution.

At minimum, where applicable, record:

- Stage ID;
- Attempt/Work ID;
- Baseline ID;
- Verification Epoch;
- execution/test/command identity;
- execution timestamp or stable execution identifier;
- relevant environment/runtime/database identity;
- result/exit state;
- raw artifact path or artifact-manifest path;
- cryptographic hash for the material artifact or manifest when the artifact is retained as proof;
- the requirement/evidence relationship that the artifact supports.

For multi-file output, a manifest may be the primary raw-artifact reference when it deterministically inventories the material files and their hashes.

A path without execution identity is insufficient. A hash without a path/manifest identity is insufficient. A raw artifact without canonical evidence correlation is supporting material only.

## 7. Current versus historical raw artifacts

Raw artifacts produced by historical W00..W22 work, old audits, old attempts, or prior verification epochs remain historical/supporting material unless a current governed evidence record explicitly revalidates and admits them.

The existence of an old `storage/**` artifact MUST NOT automatically create:

- `PASS`;
- `DONE`;
- `SATISFIED`;
- `CLOSED`;
- current evidence eligibility.

Current proof follows the active Stage/Attempt/Baseline/Epoch and current authority.

## 8. Missing, moved, or mismatched artifact

If a current proof requires a raw artifact and that artifact:

- is missing;
- cannot be read;
- has a mismatched hash;
- is incomplete relative to its manifest;
- cannot be tied to the claimed execution;

then the affected proof MUST NOT be treated as valid current execution proof.

Do not rewrite immutable evidence to hide the mismatch.

The correct action is to:

1. classify the proof gap accurately;
2. determine whether the execution can be reproduced;
3. rerun/re-export/reverify under the current valid attempt or governed successor as applicable;
4. issue new/corrective evidence according to `DOCUMENT_RECORDING_STANDARD.md`;
5. update affected traceability/closure state only after valid proof exists.

An unavailable raw artifact is a proof-artifact problem by default, not automatically an implementation defect.

## 9. Retention and repository boundary

Raw runtime output SHOULD remain in configured application storage/evidence locations and SHOULD NOT be copied wholesale into the documentation repository merely to make it reviewable.

A governed record may contain concise observed results needed to understand the proof, but large logs, dumps, generated fixtures, exported evidence packs, and replay directories belong in the runtime artifact layer.

If retention policy permits an artifact to expire, closure/proof rules that require later reproducibility MUST use an appropriate retained manifest/hash/export pack before expiry. Retention MUST NOT silently destroy proof that current governance requires to remain verifiable.

## 10. Closure invariant

When external raw artifacts are required by the applicable proof obligation, stage/attempt closure MUST verify:

- canonical evidence correlation;
- artifact existence/accessibility at verification time;
- required hash/manifest integrity;
- consistency between the governed evidence record and the raw execution result.

A closure manifest SHOULD identify the governed evidence IDs. It does not need to duplicate raw logs, but the evidence chain MUST make required raw artifacts deterministically reachable.

A gate that validates only document syntax while ignoring required raw-artifact linkage MUST NOT be used as proof of runtime execution sufficiency.

## 11. Effective-change and carry-forward rule

This standard is effective through `DOC-CHG-20260821-003`.

It does not rewrite immutable evidence or closure records issued before the change, and absence of `storage/**` from a docs-only snapshot does not by itself invalidate those historical records.

However, after this standard becomes current authority:

- new executed proof MUST follow this standard;
- an open attempt using runtime proof MUST follow this standard before closure;
- prior execution proof carried into a new attempt/closure MUST satisfy the current linkage/integrity requirements when external raw artifacts are required;
- current status MUST NOT claim that a raw artifact was verified if the available environment did not actually provide it.

This transition rule prevents artificial retroactive rewriting while ensuring all continuing/new verification uses the stronger proof boundary.
