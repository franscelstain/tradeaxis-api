# EOD Indicators Formula Specification (LOCKED)

## Purpose

Define exact, versioned, deterministic formulas for the Weekly Swing upstream indicator product.

## Input identity (LOCKED)

One run binds:

- one immutable raw-bar publication
- `STRUCTURAL_ADJUSTED` price-product/factor-set version for technical price/volume vectors
- one market-calendar/temporal-universe version
- one indicator/formula version
- one output-affecting config snapshot/hash

There is no per-row/per-date fallback to `RAW`, `close`, or provider `adj_close`. Missing/unverified factor state makes affected outputs `NULL`/invalid.

Define coherent analytical values for date X as `O(X), H(X), L(X), C(X), V(X)`. All OHLC and applicable volume factors come from the same structural-adjusted product. Define raw values as `C_raw(X), V_raw(X)`.

## Trading-day and precision rules

- `D[-N]` follows the bound Regular-Market calendar.
- Windows contain required trading-date dependencies, not merely N available rows.
- Missing expected dependency is not skipped or forward-filled.
- Use declared decimal precision with no intermediate rounding; round only at locked storage/presentation boundary.
- Ratio outputs are decimal ratios, not multiplied by 100 unless field name/version says so.

## Liquidity metrics

### Actual traded-value average

When source-backed actual daily traded value `TV(X)` exists for every required session:

`adv20_traded_value_idr_actual(D) = AVG(TV(X)) for X in D[-19]..D`

Missing actual values yield `NULL`; no proxy fallback.

### Close-volume proxy average

`CVP(X) = C_raw(X) * V_raw(X)`

`adv20_close_volume_proxy_idr(D) = AVG(CVP(X)) for X in D[-19]..D`

Legacy `dv20_idr`, if retained, is a versioned compatibility alias of this proxy and must be labelled as such.

## ATR14 Wilder (LOCKED)

### True range

For consecutive valid coherent analytical sessions X and `prev(X)`:

`TR(X) = max(H(X)-L(X), abs(H(X)-C(prev(X))), abs(L(X)-C(prev(X))))`

### Stable seed

The seed chain starts at the later of intentional dataset start `2023-01-02` and the listing's effective start, using the first 15 consecutive expected sessions with valid coherent bars. The first bar supplies previous close; the next 14 sessions supply TR values.

At seed date S:

`ATR14(S) = AVG(first 14 consecutive TR values)`

### Recursive state

For each later expected session D:

`ATR14(D) = ((ATR14(prev(D)) * 13) + TR(D)) / 14`

`atr14_pct(D) = ATR14(D) / C(D)`

Implementation must either persist versioned recursive state or recompute from the same stable seed/history chain. It must never seed from the first row of a sliding load window.

If a required session/bar/factor/state is missing or invalid, ATR is `NULL` at the gap and cannot silently skip it. Correction must rebuild the chain from the last valid prior state/stable seed. A changed historical TR can affect all later ATR values, so mutation impact is recursive/unbounded until recomputed, not a fixed 15-day horizon.

## Volume ratio

`vol_ratio(D) = V(D) / AVG(V(X) for X in D[-20]..D[-1])`

Requires 21 coherent sessions. If the prior average is zero or any required dependency is missing/invalid, output is `NULL` with explicit reason.

## Rate of change

For `N in {5,10,20}`:

`rocN(D) = (C(D) / C(D[-N])) - 1`

Requires both endpoints and coherent factor chain.

## Moving averages

`ma20(D) = AVG(C(X)) for X in D[-19]..D`

`ma50(D) = AVG(C(X)) for X in D[-49]..D`

`dist_ma20(D) = C(D) / ma20(D) - 1`

`dist_ma50(D) = C(D) / ma50(D) - 1`

## Range structure

`hh20(D) = MAX(H(X)) for X in D[-19]..D`

`ll20(D) = MIN(L(X)) for X in D[-19]..D`

`close_to_hh20_pct(D) = C(D) / hh20(D) - 1`

`close_to_ll20_pct(D) = C(D) / ll20(D) - 1`

`range_20_pct(D) = hh20(D) / ll20(D) - 1`

`range_position_20_pct(D) = (C(D)-ll20(D)) / (hh20(D)-ll20(D))`

If `hh20 = ll20`, range position is `NULL`, not zero or infinity.

## Sector/benchmark context

Using coherent, source-backed, point-in-time sector and IHSG benchmark series:

- `sector_roc20(D) = sector_C(D) / sector_C(D[-20]) - 1`
- `rs_20_vs_sector(D) = roc20(D) - sector_roc20(D)`
- `sector_rs_20_vs_ihsg(D) = sector_roc20(D) - ihsg_roc20(D)`

Missing sector membership/index bars leave only dependent fields `NULL`; no current membership or forward fill is allowed.

## Nullability and reasons

Each field owns its dependency/warm-up state. Early dataset/listing dates emit deterministic `NULL` until sufficient history exists. Distinguish at minimum:

- insufficient history
- missing expected dependency
- invalid bar/input
- unresolved structural adjustment/price-scale contamination
- missing benchmark/source fact
- zero denominator
- config/formula/provenance mismatch

A zero-price placeholder is never an input because canonical zero OHLC is forbidden.

## Versioning and corrections

Price basis, factor revision, seed rule, formula, window, rounding, nullability, or required-field change requires a new version, recomputation of every affected date, hashes, seal, publication, and supersession lineage.

## Forbidden behavior

- per-date `adj_close -> close` fallback
- mixed RAW/adjusted OHLC or raw volume with adjusted price in one coherent vector
- sliding-window ATR reseed
- skipping missing expected sessions
- intermediate rounding that changes chain results
- zero/forward-filled dependency bars
- future identity, event, factor, calendar, config, or benchmark state

## Acceptance criterion (LOCKED)

Golden long-chain fixtures must reproduce byte/number-equivalent output for the same publication/factor/config/formula identity and detect a one-value divergence far after an ATR seed or historical correction.

## Capability boundary (LOCKED)

**What a formula proves.** That a value is the exact arithmetic result of its declared definition over its declared window, reproducible to the declared precision.

**What it cannot prove.**

- **That the inputs deserved to be used.** Exactness propagates whatever it is given. A formula applied to a contaminated window returns a precisely computed wrong number.
- **That the window covers the period it names.** Windows are counted in sessions, not elapsed time. Twenty sessions spanning a long holiday sequence, a suspension, or an unrecorded market closure describe a materially longer stretch of calendar than twenty sessions in an ordinary month, while both are labelled the same. A momentum figure therefore measures a period whose real length varies.
- **That a defined value is a meaningful one.** A ratio remains defined as its denominator approaches the exchange minimum price, and a range measure remains defined across a series of no-trade sessions. Definedness is a property of the arithmetic, not of the market.

Consequently a formula result may be cited as evidence that **the declared computation was performed correctly**, never as evidence that **the quantity it names is a faithful description of the period**.
