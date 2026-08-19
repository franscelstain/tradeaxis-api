# D-WS-20260818-06 — Adopt Baseline Lock, Executable Integrity Gate, and Attempt Record Standard

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist / weekly_swing implementation governance
- **Record ID:** D-WS-20260818-06
- **Created:** 2026-08-18
- **Related Finding:** F-WS-20260818-06

## Decision

Adopt three mandatory implementation controls:

1. **Work Baseline Lock** before material `WS-Bxx` implementation/proof changes;
2. **Executable Documentation Integrity Gate** at pre-attempt, pre-close, pre-stage-DONE, and package gates;
3. **Canonical Stage Attempt Record Template** for every attempt, issued as immutable evidence when closed.

## Enforcement

- Stage/Attempt/Baseline identity must be traceable.
- `SATISFIED` strategy coverage produced after adoption must have attempt/baseline provenance.
- executable gate does not replace semantic tests, residue analysis, or strategy proof;
- baseline does not prohibit code changes inside the attempt; it fixes the starting authority/source provenance;
- authority drift cannot be hidden by editing baseline in-place;
- final attempt evidence cannot be rewritten on rerun.

## Legacy Integrity Exception

Register `DOC-INT-EXC-20260818-001` only for the pre-existing duplicate `DOC-CHG-20260818-003` block in the append-only change log. The exception is narrow and cannot be generalized to current defects.

## Outcome

Implementation governance becomes self-enforcing enough to identify what authority/source revision was used, whether documentation structure remains valid, and what precisely happened in every attempt before a stage is allowed to close.
