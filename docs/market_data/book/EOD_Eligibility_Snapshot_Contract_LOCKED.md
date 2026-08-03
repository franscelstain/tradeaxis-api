# EOD Eligibility Facts Snapshot Contract (LOCKED)

## Purpose

Define the publication-bound, point-in-time upstream data-usability facts for every temporal-universe instrument on trade date D.

Eligibility in this contract explains whether market-data is internally valid and sufficiently complete for downstream use. It does not encode ranking, alpha, picks, buy/sell signals, liquidity/tradability preference, event-avoidance preference, or portfolio policy.

## Row scope and identity (LOCKED)

One eligibility facts row exists per `(publication_id, trade_date, stable listing/instrument identity)` in the publication snapshot, including blocked and verified `NOT_EXPECTED` cases.

Current projections may expose the selected publication, but immutable history remains publication-bound. A current ticker code is not row identity.

## Required fact dimensions

Each row must persist separately:

### Expectation and delivery

- bar expectation state/reason
- delivery coverage state
- canonical bar availability/validity
- source/provenance state

### Quality

- quality state
- schema/stale/anomaly/quarantine state
- analytical price-basis and contamination state
- indicator validity and warm-up/nullability state

### Liquidity

- actual liquidity fields when sourced
- explicitly named/labelled proxy fields when actual fields are unavailable
- units, raw/adjusted basis, window, and quality state
- metric-level availability/validity state and null reason

### Status and event risk

- temporal suspension/trading-status state and source revision
- corporate-action/event-risk state
- verified factor/event revision or unresolved contamination state

### Decision and explanation

- `data_usable` boolean, or compatibility `eligible` boolean with identical upstream-only meaning
- primary/dominant reason code for compatibility
- complete ordered reason-code set or normalized reason relation
- rule/config/version and publication identity

No dimension may be reconstructed from a single overloaded `reason_code` when first-class facts are available.

## Eligibility meaning (LOCKED)

- `data_usable = true` / compatibility `eligible = true`: every declared data-integrity/readiness gate passes and the instrument/date may be evaluated by a downstream consumer.
- `data_usable = false` / compatibility `eligible = false`: one or more data-integrity/readiness gates block use; all material reasons remain explicit.

True does not mean selected, liquid enough for a strategy, ranked, attractive, event-safe under a strategy, or approved for a trade. Downstream policy may impose such preferences without changing this snapshot.

## Gate separation

- Coverage delivery failure blocks eligibility but must remain visible as coverage, not relabelled dormancy.
- Quality failure blocks eligibility independently of delivery ratio.
- Zero volume may pass delivery coverage and remains an explicit factual observation; it does not make otherwise valid market data unusable merely because a strategy would avoid the instrument.
- Verified `NOT_EXPECTED` is an expectation fact, not proof of attractiveness or current inactivity.
- Unknown status/event/factor fails safe when it prevents expectation, integrity, or price-product correctness. A known status/event flag that merely influences trading preference remains factual and does not fail data usability.

Liquidity or dormancy must never change the coverage denominator.

## Reason-code model (LOCKED)

All applicable material blocking reasons must be preserved deterministically. A primary reason may be selected by versioned precedence for compact consumer behavior, but it cannot erase secondary reasons.

Minimum reason families include:

- missing delivery/bar and fetch failure
- invalid/schema/stale/quarantined observation
- insufficient history or missing/invalid indicators
- unresolved price-scale/corporate-action factor
- temporal identity/universe/calendar/status dependency failure
- suspension/trading-status/event state that prevents an expected/valid product
- missing or invalid required metric input; optional liquidity facts remain nullable with reasons
- config/provenance/publication integrity failure

Only registry-defined codes may be emitted.

## Publication/readability relationship

A publication may be readable with some ineligible rows when run-level coverage and all global gates pass. Row eligibility does not determine the run status by itself, and a readable publication does not make every row eligible.

Consumers read eligibility facts from the same resolved publication as bars/indicators. They must not recompute upstream eligibility ad hoc.

## Immutability and replay

Eligibility rows and reason sets are frozen with the publication. Changed facts, rules, config, factor, or indicators produce new eligibility snapshots and publication lineage. Replay uses temporal/as-known inputs and reproduces the complete fact/reason set.

## Acceptance criterion (LOCKED)

For every instrument/date, the consumer can separately inspect expectation, delivery, quality, liquidity, status, event risk, indicator state, data usability, and every reason without inferring strategy policy or reading internal tables. A watchlist-specific tradability or selection decision can be changed without rewriting this snapshot.

## Cross-contract alignment

- `Eligibility_Partial_Data_Behavior_LOCKED.md`
- `EOD_COVERAGE_GATE_CONTRACT_LOCKED.md`
- `Trading_Status_Source_Contract_LOCKED.md`
- `Corporate_Action_Impact_Flags_Contract.md`
- `Domain_Boundary_Invariants_LOCKED.md`
