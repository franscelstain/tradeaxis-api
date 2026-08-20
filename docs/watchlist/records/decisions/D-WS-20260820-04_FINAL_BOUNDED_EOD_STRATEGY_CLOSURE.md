# D-WS-20260820-04 — Final Bounded EOD Strategy Closure

> **Document role:** DECISION
> **Status:** ISSUED
> **Date:** 2026-08-20
> **Finding:** `F-WS-20260820-04`

## Decision

Complete the current canonical EOD Weekly Swing strategy with four bounded hard invariants: unresolved same-daily-bar stop/target ambiguity resolves conservatively as `STOP_FIRST`; issued PLAN/recommendation truth is immutable and correction/retry uses explicit lineage; zero qualified picks is the valid state `NO_ACTIONABLE_TOP_PICKS`; and identical frozen business inputs must reproduce identical recommendation/evaluation business payloads.

## Determinism Boundary

`recommendation_generated_at` and other explicit lifecycle-time inputs may differ across attempts. Such differences may change only the governed temporal/action-window envelope when applicable; they MUST NOT change the underlying EOD recommendation truth generated from the same frozen market/strategy identity.

## Product Boundary

No realtime, intraday, orderbook, broker execution, portfolio dependency, weekday rule, or local Market Data reconstruction is introduced. The revision closes EOD ambiguity rather than broadening the product.

## Proof Rule

All 43 new mandatory clauses start `NOT_ASSESSED` under the active verification epoch. Historical code/results are revalidation inputs only and cannot satisfy the new rules automatically.

## Supersession

No prior current rule is contradicted. Existing conservative same-bar wording, empty Top-Pick semantics, and immutability/reproducibility direction are strengthened into deterministic executable invariants.
