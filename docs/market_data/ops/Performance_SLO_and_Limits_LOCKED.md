# Performance, Freshness, and Capacity SLO (STRATEGY LOCKED)

## Measurement boundary

Production SLO measurement begins only at approved `OPERATIONAL_START_DATE`. Earlier dates are development/backfill frontier and are reported separately, not counted as production incidents or availability success.

## Freshness clock

For each completed expected Regular-Market session measure from configured cutoff/session completion to:

- first acquisition attempt;
- immutable observation completion;
- valid canonical completion;
- candidate seal;
- active publication readability; and
- consumer-gateway verification.

The primary freshness indicator is time to active readable publication for `latest_expected_trade_date`, not process exit or newest stored row. Report latest expected/acquired/canonicalized/readable dates and lag in expected sessions.

## SLO registry

Numeric targets are environment/capacity decisions stored in the full effective-dated config snapshot, with alert and error-budget policy. The registry covers due-run start delay, publication latency, stale age, provider/schema failure rate, observation/canonical rejection rate, coverage/quality holds, lock contention, retry exhaustion, gateway latency/error rate, replay mismatch, and evidence-export lag.

No document may claim a numeric SLO is met without an observation window, population, activation context, and retained measurement evidence.

## Capacity and limits

Rate limits, request windows, concurrency, timeouts, batch sizes, lock TTL/heartbeat, retention, and backfill throughput are versioned configuration. Capacity controls may slow/hold output but cannot skip expected listings, shrink the denominator, drop provenance, mix price bases, or bypass gates.

## Alert states

- warning before the freshness deadline when progress is behind;
- critical when the activated latest expected date is unreadable beyond the configured bound;
- immediate integrity alert for pointer/seal/hash/config ambiguity, history mutation attempt, or consumer bypass;
- degraded alert for allowed explicitly labeled service degradation.

Alerts deduplicate by run/date/root cause but retain every affected date and observation. Recovery requires gateway-confirmed readable state or an explicit accepted incident state, not merely a later successful job.
