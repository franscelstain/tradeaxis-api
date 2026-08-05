# Volume, Traded Value, and Turnover Proxy Normalization (LOCKED)

## Purpose

Prevent actual exchange-reported traded value from being confused with a price-times-volume proxy or with lot-based position sizing.

## Canonical volume

- `RAW volume` stores provider/source-reported traded share units after verified unit normalization.
- Provider unit identity and normalization evidence are mandatory.
- No lot multiplier is applied when the source unit is shares.
- Zero volume is a real source-backed value distinct from missing volume/bar.
- Structural-adjusted volume is a separate analytical field and never overwrites raw volume.

## Actual traded value (LOCKED)

`traded_value_idr_actual` (or equivalent explicitly named field) is populated only from a source field whose semantics and units represent actual Regular-Market traded value for the session.

- Source, currency, market segment, observed date, and quality state are required.
- Unavailable actual value is `NULL`, never derived silently.
- Trade count/frequency is also source-backed and separately nullable.

## Close-times-volume proxy (LOCKED)

When actual value is unavailable, the platform may expose:

`close_volume_proxy_idr = RAW close * RAW volume`

This is an explicitly named nominal liquidity proxy, not actual turnover/traded value. It must carry formula version, `RAW` basis, window, and proxy label.

Using structural-adjusted price with raw volume is dimensionally inconsistent and forbidden. Even when coherent adjusted price/volume preserve notional scale, the result remains a derived proxy and must not be named actual.

## Rolling metrics

- `adv20_traded_value_idr_actual`: average of 20 source-backed actual daily traded values; `NULL` when required actual values are unavailable under the formula contract.
- `adv20_close_volume_proxy_idr`: average of 20 `RAW close * RAW volume` proxy values.
- legacy `dv20_idr` must be explicitly documented/migrated as a compatibility alias for the proxy; it must not be presented as actual traded value.

Actual and proxy fields must never be coalesced into one output across dates.

### Proxy labelling must be persisted (LOCKED)

The proxy is required above to carry formula version, `RAW` basis, window, and proxy label. Those are properties of a stored artifact, not of a sentence in this contract. A metric that carries them only in documentation carries them nowhere a consumer can read.

- Formula version, price basis, window length, and an explicit actual-versus-proxy marker are **queryable fields** accompanying the metric, resolvable from the same publication context.
- A consumer must be able to tell an actual value from a proxy **without consulting a document and without parsing a column name**. Name conventions assist readers; they are not a machine-readable contract.
- A metric whose actual-versus-proxy marker is absent is not assumed to be a proxy. It is **unlabelled**, and an unlabelled liquidity metric may not be published.

### Retirement of the `dv20_idr` alias (LOCKED)

`dv20_idr` names neither its basis nor its proxy nature. Read plainly, `dv` suggests daily value and `_idr` asserts a currency amount, which is precisely the reading this contract forbids. Documenting it as an alias corrects the reading for whoever reads the documentation; the column keeps asserting the wrong thing to everyone else.

- The canonical fields are the explicitly named actual and proxy metrics. `dv20_idr` is retained solely so existing consumers do not break.
- The alias is retired once no reader outside this package depends on it, demonstrated rather than assumed, through a versioned read-model change.
- Until retirement, **no new artifact, column, contract, or API field may be named `dv*` or otherwise imply traded value without stating its basis**. New surfaces use the explicit names. The alias may be preserved, not propagated.
- An alias may not stand in for the field it aliases. Where the explicitly named proxy field does not yet exist, the alias is a gap to close, not a substitute that satisfies this contract.

This is the third compatibility alias in this package to require an explicit end, after `eligible` and `ticker_id`. The pattern is now established: **an alias introduced without a retirement condition becomes permanent, and its misleading reading becomes the platform's default meaning.**

## Lot-size boundary

Exchange lot size belongs to downstream order/position sizing, not market-data traded-value normalization. Market-data must not multiply share volume by lot size or own a position-sizing configuration.

## Precision and correction

- Preserve source precision and use declared decimal arithmetic.
- Round only at the locked storage/presentation boundary.
- Source unit/value correction creates new observation and publication lineage.
- Never rewrite raw volume or historical values to repair a proxy.

## Capability boundary (LOCKED)

**What these metrics prove.** That a reported figure is dimensionally coherent — shares are shares, currency is currency, and a proxy is never renamed as actual; that unit normalization is evidenced; and that zero volume is preserved as a source-backed fact rather than treated as missing.

**What they cannot prove.**

- **That the proxy resembles actual turnover.** `RAW close × RAW volume` values every share at the closing price. A session whose trades occurred far from the close produces a proxy that is dimensionally correct and materially different from the traded value it stands in for. The gap is largest exactly where it matters most: volatile and thinly traded instruments.
- **That the metric describes tradable liquidity.** A twenty-session average can be dominated by one exceptional day. Two instruments with identical `dv20` may differ entirely in whether a position could be exited over the horizon, and that judgement belongs downstream.
- **That the volume figure is complete.** The metric normalises what the source reported. Volume missing or wrong at the source produces a clean, confident, wrong metric — as a single acquisition-date defect already demonstrated in this dataset.
- **That a shortened session was accounted for.** Session length is a calendar fact, not a volume fact. A window spanning shortened sessions is mechanically depressed, and nothing in this contract detects it.

Consequently a liquidity metric may be cited as evidence of **reported trading activity on a declared basis**, never as evidence of **actual turnover** or **executability**.

## Acceptance criterion (LOCKED)

Every liquidity value declares whether it is actual or proxy, its units, market/price basis, window, source/formula version, and quality state. No consumer can mistake `price * volume` for official traded value.

## Cross-contract alignment

- `../book/Market_Daily_Metrics_Contract.md`
- `../book/EOD_Bars_Contract.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `Indicator_Registry_Baseline_LOCKED.md`
