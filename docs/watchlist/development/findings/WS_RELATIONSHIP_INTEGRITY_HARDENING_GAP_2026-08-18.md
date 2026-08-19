# WS Relationship Integrity Hardening Gap — 2026-08-18

- **Document Type:** FINDING
- **Status:** RESOLVED_BY_DECISION
- **Scope:** current/future `WS-Bxx` Work/Attempt relationship integrity

## Observation

The existing relationship gate fully enforced unique Record ID, Stage existence, and acyclic supersession, but several required integrity claims were only partial: Attempt ID could span inconsistent baselines, non-baseline records did not universally prove Baseline existence, Related Finding/Decision checked existence but not target type, closure evidence was not baseline-validated, and cross-attempt links could be expressed without an explicit justification record.

## Risk

A stage could appear traceable while records from different attempts/baselines were silently mixed, or a Finding/Decision pointer could target the wrong record type. Closure evidence could therefore be provenance-ambiguous.

## Required Remediation

Machine-enforce all nine relationship invariants, introduce explicit Work Relationship Registry for cross-attempt/cross-baseline links, require reviewed authorization for cross-baseline closure evidence, and add mutation tests that prove each invariant fails when deliberately violated.
