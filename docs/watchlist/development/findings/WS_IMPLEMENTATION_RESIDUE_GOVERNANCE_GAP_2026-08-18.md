# F-WS-20260818-03 — Implementation Residue Governance Gap

- **Document Type:** FINDING
- **Status:** ACCEPTED
- **Scope:** watchlist / weekly_swing implementation conformance
- **Record ID:** `F-WS-20260818-03`
- **Created:** 2026-08-18
- **Related Decision:** [`../../records/decisions/WS_IMPLEMENTATION_RESIDUE_STANDARD_ADOPTION_2026-08-18.md`](../../records/decisions/WS_IMPLEMENTATION_RESIDUE_STANDARD_ADOPTION_2026-08-18.md)
- **Related Evidence:** [`../../records/evidence/E-WS-20260818-02_RESIDUE_STANDARD_VALIDATION.json`](../../records/evidence/E-WS-20260818-02_RESIDUE_STANDARD_VALIDATION.json)

## Original Observation

Current governance already requires strategy conformance, negative testing, no-silent-update recording, and evidence-backed stage closure. However, it does not yet define **implementation residue** as a first-class recurring gate.

Without a dedicated rule, a stage can appear conformant because a new path works while an old reachable path still carries superseded behavior through code, config, parameter defaults, persistence mapping, API/DTO, fixtures, tests, commands, evaluator logic, or compatibility aliases.

The opposite risk also exists: implementers may overreact and delete every legacy identifier even when it is valid historical or controlled compatibility material.

## Impact

The system can become a mixed implementation: canonical new behavior on one path and legacy behavior on another. Tests may pass while production or proof still takes a stale branch. Reruns can also repeatedly rediscover known residue instead of continuing the prior remediation lineage.

## Required Resolution

Adopt one recurring residue/conformance standard that:

1. distinguishes harmful, controlled compatibility, historical-only, and confirmed-dead residue;
2. requires reachability/behavior evidence rather than text search alone;
3. blocks implementation-stage `DONE` while harmful residue remains;
4. binds residue history into stage re-entry/rerun;
5. requires proof/evaluator paths to represent exact current strategy identity;
6. preserves valid history/compatibility rather than deleting it blindly.
