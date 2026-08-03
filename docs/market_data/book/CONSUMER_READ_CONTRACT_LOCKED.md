# Consumer Read Contract (STRATEGY LOCKED)

## Allowed input surface

All downstream consumers, including the initial Weekly Swing profile, read only the versioned market-data read model defined by `Downstream_Consumer_Read_Model_Contract_LOCKED.md` and its readiness metadata. Internal acquisition, canonical, history, candidate, current-projection, event, factor, indicator, eligibility, and seal tables are not consumer APIs.

This contract proves safe data delivery. It does not require or validate consumer screening, ranking, signals, portfolio behavior, or trading performance.

## Request

A request declares at minimum market/product/read-model version and either an explicit requested trade date or `latest_expected_completed_session`. Optional audit/replay modes are separate endpoints and cannot be selected implicitly.

## Resolution order

The read gateway must:

1. resolve requested date from the versioned Regular-Market calendar and session-completion rule;
2. resolve the active publication pointer for that date/product;
3. verify publication state, seal/manifest/config, and minimum product completeness;
4. atomically materialize every field from the same immutable publication;
5. evaluate freshness relative to latest expected date and activation context; and
6. if policy permits, resolve a prior active publication and label its true effective date and staleness.

Failure at any step is reason-coded and fail-closed. A repository must not continue by selecting convenient rows from another table/date/publication.

## Response invariant

Every success-like response includes publication ID/version, requested/effective dates, readiness/freshness states, evaluated-at time, config/factor/formula/read-model versions, and lineage reference. Row filters/pagination remain bound to those values.

A stale/degraded response is not equivalent to a fresh response. `200 OK`, non-empty rows, `eligible = 1`, or job completion cannot erase the state.

## Corrections and repeatability

An exact `publication_id` request is repeatable and returns that immutable artifact or an explicit unavailable/integrity error. A date-current request may resolve a newer corrected active publication on a later call and therefore exposes its publication identity.

Consumers must not cache across publication versions without including publication ID/read-model version in the cache key.

## Forbidden behavior

- direct `MAX(trade_date)` or latest-row queries;
- current-master or current-status fallback for historical rows;
- implicit adjusted-close/close selection;
- client-side adjustment, indicator, coverage, or eligibility calculation;
- reading candidates or superseded artifacts through the normal endpoint;
- mixing requested-date labels with prior-date data;
- substituting empty/zero defaults for missing required facts; and
- bypassing readiness because a lower-level table is populated.

## Enforcement

Application repositories expose the gateway DTO, database privileges/views deny consumer roles direct internal-table access, and static/integration tests reject bypass patterns. Audit endpoints require explicit authorization and clearly identify non-current or unsealed state.

Production lock requires executed enforcement evidence; the document alone does not prove it.
