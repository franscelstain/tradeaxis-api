# OPERATIONAL RUNBOOK — MARKET DATA

Status: ENFORCED pending operator-local targeted and full MarketData PHPUnit validation.
Contract: OPERATIONAL_READINESS_CONTRACT.
Owner: market-data operational layer.

This runbook is the operator source of truth. It describes how to run market-data without reading source code, how to decide whether a process may continue, how to handle HELD / FAILED / NOT_READABLE states, how to export evidence, how to verify replay, and which shortcuts are forbidden.


## 0. Ops environment baseline gate

Before any command output is used as evidence, validate the environment against `docs/market_data/ops/OPS_ENVIRONMENT_BASELINE.md`.

Minimum gate:

- PHP must be `>= 7.3` and `< 8.4`; preferred operator/CI baseline is PHP 8.3.x.
- Required extensions must be enabled: `dom`, `json`, `libxml`, `mbstring`, `pdo_mysql`, `pdo_sqlite`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`.
- `.env.testing` must exist before PHPUnit/migration proof.
- Command output used as evidence must contain no `PHP Warning`, `PHP Deprecated`, `Deprecated:`, `PHP Notice`, vendor/framework deprecation text, missing-extension warning, timezone warning, debug noise, or stack trace caused by environment mismatch.
- Unsupported runtime must fail closed with `ENV_UNSUPPORTED_PHP_VERSION`; that is `BLOCKED_CONTAINER_RUNTIME_ENV`, not runtime PASS.

Validation commands:

```text
php -v
composer --version
php -m
php artisan list
php artisan market-data:daily --help
php artisan market-data:evidence:export --help
php artisan market-data:replay:verify --help
vendor/bin/phpunit --version
vendor/bin/phpunit tests/Unit/MarketData/OpsEnvironmentBaselineStaticGuardTest.php
```

## 1. Scope and operator rules

The operator may run documented artisan commands only. The operator must not query raw/staging/latest data as proof of consumer readability, must not manually edit pointer/publication/run tables as a normal flow, and must not promote manual/API data unless the promote path passes coverage, hash, seal, finalize, run-publication linkage, pointer validation, evidence, and replay proof.

Required environment checks:

- `MARKET_DATA_PLATFORM_TIMEZONE=Asia/Jakarta`
- `MARKET_DATA_DEFAULT_SOURCE_MODE=manual_file` or `api`
- `MARKET_DATA_COVERAGE_MIN` configured and reviewed
- market calendar loaded for requested trading dates
- source credentials/config available when using provider/API source
- manual file path available when using `manual_file`
- writable evidence/replay output directory
- database migrations already applied
- reason-code registry and seed synchronized

The operator must stop when terminal output contains `status=BLOCKED`, `terminal_status=FAILED`, `terminal_status=HELD`, `publishability_state=NOT_READABLE`, coverage `FAIL`, coverage `NOT_EVALUABLE`, pointer mismatch, unsealed publication, replay mismatch, or missing evidence metadata.

## 2. Command coverage matrix

| Command | Purpose | Required input | Safe default | Key output | Reason code / next action | Docs / proof |
|---|---|---|---|---|---|---|
| `market-data:daily` | Import-only daily sequence | `--requested_date` or `--latest`, `--source_mode`, optional `--input_file`, `--output_dir` | Import-only; no readable publish claim | `run_id`, `request_mode=import_only`, `import_status`, `promote_status=NOT_PROMOTED`, source summary | If no valid source, stop and export evidence; next action is fix source or rerun import | command surface, import/promote, fail-safe |
| `market-data:eod-bars:ingest` | Acquire/canonicalize EOD bars | requested date/source/run context | stage-only | row counts, source context, invalid count | If zero valid rows, stop; next action is source fix | fail-safe, source resilience |
| `market-data:eod-indicators:compute` | Compute deterministic indicators | requested date/source/run context | stage-only | stage status, hash context | If input artifact missing, stop and inspect run evidence | hash/seal |
| `market-data:eod-eligibility:build` | Build eligibility and coverage context | requested date/source/run context | stage-only | coverage available/universe/missing/ratio | If coverage below threshold, stop before promote | coverage gate |
| `market-data:audit:hash` | Compute deterministic hashes | requested date/source/run context | stage-only | bars/indicator/eligibility hashes | If hash changes without data change, stop and investigate determinism | hash/seal |
| `market-data:dataset:seal` | Seal coherent dataset candidate | requested date/source/run context | seal only | `seal_state=SEALED`, `sealed_at`, `sealed_by` | If unsealed, cannot finalize/read | seal contract |
| `market-data:run:finalize` | Resolve terminal state and pointer eligibility | requested date/source/run context | guarded by service | terminal status, publishability state, publication/pointer fields | If HELD/FAILED/NOT_READABLE, pointer must remain preserved; export evidence | finalize/pointer |
| `market-data:promote` | Explicit publish path | requested date/source, optional `--run_id`, optional force reason | `--force_replace=false` | coverage, seal, finalize, pointer, publication summary | Coverage/seal/pointer failure blocks readable output; next action follows reason code | import/promote, coverage |
| `market-data:backfill` | Historical import-only range | `start_date`, `end_date`, source options | import-only range; no publish | case lines per date | Failed case does not imply readable; fix case then promote explicitly | backfill |
| `market-data:evidence:export` | Export proof bundle | exactly one selector: run/correction/replay/date | no DB state mutation except artifact write | output path, run/publication/pointer/source/reason metadata | If metadata incomplete, treat proof as failed and rerun/export issue | evidence |
| `market-data:replay:verify` | Verify executed run against fixture package | `run_id`, `fixture_path` | deterministic proof | replay id/status/mismatch reason | Mismatch blocks acceptance; next action is compare fixture/output and fix root cause | replay |
| `market-data:replay:smoke` | Run built-in replay cases | `run_id` | deterministic smoke suite | valid/broken/missing/reason mismatch cases | Any failed case blocks readiness claim | replay |
| `market-data:replay:backfill` | Replay verification over date range | start/end date, fixture case/root | deterministic range proof | case summaries | Failed case must be reason-coded and investigated | replay/backfill |
| `market-data:replay:fixture:generate` | Generate runtime MATCH replay fixture from one executed run | `run_id`, `--case`, `--output_dir` | artifact generation only | fixture path, manifest path, expected proof files, next verify command | Use when committed `valid_case` is stale against local run; then verify generated fixture must MATCH | replay/fixture |
| `market-data:session-snapshot` | Capture supplemental session snapshot | trade date, slot, source file | pointer-resolved only | publication/run/scope/captured counts | If no readable current publication, stop; do not query raw/latest | session snapshot |
| `market-data:session-snapshot:purge` | Retention purge | optional `--before_date`, `--dry-run` or `--apply` | dry-run | candidate count, operation mode | Apply only after reviewed dry-run; next action re-run with `--apply` | command safety |
| `market-data:correction:request` | Register correction request | `--trade_date`, `--reason_code`, `--reason_note` | request only | correction id/status | If missing reason, blocked; next action supply registered reason | correction |
| `market-data:correction:approve` | Approve correction | `correction_id`, optional approved_by | approval only | correction status | If not found/not executable, stop | correction |
| `market-data:correction:run` | Execute approved correction | `correction_id`, requested date/source | baseline-preserving guarded run | correction/run/publication/pointer lineage | If baseline invalid or unchanged/failed, previous current must stay preserved | correction |
| `market-data:current-publication:repair` | Detect/clear invalid current pointer mirror state | `--trade_date`, optional `--apply` | dry-run unless apply | operation mode, affected state | Apply only for documented integrity repair; export evidence after | manual DB/action policy |

Operator discovery command:

```text
php artisan list | findstr market-data
```

Help commands that must remain aligned with this runbook:

```text
php artisan market-data:daily --help
php artisan market-data:promote --help
php artisan market-data:evidence:export --help
php artisan market-data:replay:verify --help
php artisan market-data:replay:fixture:generate --help
php artisan market-data:correction:request --help
php artisan market-data:correction:approve --help
php artisan market-data:correction:run --help
```

## 3. Daily operational flow

Use this flow when the source is API/provider or manual file but the goal is only import/readiness staging.

```text
php artisan market-data:daily --requested_date=YYYY-MM-DD --source_mode=api --output_dir=storage/app/market_data/evidence/YYYY-MM-DD
```

Manual file variant:

```text
php artisan market-data:daily --requested_date=YYYY-MM-DD --source_mode=manual_file --input_file=storage/app/market_data/manual/eod-bars-YYYY-MM-DD.csv --output_dir=storage/app/market_data/evidence/YYYY-MM-DD
```

Expected valid import output:

```text
run_id=<id>
requested_date=YYYY-MM-DD
request_mode=import_only
import_status=COMPLETED
promote_status=NOT_PROMOTED
promoted=false
pointer_switched=false
source_mode=<api|manual_file>
accepted_row_count=<n>
reason_code=<registered code if any>
```

Pass criteria:

- command exits success for import path
- accepted row count is non-zero
- source context is visible
- import-only stays `promoted=false`
- pointer is not switched
- no `READABLE` claim is made from import-only

Fail/stop criteria:

- `status=BLOCKED`
- invalid date/source mode
- provider rate limit / source unavailable / malformed manual file
- zero valid rows
- empty success
- `terminal_status=FAILED` or `HELD`
- `publishability_state=NOT_READABLE` without documented next action

Next action after daily import failure: fix source/manual file/provider config, rerun import, export evidence for failed run when a run id exists, and do not promote.

## 4. Manual file import-only flow

Manual file import is not publish. It only loads candidate data and source proof.

```text
php artisan market-data:daily --requested_date=YYYY-MM-DD --source_mode=manual_file --input_file=storage/app/market_data/manual/eod-bars-YYYY-MM-DD.csv --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/import
```

Required checks:

- file path exists
- file row count is visible in source context
- source hash and source file size are captured when available
- accepted rows > 0
- invalid row count reviewed
- `request_mode=import_only`
- `promote_status=NOT_PROMOTED`
- `pointer_switched=false`

Manual file import must not become current/readable automatically. If the operator needs publication, use explicit promote.

## 5. Manual file promote flow

Use promote only after import source proof is acceptable.

```text
php artisan market-data:promote --requested_date=YYYY-MM-DD --source_mode=manual_file --run_id=<import_run_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/promote
```

Expected successful promote output:

```text
request_mode=promote
terminal_status=SUCCESS
publishability_state=READABLE
coverage_gate_state=PASS
seal_state=SEALED
publication_id=<id>
publication_version=<version>
pointer_switched=true
current_publication_id=<publication_id>
lineage_verification_status=RUN_PUBLICATION_LINK_PRESENT
```

Promote must stop when:

- coverage gate fails (`COVERAGE_BELOW_THRESHOLD` or coverage FAIL family)
- dataset is unsealed (`PUBLICATION_NOT_SEALED` family)
- run-publication mirror mismatch
- pointer mismatch / post-switch resolver mismatch
- source no valid data
- manual file malformed/empty
- correction baseline invalid

Force replacement is not normal operation. When present, `--force_replace=false` must remain default; any force use requires an auditable reason via `--force_replace_reason` or equivalent command option and must be captured in evidence.

## 6. Provider/API flow and failure handling

Provider/API flow uses the same daily/import then promote separation.

```text
php artisan market-data:daily --requested_date=YYYY-MM-DD --source_mode=api --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/import
php artisan market-data:promote --requested_date=YYYY-MM-DD --source_mode=api --run_id=<import_run_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/promote
```

Provider failure handling:

| Symptom | Terminal state expectation | Reason family | Operator next action |
|---|---|---|---|
| HTTP 429 / rate limit | HELD or FAILED + NOT_READABLE | `PROVIDER_RATE_LIMITED`, `RUN_SOURCE_RATE_LIMITED`, source retry exhausted family | Stop; wait/backoff by provider policy; do not promote; export evidence if run exists |
| timeout | HELD/FAILED + NOT_READABLE | `RUN_SOURCE_TIMEOUT` family | Check provider/network, retry once per policy, do not bypass with raw/latest |
| source unavailable | HELD/FAILED + NOT_READABLE | source unavailable / acquisition failed family | Fix provider config or switch documented source mode; never fake empty success |
| malformed payload | FAILED + NOT_READABLE | malformed payload family | Capture evidence and escalate to developer/source owner |
| no valid data | FAILED/HELD + NOT_READABLE | no valid data family | Fix source; do not publish empty output |

Provider retry must be bounded. Infinite retry is forbidden.

## 7. Coverage, hash, seal, finalize, pointer sequence

When running stages separately, use this sequence only:

```text
php artisan market-data:eod-bars:ingest --requested_date=YYYY-MM-DD --source_mode=<api|manual_file>
php artisan market-data:eod-indicators:compute --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --run_id=<run_id>
php artisan market-data:eod-eligibility:build --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --run_id=<run_id>
php artisan market-data:audit:hash --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --run_id=<run_id>
php artisan market-data:dataset:seal --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --run_id=<run_id>
php artisan market-data:run:finalize --requested_date=YYYY-MM-DD --source_mode=<api|manual_file> --run_id=<run_id>
```

Gate rule:

- coverage PASS is required before readable publication
- deterministic hash is required before seal
- `seal_state=SEALED` is required before finalize readable
- finalize must preserve pointer on HELD/FAILED/NOT_READABLE
- every readable publication must trace to its run and pointer

## 8. Terminal state handling

| Output state | Meaning | Operator decision | Next action |
|---|---|---|---|
| `SUCCESS / READABLE` | publication passed coverage/seal/finalize/pointer | Continue to evidence export and replay | Export evidence, run replay verify/smoke, record proof |
| `SUCCESS / NOT_READABLE` | process completed but not consumer-readable | Stop for publication | Read reason code, export evidence, fix gate/source/data |
| `HELD / NOT_READABLE` | process preserved safety due recoverable/blocked condition | Stop | Fix source/gate/pointer issue; do not manually switch pointer |
| `FAILED / NOT_READABLE` | process failed and cannot be read | Stop | Export evidence when possible; escalate if reason is not operational |
| `status=BLOCKED` | command input or unsafe action blocked | Stop before mutation | Correct input/options; do not bypass guard |

Required output fields for actionable handling:

- `terminal_status`
- `publishability_state`
- `reason_code` or `final_reason_code`
- source summary when source-related
- coverage summary when coverage-related
- seal/hash summary when dataset-related
- publication/pointer/lineage summary when finalize/promote-related
- `next action` from this runbook or command output where available

## 9. Reason-code handling

The operator must treat reason codes as the decision key, not exception text alone.

| Condition | Expected code family | Operator next action |
|---|---|---|
| coverage below threshold | `COVERAGE_BELOW_THRESHOLD` | Stop promote; fix universe/source; rerun import/promote |
| provider rate limited | `PROVIDER_RATE_LIMITED` / source retry exhausted family | Stop; wait/backoff; export failed evidence |
| source unavailable | source acquisition failed/unavailable family | Fix config/provider/manual source; no raw/latest fallback |
| manual file invalid | manual file malformed/empty/no-valid-data family | Fix file; rerun import-only; do not promote |
| run lock conflict | `RUN_LOCK_CONFLICT` | Wait for existing run; rerun after lock clears |
| pointer mismatch | pointer mismatch/current pointer integrity family | Stop; export evidence; use repair only under policy |
| publication not sealed | `PUBLICATION_NOT_SEALED` | Seal valid artifact; rerun finalize/promote |
| correction baseline invalid | correction baseline invalid family | Reject/run blocked; preserve current pointer |
| correction already published | already-published correction family | Do not rerun; export evidence if needed |
| replay mismatch | replay mismatch family | Compare fixture/proof; do not accept publication until resolved |
| evidence export incomplete | evidence incomplete/missing metadata family | Rerun export/fix evidence service; do not use incomplete proof |

If a terminal condition has no registered reason family, the session must be reopened as a logging/reason-code gap.

## 10. Evidence export flow

Evidence export is mandatory after successful promote and recommended for HELD/FAILED/NOT_READABLE runs.

Run evidence by run id:

```text
php artisan market-data:evidence:export --run_id=<run_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/run-<run_id>
```

Correction evidence:

```text
php artisan market-data:evidence:export --correction_id=<correction_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/correction-<correction_id>
```

Replay evidence:

```text
php artisan market-data:evidence:export --replay_id=<replay_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/replay-<replay_id>
```

Trade-date evidence:

```text
php artisan market-data:evidence:export --trade_date=YYYY-MM-DD --output_dir=storage/app/market_data/evidence/YYYY-MM-DD
```

Evidence pass checklist:

- run id and requested/effective trade date present
- terminal status and publishability state present
- source mode/name/input/file hash/attempt telemetry present when applicable
- coverage state/counts/ratio/threshold present
- hash/seal metadata present
- publication id/version/current flag present when promoted
- pointer/current-publication context present when readable
- correction lineage present when correction-related
- replay context present when replay-related
- reason code/final reason code present for failures/holds
- enough metadata exists to prove the process without manual DB query

Evidence fails if it requires the operator to query database tables manually for proof.

## 11. Replay verification flow

Replay is the proof mechanism for deterministic output.

Verify one run:

```text
php artisan market-data:replay:verify <run_id> storage/app/market_data/replay/fixtures/YYYY-MM-DD/proof.json --output_dir=storage/app/market_data/replay/output/YYYY-MM-DD/run-<run_id>
```

Smoke suite:

```text
php artisan market-data:replay:smoke <run_id> --fixture_root=storage/app/market_data/replay/fixtures --output_dir=storage/app/market_data/replay/output/YYYY-MM-DD/smoke
```

Generate a runtime MATCH fixture from the actual run when the committed `valid_case` does not match the local run context:

```text
php artisan market-data:replay:fixture:generate <run_id> --case=valid_case --output_dir=storage/app/market_data/replay-fixtures/generated-valid-run-<run_id>
php artisan market-data:replay:verify <run_id> storage/app/market_data/replay-fixtures/generated-valid-run-<run_id> --output_dir=storage/app/market-data/replay
php artisan market-data:replay:smoke <run_id> --generate_runtime_valid_case --output_dir=storage/app/market-data/replay
```

Generated fixture acceptance requires `comparison_result=MATCH`, `mismatch_count=0`, generated `manifest.json`, generated `expected/expected_replay_result.json`, and generated `expected/expected_reason_code_counts.json`.

Replay backfill:

```text
php artisan market-data:replay:backfill YYYY-MM-DD YYYY-MM-DD --fixture_case=valid_case --fixture_root=storage/app/market_data/replay/fixtures --output_dir=storage/app/market_data/replay/output/range
```

Replay must cover:

- valid fixture case
- reason code mismatch case
- broken manifest case
- missing file case
- pointer/publication mismatch
- coverage mismatch
- source context mismatch
- import/promote boundary mismatch
- correction lineage mismatch

Replay fail rule: any mismatch blocks acceptance until it has a reason code and root-cause fix.

## 12. Correction lifecycle flow

Correction must preserve previous current publication unless the correction is approved, changed, sealed, finalized, and published safely.

Request:

```text
php artisan market-data:correction:request --trade_date=YYYY-MM-DD --reason_code=<REGISTERED_REASON> --reason_note="operator note" --requested_by=<operator>
```

Approve:

```text
php artisan market-data:correction:approve <correction_id> --approved_by=<approver>
```

Run:

```text
php artisan market-data:correction:run <correction_id> --requested_date=YYYY-MM-DD --source_mode=<api|manual_file>
```

Export evidence:

```text
php artisan market-data:evidence:export --correction_id=<correction_id> --output_dir=storage/app/market_data/evidence/YYYY-MM-DD/correction-<correction_id>
```

Correction states:

| State | Meaning | Operator action |
|---|---|---|
| `REQUESTED` | request registered, not executable yet | Review reason/source/baseline |
| `APPROVED` | allowed to run | Run correction with documented source |
| `PUBLISHED` | correction changed data and safely published | Export evidence and replay |
| `CANCELLED` | not executable | Stop |
| `RESEALED` | changed artifact resealed | Continue finalize/publish only if gates pass |

Reject or stop correction when baseline is not current/readable/SEALED/SUCCESS, artifact is unchanged under unchanged policy, source fails, coverage fails, seal fails, pointer mismatch occurs, or correction already published.

## 13. Backfill flow

Backfill is import-only unless followed by explicit promote per date.

```text
php artisan market-data:backfill YYYY-MM-DD YYYY-MM-DD --source_mode=<api|manual_file> --output_dir=storage/app/market_data/backfill/YYYY-MM-DD --continue_on_error
```

Backfill prerequisites:

- market calendar has at least one trading date in range
- source mode chosen deliberately
- manual file strategy documented if date-specific files are used
- coverage expectation known before promote
- output directory set

Backfill pass criteria:

- each case has requested date, status, source context, run id if imported
- failed cases are reason-coded
- no readable publication is implied by import-only backfill
- evidence export and replay are run for promoted dates only

## 14. Session snapshot flow

Session snapshot is supplemental and must read only from pointer-resolved readable current publication.

Capture:

```text
php artisan market-data:session-snapshot YYYY-MM-DD PREOPEN --source_mode=manual_file --input_file=storage/app/market_data/session/manual-preopen-YYYY-MM-DD.csv --output_dir=storage/app/market_data/session/YYYY-MM-DD
```

Purge dry-run:

```text
php artisan market-data:session-snapshot:purge --before_date=YYYY-MM-DD --dry-run
```

Purge apply after review:

```text
php artisan market-data:session-snapshot:purge --before_date=YYYY-MM-DD --apply
```

Snapshot must stop when no readable current publication exists. Operator must not use raw/staging/latest/MAX(date) as a fallback.

## 15. Manual DB action policy

Manual DB action is exceptional, not normal operation.

Allowed only when all conditions are true:

- documented incident or repair case exists
- evidence exported before action when possible
- command-based repair is unavailable or insufficient
- backup/rollback plan exists
- SQL file is reviewed and stored under docs/tools/process notes
- reason code and operator name are recorded
- evidence exported after action
- replay or pointer validation is run after action when relevant

Preferred repair command:

```text
php artisan market-data:current-publication:repair --trade_date=YYYY-MM-DD
php artisan market-data:current-publication:repair --trade_date=YYYY-MM-DD --apply
```

Forbidden manual DB actions:

- direct pointer switch to make data readable
- direct `READABLE` update
- direct `is_current` update to bypass promote
- direct seal/finalize status edit
- manual coverage override without policy
- manual deletion/cleanup that hides audit history
- manual correction status edit to skip request/approve/run

## 16. Forbidden shortcuts

These are forbidden in operations, docs, commands, code, evidence, and replay:

- raw/staging/latest/MAX(date) consumer proof
- querying raw/staging tables to justify readability
- `MAX(trade_date)` or `latest('trade_date')` as fallback
- direct pointer edits as publish flow
- direct publication current flag edits as publish flow
- coverage bypass
- seal bypass
- finalize bypass
- replay bypass for accepted publication proof
- manual file directly becoming readable
- empty output treated as success
- silent provider failure
- unbounded provider retry
- destructive command without guard/apply confirmation

## 17. Operator checklist before publish

- source mode chosen and documented
- import-only run exists and is not promoted
- accepted rows > 0
- coverage universe and threshold understood
- invalid rows reviewed
- reason code absent or acceptable for non-terminal warnings only
- hash computed deterministically
- dataset sealed
- promote command selected explicitly
- force replacement not used unless documented

## 18. Operator checklist after publish

- terminal status is `SUCCESS`
- publishability state is `READABLE`
- coverage gate is `PASS`
- seal state is `SEALED`
- publication id/version exists
- pointer switched to the same publication
- lineages run → publication → pointer are present
- evidence exported
- replay verify/smoke passed
- audit evidence recorded for local validation

## 19. Troubleshooting quick map

| Problem | Stop? | Next action |
|---|---:|---|
| `COMMAND_INVALID_DATE_FORMAT` | Yes | Fix date format to YYYY-MM-DD |
| `COMMAND_INVALID_SOURCE_MODE` | Yes | Use `api` or `manual_file` |
| coverage fail | Yes | Fix source/universe; rerun import/promote |
| source rate limit | Yes | Wait/backoff; rerun; do not fake file unless approved source switch |
| zero valid data | Yes | Fix source/manual file |
| HELD | Yes | Read reason, preserve pointer, export evidence |
| FAILED | Yes | Export evidence if possible, fix root cause |
| NOT_READABLE | Yes | Do not expose to consumers |
| replay mismatch | Yes | Compare fixture/proof; fix deterministic mismatch |
| evidence incomplete | Yes | Rerun/fix evidence before acceptance |

## 20. Manual validation commands

Operator-local commands required before claiming DONE/LOCKED:

```text
vendor/bin/phpunit tests/Unit/MarketData/OperationalReadinessStaticGuardTest.php
vendor/bin/phpunit tests/Unit/MarketData --filter "OperationalReadiness"
vendor/bin/phpunit tests/Unit/MarketData --filter "CommandSurface"
vendor/bin/phpunit tests/Unit/MarketData --filter "Evidence"
vendor/bin/phpunit tests/Unit/MarketData --filter "Replay"
vendor/bin/phpunit tests/Unit/MarketData --filter "Correction"
vendor/bin/phpunit tests/Unit/MarketData --filter "FailSafe"
vendor/bin/phpunit tests/Unit/MarketData
php artisan list | findstr market-data
```

Expected output:

```text
OK (... tests, ... assertions)
```

Pass criteria: all targeted filters and full `tests/Unit/MarketData` pass locally, and command help lists match this runbook.

Fail criteria: any command missing from docs, missing terminal state handling, missing reason-code handling, missing evidence/replay flow, missing manual DB action policy, or any PHPUnit failure.
