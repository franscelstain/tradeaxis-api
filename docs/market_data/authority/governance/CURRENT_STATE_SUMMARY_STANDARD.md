# Current State Summary Standard

`development/implementation/CURRENT_STATE.md` is generated navigation only. It MUST NOT become an independent source of truth and MUST NOT infer implementation or production readiness beyond canonical registries/evidence.

## Required summary content

The generated current state MUST summarize, from canonical sources:

1. current verification epoch;
2. stage states/verdicts;
3. strategy coverage counts/state, including applicability state;
4. open/resolved blocking dependencies relevant to current execution;
5. current work-record counts by material record type or canonical registry total;
6. the single next executable resume point.

When any active/current stage contains conditional or transitional applicability, the summary MUST also report:

- current denominator;
- satisfied and not-assessed counts inside that denominator;
- `CONDITIONAL_NOT_APPLICABLE` count;
- `CONDITIONAL_PENDING` / `APPLICABILITY_PENDING` count;
- transitional `MANDATORY_OR_CONDITIONAL` count;
- whether the displayed coverage percentage is FINAL or PROVISIONAL.

A provisional denominator MUST NOT be displayed as if it were closure-ready coverage.

## Deterministic resume summary

When a logical stage is blocked and a dependency/remediation stage is actively executable, `CURRENT_STATE.md` MUST distinguish at least:

- logical/current blocked stage;
- active remediation/dependency stage;
- latest valid attempt for the active executable work;
- exact next executable resume point;
- return-to stage after remediation, when applicable.

Multiple stages may be `IN_PROGRESS`, but generated navigation MUST NOT present multiple ambiguous "continue here" instructions. Exactly one next executable resume point must be selected from canonical orchestration state.

If canonical records do not determine a unique resume point, the generator MUST report an orchestration inconsistency instead of guessing.
