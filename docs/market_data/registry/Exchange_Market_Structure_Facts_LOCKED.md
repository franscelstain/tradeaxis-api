# Exchange Market Structure Facts (LOCKED)

## Purpose

Own the IDX Regular-Market structural facts that market-data needs in order to tell an **ordinary session move** apart from a **change in price scale**. Before this contract these facts existed only as constants inside services, with no source, no effective date, and no owner.

## Scope boundary (LOCKED)

Each fact below has two possible uses. Market-data owns only the first.

| Use | Owner |
|---|---|
| Interpreting an observed move — is this gap reachable within one session, or does it imply a scale change? | market-data, this contract |
| Deciding whether a position can actually be entered or exited at a price | downstream order/position sizing, **not** market-data |

This is the same split already applied to exchange lot size in `Volume_and_Turnover_Normalization_LOCKED.md`. Market-data must not own a tradability, slippage, or execution-feasibility configuration.

## Facts owned here

### Auto-rejection band

The exchange constrains how far a Regular-Market price may move from its reference within one session. Market-data uses the band as an **interpretation ceiling**: a same-session move beyond the band cannot be an ordinary market move, so a structural explanation must be sought or the window must fail safe.

Requirements:

- the band is **tiered by price level** and must be stored as a tier table, not a single scalar;
- each tier row carries effective start/end dates, because the exchange has revised these bands over time and the dataset begins `2023-01-02`;
- upper and lower limits are stored separately, because they have not always been symmetric;
- each row carries source reference and verification state under the same rules as other governed reference data;
- resolution for trade date `T` uses rows valid on `T`; a current band must never be projected backward.

**Current implementation state — unsourced.** `CorporateActionDerivationService::MAX_EXCHANGE_SESSION_MOVE = 0.35` is presently a single hardcoded scalar with no tier table, no effective dating, and no source reference. It is recorded here as a placeholder that requires sourcing, not as a verified fact. Until sourced and effective-dated, no output may describe a band-based verdict as exchange-verified.

### Minimum Regular-Market price

A price floor applies to the Regular Market. Market-data uses it to recognise where proportional reasoning breaks down: near the floor a single tick is already a large percentage move, so ratio-based anomaly logic loses meaning.

Requirements: stored value, effective dating, and source reference, under the same rules as the band.

### Tick / price fraction ladder

Order prices move in exchange-defined increments that vary by price level. Market-data uses the ladder only to bound how small a *meaningful* proportional move can be at a given price.

Requirements: stored as a tiered, effective-dated table with source reference.

## Consumers

These contracts consume the facts above and must not restate or redefine them:

- `Price_Scale_Break_Detection_LOCKED.md` — references exchange price-band exceedance; the band itself is defined here
- `Price_Adjustment_Contract_LOCKED.md` — continuity reasoning across verified actions
- `../book/Corporate_Action_and_Adjustment_Policy.md` — continuity verdict boundaries

A consumer may cite these facts. A consumer may not own them, hardcode them, or hold a second copy that can drift.

## Prohibited use (LOCKED)

- treating a band exceedance as proof that a corporate action occurred — the band bounds interpretation, it does not identify an event;
- treating a move inside the band as proof that no structural change occurred, for the same reason silence from a bounded detector is not evidence;
- applying a current band, floor, or tick ladder to a historical date;
- deriving a price or volume factor from band arithmetic;
- using these facts to score, rank, or filter instruments for tradability — that boundary belongs downstream.

## Capability boundary (LOCKED)

**What these facts prove.** That a given move was or was not reachable within one session under the band effective on that date; that proportional reasoning is or is not meaningful at a given price level; that the values used in a decision were sourced and effective-dated.

**What they cannot prove.**

- **That an event occurred.** A band is a bound on what one session can produce, not an observation of what happened. Exceeding it says an ordinary move cannot explain the gap — it identifies no action, type, terms, or factor.
- **That a move inside the band was ordinary.** Most corporate actions produce effects well inside the band. Containment is the weaker inference of the two and carries no evidential weight, which is why `GAP_AMBIGUOUS` exists as a distinct verdict rather than a clean result.
- **That the recorded band applied on that date.** These are effective-dated rows. A tier boundary recorded with the wrong effective date silently reclassifies every move measured against it, in both directions.
- **That an unsourced value is a fact.** Until the band, floor, and tick tables carry source references and reconciliation dates, they are placeholders. The current in-code single scalar is recorded above as exactly that.

Consequently an exceedance result may be cited as evidence that **an ordinary session move is insufficient explanation**, never as evidence that **a corporate action occurred**.

## Acceptance criterion (LOCKED)

Every band, floor, or tick value used in a market-data decision resolves from an effective-dated, source-referenced row valid on the requested trade date. An unsourced constant may be used for no decision that reaches a published output.

## Cross-contract alignment

- `Price_Scale_Break_Detection_LOCKED.md`
- `Price_Adjustment_Contract_LOCKED.md`
- `Volume_and_Turnover_Normalization_LOCKED.md`
- `../book/Market_Calendar_Requirements_Contract.md`
- `../book/Domain_Boundary_Invariants_LOCKED.md`
