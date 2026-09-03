# Decision — register date-level anomaly thresholds in the run configuration snapshot

- ID: `D-MD-B17-A001-001`
- Verification epoch: `MD-REBASELINE-20260820-001`
- Stage / Attempt / Baseline reviewed: `MD-B17` / `MD-B17-A001` / `MD-B17-A001-BL001`
- Finding: `F-MD-B17-A001-001`
- Supporting evidence: `E-MD-B17-A001-001`
- Blocked rule: `MD-S051-R0070`
- Issued: 2026-09-01
- Decision status: `ISSUED — PENDING EXPLICIT USER AUTHORIZATION FOR STRATEGY REVISION`
- Strategy impact if authorised: `CONTROLLED_CORRECTION` limited to six additive resolved-key rows in `MD-S082`

## Question

`MD-S051-R0070` requires the date-level anomaly thresholds to be configured values bound to the
run's configuration snapshot. `MD-S082` owns the exhaustive resolved-key register and declares a
runtime key absent from that register to be a sealing error. The six keys needed by the three
already-required anomaly measures are absent, so the two authorities cannot currently be satisfied
together.

The review must decide whether to weaken the snapshot-binding clause, leave the date-level checks
permanently unbound, or complete the configuration registry without changing the anomaly semantics.

## Authority review

1. `MD-S051` unambiguously requires three date-level measures and configuration-snapshot-bound
   thresholds; the requirement must remain byte-identical.
2. `MD-S082-R0020`, `MD-S082-R0034`, and `MD-S082-R0066` require applicable quality rules and every
   runtime key to be present in the resolved snapshot and exhaustive key register.
3. `PlatformConfigRegistry` correctly rejects an application-only key that is absent from
   `MD-S082`; bypassing that rejection would weaken configuration identity and sealing safety.
4. `MD-B17-A001` proves the measurement behavior with explicit declared thresholds, reports the
   binding state as `DECLARED_PENDING_CONFIG_REGISTRATION`, and withholds `MD-S051-R0070`. It does
   not claim the declared constants are configuration-bound.
5. The defect is therefore an incomplete registry representation of an existing locked quality
   rule, not an implementation failure that can be repaired below authority and not a reason to
   weaken either owner contract.

## Decision

If explicitly authorised by the user:

1. Preserve `MD-S051` byte-for-byte and preserve its anomaly-measurement and non-destructive-finding
   semantics.
2. Add exactly these six rows to the `MD-S082` resolved-key register:

   | Key | Type | Default | Environment input |
   |---|---|---:|---|
   | `market_data.quality_gates.date_level_anomaly.zero_volume_share_max` | float | `0.30` | `MARKET_DATA_DATE_LEVEL_ANOMALY_ZERO_VOLUME_SHARE_MAX` |
   | `market_data.quality_gates.date_level_anomaly.flat_bar_share_max` | float | `0.20` | `MARKET_DATA_DATE_LEVEL_ANOMALY_FLAT_BAR_SHARE_MAX` |
   | `market_data.quality_gates.date_level_anomaly.cross_field_contradiction_max` | int | `0` | `MARKET_DATA_DATE_LEVEL_ANOMALY_CROSS_FIELD_CONTRADICTION_MAX` |
   | `market_data.quality_gates.date_level_anomaly.neighbour_trading_days` | int | `5` | `MARKET_DATA_DATE_LEVEL_ANOMALY_NEIGHBOUR_TRADING_DAYS` |
   | `market_data.quality_gates.date_level_anomaly.neighbour_elevation_factor` | float | `2.0` | `MARKET_DATA_DATE_LEVEL_ANOMALY_NEIGHBOUR_ELEVATION_FACTOR` |
   | `market_data.quality_gates.date_level_anomaly.minimum_rows` | int | `20` | `MARKET_DATA_DATE_LEVEL_ANOMALY_MINIMUM_ROWS` |

   Every row is owned by `../book/Run_Status_and_Quality_Gates_LOCKED.md`.
3. No other strategy row, threshold, quality state, readiness rule, or publishability rule is
   authorised to change. The correction may not turn a finding into row deletion or mutation.
4. Issue a change-log entry and successor strategy freeze; retain the predecessor freeze and every
   A001 baseline/evidence byte unchanged.
5. Resume executable work only through a successor B17 attempt/baseline/Change Impact Declaration.
   The service must read the six resolved values from configuration, the values must be captured by
   the run's immutable configuration snapshot, and malformed or missing values must fail closed.
6. Re-run the complete B17 predicate proof under the successor baseline. A001 contributes lineage
   and reusable guards, not an inherited verdict or inherited predicate satisfaction.

## Authorization state

**Explicit user authorization has not yet been given.** This decision does **not** authorise editing
`authority/strategy/registry/Platform_Config_Registry_LOCKED.md`, the strategy freeze manifest, the
strategy change log, runtime configuration, or the anomaly service. `F-MD-B17-A001-001` remains
`OPEN`; `MD-B17` remains `IN_PROGRESS / PARTIAL` at 245/246.

The only authorised next action after this issued decision is to wait for explicit user approval of
the bounded correction above.

## Invalidation / revalidation impact if authorised

- Strategy byte scope: six additive resolved-key rows in `MD-S082`; `MD-S051` and the other 90
  strategy documents remain byte-identical.
- Current A001 disposition: `MD-B17-A001-BL001` and `E-MD-B17-A001-001` remain immutable partial
  records under `MD-STRATEGY-FREEZE-20260823-001`; A001 never becomes PASS retroactively.
- Successor B17 work: new baseline and Change Impact Declaration, actual config/snapshot/service
  binding, positive and fail-closed tests, fresh 246-predicate proof, traceability binding, residue,
  governed evidence, integrity gates, finding resolution, and closure.
- Affected predecessor proof: the correction changes the exhaustive `MD-S082` key population.
  Affected-proof analysis must therefore revalidate at least the B04 configuration-registry and
  snapshot invariants (`MD-S082-R0001`, `R0020`, `R0034`, and `R0066`) under the successor freeze.
  Whether this is admitted as explicit affected-predecessor proof in the successor attempt or
  requires formal B04 re-entry is decided by the current gates; it may not be assumed away.
- No B00-B16 closure is rewritten merely because the freeze identity changes. Any proof found to
  depend materially on the old exhaustive key set must be revalidated and explicitly correlated.

## Scope limit

This decision does not authorise tuning any value beyond the six defaults above, adding another
configuration key, changing `MD-S051`, bypassing `PlatformConfigRegistry`, editing A001 evidence, or
entering `MD-B18` before `MD-B17` has a valid closure.
