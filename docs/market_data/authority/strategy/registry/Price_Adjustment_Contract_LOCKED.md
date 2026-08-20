# Price Adjustment Contract (LOCKED)

## Purpose

Define coherent, revisioned analytical price products across verified corporate actions without rewriting canonical `RAW` bars.

## Product separation (LOCKED)

- **`RAW`** — immutable market-observed Regular-Market OHLCV.
- **`STRUCTURAL_ADJUSTED`** — coherent OHLC and, where semantics require, inverse volume adjustment for verified structural actions.
- **`TOTAL_RETURN`** — separately versioned performance product including verified distributions when supported.

Provider `adj_close` is not any of these analytical products and is never a per-row fallback.

## Factor provenance vocabulary (LOCKED)

`adjustment_source` declares where a factor came from. It is a closed vocabulary, mirrored in
`EventRiskSourceRepository::ADJUSTMENT_SOURCES`:

| Value | Meaning | May adjust published output |
|---|---|---|
| `EXCHANGE_ANNOUNCEMENT` | Terms read from a dated exchange announcement | yes |
| `DEPOSITORY_SCHEDULE` | Terms read from the depository's corporate-action schedule | yes |
| `OPERATOR_ENTERED` | Entered by a named operator with a reference to the underlying document | yes |
| `DERIVED_FROM_PRICE_SERIES` | Inferred by the platform from a discontinuity in its own price series | **no** |

Two rules follow, and both are positive rather than exclusionary:

- Only the first three may adjust. A factor whose source is `NULL`, or a value outside this table,
  is **unattributed** and must not rescale published prices — the platform cannot say where the
  number came from, and excluding only the one known-bad value would admit every unknown one.
- A refused factor does not become harmless. Its action still contaminates the window, so the
  series is quarantined rather than published as clean while carrying an unexplained discontinuity.

`DERIVED_FROM_PRICE_SERIES` is a platform observation, not a source. The importer refuses it
outright: accepting it from a CSV would let an inference be laundered into a provenance, after which
nothing could tell the two apart.

## Factor revision model

Each applied factor revision must bind:

- corporate-action/event identity and revision
- verified `ex_date` or explicit verified continuity anchor
- price factor and applicable volume factor
- quantitative source terms and derivation formula
- action semantics and verification state
- source observation/reference/hash
- factor version, created/known timestamp, and superseded factor revision

Factor rows are append-only. A factor used by a sealed publication cannot be mutated.

## Eligibility for adjustment (LOCKED)

A factor is adjustment-active only when:

1. the event revision is `AUTHORITATIVE_VERIFIED` or governed `MANUAL_VERIFIED`
2. action type and quantitative terms are sufficient
3. effective/ex-date is verified
4. factor is finite, positive, economically valid, and source-traceable
5. factor revision is bound to the run/publication/config

`PROVIDER_REPORTED`, `SYNTHETIC_CANDIDATE`, unmapped, conflicting, missing-term, or missing-date events remain non-adjustable and contaminating.

## Structural-adjustment formula (LOCKED)

For raw bar date B expressed on the scale of analytical as-of date D, apply the ordered product of verified structural factor revisions whose verified ex-dates satisfy `B < ex_date <= D`.

All `open`, `high`, `low`, and `close` values use the same cumulative price factor. Volume uses the action-specific volume factor only when the verified event semantics require inverse share-unit adjustment. Values must retain declared precision; no destructive rounding or raw rewrite is allowed.

Rights, bonus, split, reverse-split, merger/exchange, and similar actions may have different quantitative mechanics. A generic price ratio must not invent a volume factor when event terms do not prove it.

## Total-return boundary

Cash distributions and other return components belong to `TOTAL_RETURN`, not implicitly to `STRUCTURAL_ADJUSTED`. If verified distribution terms or required reference values are incomplete, total-return output remains unavailable/contaminated rather than approximated from a price gap.

## Price-break detector boundary (LOCKED)

Price series discontinuity may produce:

- anomaly candidate
- inferred diagnostic ratio
- contamination boundary
- possible linkage candidate

It cannot establish event type, verified ex-date, corporate-action truth, or adjustment-active factor. Exchange price-band heuristics and common-ratio proximity are detection aids only.

## Contamination behavior

- Verified active factor: compute coherent product and disclose event/factor revision.
- Verified event with incomplete factor/date: quarantine affected product/range.
- Unverified or synthetic candidate: quarantine; do not adjust.
- Conflicting factor revisions: hold until one governed revision is selected.
- Factor correction: create new analytical artifacts and publication lineage for all affected dates.

## Determinism

Identical raw publication, temporal event/factor revisions, analytical as-of date, formula version, precision rules, and config must produce identical adjusted vectors and hashes.

## Forbidden behavior (LOCKED)

- rewriting `eod_bars`, `eod_bars_history`, or any sealed snapshot
- deriving an active factor from `open/previous close`, `close`, or gap magnitude alone
- treating a synthetic action as verified
- using provider `adj_close`/`close` fallback across dates
- adjusting close without coherent OHLC
- assuming volume factor without verified action semantics
- mutating factor/ex-date/terms used by a sealed publication
- treating factor `1` or absent factor as proof the window is clean
- deriving actual traded value by multiplying structural-adjusted price with raw volume and naming it actual

## Capability boundary (LOCKED)

**What the adjustment product proves.** That every adjusted value traces to an immutable raw bar, a verified event/factor revision, a formula/config version, and an analytical as-of date; that unresolved evidence fails safe rather than falling back.

**What it cannot prove.**

- **That the series is fully adjusted.** The product applies the factors it has. An event nobody recorded contributes no factor, produces no contamination flag, and leaves a discontinuity that is arithmetically invisible in the output.
- **That a coherent series is a correct one.** Coherence means OHLC and volume were transformed consistently by the same factor set. A wrong factor applied coherently yields a perfectly coherent, wrong series.
- **That factor `1` or an absent factor indicates a clean window** — already prohibited above, and restated here because it is the specific reading this boundary exists to prevent.

Consequently a `STRUCTURAL_ADJUSTED` series may be cited as evidence that **verified factors were applied coherently**, never as evidence that **the window is free of structural events**. That claim requires event completeness under the corporate-action owner contract.

## Acceptance criterion (LOCKED)

Every adjusted value traces to immutable raw bar publication plus verified event/factor revision, formula/config version, and analytical as-of date; unresolved evidence always fails safe.

## Cross-contract alignment

- `../book/Corporate_Action_and_Adjustment_Policy.md`
- `../book/Corporate_Action_and_Adjustment_Policy_Selected_Defaults_LOCKED.md`
- `Price_Scale_Break_Detection_LOCKED.md`
- `Indicator_Registry_Baseline_LOCKED.md`
