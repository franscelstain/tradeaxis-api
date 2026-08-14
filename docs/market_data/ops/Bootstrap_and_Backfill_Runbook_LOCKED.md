# Bootstrap and Backfill Runbook (STRATEGY LOCKED)

## Scope

Bootstrap starts at the intentional dataset boundary `2023-01-02` or a later listing start. Backfill fills explicitly declared missing/invalid ranges. Neither operation implies that pre-boundary history is required for current scope.

Yahoo Finance remains the bootstrap acquisition source under its source strategy; a future paid source requires a separate adapter/observation/config decision, not a hidden current backlog.

## Planning

Freeze requested range, temporal universe/listings and provider mappings, calendar/status revisions, source adapter/schema, full config, rate/request-window limits, price product/formulas, and expected output counts/states. Partition by provider-safe windows while retaining listing/date identity.

Indicator dependency loading is expressed in expected trading sessions. Stable recursive ATR state begins from its defined dataset/listing seed and must not be reseeded per chunk.

## Execution

- acquire immutable observations with checkpoints and bounded retry;
- resume only completed durable units and never overwrite earlier observations;
- validate/canonicalize each date under the same rules as daily runs;
- build immutable candidates in dependency order;
- record unknown expectation/provider missing separately from verified not-expected;
- promote only complete validated publications through the normal seal/pointer path.

## Safety

Dry-run reports dates/listings/observations/publications affected. Backfill cannot auto-create verified actions from price jumps, apply price-scale repair, mutate sealed history, or force-promote incomplete/configless candidates. Corrections discovered during backfill follow the correction/reseal lifecycle with distinct revisions.

## Completion evidence

Per range/date retain plan/config/adapter hashes, observations and outcomes, checkpoints, expected/delivered/quality counts, product/indicator proofs, publications/seals, gaps/reasons, and consumer-gateway verification. A range is not complete merely because every request was attempted.
