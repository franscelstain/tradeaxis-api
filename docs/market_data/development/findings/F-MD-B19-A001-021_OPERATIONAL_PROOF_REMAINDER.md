# Finding — F-MD-B19-A001-021

- ID: `F-MD-B19-A001-021`
- Stage / Attempt / Baseline: `MD-B19` / `MD-B19-A001` / `MD-B19-A001-BL001`
- Raised at: 2026-10-20T08:30:00+07:00
- Severity: `P2`
- Status: `OPEN`
- Evidence: `E-MD-B19-A001-029`
- Change impact: `CI-MD-B19-A001-001`

## Exact remaining predicates

E029 proposes 34 new proof bases across commands_and_runbook (2), observability_minimum (18), and scheduling_and_locking/calendar (14). The accepted E028 baseline remains 529/750; E029's proposed map is 563/750 pending independent review. Formal SATISFIED remains 0/750.

The remaining 24 selected predicates are individually identified in `E029_per_predicate_map.json`: 14 command/runbook predicates, `MD-S070-R0005`, and nine scheduling/locking predicates. The command predicates lack complete real command-specific workflows and refusal/mutation evidence. R0005 is held because D-MD-B19-A001-003 requires persisted `warning_count` NULL preservation and no warning population is defined; invalid and hard-reject counts are separately observable. Scheduling holds are specific: no freshness/SLO result (R0002), incomplete logical-key/checkpoint retry semantics (R0003), incomplete all-entrypoint fencing proof (R0004), no deployment/activation rehearsal evidence (R0006), no provider-production incident behavior and F018 severity/closure authority (R0007), no schedule-specific force/automatic-repair proof (R0009), and no proof for the stated capability boundaries (R0015/R0017/R0019).

## Governance boundaries

F018 R0011/R0012 severity escalation and incident closure remain HELD for the exact Project Owner decision. R0324 remains HELD under D008/F016. MD-DEP-0025 stays ACTIVE: the frozen candidate-v5 manifest now has 15 differing listed source files and this batch added three app source files that the immutable v5 manifest does not list. No candidate-v6, denominator change, applicability change, B18 reopening, or formal SATISFIED promotion is authorized.

The unchanged F009/E013 alias-meaning failure, coverage static-guard failure, and historical candidate-v5 build-identity failures remain visible. No full-suite result is claimed.
