# Finding — the B10 successor binder and proof gate support one successor layer and no partial re-ownership

- ID: `F-MD-B10-A003-001`
- Stage / Attempt / Baseline / Epoch: `MD-B10` / `MD-B10-A003` / `MD-B10-A003-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-05T14:07:12+07:00
- Severity: `P2` — blocks the governed binding of the three `MD-B10-A003` predicates; no semantic effect
- Status: `RESOLVED` — fixed within `MD-B10-A003` (`E-MD-B10-A003-003`)
- Class: `TOOLING_DEFECT_CROSS_ATTEMPT_REOWNERSHIP`
- Related: `CI-MD-B10-A003-001`, `E-MD-B10-A003-001`, `F-MD-B10-A002-006`, `E-MD-B10-A002-018`
- Remediation owner: `MD-B10-A003`

## Observed

`MarketDataB10SuccessorBinding` was written for `MD-B10-A002` over `MD-B10-A001` (`F-MD-B10-A002-006`). Reading it before use shows two assumptions that a third layer breaks:

| # | Where | Assumption | Effect when `MD-B10-A003` re-owns `MD-S005-R0056`, `MD-S005-R0071` (owned by `A002`) and `MD-S045-R0058` (owned by `A001`) |
|---|---|---|---|
| 1 | `plan()`, unaffected-row check | an unaffected row must be bound to evidence matching `PREDECESSOR_PATTERN` (`E-MD-B10-A001-NNN`) | the 53 rows still bound to `E-MD-B10-A002-017` are refused as `UNAFFECTED_ROW_UNEXPECTED_STATE` |
| 2 | `MarketDataPublicationLifecycleProofGate`, successor-owner group | the evidence of a layer is validated against the rows the layer currently owns (`scopeMap`, last owner wins) | `E-MD-B10-A002-017` proves 56 and would be checked against 53, giving `SUCCESSOR_EVIDENCE_PROVEN_COUNT_MISMATCH`, `..._EXTRA_RULES` |

Both are correct for one layer and wrong for two. The B10 gates are the closure gates, so the defect would block honest closure of `MD-B10-A003` rather than weaken it.

## Closure

Resolved when (1) the binder accepts for an unaffected row the evidence pattern of any earlier registered layer; (2) the gate validates a layer's evidence against that layer's own scope as recorded in its scope evidence, while pattern ownership still follows the last owner; (3) a profile for `MD-B10-A003` is registered; (4) every existing control of the binder self-test keeps its result (the failure set of the self-test before and after is identical) and new negative cases show that a wrong layer, a partial promotion and a foreign row are still refused; (5) the three predicates are bound through the binder and the B10 gates pass in bound mode.

## 2026-10-05 resolution — E-MD-B10-A003-003

All five closure conditions are met. The binder registers the `MD-B10-A003` layer, accepts the evidence of any earlier layer for an unaffected row, and gates the planned post-state against the layers that exist at the attempt; the proof gate validates a layer's evidence against that layer's own recorded scope. The binder self-test with the documented pristine `MD-B10-A002` matrix passes 54 of 54 (it was 53 of 54 at `E-MD-B10-A002-018`); the `MD-B10-A003` binder cases pass 20 of 20; three mutation probes of the tooling go red at the intended control; the three predicates were bound through the binder.
