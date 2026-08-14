# Corporate Action Type Registry (LOCKED)

## Purpose
Define the canonical taxonomy for `market_data_corporate_actions.action_type` and the
price/volume continuity semantics that each type implies for indicator computation.

Before this registry existed, `action_type` was a free-form string. `ImportCorporateActionsCommand`
accepted any non-empty uppercase token, and `EventRiskSourceRepository` resolved corporate actions
only on the exact `action_date`. As a result the platform had no way to know which actions break the
price series, and no way to protect indicator windows that still contain pre-action prices.

This registry closes that gap. It is the corporate-action counterpart of
`market_data_trading_status_event_types`, and it follows the same dictionary pattern:
the daily import stores only event identity, while semantics live in a seeded dictionary table.

This registry is upstream-only. It does not encode watchlist screening, scoring, or strategy meaning.

This registry declares type-level continuity/risk semantics only. It does not verify that an event occurred, select an ex-date, derive a factor, or authorize adjustment. Those decisions require a verified event/factor revision under `../book/Corporate_Action_and_Adjustment_Policy.md`.

---

## Dictionary table contract (LOCKED)

Table: `market_data_corporate_action_types`

| Column | Type / contract | Field role |
|---|---|---|
| `action_type_code` | `VARCHAR(64) NOT NULL` | canonical action identity; primary key |
| `price_continuity_impact` | `VARCHAR(32) NOT NULL` | `NONE`, `SCALED`, or `GAP_UNKNOWN_MAGNITUDE` |
| `volume_continuity_impact` | `VARCHAR(32) NOT NULL` | `NONE` or `SCALED` |
| `share_count_changes` | `TINYINT(1) NOT NULL DEFAULT 0` | whether outstanding share count changes |
| `description` | `VARCHAR(255) NULL` | human-readable contract note |
| `created_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | audit metadata |
| `updated_at` | `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP` | audit metadata |

---

## Continuity impact semantics (LOCKED)

### `price_continuity_impact`

- **`NONE`** — the action does not change the price scale. Indicator windows spanning the action
  date remain valid.

- **`SCALED`** — the action applies a multiplicative change to the price scale. Every bar before the
  verified ex/effective date is expressed on a different scale than every bar on/after it. Any indicator whose
  window spans that boundary is meaningless unless a verified coherent factor is applied.

  `SCALED` means the **unit is redefined**, not merely that the company issued more shares. This
  distinction decides most classifications in the table below, so it is stated explicitly:
  a stock split redefines what one share is, whereas a private placement or a warrant exercise adds
  new shares denominated in the existing unit. The first makes historical values incomparable; the
  second is dilution, which is a fundamental matter for the consumer and not an arithmetic defect in
  the series.

- **`GAP_UNKNOWN_MAGNITUDE`** — the action applies an additive price gap whose size cannot be
  determined from currently stored data. `market_data_corporate_actions` records only
  `action_date` and `action_type`; it does not store `dividend_per_share`, `ratio_from`, or
  `ratio_to`. Without the amount, the platform cannot distinguish an immaterial gap from a material
  one, so it must not claim the window is clean.

### `volume_continuity_impact`

- **`NONE`** — traded share units remain on the same scale across the action date. Dilution belongs
  here: issuing new shares at the existing unit leaves yesterday's and today's volume directly
  comparable.
- **`SCALED`** — the share unit is redefined, so raw `volume` before and after the verified ex/effective date are
  not comparable. `vol_ratio` in particular becomes a false volume-expansion signal, because a 1:4
  split multiplies typical daily share volume by roughly four while the underlying activity is
  unchanged.

---

## Canonical action types (LOCKED)

### Unit redefinition — both price and volume history change scale

| `action_type_code` | Price impact | Volume impact | Share count changes | Note |
|---|---|---|---:|---|
| `STOCK_SPLIT` | `SCALED` | `SCALED` | 1 | Pemecahan saham. Share unit redefined; price divided, share count multiplied. |
| `REVERSE_STOCK_SPLIT` | `SCALED` | `SCALED` | 1 | Penggabungan saham. Share unit redefined; price multiplied, share count divided. |
| `BONUS_SHARE` | `SCALED` | `SCALED` | 1 | Saham bonus. Every holder receives proportional extra shares, so the unit is redefined. |
| `STOCK_DIVIDEND` | `SCALED` | `SCALED` | 1 | Dividen saham. Same mechanics as bonus share. |
| `MERGER` | `SCALED` | `SCALED` | 1 | Share exchange ratio applies to every holder. |

### Price series rescaled, share unit unchanged

| `action_type_code` | Price impact | Volume impact | Share count changes | Note |
|---|---|---|---:|---|
| `RIGHTS_ISSUE` | `SCALED` | `NONE` | 1 | PMHMETD. The ex-rights adjustment rescales price history, but no holding is multiplied automatically, so volume stays comparable. |
| `CASH_DIVIDEND` | `GAP_UNKNOWN_MAGNITUDE` | `NONE` | 0 | Ex-date price gap approximates the dividend; magnitude unknown without amount data. |

### Dilution only — new shares issued at the existing unit

These do not quarantine anything. The event still reaches consumers through the exact-date
`corporate_action_flag` context, where dilution belongs as a fundamental input rather than as an
arithmetic defect.

| `action_type_code` | Price impact | Volume impact | Share count changes | Note |
|---|---|---|---:|---|
| `PRIVATE_PLACEMENT` | `NONE` | `NONE` | 1 | PMTHMETD. Dilution without redenomination. |
| `NON_PREEMPTIVE_RIGHTS_ISSUE` | `NONE` | `NONE` | 1 | PMTHMETD variant recorded by the source feed. |
| `WARRANT` | `NONE` | `NONE` | 1 | Waran is a separate listed security granting the right, not the obligation, to buy shares at an exercise price within a period. Issuing or exercising it never rescales the underlying series. |
| `WARRANT_EXERCISE` | `NONE` | `NONE` | 1 | Exercise issues new shares at the existing unit. |
| `MANDATORY_CONVERTIBLE_BOND` | `NONE` | `NONE` | 1 | Conversion issues new shares at the existing unit. |
| `ESOP_MSOP` | `NONE` | `NONE` | 1 | Employee option exercise issues new shares at the existing unit. |

### Lifecycle and identity — no continuity to break

| `action_type_code` | Price impact | Volume impact | Share count changes | Note |
|---|---|---|---:|---|
| `IPO` | `NONE` | `NONE` | 0 | Listing date. There is no prior series that could be discontinuous. |
| `DELISTING` | `NONE` | `NONE` | 0 | Seluruh saham emiten dikeluarkan dari pencatatan. No forward continuity remains to protect. |
| `PARTIAL_DELISTING` | `NONE` | `NONE` | 0 | Hanya sebagian saham/efek dikeluarkan dari pencatatan; emiten dan ticker tetap tercatat. |
| `PARTIAL_RELISTING` | `NONE` | `NONE` | 0 | Sebagian saham yang sebelumnya tidak tercatat dicatatkan kembali. Kebalikan dari partial delisting. |
| `CAPITAL_DEFICIENCY` | `NONE` | `NONE` | 0 | Kondisi kurang modal: ekuitas emiten di bawah persyaratan tertentu. Kondisi keuangan/regulasi, bukan transaksi saham. |

#### Lifecycle semantics recorded from the source domain

These three are classifications of a corporate or lifecycle event, not triggers for historical price
adjustment. IDX itself publishes "Partial Delisting" and "Kurang Modal" as corporate action
categories. Within this system their function is to serve as a risk marker, as audit context, and as
information about a change in the status of an issuer or a security.

- **`PARTIAL_DELISTING`** — only part of the shares or securities is removed from listing. The issuer
  and the ticker remain listed, which is what separates it from full `DELISTING`. A reduction in
  listed share count does not necessarily mean the company's outstanding shares were destroyed or
  erased. Useful as context for analysing changes in liquidity, listed share count, free float, and
  tradable-security status.

- **`PARTIAL_RELISTING`** — shares previously unlisted or removed from listing are listed again. It is
  not a new IPO, because the issuer was already listed. Useful as context for analysing an increase
  in listed shares, restored trading status for part of the shares, liquidity, and free float.

- **`CAPITAL_DEFICIENCY`** — the source reports that the issuer's capital or equity is deficient or
  below a given requirement. It is a financial and regulatory condition, not a share transaction, so
  it produces no mechanical change to price. It exists to give consumers a fundamental and regulatory
  warning: solvency risk, capital shortfall, need for additional capital, potential action by the
  exchange or regulator, and elevated fundamental risk.

None of the three causes historical price adjustment. That is why all three carry
`price_continuity_impact = NONE`, and it is a deliberate classification rather than an absence of
information.

---

## Known modelling gap — risk semantics are not represented here

> Historical implementation-gap note: current strategy is governed by the correction section at the end of this registry. Exact-date-only behavior described here is not the target contract.

This registry models exactly one dimension: whether an action breaks price or volume continuity.
That is sufficient for indicator quarantine and nothing else.

The lifecycle semantics above describe a second, orthogonal dimension that this registry does not
capture. `market_data_trading_status_event_types` has `risk_family`, `transition_type`, and
`carries_forward`; the corporate action dictionary has no equivalent. Two consequences follow, and
both are recorded here rather than silently tolerated:

1. **Every corporate action is currently treated as event risk.** `EventRiskSourceRepository` sets
   `event_risk_flag = 1` for any action row on the exact date, so `COMPANY_NAME_CHANGE` and
   `CAPITAL_DEFICIENCY` produce the same flag. A consumer cannot distinguish an identity change from
   a solvency warning without parsing `corporate_action_types` text.

2. **`CAPITAL_DEFICIENCY` is a persisting condition modelled as a one-day event.** Corporate action
   context is exact-date only. An issuer reported deficient on one date carries no flag the following
   day, even though the condition has not changed. Suspension and special monitoring solve this with
   `carries_forward` plus a matching clearing event; the corporate action taxonomy contains no
   clearing event, so carry-forward cannot simply be enabled without first defining what ends the
   state.

Closing this gap requires a decision about clearing semantics and is intentionally out of scope for
the contamination contract. It must not be worked around by reusing
`price_continuity_impact` to express risk.
| `TICKER_CODE_CHANGE` | `NONE` | `NONE` | 0 | Identity change only. |
| `COMPANY_NAME_CHANGE` | `NONE` | `NONE` | 0 | Identity change only. |

Operators may extend this table, but every added row must declare explicit continuity impact.
No row may be seeded with a NULL or empty impact value.

---

## Unknown action type policy (LOCKED)

If `market_data_corporate_actions.action_type` holds a value that has no row in
`market_data_corporate_action_types`, the resolver must treat it **fail-safe** as:

- `price_continuity_impact = SCALED`
- `volume_continuity_impact = SCALED`

and must emit reason code `EVENT_RISK_CA_TYPE_UNMAPPED` so the unmapped type is visible in run
events and evidence.

This mirrors the existing anti-assumption rule for trading status in
`Indicator_Registry_Baseline_LOCKED.md`: absence of source semantics must never be converted into a
fabricated non-risk state. An unmapped corporate action is an unknown, not a safe one.

Fail-safe treatment is a protection, not a resolution. Unmapped types must be mapped explicitly by
an operator; leaving a type unmapped permanently quarantines the affected ticker windows.

---

## Relationship to existing event-risk context (LOCKED)

This registry does **not** change the meaning of the existing exact-date event-risk fields.

- `corporate_action_flag` and `corporate_action_types` remain **exact-date** context, resolved only
  where `action_date = trade_date`. Their meaning is unchanged.
- Window contamination is a **separate** concern with separate fields and a separate reason code, as
  defined in the `Indicator_Registry_Baseline_LOCKED.md` amendment.

Keeping the two separate matters: exact-date event risk answers "did something happen today", while
contamination answers "is the arithmetic in this window still trustworthy". Collapsing them would
lose the ability to distinguish an action that happened today from an action 30 days ago that still
poisons `ma50`.

---

## Forbidden behavior (LOCKED)

- Inferring continuity impact from the action type string at runtime instead of reading the
  dictionary.
- Treating an unmapped action type as `NONE` for either price or volume.
- Using `adj_close` as a substitute for this contract. Provider-adjusted close does not repair
  `high`, `low`, or `volume`, so `hh20`, `ll20`, `atr14_pct`, and `vol_ratio` stay contaminated even
  when `adj_close` is used. Provider adjustment is also silently restated over history, which
  conflicts with the sealed-publication immutability rule in `docs/market_data/README.md`.
- Introducing a parallel corporate-action taxonomy anywhere else in the repository.

---

## Capability boundary (LOCKED)

**What the type registry proves.** That a registered action type has one governed meaning — whether it adjusts price, whether it adjusts volume, which effective date anchors it, and what event-risk it carries — and that this meaning is versioned rather than inferred at runtime.

**What it cannot prove.**

- **That an instance is what its type says.** The registry defines the class. Whether a particular recorded action truly is a split, and on what terms, is a verification question owned by `../book/Corporate_Action_and_Adjustment_Policy.md`. A correctly typed action with wrong terms is fully registry-conformant.
- **That an unregistered type is harmless.** An action whose type is absent from the registry has no governed semantics. It must quarantine, not default to non-adjusting, because absence of a rule is not a rule of no effect.
- **That the type set is complete.** Exchanges introduce and rename action types. The registry covers what has been registered; a genuinely new type first appears as an unknown, and the safety of that moment depends on the quarantine rule above rather than on this registry.
- **That adjusting semantics equal adjustment applied.** A type marked adjusting states that a factor is required, not that one exists or was verified.

Consequently a type-conformant action set may be cited as evidence that **recorded types carry governed semantics**, never as evidence that **the actions themselves are verified or complete**.

## Known limitation and planned follow-up

`GAP_UNKNOWN_MAGNITUDE` exists because `market_data_corporate_actions` currently stores no
quantitative payload. The table has `action_date` and `action_type` but no `ratio_from`, `ratio_to`,
`cum_date`, `ex_date`, or `dividend_per_share`.

Consequences of that limitation, recorded here so it is not mistaken for a design choice:

1. `CASH_DIVIDEND` must be quarantined conservatively even when the yield is immaterial, because the
   platform cannot measure the gap.
2. The platform cannot perform its own back-adjustment of the canonical bar series, and therefore
   cannot offer an alternative to quarantine.

Capturing the quantitative payload is the prerequisite for a future adjustment engine. Until that
exists, quarantine is the only contract that never emits a wrong number.

---

## Current event-lifecycle strategy correction (LOCKED)

This section supersedes older implementation-state wording above where it describes exact-date-only `action_date` behavior or a future adjustment engine.

- The target event model requires immutable event identity/revision, stable instrument/listing identity, source observation/reference, announcement/cum/ex/record/payment/effective dates where applicable, quantitative terms, verification state, factor revision, and as-known timestamps.
- Event occurrence context, event-risk lifecycle, and analytical-window contamination are separate facts.
- `ex_date` is the primary continuity/event-risk anchor; legacy `action_date = trade_date` projection is insufficient when ex-date or persistent lifecycle semantics are required.
- Persisting conditions require explicit effective/carry/clear semantics. They must not be fabricated as one-day-only or indefinite.
- `price_continuity_impact` must never be reused as a generic risk-family field.
- Adjustment capability is current target strategy under `Price_Adjustment_Contract_LOCKED.md`, but only verified event/factor revisions may activate it.
- Missing terms keep an event non-adjustable and quarantined. Presence of columns alone does not verify values, and price-gap derivation alone cannot fill them.
