# WS Relationship Integrity Hardening Adoption — 2026-08-18

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** current/future `WS-Bxx` Work/Attempt relationship integrity
- **Related Finding:** `../../development/findings/WS_RELATIONSHIP_INTEGRITY_HARDENING_GAP_2026-08-18.md`

## Decision

Adopt a 9/9 machine-enforced relationship integrity contract. One Attempt ID must bind exactly one Stage and one Baseline, every current record baseline must resolve to a real baseline lock, Finding/Decision references are type-safe, supersession remains acyclic, closure-critical evidence is baseline-safe, and all cross-attempt/cross-stage links require explicit justified entries in `WORK_RELATIONSHIP_REGISTRY.csv`. Cross-baseline closure evidence additionally requires a reviewed Decision.

## Implementation Rule

A relationship-gate PASS that does not enforce all nine invariants is not valid closure evidence. The gate's mutation/self-test must remain runnable and PASS.
