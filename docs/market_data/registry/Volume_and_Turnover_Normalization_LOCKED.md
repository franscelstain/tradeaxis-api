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

## Lot-size boundary

Exchange lot size belongs to downstream order/position sizing, not market-data traded-value normalization. Market-data must not multiply share volume by lot size or own a position-sizing configuration.

## Precision and correction

- Preserve source precision and use declared decimal arithmetic.
- Round only at the locked storage/presentation boundary.
- Source unit/value correction creates new observation and publication lineage.
- Never rewrite raw volume or historical values to repair a proxy.

## Acceptance criterion (LOCKED)

Every liquidity value declares whether it is actual or proxy, its units, market/price basis, window, source/formula version, and quality state. No consumer can mistake `price * volume` for official traded value.

## Cross-contract alignment

- `../book/Market_Daily_Metrics_Contract.md`
- `../book/EOD_Bars_Contract.md`
- `../indicators/EOD_Indicators_Formula_Spec.md`
- `Indicator_Registry_Baseline_LOCKED.md`
