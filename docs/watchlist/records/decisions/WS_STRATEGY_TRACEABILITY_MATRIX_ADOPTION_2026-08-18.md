# D-WS-20260818-04 — Adopt Canonical Strategy-to-Implementation Traceability/Coverage Matrix

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist / weekly_swing
- **Record ID:** `D-WS-20260818-04`
- **Created:** 2026-08-18
- **Related Finding:** `../../development/findings/WS_STRATEGY_TRACEABILITY_COVERAGE_GAP_2026-08-18.md`

## Decision

Adopt:

1. `../../authority/governance/STRATEGY_IMPLEMENTATION_TRACEABILITY_STANDARD.md` as canonical coverage governance;
2. `../../authority/governance/STRATEGY_TO_IMPLEMENTATION_TRACEABILITY_MATRIX.csv` as current rule-level coverage index;
3. `../../authority/governance/STRATEGY_TO_IMPLEMENTATION_COVERAGE_BASELINE.json` as machine-readable baseline summary.

## Rules Adopted

- every active canonical strategy requirement must have a stable traceability row;
- stage `DONE` does not by itself prove strategy completeness;
- mandatory rule satisfaction requires implementation + test + immutable evidence + conformant residue verdict;
- strategy semantic change must supersede/revalidate affected rows rather than silently reusing coverage;
- optional CONFIRM may remain `OPTIONAL_NOT_REQUESTED` without blocking core completeness;
- final 100% mandatory strategy coverage is valid only when every active mandatory/conditional row is `SATISFIED` and harmful residue open is zero;
- proof/business verdict remains separate from implementation coverage (for example, a valid OOS evaluator can be fully implemented while OOS verdict is `FAIL`).

## Initial Baseline

The matrix is initialized without inheriting unverified legacy claims:

- mandatory/conditional rows start `NOT_ASSESSED`;
- optional CONFIRM rows start `OPTIONAL_NOT_REQUESTED`;
- implementation/test/evidence refs start `TBD`;
- residue state starts `NOT_ASSESSED`.

## Effect

Current implementation alignment must now close both:

1. stage objective/exit criteria; and
2. rule-level strategy coverage for all rows owned by the stage.
