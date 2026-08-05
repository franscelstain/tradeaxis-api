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

## Horizon-derived targets (LOCKED)

`../book/Terminology_and_Scope.md` states that the consumer horizon generates a freshness requirement and assigns the value to this contract. `Yahoo_Finance_Bootstrap_Source_Strategy.md` assigns the availability-trigger threshold here as well. Both are derived from the horizon rather than chosen, so that a change in horizon forces a change here rather than silently invalidating these numbers.

The declared decision horizon is **5 trading sessions**. Lag is measured in expected sessions, never in calendar days.

### Freshness target

| Lag for `latest_expected_trade_date` | State | Reasoning |
|---|---|---|
| readable within the same session's cutoff window | `ON_TARGET` | the full horizon remains available |
| readable by the next expected session | `ACCEPTABLE` | one session consumes one fifth of the horizon |
| not readable by the second expected session | **`BREACH`** | two sessions consume two fifths; what remains is no longer the horizon the consumer profile declares |

`BREACH` is a breach of the consumer contract, not a degraded convenience. It is reported as such regardless of whether any individual component reported an error, because the platform can be entirely healthy and still miss the window it exists to serve.

### Availability trigger threshold

Acquisition failure for **5 consecutive expected sessions** — one full decision horizon — converts an operational incident into evidence that source capability is a constraint on the platform's purpose, and opens the future-source evaluation described in the bootstrap source strategy.

The unit is deliberate. A source that cannot deliver across one whole horizon has failed at the thing the horizon exists for, and a shorter threshold would fire on ordinary transient outages while a longer one would let an unusable source persist for multiple decision cycles.

Opening that evaluation is not a decision to change source, and reaching the threshold does not authorise migration. It removes the option of waiting one more day indefinitely, which is what an unthresholded trigger permits.

### Rules binding both

- Both thresholds apply only from approved `OPERATIONAL_START_DATE`, consistent with the measurement boundary above.
- Both are expressed in **expected sessions** resolved from the governed calendar. Neither may be restated in calendar days.
- A change to the declared horizon is an output-affecting change that requires re-deriving both values here.
- Neither threshold may be relaxed to convert a breach into a pass. Where a breach is accepted, it is recorded as an accepted breach with a reason, not reclassified.

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

## Capability boundary (LOCKED)

**What SLO measurement proves.** That lag from session completion to readable publication was measured in expected sessions against declared targets, and that a breach is reported as a breach rather than absorbed.

**What it cannot prove.**

- **That on-target data is correct data.** Timeliness and correctness are independent. Arriving inside the window says nothing about what arrived.
- **That the clock started at the right moment.** Measurement begins at session completion, which is a calendar and status fact. A wrong session boundary produces a precise measurement of the wrong interval.
- **That no breach occurred while measurement was off.** Targets apply only from approved activation. Development-phase lateness is outside the SLO by design, so an absence of breaches before activation carries no operational meaning.

Consequently an on-target SLO may be cited as evidence that **published data met its declared timeliness**, never as evidence that **the platform is operating correctly**.
