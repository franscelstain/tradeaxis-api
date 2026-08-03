# Scheduling and Locking Contract (STRATEGY LOCKED)

## Daily target

After the configured cutoff and verified completion of the latest IDX Regular-Market session, the scheduler targets exactly `latest_expected_trade_date` from the versioned calendar/status model. It never derives the target from wall-clock date or `MAX(trade_date)`.

The scheduled workflow is one observable chain: acquire immutable source observations, validate/normalize/canonicalize, build coherent price products and indicators, compute coverage/quality/eligibility, validate manifest/config/lineage, seal a candidate, atomically activate it, and evaluate freshness.

## Idempotency and locks

The logical key includes market, source/product, requested trade date, run type, and configuration context. Concurrent jobs for the same logical target cannot both publish. A retry either resumes from a durable checkpoint or creates a new attempt linked to its predecessor; it does not duplicate canonical facts, overwrite observations, or mutate published artifacts.

Locks have owner identity, acquired/heartbeat/expiry timestamps, fencing token, and audited release/steal behavior. Expiry alone does not prove the old worker stopped: every state-changing write validates the current fencing token.

## Scheduler outcomes

The scheduler records `SUCCESS_READABLE`, `SUCCESS_HELD`, `FAILED`, `SKIPPED_ALREADY_READABLE`, or `SKIPPED_LOCKED`, plus run/publication IDs, requested/latest-expected/latest-readable dates, reason set, and next action. Process exit success alone is not a readable-data claim.

## Activation boundary

Before `OPERATIONAL_START_DATE`, due-run gaps are development frontier and do not create production incidents, although candidates still must obey all integrity rules. Activation requires deployed scheduler/cron proof, notification routing, credentials, writable evidence/log paths, stale monitoring, runbooks, and consecutive-session rehearsal.

After activation, missing/late due runs are freshness incidents governed by SLO and alert policy. Scheduler proof with a manual fixture is not provider-production proof.

## Prohibited scheduling behavior

- overlapping unfenced promotion;
- automatic price repair, factor verification, or force content mutation;
- treating a prior readable date as success for the requested date;
- shrinking coverage for provider failures/dormancy;
- retry loops that discard the original failed observation/evidence; and
- marking a date complete before the active sealed read product is verifiably materializable.
