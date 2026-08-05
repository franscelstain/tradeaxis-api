# Domain Boundary Invariants (LOCKED)

## Purpose

Define the non-negotiable ownership boundary of Market Data Platform (EOD) so upstream market facts never drift into alpha, ranking, entry/exit, portfolio, or execution policy.

Market-data guarantees the meaning, provenance, quality state, reproducibility, and safe publication of data products. Weekly Swing is the initial consumer profile, not the definition or acceptance authority of this domain.

This document is the owner contract for that boundary. Dependent contracts, schema, configuration, code, tests, and read models must conform to it and must not redefine the boundary implicitly.

## Core boundary rule (LOCKED)

Dependency and authority flow in one direction only:

`market observations -> governed market-data facts -> stable market-data read product`

Downstream policy may consume that product through a separate interface, but it must not flow back into source observations, canonicalization, adjustments, indicators, coverage, quality classification, publication history, or market-data readiness decisions.

Market-data has no entry, exit, ranking, alpha, position-sizing, portfolio, or execution strategy. Supporting a Weekly Swing consumer does not transfer any of those responsibilities upstream.

## Data-readiness admission rule (LOCKED)

Market-data documentation, implementation, and operational readiness are admitted only from market-data evidence:

- semantic correctness and stable units/bases;
- source provenance, licensing disclosure, and schema validation;
- temporal identity, universe, calendar, and status correctness;
- coverage, integrity, quality, correction, and corporate-action safety;
- deterministic indicators and versioned configuration;
- immutable publication, lineage, reproducibility, and replay;
- explicit readiness/freshness states and fail-safe operations.

Watchlist implementation, number of candidates, rank stability, signal quality, expectancy, drawdown, turnover, profit, or user preference are neither market-data acceptance tests nor reasons to alter historical market facts. Consumer compatibility may verify field shape, semantics, versioning, and atomic publication binding only.

## Market-data ownership (LOCKED)

Market Data Platform may own and publish:

- immutable source observations and provenance
- point-in-time issuer, instrument, listing, provider-symbol, calendar, board, and trading-status facts
- canonical IDX Regular-Market EOD observations
- explicit `RAW`, `STRUCTURAL_ADJUSTED`, and, when available, `TOTAL_RETURN` data products
- verified and versioned corporate-action factors and contamination state
- deterministic, versioned indicators and factual benchmark context required by the consumer contract
- separate coverage, quality, liquidity-measure, trading-status, and event-risk facts
- a row-level data-usability decision, exposed through a clearly named field or a compatibility `eligible` field, with explicit reasons based only on factual/readiness gates
- run, configuration, hash, seal, publication, correction, and supersession identity
- replay and reproducibility evidence
- optional non-streaming supplemental session snapshots

These are data, data-quality, and publication artifacts. Their availability to a Weekly Swing consumer does not turn them into strategy outputs.

## Decisions allowed inside market-data

The domain may decide:

- whether a source observation is accepted, rejected, retried, held, or quarantined
- whether an observation is expected and delivered for coverage purposes
- whether an observation or analytical range passes declared quality rules
- which price-product and factor version a versioned calculation uses
- the value, warm-up state, and nullability of deterministic indicators
- factual liquidity measures or explicitly labelled proxies
- trading-status and event-risk classification from governed evidence
- whether an instrument/date passes declared data-integrity/use-readiness gates and why
- whether a requested date has a coherent readable publication
- which sealed publication is current and which publication is superseded
- whether a correction or replay matches its governed expectation

These decisions answer whether data exists, what it means, whether it can be trusted, and whether it is safe to expose for downstream evaluation. They must not answer whether a security should be traded or preferred.

## Watchlist-policy ownership (LOCKED)

Only the downstream watchlist or another explicitly separate strategy domain may decide:

- screening or candidate selection
- ranking, priority ordering, score aggregation, or alpha
- signal classification, conviction, or strategy fit
- long/short or buy/sell intent
- entry price or entry timing
- exit timing, stop loss, take profit, or target price
- position sizing, risk budget, or capital allocation
- portfolio construction, rebalancing, or portfolio action
- expected return, P&L interpretation, or strategy performance
- broker instruction, order routing, or execution action

Market-data must not expose these as columns, reason codes, statuses, commands, configuration keys, APIs, tables, or positive feature definitions.

## Eligibility boundary rule (LOCKED)

Eligibility in this package is strictly an upstream, explainable **data-usability** classification for one point-in-time instrument, trade date, and publication context. A compatibility field named `eligible` must be read as `data_usable`, never as `strategy_eligible`.

The eligibility artifact may expose:

- coverage state
- quality state
- factual liquidity measures and explicit proxy labels
- suspension or trading-status state
- event-risk or contamination state
- `eligible` boolean
- blocking or explanatory reason codes

`eligible = true` means the instrument/date satisfies declared data-integrity and product-readiness gates and may be evaluated by a consumer. It does **not** mean the instrument is selected, attractive, ranked, sufficiently liquid for a strategy, approved by alpha policy, or suitable for a portfolio.

`eligible = false` must remain explainable through upstream reason codes. It must not be used to hide a provider-delivery failure, rewrite the coverage denominator based on dormancy or liquidity preference, or encode a rejected trading opinion.

Coverage, quality, liquidity, trading status, event risk, and eligibility must remain separately inspectable. Combining them into an eligibility result does not permit their meanings or evidence to collapse into one field.

### Retirement of the `eligible` alias (LOCKED)

`eligible` is the most policy-suggestive name on the entire market-data surface: read plainly, it says *permitted to trade*. Every contract that uses it must therefore repeat that it means `data_usable`, and that repetition is the only thing preventing the misreading. A compatibility alias that never retires makes the correction permanent rather than temporary.

- `data_usable` is the canonical field. `eligible` is a compatibility alias retained solely so existing consumers do not break.
- The alias is retired once no consumer outside this package reads it, which must be demonstrated rather than assumed.
- Retirement is a versioned read-model change under the consumer read contract; it never silently drops the column.
- Until retirement, **no new artifact, column, reason code, config key, or API field may be named with `eligible`**. New surfaces use `data_usable`. The alias may be preserved, not propagated.

An alias with no retirement condition is a permanent ambiguity, not a compatibility measure.

## Indicator boundary rule (LOCKED)

Indicators are deterministic, versioned measurements derived from one declared coherent price basis. They may be included because the Weekly Swing consumer needs them, but they remain facts.

Indicators must not become:

- strategy signals
- ranked scores or candidate ordering
- recommendation engines
- hidden entry/exit rules
- hidden position-sizing inputs with an upstream action attached

A threshold that converts an indicator into a trade preference belongs to watchlist policy. Market-data may expose the indicator value and its quality/warm-up state, not the downstream action.

## Liquidity boundary rule (LOCKED)

Market-data may publish actual liquidity facts and clearly named proxies, including their units, basis, date, and quality state.

Market-data must not:

- rename a proxy as actual traded value
- use a strategy preference to decide whether a market observation was expected
- silently remove illiquid or dormant instruments from coverage
- turn a liquidity measure into ranking or candidate selection

Any policy-specific preference among otherwise usable instruments belongs downstream. Liquidity and zero-volume observations remain factual fields; a threshold that decides tradability or candidate membership must not enter market-data `eligible`/`data_usable`. Liquidity may block only the validity of a liquidity-derived field itself when its required observation is missing or invalid, not the usability of otherwise valid market facts.

## Dual-use fact rule (LOCKED)

Some facts are legitimately required on **both** sides of this boundary. They are the boundary's weakest point, because each side has a defensible claim to them and the split is easy to lose.

The liquidity rule above is one instance of the general pattern. Stated as a class:

- **Market-data owns the fact** — its value, unit, source, effective date, and quality state.
- **Downstream owns the preference derived from it** — any threshold, ordering, or acceptance decision.
- **Neither side may own the other's half**, and market-data may not hold a configuration key whose only purpose is a downstream threshold.

A fact does not become policy because a strategy uses it, and it does not become market-data because a pipeline reads it.

Known dual-use facts and their split:

| Fact | Market-data use | Downstream use |
|---|---|---|
| Exchange auto-rejection band | Distinguishing an ordinary session move from a change in price scale | Whether a locked instrument can actually be entered or exited |
| Tick / price fraction ladder | Bounding how small a meaningful proportional move can be at a given price | Order price construction and slippage assumptions |
| Exchange lot size | **None** — explicitly disowned by the volume contract | Position sizing |
| Traded-value measures and proxies | Factual liquidity measurement with declared unit and basis | Whether an instrument is liquid enough to trade |
| Trading status and event risk | Bar expectation, contamination, and data usability | Event-avoidance preference |

When a new dual-use fact appears, its owner contract must state both halves explicitly before the fact reaches a published output. Recording only the half that market-data needs is how the boundary erodes: the other half then gets decided implicitly, by whichever component happens to read it first.

## Publication and recency boundary rule (LOCKED)

- Publication currentness means one sealed version is the governed readable state; it is not a trading recommendation.
- Effective-date fallback means the requested date was not readable and a prior sealed date was resolved; it is not an entry-timing or recency signal.
- A development data frontier is an operational evidence fact; it is not a strategy horizon.
- Operational freshness protects consumers from stale inputs; it does not decide whether a fresh instrument should be traded.

## Replay and backtest boundary rule (LOCKED)

Market-data replay verifies that point-in-time source, universe, corporate-action, configuration, and publication rules reproduce the governed data product.

Strategy backtesting may consume point-in-time market-data, but its signal rules, trades, P&L, performance metrics, and conclusions belong downstream. Market-data replay must never be described as proof that a trading strategy works.

## Session snapshot boundary rule (LOCKED)

A session snapshot is optional, non-streaming, supplemental, aligned to the effective readable trade date, and captured at a real wall-clock time.

It must never become:

- a hidden screening engine
- a replacement for watchlist logic
- a proxy ranking layer
- entry/exit timing infrastructure

## Consumer dependency rule (LOCKED)

Consumers may read one coherent publication and apply their own policy outside this module. Market-data must not silently embed consumer policy inside:

- acquisition or canonicalization
- price adjustments
- eligibility or reason-code semantics
- coverage denominator rules
- publication/readiness rules
- indicator names or values
- anomaly or event-risk classifications
- snapshot membership
- source or platform configuration

A policy-only change in ranking, entry/exit, sizing, or portfolio preference must not mutate existing upstream observations or publication history. A new factual field required by a consumer must be introduced through an explicit, versioned market-data contract rather than by embedding strategy behavior. A downstream use case may influence which factual fields are prioritized, but it cannot define whether market-data itself is correct or ready.

## Artifact, command, and configuration naming rule (LOCKED)

Names in this domain must remain upstream-neutral.

Examples of valid upstream names:

- `eod_bars`
- `eod_indicators`
- `eod_eligibility`
- `eod_publications`
- `market_data_read_product_v1`
- `session_snapshots`

Examples forbidden as market-data artifacts or commands:

- `trade_candidates`
- `entry_signals`
- `ranked_picks`
- `buy_watchlist`
- `position_recommendations`

A DTO/schema owned inside `docs/market_data` must use market-data/read-product naming. A consumer-specific Weekly Swing policy DTO, ranking view, or screening contract belongs to the downstream watchlist package and may only reference the versioned market-data read product.

Sorting by a stable technical key for deterministic output or pagination is allowed. Assigning preference order, rank, score, or priority to instruments is not.

## Forbidden active semantics

The following terms are forbidden as positive Market Data Platform feature semantics unless explicitly used to state a prohibition or downstream ownership:

- buy or sell
- long or short
- entry or exit
- ranking or priority
- instrument candidacy or pick selection for a trade
- alpha, edge, or conviction
- target price, stop loss, or take profit
- position or risk sizing
- portfolio action
- broker or execution action

Allowed wording includes "out of scope", "must not be interpreted as", "not produced by this module", and explicit descriptions of downstream behavior.

### Overloaded vocabulary (LOCKED)

Three words carry a legitimate upstream meaning **and** a forbidden downstream meaning. The forbidden list above targets the downstream sense only. Reading it as a word blacklist would flag correct upstream usage across most of this package.

| Word | Legitimate upstream meaning | Forbidden meaning |
|---|---|---|
| `candidate` | An artifact awaiting validation, sealing, or review — a candidate publication, candidate bar, or detector candidate | An instrument being considered for a trade |
| `target` | A publication or run destination, such as `publish_target` | A price objective for a position |
| `policy` | A governed rule for handling data, such as an error or retention policy | A trading or allocation rule |

Any lint, guard test, or review that enforces the forbidden list must distinguish these senses. A guard that cannot make the distinction must check the surrounding contract, not the token, because `candidate` alone appears legitimately in over one hundred documents in this package including this one.

New vocabulary that would collide in the same way must be avoided rather than disambiguated later.

## Boundary invariants

1. Market-data readiness is judged from data evidence, not watchlist outcomes.
2. Market-data facts may be inputs to watchlist policy, never outputs of it.
3. Upstream readability and data usability are not downstream desirability.
4. Canonicalization and quarantine are not strategy filtering.
5. Indicator derivation is not signal generation.
6. Eligibility is not ranking, selection, tradability approval, or alpha approval.
7. Liquidity facts are not candidate ordering.
8. Session snapshots are not real-time signal infrastructure.
9. Publication currentness and effective-date fallback are not trading recency recommendations.
10. Market-data replay is not strategy backtesting or profitability proof.
11. Downstream policy must not rewrite upstream facts or historical publications.
12. A dual-use fact must have both halves stated by its owner contract before it reaches a published output; recording only the upstream half lets the downstream half be decided implicitly.
13. The forbidden-terms list targets meanings, not tokens; `candidate`, `target`, and `policy` carry legitimate upstream senses that no guard may flag on the word alone.
14. A compatibility alias without a stated retirement condition is a permanent ambiguity; `eligible` may be preserved but never propagated to a new surface.

## Boundary acceptance test (LOCKED)

A market-data artifact violates this boundary if, without reading a separate watchlist policy, it answers any of these questions:

- Which instrument should be preferred or selected?
- At what price or time should a position be opened or closed?
- How much capital should be allocated?
- What portfolio or broker action should occur?
- Is the strategy expected to be profitable?

An artifact remains within the boundary when it answers factual questions such as what was observed, which version produced it, whether it is complete and trustworthy, which price basis was used, and why the data is or is not usable.

## Required cross-contract alignment

This owner contract must remain aligned with:

- `Terminology_and_Scope.md`
- `Downstream_Consumer_Read_Model_Contract_LOCKED.md`
- `Downstream_Data_Readiness_Guarantee_LOCKED.md`
- `EOD_Eligibility_Snapshot_Contract_LOCKED.md`
- `../registry/Exchange_Market_Structure_Facts_LOCKED.md` — owns the dual-use exchange facts split above
- `../registry/Volume_and_Turnover_Normalization_LOCKED.md` — owns the actual-versus-proxy split and the lot-size disownment
- `../session_snapshot/Session_Snapshot_Contract_LOCKED.md`

Where a dependent document uses older wording that conflates eligibility with readability or policy, this owner boundary takes precedence until that dependent contract reaches its ordered strategy-update step.

## Anti-domain-leak rule (LOCKED)

If any document, schema field, configuration key, code path, test, command, or API in this module can be read as producing trading advice, watchlist ranking, entry/exit guidance, sizing, portfolio action, or execution guidance rather than governed market facts and publication semantics, it violates the domain boundary.
