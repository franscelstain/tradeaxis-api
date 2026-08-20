# F-WS-20260819-06 — EOD Core Boundary Ambiguity

## Role

`FINDING`

## Observation

High-trust strategy revision `D-WS-20260819-05` correctly strengthened real-world execution realism, but several expressions around `NEXT_OPEN`, delayed fill, daily-bar stop/target handling, and optional decision-time data could be misread as changing core Weekly Swing from an EOD decision-support product into an intraday/orderbook-dependent strategy.

## Risk

If left ambiguous, implementation could incorrectly:

- add realtime/orderbook as a prerequisite for PLAN or Top Picks;
- block core production availability when decision-time data is absent;
- treat historical `open(D+1)` as guaranteed investor fill;
- create false precision around queue/fill behavior;
- expand scope from EOD Weekly Swing into broker/intraday execution without an explicit strategy revision.

## Required Resolution

Current authority must explicitly distinguish recommendation truth, conservative historical modeled execution, and actual investor execution; lock realtime/intraday/orderbook outside the core dependency set; preserve optional CONFIRM as non-blocking; and require any future intraday/orderbook adoption to use a separate capability/strategy identity and proof.

## Resolution

Resolved by `../../records/decisions/D-WS-20260819-06_EOD_ONLY_CORE_EXECUTION_MODEL_CLARIFICATION.md`.

This finding resolves a **strategy-boundary ambiguity** only. All current implementation/proof rows remain `NOT_ASSESSED` until validated through the current `WS-Bxx` lifecycle.
