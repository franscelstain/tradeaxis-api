# Indicator Test Vectors (STRATEGY LOCKED)

## Price basis

Every price/range/ATR vector uses a coherent `STRUCTURAL_ADJUSTED` OHLC set produced from verified factor revisions. No vector may select provider `adj_close` with raw high/low/open or fall back between bases by row/date. RAW remains separately asserted unchanged.

## ATR14 vectors

### Seed

Starting at the later of `2023-01-02` and listing start, calculate true range for consecutive expected sessions. `ATR14` first exists at the fourteenth valid TR and equals the arithmetic mean of those fourteen TR values under locked precision.

### Recurrence

For every later consecutive expected session:

`ATR_t = ((ATR_{t-1} × 13) + TR_t) / 14`

The fixture contains at least 100 post-seed rows and expected values from an independent implementation. A sliding 14/15-row reseed is asserted unequal after the chain diverges.

### Gap and correction

An unresolved expected-session gap produces a reason-coded null/hold and does not skip/reseed. Correcting an old TR creates a new publication/state chain and asserts impact on a date more than fourteen sessions later.

## Fixed-window vectors

- ROC5/10/20 compare D with the exact trading-session lag.
- MA20/50 use exact inclusive valid windows.
- HH/LL/range20 use D[-19]…D coherent high/low.
- volume ratio uses D volume divided by prior 20 sessions excluding D, with zero-denominator reason.
- actual ADV20 requires complete source-backed actual traded value.
- proxy ADV20 averages `RAW close × RAW volume`; adjusted price×raw volume is rejected.

## Nullability and context

Later listing/dataset start produces normal warm-up null reasons. Missing optional sector/benchmark context nulls only dependent fields. Unverified structural breaks contaminate/null affected fields but never generate adjusted values. Every vector asserts field reasons, factor/formula/config/observation lineage, precision, and artifact hash.

## Acceptance

Short synthetic vectors are unit checks only. Closure needs the long-chain independent oracle, verified real structural action, unverified real discontinuity, and deterministic end-to-end publication fixture.
