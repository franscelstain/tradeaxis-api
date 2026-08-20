# D-WS-20260819-06 — EOD-Only Core and Modeled Execution Clarification

## Role

`DECISION`

## Decision

Weekly Swing remains an **EOD-only core decision-support strategy**.

1. Realtime, intraday, orderbook, broker order-state, queue position, and broker fill feeds are not core prerequisites for PLAN, RECOMMENDATION/TOP_PICKS, ranking, historical qualification, or core production availability.
2. Historical `NEXT_OPEN` / `open(D+1)` is a causal reference execution model used with frozen friction/executability assumptions. It is not a guarantee or claim of exact investor fill.
3. If EOD data cannot establish exact fill/queue behavior, the evaluator uses conservative modeled uncertainty/non-executable rules. It does not fabricate historical orderbook precision.
4. Daily OHLC high/low may support conservative stop/target touch evaluation without creating an intraday-feed dependency.
5. CONFIRM remains optional and non-blocking. It may use a separately governed decision-time source when available, but orderbook is not implicitly required.
6. Any future realtime/intraday/orderbook enhancement requires an explicit capability/strategy identity and separately identified proof. It cannot silently redefine the current EOD strategy.
7. Actual investor/broker execution remains outside the core Watchlist authority.

## Authority Impact

This decision clarifies and strengthens the existing product boundary. It does **not** convert Weekly Swing to intraday trading and does **not** authorize automatic execution.

Eight current strategy owners are strengthened without creating a fifteenth strategy owner. The complete 14-owner pre-change strategy is preserved as `H0375..H0388`.

## Verification Impact

New/clarified traceability rules remain `NOT_ASSESSED` (or `OPTIONAL_NOT_REQUESTED` for CONFIRM) in verification epoch `WS-REBASELINE-20260819-001`. Existing code/evidence does not inherit PASS from this decision.

## Related Records

- Finding: `../../development/findings/F-WS-20260819-06_EOD_CORE_BOUNDARY_AMBIGUITY.md`
- Evidence: `../evidence/E-WS-20260819-06_EOD_CORE_BOUNDARY_VALIDATION.json`
- Change log: `../../authority/governance/DOCUMENT_CHANGE_LOG.md`
