# Price Scale Break Detection Contract (LOCKED)

## Purpose

Detect and preserve evidence of possible price-scale discontinuities while failing safe when corporate-action or source-revision evidence is incomplete.

## Detector-only boundary (LOCKED)

The detector may identify an anomaly candidate; it is not a repair engine, corporate-action authority, factor authority, or permission to mutate data.

A detected break may suggest that observations use different scales, but price movement alone cannot prove:

- corporate-action identity or type
- verified ex-date/effective date
- whether source data is wrong versus legitimately as-traded
- an adjustment-active price or volume factor

## Detection sensitivity boundary (LOCKED)

The detector has a lower bound. It must be stated, because silence from a bounded detector is not evidence.

- **Ratio floor.** A candidate is raised only when the implied ratio between adjacent observations reaches `market_data.price_scale_break.min_ratio` (default `1.7`). That is roughly a **41.6 percent single-session move**. Anything smaller produces no candidate at all.
- **Minimum price guard.** Observations below the configured minimum price are excluded, because at that level a single tick is already a large proportional move. This narrows coverage further at the low end of the price range.
- **Adjacency requirement.** Only calendar-adjacent observations are compared. A discontinuity spread across a gap in the series is not seen as a break.

Consequently there is a **blind region**: every genuine scale change whose magnitude falls below the ratio floor is permanently invisible to this detector, at any run, for any configuration version that keeps the current floor.

Rights issues sit squarely inside that region. A theoretical ex-rights price commonly lands **10 to 30 percent** below the cum price — a real discontinuity in the series, and one this detector will never raise.

### Silence is not evidence (LOCKED)

- Absence of a detected break must never be recorded, reported, or reasoned about as proof that a window is free of scale change.
- Absence of a detected break must never release a quarantine, dismiss a candidate, satisfy a continuity check, or downgrade a corporate-action verdict.
- Releasing a quarantine requires positive evidence under the release rules below. Evidence derived from this detector's non-firing is inadmissible for that purpose.
- Lowering the floor does not convert past silence into past evidence. It creates a new detector version whose findings apply going forward under the idempotency rule.

This mirrors, for detection, the prohibition that `Price_Adjustment_Contract_LOCKED.md` already applies to factors: an absent factor is not proof of a clean window.

### Sensitivity disclosure requirement (LOCKED)

Any change to the ratio floor, the minimum price guard, or the adjacency rule must update this section in the same change, and must state the resulting blind region in the same units used here. A configuration change that silently moves the boundary is a contract violation.

## Candidate evidence

Each candidate must preserve:

- stable instrument/listing identity
- prior/current observation and trade-date identities
- immutable raw bar/publication references
- prior close, current open, diagnostic gap/ratio, and configured detector version
- market-calendar adjacency and price/tick guards
- detection timestamp/run/config
- candidate classification and review state
- possible corporate-action linkage candidates without declaring them verified

Using open versus prior close may improve diagnostic ratio quality, but it does not change the candidate-only status.

## Candidate classifications

Implementations may classify persistent scale shift, isolated anomaly, mixed-epoch stretch, explained candidate, or unexplained candidate, provided every classification remains diagnostic and separately stores evidence.

Proximity to a corporate-action row, exchange price-band exceedance, persistence, common-ratio tolerance, or ticker-level consensus cannot promote a candidate automatically.

## Linkage and verification (LOCKED)

- A nearby event is a linkage candidate until the event revision, ex/effective date, quantitative terms, and verification state satisfy the corporate-action owner contract.
- Active linkage used for adjustment must be explicit, atomic, and publication-bound.
- Missing/conflicting linkage keeps the candidate quarantining.
- A verified action may explain cause, while the detector still records a separate observation discontinuity; neither date silently overwrites the other.

## Quarantine behavior

Undismissed/unresolved candidates contaminate every dependent analytical window according to the versioned indicator dependency graph. They emit explicit quality/eligibility reasons.

Quarantine may be released only when:

- source evidence proves the candidate is false and a governed dismissal with note/reviewer is recorded; or
- verified corporate-action/factor linkage allows coherent analytical adjustment; or
- a revisioned source correction publishes new immutable bar content and subsequent detection proves the discontinuity resolved

Release changes derived output and therefore requires new computation/publication lineage. Review status alone never mutates bars.

## No-repair rule (LOCKED)

The detector and all commands in its family are forbidden to update `eod_bars`, `eod_bars_history`, sealed snapshots, factor rows, or corporate-action verification in-place.

Matched opposite-direction breaks, inferred consensus ratio, common split ratio, or a command `--apply` flag do not authorize bar transformation. Any source correction must ingest verified replacement observations and create new bar/publication revisions under the correction contract.

The legacy `market-data:repair-price-scale-stretches --apply` direct-mutation behavior is prohibited and must be removed/disabled before production relock. Historical evidence that such repairs ran does not validate the rewritten values.

## Idempotency

Detection for identical input publication/config produces the same candidate identities/results without duplicate active candidates. New observations or detector versions create new evidence/revision context rather than overwriting old findings.

## Forbidden behavior

- treating price-band exceedance as proof a corporate action occurred
- deriving verified factor/type/ex-date from the price series
- repairing single or paired breaks by rewriting bars
- using per-break or consensus inferred ratio as an active factor without verified event terms
- marking a candidate resolved merely because history was mutated
- clearing quarantine on absent review/evidence
- attaching candidates by current ticker text instead of stable temporal identity

## Acceptance criterion (LOCKED)

Every candidate remains traceable and fail-safe; no detector path can change raw/published content or activate a factor, and unresolved candidates always quarantine affected products.

## Cross-contract alignment

- `Price_Adjustment_Contract_LOCKED.md`
- `Exchange_Market_Structure_Facts_LOCKED.md` — owns the exchange price band, minimum price, and tick ladder referenced by this contract
- `../book/Corporate_Action_and_Adjustment_Policy.md`
- `../book/Historical_Correction_and_Reseal_Contract_LOCKED.md`
- `Indicator_Registry_Baseline_LOCKED.md`
