# D-WS-20260818-03 — Adopt Recurring Implementation Residue and Conformance Standard

- **Document Type:** DECISION
- **Status:** ISSUED
- **Scope:** watchlist / weekly_swing implementation conformance
- **Record ID:** `D-WS-20260818-03`
- **Created:** 2026-08-18
- **Related Finding:** [`../../development/findings/WS_IMPLEMENTATION_RESIDUE_GOVERNANCE_GAP_2026-08-18.md`](../../development/findings/WS_IMPLEMENTATION_RESIDUE_GOVERNANCE_GAP_2026-08-18.md)
- **Related Evidence:** [`../evidence/E-WS-20260818-02_RESIDUE_STANDARD_VALIDATION.json`](../evidence/E-WS-20260818-02_RESIDUE_STANDARD_VALIDATION.json)

## Decision

Adopt [`../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md`](../../authority/governance/IMPLEMENTATION_RESIDUE_AND_CONFORMANCE_STANDARD.md) as canonical recurring governance for implementation residue detection, classification, evidence, remediation, rerun, and closure gating.

## Locked Principles

1. Residue check is recurring, not one-time cleanup.
2. Old identifiers are not automatically defects; reachability + semantic impact determine residue class.
3. Reachable behavior that conflicts with current authority is `HARMFUL_RESIDUE` and blocks implementation-stage `DONE`.
4. Compatibility residue may remain only with explicit semantic mapping, isolation, tests, and evidence.
5. Historical residue must remain historical and must not become current execution/authority.
6. Dead residue cannot be asserted without evidence.
7. Search/grep is discovery evidence, not sufficient conformance proof by itself.
8. Proof/evaluation verdict is invalid as current proof if harmful residue can change the evaluated identity/path.
9. Reruns must read prior residue findings/evidence and must not rediscover known residue from zero.
10. Strategy must not be weakened merely to make legacy implementation look conformant.

## Strategy Impact

None. This is implementation/documentation governance. Canonical Weekly Swing business strategy does not change.

## Stage Impact

Current `WS-B00..WS-B12` register gains an explicit residue state/evidence pointer. Initial value is `NOT_ASSESSED`, not a claim of absence.
