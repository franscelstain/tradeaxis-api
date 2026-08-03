# Eligibility Partial and Degraded Data Behavior (LOCKED)

## Purpose

Define deterministic upstream data-usability facts when market-data is partial, missing, invalid, stale, quarantined, or otherwise degraded.

## Core rule (LOCKED)

Every temporal-universe instrument for D receives an explicit data-usability facts row in a completed candidate snapshot. Missing facts never cause the row to disappear or default to usable.

## Required degraded behavior

- Missing expected observation: delivery missing, canonical unavailable, `eligible=false`, registered delivery reason.
- Delivered invalid observation: delivery present, quality invalid, canonical unavailable, `eligible=false`.
- Stale/wrong-date observation: requested delivery missing/stale, `eligible=false`.
- Missing/invalid indicators: indicator state explicit, `eligible=false` when required.
- Insufficient warm-up: affected indicators `NULL`, warm-up state/reasons explicit; eligibility follows versioned required-field rules.
- Unverified corporate action/break/factor: contamination explicit, `eligible=false` for affected dependency window.
- Unknown/conflicting status or identity dependency: explicit unknown/blocking reason; no current-state fallback.
- Zero-volume/illiquid observation: delivery remains present and liquidity facts remain explicit; downstream tradability policy decides preference. It blocks only a required metric whose input is missing/invalid, not otherwise valid market data.

## Multiple-reason behavior

All material reasons are retained. A versioned primary-reason precedence may select one compatibility `reason_code`, but evidence and normalized reason set must preserve every applicable cause.

No `eligible=false` row may have an empty reason set. No `eligible=true` row may carry an unresolved blocking reason.

## Run-level distinction

Row-level blocks do not alone determine terminal run status:

- some blocked rows may coexist with readable publication if delivery threshold and all global gates pass
- global schema/provenance/config/identity/calendar corruption may make the run held/failed/not evaluable
- coverage pass cannot override quality/eligibility blockers
- a held/failed run cannot expose its candidate eligibility as readable

## Fail-safe absence rule

Absence of source/status/event/liquidity data means unknown or missing, never normal/safe/zero by default. Consumers must not guess or rebuild missing data-usability facts. Missing optional liquidity fields remain reason-coded nulls and are not silently converted into a market-data-wide strategy exclusion.

## Determinism and immutability

Identical publication-bound inputs, temporal snapshots, factor/config/formula versions, and reason precedence produce identical rows and ordered reason sets. Published rows are immutable; changes create a new publication.

## Acceptance criterion (LOCKED)

Partial data never disappears into data usability, coverage exclusions, or consumer guesses; every blocked state remains explicit, independently classified, and reason-coded. Watchlist preference is not part of this acceptance criterion.
