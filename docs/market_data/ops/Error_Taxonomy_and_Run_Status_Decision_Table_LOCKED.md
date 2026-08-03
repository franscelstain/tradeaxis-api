# Error Taxonomy and Run-Status Decision Table (STRATEGY LOCKED)

## Independent dimensions

A run records independent dimensions rather than compressing meaning into one status:

- acquisition outcome: complete/partial/failed/stale/schema-invalid;
- expectation and delivery outcome;
- canonical quality outcome;
- analytical product/factor/contamination outcome;
- indicator/daily-metric/eligibility outcome;
- config/lineage/hash/manifest/seal integrity outcome;
- publication/pointer/read-model outcome; and
- freshness outcome relative to activation/latest expected date.

All reason codes are retained. A primary reason is routing compatibility only.

## Terminal run status

| Condition | Run status | Publication/read state |
|---|---|---|
| all required gates pass and active sealed DTO verifies | `SUCCEEDED` | `READABLE` |
| deterministic data/product/gate condition prevents publication | `HELD` | previous explicit result or `NOT_AVAILABLE` |
| execution cannot complete | `FAILED` | previous explicit result or `NOT_AVAILABLE` |
| work remains in progress | `RUNNING` | candidate never readable |
| duplicate target already has verified active publication | `SKIPPED` | existing publication unchanged |
| lock owned by another fenced worker | `BLOCKED`/`SKIPPED_LOCKED` | unchanged |

`SUCCEEDED` without verified active publication/read DTO is invalid. A held run is not a failed process, and neither may claim the requested date readable.

## Required reason families

Source/transport, stale date, schema, identity/mapping, calendar/session/status, expectation/delivery, invalid OHLCV/duplicate, coverage/quality, action verification/factor/contamination, indicator/warm-up/dependency, config/lineage/hash/seal, lock/concurrency, publication/pointer/read-model, freshness, security, and proof/replay.

## Retry classification

Only explicitly retryable transport/rate-limit/transient infrastructure failures are automatically retried. Deterministic validation, unknown expectation, mapping ambiguity, unverified actions, gate failures, and integrity mismatches require changed evidence/config/code and a new attempt/candidate. No retry mutates the failed observation or published state.
