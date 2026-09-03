# F-MD-B17-A001-001 — Date-level anomaly thresholds cannot be bound to the run configuration snapshot

- Status: `RESOLVED`
- Severity: `P2`
- Stage / Attempt / Baseline / Epoch: `MD-B17` / `MD-B17-A001` / `MD-B17-A001-BL001` / `MD-REBASELINE-20260820-001`
- Owning surface for remediation: `Platform_Config_Registry_LOCKED.md` (`MD-S082`) — current strategy authority; the change requires a controlled correction and is not available to an implementation attempt
- Reviewed decision: `D-MD-B17-A001-001` — `ISSUED`, pending explicit user authorization for the bounded six-key strategy correction
- Blocks at proof-owning stage `MD-B17`: the threshold-binding clause of `MD-S051-R0067` and the date-level rules that depend on it

## Finding

`Run_Status_and_Quality_Gates_LOCKED.md` owns the date-level anomaly checks and states why they are
necessary:

> Row-level validation cannot, by construction, see a pattern across rows. A defect affecting many
> instruments on one acquisition date presents as many individually admissible rows, and every
> per-row rule passes.

It then names three measures — zero-volume share, flat-bar share, cross-field contradiction count —
and attaches a rule to them:

> Thresholds are configured values bound to the run's configuration snapshot, never implicit
> judgement.

`MD-B17-A001` implemented the three measures. `DateLevelAnomalyCheckService` computes each of them,
compares against neighbouring dates resolved through the governed market calendar rather than by
date arithmetic, and returns a state and finding set without altering any row. Eight guards cover
it and all six mutation probes fire.

**The threshold clause is not satisfied, and cannot be satisfied by this attempt.**

The thresholds need six configuration keys under `market_data.quality_gates.date_level_anomaly`.
`PlatformConfigRegistry` admits only keys registered in
`Platform_Config_Registry_LOCKED.md`, and rejected all six the moment they were added:

```
CONFIG_REGISTRY_KEY_MISMATCH: unregistered=market_data.quality_gates.date_level_anomaly.cross_field_contradiction_max, …
```

That registry is `MD-S082`, `CURRENT_STRATEGY_AUTHORITY`, verified byte-for-byte against
`MARKET_DATA_STRATEGY_FREEZE_MANIFEST.json`. Its one prior change went through a controlled
correction (`DOC-CHG-20260822-001` / `D-MD-20260822-06`), not an implementation edit. Registering
six new keys so that this attempt's own output would pass is precisely what the governed workflow
forbids.

## What was done instead

The thresholds are declared as `DateLevelAnomalyCheckService::DECLARED_THRESHOLDS` and every result
carries `date_level_anomaly_threshold_binding = DECLARED_PENDING_CONFIG_REGISTRATION`, so no reader
can mistake a declared constant for a configuration-snapshot-bound value. A guard asserts that
state, and asserts the keys are genuinely still unregistered — so the day they are registered, the
guard fails and the deferral ends rather than persisting unnoticed.

The alternative — hard-coding the thresholds silently — would have satisfied the measurement and
quietly contradicted the sentence that governs it. The contract's own words rule that out: *never
implicit judgement*.

## Consequence

The measurement half of the date-level checks is implemented and proven. The threshold half is not,
so the predicates that depend on it remain `NOT_ASSESSED` and **`MD-B17` cannot close on this
attempt.** A stage closure asserting 246/246 while this clause is unmet would be a false claim, and
`STAGE_CLOSURE_MANIFEST_STANDARD.md` forbids it.

## Remediation

A controlled correction to `Platform_Config_Registry_LOCKED.md` registers:

- `market_data.quality_gates.date_level_anomaly.zero_volume_share_max`
- `market_data.quality_gates.date_level_anomaly.flat_bar_share_max`
- `market_data.quality_gates.date_level_anomaly.cross_field_contradiction_max`
- `market_data.quality_gates.date_level_anomaly.neighbour_trading_days`
- `market_data.quality_gates.date_level_anomaly.neighbour_elevation_factor`
- `market_data.quality_gates.date_level_anomaly.minimum_rows`

`config/market_data.php` then declares them, `DateLevelAnomalyCheckService` reads them through
`config()` instead of `DECLARED_THRESHOLDS`, and `THRESHOLD_BINDING_STATE` becomes
`CONFIG_SNAPSHOT_BOUND`. The existing guard already asserts the binding state and the registry
contents, so it will hold the change to the same standard without modification — and will fail until
the service is switched over, which is the intended order.

Related: [[F-MD-B14-A001-001]] records the same shape — an implementation blocked from completing a
contract clause because the vocabulary or registry it needs is strategy-owned.

## Change-control state

`D-MD-B17-A001-001` preserves `MD-S051` and selects a bounded additive correction: exactly the six
keys above, with the declared A001 values made explicit as defaults and with typed environment
inputs captured by the immutable run configuration snapshot.

## Resolution — 2026-09-03

The user explicitly authorised the bounded decision. `DOC-CHG-20260903-001` adds exactly the six
resolved-key rows to `MD-S082`, successor freeze `MD-STRATEGY-FREEZE-20260903-001` passes the
strategy integrity check, and `E-MD-B17-A001-002` records the authorization and non-PASS A001
disposition. The authority-completeness finding is therefore resolved.

Resolution does not prove the runtime binding and does not close B17. `MD-B17-A001` remains
`PARTIAL_REBASELINE_REQUIRED`; `MD-B17-A002` must bind the values to the run configuration snapshot,
revalidate the affected B04 configuration invariants, freshly prove all 246 B17 predicates, and
close only through the normal evidence and gate chain.
