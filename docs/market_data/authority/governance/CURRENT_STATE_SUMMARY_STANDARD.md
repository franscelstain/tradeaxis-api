# Current State Summary Standard

`development/implementation/CURRENT_STATE.md` is generated navigation only. It MUST NOT become an independent source of truth and MUST NOT infer implementation or production readiness beyond canonical registries/evidence.

## Required summary content

The generated current state MUST summarize, from canonical sources:

1. current verification epoch;
2. stage states/verdicts;
3. strategy coverage counts/state;
4. open/resolved blocking dependencies relevant to current execution;
5. current work-record counts by material record type or canonical registry total;
6. the single next executable resume point.

## Deterministic resume summary

When a logical stage is blocked and a dependency/remediation stage is actively executable, `CURRENT_STATE.md` MUST distinguish at least:

- logical/current blocked stage;
- active remediation/dependency stage;
- latest valid attempt for the active executable work;
- exact next executable resume point;
- return-to stage after remediation, when applicable.

Multiple stages may be `IN_PROGRESS`, but generated navigation MUST NOT present multiple ambiguous "continue here" instructions. Exactly one next executable resume point must be selected from canonical orchestration state.

If canonical records do not determine a unique resume point, the generator MUST report an orchestration inconsistency instead of guessing.
