# D-WS-20260820-03 — Real-World Weekly Swing Strategy Strengthening

> **Document role:** DECISION
> **Status:** ISSUED
> **Date:** 2026-08-20
> **Finding:** `F-WS-20260820-03`

## Decision

Strengthen current canonical Weekly Swing strategy with deterministic EOD-only rules for: repeated recommendation continuity and non-overlapping follower replay; authoritative corporate-action economic return; supported execution-mode eligibility; condition-dependent EOD slippage; FOLLOW_TOP1/FOLLOW_TOP3/FOLLOW_TOP5 proof; edge-concentration stress; genuine Market Data correction sensitivity; and shortened/half-day session comparability.

## Product Boundary

The change does **not** add realtime, orderbook, broker execution, portfolio optimization, weekday trading rules, or local Market Data reconstruction. Core Top Picks remain position-independent EOD recommendations for manual decision support.

## Proof Rule

All new mandatory clauses start `NOT_ASSESSED` in the active verification epoch. Historical implementation/results may be supporting context but cannot satisfy the revised strategy automatically.

## Supersession

This decision strengthens the existing strategy; it does not invalidate historical facts. Pre-change strategy bytes are preserved in history snapshots and current strategy authority is the revised 14-owner set.
