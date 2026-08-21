# Stage Execution and Rework Standard

## 1. Stage and attempt identity

Current stages are `MD-B00..MD-B22`. Attempt format: `MD-Bxx-Ayyy`.

`DONE` requires objective/exit criteria, required strategy coverage, current baseline, current evidence, conformant residue state, required change-impact declaration when applicable, and all required integrity/governance gates.

A failed implementation-stage acceptance test cannot be marked `DONE`.

Waiting dependency is active, not terminal.

## 2. Re-entry

Re-entry MUST read:

- latest valid attempt;
- open findings;
- open dependencies;
- prior residue/rework state;
- do-not-repeat constraints;
- current traceability state;
- exact resume point.

If the latest attempt is still valid and can continue, continue that attempt. If remediation/rebaseline/re-entry is required, create the next valid attempt according to the applicable standards.

Historical work may be reused only after current revalidation; historical `PASS/DONE/SUCCESS` is never inherited automatically.

## 3. Dependency-driven remediation

A blocked logical stage may remain `IN_PROGRESS` while another stage is opened/re-entered to remediate a declared dependency.

When this happens canonical orchestration MUST record:

- blocked logical stage;
- blocking dependency ID;
- active remediation stage;
- active remediation attempt;
- exact next executable resume point;
- return-to stage after the dependency is resolved.

Dependency-driven remediation is not an uncontrolled stage skip. The dependency relationship MUST be explicit and registered according to `DEPENDENCY_REGISTRY_STANDARD.md` and `WORK_CORRELATION_AND_RECORD_REGISTRY_STANDARD.md`.

## 4. Exact resume point invariant

At any moment there MUST be no more than one canonical **next executable resume point**.

More than one `IN_PROGRESS` stage is allowed only when their orchestration roles are explicit (for example, blocked logical stage versus active remediation stage).

If two or more canonical records independently instruct the agent/operator to continue different executable stages without an explicit precedence/dependency relationship, orchestration is inconsistent and closure/progression MUST stop until the state is reconciled.

## 5. Closure and return

When dependency remediation closes successfully, update the dependency and relationship state first, then return execution to the recorded return-to stage/attempt flow. Do not silently treat the remediation stage's proof as proof of the blocked stage; cross-stage proof reuse requires explicit correlation/relationship and traceability support.


## 6. Runtime artifact read discipline

Re-entry/resume is driven by canonical docs and current records first. Inspect `storage/**` only when the selected current proof obligation or referenced current evidence requires raw execution verification.

Do not recursively scan storage as a substitute for determining current state.

For an open attempt that needs executed proof, the raw-artifact admission/linkage requirements of `RUNTIME_ARTIFACT_AND_GOVERNED_EVIDENCE_STANDARD.md` apply before that proof can support closure.

If an environment-blocked proof becomes executable again, continue the valid attempt when governance permits, execute the deferred command/test, and bind the resulting raw artifact to new/current governed evidence. Environment restoration alone does not create PASS.
