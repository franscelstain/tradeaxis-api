# Test Coverage Closure Contract (STRATEGY LOCKED)

## Allowed states

- `PROVEN`: required positive/negative/real-market oracle executes on the production path and admitted evidence passes.
- `PARTIAL`: some layers execute, but an oracle/runtime/database/concurrency/operations layer is missing.
- `BLOCKED`: prerequisites prevent execution.
- `NOT_IMPLEMENTED`: test/fixture or behavior does not exist.
- `SUPERSEDED`: proves a rule rejected by the current strategy.

Only `PROVEN` closes an item. `BLOCKED`, historical green, mock-only, schema-presence-only, command-exit-only, and copied implementation snapshots do not.

## Closure row

Each invariant records owner contract/version, risk/P0-P1 mapping, fixture/oracle, positive and negative tests, production code path, MariaDB/SQLite/runtime scope, last execution timestamp/build, evidence artifact/hash, state, and open gap.

## Release gate

Every P0/P1 invariant must be `PROVEN`, the full supported suite must pass without superseded expectations, and consecutive activated trading-session operational evidence must exist before order 22 may relock production.

Current V2 schema-mirror proof is `PARTIAL`; it establishes structure only. Strategy behavior and operational proof remain open.
