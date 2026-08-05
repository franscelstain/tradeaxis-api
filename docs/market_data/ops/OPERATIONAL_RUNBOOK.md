# Market Data Operational Runbook

Status: **strategy corrected; production activation/relock not yet proven**.

This is the operational entry point. It must be read with the owner contracts for acquisition, temporal identity/calendar/status, immutable publication/correction, configuration snapshots, readiness, replay, and command safety. Earlier green tests or operator-local command demonstrations prove only the behavior they executed; they do not override open semantic gaps in the current audit.

## Capability boundary scope for ops documents (LOCKED)

Global gate 11 applies to documents owning a mechanism that produces a verdict, state, flag, or signal. Most documents in this directory do not: runbooks describe operator procedure, command documents describe invocation, format and schema documents describe artifact shape, and inventories record what exists. Adding a generic boundary to each would satisfy a mechanical check while teaching nothing, which the gate itself forbids.

**Out of gate 11 scope as a class:** operational runbooks including this one, `commands/*`, `IMPLEMENTATION_GUIDE.md`, `Operator_Decision_Trees_LOCKED.md`, `Audit_Query_Cookbook_LOCKED.md`, artifact/format/logging schema documents, evidence-pack shape contracts, and all `*_INVENTORY.md`.

**In gate 11 scope, and each states its own boundary:** `Performance_SLO_and_Limits_LOCKED.md`, `Incident_Classification_and_Response_Matrix_LOCKED.md`, `Executed_Run_Admission_Criteria_LOCKED.md`, `Release_Gates_LOCKED.md`, and `Scheduling_and_Locking_Contract_LOCKED.md`.

A document moving from describing procedure to producing a verdict moves into scope and must state its boundary at that point.

## Operating modes

- **Development frontier:** before `OPERATIONAL_START_DATE`; missing future daily runs are planned incompleteness, not incidents. Integrity rules still apply and no output may falsely claim freshness/readability.
- **Bootstrap/backfill:** explicit bounded historical work beginning no earlier than intentional dataset start `2023-01-02`, with resumable checkpoints and normal publication gates.
- **Activated daily operation:** scheduled acquisition-to-promotion for each latest completed expected IDX Regular-Market session, with SLO, stale monitoring, alerts, and incident response.
- **Correction/replay:** explicit publication-bound workflows; never an implicit daily repair mode.

## Activation checklist

Activation requires an approved date and environment plus evidence for:

- deployed scheduler/cron and due-run discovery;
- provider credential profile, entitlement, rate limits, adapter/schema, and safe smoke result;
- complete temporal identity/mapping, calendar/session, and trading-status sources;
- full config snapshots and non-null run/publication/seal bindings;
- immutable observation, canonical history, event/factor revision, publication, and pointer schema;
- disabled direct repair/mutation paths and enforced consumer gateway;
- locks/fencing, bounded retry, quarantine, backfill, correction, and rollback-by-pointer;
- freshness/readiness monitoring and notification delivery;
- storage/retention/backup/restore/evidence capacity; and
- consecutive expected-session rehearsals including failure and recovery.

Missing activation evidence means “not activated,” not a waived prerequisite.

### Ops environment baseline gate

Before any targeted proof, full-suite proof, scheduler rehearsal, or activation claim, execute and retain the gate defined in `docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md`. PHP must be `>= 7.3` and `< 8.4`. Treat `ENV_UNSUPPORTED_PHP_VERSION` and `BLOCKED_CONTAINER_RUNTIME_ENV` as blocking outcomes; do not suppress or relabel them as application failures. The static contract is guarded by `OpsEnvironmentBaselineStaticGuardTest.php`.

## Daily operator flow

1. Compare latest expected, acquired, canonicalized, and readable dates.
2. Inspect scheduler outcome, lock owner/fencing token, source/schema/status, and config snapshot.
3. Run or observe the documented daily pipeline; do not improvise internal-table writes.
4. If readable, verify through the consumer gateway and archive evidence.
5. If held/failed/stale, preserve the previous pointer, classify all reasons, alert according to activation/SLO context, and follow the failure playbook.
6. Retry only retryable acquisition/runtime failures. Use explicit backfill/correction workflows for historical gaps or fact revisions.

## Absolute safety rules

- no in-place update/delete of observations, canonical snapshots, analytical artifacts, factors, eligibility, manifests, seals, or published history;
- no automatic price scaling/repair or synthetic corporate-action verification;
- no manual/force bypass of complete candidate validation;
- no coverage denominator exclusion for provider absence, dormancy, zero volume, or current inactivity;
- no unsealed/current-table/latest-date consumer fallback;
- no secret material in logs, observations, manifests, or evidence;
- no incident closure without gateway state and evidence matching the claimed outcome.

## Evidence per operation

Retain command/build identity, actor/environment, timestamps, run/attempt/checkpoint/lock IDs, requested and latest-state dates, config/adapter/schema IDs/hashes, observation outcomes, coverage/quality/product/indicator/eligibility reasons, candidate/publication/pointer/seal hashes, alerts, and final consumer-gateway verification.

## Supporting documents

- `Scheduling_and_Locking_Contract_LOCKED.md`
- `Daily_Pipeline_Execution_and_Sealing_Runbook_LOCKED.md`
- `Bootstrap_and_Backfill_Runbook_LOCKED.md`
- `Historical_Correction_Runbook_LOCKED.md`
- `Failure_Playbook_LOCKED.md`
- `Incident_Classification_and_Response_Matrix_LOCKED.md`
- `Performance_SLO_and_Limits_LOCKED.md`
- `Commands_and_Runbook_LOCKED.md`

Production readiness may be restored only by the order-22 re-audit after schema/code/test/operational proof closes every P0/P1 finding.

## OPERATIONAL_READINESS_CONTRACT compatibility operator appendix

This appendix preserves discoverable command/runbook vocabulary while the V2 gateway and commands are implemented. It does not upgrade the status above.

### Daily operational flow

The **Coverage, hash, seal, finalize, pointer sequence** is observation/canonical/product gates → coverage/quality → all artifact and manifest hashes → seal → finalize → atomic pointer → gateway check. Valid terminal summaries are `SUCCESS / READABLE`, `SUCCESS / NOT_READABLE`, `HELD / NOT_READABLE`, and `FAILED / NOT_READABLE`. Invalid input or proof is `status=BLOCKED`.

Always record `terminal_status`, `publishability_state`, `reason_code`, and `final_reason_code`, then choose the documented **next action**. **Stop** when integrity/lineage is ambiguous and **preserve pointer** to the last verified publication.

### Manual file import-only flow

`manual_file` with `request_mode=import_only` must end with `promote_status=NOT_PROMOTED`, `promoted=false`, and `pointer_switched=false`. **Manual file import must not become current/readable automatically**.

### Manual file promote flow

Use `market-data:promote --requested_date=<YYYY-MM-DD>` with `request_mode=promote`. Promotion additionally requires `coverage_gate_state=PASS`, complete V2 product/lineage/config, `seal_state=SEALED`, and only then `pointer_switched=true`.

### Provider/API flow

Provider/API acquisition follows the immutable-observation workflow. A smoke command is non-publishing; scheduler proof is not live provider proof.

### Evidence export flow

- `market-data:evidence:export --run_id=<id>`
- `market-data:evidence:export --correction_id=<id>`
- `market-data:evidence:export --replay_id=<id>`
- `market-data:evidence:export --trade_date=<YYYY-MM-DD>`
- `market-data:evidence-replay:full-range-current`

Admission verifies: **run id and requested/effective trade date present**; **publication id/version/current flag present**; **pointer/current-publication context present**; **coverage state/counts/ratio/threshold present**; and **source mode/name/input/file hash/attempt telemetry present**. V2 additionally requires observation/config/temporal/factor/formula/read-model context.

### Replay verification flow

**Replay is the proof mechanism**, not a repair path. Surfaces include `market-data:replay:verify`, `market-data:replay:smoke`, `market-data:replay:backfill`, `market-data:replay:fixture:generate`, and `market-data:evidence-replay:full-range-current`. Negative cases include **reason code mismatch case**, **broken manifest case**, **missing file case**, **coverage mismatch**, and **source context mismatch**, plus the V2 as-known/future-leak cases.

### Correction lifecycle flow

Use `market-data:correction:request`, then `market-data:correction:approve`, then `market-data:correction:run`. Compatibility lifecycle labels `REQUESTED`, `APPROVED`, `RESEALED`, `PUBLISHED`, and `CANCELLED` never permit content mutation. If the **baseline is not current/readable/SEALED/SUCCESS**, stop; the **previous current must stay preserved**.

### Backfill flow

Use `market-data:backfill:lifecycle` or `market-data:backfill:missing-tickers` with bounded dates, plan/checkpoint, immutable observations, and the normal candidate/seal/pointer gates.

### Session snapshot flow

Session snapshots are supplemental publication-bound context and cannot determine EOD readiness or replace canonical bars.

### Supporting source/context imports

`market-data:sector-indexes:ingest-api`, `market-data:sector-indexes:import-bars`, `market-data:sectors:import-memberships`, `market-data:events:import-corporate-actions`, and `market-data:events:import-trading-status` create source/revision context only. They do not directly rewrite a readable publication.

### Manual DB action policy

**Manual DB action is exceptional**. It requires that a **backup/rollback plan exists**, the **SQL file is reviewed**, **reason code and operator name are recorded**, and **evidence exported after action**. `market-data:current-publication:repair` may only clear/restore pointer integrity among already valid immutable publications; it cannot repair content.

Prohibited manual actions include **direct pointer switch to make data readable**, **direct publication current flag edits as publish flow**, and **manual correction status edit**.

### Forbidden shortcuts

Forbidden tokens/patterns include `raw/staging/latest/MAX(date)`, `MAX(trade_date)`, `latest('trade_date')`, **direct pointer switch**, **direct `READABLE` update**, **coverage bypass**, **seal bypass**, **finalize bypass**, **replay bypass**, **empty output treated as success**, **silent provider failure**, and **unbounded provider retry**.

### Operator checklist before publish

Complete preflight/activation checks, verify the requested/latest expected dates, lock/fencing token, immutable config/observation/temporal/factor context, and ensure no prohibited repair/derived-action path ran.

### Operator checklist after publish

Verify the active publication through the gateway, dates/freshness, manifest/seal/hash/context, pointer atomicity, alerts, and exported evidence.

### Troubleshooting quick map

Transport failures receive bounded retry; deterministic validation/gate failures are held; provenance/hash/pointer ambiguity fails closed; corrections create a new publication; stale output retains its true effective date.

### Scheduler / cron deployment flow

The deployment scheduler invokes `php artisan schedule:run`. Activation configuration includes `MARKET_DATA_DAILY_ENABLED=true`, `MARKET_DATA_PLATFORM_TIMEZONE=Asia/Jakarta`, `MARKET_DATA_PLATFORM_EOD_CUTOFF_TIME=HH:MM:SS`, `MARKET_DATA_SCHEDULER_OUTPUT_PATH`, and `MARKET_DATA_SCHEDULER_WITHOUT_OVERLAPPING_MINUTES`. The due event is `market-data:daily --latest`.

Logs distinguish `scheduler_status=SUCCESS` and `scheduler_status=FAILURE`; a failed/held run records `pointer_switched=false`. **Scheduler proof is not live provider proof.**

### Registered runtime commands

- `market-data:daily`
- `market-data:eod-bars:ingest`
- `market-data:eod-indicators:compute`
- `market-data:eod-indicators:recompute-current`
- `market-data:eod-eligibility:build`
- `market-data:audit:hash`
- `market-data:dataset:seal`
- `market-data:run:finalize`
- `market-data:promote`
- `market-data:backfill`
- `market-data:backfill:lifecycle`
- `market-data:backfill:missing-tickers`
- `market-data:evidence:export`
- `market-data:evidence-replay:full-range-current`
- `market-data:sector-indexes:ingest-api`
- `market-data:sector-indexes:import-bars`
- `market-data:sectors:import-memberships`
- `market-data:events:import-corporate-actions`
- `market-data:events:import-trading-status`
- `market-data:replay:verify`
- `market-data:replay:smoke`
- `market-data:replay:backfill`
- `market-data:replay:fixture:generate`
- `market-data:session-snapshot`
- `market-data:session-snapshot:purge`
- `market-data:correction:request`
- `market-data:correction:approve`
- `market-data:correction:run`
- `market-data:current-publication:repair`
- `market-data:provider:smoke`
- `market-data:detect-price-scale-breaks`
- `market-data:repair-price-scale-stretches` — registered P0 blocker; `--apply` must be removed/disabled.
- `market-data:events:derive-corporate-actions` — registered P0 blocker when apply creates/verifies factors from prices.

### Manual validation commands

Run schema/migration parity, targeted owner-contract tests, negative P0/P1 tests, full MarketData PHPUnit, MariaDB integration/replay, command help/dry-run checks, gateway concurrency checks, and consecutive activated-session rehearsals. Store commands, build/runtime identity, results, and artifacts; missing execution is not pass.
