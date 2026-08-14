# Indicator Registry — Baseline (STRATEGY LOCKED)

## Purpose and boundary

This registry owns the minimum upstream indicator artifact for the initial EOD read-product profile. Weekly Swing is the first intended consumer, not the indicator artifact's acceptance authority. This registry does not own screening, ranking, entry/exit, position sizing, or portfolio policy.

The formula owner is `../indicators/EOD_Indicators_Formula_Spec.md`; orchestration is owned by `../indicators/Indicator_Computation_Specification.md`; null behavior is owned by `../book/Indicator_Nullability_And_OHLCV_Gap_Contract.md`. This registry may name fields and dependencies but may not redefine those rules.

## Artifact-wide binding

Every row is bound to one immutable publication, trade date, listing identity, coherent price product, factor-set revision, indicator-set/formula version, full configuration snapshot/hash, identity/calendar/status/event revisions, source observation lineage, and computation run.

No row may silently read mutable current master data or mix price bases. A compatibility current projection is not the authoritative history.

## Required baseline fields

### Price and range

- `ma20`, `ma50`
- `roc5`, `roc10`, `roc20`
- `hh20`, `ll20`
- `close_to_hh20_pct`, `close_to_ll20_pct`
- `range_20_pct`, `range_position_20_pct`
- `atr14`, `atr14_pct`

These use the coherent `STRUCTURAL_ADJUSTED` OHLC product selected for the run. Provider `adj_close`, mixed adjusted-close/raw-high-low input, and close fallback are prohibited.

### Volume and liquidity

- `vol_ratio_20`
- `adv20_traded_value_idr_actual`, only when source-backed actual traded value is complete for the required window
- `adv20_close_volume_proxy_idr`, explicitly labeled as the `RAW close × RAW volume` proxy

Legacy `dv20_idr`, if temporarily exposed, aliases only `adv20_close_volume_proxy_idr`; it must never be described as actual exchange turnover. Adjusted price multiplied by raw volume is not a valid proxy.

### Context facts

- nullable source-backed `sector_code`
- nullable, version-bound `sector_roc20`, `rs_20_vs_sector`, and `sector_rs_20_vs_ihsg`
- `corporate_action_flag`, action/event revision references, contamination state and reasons
- temporal `trading_status_code`, `is_suspended`, `is_uma`, `event_risk_flag`, and all event-risk reasons

Context fields are facts, not alpha policy. Missing optional sector or benchmark facts do not justify inventing a value or invalidating independent price indicators.

## ATR state and dependency graph

`ATR14` uses Wilder recurrence with one stable seed constructed from the first fourteen valid true ranges after the later of the intentional dataset start and listing start. After seeding, each date consumes the immediately preceding stored ATR state and that date's true range.

- Sliding-window reseeding is prohibited.
- A wider query window is not a substitute for the stable seed/state.
- An unresolved expected-session gap cannot be skipped or forward-filled.
- A corrected historical true range may affect every later ATR value; its impact is recursive and is not capped at fourteen or any other fixed number of sessions.

Fixed-window indicators retain their exact dependency horizons as defined by the formula owner. The registry and implementation must publish a dependency manifest sufficient to compute correction impact.

## Corporate-action and contamination rules

Only verified event revisions and approved factor-set revisions may transform the structural-adjusted product. Price jumps, provider adjustment fields, or synthetic detector candidates may quarantine or contaminate output but cannot activate a factor.

Action timing uses verified exchange-effective/ex-date semantics. Exact-date-only contamination is insufficient: each affected field carries the applicable dependency interval, event revision, factor-set revision, contamination state, and reason set.

Unresolved structural breaks block or null every dependent field rather than mixing incompatible regimes. Raw canonical bars remain unchanged.

## Contamination radius (LOCKED)

`Terminology_and_Scope.md` assigns this contract the duty of publishing the radius as a number. The baseline field set has two radii, not one, and collapsing them into a single figure would understate the worse case.

**Fixed-window radius: 50 trading sessions.** The longest fixed dependency in the baseline is `ma50`, defined over `D[-49]..D`. An undetected structural event at session `T` therefore mislabels every dependent fixed-window value from `T` through `T+49`. Against the declared decision horizon of five trading days, fifty sessions is roughly **ten consecutive decision cycles** — the defect is not diluted by averaging, it is carried by it.

**Recursive radius: unbounded forward.** `ATR14` uses Wilder recurrence from one stable seed, so a wrong true range at session `T` propagates into every later `ATR14` value without limit. This is already stated above as an impact rule; restated here it is a radius, and it is the reason ATR-dependent output cannot be bounded by any window length.

Consequences that bind:

- Impact resolution for a correction at session `T` covers at minimum `T` through `T+49` for fixed-window fields, and the **entire remaining chain** for ATR-derived fields.
- A quarantine that covers only the event date is insufficient by a factor of fifty for fixed-window fields and by an unbounded factor for recursive ones.
- Adding a field with a longer fixed window raises the fixed-window radius and is an output-affecting change under the version rule, not a routine addition.
- Publishing these numbers does not make them safe. It makes the cost of an undetected event explicit, which is the point: at this horizon, a single missed event consumes more decision opportunities than most operators expect.

## Capability boundary (LOCKED)

**What the engine proves.** That each value follows its declared formula exactly; that the ATR chain is stable and reproducible; that warm-up and insufficient history produce declared nulls with reason codes; that **detected** contamination is surfaced on every dependent field.

**What the engine cannot prove.**

- **That the window is free of undetected discontinuity.** Contamination rules act on verified event revisions and detector candidates. An event nobody recorded, and a scale change below the detector's sensitivity floor, leave no trace in the input. The engine computes over the window it is given.
- **That a value is meaningful.** An indicator computed across an unadjusted corporate action is arithmetically correct and semantically wrong. It is non-null, within range, carries no reason code, and participates in the artifact hash like any other value.
- **That the value can be told apart from a real move.** `ROC20` of roughly minus ninety percent across an unadjusted ten-for-one split is numerically indistinguishable from a genuine collapse of the same size. Nothing in the field, its reason set, or its precision reveals which one it is.

### Consequences (LOCKED)

- A non-null indicator value is **not** evidence that its window is clean.
- An empty contamination reason set records what was **detected**, not what **occurred**. It may never be read, exported, or reported as proof of an undisturbed window.
- Evidence that a window is clean must come from event completeness under the corporate-action owner contract, never from the shape, range, or nullability of the computed value.

A twenty-session window under a five-trading-day decision horizon spans roughly four weeks of decisions. One undetected structural event does not corrupt a single number; it mislabels every value that window feeds, and each of them looks ordinary.

## Nullability, precision, and reasons

For every registered field, the versioned registry entry must declare:

- required or optional status;
- dependency fields and window/state rule;
- price/liquidity basis and unit;
- warm-up rule;
- precision, rounding, and serialization rule;
- allowed null reason codes; and
- formula and registry version.

Canonical zero-price placeholders, arbitrary defaults, infinity, and `NaN` are forbidden. Field-level reason sets are retained even when a compatibility primary reason is exposed.

## Artifact hash

Every published field and its semantic annotation participates in the artifact content hash, including reason sets, contamination windows, and `corporate_action_window_reasons`. Excluding a published annotation can make two semantically different artifacts hash-identical and can prevent a hash-gated correction from being promoted.

The canonical hash column order, null encoding, numeric normalization, character encoding, and row ordering are versioned config. A formula-, reason-, annotation-, lineage-, or value-change creates a new immutable artifact/publication version.

## Acceptance proof

Order 15 is implementation-ready only when independent golden fixtures prove at least:

1. a long uninterrupted ATR chain against an external/reference oracle;
2. a later listing and the intentional dataset-start warm-up;
3. a gap that does not silently reseed or skip state;
4. an old true-range correction whose ATR impact propagates beyond fourteen sessions;
5. a verified split with coherent OHLC/volume adjustment;
6. an unverified price break that contaminates but never adjusts;
7. actual traded value and close-volume proxy remain distinct; and
8. deterministic replay produces byte-identical values, reasons, lineage, and hash.

Until these executed fixtures pass against the production path, this document is strategy-locked but the indicator implementation is not production-relocked.
