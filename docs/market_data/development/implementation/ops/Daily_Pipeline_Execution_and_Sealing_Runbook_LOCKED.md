# Daily Pipeline Execution and Sealing Runbook (STRATEGY LOCKED; ACTIVATION NOT YET PROVEN)

## Preflight

Confirm operational activation context, provider credential profile/entitlement, calendar and session completion, temporal universe/provider mappings, full config snapshot, schema/adapter versions, free storage, database health, notification routing, and absence/ownership of a conflicting fenced lock.

Resolve and display requested date plus latest expected/acquired/canonicalized/readable dates before any write.

## Execution order

1. Create a run/attempt and immutable full config snapshot.
2. Acquire source responses into immutable observation envelopes; retain failed/stale/schema-invalid outcomes and provenance.
3. Validate requested-date alignment, timestamps, schema, units, duplicates, and positive-price invariants.
4. Normalize provider-neutral rows and canonicalize accepted `RAW` facts without overwriting history.
5. Resolve point-in-time identity, calendar/session, status, event, and factor revisions.
6. Build one coherent `STRUCTURAL_ADJUSTED` analytical product; quarantine unresolved breaks and never auto-verify synthetic actions.
7. Compute daily metrics, indicators, and eligibility with versioned formulas/reasons.
8. Evaluate expectation, delivery, quality, and product gates separately.
9. Build immutable publication artifacts/manifest; verify config, lineage, content hashes, and minimum read DTO.
10. Seal and atomically activate the candidate only if all required gates pass.
11. Re-read through the consumer gateway, record freshness/readiness, export evidence, and emit alerts.

## Failure behavior

On source, validation, coverage, factor, indicator, config, hash, or seal failure, preserve the prior active publication and mark the requested run `HELD` or `FAILED` with all reasons. No partial candidate is consumer-readable.

Retry is bounded, reason-aware, and idempotent. A refetch creates a new linked observation. Backfill and correction use explicit modes; they do not turn the daily command into a history-rewrite path.

## Post-run checks

- exactly one active pointer per product/date scope;
- gateway publication/config/factor/formula IDs match manifest and seal;
- requested/effective date and freshness are truthful;
- coverage denominator includes expected plus unknown and excludes only verified not-expected;
- no unsealed/mixed/current-master fallback is visible;
- alerts/evidence contain run, attempt, observation, candidate, publication, and prior-pointer context.

## Operational proof gate

Do not label this runbook production ready until deployed scheduled executions, provider path, locks, notifications, stale detection, failure/retry, and gateway checks have passed across consecutive expected trading sessions after an approved activation rehearsal.
