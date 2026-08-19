# D-WS-20260818-02 — Adopt Stage Execution, Re-entry, and Evidence-backed Closure Standard

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist / weekly_swing implementation orchestration
- **Record ID:** `D-WS-20260818-02`
- **Created:** 2026-08-18
- **Related Finding:** [`../../development/findings/WS_STAGE_REENTRY_AND_CLOSURE_GAP_2026-08-18.md`](../../development/findings/WS_STAGE_REENTRY_AND_CLOSURE_GAP_2026-08-18.md)
- **Related Evidence:** [`../evidence/E-WS-20260818-01_STAGE_GOVERNANCE_VALIDATION.json`](../evidence/E-WS-20260818-01_STAGE_GOVERNANCE_VALIDATION.json)

## Decision

Adopt [`../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md`](../../authority/governance/STAGE_EXECUTION_AND_REWORK_STANDARD.md) as canonical governance for all current/future `WS-Bxx` execution, rerun, remediation, dependency waiting, successor/decomposition, and terminal closure.

Adopt [`../../development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md`](../../development/implementation/WS_IMPLEMENTATION_STAGE_REGISTER.md) as the single current resume index.

## Locked Principles

1. Failure count, elapsed time, fatigue, or opinion are never closure criteria.
2. Every attempt closes with evidence, even when the stage remains active.
3. Every rerun reads the prior lineage before code changes.
4. Diagnostic convergence that is still improving keeps the stage open.
5. `WAITING_VERIFIED_DEPENDENCY` remains active and needs an objective resume trigger.
6. `DONE` means the declared stage objective/exit criteria were achieved.
7. A valid evaluation stage may be `DONE` with evaluation verdict `FAIL`; that means evaluation executed correctly and the downstream proof gate stops.
8. If a stage objective itself cannot be achieved, terminal unresolved closure requires objective evidence, exhausted/infeasible reasonable remedies, and explicit reviewed decision.
9. Successor/decomposition is allowed only when the new path is materially different and all residual objective is mapped.
10. Strategy cannot be weakened merely to make implementation appear complete.

## Strategy Impact

None. This decision changes documentation/implementation governance only. Canonical Weekly Swing behavior remains unchanged.

## Supersession

Historical campaign statuses remain immutable evidence. They do not define current `WS-Bxx` lifecycle semantics.
