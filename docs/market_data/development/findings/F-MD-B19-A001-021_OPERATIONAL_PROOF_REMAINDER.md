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

## E034 progress amendment

E034 proposes one additional scheduling/locking basis, `MD-S078-R0002`, from a real scheduled import-to-promotion-to-status MariaDB chain with idempotent invocation, due-run provider outage, retained prior current publication, lock refusal and missing-calendar refusal. Eight scheduling predicates remain unbased: `MD-S078-R0003`, `R0004`, `R0006`, `R0007`, `R0009`, `R0015`, `R0017` and `R0019`. These include full logical-key/checkpoint semantics, complete fencing and audited recovery entrypoints, deployment activation/rehearsal, provider-production behavior, schedule-specific force prohibition and exact capability boundaries. The accepted total remains 595/750; E034 proposes 620/750 pending independent review. F018 severity and closure rules remain unresolved and are not inferred.

## E047 governed freshness continuation

External E046 review accepted three conditional freshness rows and the repaired scheduler row, leaving 642/750 independently accepted; `MD-S072-R0011/R0015` remain reviewer-held proposals. E047 repairs their production semantics: lag ends at verified readability rather than assessment, and a readable second session does not become BREACH merely because assessment occurs after its cutoff. A new read-only path resolves completed expected sessions from actual governed MariaDB revisions, exposes revision IDs/source refs, refuses inferred or caller-substituted calendar facts, and retains pre-request failed sessions for the five-session availability threshold. These repairs do not establish approved activation or live SLO attainment. The new bounded candidate rows are `MD-S072-R0018/R0021`; the remaining milestone measurement, alerts, accepted-breach workflow and production activation clauses stay unproved with row-specific reasons in `E047_execution_matrix.json`. `MD-S078-R0003/R0004` still require their full logical-key/checkpoint and every-state-changing-write matrices. Operations proof remains FAIL, formal SATISFIED zero.

## E048 calendar completeness repair

E047 independent review accepted R0011/R0015/R0018, bringing the independent baseline to 645/750; R0021 remains an existing held repository proposal. E048 enumerates every calendar date in the assessment horizon and requires verified revision/reconciliation evidence for both sessions and closed dates. Missing a whole weekday or closed-date row fails explicitly, as does a projected holiday; no weekend or holiday is inferred. Migrated MariaDB exercises nine dates, seven expected sessions, exact revision/source references, two-session lag and the five-session failure threshold. Missing-day-skip and projected-holiday counterfactuals fail exact assertions and restore bytes. This repairs R0021 without recount and does not certify the live exchange calendar or activation. R0003/R0004 and milestone/alert production remain unproved.
