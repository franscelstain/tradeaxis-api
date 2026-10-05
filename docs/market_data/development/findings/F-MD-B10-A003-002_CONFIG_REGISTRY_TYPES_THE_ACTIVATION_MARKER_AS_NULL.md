# Finding — the configuration registry types the operational activation marker as `null`, so an activated run cannot be created

- ID: `F-MD-B10-A003-002`
- Stage / Attempt / Baseline / Epoch: `MD-B10` / `MD-B10-A003` / `MD-B10-A003-BL001` / `MD-REBASELINE-20260820-001`
- Raised: 2026-10-05T15:13:51+07:00
- Severity: `P3` — no effect on `MD-B10-A003` or candidate-v4; recorded because it bounds what "activated publications continue through existing rules" can mean today
- Status: `OPEN` — observation recorded; not remediated, not owned by `MD-B10`
- Class: `AUTHORITY_CONFIGURATION_OBSERVATION`
- Related: `F-MD-B18-A002-033` (row 7, activated world), `E-MD-B10-A003-002`, `MD-S056-R0045`
- Remediation owner: the stage that sets `OPERATIONAL_START_DATE` (`MD-B19`/`MD-B22` activation gates); no owner decision is needed to record it

## Observed

`Platform_Config_Registry_LOCKED.md` line 129 registers `market_data.scope.operational_start_date` with type `null`, default `null`, environment input `MARKET_DATA_OPERATIONAL_START_DATE`. `PlatformConfigRegistry` checks the resolved configuration against the registered type every time a run resolves its configuration snapshot. While writing the run-creation tests of `MD-B10-A003` a configured marker string was refused:

`CONFIG_REGISTRY_TYPE_MISMATCH: market_data.scope.operational_start_date expected=null actual=string`

(`PreActivationFreshnessRunLabelTest::test_the_configuration_registry_still_refuses_a_configured_marker_so_the_activated_branch_is_not_reachable_here` pins the fact.) Consequences:

- no run can be created with an operational marker through configuration in this build; every run so far has `operational_start_date` null, consistent with the development state;
- the activated branch of the two configuration-driven run creators (new run, as-known replay) is unreachable; it is covered by the domain rule (`FreshnessState`) and by the promote creator, which takes the marker from its seed run;
- the corrected freshness authority is unaffected: it defines what happens when a marker is effective, and `FreshnessState::isInForce` implements that rule independently of how the marker is configured.

## Not decided here

Whether the registry type should become `date|null` is an authority question (a strategy change under `DOCUMENT_CHANGE_POLICY.md`), together with the freshness evaluator that activation needs (`F-MD-B18-A002-033` row 7). Neither is part of the freshness correction, and neither blocks candidate-v4, which derives a pre-activation world.

## Closure

Resolved when the activation work (`MD-B19`/`MD-B22`) either changes the registered type through a controlled revision or records why the marker is set another way, and the activated-world creators are proven.
