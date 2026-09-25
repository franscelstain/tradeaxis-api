# Config Change Protocol (LOCKED)

Any output-affecting config change must be treated as a contract change.

## Locked rules
- change must be recorded in the configuration registry with effective date
- reruns must use the registry version effective for the requested trade date or explicitly documented override. Default: a rerun of requested trade date D binds the configuration version effective for D; live, current or latest configuration is never substituted implicitly. Override: an explicitly documented override exists only when a governed correction explicitly requests it and records the identity of the configuration to use and the reason; that correction may be approved by system, but an approval without that request and configuration identity is not an override and permits no substitution. An override run records that it is an override and which configuration was effective for D, so it stays distinguishable from historical-default execution, and it never records or re-stamps the override configuration as effective for D. Failure: when the configuration effective for D is not the live executable configuration and no override exists, the rerun is BLOCKED and is not executed under any other configuration. A configuration's effective date is the effective interval declared in the configuration registry (registry/Platform_Config_Registry_LOCKED.md, Registry metadata), never a date derived from a rerun or backfill requested date.
- if semantics change output, indicator_set_version and/or replay expectations must be updated
- ad-hoc undocumented config tweaks are forbidden for production output
